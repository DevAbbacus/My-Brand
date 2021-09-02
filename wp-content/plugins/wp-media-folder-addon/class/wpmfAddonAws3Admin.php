<?php
/* Prohibit direct script loading */
defined('ABSPATH') || die('No direct script access allowed!');
require_once(WPMFAD_PLUGIN_DIR . '/class/wpmfAws3.php');

use WP_Media_Folder\Aws\S3\Exception\S3Exception;
use Joomunited\WPMF\JUMainQueue;

/**
 * Class WpmfAddonOneDriveAdmin
 * This class that holds most of the admin functionality for OneDrive
 */
class WpmfAddonAws3Admin
{

    /**
     * Amazon settings
     *
     * @var array
     */
    public $aws3_settings = array();

    /**
     * Amazon default settings
     *
     * @var array
     */
    public $aws3_config_default = array();

    /**
     * WpmfAddonOneDriveAdmin constructor.
     */
    public function __construct()
    {
        if (is_plugin_active('wp-media-folder/wp-media-folder.php')) {
            $this->runUpgrades();
            $aws3config                = get_option('_wpmfAddon_aws3_config');
            $this->aws3_config_default = array(
                'signature_version'        => 'v4',
                'version'                  => '2006-03-01',
                'region'                   => 'us-east-1',
                'bucket'                   => 0,
                'credentials'              => array(
                    'key'    => '',
                    'secret' => ''
                ),
                'copy_files_to_bucket'     => 0,
                'remove_files_from_server' => 0,
                'attachment_label'         => 0,
                'enable_custom_domain'         => 0,
                'custom_domain'         => ''
            );

            if (is_array($aws3config)) {
                $this->aws3_settings = array_merge($this->aws3_config_default, $aws3config);
            } else {
                $this->aws3_settings = $this->aws3_config_default;
            }

            $this->actionHooks();
            $this->filterHooks();
            $this->handleAjax();
        }
    }

    /**
     * Ajax action
     *
     * @return void
     */
    public function handleAjax()
    {
        add_action('wp_ajax_wpmf-get-buckets', array($this, 'getBucketsList'));
        add_action('wp_ajax_wpmf-create-bucket', array($this, 'createBucket'));
        add_action('wp_ajax_wpmf-delete-bucket', array($this, 'deleteBucket'));
        add_action('wp_ajax_wpmf-select-bucket', array($this, 'selectBucket'));
        add_action('wp_ajax_wpmf-uploadto-s3', array($this, 'uploadToS3'));
        add_action('wp_ajax_wpmf-download-s3', array($this, 'downloadObject'));
        add_action('wp_ajax_wpmf_upload_single_file_to_s3', array($this, 'ajaxUploadSingleFileToS3'));
        add_action('wp_ajax_wpmf_remove_local_file', array($this, 'removeLocalFile'));
        add_action('wp_ajax_wpmf-list-all-objects-from-bucket', array($this, 'listAllObjectsFromBucket'));
        add_action('wp_ajax_wpmf-list-all-copy-objects-from-bucket', array($this, 'listAllCopyObjects'));
        add_action('wp_ajax_wpmf-copy-objects-from-bucket', array($this, 'ajaxCopyObject'));
    }

    /**
     * Action hooks
     *
     * @return void
     */
    public function actionHooks()
    {
        if (!empty($this->aws3_settings['copy_files_to_bucket'])) {
            add_action('add_attachment', array($this, 'addAttachment'), 10, 1);
        }

        add_action('admin_enqueue_scripts', array($this, 'loadAdminScripts'));
        add_action('add_meta_boxes', array($this, 'attachmentMetaBox'));
    }

    /**
     * Filter hooks
     *
     * @return void
     */
    public function filterHooks()
    {
        add_filter('wpmfaddon_aws3settings', array($this, 'renderSettings'), 10, 1);
        add_filter('delete_attachment', array($this, 'deleteAttachment'), 20);
        add_filter('wp_get_attachment_url', array($this, 'wpGetAttachmentUrl'), 99, 2);
        add_filter('get_attached_file', array($this, 'getAttachedFile'), 10, 2);
        add_filter('wpmf_get_attached_file', array($this, 'getAttachedS3File'), 20, 3);
        add_filter('wpmf_get_attached_file', array($this, 'imageEditorDownloadFile'), 10, 3);
        add_filter('wpmf_get_attached_file', array($this, 'regenerateThumbnails'), 10, 3);
        add_filter('wpmf_get_attached_file', array($this, 'cropImage'), 10, 3);
        add_filter('wp_calculate_image_srcset', array($this, 'wpCalculateImageSrcset'), 10, 5);
        add_filter('wp_calculate_image_srcset_meta', array($this, 'wpCalculateImageSrcsetMeta'), 10, 4);
        add_filter('wp_prepare_attachment_for_js', array($this, 'wpPrepareAttachmentForJs'), 99, 3);
        add_filter('wp_generate_attachment_metadata', array($this, 'wpUpdateAttachmentMetadata'), 110, 2);
        add_filter('wpmf_s3_replace_local', array($this, 'replaceLocalUrl'), 10, 3);
        add_filter('wpmf_s3_replace_urls3', array($this, 'replaceLocalUrlS3'), 10, 3);
        add_filter('wpmf_replace_s3_url_by_page', array($this, 'updateAttachmentUrlToDatabaseByPage'), 10, 3);
        add_filter('wpmf_s3_import', array($this, 'importObjectsFromBucket'), 10, 3);
    }

    /**
     * Import objects from Bucket
     *
     * @param integer|boolean $result     Result
     * @param array           $datas      QUeue datas
     * @param integer         $element_id Queue ID
     *
     * @return boolean
     */
    public function importObjectsFromBucket($result, $datas, $element_id)
    {
        set_time_limit(0);
        // insert folder parent
        if (dirname($datas['key']) === '.') {
            $root_folder = wp_insert_term($datas['bucket'], WPMF_TAXO, array('parent' => 0));
            if (is_wp_error($root_folder)) {
                if (isset($root_folder->error_data) && isset($root_folder->error_data['term_exists'])) {
                    $parent = $root_folder->error_data['term_exists'];
                }
            } else {
                $parent = $root_folder['term_id'];
            }
        } else {
            global $wpdb;
            $queue = $wpdb->get_row($wpdb->prepare('SELECT * FROM ' . $wpdb->prefix . 'wpmf_queue WHERE id = %d', array($element_id)));
            $responses = json_decode($queue->responses, true);
            $parent = $responses['parent'];
        }

        $import_key = sanitize_title($datas['bucket'] . '/' . $datas['key']);
        // insert child folder
        if ($datas['type'] === 'folder') {
            // check folder imported
            $args = array(
                'hide_empty' => false,
                'meta_query' => array(
                    array(
                        'key'       => 'wpmf_s3_import_key',
                        'value'     => $import_key,
                        'compare'   => '='
                    )
                ),
                'taxonomy'  => WPMF_TAXO
            );
            $folders = get_terms($args);
            // if folder not exists
            if (empty($folders)) {
                $inserted = wp_insert_term(
                    basename($datas['key']),
                    WPMF_TAXO,
                    array(
                        'parent' => $parent,
                        'slug' => sanitize_title($datas['key']) . WPMF_TAXO
                    )
                );
                $parent = $inserted['term_id'];
            } else {
                $parent = (int)$folders[0]->term_id;
            }

            update_term_meta($parent, 'wpmf_s3_import_key', $import_key);
            JUMainQueue::updateQueueTermMeta((int)$parent, (int)$element_id);
        } else {
            global $wpdb;
            // check attachment imported
            $exist = $wpdb->get_var($wpdb->prepare('SELECT COUNT(meta_id) FROM ' . $wpdb->postmeta . ' WHERE meta_key = "wpmf_s3_import_key" AND meta_value = %s', array($import_key)));
            if (empty($exist)) {
                // don't upload to S3
                remove_action('add_attachment', array($this, 'addAttachment'));
                $upload_dir = wp_upload_dir();
                $aws3 = new WpmfAddonAWS3($datas['region']);
                $upload_dir = wp_upload_dir();
                if (file_exists($upload_dir['path'] . '/' . basename($datas['key']))) {
                    $file   = wp_unique_filename($upload_dir['path'], basename($datas['key']));
                } else {
                    $file = basename($datas['key']);
                }

                $file_path = $upload_dir['path'] . '/' . $file;
                if (file_exists($file_path)) {
                    return true;
                }
                $file_url = $upload_dir['url'] . '/' . $file;
                $path_parts = pathinfo($file_path);
                $info_file  = wp_check_filetype($file_path);
                // download attachment from S3
                try {
                    $aws3->getObject(array(
                        'Bucket' => $datas['bucket'],
                        'Key'    => $datas['key'],
                        'SaveAs' => $file_path
                    ));

                    // insert attachment to media library
                    $attachment = array(
                        'guid' => $file_url,
                        'post_mime_type' => $info_file['type'],
                        'post_title'     => $path_parts['filename'],
                        'post_status'    => 'inherit'
                    );
                    // Insert attachment
                    $attach_id   = wp_insert_attachment($attachment, $file_path);
                    // set attachment to term
                    wp_set_object_terms((int) $attach_id, (int) $parent, WPMF_TAXO, false);
                    $attach_data = wp_generate_attachment_metadata($attach_id, $file_path);
                    wp_update_attachment_metadata($attach_id, $attach_data);
                    update_post_meta($attach_id, 'wpmf_s3_import_key', $import_key);
                    JUMainQueue::updateQueuePostMeta((int)$attach_id, (int)$element_id);
                    return true;
                } catch (S3Exception $e) {
                    return false;
                }
            }
        }

        if ($datas['type'] === 'folder') {
            // update parent in array
            global $wpdb;
            $wpdb->update(
                $wpdb->prefix . 'wpmf_queue',
                array(
                    'responses' => stripslashes(json_encode(array('parent' => $parent)))
                ),
                array('responses' => stripslashes('{"parent":"' . $datas['bucket'] . '-' . $datas['key'] . '"}')),
                array('%s'),
                array('%s')
            );
        }
        return true;
    }

    /**
     * List all objects from Bucket
     *
     * @return void
     */
    public function listAllObjectsFromBucket()
    {
        if (empty($_POST['wpmf_nonce'])
            || !wp_verify_nonce($_POST['wpmf_nonce'], 'wpmf_nonce')) {
            die();
        }

        set_time_limit(0);
        $aws3config = get_option('_wpmfAddon_aws3_config');
        $aws3config['bucket'] = $_POST['bucket'];
        $aws3 = new WpmfAddonAWS3();
        $region = $aws3->getBucketLocation(
            array('Bucket' => $_POST['bucket'])
        );
        $aws3config['region'] = $region;
        if (isset($aws3config['bucket'])) {
            $arrs = $this->getAllImportObjects($aws3config);
            $list = $arrs['list'];
            $term_root_id = $arrs['term_root_id'];
            $objests_list = $this->extractListObjectsFromPath($list, $term_root_id);
            ksort($objests_list);
            foreach ($objests_list as $objest) {
                $datas = array(
                    'key' => $objest['key'],
                    'type' => $objest['type'],
                    'parent' => $objest['parent'],
                    'bucket' => $_POST['bucket'],
                    'region' => $region,
                    'action' => 'wpmf_s3_import',
                );

                $responses = array(
                    'parent' => $_POST['bucket'] . '-' . $objest['parent']
                );

                $row = JUMainQueue::checkQueueExist(json_encode($datas));
                if (!$row) {
                    JUMainQueue::addToQueue($datas, $responses);
                }
            }
            wp_send_json(array('status' => true, 'region' => $region));
        }

        wp_send_json(array('status' => false));
    }

    /**
     * Extract list objects from path list
     *
     * @param array   $arrs         Origin list objects
     * @param integer $term_root_id ID of bucket folder on media library
     * @param array   $new_list     New list objects
     *
     * @return array
     */
    public function extractListObjectsFromPath($arrs, $term_root_id = 0, $new_list = array())
    {
        foreach ($arrs as $k => $arr) {
            $parent = dirname($arr['key']);
            if (!isset($new_list[$arr['key']])) {
                $new_list[$arr['key']] = $arr;
            }
            unset($arrs[$k]);
            if ($parent !== '.') {
                if (!isset($arrs[$parent . '/'])) {
                    $arrs[$parent . '/'] = array(
                        'key' => $parent . '/',
                        'type' => 'folder',
                        'parent' => (dirname($parent) !== '.') ? dirname($parent) . '/' : (int) $term_root_id,
                    );
                    if (strpos($k, '***' . $parent) !== false) {
                        unset($arrs[$k]);
                    }
                }
            }
        }
        if (!empty($arrs)) {
            $new_list = $this->extractListObjectsFromPath($arrs, $term_root_id, $new_list);
        }
        return $new_list;
    }

    /**
     * List all objects from Bucket
     *
     * @param array $aws3config Options
     *
     * @return array
     */
    public function getAllImportObjects($aws3config)
    {
        $aws3 = new WpmfAddonAWS3($aws3config['region']);
        $objects = $aws3->getFoldersFilesFromBucket(array('Bucket' => $aws3config['bucket']));
        if (empty($objects)) {
            wp_send_json(array('status' => false));
        }

        $arrs = array();
        $term_root_id = 0;
        foreach ($objects as $object) {
            $info = pathinfo($object['Key']);
            $parent = dirname($object['Key']) . '/';
            if ($parent === './') {
                $inserted = wp_insert_term($aws3config['bucket'], WPMF_TAXO, array('parent' => 0));
                if (is_wp_error($inserted)) {
                    if (isset($inserted->error_data) && isset($inserted->error_data['term_exists'])) {
                        $parent = $inserted->error_data['term_exists'];
                        $term_root_id = $inserted->error_data['term_exists'];
                    }
                } else {
                    $parent = $inserted['term_id'];
                    $term_root_id = $inserted['term_id'];
                }
            }

            if (empty($info['extension'])) {
                $arrs[$object['Key'] . '***' . $parent] = array(
                    'key' => $object['Key'],
                    'type' => 'folder',
                    'parent' => $parent
                );
            } else {
                $arrs[$object['Key'] . '***' . $parent] = array(
                    'key' => $object['Key'],
                    'type' => 'file',
                    'parent' => $parent
                );
            }
        }
        ksort($arrs);
        return array('list' => $arrs, 'term_root_id' => $term_root_id);
    }

    /**
     * Remove local file
     *
     * @return void
     */
    public function removeLocalFile()
    {
        if (empty($_POST['wpmf_nonce'])
            || !wp_verify_nonce($_POST['wpmf_nonce'], 'wpmf_nonce')) {
            die();
        }

        $configs = get_option('_wpmfAddon_aws3_config');
        if (empty($configs['remove_files_from_server'])) {
            wp_send_json(array('status' => false));
        }

        if (empty($_POST['ids'])) {
            wp_send_json(array('status' => false));
        }

        $ids = explode(',', $_POST['ids']);
        foreach ($ids as $id) {
            $this->doRemoveLocalFile($id);
        }

        wp_send_json(array('status' => true));
    }

    /**
     * Ajax upload single file to S3
     *
     * @return void
     */
    public function ajaxUploadSingleFileToS3()
    {
        if (empty($_POST['wpmf_nonce'])
            || !wp_verify_nonce($_POST['wpmf_nonce'], 'wpmf_nonce')) {
            die();
        }

        if (empty($_POST['ids'])) {
            wp_send_json(array('status' => false));
        }
        $configs = get_option('_wpmfAddon_aws3_config');
        $remove = (!empty($configs['remove_files_from_server'])) ? 1: 0;
        $ids = explode(',', $_POST['ids']);
        $success_ids = array();
        foreach ($ids as $id) {
            $infos = get_post_meta((int) $id, 'wpmf_awsS3_info', true);
            if (!empty($infos)) {
                continue;
            }

            $aws3       = new WpmfAddonAWS3();
            $return = $this->uploadSingleFileToS3((int) $id, $aws3);
            if ($return['status']) {
                $success_ids[] = (int) $id;
            }
        }

        if (empty($success_ids)) {
            wp_send_json(array('status' => false));
        }

        wp_send_json(array('status' => true, 'remove' => $remove, 'ids' => implode(',', $success_ids)));
    }

    /**
     * List all objects from Bucket
     *
     * @param array $aws3config Options
     *
     * @return array
     */
    public function getAllCopyObjects($aws3config)
    {
        $aws3 = new WpmfAddonAWS3($aws3config['region']);
        $objects = $aws3->getFoldersFilesFromBucket(array('Bucket' => $aws3config['bucket']));
        if (empty($objects)) {
            wp_send_json(array('status' => false));
        }

        $arrs = array();
        foreach ($objects as $object) {
            $info = pathinfo($object['Key']);
            if (empty($info['extension'])) {
                $arrs[$object['Key']] = array(
                    'key' => $object['Key'],
                    'type' => 'folder'
                );
            } else {
                $arrs[$object['Key']] = array(
                    'key' => $object['Key'],
                    'type' => 'file'
                );
            }
        }
        ksort($arrs);
        return $arrs;
    }

    /**
     * List all copy objects
     *
     * @return void
     */
    public function listAllCopyObjects()
    {
        if (empty($_POST['wpmf_nonce'])
            || !wp_verify_nonce($_POST['wpmf_nonce'], 'wpmf_nonce')) {
            die();
        }

        set_time_limit(0);
        $aws3config = get_option('_wpmfAddon_aws3_config');
        $aws3config['bucket'] = $_POST['from_bucket'];
        $aws3 = new WpmfAddonAWS3();
        $from_region = $aws3->getBucketLocation(
            array('Bucket' => $_POST['from_bucket'])
        );

        $to_region = $aws3->getBucketLocation(
            array('Bucket' => $_POST['to_bucket'])
        );
        $aws3config['region'] = $from_region;
        $arrs = $this->getAllCopyObjects($aws3config);
        update_option('wpmf_s3_copy_list', $arrs);
        wp_send_json(array('status' => true, 'to_region' => $to_region));
    }

    /**
     * Ajax copy object
     *
     * @return void
     */
    public function ajaxCopyObject()
    {
        if (empty($_POST['wpmf_nonce'])
            || !wp_verify_nonce($_POST['wpmf_nonce'], 'wpmf_nonce')) {
            die();
        }

        set_time_limit(0);
        $arrs = get_option('wpmf_s3_copy_list', true);
        if (empty($arrs)) {
            wp_send_json(array('status' => true, 'continue' => false));
        }

        $aws3 = new WpmfAddonAWS3($_POST['region']);
        $from_bucket = $_POST['from_bucket'];
        $to_bucket = $_POST['to_bucket'];
        // get first element
        $copys = array_slice($arrs, 0, 5);
        foreach ($copys as $key => $copy) {
            try {
                $result = $aws3->copyObject(array(
                    'ACL'          => 'public-read',
                    'Bucket' => $to_bucket,
                    'CopySource' => urlencode($from_bucket . '/' . $copy['key']),
                    'Key' => $copy['key'],
                    'MetadataDirective' => 'COPY'
                ));
                unset($arrs[$key]);
            } catch (S3Exception $e) {
                unset($arrs[$key]);
            }
        }

        update_option('wpmf_s3_copy_list', $arrs);
        wp_send_json(array('status' => true, 'continue' => true));
    }

    /**
     * Filters the attachment data prepared for JavaScript.
     * Base on /wp-includes/media.php
     *
     * @param array          $response   Array of prepared attachment data.
     * @param integer|object $attachment Attachment ID or object.
     * @param array          $meta       Array of attachment meta data.
     *
     * @return mixed $response
     */
    public function wpPrepareAttachmentForJs($response, $attachment, $meta)
    {
        $infos = get_post_meta($attachment->ID, 'wpmf_awsS3_info', true);
        if (empty($infos)) {
            return $response;
        }

        $response['aws3_infos'] = $infos;
        return $response;
    }

    /**
     * Alter the image meta data to add srcset support for object versioned S3 URLs
     *
     * @param array   $image_meta    The image meta data as returned by 'wp_get_attachment_metadata()'.
     * @param array   $size_array    Array of width and height values in pixels (in that order).
     * @param string  $image_src     The 'src' of the image.
     * @param integer $attachment_id The image attachment ID to pass to the filter
     *
     * @return array
     */
    public function wpCalculateImageSrcsetMeta($image_meta, $size_array, $image_src, $attachment_id)
    {
        if (empty($image_meta['file'])) {
            return $image_meta;
        }

        if (false !== strpos($image_src, $image_meta['file'])) {
            return $image_meta;
        }

        //  return if not on s3
        $infos = get_post_meta($attachment_id, 'wpmf_awsS3_info', true);
        if (empty($infos)) {
            return $image_meta;
        }

        $image_meta['file'] = rawurlencode(wp_basename($image_meta['file']));
        if (!empty($image_meta['sizes'])) {
            $image_meta['sizes'] = array_map(function ($size) {
                $size['file'] = rawurlencode($size['file']);
                return $size;
            }, $image_meta['sizes']);
        }

        return $image_meta;
    }

    /**
     * Replace local URLs with S3 ones for srcset image sources
     *
     * @param array   $srcs          Source
     * @param array   $size_array    Array of width and height values in pixels (in that order).
     * @param string  $image_src     The 'src' of the image.
     * @param array   $image_meta    The image meta data as returned by 'wp_get_attachment_metadata()'.
     * @param integer $attachment_id The image attachment ID to pass to the filter
     *
     * @return array
     */
    public function wpCalculateImageSrcset($srcs, $size_array, $image_src, $image_meta, $attachment_id)
    {
        if (!is_array($srcs)) {
            return $srcs;
        }

        //  return if not on s3
        $infos = get_post_meta($attachment_id, 'wpmf_awsS3_info', true);
        if (empty($infos)) {
            return $srcs;
        }

        foreach ($srcs as $width => $source) {
            $size = $this->getImageSizeByWidth($image_meta['sizes'], $width, wp_basename($source['url']));
            if (!empty($size)) {
                $url                 = wp_get_attachment_image_src($attachment_id, $size);
                $srcs[$width]['url'] = $url[0];
            } else {
                $url                 = wp_get_attachment_url($attachment_id);
                $srcs[$width]['url'] = $url;
            }
        }

        return $srcs;
    }

    /**
     * Helper function to find size name from width and filename
     *
     * @param array  $sizes    List sizes
     * @param string $width    Width
     * @param string $filename File name
     *
     * @return null|string
     */
    public function getImageSizeByWidth($sizes, $width, $filename)
    {
        foreach ($sizes as $size_name => $size) {
            if ($width === (int) $size['width'] && $filename === $size['file']) {
                return $size_name;
            }
        }

        return null;
    }

    /**
     * Check if the plugin need to run an update of db or options
     *
     * @return void
     */
    public function runUpgrades()
    {
        $version = get_option('wpmf_addon_version', '1.0.0');
        // Up to date, nothing to do
        if ($version === WPMFAD_VERSION) {
            return;
        }

        if (version_compare($version, '2.2.0', '<')) {
            global $wpdb;
            $wpdb->query('CREATE TABLE `' . $wpdb->prefix . 'wpmf_s3_queue` (
                      `id` int(11) NOT NULL,
                      `post_id` int(11) NOT NULL,
                      `destination` text NOT NULL,
                      `date_added` varchar(14) NOT NULL,
                      `date_done` varchar(14) DEFAULT NULL,
                      `status` tinyint(1) NOT NULL
                    ) ENGINE=InnoDB');

            $wpdb->query('ALTER TABLE `' . $wpdb->prefix . 'wpmf_s3_queue`
                          ADD UNIQUE KEY `id` (`id`),
                          ADD KEY `date_added` (`date_added`,`status`);');

            $wpdb->query('ALTER TABLE `' . $wpdb->prefix . 'wpmf_s3_queue`
                          MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;');
        }

        // Set default options values
        $options = get_option('wp-media-folder-addon-tables');
        if (!$options) {
            add_option(
                'wp-media-folder-addon-tables',
                array(
                    'wp_posts' => array(
                        'post_content' => 1,
                        'post_excerpt' => 1
                    )
                )
            );
        }
        update_option('wpmf_addon_version', WPMFAD_VERSION);
    }

    /**
     * Includes styles and some scripts
     *
     * @return void
     */
    public function loadAdminScripts()
    {
        global $current_screen;
        if (!empty($current_screen->base) && $current_screen->base === 'settings_page_option-folder') {
            wp_enqueue_style(
                'wpmf-magnific-popup',
                WPMF_PLUGIN_URL . '/assets/css/display-gallery/magnific-popup.css',
                array(),
                '0.9.9'
            );

            wp_enqueue_script(
                'wpmf-magnific-popup',
                WPMF_PLUGIN_URL. '/assets/js/display-gallery/jquery.magnific-popup.min.js',
                array('jquery'),
                '0.9.9',
                true
            );

            wp_enqueue_script(
                'wpmf-circle-progress',
                plugins_url('assets/js/circle-progress.js', dirname(__FILE__)),
                array('jquery'),
                WPMFAD_VERSION
            );

            wp_enqueue_script(
                'wpmf-aws3-option',
                plugins_url('/assets/js/aws3-option.js', dirname(__FILE__)),
                array('jquery', 'wpmf-script-option', 'wpmf-magnific-popup', 'wpmf-circle-progress'),
                WPMFAD_VERSION
            );

            wp_localize_script('wpmf-aws3-option', 'wpmfS3', array(
                'l18n' => array(
                    'import_s3_to_library'  => esc_html__('Importing Amazon S3 files to media', 'wpmfAddon'),
                    'bucket_selected'  => esc_html__('Selected bucket', 'wpmfAddon'),
                    'sync_process_text' => esc_html__('Syncronization on the way, please wait', 'wpmfAddon'),
                    'bucket_select'    => esc_html__('Select bucket', 'wpmfAddon'),
                    'no_upload_s3_msg' => esc_html__('Please enable (Copy to Amazon S3) option', 'wpmfAddon'),
                    'sync_btn_text' => esc_html__('Synchronize with Amazon S3', 'wpmfAddon'),
                    'upload_to_s3' => esc_html__('Uploading the files to S3...', 'wpmfAddon'),
                    'download_from_s3' => esc_html__('Downloading the files from S3...', 'wpmfAddon'),
                    'update_local_url' => esc_html__('Updating content...', 'wpmfAddon'),
                    'delete_local_files' => esc_html__('Deleting the files on server...', 'wpmfAddon'),
                    'dialog_label' => esc_html__('Infomation', 'wpmfAddon'),
                    'choose_bucket_copy' => esc_html__('You need choose copy bucket and destination bucket', 'wpmfAddon'),
                    'queue_import_alert' => __('Media will be imported asynchronously in backgound', 'wpmfAddon')
                ),
                'vars' => array(
                    'wpmf_nonce' => wp_create_nonce('wpmf_nonce'),
                )
            ));
        }
    }

    /**
     * Get S3 complete percent
     *
     * @return array
     */
    public function getS3CompletePercent()
    {
        global $wpdb;
        $all_attachments    = $wpdb->get_var('SELECT COUNT(ID) FROM ' . $wpdb->posts . ' WHERE post_type = "attachment" AND post_status != "trash"');
        $all_cloud_attachments = $wpdb->get_var('SELECT COUNT(ID) FROM ' . $wpdb->posts . ' as p INNER JOIN ' . $wpdb->postmeta . ' as pm ON p.ID = pm.post_id WHERE post_type = "attachment" AND pm.meta_key = "wpmf_drive_id" AND pm.meta_value != ""');
        $count_attachment    = $all_attachments - $all_cloud_attachments;
        $count_attachment_s3 = $wpdb->get_var('SELECT COUNT(ID) FROM ' . $wpdb->posts . ' as p INNER JOIN ' . $wpdb->postmeta . ' as pm ON p.ID = pm.post_id WHERE p.post_type = "attachment" AND pm.meta_key = "wpmf_awsS3_info" AND pm.meta_value !=""');
        if ($count_attachment_s3 >= $count_attachment) {
            $s3_percent = 100;
        } else {
            if ((int) $count_attachment === 0) {
                $s3_percent = 0;
            } else {
                $s3_percent = ceil($count_attachment_s3 / $count_attachment * 100);
            }
        }

        $local_files_count = $all_attachments - $all_cloud_attachments - $count_attachment_s3;
        return array('local_files_count' => $local_files_count, 's3_percent' => (int) $s3_percent);
    }

    /**
     * Update new URL attachment in database
     *
     * @param integer $post_id     Attachment ID
     * @param string  $file_path   Files path
     * @param string  $destination Destination
     * @param boolean $retrieve    Retrieve
     * @param array   $tables      All tables in database
     *
     * @return void
     */
    public function updateAttachmentUrlToDatabase($post_id, $file_path, $destination, $retrieve, $tables)
    {
        global $wpdb;
        $infos = get_post_meta($post_id, 'wpmf_awsS3_info', true);
        if (empty($infos)) {
            return;
        }

        $meta   = get_post_meta($post_id, '_wp_attachment_metadata', true);
        // get attachted file
        if (!empty($meta) && !empty($meta['file'])) {
            $attached_file = $meta['file'];
        } else {
            $attached_file = get_post_meta($post_id, '_wp_attached_file', true);
        }

        $old_url = str_replace(
            str_replace('\\', '/', get_home_path()),
            str_replace('\\', '/', home_url()) . '/',
            str_replace('\\', '/', $file_path)
        );

        $new_url = str_replace(rtrim(home_url(), '/'), $destination, $old_url);
        $new_url = urldecode($this->encodeFilename($new_url));

        if ($retrieve) {
            $search_url = $new_url;
            $replace_url = $old_url;
        } else {
            $search_url = $old_url;
            $replace_url = $new_url;
        }

        if ($search_url === '' || $replace_url === '') {
            return;
        }

        // ===========================
        foreach ($tables as $table => &$columns) {
            if (!count($columns)) {
                continue;
            }

            // Get the primary key of the table
            $key = $wpdb->get_row('SHOW KEYS FROM  ' . esc_sql($table) . ' WHERE Key_name = "PRIMARY"');

            // No primary key, we can't do anything in this table
            if ($key === null) {
                continue;
            }

            $key = $key->Column_name;

            $count_records = $wpdb->get_var('SELECT COUNT(' . esc_sql($key) . ') FROM ' . esc_sql($table));
            $limit = 200;
            $total_pages = ceil($count_records/$limit);
            for ($i = 1; $i <= $total_pages; $i++) {
                $datas = array(
                    'table' => $table,
                    'columns' => $columns,
                    'page' => (int)$i,
                    'limit' => (int)$limit,
                    'key' => $key,
                    'search_url' => $search_url,
                    'replace_url' => $replace_url,
                    'attached_file' => $attached_file,
                    'action' => 'wpmf_replace_s3_url_by_page'
                );
                $row = JUMainQueue::checkQueueExist(json_encode($datas));
                if (!$row) {
                    JUMainQueue::addToQueue($datas);
                }
            }
        }
    }

    /**
     * Replace S3 URL in database by page
     *
     * @param boolean $result     Result
     * @param array   $datas      Data details
     * @param integer $element_id ID of queue element
     *
     * @return boolean
     */
    public function updateAttachmentUrlToDatabaseByPage($result, $datas, $element_id)
    {
        global $wpdb;
        $table = $datas['table'];
        $columns = $datas['columns'];
        $key = $datas['key'];
        $search_url = $datas['search_url'];
        $replace_url = $datas['replace_url'];
        $attached_file = $datas['attached_file'];
        $offset = ((int)$datas['page'] - 1)*(int)$datas['limit'];
        foreach ($columns as $column => $column_value) {
            if ($column === 'key') {
                continue;
            }

            // Search for serialized strings
            $query = 'SELECT ' . esc_sql($key) . ',' . esc_sql($column) . ' FROM ' . esc_sql($table) . ' WHERE
' . esc_sql($column) . ' REGEXP \'s:[0-9]+:".*(' . esc_sql(preg_quote($search_url)) . '|' . esc_sql(preg_quote($attached_file)) . ').*";\' LIMIT '. esc_sql($datas['limit']) .' OFFSET ' . esc_sql($offset);

            // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Query escaped previously
            $results = $wpdb->get_results($query, ARRAY_N);

            if (count($results)) {
                foreach ($results as $result) {
                    $unserialized_var = unserialize($result[1]);
                    if ($unserialized_var !== false) {
                        // We're sure this is a serialized value, proceed it here
                        unset($columns[$column]);
                        // Actually replace string in all available strin array and properties
                        $unserialized_var = $this->replaceStringRecursive($unserialized_var, $search_url, $replace_url);
                        // Serialize it back
                        $serialized_var = serialize($unserialized_var);
                        // Update the database with new serialized value
                        $nb_rows = $wpdb->query($wpdb->prepare(
                            'UPDATE ' . esc_sql($table) . ' SET ' . esc_sql($column) . '=%s WHERE ' . esc_sql($key) . '=%s AND meta_key NOT IN("_wp_attached_file", "_wp_attachment_metadata")',
                            array($serialized_var, $result[0])
                        ));
                    }
                }
            }
        }

        if (count($columns)) {
            $columns_query = array();

            foreach ($columns as $column => $column_value) {
                // Relative urls
                $columns_query[] = '`' . $column . '` = replace(`' . esc_sql($column) . '`, "' . esc_sql($search_url) . '", "' . esc_sql($replace_url) . '")';
            }

            $query = 'UPDATE `' . esc_sql($table) . '` SET ' . implode(',', $columns_query);

            // Ignore attachments meta column
            if ($table === $wpdb->prefix . 'postmeta') {
                $query .= ' WHERE meta_key NOT IN("_wp_attached_file", "_wp_attachment_metadata")';
            }

            // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Query escaped previously
            $wpdb->query($query);
        }
        return true;
    }

    /**
     * Onedrive settings html
     *
     * @param string $html HTML
     *
     * @return string
     */
    public function renderSettings($html)
    {
        $connect    = false;
        $s3_percent = $this->getS3CompletePercent();
        $allow_syncs3_extensions = wpmfGetOption('allow_syncs3_extensions');
        try {
            $aws3 = new WpmfAddonAWS3();
            if (isset($_POST['btn_wpmf_save'])) {
                if (empty($_POST['wpmf_nonce'])
                    || !wp_verify_nonce($_POST['wpmf_nonce'], 'wpmf_nonce')) {
                    die();
                }
                if (!empty($_POST['aws3_config'])) {
                    $oldConfigs = get_option('_wpmfAddon_aws3_config');
                    if (empty($oldConfigs)) {
                        $oldConfigs = array();
                    }

                    $requestConfigs = $_POST['aws3_config'];
                    $newConfigs = array_merge($oldConfigs, $requestConfigs);
                    update_option('_wpmfAddon_aws3_config', $newConfigs);
                }
                $aws3 = new WpmfAddonAWS3();
            }

            $aws3config = get_option('_wpmfAddon_aws3_config');
            if (is_array($aws3config)) {
                $aws3config = array_merge($this->aws3_config_default, $aws3config);
            } else {
                $aws3config = $this->aws3_config_default;
            }

            $copy_files_to_bucket     = $aws3config['copy_files_to_bucket'];
            $remove_files_from_server = $aws3config['remove_files_from_server'];
            $attachment_label         = $aws3config['attachment_label'];
            // get all buckets
            if (!empty($aws3config['credentials']['key']) && !empty($aws3config['credentials']['secret'])) {
                $list_buckets = $aws3->listBuckets();
                if (!empty($aws3config['bucket'])) {
                    $location_name = $aws3->regions[$aws3config['region']];
                }

                $connect = true;
            }
        } catch (S3Exception $e) {
            $connect = false;
            $msg     = $e->getAwsErrorMessage();
        }

        ob_start();
        require_once 'templates/settings_aws3.php';
        $html = ob_get_contents();
        ob_end_clean();
        return $html;
    }

    /**
     * Add the S3 meta box to the attachment screen
     *
     * @return void
     */
    public function attachmentMetaBox()
    {
        add_meta_box(
            's3-actions',
            __('Amazon Infos', 'wpmfAddon'),
            array($this, 'metaBox'),
            'attachment',
            'side',
            'core'
        );
    }

    /**
     * Render the S3 attachment meta box
     *
     * @return void
     */
    public function metaBox()
    {
        require_once 'templates/attachment-metabox.php';
    }

    /**
     * Upload attachment to s3
     *
     * @param object  $aws3    S3 class object
     * @param integer $post_id Attachment ID
     * @param array   $data    Attachment meta data
     *
     * @return array
     */
    public function doUploadToS3($aws3, $post_id, $data)
    {
        $parent_path = $this->getFolderS3Path($post_id);
        $file_paths = $this->getAttachmentFilePaths($post_id, $data);
        $infos = get_post_meta($post_id, 'wpmf_awsS3_info', true);
        if (!empty($infos)) {
            foreach ($file_paths as $size => $file_path) {
                if (!file_exists($file_path)) {
                    continue;
                }

                try {
                    $aws3->uploadObject(
                        array(
                            'ACL'          => 'public-read',
                            'Bucket'       => $this->aws3_settings['bucket'],
                            'Key'          => $parent_path . basename($file_path),
                            'SourceFile'   => $file_path,
                            'ContentType'  => get_post_mime_type($post_id),
                            'CacheControl' => 'max-age=31536000',
                            'Expires'      => date('D, d M Y H:i:s O', time() + 31536000),
                            'Metadata'     => array(
                                'attachment_id' => $post_id,
                                'size'          => $size
                            )
                        )
                    );
                } catch (Exception $e) {
                    $res = array('status' => false, 'msg' => esc_html($e->getMessage()));
                    return $res;
                }
            }
        }

        $res = array('status' => true);
        return $res;
    }

    /**
     * Add a file to the queue
     *
     * @param integer $post_id     Attachment id
     * @param string  $destination Destination
     * @param string  $status      Status
     *
     * @return void
     */
    public function addToQueue($post_id, $destination, $status = 0)
    {
        global $wpdb;
        $check = $wpdb->get_results($wpdb->prepare('SELECT * FROM ' . $wpdb->prefix . 'wpmf_s3_queue WHERE post_id=%d', array($post_id)));
        if (empty($check)) {
            $wpdb->insert(
                $wpdb->prefix . 'wpmf_s3_queue',
                array(
                    'post_id'     => $post_id,
                    'date_added'  => round(microtime(true) * 1000),
                    'destination' => $this->encodeFilename($destination),
                    'date_done'   => null,
                    'status'      => $status
                ),
                array(
                    '%d',
                    '%d',
                    '%s',
                    '%d',
                    '%d'
                )
            );
        }
    }

    /**
     * Update attachment metadata
     *
     * @param array   $data    Meta data
     * @param integer $post_id Attachment ID
     *
     * @return array
     */
    public function wpUpdateAttachmentMetadata($data, $post_id)
    {
        if (is_null($data)) {
            $data = wp_get_attachment_metadata($post_id, true);
        }

        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- No action, nonce is not required
        if (!empty($_POST['wpmf_folder'])) {
            $folder_id = (int) $_POST['wpmf_folder'];
            $cloud_id = wpmfGetCloudFolderID($folder_id);
            if ($cloud_id) {
                return $data;
            }
        }

        $infos      = get_post_meta($post_id, 'wpmf_awsS3_info', true);
        if (empty($infos)) {
            return $data;
        }

        $aws3 = new WpmfAddonAWS3();
        $return = $this->doUploadToS3($aws3, $post_id, $data);
        $configs = get_option('_wpmfAddon_aws3_config');
        if (!empty($configs['remove_files_from_server'])) {
            $this->doRemoveLocalFile($post_id);
        }
        return $data;
    }

    /**
     * Add attachment to cloud
     *
     * @param integer $attachment_id Attachment ID
     *
     * @return void
     */
    public function addAttachment($attachment_id)
    {
        $path = get_attached_file($attachment_id);
        if (file_exists($path)) {
            // phpcs:ignore WordPress.Security.NonceVerification.Missing -- No action, nonce is not required
            if (!empty($_POST['wpmf_folder'])) {
                // phpcs:ignore WordPress.Security.NonceVerification.Missing -- No action, nonce is not required
                $folder_id = (int)$_POST['wpmf_folder'];
                $cloud_id = wpmfGetCloudFolderID($folder_id);
                if (!$cloud_id) {
                    if (!empty($this->aws3_settings['bucket'])) {
                        $this->addMetaInfo($attachment_id, 1);
                    }
                }
            } else {
                if (!empty($this->aws3_settings['bucket'])) {
                    $this->addMetaInfo($attachment_id, 1);
                }
            }
        }
    }

    /**
     * Add meta info
     *
     * @param integer $attachment_id Attachment ID
     * @param integer $status        Status
     *
     * @return void
     */
    public function addMetaInfo($attachment_id, $status = 0)
    {
        $parent_path = $this->getFolderS3Path($attachment_id);
        $file_path = get_attached_file($attachment_id);
        update_post_meta($attachment_id, 'wpmf_awsS3_info', array(
            'Acl'    => 'public-read',
            'Region' => $this->aws3_settings['region'],
            'Bucket' => $this->aws3_settings['bucket'],
            'Key'    => $parent_path . basename($file_path)
        ));

        $destination = $this->getDestination($attachment_id);
        if ($destination) {
            $this->addToQueue($attachment_id, $destination, $status);
        }
    }

    /**
     * Get all text assimilated columns from database
     *
     * @param boolean $all Retrive only prefix tables or not
     *
     * @return array|null|object
     */
    public function getDbColumns($all)
    {
        global $wpdb;
        $extra_query = '';

        // Not forced to retrieve all tables
        if (!$all) {
            $extra_query = ' AND TABLE_NAME LIKE "' . $wpdb->prefix . '%" ';
        }

        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Nothing to prepare
        return $wpdb->get_results('SELECT TABLE_NAME, COLUMN_NAME, COLUMN_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE DATA_TYPE IN ("varchar", "text", "tinytext", "mediumtext", "longtext") AND TABLE_SCHEMA = "' . DB_NAME . '" ' . $extra_query . ' ORDER BY TABLE_NAME', OBJECT);
    }


    /**
     * Get the columns that can contain images
     *
     * @return array
     */
    public function getDefaultDbColumns()
    {
        global $wpdb;
        $columns = $this->getDbColumns(false);
        $final_columns = array();

        $exclude_tables = array(
            $wpdb->prefix . 'users',
            $wpdb->prefix . 'term_taxonomy',
            $wpdb->prefix . 'term_relationships',
            $wpdb->prefix . 'terms',
            $wpdb->prefix . 'wpmf_s3_queue',
            $wpdb->prefix . 'cmplz_cookiebanners',
            $wpdb->prefix . 'cmplz_cookies',
            $wpdb->prefix . 'cmplz_services',
            $wpdb->prefix . 'cmplz_statistics',
            $wpdb->prefix . 'easy_pie_contacts',
            $wpdb->prefix . 'easy_pie_cs_subscribers',
            $wpdb->prefix . 'easy_pie_emails',
            $wpdb->prefix . 'newsletter',
            $wpdb->prefix . 'newsletter_sent',
            $wpdb->prefix . 'newsletter_stats',
            $wpdb->prefix . 'newsletter_user_logs',
            $wpdb->prefix . 'duplicator_pro_entities',
            $wpdb->prefix . 'duplicator_pro_packages',
            $wpdb->prefix . 'icl_content_status',
            $wpdb->prefix . 'icl_core_status',
            $wpdb->prefix . 'icl_flags',
            $wpdb->prefix . 'icl_languages',
            $wpdb->prefix . 'icl_languages_translations',
            $wpdb->prefix . 'icl_locale_map',
            $wpdb->prefix . 'icl_message_status',
            $wpdb->prefix . 'icl_node',
            $wpdb->prefix . 'icl_reminders',
            $wpdb->prefix . 'icl_string_positions',
            $wpdb->prefix . 'icl_string_status',
            $wpdb->prefix . 'icl_string_translations',
            $wpdb->prefix . 'icl_translate',
            $wpdb->prefix . 'icl_translate_job',
            $wpdb->prefix . 'icl_translate',
            $wpdb->prefix . 'icl_translation_status',
            $wpdb->prefix . 'icl_translate_job',
            $wpdb->prefix . 'yoast_seo_meta',
            $wpdb->prefix . 'yoast_migrations',
            $wpdb->prefix . 'yoast_primary_term',
            $wpdb->prefix . 'wpmf_queue',
            $wpdb->prefix . 'wpfd_queue'
        );
        foreach ($columns as $column) {
            if (in_array($column->TABLE_NAME, $exclude_tables)) {
                continue;
            }

            if (strpos($column->TABLE_NAME, 'woocommerce') !== false || strpos($column->TABLE_NAME, 'wptm') !== false) {
                continue;
            }
            $matches = array();
            preg_match('/varchar\(([0-9]+)\)/', $column->COLUMN_TYPE, $matches);

            if (count($matches) && (int) $matches[1] < 40) {
                continue;
            }

            if (!isset($final_columns[$column->TABLE_NAME])) {
                $final_columns[$column->TABLE_NAME] = array();
            }

            if ($column->TABLE_NAME === $wpdb->posts) {
                if (in_array($column->COLUMN_NAME, array('post_mime_type', 'pinged', 'to_ping', 'post_password', 'post_title', 'post_name'))) {
                    continue;
                }
            }

            if ($column->TABLE_NAME === $wpdb->postmeta) {
                if (in_array($column->COLUMN_NAME, array('meta_key'))) {
                    continue;
                }
            }

            if ($column->TABLE_NAME === $wpdb->termmeta) {
                if (in_array($column->COLUMN_NAME, array('meta_key'))) {
                    continue;
                }
            }

            if ($column->TABLE_NAME === $wpdb->options) {
                if (in_array($column->COLUMN_NAME, array('option_name'))) {
                    continue;
                }
            }

            if ($column->TABLE_NAME === $wpdb->usermeta) {
                if (in_array($column->COLUMN_NAME, array('meta_key'))) {
                    continue;
                }
            }

            if ($column->TABLE_NAME === $wpdb->commentmeta) {
                if (in_array($column->COLUMN_NAME, array('meta_key'))) {
                    continue;
                }
            }

            if ($column->TABLE_NAME === $wpdb->comments) {
                if (in_array($column->COLUMN_NAME, array('comment_author', 'comment_author_email', 'comment_author_url', 'comment_author_IP', 'comment_agent'))) {
                    continue;
                }
            }

            if ($column->TABLE_NAME === $wpdb->links) {
                if (in_array($column->COLUMN_NAME, array('link_rel', 'link_rss', 'link_name'))) {
                    continue;
                }
            }

            $final_columns[$column->TABLE_NAME][$column->COLUMN_NAME] = 1;
        }

        return $final_columns;
    }

    /**
     * Update File Size
     *
     * @param integer $post_id Attachment ID
     *
     * @return void
     */
    public function updateFileSize($post_id)
    {
        $meta      = get_post_meta($post_id, '_wp_attachment_metadata', true);
        $file_path = get_attached_file($post_id, true);
        if (file_exists($file_path)) {
            $filesize  = filesize($file_path);
            if ($filesize > 0) {
                $meta['filesize'] = $filesize;
                update_post_meta($post_id, '_wp_attachment_metadata', $meta);
            }
        }
    }

    /**
     * Remove local file by ID
     *
     * @param integer $id ID of file
     *
     * @return void
     */
    public function doRemoveLocalFile($id)
    {
        $configs = get_option('_wpmfAddon_aws3_config');
        if (empty($configs['remove_files_from_server'])) {
            return;
        }
        // update file size
        $this->updateFileSize($id);
        $meta       = get_post_meta($id, '_wp_attachment_metadata', true);
        $file_paths = $this->getAttachmentFilePaths($id, $meta);
        foreach ($file_paths as $size => $file_path) {
            if (!file_exists($file_path)) {
                continue;
            }

            if (!is_writable($file_path)) {
                continue;
            }

            // phpcs:ignore Generic.PHP.NoSilencedErrors.Discouraged -- fix warning when not have permission unlink
            @unlink($file_path);
        }
    }

    /**
     * Recursively parse a variable to replace a string
     *
     * @param mixed  $var     Variable to replace string into
     * @param string $search  String to search
     * @param string $replace String to replace with
     *
     * @return mixed
     */
    public function replaceStringRecursive($var, $search, $replace)
    {
        switch (gettype($var)) {
            case 'string':
                return str_replace($search, $replace, $var);

            case 'array':
                foreach ($var as &$property) {
                    $property = self::replaceStringRecursive($property, $search, $replace);
                }
                return $var;

            case 'object':
                foreach (get_object_vars($var) as $property_name => $property_value) {
                    $var->{$property_name} = self::replaceStringRecursive($property_value, $search, $replace);
                }
                return $var;
        }
        return '';
    }

    /**
     * Delete Attachment
     *
     * @param integer $post_id Attachment ID
     *
     * @return void
     */
    public function deleteAttachment($post_id)
    {
        $infos = get_post_meta($post_id, 'wpmf_awsS3_info', true);
        global $wpdb;
        // delete in wpmf_s3_queue table
        $wpdb->delete($wpdb->prefix . 'wpmf_s3_queue', array('post_id' => $post_id), array('%d'));
        if (!empty($infos)) {
            try {
                set_time_limit(0);
                // delete on s3 server
                $aws3       = new WpmfAddonAWS3();
                $file_paths = $this->getAttachmentFilePaths($post_id);
                foreach ($file_paths as $size => $file_path) {
                    $aws3->deleteObject(
                        array(
                            'Bucket' => $infos['Bucket'],
                            'Key'    => dirname($infos['Key']) . '/' . basename($file_path)
                        )
                    );
                }
            } catch (S3Exception $e) {
                echo esc_html($e->getAwsErrorMessage());
            }
        }
    }

    /**
     * Get file paths for all attachment versions.
     *
     * @param integer       $attachment_id Attachment ID
     * @param array|boolean $meta          Meta data
     *
     * @return array
     */
    public function getAttachmentFilePaths($attachment_id, $meta = false)
    {
        $file_path = get_attached_file($attachment_id, true);
        $paths     = array(
            'original' => $file_path,
        );

        if (empty($meta)) {
            $meta = get_post_meta($attachment_id, '_wp_attachment_metadata', true);
        }

        if (is_wp_error($meta)) {
            return $paths;
        }

        // Get file name of original path
        $file_name = wp_basename($file_path);
        $full_urls = wp_get_attachment_image_src($attachment_id, 'full');
        $full_url = $full_urls[0];
        $file_name_of_full = wp_basename($full_url);
        $file_name_of_full = str_replace('-scaled', '', $file_name_of_full);
        if ($file_name !== $file_name_of_full) {
            $paths['full'] = str_replace($file_name, $file_name_of_full, $file_path);
        }

        // If file edited, current file name might be different.
        if (isset($meta['file'])) {
            $paths['file'] = str_replace($file_name, wp_basename($meta['file']), $file_path);
        }

        // Sizes
        if (isset($meta['sizes'])) {
            foreach ($meta['sizes'] as $size => $file) {
                if (isset($file['file'])) {
                    $paths[$size] = str_replace($file_name, $file['file'], $file_path);
                }
            }
        }

        // Get backup size
        $backups = get_post_meta($attachment_id, '_wp_attachment_backup_sizes', true);
        if (is_array($backups)) {
            foreach ($backups as $size => $file) {
                if (isset($file['file'])) {
                    $paths[$size] = str_replace($file_name, $file['file'], $file_path);
                }
            }
        }

        // Remove duplicates
        $paths = array_unique($paths);
        return $paths;
    }

    /**
     * Get folder breadcrumb
     *
     * @param integer $post_id Attachment ID
     *
     * @return string
     */
    public function getFolderS3Path($post_id)
    {
        $attached  = get_attached_file($post_id);
        $attached  = str_replace('\\', '/', $attached);
        $attached  = str_replace(basename($attached), '', $attached);
        $home_path = str_replace('\\', '/', get_home_path());
        $path      = str_replace($home_path, '', $attached);
        $path      = str_replace('//', '', $path);
        $configs = get_option('_wpmfAddon_aws3_config');
        $root_folder_name = (isset($configs['root_folder_name'])) ? $configs['root_folder_name'] : 'wp-media-folder-' . sanitize_title(get_bloginfo('name'));
        return $root_folder_name . '/' . $path;
    }

    /**
     * Get folder breadcrumb
     *
     * @param integer $id     Folder id
     * @param integer $parent Folder parent
     * @param string  $string Current breadcrumb
     *
     * @return string
     */
    public function getCategoryDir($id, $parent, $string)
    {
        if (!empty($parent)) {
            $term   = get_term($parent, WPMF_TAXO);
            $string = $this->getCategoryDir($id, $term->parent, $term->name . '/' . $string);
        }

        return $string;
    }

    /**
     * Create a bucket
     *
     * @return void
     */
    public function createBucket()
    {
        if (empty($_POST['wpmf_nonce'])
            || !wp_verify_nonce($_POST['wpmf_nonce'], 'wpmf_nonce')) {
            die();
        }

        if (isset($_POST['name']) && $_POST['name'] !== '') {
            $name = $_POST['name'];
            $args = array('Bucket' => $name);
            if (isset($_POST['region'])) {
                $args['CreateBucketConfiguration'] = array('LocationConstraint' => $_POST['region']);
            }

            try {
                $aws3 = new WpmfAddonAWS3($_POST['region']);
                $aws3->createBucket($args);
                // select bucket after create
                $aws3config = get_option('_wpmfAddon_aws3_config');
                if (is_array($aws3config)) {
                    $aws3config['bucket'] = $name;
                    $aws3config['region'] = $_POST['region'];
                    update_option('_wpmfAddon_aws3_config', $aws3config);
                }
                $location_name = $aws3->regions[$_POST['region']];
                wp_send_json(array('status' => true, 'msg' => esc_html__('Created bucket success!', 'wpmfAddon'), 'location_name' => $location_name));
            } catch (S3Exception $e) {
                wp_send_json(array(
                    'status' => false,
                    'msg'    => esc_html($e->getAwsErrorMessage())
                ));
            }
        }
    }

    /**
     * Delete a bucket
     *
     * @return void
     */
    public function deleteBucket()
    {
        if (empty($_POST['wpmf_nonce'])
            || !wp_verify_nonce($_POST['wpmf_nonce'], 'wpmf_nonce')) {
            die();
        }

        if (isset($_POST['name']) && $_POST['name'] !== '') {
            $name = $_POST['name'];

            try {
                $aws3   = new WpmfAddonAWS3();
                $region = $aws3->getBucketLocation(
                    array('Bucket' => $name)
                );
                $args   = get_option('_wpmfAddon_aws3_config');
                if ($region !== $args['region']) {
                    $aws3 = new WpmfAddonAWS3($region);
                }

                $list_objects = $aws3->listObjects(array('Bucket' => $name));
                if (!empty($list_objects['Contents'])) {
                    foreach ($list_objects['Contents'] as $list_object) {
                        $aws3->deleteObject(array(
                            'Bucket' => $name,
                            'Key'    => $list_object['Key']
                        ));
                    }
                }

                $result = $aws3->deleteBucket(array(
                    'Bucket' => $name
                ));

                wp_send_json(array('status' => true));
            } catch (S3Exception $e) {
                wp_send_json(array('status' => false, 'msg' => esc_html($e->getAwsErrorMessage())));
            }
        }
        wp_send_json(array('status' => false, 'msg' => esc_html__('Delete failed!', 'wpmfAddon')));
    }

    /**
     * Select a bucket
     *
     * @return void
     */
    public function selectBucket()
    {
        if (empty($_POST['wpmf_nonce'])
            || !wp_verify_nonce($_POST['wpmf_nonce'], 'wpmf_nonce')) {
            die();
        }

        $public = false;
        $aws3       = new WpmfAddonAWS3();
        $region = $aws3->getBucketLocation(
            array('Bucket' => $_POST['bucket'])
        );
        $aws3       = new WpmfAddonAWS3($region);
        try {
            $res = $aws3->getPublicAccessBlock(array('Bucket' => $_POST['bucket']));
            if (!empty($res['PublicAccessBlockConfiguration'])) {
                if (!$res['PublicAccessBlockConfiguration']['BlockPublicAcls']
                    && !$res['PublicAccessBlockConfiguration']['IgnorePublicAcls']
                    && !$res['PublicAccessBlockConfiguration']['BlockPublicPolicy']
                    && !$res['PublicAccessBlockConfiguration']['RestrictPublicBuckets']) {
                    $public = true;
                }
            }
        } catch (S3Exception $e) {
            $public = true;
        }

        if ($public) {
            $aws3config = get_option('_wpmfAddon_aws3_config');
            if (is_array($aws3config)) {
                $aws3config['bucket'] = $_POST['bucket'];
                $aws3config['region'] = $region;
                update_option('_wpmfAddon_aws3_config', $aws3config);
                wp_send_json(array(
                    'status' => true,
                    'bucket' => $aws3config['bucket'],
                    'region' => $aws3->regions[$aws3config['region']]
                ));
            }
        }

        wp_send_json(array('status' => false, 'msg' => esc_html__('Select bucket failed!', 'wpmfAddon')));
    }

    /**
     * Get buckets list
     *
     * @return void
     */
    public function getBucketsList()
    {
        if (empty($_POST['wpmf_nonce'])
            || !wp_verify_nonce($_POST['wpmf_nonce'], 'wpmf_nonce')) {
            die();
        }

        $aws3         = new WpmfAddonAWS3();
        $list_buckets = $aws3->listBuckets();
        $aws3config   = get_option('_wpmfAddon_aws3_config');
        $html         = '';
        if (!empty($list_buckets['Buckets'])) {
            foreach ($list_buckets['Buckets'] as $bucket) {
                if (isset($aws3config['bucket']) && $aws3config['bucket'] === $bucket['Name']) {
                    $html .= '<tr class="row_bucket bucket-selected" data-bucket="' . esc_attr($bucket['Name']) . '">';
                } else {
                    $html .= '<tr class="row_bucket aws3-select-bucket" data-bucket="' . esc_attr($bucket['Name']) . '">';
                }

                $html .= '<td>' . esc_html($bucket['Name']) . '</td>';
                $html .= '<td>' . esc_html($bucket['CreationDate']) . '</td>';
                if (isset($aws3config['bucket']) && $aws3config['bucket'] === $bucket['Name']) {
                    $html .= '<td><label class="btn-select-bucket">' . esc_html__('Selected bucket', 'wpmfAddon') . '</label></td>';
                } else {
                    $html .= '<td><label class="btn-select-bucket">' . esc_html__('Select bucket', 'wpmfAddon') . '</label></td>';
                }
                $html .= '<td><a class="delete-bucket wpmfqtip" data-alt="' . esc_html__('Delete bucket', 'wpmfAddon') . '" data-bucket="' . esc_attr($bucket['Name']) . '"><i class="material-icons"> delete_outline </i></a></td>';
                $html .= '</tr>';
            }
        }

        wp_send_json(array('status' => true, 'html' => $html, 'buckets' => $list_buckets['Buckets']));
    }

    /**
     * Update S3 URL to local URL
     *
     * @param integer|boolean $result     Result
     * @param array           $datas      QUeue datas
     * @param integer         $element_id Queue ID
     *
     * @return boolean
     */
    public function replaceLocalUrlS3($result, $datas, $element_id)
    {
        if (isset($datas['attachment_id'])) {
            try {
                $file_paths = get_post_meta($datas['attachment_id'], 'wpmf_origin_file_paths', true);
                // get tables
                $tables = self::getDefaultDbColumns();
                foreach ($file_paths as $size => $file_path) {
                    $this->updateAttachmentUrlToDatabase((int)$datas['attachment_id'], $file_path, $datas['destination'], true, $tables);
                }

                // Update queue meta
                JUMainQueue::updateQueuePostMeta((int)$datas['attachment_id'], (int)$element_id);
                return true;
            } catch (Exception $e) {
                return false;
            }
        }
        return false;
    }

    /**
     * Download attachment from s3 s3
     *
     * @return void
     */
    public function downloadObject()
    {
        if (empty($_POST['wpmf_nonce'])
            || !wp_verify_nonce($_POST['wpmf_nonce'], 'wpmf_nonce')) {
            die();
        }

        /**
         * Filter check capability of current user to regenerate image thumbnail
         *
         * @param boolean The current user has the given capability
         * @param string  Action name
         *
         * @return boolean
         *
         * @ignore Hook already documented
         */
        $wpmf_capability = apply_filters('wpmf_user_can', current_user_can('manage_options'), 'download_object');
        if (!$wpmf_capability) {
            wp_send_json(array('status' => false, 'msg' => 'You not have permission!', 'wpmfAddon'));
        }

        set_time_limit(0);
        global $wpdb;
        $file = $wpdb->get_row('SELECT * FROM ' . $wpdb->prefix . 'wpmf_s3_queue WHERE status = 1 LIMIT 1');
        if ($file) {
            try {
                $aws3 = new WpmfAddonAWS3();
                $infos      = get_post_meta($file->post_id, 'wpmf_awsS3_info', true);
                if (empty($infos)) {
                    // delete this row
                    $this->updateStatusS3($file->id, 0);
                    wp_send_json(array('status'  => true, 'continue' => true));
                }

                $file_paths = $this->getAttachmentFilePaths($file->post_id);
                foreach ($file_paths as $file_path) {
                    if (file_exists($file_path)) {
                        continue;
                    }

                    $aws3->getObject(array(
                        'Bucket' => $infos['Bucket'],
                        'Key'    => dirname($infos['Key']) . '/' . basename($file_path),
                        'SaveAs' => $file_path
                    ));
                }

                // add to queue, use to replace URL in database
                $datas = array(
                    'attachment_id' => $file->post_id,
                    'action' => 'wpmf_s3_replace_urls3',
                    'destination' => $file->destination
                );

                $row = JUMainQueue::checkQueueExist(json_encode($datas));
                if (!$row) {
                    JUMainQueue::addToQueue($datas);
                }

                // delete meta info
                delete_post_meta($file->post_id, 'wpmf_awsS3_info');
                // update status queue
                $this->updateStatusS3($file->id, 0);
                $count = $wpdb->get_var('SELECT COUNT(id) FROM ' . $wpdb->prefix . 'wpmf_s3_queue WHERE status = 1 OR status = 0');
                $count1 = $wpdb->get_var('SELECT COUNT(id) FROM ' . $wpdb->prefix . 'wpmf_s3_queue WHERE status = 0');
                $percent          = ($count1 / $count) * 100;
                sleep(0.5);
                wp_send_json(array('status'  => true, 'continue' => true, 'percent' => $percent));
            } catch (S3Exception $e) {
                $this->updateStatusS3($file->id, 0);
                $count = $wpdb->get_var('SELECT COUNT(id) FROM ' . $wpdb->prefix . 'wpmf_s3_queue WHERE status = 1 OR status = 0');
                $count1 = $wpdb->get_var('SELECT COUNT(id) FROM ' . $wpdb->prefix . 'wpmf_s3_queue WHERE status = 0');
                $percent          = ($count1 / $count) * 100;
                sleep(0.5);
                wp_send_json(array('status'  => true, 'continue' => true, 'percent' => $percent));
            }
        } else {
            wp_send_json(array('status' => true, 'continue' => false));
        }
    }

    /**
     * Update status for file
     *
     * @param integer $fileID File ID
     * @param integer $status Status
     *
     * @return void
     */
    public function updateStatusS3($fileID, $status)
    {
        global $wpdb;
        $wpdb->update(
            $wpdb->prefix . 'wpmf_s3_queue',
            array(
                'status'    => $status
            ),
            array('id' => $fileID),
            array(
                '%d',
            ),
            array('%d')
        );
    }

    /**
     * Upload single file to S3
     *
     * @param integer $attachment_id Attachment ID
     * @param object  $aws3          WpmfAddonAWS3 class
     *
     * @return array
     */
    public function uploadSingleFileToS3($attachment_id, $aws3)
    {
        $data = wp_get_attachment_metadata($attachment_id, true);
        // do upload to s3
        $this->addMetaInfo($attachment_id);
        $return = $this->doUploadToS3($aws3, $attachment_id, $data);
        if (isset($return['status']) && $return['status']) {
            global $wpdb;
            // update status s3 queue
            $wpdb->update(
                $wpdb->prefix . 'wpmf_s3_queue',
                array(
                    'status'    => 1,
                    'date_done' => round(microtime(true) * 1000)
                ),
                array('post_id' => $attachment_id),
                array(
                    '%d',
                    '%d'
                ),
                array('%d')
            );

            // store origin file paths to meta
            $meta       = get_post_meta($attachment_id, '_wp_attachment_metadata', true);
            $file_paths = $this->getAttachmentFilePaths($attachment_id, $meta);
            update_post_meta($attachment_id, 'wpmf_origin_file_paths', $file_paths);

            // get destination
            $destination = $this->getDestination($attachment_id);
            // add to queue, use to replace URL in database
            $datas = array(
                'attachment_id' => $attachment_id,
                'action' => 'wpmf_s3_replace_local',
                'destination' => $destination
            );

            $row = JUMainQueue::checkQueueExist(json_encode($datas));
            if (!$row) {
                JUMainQueue::addToQueue($datas);
            }
            // remove local file
            $this->doRemoveLocalFile($attachment_id);
        }
        return $return;
    }

    /**
     * Get destination
     *
     * @param integer $attachment_id Attachment ID
     *
     * @return boolean|string
     */
    public function getDestination($attachment_id)
    {
        $destination = false;
        $infos = get_post_meta($attachment_id, 'wpmf_awsS3_info', true);
        $configs = get_option('_wpmfAddon_aws3_config');
        $root_folder_name = (isset($configs['root_folder_name'])) ? $configs['root_folder_name'] : 'wp-media-folder-' . sanitize_title(get_bloginfo('name'));
        if (!empty($infos)) {
            if (isset($infos['Region']) && $infos['Region'] !== 'us-east-1') {
                $destination = 'https://s3-' . $infos['Region'] . '.amazonaws.com/' . $infos['Bucket'] . '/' . $root_folder_name;
            } else {
                $destination = 'https://s3.amazonaws.com/' . $infos['Bucket'] . '/'. $root_folder_name;
            }
        }

        return $destination;
    }

    /**
     * Sync media library with s3
     *
     * @return void
     */
    public function uploadToS3()
    {
        if (empty($_POST['wpmf_nonce'])
            || !wp_verify_nonce($_POST['wpmf_nonce'], 'wpmf_nonce')) {
            die();
        }

        /**
         * Filter check capability of current user to regenerate image thumbnail
         *
         * @param boolean The current user has the given capability
         * @param string  Action name
         *
         * @return boolean
         *
         * @ignore Hook already documented
         */
        $wpmf_capability = apply_filters('wpmf_user_can', current_user_can('manage_options'), 'upload_to_s3');
        if (!$wpmf_capability) {
            wp_send_json(
                array(
                    'status' => false,
                    'msg'    => esc_html__('Permission defined!', 'wpmfAddon')
                )
            );
        }

        $aws3config = get_option('_wpmfAddon_aws3_config');
        if (empty($aws3config['copy_files_to_bucket'])) {
            wp_send_json(
                array(
                    'status' => false,
                    'msg'    => esc_html__('Please enable (Copy to Amazon S3) option', 'wpmfAddon')
                )
            );
        }

        if (empty($aws3config['bucket'])) {
            wp_send_json(
                array(
                    'status' => false,
                    'msg'    => esc_html__('Please select an Amazon bucket to start using S3 server', 'wpmfAddon')
                )
            );
        }

        set_time_limit(0);
        $query = new WP_Query(array(
            'posts_per_page' => 1,
            'post_type' => 'attachment',
            'post_status' => 'any',
            'orderby' => 'ID',
            'order' => 'DESC',
            'meta_query' => array(
                'relation' => 'AND',
                array(
                    'key'     => 'wpmf_drive_id',
                    'compare' => 'NOT EXISTS'
                ),
                array(
                    'key'     => 'wpmf_awsS3_info',
                    'compare' => 'NOT EXISTS'
                )
            )
        ));

        $attachments = $query->get_posts();
        $count = count($attachments);
        // return if empty local file
        if ($count === 0) {
            wp_send_json(array(
                'status'                   => true,
                'continue' => false,
                's3_percent'               => 100
            ));
        }

        try {
            $aws3       = new WpmfAddonAWS3();
            $s3_percent = $this->getS3CompletePercent();
            $allow_exts = wpmfGetOption('allow_syncs3_extensions');
            $allow_exts_array = explode(',', trim($allow_exts));
            foreach ($attachments as $attachment) {
                $file_url = wp_get_attachment_url($attachment->ID);
                $filetype = wp_check_filetype($file_url);
                if (in_array($filetype['ext'], $allow_exts_array) || in_array(strtolower($filetype['ext']), $allow_exts_array)) {
                    $return = $this->uploadSingleFileToS3($attachment->ID, $aws3);
                }
            }

            $process_percent = 0;
            if (isset($_POST['local_files_count'])) {
                $process_percent = (1 / (int) $_POST['local_files_count']) * 100;
            }

            wp_send_json(array(
                'status'                   => true,
                'continue' => true,
                'percent'               => $process_percent,
                's3_percent' => $s3_percent['s3_percent']
            ));
        } catch (S3Exception $e) {
            wp_send_json(
                array(
                    'status' => false,
                    'msg'    => esc_html($e->getAwsErrorMessage())
                )
            );
        }
    }

    /**
     * Update local URL to S3 URL
     *
     * @param integer|boolean $result     Result
     * @param array           $datas      QUeue datas
     * @param integer         $element_id Queue ID
     *
     * @return boolean
     */
    public function replaceLocalUrl($result, $datas, $element_id)
    {
        // update database
        if (isset($datas['attachment_id'])) {
            try {
                $file_paths = get_post_meta($datas['attachment_id'], 'wpmf_origin_file_paths', true);
                // get tables
                $tables = self::getDefaultDbColumns();
                foreach ($file_paths as $size => $file_path) {
                    $this->updateAttachmentUrlToDatabase($datas['attachment_id'], $file_path, $datas['destination'], false, $tables);
                }
                // Update queue meta
                JUMainQueue::updateQueuePostMeta((int)$datas['attachment_id'], (int)$element_id);
                return true;
            } catch (Exception $e) {
                return false;
            }
        }
        return false;
    }

    /**
     * Encode file names according to RFC 3986 when generating urls
     *
     * @param string $file File name
     *
     * @return string Encoded filename
     */
    public function encodeFilename($file)
    {
        if (!is_admin()) {
            return $file;
        }

        $url = parse_url($file);

        if (!isset($url['path'])) {
            // Can't determine path, return original
            return $file;
        }

        $file = str_replace(' ', '+', $file);
        if (isset($url['query'])) {
            // Manually strip query string, as passing $url['path'] to basename results in corrupt characters
            $file_name = wp_basename(str_replace('?' . $url['query'], '', $file));
        } else {
            $file_name = wp_basename($file);
        }

        if (false !== strpos($file_name, '%')) {
            // File name already encoded, return original
            return $file;
        }

        $encoded_file_name = rawurlencode($file_name);
        if ($file_name === $encoded_file_name) {
            // File name doesn't need encoding, return original
            return $file;
        }

        return str_replace($file_name, $encoded_file_name, $file);
    }

    /**
     * Get attachment URL
     *
     * @param string  $url     Old URL
     * @param integer $post_id Attachment ID
     *
     * @return string
     */
    public function wpGetAttachmentUrl($url, $post_id)
    {
        $infos = get_post_meta($post_id, 'wpmf_awsS3_info', true);
        if (!empty($infos)) {
            $aws3config = get_option('_wpmfAddon_aws3_config');
            if (!empty($aws3config['enable_custom_domain']) && !empty($aws3config['custom_domain'])) {
                return 'https://' . $aws3config['custom_domain'] . '/' . $infos['Key'];
            } else {
                if (isset($infos['Region']) && $infos['Region'] !== 'us-east-1') {
                    return 'https://s3-' . $infos['Region'] . '.amazonaws.com/' . $infos['Bucket'] . '/' . str_replace(' ', '%20', $infos['Key']);
                } else {
                    return 'https://s3.amazonaws.com/' . $infos['Bucket'] . '/' . str_replace(' ', '%20', $infos['Key']);
                }
            }
        }

        return $url;
    }

    /**
     * Get attachment path
     *
     * @param string  $file          Attachment path
     * @param integer $attachment_id Attachment ID
     *
     * @return string
     */
    public function getAttachedFile($file, $attachment_id)
    {
        $infos = get_post_meta($attachment_id, 'wpmf_awsS3_info', true);
        if (file_exists($file) || empty($infos)) {
            return $file;
        }

        $url = wp_get_attachment_url($attachment_id);
        // return the URL by default
        $file = apply_filters('wpmf_get_attached_file', $url, $file, $attachment_id);
        return $file;
    }

    /**
     * Download attachment from s3 to server when regenerate thumbnail
     *
     * @param string  $url           Attachment URL
     * @param string  $file          Attachment path
     * @param integer $attachment_id Attachment ID
     *
     * @return mixed
     */
    public function regenerateThumbnails($url, $file, $attachment_id)
    {
        if (!$this->processAction(array(
            'wpmf_regeneratethumbnail',
            'regeneratethumbnail',
            'wpmf_duplicate_file'
        ), true)) {
            return $url;
        }

        // download attachment from s3 to server
        $infos = get_post_meta($attachment_id, 'wpmf_awsS3_info', true);
        $file  = $this->downloadAttachment($infos, $file);
        if ($file) {
            // Return the file if successfully downloaded from S3
            return $file;
        };

        return $url;
    }

    /**
     * Download image crop when open crop modal
     *
     * @param string  $url           Attachment URL
     * @param string  $file          Attachment path
     * @param integer $attachment_id Attachment ID
     *
     * @return string
     */
    public function imageEditorDownloadFile($url, $file, $attachment_id)
    {
        if (!$this->isAjax()) {
            return $url;
        }

        // restores image
        $infos = get_post_meta($attachment_id, 'wpmf_awsS3_info', true);
        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- No action, nonce is not required
        if (isset($_POST['do']) && $_POST['do'] === 'restore') {
            $backup_sizes      = get_post_meta($attachment_id, '_wp_attachment_backup_sizes', true);
            $filename          = $backup_sizes['full-orig']['file'];
            $orig_infos        = $infos;
            $orig_infos['Key'] = dirname($infos['Key']) . '/' . $filename;
            $orig_file         = dirname($file) . '/' . $filename;

            // Copy the original file back to the server
            $this->downloadAttachment($orig_infos, $orig_file);

            // Download attachment from s3
            $new_file = $this->downloadAttachment($infos, $file);
            if ($new_file) {
                return $new_file;
            };
        }

        $action = filter_input(INPUT_GET, 'action') ?: filter_input(INPUT_POST, 'action');
        if (in_array($action, array('image-editor', 'imgedit-preview'))) {
            global $wpdb;
            // phpcs:ignore PHPCompatibility.FunctionUse.ArgumentFunctionsReportCurrentValue.NeedsInspection, WordPress.PHP.DevelopmentFunctions.error_log_debug_backtrace -- Get function loader
            foreach (debug_backtrace() as $fun) {
                if (isset($fun['function']) && $fun['function'] === '_load_image_to_edit_path') {
                    // Download attachment from s3
                    $new_file = $this->downloadAttachment($infos, $file);
                }
            }
        }

        return $url;
    }

    /**
     * Check crop action
     *
     * @return boolean
     */
    public function isCrop()
    {
        $head_crop = $this->processAction(array('custom-header-crop'), true);
        $img_crop  = $this->processAction(array('crop-image'), true, array('site-icon', 'custom_logo'));
        if (!$head_crop && !$img_crop) {
            return false;
        }

        return true;
    }

    /**
     * Download attachment from s3 to server when crop image
     *
     * @param string  $url           Attachment URL
     * @param string  $file          Attachment path
     * @param integer $attachment_id Attachment ID
     *
     * @return mixed
     */
    public function cropImage($url, $file, $attachment_id)
    {
        if (!$this->isCrop()) {
            return $url;
        }

        // download attachment from s3 to server
        $infos = get_post_meta($attachment_id, 'wpmf_awsS3_info', true);
        $file  = $this->downloadAttachment($infos, $file);
        if ($file) {
            return $file;
        };

        return $url;
    }

    /**
     * Download attachment from S3
     *
     * @param array  $infos Attachment s3 infos
     * @param string $file  Attachment path
     *
     * @return boolean
     */
    public function downloadAttachment($infos, $file)
    {
        $dir = dirname($file);
        if (!wp_mkdir_p($dir)) {
            return false;
        }

        try {
            $aws3 = new WpmfAddonAWS3();
            $aws3->getObject(array(
                'Bucket' => $infos['Bucket'],
                'Key'    => $infos['Key'],
                'SaveAs' => $file,
            ));
        } catch (S3Exception $e) {
            return false;
        }

        return $file;
    }

    /**
     * Check the current request
     *
     * @param array             $actions Actions list
     * @param boolean           $ajax    Is ajax
     * @param null|string|array $key     Context key
     *
     * @return boolean
     */
    public function processAction($actions, $ajax, $key = null)
    {
        if ($ajax !== $this->isAjax()) {
            return false;
        }

        $method = 'GET';
        // phpcs:disable WordPress.Security.NonceVerification.Recommended, WordPress.Security.NonceVerification.Missing -- No action, nonce is not required
        if (isset($_GET['action'])) {
            $action = $this->filterInput('action');
        } elseif (isset($_POST['action'])) {
            $method = 'POST';
            $action = $this->filterInput('action', INPUT_POST);
        } else {
            return false;
        }
        // phpcs:enable
        $check = true;
        if (!is_null($key)) {
            $global  = constant('INPUT_' . $method);
            $context = $this->filterInput('context', $global);

            if (is_array($key)) {
                $check = in_array($context, $key);
            } else {
                $check = ($key === $context);
            }
        }

        return (in_array(sanitize_key($action), $actions) && $check);
    }

    /**
     * Gets a specific external variable by name and optionally filters it
     *
     * @param string  $var     Variable Name
     * @param integer $type    Variable type
     * @param integer $filter  Filter
     * @param mixed   $options Options
     *
     * @return mixed
     */
    public function filterInput($var, $type = INPUT_GET, $filter = FILTER_DEFAULT, $options = array())
    {
        return filter_input($type, $var, $filter, $options);
    }

    /**
     * Is this an AJAX
     *
     * @return boolean
     */
    public function isAjax()
    {
        if (defined('DOING_AJAX') && DOING_AJAX) {
            return true;
        }

        return false;
    }

    /**
     * Get attachment path from S3
     *
     * @param string  $url           Attachment URL
     * @param string  $file          Attachment path
     * @param integer $attachment_id Attachment ID
     *
     * @return string
     */
    public function getAttachedS3File($url, $file, $attachment_id)
    {
        if ($url === $file) {
            return $file;
        }

        $infos = get_post_meta($attachment_id, 'wpmf_awsS3_info', true);
        if (!empty($infos)) {
            $s3Url = 's3';
            $s3Url .= str_replace('-', '', $infos['Region']);
            return $s3Url . '://' . $infos['Bucket'] . '/' . $infos['Key'];
        }

        return $url;
    }
}
