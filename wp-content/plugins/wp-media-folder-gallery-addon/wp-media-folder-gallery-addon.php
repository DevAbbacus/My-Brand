<?php
/*
  Plugin Name: WP Media folder Gallery Addon
  Plugin URI: http://www.joomunited.com
  Description: WP Media Folder Gallery Addon enhances WPMF plugin by adding a full image gallery management
  Author: Joomunited
  Version: 2.1.12
  Author URI: http://www.joomunited.com
  Text Domain: wp-media-folder-gallery-addon
  Domain Path: /languages
  Licence : GNU General Public License version 2 or later; http://www.gnu.org/licenses/gpl-2.0.html
  Copyright : Copyright (C) 2014 JoomUnited (http://www.joomunited.com). All rights reserved.
 */
// Prohibit direct script loading
defined('ABSPATH') || die('No direct script access allowed!');

//Check plugin requirements
if (version_compare(PHP_VERSION, '5.6', '<')) {
    if (!function_exists('wpmfGalleryShowError')) {
        /**
         * Show notice
         *
         * @return void
         */
        function wpmfGalleryShowError()
        {
            echo '<div class="error"><p>';
            echo '<strong>WP Media Folder Gallery Addon</strong>';
            echo ' need at least PHP 5.6 version, please update php before installing the plugin.</p></div>';
        }
    }

    //Add actions
    add_action('admin_notices', 'wpmfGalleryShowError');
    //Do not load anything more
    return;
}
if (!defined('WPMF_GALLERY_ADDON_PLUGIN_DIR')) {
    /**
     * Path to WP Media Folder Gallery addon plugin
     */
    define('WPMF_GALLERY_ADDON_PLUGIN_DIR', plugin_dir_path(__FILE__));
}

if (!defined('WPMF_GALLERY_ADDON_PLUGIN_URL')) {
    /**
     * Url to WP Media Folder Gallery addon plugin
     */
    define('WPMF_GALLERY_ADDON_PLUGIN_URL', plugin_dir_url(__FILE__));
}

if (!defined('WPMF_GALLERY_ADDON_FILE')) {
    /**
     * Path to this file
     */
    define('WPMF_GALLERY_ADDON_FILE', __FILE__);
}

if (!defined('WPMF_GALLERY_ADDON_DOMAIN')) {
    /**
     * Text domain
     */
    define('WPMF_GALLERY_ADDON_DOMAIN', 'wp-media-folder-gallery-addon');
}

if (!defined('WPMF_GALLERY_ADDON_VERSION')) {
    /**
     * Plugin version
     */
    define('WPMF_GALLERY_ADDON_VERSION', '2.1.12');
}

if (!defined('WPMF_GALLERY_ADDON_TAXO')) {
    /**
     * Gallery taxonomy name
     */
    define('WPMF_GALLERY_ADDON_TAXO', 'wpmf-gallery-category');
}

//JUtranslation
add_filter('wpmf_get_addons', function ($addons) {
    $addon                          = new stdClass();
    $addon->main_plugin_file        = __FILE__;
    $addon->extension_name          = 'WP Media Folder Gallery Addon';
    $addon->extension_slug          = 'wpmf-gallery-addon';
    $addon->text_domain             = 'wp-media-folder-gallery-addon';
    $addon->language_file           = plugin_dir_path(__FILE__) . 'languages' . DIRECTORY_SEPARATOR . 'wp-media-folder-gallery-addon-en_US.mo';
    $addons[$addon->extension_slug] = $addon;
    return $addons;
});

/**
 * Load Jutranslation
 *
 * @return void
 */
function wpmfGalleryAddonsInit()
{
    if (!class_exists('\Joomunited\WPMFGALLERYADDON\JUCheckRequirements')) {
        require_once(trailingslashit(dirname(__FILE__)) . 'requirements.php');
    }

    if (class_exists('\Joomunited\WPMFGALLERYADDON\JUCheckRequirements')) {
        // Plugins name for translate
        $args = array(
            'plugin_name' => esc_html__('WP Media Folder Gallery Addon', 'wp-media-folder-gallery-addon'),
            'plugin_path' => 'wp-media-folder-gallery-addon/wp-media-folder-gallery-addon.php',
            'plugin_textdomain' => 'wp-media-folder-gallery-addon',
            'requirements' => array(
                'plugins'     => array(
                    array(
                        'name' => 'WP Media Folder',
                        'path' => 'wp-media-folder/wp-media-folder.php',
                        'requireVersion' => '4.7.2'
                    )
                ),
                'php_version' => '5.6'
            )
        );
        $wpmfCheck = call_user_func('\Joomunited\WPMFGALLERYADDON\JUCheckRequirements::init', $args);

        if (!$wpmfCheck['success']) {
            // Do not load anything more
            unset($_GET['activate']);
            return;
        }
    }
}

/**
 * Get plugin path
 *
 * @return string
 */
function wpmfGalleryAddons_getPath()
{
    return 'wp-media-folder-gallery-addon/wp-media-folder-gallery-addon.php';
}

include_once(ABSPATH . 'wp-admin/includes/plugin.php');

register_activation_hook(__FILE__, 'wpmfGalleryInstall');

/**
 * Add some options
 *
 * @return void
 */
function wpmfGalleryInstall()
{
    /* create number of items per page for image selection */
    if (!get_option('wpmf_gallery_img_per_page', false)) {
        update_option('wpmf_gallery_img_per_page', 20);
    }

    if (!get_option('wpmfgrl_relationships_media', false)) {
        add_option('wpmfgrl_relationships_media', array(), '', 'yes');
    }
}

/**
 * Sort parents before children
 * http://stackoverflow.com/questions/6377147/sort-an-array-placing-children-beneath-parents
 *
 * @param array   $objects List folder
 * @param array   $result  Result
 * @param integer $parent  Parent of folder
 * @param integer $depth   Depth of folder
 *
 * @return array           output
 */
function wpmfParentSort(array $objects, array &$result = array(), $parent = 0, $depth = 0)
{
    foreach ($objects as $key => $object) {
        $order = get_term_meta($object->term_id, 'wpmf_order', true);
        if (empty($order)) {
            $order = 0;
        }
        $object->order = $order;

        if ((int) $object->parent === (int) $parent) {
            $object->depth = $depth;
            array_push($result, $object);
            unset($objects[$key]);
            wpmfParentSort($objects, $result, $object->term_id, $depth + 1);
        }
    }
    return $result;
}

/**
 * Order attachment by order
 *
 * @param integer $a Item details
 * @param integer $b Item details
 *
 * @return mixed
 */
function wpmfSortByOrder($a, $b)
{
    return $a->order - $b->order;
}

/* Register WPMF_GALLERY_ADDON_TAXO taxonomy */
add_action('init', 'wpmfGalleryRegisterTaxonomy', 0);
/**
 * Register gallery taxonomy
 *
 * @return void
 */
function wpmfGalleryRegisterTaxonomy()
{
    if (!taxonomy_exists('wpmf-category')) {
        register_taxonomy(
            'wpmf-category',
            'attachment',
            array(
                'hierarchical' => true,
                'show_in_nav_menus' => false,
                'show_ui' => false,
                'public' => false,
                'labels' => array(
                    'name' => __('WPMF Categories', 'wp-media-folder-gallery-addon'),
                    'singular_name' => __('WPMF Category', 'wp-media-folder-gallery-addon'),
                    'menu_name' => __('WPMF Categories', 'wp-media-folder-gallery-addon'),
                    'all_items' => __('All WPMF Categories', 'wp-media-folder-gallery-addon'),
                    'edit_item' => __('Edit WPMF Category', 'wp-media-folder-gallery-addon'),
                    'view_item' => __('View WPMF Category', 'wp-media-folder-gallery-addon'),
                    'update_item' => __('Update WPMF Category', 'wp-media-folder-gallery-addon'),
                    'add_new_item' => __('Add New WPMF Category', 'wp-media-folder-gallery-addon'),
                    'new_item_name' => __('New WPMF Category Name', 'wp-media-folder-gallery-addon'),
                    'parent_item' => __('Parent WPMF Category', 'wp-media-folder-gallery-addon'),
                    'parent_item_colon' => __('Parent WPMF Category:', 'wp-media-folder-gallery-addon'),
                    'search_items' => __('Search WPMF Categories', 'wp-media-folder-gallery-addon'),
                )
            )
        );
    }

    /* get image term selection */
    $glr_selection = get_term_by('name', 'Gallery Upload', 'wpmf-category');
    if (!$glr_selection) {
        $inserted = wp_insert_term('Gallery Upload', 'wpmf-category', array());
        $relationships = array($inserted['term_id']);
        update_option('wpmfgrl_relationships', $relationships);
    }

    register_taxonomy(WPMF_GALLERY_ADDON_TAXO, 'attachment', array(
        'hierarchical' => true,
        'show_in_nav_menus' => false,
        'show_ui' => false,
        'public' => false,
        'labels' => array(
            'name' => __('WPMF Gallery Categories', 'wp-media-folder-gallery-addon'),
            'singular_name' => __('WPMF Gallery Category', 'wp-media-folder-gallery-addon'),
            'menu_name' => __('WPMF Gallery Categories', 'wp-media-folder-gallery-addon'),
            'all_items' => __('All WPMF Gallery Categories', 'wp-media-folder-gallery-addon'),
            'edit_item' => __('Edit WPMF Gallery Category', 'wp-media-folder-gallery-addon'),
            'view_item' => __('View WPMF Gallery Category', 'wp-media-folder-gallery-addon'),
            'update_item' => __('Update WPMF Gallery Category', 'wp-media-folder-gallery-addon'),
            'add_new_item' => __('Add New WPMF Gallery Category', 'wp-media-folder-gallery-addon'),
            'new_item_name' => __('New WPMF Gallery Category Name', 'wp-media-folder-gallery-addon'),
            'parent_item' => __('Parent WPMF Gallery Category', 'wp-media-folder-gallery-addon'),
            'parent_item_colon' => __('Parent WPMF Gallery Category:', 'wp-media-folder-gallery-addon'),
            'search_items' => __('Search WPMF Gallery Categories', 'wp-media-folder-gallery-addon'),
        ),
    ));
}

require_once(WPMF_GALLERY_ADDON_PLUGIN_DIR . 'admin/class/wp-media-folder-gallery-addon.php');
new WpmfGlrAddonAdmin;

require_once(WPMF_GALLERY_ADDON_PLUGIN_DIR . 'frontend/class/wp-media-folder-gallery-addon.php');
new WpmfGlrAddonFrontEnd;


/**
 * Load elementor widget
 *
 * @return void
 */
function wpmfGalleryAddonLoadElementorWidget()
{
    require_once(WPMF_GALLERY_ADDON_PLUGIN_DIR . 'elementor-widgets/class-gallery-elementor-widget.php');
    \Elementor\Plugin::instance()->widgets_manager->register_widget_type(new \WpmfGalleryAddonElementorWidget());
}

add_action('elementor/widgets/widgets_registered', 'wpmfGalleryAddonLoadElementorWidget');

/**
 * Enqueue script in divi gallery addon module
 *
 * @return void
 */
function wpmfInitGalleryAddonDivi()
{
    require_once(WPMF_GALLERY_ADDON_PLUGIN_DIR . 'frontend/class/wp-media-folder-gallery-addon.php');
    $gallery_addon = new WpmfGlrAddonFrontEnd;
    $gallery_addon->galleryScripts();
    $gallery_addon->enqueueScript('divi');
}

add_action('wpmf_init_gallery_addon_divi', 'wpmfInitGalleryAddonDivi');

if (is_admin()) {
    if (!defined('JU_BASE')) {
        /**
         * Joomunited site url
         */
        define('JU_BASE', 'https://www.joomunited.com/');
    }

    $remote_updateinfo = JU_BASE . 'juupdater_files/wp-media-folder-gallery-addon.json';
    //end config

    require 'juupdater/juupdater.php';
    $UpdateChecker = Jufactory::buildUpdateChecker(
        $remote_updateinfo,
        __FILE__
    );
}
