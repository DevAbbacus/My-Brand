<?php
/*
  Plugin Name: WP Media folder Addon
  Plugin URI: http://www.joomunited.com
  Description: WP Media Addon adds cloud connectors to the WordPress Media library
  Author: Joomunited
  Version: 3.4.10
  Text Domain: wpmfAddon
  Domain Path: /languages
  Author URI: http://www.joomunited.com
  Licence : GNU General Public License version 2 or later; http://www.gnu.org/licenses/gpl-2.0.html
  Copyright : Copyright (C) 2014 JoomUnited (http://www.joomunited.com). All rights reserved.
 */
// Prohibit direct script loading
defined('ABSPATH') || die('No direct script access allowed!');
//Check plugin requirements
if (version_compare(PHP_VERSION, '5.6', '<')) {
    if (!function_exists('wpmfAddonDisablePlugin')) {
        /**
         * Deactivate plugin
         *
         * @return void
         */
        function wpmfAddonDisablePlugin()
        {
            /**
             * Filter check user capability to do an action
             *
             * @param boolean The current user has the given capability
             * @param string  Action name
             *
             * @return boolean
             *
             * @ignore Hook already documented
             */
            $wpmf_capability = apply_filters('wpmf_user_can', current_user_can('activate_plugins'), 'activate_plugins');
            if ($wpmf_capability && is_plugin_active(plugin_basename(__FILE__))) {
                deactivate_plugins(__FILE__);
                unset($_GET['activate']);
            }
        }
    }

    if (!function_exists('wpmfAddonShowError')) {
        /**
         * Show error
         *
         * @return void
         */
        function wpmfAddonShowError()
        {
            echo '<div class="error"><p><strong>WP Media Folder Addon</strong>
 need at least PHP 5.6 version, please update php before installing the plugin.</p></div>';
        }
    }

    //Add actions
    add_action('admin_init', 'wpmfAddonDisablePlugin');
    add_action('admin_notices', 'wpmfAddonShowError');

    //Do not load anything more
    return;
}

/**
 * Load Jutranslation
 *
 * @return void
 */
function wpmfAddonsInit()
{
    if (!class_exists('\Joomunited\WPMFADDON\JUCheckRequirements')) {
        require_once(trailingslashit(dirname(__FILE__)) . 'requirements.php');
    }

    if (class_exists('\Joomunited\WPMFADDON\JUCheckRequirements')) {
        // Plugins name for translate
        $args = array(
            'plugin_name' => esc_html__('WP Media Folder Addon', 'wpmfAddon'),
            'plugin_path' => wpmfAddons_getPath(),
            'plugin_textdomain' => 'wpmfAddon',
            'requirements' => array(
                'plugins' => array(
                    array(
                        'name' => 'WP Media Folder',
                        'path' => 'wp-media-folder/wp-media-folder.php',
                        'requireVersion' => '4.8.0'
                    )
                ),
                'php_version' => '5.6',
                'php_modules' => array(
                    'curl' => 'warning'
                )
            )
        );
        $wpmfCheck = call_user_func('\Joomunited\WPMFADDON\JUCheckRequirements::init', $args);

        if (!$wpmfCheck['success']) {
            // Do not load anything more
            unset($_GET['activate']);
            return;
        }
    }

    if (!get_option('wpmf_addon_version', false)) {
        add_option('wpmf_cloud_connection_notice', 1);
    }

    if (!get_option('wpmf_cloud_connection_notice', false)) {
        $dropbox_config = get_option('_wpmfAddon_dropbox_config');
        $google_config = get_option('_wpmfAddon_cloud_config');
        $onedrive_config = get_option('_wpmfAddon_onedrive_config');
        $onedrive_business_config = get_option('_wpmfAddon_onedrive_business_config');
        if (!empty($dropbox_config['dropboxToken'])
            || (!empty($google_config['googleCredentials']) && !empty($google_config['googleBaseFolder']))
            || (!empty($onedrive_config['connected']) && !empty($onedrive_config['onedriveBaseFolder']['id']))
            || (!empty($onedrive_business_config['connected']) && !empty($onedrive_business_config['onedriveBaseFolder']['id']))) {
            add_action('admin_notices', 'wpmfAddonShowCloudConnectionNotice');
        }
    }

    //JUtranslation
    add_filter('wpmf_get_addons', function ($addons) {
        $addon = new stdClass();
        $addon->main_plugin_file = __FILE__;
        $addon->extension_name = 'WP Media Folder Addon';
        $addon->extension_slug = 'wpmf-addon';
        $addon->text_domain = 'wpmfAddon';
        $addon->language_file = plugin_dir_path(__FILE__) . 'languages' . DIRECTORY_SEPARATOR . 'wpmfAddon-en_US.mo';
        $addons[$addon->extension_slug] = $addon;
        return $addons;
    });

    add_action('init', function () {
        load_plugin_textdomain(
            'wpmfAddon',
            false,
            dirname(plugin_basename(__FILE__)) . DIRECTORY_SEPARATOR . 'languages' . DIRECTORY_SEPARATOR
        );
    }, 1);

    require_once(ABSPATH . 'wp-admin/includes/plugin.php');
    require_once(WPMFAD_PLUGIN_DIR . '/class/wpmfAddonGooglePhotoAdmin.php');
    $wpmfgooglephoto = new WpmfAddonGooglePhotoAdmin;
    require_once(WPMFAD_PLUGIN_DIR . '/class/wpmfAddonGoogleAdmin.php');
    $wpmfgoogleaddon = new WpmfAddonGoogle;
    require_once(WPMFAD_PLUGIN_DIR . '/class/wpmfAddonDropboxAdmin.php');
    $wpmfdropboxaddon = new WpmfAddonDropboxAdmin;
    require_once(WPMFAD_PLUGIN_DIR . '/class/wpmfAddonAws3Admin.php');
    $wpmfaws3addon = new WpmfAddonAws3Admin;
    require_once(WPMFAD_PLUGIN_DIR . '/class/wpmfAddonOneDriveBusinessAdmin.php');
    $wpmfonedrivebusinessaddon = new WpmfAddonOneDriveBusinessAdmin;
    require_once(WPMFAD_PLUGIN_DIR . '/class/wpmfAddonOneDriveAdmin.php');
    $wpmfonedriveaddon = new WpmfAddonOneDriveAdmin;
    require_once(WPMFAD_PLUGIN_DIR . '/class/wpmfHandleHooks.php');
    new WpmfHandleHooks;

    add_action('admin_init', 'wpmfAddonInit');
    add_action('wp_enqueue_scripts', 'wpmfAddonFrontStyle');

    // phpcs:disable WordPress.Security.NonceVerification.Recommended -- No action, nonce is not required
    if (isset($_GET['task']) && $_GET['task'] === 'wpmf') {
        if (isset($_GET['function'])) {
            switch ($_GET['function']) {
                case 'wpmf_authenticated':
                    $wpmfgoogleaddon->ggAuthenticated();
                    break;

                case 'wpmf_google_photo_authenticated':
                    $wpmfgoogleaddon->ggAuthenticated('google-photo');
                    break;

                case 'wpmf_gglogout':
                    $wpmfgoogleaddon->ggLogout();
                    break;

                case 'wpmf_google_photo_logout':
                    $wpmfgoogleaddon->ggLogout('google-photo');
                    break;

                case 'wpmf_dropboxlogout':
                    $wpmfdropboxaddon->dbxLogout();
                    break;
            }
        }
    } else {
        if (!empty($_GET['code']) && !empty($_GET['state'])) {
            global $pagenow;
            if ($pagenow === 'upload.php' && $_GET['state'] === 'wpmf-onedrive-business') {
                $wpmfonedrivebusinessaddon->createToken($_GET['code']);
            } elseif ($_GET['state'] === 'wpmf-onedrive') {
                $wpmfonedriveaddon->createToken($_GET['code']);
            }
        }
        // phpcs:enable
    }

    add_filter('wp_get_attachment_url', 'wpmfGetAttachmentUrl', 99, 2);
    add_filter('wp_prepare_attachment_for_js', 'wpmfGetAttachmentData', 10, 3);
    add_filter('wp_get_attachment_image_src', 'wpmfGetImgSrc', 10, 4);
    add_action('wp_ajax_wpmf_cloud_import', 'wpmfCloudImport');
    add_filter('cron_schedules', 'wpmfGetSchedules');
    add_action('wpmf_save_settings', 'wpmfRunCrontab');

    add_filter('wp_video_shortcode_override', 'wpmfVideoShortcodeOverride', 100, 4);
    /**
     * Filters the default video shortcode output.
     *
     * @param string  $html     Empty variable to be replaced with shortcode markup.
     * @param array   $atts     Attributes of the shortcode. @see wp_video_shortcode()
     * @param string  $content  Video shortcode content.
     * @param integer $instance Unique numeric ID of this video shortcode instance.
     *
     * @return string
     */
    function wpmfVideoShortcodeOverride($html, $atts, $content, $instance)
    {
        $url = '';
        if (!empty($atts['src'])) {
            $url = $atts['src'];
        } elseif (!empty($atts['mp4'])) {
            $url = $atts['mp4'];
        }
        
        if (strpos($url, 'drive.google.com/uc?id') !== false
            || strpos($url, 'api.onedrive.com') !== false
            || strpos($url, 'dropbox.com/s') !== false
            || strpos($url, 'wpmf-download-file') !== false
            || strpos($url, 'wpmf-dbxdownload-file') !== false
            || strpos($url, 'wpmf_onedrive_download') !== false
            || strpos($url, 'wpmf_onedrive_business_download') !== false) {
            $parts = parse_url($url);
            parse_str($parts['query'], $query);
            if ($url !== '' && isset($query['id']) && $query['id'] !== '') {
                return '<iframe src="https://drive.google.com/file/d/'. $query['id'] .'/preview" width="'. $atts['width'] .'" height="'. $atts['height'] .'"></iframe>';
            }
        }

        return $html;
    }

    if (is_admin()) {
        // Config section
        if (!defined('JU_BASE')) {
            define('JU_BASE', 'https://www.joomunited.com/');
        }

        $remote_updateinfo = JU_BASE . 'juupdater_files/wp-media-folder-addon.json';
        //end config
        require 'juupdater/juupdater.php';
        $UpdateChecker = Jufactory::buildUpdateChecker(
            $remote_updateinfo,
            __FILE__
        );
    }
}

require_once 'cloud-connector/CloudConnector.php';
$cloud_automatic = call_user_func(
    '\Joomunited\Cloud\WPMF\CloudConnector::getInstance',
    __FILE__,
    'wpmf',
    'WP Media Folder',
    'wpmfAddon'
);
$cloud_automatic->init();

register_deactivation_hook(__FILE__, 'wpmfAddondeactivation');

/**
 * Deactivate plugin
 *
 * @return void
 */
function wpmfAddondeactivation()
{
    wp_clear_scheduled_hook('wpmfSyncGoogle');
    wp_clear_scheduled_hook('wpmfSyncDropbox');
    wp_clear_scheduled_hook('wpmfSyncOnedrive');
    wp_clear_scheduled_hook('wpmfSyncOnedriveBusiness');
}

/**
 * Get plugin path
 *
 * @return string
 */
function wpmfAddons_getPath()
{
    if (!function_exists('plugin_basename')) {
        include_once(ABSPATH . 'wp-admin/includes/plugin.php');
    }

    return plugin_basename(__FILE__);
}

if (!defined('WPMFAD_PLUGIN_DIR')) {
    define('WPMFAD_PLUGIN_DIR', plugin_dir_path(__FILE__));
}

define('WPMFAD_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WPMFAD_URL', plugin_dir_url(__FILE__));
define('WPMFAD_VERSION', '3.4.10');

/**
 * Add cloud connection notice
 *
 * @return void
 */
function wpmfAddonShowCloudConnectionNotice()
{
    ?>
    <div class="error wpmf_cloud_connection_notice" id="wpmf_error">
        <p><?php esc_html_e('WP Media Folder plugin has updated its cloud connection system, it\'s now fully integrated in the media library. It requires to make a synchronization', 'wpmfAddon') ?>
            <button class="button button-primary btn-run-sync-cloud" style="margin: 0 5px;">
                <?php esc_html_e('RUN NOW', 'wpmfAddon') ?><span class="spinner spinner-cloud-sync"
                                                                 style="display:none; visibility:visible"></span>
            </button>
        </p>
    </div>
    <?php
}

/**
 * Init
 *
 * @return void
 */
function wpmfAddonInit()
{
    if (!get_option('_wpmfAddon_cloud_config', false)) {
        update_option('_wpmfAddon_cloud_config', array('link_type' => 'public'));
    }

    if (!get_option('_wpmfAddon_dropbox_config', false)) {
        update_option('_wpmfAddon_dropbox_config', array('link_type' => 'public'));
    }

    if (!get_option('_wpmfAddon_onedrive_config', false)) {
        update_option('_wpmfAddon_onedrive_config', array('link_type' => 'public'));
    }
}

/**
 * Load scripts and style
 *
 * @return void
 */
function wpmfAddonFrontStyle()
{
    wp_enqueue_style(
        'wpmfAddonFrontStyle',
        WPMFAD_PLUGIN_URL . '/assets/css/front.css',
        array(),
        WPMFAD_VERSION
    );
}

/**
 * Filters the image src result.
 *
 * @param array|false  $image         Either array with src, width & height, icon src, or false.
 * @param integer      $attachment_id Image attachment ID.
 * @param string|array $size          Size of image. Image size or array of width and height values
 *                                    (in that order). Default 'thumbnail'.
 * @param boolean      $icon          Whether the image should be treated as an icon. Default false.
 *
 * @return mixed
 */
function wpmfGetImgSrc($image, $attachment_id, $size, $icon)
{
    if (!$icon) {
        $drive_id = get_post_meta($attachment_id, 'wpmf_drive_id', true);
        if (!empty($drive_id)) {
            $post_url = wpmfGetDriveLink($attachment_id, $drive_id);
            $image[0] = $post_url;
        }
    }

    return $image;
}

/**
 * Get drive link
 *
 * @param integer $attachment_id Attachment ID
 * @param integer $drive_id      Drive ID
 *
 * @return string
 */
function wpmfGetDriveLink($attachment_id, $drive_id)
{
    $drive_post = get_post($attachment_id);
    $drive_type = get_post_meta($attachment_id, 'wpmf_drive_type', true);

    switch ($drive_type) {
        case 'onedrive_business':
            $post_url = str_replace('&amp;', '&', $drive_post->guid);
            break;

        case 'onedrive':
            $onedrive_config = get_option('_wpmfAddon_onedrive_config');
            if (isset($onedrive_config['link_type']) && $onedrive_config['link_type'] === 'private') {
                $post_url = admin_url('admin-ajax.php') . '?action=wpmf_onedrive_download&id=' . urlencode($drive_id) . '&link=true&dl=0';
            } else {
                $post_url = str_replace('&amp;', '&', $drive_post->guid);
            }
            break;

        case 'google_drive':
            $googleconfig = get_option('_wpmfAddon_cloud_config');
            if (isset($googleconfig['link_type']) && $googleconfig['link_type'] === 'private') {
                $post_url = admin_url('admin-ajax.php') . '?action=wpmf-download-file&id=' . urlencode($drive_id) . '&link=true&dl=0';
            } else {
                $post_url = str_replace('&amp;', '&', $drive_post->guid);
            }
            break;

        case 'dropbox':
            $dropboxconfig = get_option('_wpmfAddon_dropbox_config');
            if (isset($dropboxconfig['link_type']) && $dropboxconfig['link_type'] === 'private') {
                $post_url = admin_url('admin-ajax.php') . '?action=wpmf-dbxdownload-file&id=' . urlencode($drive_id) . '&link=true&dl=0';
            } else {
                $post_url = str_replace('&amp;', '&', $drive_post->guid);
                $post_url = str_replace('&#038;', '&', $post_url);
            }
            break;
    }

    return $post_url;
}

/**
 * Sync cloud files to media library
 *
 * @return void
 */
function wpmfCloudImport()
{
    if (empty($_REQUEST['wpmf_nonce'])
        || !wp_verify_nonce($_REQUEST['wpmf_nonce'], 'wpmf_nonce')) {
        die();
    }

    /**
     * Filter check capability of current user to import onedrive files
     *
     * @param boolean The current user has the given capability
     * @param string  Action name
     *
     * @return boolean
     *
     * @ignore Hook already documented
     */
    $wpmf_capability = apply_filters('wpmf_user_can', current_user_can('upload_files'), 'import_onedrive_files');
    if (!$wpmf_capability) {
        wp_send_json(array('status' => false));
    }
    if (isset($_POST['ids'])) {
        $ids = explode(',', $_POST['ids']);
        $term_id = (!empty($_POST['folder'])) ? $_POST['folder'] : 0;
        $i = 0;
        foreach ($ids as $k => $id) {
            $filepath = get_attached_file($id);
            $info = pathinfo($filepath);
            $filename = $info['basename'];
            $ext = $info['extension'];
            $cloud_id = wpmfGetCloudFileID($id);
            if (!$cloud_id) {
                continue;
            }
            if ($i >= 1) {
                wp_send_json(array('status' => true, 'continue' => true, 'ids' => implode(',', $ids))); // run again ajax
            } else {
                $status = false;
                $cloud_type = wpmfGetCloudFileType($id);
                if ($cloud_type === 'onedrive_business') {
                    $status = apply_filters('wpmf_onedrive_business_import', $cloud_id, $term_id, false, $filename, $ext);
                } elseif ($cloud_type === 'onedrive') {
                    $status = apply_filters('wpmf_onedrive_import', $cloud_id, $term_id, false, $filename, $ext);
                } elseif ($cloud_type === 'google_drive') {
                    $status = apply_filters('wpmf_google_import', $cloud_id, $term_id, false, $filename, $ext);
                } elseif ($cloud_type === 'dropbox') {
                    $status = apply_filters('wpmf_dropbox_import', $cloud_id, $term_id, false, $filename, $ext);
                }

                if ($status) {
                    unset($ids[$k]);
                    $i++;
                }
            }
        }
        wp_send_json(array('status' => true, 'continue' => false)); // run again ajax
    }
    wp_send_json(array('status' => false));
}

/**
 * Filters the attachment data prepared for JavaScript.
 *
 * @param array       $response   Array of prepared attachment data.
 * @param WP_Post     $attachment Attachment object.
 * @param array|false $meta       Array of attachment meta data, or false if there is none.
 *
 * @return mixed
 */
function wpmfGetAttachmentData($response, $attachment, $meta)
{
    $drive_id = get_post_meta($attachment->ID, 'wpmf_drive_id', true);
    if (!empty($drive_id)) {
        $post_url = wpmfGetDriveLink($attachment->ID, $drive_id);
        $response['url'] = $post_url;
        $attached_file = get_post_meta($attachment->ID, '_wp_attached_file', true);
        $response['filename'] = basename($attached_file);
    }

    return $response;
}

/**
 * Filters the attachment URL.
 *
 * @param string  $url           URL for the given attachment.
 * @param integer $attachment_id Attachment post ID.
 *
 * @return mixed
 */
function wpmfGetAttachmentUrl($url, $attachment_id)
{
    $drive_id = get_post_meta($attachment_id, 'wpmf_drive_id', true);
    if (!empty($drive_id)) {
        $post_url = wpmfGetDriveLink($attachment_id, $drive_id);
        return $post_url;
    }

    return $url;
}

/**
 * Add recurrences
 *
 * @param array $schedules Schedules
 *
 * @return mixed
 */
function wpmfGetSchedules($schedules)
{
    $method = wpmfGetOption('sync_method');
    $periodicity = wpmfGetOption('sync_periodicity');
    if ((int)$periodicity !== 0 && $method === 'crontab') {
        $schedules[$periodicity . 's'] = array('interval' => $periodicity, 'display' => $periodicity . 's');
    }
    return $schedules;
}

/**
 * CLear and add new crontab
 *
 * @return void
 */
function wpmfRunCrontab()
{
    $method = wpmfGetOption('sync_method');
    $periodicity = wpmfGetOption('sync_periodicity');
    $hooks = array('wpmfSyncOnedrive', 'wpmfSyncOnedriveBusiness', 'wpmfSyncDropbox', 'wpmfSyncGoogle');
    if ($method === 'crontab' && (int)$periodicity !== 0) {
        foreach ($hooks as $synchook) {
            wp_clear_scheduled_hook($synchook);
            if (!wp_next_scheduled($synchook)) {
                wp_schedule_event(time(), $periodicity . 's', $synchook);
            }
        }
    } else {
        foreach ($hooks as $synchook) {
            wp_clear_scheduled_hook($synchook);
        }
    }
}

/**
 * Check sync cloud continue
 *
 * @param array $options Option list
 *
 * @return boolean
 */
function wpmfCheckSyncNextCloud($options)
{
    foreach ($options as $option) {
        $check = get_option($option);
        if (!empty($check)) {
            return true;
        }
    }

    return false;
}

/**
 * Get Image Size
 *
 * @param string $url     URL of image
 * @param string $referer Referer
 *
 * @return array
 */
function wpmfGetImgSize($url, $referer = '')
{
    // Set headers
    $headers = array('Range: bytes=0-131072');
    if (!empty($referer)) {
        array_push($headers, 'Referer: ' . $referer);
    }

    // Get remote image
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    $data = curl_exec($ch);
    $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_errno = curl_errno($ch);
    curl_close($ch);

    // Get network stauts
    if ((int) $http_status !== 200) {
        return array(0, 0);
    }

    // Process image
    $image = imagecreatefromstring($data);
    $dims = array(imagesx($image), imagesy($image));
    imagedestroy($image);

    return $dims;
}
