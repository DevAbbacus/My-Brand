<?php
/* Prohibit direct script loading */
defined('ABSPATH') || die('No direct script access allowed!');

/**
 * Class WpmfHandleHooks
 * This class that holds most of the admin functionality
 */
class WpmfHandleHooks
{
    /**
     * WpmfAddonOneDriveAdmin constructor.
     */
    public function __construct()
    {
        if (!get_option('wpmf_cloud_name_syncing', false)) {
            add_option('wpmf_cloud_name_syncing', '', '', 'yes');
        }

        add_action('admin_enqueue_scripts', array($this, 'wpmfAddonLoadAutoSyncCloudScript'));
        add_action('wp_ajax_wpmf_update_cloud_last_sync', array($this, 'updateCloudLastSync'));
        add_action('wp_ajax_wpmf_get_cloud_syncing', array($this, 'getCloudSyncing'));
    }

    /**
     * Get cloud syncing
     *
     * @return void
     */
    public function getCloudSyncing()
    {
        if (empty($_POST['wpmf_nonce'])
            || !wp_verify_nonce($_POST['wpmf_nonce'], 'wpmf_nonce')) {
            die();
        }

        $cloudNameSyncing = get_option('wpmf_cloud_name_syncing');
        wp_send_json(array('status' => true, 'cloud' => $cloudNameSyncing));
    }

    /**
     * Load auto sync cloud script
     *
     * @return void
     */
    public function wpmfAddonLoadAutoSyncCloudScript()
    {
        // check cloud connected
        $dropbox_config = get_option('_wpmfAddon_dropbox_config');
        $google_config = get_option('_wpmfAddon_cloud_config');
        $onedrive_config = get_option('_wpmfAddon_onedrive_config');
        $onedrive_business_config = get_option('_wpmfAddon_onedrive_business_config');
        if (!empty($dropbox_config['dropboxToken'])
            || (!empty($google_config['googleCredentials']) && !empty($google_config['googleBaseFolder']))
            || (!empty($onedrive_config['connected']) && !empty($onedrive_config['onedriveBaseFolder']['id']))
            || (!empty($onedrive_business_config['connected']) && !empty($onedrive_business_config['onedriveBaseFolder']['id']))) {
            // check sync method to run ajax
            $sync_method = wpmfGetOption('sync_method');
            // remove curl mothod, so we use ajax method
            if ($sync_method === 'curl') {
                $sync_method = 'ajax';
            }
            $sync_periodicity = wpmfGetOption('sync_periodicity');
            $last_sync = get_option('wpmf_cloud_time_last_sync');
            if (empty($last_sync)) {
                add_option('wpmf_cloud_time_last_sync', time());
                $last_sync = get_option('wpmf_cloud_time_last_sync');
            }

            $cloudNameSyncing = get_option('wpmf_cloud_name_syncing');
            wp_enqueue_script(
                'wpmfAutoSyncClouds',
                WPMFAD_PLUGIN_URL . 'assets/js/sync_clouds.js',
                array('jquery'),
                WPMFAD_VERSION
            );

            wp_localize_script('wpmfAutoSyncClouds', 'wpmfAutoSyncClouds', array(
                'vars' => array(
                    'last_sync' => (int) $last_sync,
                    'sync_method' => $sync_method,
                    'sync_periodicity' => (int) $sync_periodicity,
                    'cloudNameSyncing' => $cloudNameSyncing,
                    'wpmf_nonce' => wp_create_nonce('wpmf_nonce')
                ),
                'l18n' => array(
                    'hover_cloud_syncing' => esc_html__('Cloud syncing on the way', 'wpmfAddon'),
                    'sync_all_clouds_notice'  => __('Please wait while WP Media Folder is syncing the cloud files', 'wpmfAddon')
                )
            ));
        }
    }

    /**
     * Update cloud last sync
     *
     * @return void
     */
    public function updateCloudLastSync()
    {
        if (empty($_POST['wpmf_nonce'])
            || !wp_verify_nonce($_POST['wpmf_nonce'], 'wpmf_nonce')) {
            die();
        }

        $time = time();
        if (!get_option('wpmf_cloud_time_last_sync', false)) {
            add_option('wpmf_cloud_time_last_sync', $time);
        } else {
            update_option('wpmf_cloud_time_last_sync', $time);
        }

        if (!get_option('wpmf_cloud_connection_notice', false)) {
            add_option('wpmf_cloud_connection_notice', 1);
        }

        update_option('wpmf_cloud_name_syncing', '');
        delete_option('wpmf_cloud_sync_token');
        delete_option('wpmf_cloud_sync_time');
        wp_send_json(array('status' => true, 'time' => $time));
    }
}
