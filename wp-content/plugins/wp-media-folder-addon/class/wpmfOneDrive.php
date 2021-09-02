<?php
/* Prohibit direct script loading */
defined('ABSPATH') || die('No direct script access allowed!');
require_once(WPMFAD_PLUGIN_DIR . '/class/Onedrive/vendor/autoload.php');

use Joomunited\WPMF\JUMainQueue;
use GuzzleHttp\Client as GuzzleHttpClient;
use Krizalys\Onedrive\Client;
use Microsoft\Graph\Graph;
use Krizalys\Onedrive\File;
use Microsoft\Graph\Model\DriveItem;
use Microsoft\Graph\Model;
use Microsoft\Graph\Model\UploadSession;

/**
 * Class WpmfAddonOneDrive
 * This class that holds most of the admin functionality for OneDrive
 */
class WpmfAddonOneDrive
{

    /**
     * OneDrive Client
     *
     * @var OneDrive_Client
     */
    private $client = null;

    /**
     * File fields
     *
     * @var string
     */
    protected $apifilefields = 'thumbnails,children(top=1000;expand=thumbnails(select=medium,large,mediumSquare,c1500x1500))';

    /**
     * List files fields
     *
     * @var string
     */
    protected $apilistfilesfields = 'thumbnails(select=medium,large,mediumSquare,c1500x1500)';

    /**
     * BreadCrumb
     *
     * @var string
     */
    public $breadcrumb = '';

    /**
     * AccessToken
     *
     * @var string
     */
    private $accessToken;

    /**
     * Refresh token
     *
     * @var string
     */
    private $refreshToken;

    /**
     * Get token from _wpmfAddon_onedrive_config option
     *
     * @return boolean|WP_Error
     */
    public function loadToken()
    {
        $onedriveconfig = get_option('_wpmfAddon_onedrive_config');
        if (empty($onedriveconfig['state']->token)) {
            return new WP_Error('broke', __("The plugin isn't yet authorized to use your OneDrive!
             Please (re)-authorize the plugin", 'wpmfAddon'));
        } else {
            $this->accessToken = $onedriveconfig['state']->token->data->access_token;
            $this->refreshToken = $onedriveconfig['state']->token->data->refresh_token;
        }

        return true;
    }

    /**
     * Revoke token
     * To-Do: Revoke Token is not yet possible with OneDrive API
     *
     * @return boolean
     */
    public function revokeToken()
    {
        $this->accessToken = '';
        $this->refreshToken = '';
        $onedriveconfig = get_option('_wpmfAddon_onedrive_config');
        $onedriveconfig['state'] = array();
        $onedriveconfig['connected'] = 0;
        update_option('_wpmfAddon_onedrive_config', $onedriveconfig);
        return true;
    }

    /**
     * Renews the access token from OAuth. This token is valid for one hour.
     *
     * @param object $client         Client
     * @param array  $onedriveconfig Setings
     *
     * @return Client
     */
    public function renewAccessToken($client, $onedriveconfig)
    {
        $client->renewAccessToken($onedriveconfig['OneDriveClientSecret']);
        $onedriveconfig['state'] = $client->getState();
        update_option('_wpmfAddon_onedrive_config', $onedriveconfig);
        $graph = new Graph();
        $graph->setAccessToken($client->getState()->token->data->access_token);
        $client = new Client(
            $onedriveconfig['OneDriveClientId'],
            $graph,
            new GuzzleHttpClient(),
            array(
                'state' => $client->getState()
            )
        );

        return $client;
    }

    /**
     * Read OneDrive app key and secret
     *
     * @return Client|OneDrive_Client|boolean
     */
    public function getClient()
    {
        $onedriveconfig = get_option('_wpmfAddon_onedrive_config');
        if (empty($onedriveconfig['OneDriveClientId']) && empty($onedriveconfig['OneDriveClientSecret'])) {
            return false;
        }

        try {
            if (isset($onedriveconfig['state']) && isset($onedriveconfig['state']->token->data->access_token)) {
                $graph = new Graph();
                $graph->setAccessToken($onedriveconfig['state']->token->data->access_token);
                $client = new Client(
                    $onedriveconfig['OneDriveClientId'],
                    $graph,
                    new GuzzleHttpClient(),
                    array(
                        'state' => $onedriveconfig['state']
                    )
                );

                if ($client->getAccessTokenStatus() === -2) {
                    $client = $this->renewAccessToken($client, $onedriveconfig);
                }
            } else {
                $client = new Client(
                    $onedriveconfig['OneDriveClientId'],
                    new Graph(),
                    new GuzzleHttpClient()
                );
            }

            $this->client = $client;
            return $this->client;
        } catch (Exception $ex) {
            echo esc_html($ex->getMessage());
            return false;
        }
    }

    /**
     * Start OneDrive API Client with token
     *
     * @return OneDrive_Client|WP_Error
     */
    public function startClient()
    {
        if ($this->accessToken === false) {
            die();
        }

        return $this->client;
    }

    /**
     * Get DriveInfo
     *
     * @return boolean|null|OneDrive_Service_Drive_About|WP_Error
     */
    public function getDriveInfo()
    {
        if ($this->client === null) {
            return false;
        }

        $driveInfo = null;
        try {
            $driveInfo = $this->client->getDrives();
        } catch (Exception $ex) {
            return new WP_Error('broke', $ex->getMessage());
        }
        if ($driveInfo !== null) {
            return $driveInfo;
        } else {
            return new WP_Error('broke', 'drive null');
        }
    }

    /**
     * Get a $authorizeUrl
     *
     * @return string|WP_Error
     */
    public function getAuthUrl()
    {
        try {
            $onedriveconfig = get_option('_wpmfAddon_onedrive_config');
            $authorizeUrl = $this->client->getLogInUrl(array(
                'files.read',
                'files.read.all',
                'files.readwrite',
                'files.readwrite.all',
                'offline_access',
            ), admin_url(), 'wpmf-onedrive');

            $onedriveconfig['state'] = $this->client->getState();
            update_option('_wpmfAddon_onedrive_config', $onedriveconfig);
        } catch (Exception $ex) {
            return new WP_Error('broke', __('Could not start authorization: ', 'wpmfAddon') . $ex->getMessage());
        }
        return $authorizeUrl;
    }

    /**
     * Set redirect URL
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
     * Create token after connected
     *
     * @param string $code Code to access to onedrive app
     *
     * @return boolean|WP_Error
     */
    public function createToken($code)
    {
        try {
            $onedriveconfig = get_option('_wpmfAddon_onedrive_config');
            $client = new Client(
                $onedriveconfig['OneDriveClientId'],
                new Graph(),
                new GuzzleHttpClient(),
                array(
                    'state' => $onedriveconfig['state']
                )
            );

            $blogname = trim(str_replace(array('.', ',', ':', '~', '"', '%', '&', '*', '<', '>', '?', '/', '\\', '{', '|', '}'), '', get_bloginfo('name')));
            // Obtain the token using the code received by the OneDrive API.
            $client->obtainAccessToken($onedriveconfig['OneDriveClientSecret'], $code);
            $graph = new Graph();
            $graph->setAccessToken($client->getState()->token->data->access_token);

            if (empty($onedriveconfig['onedriveBaseFolder'])) {
                $root = $client->createFolder('WP Media Folder - ' . $blogname);
                $onedriveconfig['onedriveBaseFolder'] = array(
                    'id' => $root->getId(),
                    'name' => $root->getName()
                );
            } else {
                $root = $graph
                    ->createRequest('GET', '/me/drive/items/' . $onedriveconfig['onedriveBaseFolder']['id'])
                    ->setReturnType(Model\DriveItem::class)// phpcs:ignore PHPCompatibility.Constants.NewMagicClassConstant.Found -- Use to sets the return type of the response object
                    ->execute();

                if (!is_wp_error($root)) {
                    $onedriveconfig['onedriveBaseFolder'] = array(
                        'id' => $root->getId(),
                        'name' => $root->getName()
                    );
                }
            }

            $token = $client->getState()->token->data->access_token;
            $this->accessToken = $token;
            $onedriveconfig['connected'] = 1;
            $onedriveconfig['state'] = $client->getState();
            // update _wpmfAddon_onedrive_config option and redirect page
            update_option('_wpmfAddon_onedrive_config', $onedriveconfig);
            update_option('wpmf_onedrive_notice', 1);
            $this->redirect(admin_url('options-general.php?page=option-folder#one_drive_box'));
        } catch (Exception $ex) {
            ?>
            <div class="error" id="wpmf_error">
                <p>
                    <?php
                    if ((int)$ex->getCode() === 409) {
                        echo esc_html__('The root folder name already exists on cloud. Please rename or delete that folder before connect', 'wpmfAddon');
                    } else {
                        echo esc_html__('Error communicating with OneDrive API: ', 'wpmfAddon');
                        echo esc_html($ex->getMessage());
                    }
                    ?>
                </p>
            </div>
            <?php
            return new WP_Error(
                'broke',
                esc_html__('Error communicating with OneDrive API: ', 'wpmfAddon') . $ex->getMessage()
            );
        }

        return true;
    }

    /**
     * Create folder
     *
     * @param string $name     Folder name
     * @param string $parentID Folder parent ID
     *
     * @return \Krizalys\Onedrive\Folder
     */
    public function doCreateFolder($name, $parentID)
    {
        $client = $this->getClient();
        $folder = $client->createFolder($name, $parentID);
        return $folder;
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
        $client = $this->getClient();
        $upload_dir = wp_upload_dir();
        $file = new File($client, $cloud_id);
        if ($file) {
            $content = $file->fetchContent();
            $extension = strtolower($extension);
            include_once 'includes/mime-types.php';
            $mimeType = getMimeType($extension);
            $status = $this->insertAttachmentMetadata(
                $upload_dir['path'],
                $upload_dir['url'],
                $filename,
                $content,
                $mimeType,
                $extension,
                $term_id
            );

            if ($status) {
                return true;
            }
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
    public function insertAttachmentMetadata($upload_path, $upload_url, $file, $content, $mime_type, $ext, $term_id)
    {
        $file = wp_unique_filename($upload_path, $file);
        $upload = file_put_contents($upload_path . '/' . $file, $content);
        if ($upload) {
            $attachment = array(
                'guid' => $upload_url . '/' . $file,
                'post_mime_type' => $mime_type,
                'post_title' => str_replace('.' . $ext, '', $file),
                'post_status' => 'inherit'
            );

            $image_path = $upload_path . '/' . $file;
            // Insert attachment
            $attach_id = wp_insert_attachment($attachment, $image_path);
            $attach_data = wp_generate_attachment_metadata($attach_id, $image_path);
            wp_update_attachment_metadata($attach_id, $attach_data);
            // set attachment to term{
            wp_set_object_terms((int) $attach_id, (int) $term_id, WPMF_TAXO, true);
            return true;
        }
        return false;
    }

    /**
     * Download a file
     *
     * @return void
     */
    public function downloadFile()
    {
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- download URL inserted post content
        if (empty($_REQUEST['id'])) {
            wp_send_json(array('status' => false));
        }

        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- download URL inserted post content
        $id = $_REQUEST['id'];
        $client = $this->getClient();
        $file = new File($client, $id);
        $infofile = pathinfo($file->getName());

        $contenType = 'application/octet-stream';
        if (isset($infofile['extension'])) {
            include_once 'includes/mime-types.php';
            $contenType = getMimeType($infofile['extension']);
        }

        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- download URL inserted post content
        if (!empty($_REQUEST['dl'])) {
            $this->downloadHeader($file->getName(), (int)$file->getSize(), $contenType, true);
        } else {
            $this->downloadHeader($file->getName(), (int)$file->getSize(), $contenType, false);
        }

        // phpcs:ignore WordPress.Security.EscapeOutput -- Content already escaped in the method
        echo $file->fetchContent();
        die();
    }

    /**
     * Send a raw HTTP header
     *
     * @param string  $file        File name
     * @param integer $size        File size
     * @param string  $contentType Content type
     * @param string  $download    Download
     *
     * @internal param string $contenType content type
     *
     * @return void
     */
    public function downloadHeader($file, $size, $contentType, $download = true)
    {
        ob_end_clean();
        ob_start();
        if ($download) {
            header('Content-Disposition: attachment; filename="' . basename($file) . '"');
        } else {
            header('Content-Disposition: inline; filename="' . basename($file) . '"');
        }

        header('Content-Description: File Transfer');
        header('Content-Type: ' . $contentType);
        header('Content-Transfer-Encoding: binary');
        header('Pragma: public');
        if ((int)$size !== 0) {
            header('Content-Length: ' . $size);
        }
        ob_clean();
        flush();
    }

    /**
     * Get share link
     *
     * @param string $id ID of item
     *
     * @return mixed
     */
    public function getLink($id)
    {
        $onedriveconfig = get_option('_wpmfAddon_onedrive_config');
        if (isset($onedriveconfig['link_type']) && $onedriveconfig['link_type'] === 'public') {
            // public file
            try {
                $graph = new Graph();
                $graph->setAccessToken($onedriveconfig['state']->token->data->access_token);
                $response = $graph
                    ->createRequest('POST', '/me/drive/items/' . $id . '/createLink')
                    ->attachBody(array('type' => 'embed', 'scope' => 'anonymous'))
                    ->setReturnType(Model\Permission::class)// phpcs:ignore PHPCompatibility.Constants.NewMagicClassConstant.Found -- Use to sets the return type of the response object
                    ->execute();
                $shareId = $response->getShareId();
                return 'https://api.onedrive.com/v1.0/shares/' . $shareId . '/root/content';
            } catch (Exception $e) {
                $link = false;
            }
        } else {
            $link = admin_url('admin-ajax.php') . '?action=wpmf_onedrive_download&id=' . urlencode($id) . '&link=true&dl=0';
        }

        if (!$link) {
            try {
                $graph = new Graph();
                $graph->setAccessToken($onedriveconfig['state']->token->data->access_token);
                $response = $graph
                    ->createRequest('POST', '/me/drive/items/' . $id . '/createLink')
                    ->attachBody(array('type' => 'view', 'scope' => 'anonymous'))
                    ->setReturnType(Model\Permission::class)// phpcs:ignore PHPCompatibility.Constants.NewMagicClassConstant.Found -- Use to sets the return type of the response object
                    ->execute();
                $links = $response->getLink();
                return $links->getWebUrl() . '?download=1';
            } catch (Exception $e) {
                $link = false;
            }
        }

        return $link;
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
        $params = get_option('_wpmfAddon_onedrive_config');
        if (empty($params['connected']) || empty($params['onedriveBaseFolder']['id'])) {
            wp_send_json(array('status' => false));
        }
        // add to queue
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
        if (empty($params['connected']) || empty($params['onedriveBaseFolder']['id'])) {
            return;
        }
        $datas = array(
            'id' => $params['onedriveBaseFolder']['id'],
            'folder_parent' => 0,
            'name' => 'Onedrive',
            'action' => 'wpmf_sync_onedrive',
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
                    'value'     => 'onedrive',
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
     * Do upload File
     *
     * @param string $filePath   File path
     * @param string $parentPath Cloud parent path
     * @param string $name       File name
     * @param string $action     Action
     *
     * @return mixed
     */
    public function doUploadFile($filePath, $parentPath, $name, $action = 'upload')
    {
        try {
            $content = file_get_contents($filePath);
            $onedriveconfig = get_option('_wpmfAddon_onedrive_config');
            $graph = new Graph();
            $graph->setAccessToken($onedriveconfig['state']->token->data->access_token);
            $res = $graph
                ->createRequest('POST', '/me' . $parentPath . '/' . $name . ':/createUploadSession')
                ->setReturnType(UploadSession::class)// phpcs:ignore PHPCompatibility.Constants.NewMagicClassConstant.Found -- Use to sets the return type of the response object
                ->execute();

            $uploadUrl = $res->getUploadUrl();
            $fragSize = 1024 * 5 * 1024;
            $fileSize = strlen($content);
            $numFragments = ceil($fileSize / $fragSize);
            $bytesRemaining = $fileSize;
            $i = 0;
            $ch = curl_init($uploadUrl);
            while ($i < $numFragments) {
                set_time_limit(60);
                $chunkSize = $fragSize;
                $numBytes = $fragSize;
                $start = $i * $fragSize;
                $end = $i * $fragSize + $chunkSize - 1;
                $offset = $i * $fragSize;
                if ($bytesRemaining < $chunkSize) {
                    $chunkSize = $bytesRemaining;
                    $numBytes = $bytesRemaining;
                    $end = $fileSize - 1;
                }

                $stream = fopen($filePath, 'r');
                if ($stream) {
                    // get contents using offset
                    $data = stream_get_contents($stream, $chunkSize, $offset);
                    fclose($stream);
                }

                $content_range = ' bytes ' . $start . '-' . $end . '/' . $fileSize;
                $headers = array(
                    'Content-Length: ' . $numBytes,
                    'Content-Range:' . $content_range
                );

                curl_setopt($ch, CURLOPT_URL, $uploadUrl);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                $response_info = curl_exec($ch);
                curl_getinfo($ch);
                $bytesRemaining = $bytesRemaining - $chunkSize;
                $i++;

                if ($action === 'upload_from_library') {
                    $info_file = \GuzzleHttp\json_decode($response_info);
                    if (!empty($info_file->id)) {
                        return $info_file;
                    }
                }
            }
        } catch (Exception $ex) {
            return false;
        }

        return true;
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
                if ($cloud_type && $cloud_type === 'onedrive') {
                    try {
                        $filePath = get_attached_file($attachment_id);
                        $scaled = WpmfAddonHelper::fixImageOrientation(array('file' => $filePath));
                        $filePath = $scaled['file'];
                        if (file_exists($filePath)) {
                            $info = pathinfo($filePath);
                            // get client
                            $client = $this->getClient();
                            $onedriveconfig = get_option('_wpmfAddon_onedrive_config');
                            $graph = new Graph();
                            $graph->setAccessToken($onedriveconfig['state']->token->data->access_token);
                            $item = $graph
                                ->createRequest('GET', '/me/drive/items/' . $cloud_id)
                                ->setReturnType(Model\DriveItem::class)// phpcs:ignore PHPCompatibility.Constants.NewMagicClassConstant.Found -- Use to sets the return type of the response object
                                ->execute();

                            $parentPath = $item->getParentReference()->getPath() . '/' . $item->getName();
                            // upload attachment to cloud
                            $uploaded_file = $this->doUploadFile($filePath, $parentPath, $info['basename'], 'upload_from_library');
                            if (isset($uploaded_file->id)) {
                                // add attachment meta
                                global $wpdb;
                                add_post_meta($attachment_id, 'wpmf_drive_id', $uploaded_file->id);
                                add_post_meta($attachment_id, 'wpmf_drive_type', 'onedrive');

                                // update guid URL
                                $where = array('ID' => $attachment_id);
                                $link = $this->getLink($uploaded_file->id);
                                $wpdb->update($wpdb->posts, array('guid' => $link), $where);
                                unlink($filePath);

                                // add attachment metadata
                                $upload_path = wp_upload_dir();
                                $attached = trim($upload_path['subdir'], '/') . '/' . $uploaded_file->name;
                                $meta = array();
                                if (strpos($uploaded_file->file->mimeType, 'image') !== false) {
                                    if (isset($uploaded_file->image->width) && isset($uploaded_file->image->height)) {
                                        $meta['width'] = $uploaded_file->image->width;
                                        $meta['height'] = $uploaded_file->image->height;
                                    } else {
                                        list($width, $heigth) = wpmfGetImgSize($link);
                                        $meta['width'] = $width;
                                        $meta['height'] = $heigth;
                                    }

                                    $meta['file'] = $attached;
                                }

                                if (isset($uploaded_file->size)) {
                                    $meta['filesize'] = $uploaded_file->size;
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
            if (!empty($data)) {
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
                if ($cloud_type && $cloud_type === 'onedrive') {
                    $folder = $this->doCreateFolder($name, $cloud_id);
                    add_term_meta($folder_id, 'wpmf_drive_id', $folder->getId());
                    add_term_meta($folder_id, 'wpmf_drive_type', 'onedrive');
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
                if ($cloud_type && $cloud_type === 'onedrive') {
                    $config = get_option('_wpmfAddon_onedrive_config');
                    if ($config['onedriveBaseFolder']['id'] !== $cloud_id) {
                        $client = $this->getClient();
                        $client->deleteDriveItem($cloud_id);
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
                if ($cloud_type && $cloud_type === 'onedrive') {
                    $config = get_option('_wpmfAddon_onedrive_config');
                    if ($config['onedriveBaseFolder']['id'] !== $cloud_id) {
                        if (isset($name)) {
                            $params = array('name' => $name);
                        } else {
                            $params = array();
                        }

                        $client = $this->getClient();
                        $client->updateDriveItem($cloud_id, $params);
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
                if ($cloud_type && $cloud_type === 'onedrive') {
                    $config = get_option('_wpmfAddon_onedrive_config');
                    if ($config['onedriveBaseFolder']['id'] !== $cloud_id) {
                        $cloud_parentid = wpmfGetCloudFolderID($parent_id);
                        $client = $this->getClient();
                        // Set new parent for item
                        $client->moveDriveItem($cloud_id, $cloud_parentid);
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
                if ($cloud_type && $cloud_type === 'onedrive') {
                    $cloud_parentid = wpmfGetCloudFolderID($parent_id);
                    $client = $this->getClient();
                    // Set new parent for item
                    $client->moveDriveItem($cloud_id, $cloud_parentid);
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
                if ($cloud_type && $cloud_type === 'onedrive') {
                    $client = $this->getClient();
                    $client->deleteDriveItem($cloud_id);
                }
            }
        } catch (Exception $ex) {
            return false;
        }

        return true;
    }

    /**
     * Insert attachment
     *
     * @param array   $info        File info
     * @param array   $child       File details
     * @param integer $parent      Parent folder
     * @param array   $upload_path Upload path
     * @param string  $link        Link
     *
     * @return void
     */
    public function insertAttachment($info, $child, $parent, $upload_path, $link)
    {
        $attachment = array(
            'guid' => $link,
            'post_mime_type' => $child['file']['mimeType'],
            'post_title' => $info['filename'],
            'post_type' => 'attachment',
            'post_status' => 'inherit'
        );

        $attach_id = wp_insert_post($attachment);
        $attached = trim($upload_path['subdir'], '/') . '/' . $child['name'];
        wp_set_object_terms((int)$attach_id, (int)$parent, WPMF_TAXO);

        update_post_meta($attach_id, '_wp_attached_file', $attached);
        update_post_meta($attach_id, 'wpmf_size', $child['size']);
        update_post_meta($attach_id, 'wpmf_filetype', $info['extension']);
        update_post_meta($attach_id, 'wpmf_order', 0);
        update_post_meta($attach_id, 'wpmf_drive_id', $child['id']);
        update_post_meta($attach_id, 'wpmf_drive_type', 'onedrive');

        $meta = array();
        if (strpos($child['file']['mimeType'], 'image') !== false) {
            if (isset($child['image']['width']) && isset($child['image']['height'])) {
                $meta['width'] = $child['image']['width'];
                $meta['height'] = $child['image']['height'];
            } else {
                list($width, $heigth) = wpmfGetImgSize($link);
                $meta['width'] = $width;
                $meta['height'] = $heigth;
            }

            $meta['file'] = $attached;
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
            if (!$curent_parents) {
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
        $params = get_option('_wpmfAddon_onedrive_config');
        if (empty($params['connected']) || empty($params['onedriveBaseFolder']['id'])) {
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
        $configs = get_option('_wpmfAddon_onedrive_config');
        if (!empty($configs['connected']) && !empty($configs['onedriveBaseFolder']['id'])) {
            $datas = array(
                'id' => $configs['onedriveBaseFolder']['id'],
                'folder_parent' => 0,
                'name' => 'Onedrive',
                'action' => 'wpmf_sync_onedrive',
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
        $configs = get_option('_wpmfAddon_onedrive_config');
        if (empty($configs['connected'])) {
            return -1;
        }
        global $wpdb;
        $name = html_entity_decode($datas['name']);
        if ($datas['type'] === 'folder') {
            // check folder exists
            $row = $wpdb->get_row($wpdb->prepare('SELECT term_id, meta_value FROM ' . $wpdb->termmeta . ' WHERE meta_key = %s AND meta_value = %s', array('wpmf_drive_id', $datas['id'])));
            // if folder not exists
            if (!$row) {
                $inserted = wp_insert_term($name, WPMF_TAXO, array('parent' => (int)$datas['folder_parent']));
                if (is_wp_error($inserted)) {
                    $folder_id = (int)$inserted->error_data['term_exists'];
                } else {
                    $folder_id = (int)$inserted['term_id'];
                }
                if ($name === 'Onedrive' && (int)$datas['folder_parent'] === 0) {
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

            if (!empty($folder_id)) {
                $responses = array();
                $responses['folder_id'] = (int)$folder_id;
                update_term_meta($responses['folder_id'], 'wpmf_drive_type', 'onedrive');
                JUMainQueue::updateQueueTermMeta((int)$responses['folder_id'], (int)$element_id);
                JUMainQueue::updateResponses((int)$element_id, $responses);
                // find childs element to add to queue
                $this->addChildsToQueue($datas['id'], $folder_id);
            }
        } else {
            $upload_path = wp_upload_dir();
            $info = pathinfo($name);
            $row = $wpdb->get_row($wpdb->prepare('SELECT post_id, meta_value FROM ' . $wpdb->postmeta . ' WHERE meta_key = %s AND meta_value = %s', array('wpmf_drive_id', $datas['id'])));
            if (!$row) {
                $link = $this->getLink($datas['id']);
                if (!$link) {
                    return false;
                }
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
                update_post_meta($file_id, 'wpmf_drive_type', 'onedrive');

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
                $this->updateAttachment($info, $row->post_id, $datas['folder_parent']);
                $file = get_post($file_id);
                // update file URL
                if (strpos($file->guid, 'wpmf_onedrive_download') !== false && $configs['link_type'] === 'public') {
                    $link = $this->getLink($datas['id']);
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
        $client = $this->getClient();
        $params = get_option('_wpmfAddon_onedrive_config');
        $graph = new Graph();
        $graph->setAccessToken($params['state']->token->data->access_token);
        try {
            $contents = $graph
                ->createRequest('GET', '/me/drive/items/' . $folderID . '?expand=children(expand=thumbnails)')
                ->setReturnType(Model\DriveItem::class)// phpcs:ignore PHPCompatibility.Constants.NewMagicClassConstant.Found -- Use to sets the return type of the response object
                ->execute();
            $childs = $contents->getChildren();
        } catch (Exception $ex) {
            $error = true;
            $childs = array();
        }

        if ($error) {
            return;
        }

        // get folder childs list on cloud
        $cloud_folders_list = array();
        // get file childs list on cloud
        $cloud_files_list = array();
        // Create files in media library
        foreach ($childs as $child) {
            $datas = array(
                'id' => $child['id'],
                'folder_parent' => $folder_parent,
                'name' => mb_convert_encoding($child['name'], 'HTML-ENTITIES', 'UTF-8'),
                'action' => 'wpmf_sync_onedrive',
                'cloud_parent' => $folderID
            );

            if (!empty($child['file'])) {
                $cloud_files_list[] = $child['id'];
                $datas['type'] = 'file';
                $datas['file'] = array('mimeType' => $child['file']['mimeType']);
                $datas['image'] = array();
                $datas['size'] = $child['size'];
                if (strpos($child['file']['mimeType'], 'image') !== false && isset($child['image'])) {
                    $datas['image'] = $child['image'];
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
            'action' => 'wpmf_onedrive_remove',
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