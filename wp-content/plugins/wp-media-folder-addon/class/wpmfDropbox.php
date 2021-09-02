<?php
/* Prohibit direct script loading */
defined('ABSPATH') || die('No direct script access allowed!');

use Joomunited\WPMF\JUMainQueue;

/**
 * Class WpmfAddonDropbox
 * This class that holds most of the admin functionality for Dropbox
 */
class WpmfAddonDropbox
{
    /**
     * Params
     *
     * @var object
     */
    protected $params;

    /**
     * App name
     *
     * @var string
     */
    protected $appName = 'WpmfAddon/1.0';

    /**
     * Last Error
     *
     * @var string
     */
    protected $lastError;

    /**
     * WpmfAddonDropbox constructor.
     */
    public function __construct()
    {
        set_include_path(__DIR__ . PATH_SEPARATOR . get_include_path());
        require_once 'Dropbox/autoload.php';
        $this->loadParams();
    }

    /**
     * Get last error
     *
     * @return mixed
     */
    public function getLastError()
    {
        return $this->lastError;
    }

    /**
     * Get dropbox config by name
     *
     * @param string $name Name of option
     *
     * @return array|null
     */
    public function getDataConfigByDropbox($name)
    {
        return WpmfAddonHelper::getDataConfigByDropbox($name);
    }

    /**
     * Get dropbox config
     *
     * @return mixed
     */
    public function getAllDropboxConfigs()
    {
        return WpmfAddonHelper::getAllDropboxConfigs();
    }

    /**
     * Save dropbox config
     *
     * @param array $data Datas value
     *
     * @return boolean
     */
    public function saveDropboxConfigs($data)
    {
        return WpmfAddonHelper::saveDropboxConfigs($data);
    }

    /**
     * Load parameters
     *
     * @return void
     */
    protected function loadParams()
    {
        $params = $this->getDataConfigByDropbox('dropbox');

        $this->params = new stdClass();

        $this->params->dropboxKey    = isset($params['dropboxKey']) ? $params['dropboxKey'] : '';
        $this->params->dropboxSecret = isset($params['dropboxSecret']) ? $params['dropboxSecret'] : '';
        $this->params->dropboxToken  = isset($params['dropboxToken']) ? $params['dropboxToken'] : '';
    }

    /**
     * Save parameters
     *
     * @return void
     */
    protected function saveParams()
    {
        $params                  = $this->getAllDropboxConfigs();
        $params['dropboxKey']    = $this->params->dropboxKey;
        $params['dropboxSecret'] = $this->params->dropboxSecret;
        $params['dropboxToken']  = $this->params->dropboxToken;
        $this->saveDropboxConfigs($params);
    }

    /**
     * Get web auth
     *
     * @return \WPMFDropbox\WebAuthNoRedirect
     */
    public function getWebAuth()
    {
        $dropboxKey    = '';
        $dropboxSecret = 'dropboxSecret';
        if (!empty($this->params->dropboxKey)) {
            $dropboxKey = $this->params->dropboxKey;
        }
        if (!empty($this->params->dropboxSecret)) {
            $dropboxSecret = $this->params->dropboxSecret;
        }

        $appInfo = new WPMFDropbox\AppInfo($dropboxKey, $dropboxSecret);
        $webAuth = new WPMFDropbox\WebAuthNoRedirect($appInfo, $this->appName);

        return $webAuth;
    }

    /**
     * Get author Url allow user
     *
     * @return string
     */
    public function getAuthorizeDropboxUrl()
    {
        $authorizeUrl = $this->getWebAuth()->start();

        return $authorizeUrl;
    }

    /**
     * Convert the authorization code into an access token
     *
     * @param string $authCode Authorization code
     *
     * @return array
     */
    public function convertAuthorizationCode($authCode)
    {
        $list = array();
        list($accessToken, $dropboxUserId) = $this->getWebAuth()->finish($authCode);
        $list = array(
            'accessToken'   => $accessToken,
            'dropboxUserId' => $dropboxUserId
        );
        return $list;
    }

    /**
     * Check Author
     *
     * @return boolean
     */
    public function checkAuth()
    {
        $dropboxToken = $this->params->dropboxToken;
        if (!empty($dropboxToken)) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Logout dropbox app
     *
     * @return void
     */
    public function logout()
    {
        $params                  = $this->getAllDropboxConfigs();
        $params['dropboxKey']    = $this->params->dropboxKey;
        $params['dropboxSecret'] = $this->params->dropboxSecret;
        $params['dropboxAuthor'] = '';
        $params['dropboxToken']  = '';
        $this->saveDropboxConfigs($params);
        $this->redirect(admin_url('options-general.php?page=option-folder&tab=wpmf-dropbox'));
    }

    /**
     * Get dropbox client
     *
     * @return \WPMFDropbox\Client|boolean
     */
    public function getAccount()
    {
        try {
            $wpmfAddon_dropbox_config = get_option('_wpmfAddon_dropbox_config');
            $dropboxToken             = $wpmfAddon_dropbox_config['dropboxToken'];
            $dbxClient                = new WPMFDropbox\Client($dropboxToken, $this->appName);
            return $dbxClient;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Create folder
     *
     * @param string $name Folder name
     * @param string $path Folder parent path
     *
     * @return array|null
     */
    public function doCreateFolder($name, $path)
    {
        $dropbox = $this->getAccount();
        try {
            $parent   = $path . '/' . $name;
            $result = $dropbox->createFolder($parent);
        } catch (Exception $e) {
            $parent   = $path . '/' . $name . '-' . time();
            $result = $dropbox->createFolder($parent);
        }
        return $result;
    }

    /**
     * Sync folders with media library
     *
     * @return void
     */
    public function ajaxAddToQueue()
    {
        if (empty($_POST['wpmf_nonce'])
            || !wp_verify_nonce($_POST['wpmf_nonce'], 'wpmf_nonce')) {
            die();
        }

        set_time_limit(0);
        $params = get_option('_wpmfAddon_dropbox_config');
        if (empty($params['dropboxToken'])) {
            wp_send_json(array('status' => false));
        }

        if (isset($_POST['type']) && $_POST['type'] === 'auto') {
            // only run auto sync in one tab
            if (!empty($_POST['sync_token'])) {
                if (!get_option('wpmf_cloud_sync_time', false) && !get_option('wpmf_cloud_sync_token', false)) {
                    add_option('wpmf_cloud_sync_time', time());
                    add_option('wpmf_cloud_sync_token', $_POST['sync_token']);
                } else {
                    if ($_POST['sync_token'] !== get_option('wpmf_cloud_sync_token')) {
                        // stop run
                        if (time() - (int)get_option('wpmf_cloud_sync_time') < 60) {
                            wp_send_json(array('status' => false, 'continue' => false));
                        } else {
                            update_option('wpmf_cloud_sync_token', $_POST['sync_token']);
                            update_option('wpmf_cloud_sync_time', time());
                        }
                    }
                }
            }
        }

        $this->doAddToQueue($params);
        wp_send_json(array('status' => true));
    }

    /**
     * Do add to queue
     *
     * @param array $params Configs details
     *
     * @return void
     */
    public function doAddToQueue($params)
    {
        if (empty($params['dropboxToken'])) {
            return;
        }
        $datas = array(
            'id' => '',
            'folder_parent' => 0,
            'name' => 'Dropbox',
            'action' => 'wpmf_sync_dropbox',
            'type' => 'folder'
        );

        $row = JUMainQueue::checkQueueExist(json_encode($datas));
        if (!$row) {
            JUMainQueue::addToQueue($datas);
        } else {
            if ((int)$row->status !== 0) {
                JUMainQueue::addToQueue($datas);
            }
        }
    }

    /**
     * Remove the files/folders when sync
     *
     * @param boolean $result     Result
     * @param array   $datas      Data details
     * @param integer $element_id ID of queue element
     *
     * @return boolean|integer
     */
    public function syncRemoveItems($result, $datas, $element_id)
    {
        remove_action('delete_attachment', array($this, 'deleteAttachment'));
        remove_action('wpmf_before_delete_folder', array($this, 'deleteFolderLibrary'));
        $args = array(
            'post_type' => 'attachment',
            'posts_per_page' => -1,
            'post_status' => 'any',
            'meta_query' => array(
                array(
                    'key'       => 'wpmf_drive_type',
                    'value'     => 'dropbox',
                    'compare'   => '='
                )
            ),
            'tax_query'      => array(
                array(
                    'taxonomy'         => WPMF_TAXO,
                    'field'            => 'term_id',
                    'terms'            => (int)$datas['media_folder_id'],
                    'include_children' => false
                )
            ),
        );
        $media_library_files = get_posts($args);
        foreach ($media_library_files as $file) {
            $drive_id = get_post_meta($file->ID, 'wpmf_drive_id', true);
            if (is_array($datas['cloud_files_list']) && !empty($datas['cloud_files_list']) && !empty($drive_id) && !in_array($drive_id, $datas['cloud_files_list'])) {
                wp_delete_attachment($file->ID);
            }
        }

        // get media library files in current folder, then remove the folder not exist
        $folders = get_categories(array('hide_empty' => false, 'taxonomy' => WPMF_TAXO, 'parent' => (int)$datas['media_folder_id']));
        foreach ($folders as $folder) {
            $drive_id = get_term_meta($folder->term_id, 'wpmf_drive_id', true);
            if (is_array($datas['cloud_folders_list']) && !empty($datas['cloud_folders_list']) && !empty($drive_id) && !in_array($drive_id, $datas['cloud_folders_list'])) {
                wp_delete_term($folder->term_id, WPMF_TAXO);
            }
        }
        return true;
    }

    /**
     * Import file to media library
     *
     * @param string  $cloud_id  Cloud file ID
     * @param integer $term_id   Folder target ID
     * @param boolean $imported  Check imported
     * @param string  $filename  File name
     * @param string  $extension File extension
     *
     * @return boolean
     */
    public function importFile($cloud_id, $term_id, $imported, $filename, $extension)
    {
        $dropbox    = $this->getAccount();
        $upload_dir = wp_upload_dir();
        require_once 'includes/mime-types.php';

        // get dropbox file path by ID
        $cloud_path = $dropbox->getFileByID($cloud_id);
        if (empty($cloud_path['path_display'])) {
            return false;
        }

        $extension   = strtolower($extension);
        $content     = $dropbox->get_filecontent($cloud_path['path_display']);
        $getMimeType = getMimeType($extension);
        $status = $this->insertAttachmentMetadata(
            $upload_dir['path'],
            $upload_dir['url'],
            $filename,
            $content,
            $getMimeType,
            $extension,
            $term_id
        );

        if ($status) {
            return true;
        }

        return $imported;
    }

    /**
     * Insert a attachment to database
     *
     * @param string  $upload_path Wordpress upload path
     * @param string  $upload_url  Wordpress upload url
     * @param string  $file        File name
     * @param string  $content     Content of file
     * @param string  $mime_type   Mime type of file
     * @param string  $ext         Extension of file
     * @param integer $term_id     Media folder id to set file to folder
     *
     * @return boolean
     */
    public function insertAttachmentMetadata(
        $upload_path,
        $upload_url,
        $file,
        $content,
        $mime_type,
        $ext,
        $term_id
    ) {
        $file   = wp_unique_filename($upload_path, $file);
        $upload = file_put_contents($upload_path . '/' . $file, $content);
        if ($upload) {
            $attachment = array(
                'guid'           => $upload_url . '/' . $file,
                'post_mime_type' => $mime_type,
                'post_title'     => str_replace('.' . $ext, '', $file),
                'post_status'    => 'inherit'
            );

            $image_path = $upload_path . '/' . $file;
            // Insert attachment
            $attach_id   = wp_insert_attachment($attachment, $image_path);
            $attach_data = wp_generate_attachment_metadata($attach_id, $image_path);
            wp_update_attachment_metadata($attach_id, $attach_data);
            // set attachment to term
            wp_set_object_terms((int) $attach_id, (int) $term_id, WPMF_TAXO, true);
            return true;
        }

        return false;
    }

    /**
     * Download dropbox file
     *
     * @return void
     */
    public function downloadFile()
    {
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- download URL inserted post content
        if (isset($_REQUEST['id'])) {
            // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- download URL inserted post content
            $id_file  = $_REQUEST['id'];
            $dropbox  = $this->getAccount();
            $getFile  = $dropbox->getMetadata($id_file);
            $pinfo    = pathinfo($getFile['path_lower']);
            $tempfile = $pinfo['basename'];
            include_once 'includes/mime-types.php';
            $contenType = getMimeType($pinfo['extension']);
            header('Content-Disposition: inline; filename="' . basename($tempfile) . '"');
            header('Content-Description: File Transfer');
            header('Content-Type: ' . $contenType);
            header('Content-Transfer-Encoding: binary');

            header('Pragma: public');
            header('Content-Length: ' . $getFile['size']);
            $content = $dropbox->get_filecontent($getFile['path_lower']);
            // phpcs:ignore WordPress.Security.EscapeOutput -- Content already escaped in the method
            echo $content;
            die();
        } else {
            wp_send_json(false);
        }
    }

    /**
     * Redirect url
     *
     * @param string $location URL
     *
     * @return void
     */
    public function redirect($location)
    {
        if (!headers_sent()) {
            header('Location: ' . $location, true, 303);
        } else {
            // phpcs:ignore WordPress.Security.EscapeOutput -- Content already escaped in the method
            echo "<script>document.location.href='" . str_replace("'", '&apos;', $location) . "';</script>\n";
        }
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
        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- No action, nonce is not required
        if (!empty($_POST['wpmf_folder'])) {
            $folder_id = (int) $_POST['wpmf_folder'];
            $cloud_id = wpmfGetCloudFolderID($folder_id);
            if ($cloud_id) {
                $cloud_type = wpmfGetCloudFolderType($folder_id);
                if ($cloud_type && $cloud_type === 'dropbox') {
                    try {
                        $dropbox_config = get_option('_wpmfAddon_dropbox_config');
                        $filePath = get_attached_file($attachment_id);
                        $scaled = WpmfAddonHelper::fixImageOrientation(array('file' => $filePath));
                        $filePath = $scaled['file'];
                        $size = filesize($filePath);
                        if (file_exists($filePath)) {
                            $info = pathinfo($filePath);
                            $id_folder = ($cloud_id === 'root') ? '' : $cloud_id;
                            $f         = fopen($filePath, 'rb');
                            $dropbox   = $this->getAccount();
                            $path      = $id_folder . '/' . $info['basename'];

                            $result = $dropbox->uploadFile($path, WPMFDropbox\WriteMode::add(), $f, $size);
                            // upload attachment to cloud
                            if (!empty($result)) {
                                $metadata = $dropbox->getFileMetadata($result['path_display']);
                                // add attachment meta
                                global $wpdb;
                                add_post_meta($attachment_id, 'wpmf_drive_id', $result['id']);
                                add_post_meta($attachment_id, 'wpmf_drive_type', 'dropbox');

                                // update guid URL
                                $where = array('ID' => $attachment_id);
                                if (isset($dropbox_config['link_type']) && $dropbox_config['link_type'] === 'public') {
                                    // public file
                                    $links = $dropbox->get_shared_links($result['path_display']);
                                    if (!empty($links['links'])) {
                                        $shared_links = $links['links'][0];
                                    } else {
                                        $shared_links = $dropbox->create_shared_link($result['path_display']);
                                    }
                                    $link = $shared_links['url'] . '&raw=1';
                                } else {
                                    $link = admin_url('admin-ajax.php') . '?action=wpmf-dbxdownload-file&id=' . urlencode($result['id']) . '&link=true&dl=0';
                                }

                                $wpdb->update($wpdb->posts, array('guid' => $link), $where);
                                unlink($filePath);

                                // add attachment metadata
                                $upload_path = wp_upload_dir();
                                $attached = trim($upload_path['subdir'], '/') . '/' . $info['basename'];
                                $meta = array();
                                if (isset($metadata['media_info']['metadata']['dimensions']['width']) && isset($metadata['media_info']['metadata']['dimensions']['height'])) {
                                    $meta['width'] = $metadata['media_info']['metadata']['dimensions']['width'];
                                    $meta['height'] = $metadata['media_info']['metadata']['dimensions']['height'];
                                } else {
                                    list($width, $heigth) = wpmfGetImgSize($link);
                                    $meta['width'] = $width;
                                    $meta['height'] = $heigth;
                                }

                                $meta['file'] = $attached;
                                if (isset($metadata['size'])) {
                                    $meta['filesize'] = $metadata['size'];
                                }
                                add_post_meta($attachment_id, 'wpmf_attachment_metadata', $meta);
                            }
                        }
                    } catch (Exception $e) {
                        echo esc_html($e->getMessage());
                    }
                }
            }
        }
    }

    /**
     * Update metadata for cloud file
     *
     * @param array   $meta          Meta data
     * @param integer $attachment_id Attachment ID
     *
     * @return mixed
     */
    public function wpGenerateAttachmentMetadata($meta, $attachment_id)
    {
        $drive_id = get_post_meta($attachment_id, 'wpmf_drive_id', true);
        if (!empty($drive_id)) {
            $data = get_post_meta($attachment_id, 'wpmf_attachment_metadata', true);
            if (!empty($data) && !empty($meta)) {
                $meta = $data;
                delete_post_meta($attachment_id, 'wpmf_attachment_metadata');
            }
        }

        return $meta;
    }

    /**
     * Create cloud folder from media library
     *
     * @param integer $folder_id    Local folder ID
     * @param string  $name         Folder name
     * @param integer $parent_id    Local folder parent ID
     * @param array   $informations Informations
     *
     * @return boolean
     */
    public function createFolderLibrary($folder_id, $name, $parent_id, $informations)
    {
        try {
            $cloud_id = wpmfGetCloudFolderID($parent_id);
            if ($cloud_id) {
                $cloud_type = wpmfGetCloudFolderType($parent_id);
                if ($cloud_type && $cloud_type === 'dropbox') {
                    if ($cloud_id === 'root') {
                        $cloud_path = '';
                    } else {
                        $dropbox = $this->getAccount();
                        $cloud_id = $dropbox->getFileByID($cloud_id);
                        $cloud_path = $cloud_id['path_display'];
                    }

                    $folder = $this->doCreateFolder($name, $cloud_path);
                    add_term_meta($folder_id, 'wpmf_drive_id', $folder['id']);
                    add_term_meta($folder_id, 'wpmf_drive_type', 'dropbox');
                }
            }
        } catch (Exception $ex) {
            return false;
        }

        return true;
    }

    /**
     * Delete cloud folder from media library
     *
     * @param object $folder Local folder info
     *
     * @return boolean
     */
    public function deleteFolderLibrary($folder)
    {
        try {
            $cloud_id = wpmfGetCloudFolderID($folder->term_id);
            if ($cloud_id) {
                $cloud_type = wpmfGetCloudFolderType($folder->term_id);
                if ($cloud_type && $cloud_type === 'dropbox') {
                    $dropbox = $this->getAccount();
                    if ($cloud_id !== 'root' && $cloud_id !== '') {
                        $cloud_path = $dropbox->getFileByID($cloud_id);
                        $dropbox->delete($cloud_path['path_display']);
                    }
                }
            }
        } catch (Exception $ex) {
            return false;
        }

        return true;
    }

    /**
     * Rename cloud folder from media library
     *
     * @param integer $id   Local folder ID
     * @param string  $name New name
     *
     * @return boolean
     */
    public function updateFolderNameLibrary($id, $name)
    {
        try {
            $cloud_id = wpmfGetCloudFolderID($id);
            if ($cloud_id) {
                $cloud_type = wpmfGetCloudFolderType($id);
                if ($cloud_type && $cloud_type === 'dropbox') {
                    $dropbox = $this->getAccount();
                    if ($cloud_id !== 'root') {
                        $cloud_path = $dropbox->getFileByID($cloud_id);
                        $pathinfo = pathinfo($cloud_path['path_display']);
                        $dropbox->move($cloud_path['path_display'], rtrim($pathinfo['dirname'], '/') . '/' . urldecode($name));
                    }
                }
            }
        } catch (Exception $ex) {
            return false;
        }

        return true;
    }

    /**
     * Move cloud folder from media library
     *
     * @param integer $folder_id    Local folder ID
     * @param integer $parent_id    Local folder new parent ID
     * @param array   $informations Informations
     *
     * @return boolean
     */
    public function moveFolderLibrary($folder_id, $parent_id, $informations)
    {
        try {
            $cloud_id = wpmfGetCloudFolderID($folder_id);
            if ($cloud_id) {
                $cloud_type = wpmfGetCloudFolderType($folder_id);
                if ($cloud_type && $cloud_type === 'dropbox') {
                    if ($cloud_id !== 'root') {
                        $dropbox = $this->getAccount();
                        $cloud_parentid = wpmfGetCloudFolderID($parent_id);
                        $cloud_path = $dropbox->getFileByID($cloud_id);
                        $pathinfo = pathinfo($cloud_path['path_display']);
                        if ($cloud_parentid === 'root') {
                            $newpath = '/' . $pathinfo['filename'];
                        } else {
                            $cloud_parent_path = $dropbox->getFileByID($cloud_parentid);
                            $newpath = $cloud_parent_path['path_display'] . '/' . $pathinfo['filename'];
                        }

                        $dropbox->move($cloud_path['path_display'], $newpath);
                    }
                }
            }
        } catch (Exception $ex) {
            return false;
        }

        return true;
    }

    /**
     * Move cloud folder from media library
     *
     * @param integer $fileid       Local file ID
     * @param integer $parent_id    Local folder new parent ID
     * @param array   $informations Informations
     *
     * @return boolean
     */
    public function moveFileLibrary($fileid, $parent_id, $informations)
    {
        try {
            $cloud_id = wpmfGetCloudFileID($fileid);
            if ($cloud_id) {
                $cloud_type = wpmfGetCloudFileType($fileid);
                if ($cloud_type && $cloud_type === 'dropbox') {
                    $dropbox = $this->getAccount();
                    $cloud_parentid = wpmfGetCloudFolderID($parent_id);

                    $cloud_path = $dropbox->getFileByID($cloud_id);
                    $pathinfo = pathinfo($cloud_path['path_display']);
                    if ($cloud_parentid === 'root') {
                        $newpath = '/' . $pathinfo['basename'];
                    } else {
                        $cloud_parent_path = $dropbox->getFileByID($cloud_parentid);
                        $newpath = $cloud_parent_path['path_display'] . '/' . $pathinfo['basename'];
                    }

                    $dropbox->move($cloud_path['path_display'], $newpath);
                }
            }
        } catch (Exception $ex) {
            return false;
        }

        return true;
    }

    /**
     * Delete cloud attachment
     *
     * @param integer $pid Attachment ID
     *
     * @return boolean
     */
    public function deleteAttachment($pid)
    {
        try {
            $cloud_id = wpmfGetCloudFileID($pid);
            if ($cloud_id) {
                $cloud_type = wpmfGetCloudFileType($pid);
                if ($cloud_type && $cloud_type === 'dropbox') {
                    $dropbox = $this->getAccount();
                    $cloud_path = $dropbox->getFileByID($cloud_id);
                    $dropbox->delete($cloud_path['path_display']);
                }
            }
        } catch (Exception $ex) {
            return false;
        }

        return true;
    }

    /**
     * Get file link
     *
     * @param string $id             Cloud file ID
     * @param array  $dropbox_config Dropbox settings
     * @param object $dropbox        Dropbox Client
     *
     * @return boolean|string
     */
    public function getLink($id, $dropbox_config, $dropbox)
    {
        try {
            $cloud_path = $dropbox->getFileByID($id);
            if (isset($dropbox_config['link_type']) && $dropbox_config['link_type'] === 'public') {
                // public file
                $links = $dropbox->get_shared_links($cloud_path['path_display']);
                if (!empty($links['links'])) {
                    $shared_links = $links['links'][0];
                } else {
                    $shared_links = $dropbox->create_shared_link($cloud_path['path_display']);
                }
                $link = $shared_links['url'] . '&raw=1';
            } else {
                $link = admin_url('admin-ajax.php') . '?action=wpmf-dbxdownload-file&id=' . urlencode($cloud_path['path_display']) . '&link=true&dl=0';
            }
        } catch (Exception $e) {
            $link = false;
        }

        return $link;
    }

    /**
     * Insert attachment
     *
     * @param array   $info        File info
     * @param array   $child       File details
     * @param integer $parent      Parent folder
     * @param array   $upload_path Upload path
     * @param string  $link        Link
     * @param string  $mimeType    Mime Type
     * @param integer $width       Width
     * @param integer $height      Height
     *
     * @return void
     */
    public function insertAttachment($info, $child, $parent, $upload_path, $link, $mimeType, $width = 0, $height = 0)
    {
        $attachment = array(
            'guid'           => $link,
            'post_mime_type' => $mimeType,
            'post_title'     => $info['filename'],
            'post_type'     => 'attachment',
            'post_status'    => 'inherit'
        );

        $attach_id   = wp_insert_post($attachment);
        $attached = trim($upload_path['subdir'], '/') . '/' . $child['name'];
        wp_set_object_terms((int) $attach_id, (int) $parent, WPMF_TAXO);

        update_post_meta($attach_id, '_wp_attached_file', $attached);
        update_post_meta($attach_id, 'wpmf_size', $child['size']);
        update_post_meta($attach_id, 'wpmf_filetype', $info['extension']);
        update_post_meta($attach_id, 'wpmf_order', 0);
        update_post_meta($attach_id, 'wpmf_drive_id', $child['id']);
        update_post_meta($attach_id, 'wpmf_drive_type', 'dropbox');

        $meta = array();
        if (strpos($mimeType, 'image') !== false) {
            if (!empty($width) && !empty($height)) {
                $meta['width'] = $width;
                $meta['height'] = $height;
            } else {
                list($width, $heigth) = wpmfGetImgSize($link);
                $meta['width'] = $width;
                $meta['height'] = $heigth;
            }
        }

        if (isset($child['size'])) {
            $meta['filesize'] = $child['size'];
        }
        update_post_meta($attach_id, '_wp_attachment_metadata', $meta);
    }

    /**
     * Update attachment
     *
     * @param array   $info    File info
     * @param integer $file_id Attachment ID
     * @param integer $parent  Parent folder
     *
     * @return void
     */
    public function updateAttachment($info, $file_id, $parent)
    {
        $curent_parents = get_the_terms($file_id, WPMF_TAXO);
        if (isset($parent)) {
            if (empty($curent_parents)) {
                wp_set_object_terms((int) $file_id, (int)$parent, WPMF_TAXO);
            } else {
                foreach ($curent_parents as $curent_parent) {
                    if (!empty($parent) && (int)$curent_parent->term_id !== (int)$parent) {
                        wp_set_object_terms((int) $file_id, (int)$parent, WPMF_TAXO);
                    }
                }
            }
        }

        $attached_file = get_post_meta($file_id, '_wp_attached_file', true);
        $attached_info = pathinfo($attached_file);
        if ($info['filename'] !== $attached_info['filename']) {
            $new_path = str_replace($attached_info['filename'], $info['filename'], $attached_file);
            update_post_meta($file_id, '_wp_attached_file', $new_path);
        }
    }

    /**
     * Sync folders and files with crontab method
     *
     * @return void
     */
    public function autoSyncWithCrontabMethod()
    {
        $params = get_option('_wpmfAddon_dropbox_config');
        if (empty($params['dropboxToken'])) {
            return;
        }
        if (!class_exists('\Joomunited\WPMF\JUMainQueue')) {
            require_once WP_MEDIA_FOLDER_PLUGIN_DIR . 'class/queue/main-queue.php';
        }
        $args = wpmfGetQueueOptions(true);
        call_user_func('\Joomunited\WPMF\JUMainQueue::init', $args);
        $this->doAddToQueue($params);
        JUMainQueue::proceedQueueAsync();
    }

    /**
     * Add root to queue
     *
     * @return void
     */
    public function addRootToQueue()
    {
        $params = get_option('_wpmfAddon_dropbox_config');
        if (!empty($params['dropboxToken'])) {
            $datas = array(
                'id' => '',
                'folder_parent' => 0,
                'name' => 'Dropbox',
                'action' => 'wpmf_sync_dropbox',
                'type' => 'folder'
            );

            $row = JUMainQueue::checkQueueExist(json_encode($datas));
            if (!$row) {
                JUMainQueue::addToQueue($datas);
            }
        }
    }

    /**
     * Sync cloud folder and file from queue
     *
     * @param boolean $result     Result
     * @param array   $datas      Data details
     * @param integer $element_id ID of queue element
     *
     * @return boolean|integer
     */
    public function doSync($result, $datas, $element_id)
    {
        $configs = get_option('_wpmfAddon_dropbox_config');
        if (empty($configs['dropboxToken'])) {
            return -1;
        }
        global $wpdb;
        $name = html_entity_decode($datas['name']);
        if ($datas['type'] === 'folder') {
            // check folder exists
            $row = $wpdb->get_row($wpdb->prepare('SELECT term_id, meta_value FROM ' . $wpdb->termmeta . ' WHERE meta_key = %s AND BINARY meta_value = BINARY %s', array('wpmf_drive_id', $datas['id'])));
            // if folder not exists
            if (!$row) {
                $inserted = wp_insert_term($name, WPMF_TAXO, array('parent' => (int)$datas['folder_parent']));
                if (is_wp_error($inserted)) {
                    $folder_id = (int)$inserted->error_data['term_exists'];
                } else {
                    $folder_id = (int)$inserted['term_id'];
                }
                if ($name === 'Dropbox' && (int)$datas['folder_parent'] === 0) {
                    update_term_meta($folder_id, 'wpmf_drive_root_id', $datas['id']);
                } else {
                    update_term_meta($folder_id, 'wpmf_drive_id', $datas['id']);
                }
            } else {
                $folder_id = (int)$row->term_id;
                $exist_folder = get_term($folder_id, WPMF_TAXO);
                // if folder exists, then update parent and name
                if (!empty($datas['folder_parent']) && (int)$exist_folder->parent !== (int)$datas['folder_parent']) {
                    $parent_exist = get_term((int)$datas['folder_parent'], WPMF_TAXO);
                    if (!is_wp_error($parent_exist)) {
                        wp_update_term($folder_id, WPMF_TAXO, array('parent' => (int) $datas['folder_parent']));
                    }
                }

                if ($name !== $exist_folder->name) {
                    wp_update_term($folder_id, WPMF_TAXO, array('name' => $name));
                }
            }

            // find childs element to add to queue
            if (!empty($folder_id)) {
                $responses = array();
                $responses['folder_id'] = (int)$folder_id;
                update_term_meta($responses['folder_id'], 'wpmf_drive_type', 'dropbox');
                JUMainQueue::updateQueueTermMeta((int)$responses['folder_id'], (int)$element_id);
                JUMainQueue::updateResponses((int)$element_id, $responses);
                $this->addChildsToQueue($datas['id'], $folder_id);
            }
        } else {
            $upload_path = wp_upload_dir();
            $info = pathinfo($name);
            $row = $wpdb->get_row($wpdb->prepare('SELECT post_id, meta_value FROM ' . $wpdb->postmeta . ' WHERE meta_key = %s AND BINARY meta_value = BINARY %s', array('wpmf_drive_id', $datas['id'])));
            if (!$row) {
                $dropbox      = $this->getAccount();
                $link = $this->getLink($datas['id'], $configs, $dropbox);
                if (!$link) {
                    return false;
                }

                // insert attachment
                $attachment = array(
                    'guid'           => $link,
                    'post_mime_type' => $datas['file']['mimeType'],
                    'post_title'     => $info['filename'],
                    'post_type'     => 'attachment',
                    'post_status'    => 'inherit'
                );

                $file_id   = wp_insert_post($attachment);
                $attached = trim($upload_path['subdir'], '/') . '/' . $name;
                wp_set_object_terms((int) $file_id, (int)$datas['folder_parent'], WPMF_TAXO);

                update_post_meta($file_id, '_wp_attached_file', $attached);
                update_post_meta($file_id, 'wpmf_size', $datas['size']);
                update_post_meta($file_id, 'wpmf_filetype', $info['extension']);
                update_post_meta($file_id, 'wpmf_order', 0);
                update_post_meta($file_id, 'wpmf_drive_id', $datas['id']);
                update_post_meta($file_id, 'wpmf_drive_type', 'dropbox');

                $meta = array();
                if (strpos($datas['file']['mimeType'], 'image') !== false) {
                    if (isset($child['image']['width']) && isset($datas['image']['height'])) {
                        $meta['width'] = $datas['image']['width'];
                        $meta['height'] = $datas['image']['height'];
                    } else {
                        list($width, $heigth) = wpmfGetImgSize($link);
                        $meta['width'] = $width;
                        $meta['height'] = $heigth;
                    }

                    $meta['file'] = $attached;
                }

                if (isset($datas['size'])) {
                    $meta['filesize'] = $datas['size'];
                }
                update_post_meta($file_id, '_wp_attachment_metadata', $meta);
            } else {
                // update attachment
                $file_id = $row->post_id;
                $this->updateAttachment($info, $file_id, $datas['folder_parent']);
                $file = get_post($file_id);
                // update file URL
                if (strpos($file->guid, 'wpmf-dbxdownload-file') !== false && $configs['link_type'] === 'public') {
                    $dropbox      = $this->getAccount();
                    $link = $this->getLink($datas['id'], $configs, $dropbox);
                    if (!$link) {
                        return false;
                    }

                    $wpdb->update(
                        $wpdb->posts,
                        array(
                            'guid' => $link
                        ),
                        array('ID' => $file_id),
                        array(
                            '%s'
                        ),
                        array('%d')
                    );
                }
            }

            if (!empty($file_id)) {
                $responses = array();
                $responses['attachment_id'] = (int)$file_id;
                JUMainQueue::updateResponses((int)$element_id, $responses);
                JUMainQueue::updateQueuePostMeta((int)$file_id, (int)$element_id);
            }
        }

        return true;
    }

    /**
     * Add child items to queue
     *
     * @param string  $folderID      ID of cloud folder
     * @param integer $folder_parent ID of folder parent on media library
     *
     * @return void
     */
    public function addChildsToQueue($folderID, $folder_parent)
    {
        $error = false;
        $has_more  = false;
        $cursor  = '';
        $childs = array();
        do {
            try {
                $dropbox = $this->getAccount();
                if ($has_more) {
                    $fs = $dropbox->getMoreChildrens(array('cursor' => $cursor));
                } else {
                    $fs = $dropbox->getMetadataWithChildren($folderID, false, array('limit' => 200));
                }

                $childs = array_merge($childs, $fs['entries']);
                $has_more = $fs['has_more'];
                $cursor = $fs['cursor'];
            } catch (Exception $e) {
                $error = true;
                $has_more = false;
            }
        } while ($has_more);

        if ($error) {
            return;
        }

        include_once 'includes/mime-types.php';
        // get folder childs list on cloud
        $cloud_folders_list = array();
        // get file childs list on cloud
        $cloud_files_list = array();
        // Create files in media library
        foreach ($childs as $child) {
            $datas = array(
                'id' => $child['id'],
                'path_lower' => $child['path_lower'],
                'folder_parent' => $folder_parent,
                'name' => mb_convert_encoding($child['name'], 'HTML-ENTITIES', 'UTF-8'),
                'action' => 'wpmf_sync_dropbox',
                'cloud_parent' => $folderID
            );

            if ($child['.tag'] === 'file') {
                $cloud_files_list[] = $child['id'];
                $fileExtension = pathinfo($child['name'], PATHINFO_EXTENSION);
                $mimeType   = getMimeType($fileExtension);
                $datas['type'] = 'file';
                $datas['rev'] = $child['rev'];
                $datas['file'] = array('mimeType' => $mimeType);
                $datas['image'] = array();
                $datas['size'] = $child['size'];
                if (strpos($mimeType, 'image') !== false) {
                    $dimensions = array('width' => 0, 'height' => 0);
                    if (isset($child['media_info'])) {
                        if (empty($child['media_info']['metadata']['dimensions'])) {
                            $dimensions = array(
                                'width' => $child['media_info']['metadata']['dimensions']['width'],
                                'height' => $child['media_info']['metadata']['dimensions']['height']
                            );
                        }
                    }
                    $datas['image'] = $dimensions;
                }
            } else {
                $cloud_folders_list[] = $child['id'];
                $datas['type'] = 'folder';
            }
            JUMainQueue::addToQueue($datas);
        }

        // then remove the file and folder not exist
        $datas = array(
            'id' => '',
            'media_folder_id' => $folder_parent,
            'cloud_folder_id' => $folderID,
            'action' => 'wpmf_dropbox_remove',
            'cloud_files_list' => $cloud_files_list,
            'cloud_folders_list' => $cloud_folders_list,
            'time' => time()
        );
        $row = JUMainQueue::checkQueueExist(json_encode($datas));
        if (!$row) {
            JUMainQueue::addToQueue($datas);
        } else {
            if ((int)$row->status !== 0) {
                JUMainQueue::addToQueue($datas);
            }
        }
    }
}
