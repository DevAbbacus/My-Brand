<?php
/* Prohibit direct script loading */
defined('ABSPATH') || die('No direct script access allowed!');

/**
 * Class WpmfAddonHelper
 */
class WpmfAddonHelper
{

    /**
     * Get cloud configs
     *
     * @param string $type Google photo or google drive
     *
     * @return mixed
     */
    public static function getAllCloudConfigs($type = 'google-drive')
    {
        $default = array(
            'googleClientId'     => '',
            'googleClientSecret' => ''
        );

        if ($type === 'google-drive') {
            return get_option('_wpmfAddon_cloud_config', $default);
        } else {
            return get_option('_wpmfAddon_google_photo_config', $default);
        }
    }

    /**
     * Save cloud configs
     *
     * @param array  $data Data config
     * @param string $type Google photo or google drive
     *
     * @return boolean
     */
    public static function saveCloudConfigs($data, $type = 'google-drive')
    {
        if ($type === 'google-drive') {
            $result = update_option('_wpmfAddon_cloud_config', $data);
        } else {
            $result = update_option('_wpmfAddon_google_photo_config', $data);
        }

        return $result;
    }

    /**
     * Get all cloud configs
     *
     * @return mixed
     */
    public static function getAllCloudParams()
    {
        return get_option('_wpmfAddon_cloud_category_params');
    }

    /**
     * Set cloud configs
     *
     * @param array $cloudParams Cloud params
     *
     * @return boolean
     */
    public static function setCloudConfigsParams($cloudParams)
    {
        $result = update_option('_wpmfAddon_cloud_category_params', $cloudParams);
        return $result;
    }

    /**
     * Get google drive params
     *
     * @return mixed
     */
    public static function getGoogleDriveParams()
    {
        $params = self::getAllCloudParams();
        return isset($params['googledrive']) ? $params['googledrive'] : false;
    }

    /**
     * Save Cloud configs
     *
     * @param string       $key Key
     * @param string|array $val Value
     *
     * @return void
     */
    public static function setCloudParam($key, $val)
    {
        $params       = self::getAllCloudConfigs();
        $params[$key] = $val;
        self::saveCloudConfigs($params);
    }


    /**
     * Get termID
     *
     * @param string $googleDriveId Id of folder
     *
     * @return boolean
     */
    public static function getTermIdGoogleDriveByGoogleId($googleDriveId)
    {
        $returnData   = false;
        $googleParams = self::getGoogleDriveParams();
        if ($googleParams) {
            foreach ($googleParams as $key => $val) {
                if ($val['idCloud'] === $googleDriveId) {
                    $returnData = $val['termId'];
                }
            }
        }
        return $returnData;
    }

    /**
     * Get google drive data by term id
     *
     * @param integer $termId Term id
     *
     * @return boolean
     */
    public static function getGoogleDriveIdByTermId($termId)
    {
        $returnData   = false;
        $googleParams = self::getGoogleDriveParams();
        if ($googleParams) {
            foreach ($googleParams as $key => $val) {
                if ((int) $val['termId'] === (int) $termId) {
                    $returnData = $val['idCloud'];
                }
            }
        }
        return $returnData;
    }

    /**
     * Get category id by cloud ID
     *
     * @param string $cloud_id Cloud id
     *
     * @return boolean
     */
    public static function getCatIdByCloudId($cloud_id)
    {
        $returnData   = false;
        $googleParams = self::getGoogleDriveParams();
        if ($googleParams) {
            foreach ($googleParams as $key => $val) {
                if ($val['idCloud'] === $cloud_id) {
                    $returnData = $val['termId'];
                }
            }
        }
        return $returnData;
    }

    /**
     * Get all google drive id
     *
     * @return array
     */
    public static function getAllGoogleDriveId()
    {
        $returnData   = array();
        $googleParams = self::getGoogleDriveParams();
        if ($googleParams) {
            foreach ($googleParams as $key => $val) {
                $returnData[] = $val['idCloud'];
            }
        }
        return $returnData;
    }

    /**
     * Sync interval
     *
     * @return float
     */
    public static function curSyncInterval()
    {
        //get last_log param
        $config = self::getAllCloudConfigs();
        if (isset($config['last_log']) && !empty($config['last_log'])) {
            $last_log  = $config['last_log'];
            $last_sync = (int) strtotime($last_log);
        } else {
            $last_sync = 0;
        }

        $time_new     = (int) strtotime(date('Y-m-d H:i:s'));
        $timeInterval = $time_new - $last_sync;
        $curtime      = $timeInterval / 60;

        return $curtime;
    }

    /**
     * Get extension
     *
     * @param string $file File name
     *
     * @return string
     */
    public static function getExt($file)
    {
        $dot = strrpos($file, '.') + 1;

        return substr($file, $dot);
    }

    /**
     * Strips the last extension off of a file name
     *
     * @param string $file The file name
     *
     * @return string  The file name without the extension
     */
    public static function stripExt($file)
    {
        return preg_replace('#\.[^.]*$#', '', $file);
    }

    /*----------- Dropbox -----------------*/
    /**
     * Get all dropbox configs
     *
     * @return mixed
     */
    public static function getAllDropboxConfigs()
    {
        $default = array(
            'dropboxKey'        => '',
            'dropboxSecret'     => '',
            'dropboxSyncTime'   => '5',
            'dropboxSyncMethod' => 'sync_page_curl'
        );
        return get_option('_wpmfAddon_dropbox_config', $default);
    }

    /**
     * Save dropbox config
     *
     * @param array $data Data config
     *
     * @return boolean
     */
    public static function saveDropboxConfigs($data)
    {

        $result = update_option('_wpmfAddon_dropbox_config', $data);
        return $result;
    }

    /**
     * Get dropbox config
     *
     * @param string $name Dropbox name
     *
     * @return array|null
     */
    public static function getDataConfigByDropbox($name)
    {
        $DropboxParams = array();

        if (self::getAllDropboxConfigs()) {
            foreach (self::getAllDropboxConfigs() as $key => $val) {
                if (strpos($key, 'dropbox') !== false) {
                    $DropboxParams[$key] = $val;
                }
            }
            $result = null;
            switch ($name) {
                case 'dropbox':
                    $result = $DropboxParams;
                    break;
            }
            return $result;
        }
        return null;
    }

    /**
     * Set dropbox config
     *
     * @param array $dropboxParams Params of dropbox
     *
     * @return boolean
     */
    public static function setDropboxConfigsParams($dropboxParams)
    {
        $result = update_option('_wpmfAddon_dropbox_category_params', $dropboxParams);
        return $result;
    }

    /**
     * Get dropbox params
     *
     * @return mixed
     */
    public static function getDropboxParams()
    {
        return get_option('_wpmfAddon_dropbox_category_params', array());
    }

    /**
     * Get id by termID
     *
     * @param integer $termId Folder id
     *
     * @return boolean
     */
    public static function getDropboxIdByTermId($termId)
    {
        $returnData = false;
        $dropParams = self::getDropboxParams();
        if ($dropParams && isset($dropParams[$termId])) {
            $returnData = $dropParams[$termId]['idDropbox'];
        }
        return $returnData;
    }

    /**
     * Get dropbox folder id
     *
     * @param integer $termId Folder id
     *
     * @return boolean
     */
    public static function getIdFolderByTermId($termId)
    {
        $returnData = false;
        $dropParams = self::getDropboxParams();
        if ($dropParams && isset($dropParams[$termId])) {
            $returnData = $dropParams[$termId]['id'];
        }
        return $returnData;
    }

    /**
     * Get term id by Path
     *
     * @param string $path Path
     *
     * @return boolean|integer|string
     */
    public static function getTermIdByDropboxPath($path)
    {
        $dropbox_list = self::getDropboxParams();
        $result       = false;
        $path         = strtolower($path);
        if (!empty($dropbox_list)) {
            foreach ($dropbox_list as $k => $v) {
                if (strtolower($v['idDropbox']) === $path) {
                    $result = $k;
                }
            }
        }
        return $result;
    }

    /**
     * Get path by id
     *
     * @param string $id Dropbox file id
     *
     * @return boolean
     */
    public static function getPathByDropboxId($id)
    {
        $dropbox_list = self::getDropboxParams();
        $result       = false;
        if (!empty($dropbox_list)) {
            foreach ($dropbox_list as $k => $v) {
                if ($v['id'] === $id) {
                    $result = $v['idDropbox'];
                }
            }
        }

        return $result;
    }

    /**
     * Set dropbox file infos
     *
     * @param array $params Params
     *
     * @return boolean
     */
    public static function setDropboxFileInfos($params)
    {
        $result = update_option('_wpmfAddon_dropbox_fileInfo', $params);
        return $result;
    }

    /**
     * Get dropbox infos
     *
     * @return mixed
     */
    public static function getDropboxFileInfos()
    {
        return get_option('_wpmfAddon_dropbox_fileInfo');
    }

    /**
     * Sync interval dropbox
     *
     * @return float
     */
    public static function curSyncIntervalDropbox()
    {
        //get last_log param
        $config = self::getAllDropboxConfigs();
        if (isset($config['last_log']) && !empty($config['last_log'])) {
            $last_log  = $config['last_log'];
            $last_sync = (int) strtotime($last_log);
        } else {
            $last_sync = 0;
        }

        $time_new     = (int) strtotime(date('Y-m-d H:i:s'));
        $timeInterval = $time_new - $last_sync;
        $curtime      = $timeInterval / 60;
        return $curtime;
    }

    /**
     * Transfer iptc exif to image
     *
     * @param array   $image_info           Image info
     * @param string  $destination_image    Destination image
     * @param integer $original_orientation Original orientation
     *
     * @return boolean|integer
     */
    public static function transferIptcExifToImage($image_info, $destination_image, $original_orientation)
    {
        // Check destination exists
        if (!file_exists($destination_image)) {
            return false;
        }

        // Get EXIF data from the image info, and create the IPTC segment
        $exif_data = ((is_array($image_info) && key_exists('APP1', $image_info)) ? $image_info['APP1'] : null);
        if ($exif_data) {
            // Find the image's original orientation flag, and change it to 1
            // This prevents applications and browsers re-rotating the image, when we've already performed that function
            // @TODO I'm not sure this is the best way of changing the EXIF orientation flag, and could potentially affect
            // other EXIF data
            $exif_data = str_replace(chr(dechex($original_orientation)), chr(0x1), $exif_data);

            $exif_length = strlen($exif_data) + 2;
            if ($exif_length > 0xFFFF) {
                return false;
            }

            // Construct EXIF segment
            $exif_data = chr(0xFF) . chr(0xE1) . chr(($exif_length >> 8) & 0xFF) . chr($exif_length & 0xFF) . $exif_data;
        }

        // Get IPTC data from the source image, and create the IPTC segment
        $iptc_data = ((is_array($image_info) && key_exists('APP13', $image_info)) ? $image_info['APP13'] : null);
        if ($iptc_data) {
            $iptc_length = strlen($iptc_data) + 2;
            if ($iptc_length > 0xFFFF) {
                return false;
            }

            // Construct IPTC segment
            $iptc_data = chr(0xFF) . chr(0xED) . chr(($iptc_length >> 8) & 0xFF) . chr($iptc_length & 0xFF) . $iptc_data;
        }

        // Get the contents of the destination image
        $destination_image_contents = file_get_contents($destination_image);
        if (!$destination_image_contents) {
            return false;
        }
        if (strlen($destination_image_contents) === 0) {
            return false;
        }

        // Build the EXIF and IPTC data headers
        $destination_image_contents = substr($destination_image_contents, 2);
        $portion_to_add = chr(0xFF) . chr(0xD8); // Variable accumulates new & original IPTC application segments
        $exif_added = !$exif_data;
        $iptc_added = !$iptc_data;

        while ((substr($destination_image_contents, 0, 2) & 0xFFF0) === 0xFFE0) {
            $segment_length = (substr($destination_image_contents, 2, 2) & 0xFFFF);
            $iptc_segment_number = (substr($destination_image_contents, 1, 1) & 0x0F);   // Last 4 bits of second byte is IPTC segment #
            if ($segment_length <= 2) {
                return false;
            }

            $thisexistingsegment = substr($destination_image_contents, 0, $segment_length + 2);
            if ((1 <= $iptc_segment_number) && (!$exif_added)) {
                $portion_to_add .= $exif_data;
                $exif_added = true;
                if (1 === $iptc_segment_number) {
                    $thisexistingsegment = '';
                }
            }

            if ((13 <= $iptc_segment_number) && (!$iptc_added)) {
                $portion_to_add .= $iptc_data;
                $iptc_added = true;
                if (13 === $iptc_segment_number) {
                    $thisexistingsegment = '';
                }
            }

            $portion_to_add .= $thisexistingsegment;
            $destination_image_contents = substr($destination_image_contents, $segment_length + 2);
        }

        // Write the EXIF and IPTC data to the new file
        if (!$exif_added) {
            $portion_to_add .= $exif_data;
        }
        if (!$iptc_added) {
            $portion_to_add .= $iptc_data;
        }

        $output_file = fopen($destination_image, 'w');
        if ($output_file) {
            return fwrite($output_file, $portion_to_add . $destination_image_contents);
        }

        return false;
    }

    /**
     * Fix image orientation
     *
     * @param array $file File info
     *
     * @return mixed
     */
    public static function fixImageOrientation($file)
    {
        // Check we have a file
        if (!file_exists($file['file'])) {
            return $file;
        }

        // Attempt to read EXIF data from the image
        $exif_data = wp_read_image_metadata($file['file']);
        if (!$exif_data) {
            return $file;
        }

        // Check if an orientation flag exists
        if (!isset($exif_data['orientation'])) {
            return $file;
        }

        // Check if the orientation flag matches one we're looking for
        $required_orientations = array(8, 3, 6);
        if (!in_array($exif_data['orientation'], $required_orientations)) {
            return $file;
        }

        // If here, the orientation flag matches one we're looking for
        // Load the WordPress Image Editor class
        $image = wp_get_image_editor($file['file']);
        if (is_wp_error($image)) {
            // Something went wrong - abort
            return $file;
        }

        // Store the source image EXIF and IPTC data in a variable, which we'll write
        // back to the image once its orientation has changed
        // This is required because when we save an image, it'll lose its metadata.
        $source_size = getimagesize($file['file'], $image_info);
        // Depending on the orientation flag, rotate the image
        switch ($exif_data['orientation']) {

            /**
             * Rotate 90 degrees counter-clockwise
             */
            case 8:
                $image->rotate(90);
                break;

            /**
             * Rotate 180 degrees
             */
            case 3:
                $image->rotate(180);
                break;

            /**
             * Rotate 270 degrees counter-clockwise ($image->rotate always works counter-clockwise)
             */
            case 6:
                $image->rotate(270);
                break;
        }

        // Save the image, overwriting the existing image
        // This will discard the EXIF and IPTC data
        $image->save($file['file']);

        // Finally, return the data that's expected
        return $file;
    }
}
