<?php
/* Prohibit direct script loading */
defined('ABSPATH') || die('No direct script access allowed!');

/**
 * Class WpmfGlrAddonAdmin
 * This class that holds most of the admin functionality for WP Media Folder Gallery
 */
class WpmfGlrAddonAdmin
{
    /**
     * WpmfGlrAddonAdmin constructor.
     */
    public function __construct()
    {
        if (is_plugin_active('wp-media-folder/wp-media-folder.php')) {
            add_action('init', array($this, 'init'), 1);
            add_action('admin_init', array($this, 'setupTinyMce'));
            add_action('admin_menu', array($this, 'addMenuPage'));
            add_action('admin_enqueue_scripts', array($this, 'register'));
            add_action('enqueue_block_editor_assets', array($this, 'addEditorAssets'), 9999);
            add_action('wp_enqueue_media', array($this, 'postEnqueue'));
            add_action('media_upload_wpmfgallery', array($this, 'mediaUploadWpmfgallery'));
            add_filter('media_upload_tabs', array($this, 'addUploadTab'));
            add_filter('wpmfgallery_settings', array($this, 'gallerySettings'), 10, 1);
            add_filter('wpmfgallery_shortcode', array($this, 'renderGalleryShortcode'), 10, 1);
            add_action('wp_ajax_wpmfgallery', array($this, 'startProcess'));
            add_action('wp_ajax_wpmf_load_gallery_html', array($this, 'loadGalleryHtml'));
        }
    }

    /**
     * Load plugin text domain
     *
     * @return void
     */
    public function init()
    {
        load_plugin_textdomain(
            'wp-media-folder-gallery-addon',
            false,
            dirname(plugin_basename(WPMF_GALLERY_ADDON_FILE)) . '/languages/'
        );
    }

    /**
     * Run ajax
     *
     * @return void
     */
    public function startProcess()
    {
        if (empty($_POST['wpmf_gallery_nonce'])
            || !wp_verify_nonce($_POST['wpmf_gallery_nonce'], 'wpmf_gallery_nonce')) {
            die();
        }

        if (isset($_REQUEST['task'])) {
            switch ($_REQUEST['task']) {
                case 'change_gallery':
                    $this->changeGallery();
                    break;
                case 'get_library_tree':
                    $this->getLibraryTree();
                    break;
                case 'import_images_from_wp':
                    $this->importImagesFromWp();
                    break;
                case 'create_gallery':
                    $this->createGallery();
                    break;
                case 'delete_gallery':
                    $this->deleteGallery();
                    break;
                case 'edit_gallery':
                    $this->editGallery();
                    break;
                case 'delete_imgs_selected':
                    $this->deleteImgsSelected();
                    break;
                case 'image_details':
                    $this->imageDetails();
                    break;
                case 'image_selection_delete':
                    $this->imageSelectionDelete();
                    break;
                case 'update_image':
                    $this->updateImage();
                    break;
                case 'gallery_uploadfile':
                    $this->galleryUploadFile();
                    break;
                case 'get_imgselection':
                    $this->getImgSelectionNav();
                    break;
                case 'update_img_per_page':
                    $this->updateImgPerpage();
                    break;
                case 'update_parent_gallery':
                    $this->updateParentGallery();
                    break;
                case 'reorder_image_gallery':
                    $this->reorderFile();
                    break;
                case 'reordergallery':
                    $this->reorderGallery();
                    break;
                case 'get-insert-wpmfcategories':
                    $this->getInsertWpmfCategories();
                    break;
                case 'update-wpmfgallery-categories':
                    $this->updateWpmfGalleryCategories();
                    break;
            }
        }
    }

    /**
     * Import WPMF categories to Gallery
     *
     * @return void
     */
    public function getInsertWpmfCategories()
    {
        if (empty($_POST['wpmf_gallery_nonce'])
            || !wp_verify_nonce($_POST['wpmf_gallery_nonce'], 'wpmf_gallery_nonce')) {
            die();
        }

        global $wpdb;
        if (!get_option('wpmf_categories_list', false)) {
            add_option('wpmf_categories_list', array('0' => 0));
        }
        if (!empty($_POST['first'])) {
            $termsRel = array('0' => 0);
        } else {
            $termsRel = get_option('wpmf_categories_list', true);
        }
        $paged = (isset($_POST['paged'])) ? (int) $_POST['paged'] : 1;
        $limit = 30;
        $offset = ($paged - 1) * $limit;
        $ids = (isset($_POST['ids'])) ? $_POST['ids'] : '';
        $theme = $this->getTheme($_POST['theme']);
        // if not selected then stop
        if (empty($ids)) {
            wp_send_json(array('status' => true, 'continue' => false));
        }

        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Variable has been prepare
        $wpmf_categories = $wpdb->get_results($wpdb->prepare('SELECT * FROM ' . $wpdb->terms . ' as t INNER JOIN ' . $wpdb->term_taxonomy . ' AS tt ON tt.term_id = t.term_id WHERE taxonomy = %s AND t.term_id IN ('. $ids .') LIMIT %d OFFSET %d', array(WPMF_TAXO, (int) $limit, (int) $offset)));
        if (empty($wpmf_categories)) {
            wp_send_json(array('status' => true, 'continue' => false));
        }

        $galleries = get_option('wpmf_galleries');
        foreach ($wpmf_categories as $wpmf_category) {
            $inserted = wp_insert_term(
                $wpmf_category->name,
                WPMF_GALLERY_ADDON_TAXO,
                array('slug' => wp_unique_term_slug($wpmf_category->slug, $wpmf_category))
            );
            if (!is_wp_error($inserted)) {
                $termsRel[$wpmf_category->term_id] = array('id' => $inserted['term_id'], 'name' => $wpmf_category->name, 'term_parent' => $wpmf_category->parent);
                if (empty($galleries) && !is_array($galleries)) {
                    $galleries = array();
                    $galleries[$inserted['term_id']] = array(
                        'gallery_id' => $inserted['term_id'],
                        'theme' => $theme
                    );
                } else {
                    $galleries[$inserted['term_id']] = array(
                        'gallery_id' => $inserted['term_id'],
                        'theme' => $theme
                    );
                }
                update_term_meta((int) $inserted['term_id'], 'wpmf_theme', $theme);
                /* set option wpmf_galleries to relative gallery id with theme */
                update_option('wpmf_galleries', $galleries);
            }
        }
        update_option('wpmf_categories_list', $termsRel);
        wp_send_json(array('status' => true, 'continue' => true));
    }

    /**
     * Update parent for new imported folder from WPMF category
     *
     * @return void
     */
    public function updateWpmfGalleryCategories()
    {
        if (empty($_POST['wpmf_gallery_nonce'])
            || !wp_verify_nonce($_POST['wpmf_gallery_nonce'], 'wpmf_gallery_nonce')) {
            die();
        }

        $termsRel = get_option('wpmf_categories_list', true);
        $paged = (isset($_POST['paged'])) ? (int) $_POST['paged'] : 1;
        $limit = 5;
        $offset = ($paged - 1) * $limit;
        $categories = array_slice($termsRel, $offset, $limit, true);
        if (empty($categories)) {
            update_option('wpmf_categories_list', array('0' => 0));
            wp_send_json(array('status' => true, 'continue' => false));
        }

        global $wpdb;
        foreach ($categories as $term_id => $category) {
            wp_update_term($termsRel[$term_id]['id'], WPMF_GALLERY_ADDON_TAXO, array('parent' => (int) $termsRel[$category['term_parent']]['id']));
            // add attachment to folder
            $attachments = $wpdb->get_results($wpdb->prepare('SELECT * FROM ' . $wpdb->term_relationships . ' WHERE term_taxonomy_id = %d', array((int) $term_id)));
            foreach ($attachments as $attachment) {
                wp_set_object_terms($attachment->object_id, $termsRel[$term_id]['id'], WPMF_GALLERY_ADDON_TAXO, true);
            }
        }
        wp_send_json(array('status' => true, 'continue' => true));
    }

    /**
     * Customize Tiny MCE Editor
     *
     * @return void
     */
    public function setupTinyMce()
    {
        /**
         * Filter check capability of current user to edit posts
         *
         * @param boolean The current user has the given capability
         * @param string  Action name
         *
         * @return boolean
         *
         * @ignore Hook already documented
         */
        $wpmf_capability = apply_filters('wpmf_user_can', current_user_can('edit_posts'), 'edit_posts');

        /**
         * Filter check capability of current user to edit pages
         *
         * @param boolean The current user has the given capability
         * @param string  Action name
         *
         * @return boolean
         *
         * @ignore Hook already documented
         */
        $wpmf_capability_1 = apply_filters('wpmf_user_can', current_user_can('edit_pages'), 'edit_pages');

        if ($wpmf_capability && $wpmf_capability_1) {
            add_filter('mce_external_plugins', array($this, 'filterMcePlugin'));
            add_filter('mce_css', array($this, 'pluginMceCss'));
        }
    }

    /**
     * Load external TinyMCE plugins.
     *
     * @param array $plugins List TinyMCE plugins
     *
     * @return mixed
     */
    public function filterMcePlugin($plugins)
    {
        $plugins['wpmfglr'] = plugins_url('assets/js/tmce_plugin.js', WPMF_GALLERY_ADDON_FILE);
        return $plugins;
    }

    /**
     * Load tinyMCE plugin css
     *
     * @param string $mce_css Css
     *
     * @return string
     */
    public function pluginMceCss($mce_css)
    {
        if (!empty($mce_css)) {
            $mce_css .= ',';
        }
        $mce_css .= plugins_url('assets/css/tmce_plugin.css', WPMF_GALLERY_ADDON_FILE);
        return $mce_css;
    }

    /**
     * Add a tab to media menu in iframe
     *
     * @param array $tabs An array of media tabs
     *
     * @return array
     */
    public function addUploadTab($tabs)
    {
        global $current_screen;
        if (!method_exists($current_screen, 'is_block_editor') || !$current_screen->is_block_editor()) {
            $newtab = array('wpmfgallery' => __('WP Media Folder Gallery', 'wp-media-folder-gallery-addon'));
            return array_merge($tabs, $newtab);
        }

        return $tabs;
    }

    /**
     * Create iframe
     *
     * @return void
     */
    public function mediaUploadWpmfgallery()
    {
        $errors = false;
        wp_iframe(array($this, 'mediaUploadWpmfgalleryForm'), $errors);
    }

    /**
     * Load html iframe
     *
     * @return void
     */
    public function mediaUploadWpmfgalleryForm()
    {
        $this->enqueue();
        $type = 'iframe';
        require_once(WPMF_GALLERY_ADDON_PLUGIN_DIR . '/admin/pages/gallerylists.php');
        if (!class_exists('_WP_Editors', false)) {
            require_once ABSPATH . 'wp-includes/class-wp-editor.php';
            _WP_Editors::wp_link_dialog();
        }
    }

    /**
     * Load scripts
     *
     * @return void
     */
    public function postEnqueue()
    {
        wp_enqueue_script(
            'wpmf_btn_asgallery',
            WPMF_GALLERY_ADDON_PLUGIN_URL . 'assets/js/btn_save_asgallery.js',
            array('jquery'),
            WPMF_GALLERY_ADDON_VERSION
        );
        wp_localize_script('wpmf_btn_asgallery', 'wpmf_btn_asgallery', array(
            'btn_save_as_gallery' => __('Save as WPMF gallery', 'wp-media-folder-gallery-addon'),
            'new_gallery' => __('New gallery', 'wp-media-folder-gallery-addon'),
            'wpmf_gallery_nonce' => wp_create_nonce('wpmf_gallery_nonce'),
        ));
    }

    /**
     * Load scripts and style
     *
     * @return void
     */
    public function register()
    {
        global $pagenow;
        wp_register_script(
            'wordpresscanvas-imagesloaded',
            WPMF_PLUGIN_URL . '/assets/js/display-gallery/imagesloaded.pkgd.min.js',
            array(),
            '3.1.5',
            true
        );
        wp_register_script(
            'wpmf-galleryaddon-jquery-form',
            WPMF_PLUGIN_URL . 'assets/js/jquery.form.js',
            array('jquery'),
            WPMF_VERSION
        );

        wp_register_script(
            'wpmf-glraddon-popup',
            WPMF_PLUGIN_URL . '/assets/js/display-gallery/jquery.magnific-popup.min.js',
            array('jquery'),
            '0.9.9',
            true
        );

        wp_register_style(
            'wpmf-glraddon-popup-style',
            WPMF_PLUGIN_URL . '/assets/css/display-gallery/magnific-popup.css',
            array(),
            '0.9.9'
        );

        wp_register_style(
            'wpmf-import-gallery-style',
            WPMF_GALLERY_ADDON_PLUGIN_URL . '/assets/css/import-gallery.css',
            array(),
            WPMF_GALLERY_ADDON_VERSION
        );

        wp_register_script(
            'wpmf-glraddon-library_tree',
            WPMF_GALLERY_ADDON_PLUGIN_URL . '/assets/js/import-gallery.js',
            array('jquery'),
            WPMF_GALLERY_ADDON_VERSION
        );

        wp_register_script(
            'wpmf-glraddon-script',
            WPMF_GALLERY_ADDON_PLUGIN_URL . '/assets/js/script.js',
            array('jquery', 'plupload'),
            WPMF_GALLERY_ADDON_VERSION
        );

        wp_register_script(
            'wpmf-gallery-tree',
            WPMF_GALLERY_ADDON_PLUGIN_URL . '/assets/js/gallery_tree.js',
            array('jquery', 'wpmf-glraddon-script'),
            WPMF_GALLERY_ADDON_VERSION
        );

        wp_register_style(
            'wpmf-glraddon-style',
            WPMF_GALLERY_ADDON_PLUGIN_URL . '/assets/css/style.css',
            array(),
            WPMF_GALLERY_ADDON_VERSION
        );

        wp_register_style(
            'wpmf-glraddon-justyle',
            WPMF_GALLERY_ADDON_PLUGIN_URL . '/assets/css/justyle.css',
            array(),
            WPMF_GALLERY_ADDON_VERSION
        );

        wp_register_script(
            'wpmf-glraddon-qtip-js',
            WPMF_GALLERY_ADDON_PLUGIN_URL . '/assets/js/jquery.qtip.min.js',
            array('jquery'),
            WPMF_GALLERY_ADDON_VERSION,
            true
        );

        wp_register_style(
            'wpmf-glraddon-qtip-css',
            WPMF_GALLERY_ADDON_PLUGIN_URL . '/assets/css/jquery.qtip.css',
            array(),
            WPMF_GALLERY_ADDON_VERSION
        );
    }

    /**
     * Load scripts and style.
     *
     * @return void
     */
    public function enqueue()
    {
        wp_enqueue_media();
        wp_enqueue_script('jquery');
        wp_enqueue_script('jquery-ui-sortable');
        wp_enqueue_script('wpmf-galleryaddon-jquery-form');
        wp_enqueue_script('wordpresscanvas-imagesloaded');
        wp_enqueue_style(
            'wpmf-material-icon',
            'https://fonts.googleapis.com/css?family=Material+Icons|Material+Icons+Outlined'
        );

        wp_enqueue_script(
            'jQuery.fileupload',
            WPMF_GALLERY_ADDON_PLUGIN_URL . '/assets/fileupload/jquery.fileupload.js',
            array('jquery'),
            false,
            true
        );
        wp_enqueue_script(
            'jQuery.fileupload-process',
            WPMF_GALLERY_ADDON_PLUGIN_URL . '/assets/fileupload/jquery.fileupload-process.js',
            array('jquery'),
            false,
            true
        );

        wp_enqueue_style('wpmf-glraddon-justyle');
        wp_enqueue_style('wpmf-import-gallery-style');
        wp_enqueue_script('wpmf-glraddon-library_tree');
        wp_enqueue_script('wpmf-glraddon-script');
        wp_enqueue_script('wpmf-gallery-tree');
        wp_enqueue_style('wpmf-glraddon-style');
        wp_enqueue_script('wpmf-glraddon-popup');
        wp_enqueue_style('wpmf-glraddon-popup-style');
        wp_enqueue_script('wpmf-glraddon-qtip-js');
        wp_enqueue_style('wpmf-glraddon-qtip-css');
        wp_localize_script(
            'wpmf-glraddon-script',
            'wpmf_glraddon',
            $this->localizeScript()
        );

        if (isset($_GET['noheader'])) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- No action, nonce is not required
            wp_enqueue_style(
                'wpmf-glraddon-form',
                WPMF_GALLERY_ADDON_PLUGIN_URL . '/assets/css/form.css',
                array(),
                WPMF_VERSION
            );

            wp_enqueue_style(
                'wpmf-glraddon-common',
                WPMF_GALLERY_ADDON_PLUGIN_URL . '/assets/css/common.css',
                array(),
                WPMF_VERSION
            );
        }
    }

    /**
     * Enqueue styles and scripts for gutenberg
     *
     * @return void
     */
    public function addEditorAssets()
    {
        wp_enqueue_script('jquery-masonry');
        wp_enqueue_style(
            'wpmf-flexslider-style',
            WPMF_PLUGIN_URL . 'assets/css/display-gallery/flexslider.css',
            array(),
            '2.4.0'
        );

        wp_enqueue_script(
            'wordpresscanvas-imagesloaded',
            WPMF_PLUGIN_URL . '/assets/js/display-gallery/imagesloaded.pkgd.min.js',
            array(),
            '3.1.5',
            true
        );

        wp_enqueue_script(
            'wpmf-gallery-flexslider',
            WPMF_PLUGIN_URL . 'assets/js/display-gallery/flexslider/jquery.flexslider.js',
            array('jquery'),
            '2.0.0',
            true
        );

        wp_enqueue_style(
            'wpmf-gallery-style',
            WPMF_PLUGIN_URL . '/assets/css/display-gallery/style-display-gallery.css',
            array(),
            WPMF_VERSION
        );

        wp_enqueue_script(
            'wpmf-flipster-js',
            WPMF_GALLERY_ADDON_PLUGIN_URL . '/assets/js/jquery.flipster.js',
            array('jquery'),
            WPMF_GALLERY_ADDON_VERSION,
            true
        );

        wp_enqueue_style(
            'wpmf-flipster-css',
            WPMF_GALLERY_ADDON_PLUGIN_URL . '/assets/css/jquery.flipster.css',
            array(),
            WPMF_GALLERY_ADDON_VERSION
        );

        wp_enqueue_style(
            'wpmf-gallery-css',
            WPMF_GALLERY_ADDON_PLUGIN_URL . '/assets/css/gallery.css',
            array(),
            WPMF_GALLERY_ADDON_VERSION
        );

        wp_enqueue_style(
            'wpmf-jaofiletree',
            WPMF_PLUGIN_URL . '/assets/css/jaofiletree.css',
            array(),
            WPMF_VERSION
        );

        wp_enqueue_script(
            'wpmfgallery_blocks',
            WPMF_GALLERY_ADDON_PLUGIN_URL . '/assets/blocks/gallery/block.js',
            array('wp-blocks', 'wp-i18n', 'wp-element', 'wp-data', 'wp-editor'),
            WPMF_GALLERY_ADDON_VERSION
        );

        wp_enqueue_style(
            'wpmfgallery_blocks',
            WPMF_GALLERY_ADDON_PLUGIN_URL . '/assets/blocks/gallery/style.css',
            array(),
            WPMF_GALLERY_ADDON_VERSION
        );

        $gallery_configs = get_option('wpmf_gallery_settings');
        $themes_setting = get_option('wpmf_galleries');
        $sizes = apply_filters('image_size_names_choose', array(
            'thumbnail' => __('Thumbnail', 'wp-media-folder-gallery-addon'),
            'medium' => __('Medium', 'wp-media-folder-gallery-addon'),
            'large' => __('Large', 'wp-media-folder-gallery-addon'),
            'full' => __('Full Size', 'wp-media-folder-gallery-addon'),
        ));

        $sizes_value = json_decode(get_option('wpmf_gallery_image_size_value'));
        if (!empty($sizes_value)) {
            foreach ($sizes as $k => $size) {
                if (!in_array($k, $sizes_value)) {
                    unset($sizes[$k]);
                }
            }
        }

        $galleries = get_categories(
            array(
                'hide_empty' => false,
                'taxonomy' => WPMF_GALLERY_ADDON_TAXO,
                'pll_get_terms_not_translated' => 1
            )
        );

        $galleries = wpmfParentSort($galleries);
        $params = array(
            'l18n' => array(
                'btnopen' => __('Load WP Media Folder Gallery', 'wp-media-folder-gallery-addon'),
                'gallery_title' => __('WPMF Gallery Addon', 'wp-media-folder-gallery-addon'),
                'select_gallery_title' => __('Select or Create gallery', 'wp-media-folder-gallery-addon'),
                'edit' => __('Edit', 'wp-media-folder-gallery-addon'),
                'remove' => __('Remove', 'wp-media-folder-gallery-addon')
            ),
            'vars' => array(
                'admin_gallery_page' => admin_url('upload.php?page=media-folder-galleries&noheader=1&editor=gutenberg'),
                'gallery_configs' => $gallery_configs,
                'themes_setting' => $themes_setting,
                'sizes' => $sizes,
                'galleries' => $galleries,
                'wpmf_gallery_nonce' => wp_create_nonce('wpmf_gallery_nonce'),
                'block_cover' => WPMF_GALLERY_ADDON_PLUGIN_URL .'assets/blocks/gallery/preview.png',
                'ajaxurl' => admin_url('admin-ajax.php')
            )
        );

        wp_localize_script('wpmfgallery_blocks', 'wpmfgalleryblocks', $params);
    }

    /**
     * Get all gallery
     *
     * @return array
     */
    public function getAllGalleries()
    {
        $terms = get_categories(
            array(
                'hide_empty' => false,
                'taxonomy' => WPMF_GALLERY_ADDON_TAXO
            )
        );
        $terms = $this->parentSort($terms);
        $terms_order = array();
        $attachment_terms[] = array(
            'id' => 0,
            'label' => __('Galleries', 'wp-media-folder-gallery-addon'),
            'slug' => '',
            'parent_id' => 0
        );
        $terms_order[] = 0;

        foreach ($terms as $term) {
            $order = $this->getOrderGallery($term->term_id);
            $attachment_terms[$term->term_id] = array(
                'id' => $term->term_id,
                'label' => $term->name,
                'slug' => $term->slug,
                'parent_id' => $term->category_parent,
                'depth' => $term->depth,
                'order' => $order
            );
            $terms_order[] = $term->term_id;
        }

        return array(
            'terms_order' => $terms_order,
            'attachment_terms' => $attachment_terms
        );
    }

    /**
     * Localize a script
     *
     * @return array
     */
    public function localizeScript()
    {
        if (isset($_GET['gallery_id'])) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- No action, nonce is not required
            $gallery_id = (int)$_GET['gallery_id'];
        } else {
            $gallery_id = 0;
        }

        // get all gallery
        $terms = $this->getAllGalleries();
        $attachment_terms = $terms['attachment_terms'];
        $terms_order = $terms['terms_order'];

        $themes = array(
            'default' => __('Default', 'wp-media-folder-gallery-addon'),
            'masonry' => __('Masonry', 'wp-media-folder-gallery-addon'),
            'portfolio' => __('Portfolio', 'wp-media-folder-gallery-addon'),
            'slider' => __('Slider', 'wp-media-folder-gallery-addon'),
            'flowslide' => __('Flow slide', 'wp-media-folder-gallery-addon'),
            'square_grid' => __('Square grid', 'wp-media-folder-gallery-addon'),
            'material' => __('Material', 'wp-media-folder-gallery-addon')
        );

        $l18n = array(
            'root_title' => __('Galleries', 'wp-media-folder-gallery-addon'),
            'create_gallery_desc' => __('Select a folder to create gallery
             and sub-galleries', 'wp-media-folder-gallery-addon'),
            'cancel' => __('Cancel', 'wp-media-folder-gallery-addon'),
            'delete' => __('Delete', 'wp-media-folder-gallery-addon'),
            'create' => __('Create', 'wp-media-folder-gallery-addon'),
            'theme_label' => __('Gallery Theme', 'wp-media-folder-gallery-addon'),
            'select_theme_label' => __('Apply theme:', 'wp-media-folder-gallery-addon'),
            'iframe_import_label' => __('Select or upload image to import
             them to image gallery selection', 'wp-media-folder-gallery-addon'),
            'import' => __('Import images', 'wp-media-folder-gallery-addon'),
            'edit_gallery' => __('Edit gallery', 'wp-media-folder-gallery-addon'),
            'error' => __('Error', 'wp-media-folder-gallery-addon'),
            'save' => __('Save', 'wp-media-folder-gallery-addon'),
            'delete_image_gallery' => __('Are you sure to want to delete this image?', 'wp-media-folder-gallery-addon'),
            'delete_selected_image' => __('Are you sure to want
             to delete these images?', 'wp-media-folder-gallery-addon'),
            'delete_gallery' => __('Are you sure you want to remove this gallery?', 'wp-media-folder-gallery-addon'),
            'image_details' => __('Image Details', 'wp-media-folder-gallery-addon'),
            'add_gallery' => __('Gallery added', 'wp-media-folder-gallery-addon'),
            'save_img' => __('Images saved', 'wp-media-folder-gallery-addon'),
            'delete_img' => __('Images removed', 'wp-media-folder-gallery-addon'),
            'upload_img' => __('Images uploaded', 'wp-media-folder-gallery-addon'),
            'save_glr' => __('Gallery saved', 'wp-media-folder-gallery-addon'),
            'save_glr_modal' => __('Gallery saved: Insert to apply', 'wp-media-folder-gallery-addon'),
            'delete_glr' => __('Gallery removed', 'wp-media-folder-gallery-addon'),
            'new_gallery' => __('New gallery', 'wp-media-folder-gallery-addon'),
            'import_gallery' => __('Gallery import on the way...', 'wp-media-folder-gallery-addon'),
            'gallery_imported' => __('New gallery imported', 'wp-media-folder-gallery-addon'),
            'reordergallery' => __('New gallery order saved!', 'wp-media-folder-gallery-addon'),
            'gallery_saving' => __('Gallery saving', 'wp-media-folder-gallery-addon'),
            'maxNumberOfFiles' => __('Maximum number of files exceeded', 'wp-media-folder-gallery-addon'),
            'acceptFileTypes' => __('File type not allowed', 'wp-media-folder-gallery-addon'),
            'maxFileSize' => __('File is too large', 'wp-media-folder-gallery-addon'),
            'minFileSize' => __('File is too small', 'wp-media-folder-gallery-addon'),
            'uploading' => __('Uploading', 'wp-media-folder-gallery-addon'),
            'gallery_importing' => __('Gallery importing...', 'wp-media-folder-gallery-addon'),
            'folder_listing' => __('Folders listing...', 'wp-media-folder-gallery-addon'),
            'upload_error' => __('Post-processing of the image failed likely because the server is busy or does not have enough resources. Uploading a smaller image may help. Suggested maximum size is 2500 pixels.', 'wp-media-folder-gallery-addon')
        );

        $vars = array(
            'themes' => $themes,
            'gallery_id' => $gallery_id,
            'wpmf_gallery_nonce' => wp_create_nonce('wpmf_gallery_nonce'),
            'categories' => $attachment_terms,
            'categories_order' => $terms_order,
            'plugin_url_image' => WPMF_GALLERY_ADDON_PLUGIN_URL . 'assets/images/',
            'admin_url' => admin_url()
        );

        return array(
            'l18n' => $l18n,
            'vars' => $vars
        );
    }

    /**
     * Sort parents before children
     * http://stackoverflow.com/questions/6377147/sort-an-array-placing-children-beneath-parents
     *
     * @param array   $objects Input objects with attributes 'id' and 'parent'
     * @param array   $result  Optional, reference) internal
     * @param integer $parent  Parent of gallery
     * @param integer $depth   Depth of gallery
     *
     * @return array           output
     */
    public function parentSort(array $objects, array &$result = array(), $parent = 0, $depth = 0)
    {
        foreach ($objects as $key => $object) {
            if ((int)$object->parent === (int)$parent) {
                $object->depth = $depth;
                array_push($result, $object);
                unset($objects[$key]);
                $this->parentSort($objects, $result, $object->term_id, $depth + 1);
            }
        }
        return $result;
    }

    /**
     * Add menu media page
     *
     * @return void
     */
    public function addMenuPage()
    {
        add_media_page(
            'Media Folder Galleries',
            'Media Folder Galleries',
            'upload_files',
            'media-folder-galleries',
            array($this, 'showGalleryList')
        );
    }

    /**
     * Galleries list page
     *
     * @return void
     */
    public function showGalleryList()
    {
        if (version_compare(WPMF_VERSION, '4.4.2', '<') && WPMF_VERSION !== '2.1.12') {
            echo '<div class="error" id="wpmf_error">';
            echo '<p>';
            esc_html_e('Please update WP Media Folder to 4.4.2+ version
             to use WP Media Folder gallery addon', 'wp-media-folder-gallery-addon');
            echo '</p>';
            echo '</div>';
        } else {
            if (isset($_GET['noheader'])) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- No action, nonce is not required
                global $hook_suffix;
                _wp_admin_html_begin();
                do_action('admin_enqueue_scripts', $hook_suffix);
                do_action('admin_print_scripts-' . $hook_suffix);
                do_action('admin_print_scripts');
                $style = '
                    html.wp-toolbar {
                        padding: 0 !important;
                    }
                ';
                wp_add_inline_style('wpmf-glraddon-style', $style);
            }
            if (isset($_GET['view']) && $_GET['view'] === 'framemedia') { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- No action, nonce is not required
                if (isset($_GET['gallery_id'])) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- No action, nonce is not required
                    $gallery_id = $_GET['gallery_id']; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- No action, nonce is not required
                } else {
                    $gallery_id = 0;
                }

                $this->enqueue();
                wp_localize_script(
                    'wpmf-modal-import-js',
                    'wpmfmd_import',
                    array(
                        'current_site' => admin_url(),
                        'gallery_id' => $gallery_id
                    )
                );

                ?>
                <input type="button" class="ju-button btn_modal_import_image_fromwp wpmfstrtoupper"
                       value="<?php esc_html_e('Import from wordpress', 'wp-media-folder-gallery-addon') ?>">
                <?php
            } else {
                $this->enqueue();
                // phpcs:disable WordPress.Security.NonceVerification.Recommended -- No action, nonce is not required
                if (isset($_GET['noheader'])) {
                    $type = 'iframe';
                    if (isset($_GET['editor']) && $_GET['editor'] === 'gutenberg') {
                        $editor_type = 'wpmfgutenberg';
                    }
                } else {
                    $type = 'notiframe';
                }
                // phpcs:enable
                echo '<div class="first_bg_load" style="width: 100%;height: 100%;background: #fff;float: left;position: absolute;"></div>';
                require_once(WPMF_GALLERY_ADDON_PLUGIN_DIR . '/admin/pages/gallerylists.php');
            }
        }
    }

    /**
     * Render gallery shortcode settings
     *
     * @param string $html Current html
     *
     * @return string
     */
    public function renderGalleryShortcode($html)
    {
        wp_enqueue_script(
            'wpmf-glraddon-settings',
            WPMF_GALLERY_ADDON_PLUGIN_URL . '/assets/js/gallery_settings.js',
            array('jquery'),
            WPMF_GALLERY_ADDON_VERSION,
            true
        );

        wp_localize_script(
            'wpmf-glraddon-settings',
            'glraddon_settings',
            array(
                'l18n' => array(
                    'success_copy_shortcode' => __('Gallery shortcode copied!', 'wp-media-folder-gallery-addon'),
                ),
                'vars' => array()
            )
        );

        $shortcode_configs = wpmfGetOption('gallery_shortcode');
        ob_start();
        $lists_themes = array(
            'default_theme',
            'portfolio_theme',
            'masonry_theme',
            'slider_theme',
            'flowslide_theme',
            'square_grid_theme',
            'material_theme'
        );

        $params = array();
        foreach ($lists_themes as $key_theme) {
            $params[$key_theme] = $this->shortcodeSettings(
                $key_theme,
                $shortcode_configs
            );
        }

        foreach ($params as $attr_key => $attr_value) {
            ${$attr_key} = $attr_value;
        }
        require_once(WPMF_GALLERY_ADDON_PLUGIN_DIR . '/admin/pages/gallery_shortcode/render_gallery_shortcode.php');
        $html .= ob_get_contents();
        ob_end_clean();
        return $html;
    }

    /**
     * Gallery shortcode settings
     *
     * @param string $theme_name        Theme name
     * @param array  $shortcode_configs Shortcode settings
     *
     * @return string
     */
    public function shortcodeSettings($theme_name, $shortcode_configs)
    {
        ob_start();
        $settings = $shortcode_configs['theme'][$theme_name];
        require(WPMF_GALLERY_ADDON_PLUGIN_DIR . '/admin/pages/gallery_shortcode/shortcode_settings.php');
        $html = ob_get_contents();
        ob_end_clean();
        return $html;
    }

    /**
     * Gallery settings
     *
     * @param string $html Gallery html
     *
     * @return string
     */
    public function gallerySettings($html)
    {
        $gallery_configs = get_option('wpmf_gallery_settings');
        ob_start();
        $default_label = __('Default gallery theme', 'wp-media-folder-gallery-addon');
        $portfolio_label = __('Portfolio gallery theme', 'wp-media-folder-gallery-addon');
        $masonry_label = __('Masonry gallery theme', 'wp-media-folder-gallery-addon');
        $slider_label = __('Slider gallery theme', 'wp-media-folder-gallery-addon');
        $flowslide_label = __('Flow slide theme', 'wp-media-folder-gallery-addon');
        $square_grid_label = __('Square grid theme', 'wp-media-folder-gallery-addon');
        $material_label = __('Material theme', 'wp-media-folder-gallery-addon');

        $default_theme = $this->themeSettings(
            'default_theme',
            $gallery_configs,
            $default_label
        );
        $portfolio_theme = $this->themeSettings(
            'portfolio_theme',
            $gallery_configs,
            $portfolio_label
        );
        $masonry_theme = $this->themeSettings(
            'masonry_theme',
            $gallery_configs,
            $masonry_label
        );
        $slider_theme = $this->themeSettings(
            'slider_theme',
            $gallery_configs,
            $slider_label
        );
        $flowslide_theme = $this->themeSettings(
            'flowslide_theme',
            $gallery_configs,
            $flowslide_label
        );
        $square_grid_theme = $this->themeSettings(
            'square_grid_theme',
            $gallery_configs,
            $square_grid_label
        );
        $material_theme = $this->themeSettings(
            'material_theme',
            $gallery_configs,
            $material_label
        );
        require_once(WPMF_GALLERY_ADDON_PLUGIN_DIR . '/admin/pages/gallery_settings.php');
        $html .= ob_get_contents();
        ob_end_clean();
        return $html;
    }

    /**
     * Gallery settings
     *
     * @param string $theme_name      Theme name
     * @param array  $gallery_configs Gallery config
     * @param string $theme_label     Theme label
     *
     * @return string
     */
    public function themeSettings($theme_name, $gallery_configs, $theme_label)
    {
        ob_start();
        $settings = $gallery_configs['theme'][$theme_name];
        require(WPMF_GALLERY_ADDON_PLUGIN_DIR . '/admin/pages/theme_settings.php');
        $html = ob_get_contents();
        ob_end_clean();
        return $html;
    }

    /**
     * Get count image selection
     *
     * @param integer $gallery_id Id of gallery
     *
     * @return integer
     */
    public function getCountImageSelection($gallery_id)
    {
        $params = array(
            'taxonomy' => WPMF_GALLERY_ADDON_TAXO,
            'field' => 'term_id',
            'terms' => (int)$gallery_id,
            'include_children' => false
        );

        $args = array(
            'posts_per_page' => -1,
            'post_status' => 'any',
            'post_type' => array('attachment'),
            'tax_query' => array(
                $params
            )
        );
        $querycount = new WP_Query($args);
        $post_count = $querycount->post_count;
        return $post_count;
    }

    /**
     * Update image
     *
     * @return void
     */
    public function updateImage()
    {
        if (empty($_POST['wpmf_gallery_nonce'])
            || !wp_verify_nonce($_POST['wpmf_gallery_nonce'], 'wpmf_gallery_nonce')) {
            wp_send_json(
                array(
                    'status' => false
                )
            );
        }

        /**
         * Filter check capability of current user to update image in gallery
         *
         * @param boolean The current user has the given capability
         * @param string  Action name
         *
         * @return boolean
         *
         * @ignore Hook already documented
         */
        $wpmf_capability = apply_filters('wpmf_user_can', current_user_can('upload_files'), 'gallery_update_image');
        if (!$wpmf_capability) {
            wp_send_json(
                array(
                    'status' => false
                )
            );
        }

        if (isset($_POST['id'])) {
            $id = (int)$_POST['id'];
            // Update post
            $params = array(
                'ID' => $id,
                'post_title' => sanitize_text_field($_POST['title']),
                'post_excerpt' => sanitize_text_field($_POST['excerpt']),
                'post_content' => sanitize_text_field($_POST['content'])
            );

            // Update the post into the database
            wp_update_post($params);
            update_post_meta(
                $id,
                '_wp_attachment_image_alt',
                sanitize_text_field($_POST['alt'])
            );
            update_post_meta(
                $id,
                '_wpmf_gallery_custom_image_link',
                sanitize_text_field($_POST['link_to'])
            );
            update_post_meta(
                $id,
                '_gallery_link_target',
                sanitize_text_field($_POST['link_target'])
            );
            update_post_meta(
                $id,
                'wpmf_img_tags',
                trim(sanitize_text_field($_POST['img_tags']))
            );
            wp_send_json(array('status' => true));
        }
        wp_send_json(array('status' => false));
    }

    /**
     * Get image selection details
     *
     * @return void
     */
    public function imageDetails()
    {
        if (empty($_POST['wpmf_gallery_nonce'])
            || !wp_verify_nonce($_POST['wpmf_gallery_nonce'], 'wpmf_gallery_nonce')) {
            die();
        }

        if (isset($_POST['id'])) {
            ob_start();
            $id = (int)$_POST['id'];
            $details = get_post($id);
            if (empty($details)) {
                wp_send_json(array('status' => false, 'html' => ''));
            }
            $medium_url = wp_get_attachment_image_src($id, 'medium');
            $alt = get_post_meta($id, '_wp_attachment_image_alt', true);
            $link_to = get_post_meta($id, '_wpmf_gallery_custom_image_link', true);
            $link_target = get_post_meta($id, '_gallery_link_target', true);
            $img_tags = get_post_meta($id, 'wpmf_img_tags', true);

            /* set default meta */
            if (empty($alt)) {
                $alt = '';
            }
            if (empty($link_to)) {
                $link_to = '';
            }
            if (empty($link_target)) {
                $link_target = '';
            }
            if (empty($img_tags)) {
                $img_tags = '';
            }
            require_once(WPMF_GALLERY_ADDON_PLUGIN_DIR . '/admin/pages/image_details_popup.php');

            $images_html = ob_get_contents();
            ob_end_clean();
            wp_send_json(array('status' => true, 'html' => $images_html));
        }
        wp_send_json(array('status' => false, 'html' => ''));
    }

    /**
     * Delete image selection from gallery
     *
     * @return void
     */
    public function imageSelectionDelete()
    {
        if (empty($_POST['wpmf_gallery_nonce'])
            || !wp_verify_nonce($_POST['wpmf_gallery_nonce'], 'wpmf_gallery_nonce')) {
            wp_send_json(
                array(
                    'status' => false
                )
            );
        }

        /**
         * Filter check capability of current user to delete image in gallery
         *
         * @param boolean The current user has the given capability
         * @param string  Action name
         *
         * @return boolean
         *
         * @ignore Hook already documented
         */
        $wpmf_capability = apply_filters('wpmf_user_can', current_user_can('upload_files'), 'gallery_delete_image_selection');
        if (!$wpmf_capability) {
            wp_send_json(
                array(
                    'status' => false
                )
            );
        }

        if (isset($_POST['id'])) {
            // delete gallery to media library
            $relationships = get_option('wpmfgrl_relationships');
            $id = (int)$_POST['id'];
            $metatype = get_post_meta((int)$id, 'wpmfglr_type', true);
            if (isset($metatype) && $metatype === 'upload') {
                wp_delete_attachment($id);
            } else {
                /* Remove in gallery */
                wp_remove_object_terms($id, (int)$_POST['id_gallery'], WPMF_GALLERY_ADDON_TAXO);
                /* Remove in media library gallery */
                wp_remove_object_terms($id, (int)$relationships[$_POST['id_gallery']], WPMF_TAXO);
            }

            /* get count image selection */
            $count = $this->getCountImageSelection($_POST['id_gallery']);
            $nav = $this->regenerationNav($count);
            wp_send_json(array('status' => true, 'nav' => $nav));
        }
        wp_send_json(array('status' => false));
    }

    /**
     * Get nav of image selection
     *
     * @param integer $post_count       Count image in gallery
     * @param integer $current_page_nav Current page
     *
     * @return string
     */
    public function regenerationNav($post_count, $current_page_nav = 1)
    {
        $limit = get_option('wpmf_gallery_img_per_page');
        $page_count = ceil($post_count / $limit);
        $nav = '';
        ob_start();
        if ($page_count > 1) {
            require_once(WPMF_GALLERY_ADDON_PLUGIN_DIR . '/admin/pages/nav.php');
        }
        $nav = ob_get_contents();
        ob_end_clean();
        return $nav;
    }

    /**
     * Change gallery
     *
     * @return void
     */
    public function changeGallery()
    {
        if (empty($_POST['wpmf_gallery_nonce'])
            || !wp_verify_nonce($_POST['wpmf_gallery_nonce'], 'wpmf_gallery_nonce')) {
            die();
        }

        /**
         * Filter check capability of current user to get gallery
         *
         * @param boolean The current user has the given capability
         * @param string  Action name
         *
         * @return boolean
         *
         * @ignore Hook already documented
         */
        $wpmf_capability = apply_filters('wpmf_user_can', current_user_can('upload_files'), 'get_gallery');
        if (!$wpmf_capability) {
            wp_send_json(false);
        }

        $id = 0;
        $limit = get_option('wpmf_gallery_img_per_page');
        if (!empty($_POST['id'])) {
            $id = (int)$_POST['id'];
        }

        $current_gallery = get_term($id, WPMF_GALLERY_ADDON_TAXO);
        $child = get_term_children((int)$current_gallery->term_id, WPMF_GALLERY_ADDON_TAXO);
        $countchild = count($child);

        // get params
        $gallery_configs = get_option('wpmf_gallery_settings');
        $galleries = get_option('wpmf_galleries');
        $theme = $galleries[$id]['theme'];
        $params = array_merge(array(
            'columns' => 3,
            'size' => 'medium',
            'targetsize' => 'large',
            'link' => 'file',
            'wpmf_orderby' => 'post__in',
            'wpmf_order' => 'ASC',
            'display_tree' => 0,
            'display_tag' => 0,
            'animation' => 'slide',
            'duration' => 4000,
            'auto_animation' => 1,
            'show_buttons' => 1
        ), $gallery_configs['theme'][$theme . '_theme'], $galleries[$id]);

        // get images html
        $args = array(
            'posts_per_page' => $limit,
            'post_status' => 'any',
            'post_type' => array('attachment'),
            'tax_query' => array(
                array(
                    'taxonomy' => WPMF_GALLERY_ADDON_TAXO,
                    'field' => 'term_id',
                    'terms' => $id,
                    'include_children' => false
                )
            ),
            'orderby' => $params['wpmf_orderby'],
            'order' => $params['wpmf_order']
        );

        $query = new WP_Query($args);
        $imageIDs = $query->get_posts();
        if ($params['orderby'] === 'post__in') {
            foreach ($imageIDs as &$val) {
                $order = get_post_meta((int)$val->ID, 'wpmf_gallery_order', true);
                $val->order = (int) $order;
            }

            usort($imageIDs, 'wpmfSortByOrder');
        }

        $glr = array(
            'name' => $current_gallery->name,
            'id' => $current_gallery->term_id,
            'parent' => $current_gallery->parent,
            'count_child' => $countchild,
            'term_group' => $current_gallery->term_group,
            'theme' => $theme,
            'params' => $params,
            'images' => $imageIDs
        );

        if (count($imageIDs) === 0 || (count($imageIDs) === 1 && (int)$imageIDs[0] === 0)) {
            ob_start();
            require(WPMF_GALLERY_ADDON_PLUGIN_DIR . '/admin/pages/dragdrop.php');
            $images_html = ob_get_contents();
            ob_end_clean();
            wp_send_json(array('status' => false, 'nav' => '', 'images_html' => $images_html, 'theme' => $theme, 'glr' => $glr));
        } else {
            $images = array();
            $images_html = '';
            ob_start();
            foreach ($imageIDs as $image) {
                $thumnailUrl = wp_get_attachment_image_url($image->ID, 'large');
                if ($thumnailUrl) {
                    require(WPMF_GALLERY_ADDON_PLUGIN_DIR . '/admin/pages/thumbnail_selection.php');
                }
            }
            $images_html = ob_get_contents();
            ob_end_clean();
            $post_count = $this->getCountImageSelection($id);
            $nav = $this->regenerationNav($post_count);
            wp_send_json(
                array(
                    'status' => true,
                    'nav' => $nav,
                    'images_html' => $images_html,
                    'theme' => $theme,
                    'glr' => $glr
                )
            );
        }
    }

    /**
     * Get library folder tree
     *
     * @return void
     */
    public function getLibraryTree()
    {
        if (empty($_POST['wpmf_gallery_nonce'])
            || !wp_verify_nonce($_POST['wpmf_gallery_nonce'], 'wpmf_gallery_nonce')) {
            die();
        }

        /**
         * Filter check capability of current user to get wpmf folders list
         *
         * @param boolean The current user has the given capability
         * @param string  Action name
         *
         * @return boolean
         *
         * @ignore Hook already documented
         */
        $wpmf_capability = apply_filters('wpmf_user_can', current_user_can('upload_files'), 'get_wpmf_category');
        if (!$wpmf_capability) {
            wp_send_json(false);
        }
        global $current_user;
        $dir = '/';
        if (!empty($_POST['dir'])) {
            $dir = $_POST['dir'];
            if ($dir[0] === '/') {
                $dir = '.' . $dir . '/';
            }
        }
        $dir = str_replace('..', '', $dir);
        $dirs = array();
        $id = 0;
        if (!empty($_POST['id'])) {
            $id = (int)$_POST['id'];
        }

        // Retrieve the terms in a given taxonomy or list of taxonomies.
        $categories = get_categories(
            array(
                'taxonomy' => WPMF_TAXO,
                'orderby' => 'name',
                'order' => 'ASC',
                'parent' => $id,
                'hide_empty' => false
            )
        );
        $wpmf_active_media = get_option('wpmf_active_media');
        $wpmf_create_folder = get_option('wpmf_create_folder');
        $user_roles = $current_user->roles;
        $role = array_shift($user_roles);
        $current_role = $this->getRoles(get_current_user_id());
        foreach ($categories as $category) {
            if ((int)$category->parent === 0 && $category->name === 'Gallery Upload') {
                continue;
            }

            $child = get_term_children((int)$category->term_id, WPMF_TAXO);
            $countchild = count($child);
            if (($role !== 'administrator' && isset($wpmf_active_media) && (int)$wpmf_active_media === 1)
                || ($role === 'administrator' && isset($_SESSION['wpmf_display_media'])
                    && $_SESSION['wpmf_display_media'] === 'yes')) {
                if ($wpmf_create_folder === 'user') {
                    if ((int)$category->term_group === (int)get_current_user_id()) {
                        $dirs[] = array(
                            'type' => 'dir',
                            'dir' => $dir,
                            'file' => $category->name,
                            'id' => $category->term_id,
                            'parent_id' => $category->parent,
                            'count_child' => $countchild,
                            'term_group' => $category->term_group
                        );
                    }
                } else {
                    $role = $this->getRoles($category->term_group);
                    if ($current_role === $role) {
                        $dirs[] = array(
                            'type' => 'dir',
                            'dir' => $dir,
                            'file' => $category->name,
                            'id' => $category->term_id,
                            'parent_id' => $category->parent,
                            'count_child' => $countchild,
                            'term_group' => $category->term_group
                        );
                    }
                }
            } else {
                $dirs[] = array(
                    'type' => 'dir',
                    'dir' => $dir,
                    'file' => $category->name,
                    'id' => $category->term_id,
                    'parent_id' => $category->parent,
                    'count_child' => $countchild,
                    'term_group' => $category->term_group
                );
            }
        }

        if (count($dirs) < 0) {
            wp_send_json(array('status' => false));
        } else {
            wp_send_json(array('dirs' => $dirs, 'status' => true));
        }
    }

    /**
     * Get all terms need import
     *
     * @param integer $parent  ID of term parent
     * @param array   $results Results
     *
     * @return array
     */
    public function getTermChild($parent, $results)
    {
        if (empty($results)) {
            $results = array();
        }

        $terms = get_terms(WPMF_TAXO, array(
            'orderby' => 'name',
            'order' => 'ASC',
            'hide_empty' => false,
            'child_of' => 1,
            'parent' => $parent
        ));


        if (!empty($terms)) {
            foreach ($terms as $term) {
                $results[] = $term;
                $results = $this->getTermChild($term->term_id, $results);
            }
        }

        return $results;
    }

    /**
     * Create gallery on media library
     *
     * @return void
     */
    public function importToMedia()
    {
        // get all term taxonomy 'category'
        $terms = get_terms(WPMF_GALLERY_ADDON_TAXO, array(
            'orderby' => 'name',
            'order' => 'ASC',
            'hide_empty' => false,
            'child_of' => 0
        ));

        $relationships = get_option('wpmfgrl_relationships');
        // update parent wpmf-category term
        foreach ($terms as $term) {
            wp_update_term($relationships[$term->term_id], WPMF_TAXO, array('parent' => $relationships[$term->parent]));
            //update attachments
            $objects = get_objects_in_term($term->term_id, WPMF_GALLERY_ADDON_TAXO);
            foreach ($objects as $object) {
                wp_set_object_terms($object, $relationships[$term->term_id], WPMF_TAXO, true);
            }
        }
    }

    /**
     * Ajax import images from wordpress
     *
     * @return void
     */
    public function importImagesFromWp()
    {
        if (empty($_POST['wpmf_gallery_nonce'])
            || !wp_verify_nonce($_POST['wpmf_gallery_nonce'], 'wpmf_gallery_nonce')) {
            wp_send_json(
                array(
                    'status' => false
                )
            );
        }

        /**
         * Filter check capability of current user to import images from wordpress
         *
         * @param boolean The current user has the given capability
         * @param string  Action name
         *
         * @return boolean
         *
         * @ignore Hook already documented
         */
        $wpmf_capability = apply_filters('wpmf_user_can', current_user_can('upload_files'), 'import_image_from_wp');
        if (!$wpmf_capability) {
            wp_send_json(array('status' => false));
        }

        if (empty($_POST['id'])) {
            wp_send_json(array('status' => false));
        }

        /* set images to gallery */
        wp_set_object_terms((int)$_POST['id'], (int)$_POST['gallery_id'], WPMF_GALLERY_ADDON_TAXO, true);

        // set to root folder
        $root_folder = get_the_terms($_POST['id'], WPMF_TAXO);
        if (empty($root_folder)) {
            $root_id = get_option('wpmf_folder_root_id');
            wp_set_object_terms(
                (int)$_POST['id'],
                (int)$root_id,
                WPMF_TAXO,
                true
            );
        }
        /* Add to media library */
        $relationships = get_option('wpmfgrl_relationships');
        wp_set_object_terms(
            (int)$_POST['id'],
            (int)$relationships[(int)$_POST['gallery_id']],
            WPMF_TAXO,
            true
        );
        // set default order images
        update_post_meta((int)$_POST['id'], 'wpmf_gallery_order', 0);

        $images_html = '';
        ob_start();
        $this->generateAttachmentHtml($_POST['title'], (int)$_POST['id']);
        $images_html .= ob_get_contents();
        ob_end_clean();
        $post_count = $this->getCountImageSelection($_POST['gallery_id']);
        $nav = $this->regenerationNav($post_count);
        wp_send_json(array('status' => true, 'id' => $_POST['id'], 'html' => $images_html, 'nav' => $nav));
    }

    /**
     * Generate attachment html
     *
     * @param string  $title Title of image
     * @param integer $id    Id of image
     *
     * @return void
     */
    public function generateAttachmentHtml($title, $id)
    {
        ?>
        <li aria-label="<?php echo esc_html($title) ?>" aria-checked="false" data-id="<?php echo esc_html($id) ?>"
            class="attachment">
            <div class="wpmfglr-attachment-preview">
                <?php
                $thumnailUrl = wp_get_attachment_image_src($id, 'medium');
                ?>
                <img src="<?php echo esc_html($thumnailUrl[0]) ?>" draggable="false" alt="">
                <div class="action_images">
                    <span data-id="<?php echo esc_html($id) ?>"
                          class="edit_image_selection dashicons dashicons-edit"></span>
                    <span data-id="<?php echo esc_html($id) ?>"
                          class="delete_image_selection dashicons dashicons-trash"></span>
                </div>
            </div>
            <button type="button" class="check" tabindex="-1"><span class="media-modal-icon"></span><span
                        class="screen-reader-text">Deselect</span></button>
        </li>
        <?php
    }

    /**
     * Get theme, if not exist return default theme
     *
     * @param string $theme Theme name
     *
     * @return string
     */
    public function getTheme($theme)
    {
        $allow_themes = array(
            'default',
            'masonry',
            'portfolio',
            'slider',
            'flowslide',
            'square_grid',
            'material'
        );
        if (in_array($theme, $allow_themes)) {
            return $theme;
        }
        return 'default';
    }

    /**
     * Ajax create gallery
     *
     * @return void
     */
    public function createGallery()
    {
        if (empty($_POST['wpmf_gallery_nonce'])
            || !wp_verify_nonce($_POST['wpmf_gallery_nonce'], 'wpmf_gallery_nonce')) {
            wp_send_json(
                array(
                    'status' => false
                )
            );
        }

        /**
         * Filter check capability of current user to create a gallery
         *
         * @param boolean The current user has the given capability
         * @param string  Action name
         *
         * @return boolean
         *
         * @ignore Hook already documented
         */
        $wpmf_capability = apply_filters('wpmf_user_can', current_user_can('upload_files'), 'create_gallery');
        if (!$wpmf_capability) {
            wp_send_json(array('status' => false));
        }

        if (isset($_POST['type']) && $_POST['type'] === 'save_as_gallery') {
            $title = time();
        } else {
            if (isset($_POST['title'])) {
                $title = $_POST['title'];
            } else {
                $title = __('New gallery', 'wp-media-folder-gallery-addon');
            }
        }

        // get theme
        $theme = $this->getTheme($_POST['theme']);
        /* add new gallery and params to array */
        $galleries = get_option('wpmf_galleries');
        $inserted = wp_insert_term(
            $title,
            WPMF_GALLERY_ADDON_TAXO,
            array('parent' => (int)$_POST['parent'])
        );

        if (is_wp_error($inserted)) {
            wp_send_json(array('status' => false, 'msg' => $inserted->get_error_message()));
        }

        update_term_meta((int) $inserted['term_id'], 'wpmf_theme', $theme);
        update_term_meta((int) $inserted['term_id'], 'wpmf_order', 0);

        $termInfos = get_term($inserted['term_id'], WPMF_GALLERY_ADDON_TAXO);
        /* Add to media library */
        $relationships = get_option('wpmfgrl_relationships');
        $library_insert = wp_insert_term(
            $title,
            WPMF_TAXO,
            array('parent' => $relationships[$termInfos->parent])
        );

        $relationships[$inserted['term_id']] = $library_insert['term_id'];
        update_option('wpmfgrl_relationships', $relationships);

        /* create wpmf_galleries option */
        if (empty($galleries) && !is_array($galleries)) {
            $galleries = array();
            $galleries[$inserted['term_id']] = array(
                'gallery_id' => $inserted['term_id'],
                'theme' => $theme
            );
        } else {
            $galleries[$inserted['term_id']] = array(
                'gallery_id' => $inserted['term_id'],
                'theme' => $theme
            );
        }

        $termInfos->theme = $theme;
        /* set option wpmf_galleries to relative gallery id with theme */
        update_option('wpmf_galleries', $galleries);
        /* get dropdown gallery */
        $dropdown_gallery = $this->dropdownGallery();

        // get all gallery
        $terms = $this->getAllGalleries();
        $attachment_terms = $terms['attachment_terms'];
        $terms_order = $terms['terms_order'];

        wp_send_json(
            array(
                'items' => $termInfos,
                'dropdown_gallery' => $dropdown_gallery,
                'status' => true,
                'categories' => $attachment_terms,
                'categories_order' => $terms_order
            )
        );
    }

    /**
     * Generation dropdown gallery
     *
     * @return string
     */
    public function dropdownGallery()
    {
        ob_start();
        $html = '';
        $dropdown_options = array(
            'show_option_none' => __('Parent gallery', 'wp-media-folder-gallery-addon'),
            'option_none_value' => 0,
            'hide_empty' => false,
            'hierarchical' => true,
            'orderby' => 'name',
            'taxonomy' => WPMF_GALLERY_ADDON_TAXO,
            'class' => 'wpmf-gallery-categories ju-select',
            'name' => 'wpmf-gallery-categories'
        );
        wp_dropdown_categories($dropdown_options);
        $html .= ob_get_contents();
        ob_end_clean();

        return $html;
    }

    /**
     * Ajax delete gallery
     *
     * @return void
     */
    public function deleteGallery()
    {
        if (empty($_POST['wpmf_gallery_nonce'])
            || !wp_verify_nonce($_POST['wpmf_gallery_nonce'], 'wpmf_gallery_nonce')) {
            wp_send_json(
                array(
                    'status' => false
                )
            );
        }

        /**
         * Filter check capability of current user to delete a gallery
         *
         * @param boolean The current user has the given capability
         * @param string  Action name
         *
         * @return boolean
         *
         * @ignore Hook already documented
         */
        $wpmf_capability = apply_filters('wpmf_user_can', current_user_can('upload_files'), 'delete_gallery');
        if (!$wpmf_capability) {
            wp_send_json(array('status' => false));
        }

        if (wp_delete_term((int)$_POST['id'], WPMF_GALLERY_ADDON_TAXO)) {
            /* update setting gallery */
            $galleries = get_option('wpmf_galleries');
            if (isset($galleries[$_POST['id']])) {
                unset($galleries[$_POST['id']]);
                update_option('wpmf_galleries', $galleries);
            }
            /* remove relationship in library */
            $relationships = get_option('wpmfgrl_relationships');
            wp_delete_term((int)$relationships[(int)$_POST['id']], WPMF_TAXO);
            if (isset($relationships[$_POST['id']])) {
                unset($relationships[$_POST['id']]);
                update_option('wpmfgrl_relationships', $relationships);
            }

            $relas = get_option('wpmfgrl_relationships_media');
            if (in_array($_POST['id'], $relas)) {
                $k = array_search($_POST['id'], $relas);
                unset($relas[$k]);
                update_option('wpmfgrl_relationships_media', $relas);
            }

            wp_send_json(array('status' => true));
        } else {
            wp_send_json(array('status' => false));
        }
    }

    /**
     * Ajax edit gallery
     *
     * @return void
     */
    public function editGallery()
    {
        if (empty($_POST['wpmf_gallery_nonce'])
            || !wp_verify_nonce($_POST['wpmf_gallery_nonce'], 'wpmf_gallery_nonce')) {
            wp_send_json(
                array(
                    'status' => false,
                    'msg' => __('Edit failed. Please try again.', 'wp-media-folder-gallery-addon')
                )
            );
        }

        /**
         * Filter check capability of current user to edit a gallery
         *
         * @param boolean The current user has the given capability
         * @param string  Action name
         *
         * @return boolean
         *
         * @ignore Hook already documented
         */
        $wpmf_capability = apply_filters('wpmf_user_can', current_user_can('upload_files'), 'edit_gallery');
        if (!$wpmf_capability) {
            wp_send_json(
                array(
                    'status' => false,
                    'msg' => __('Edit failed. Please try again.', 'wp-media-folder-gallery-addon')
                )
            );
        }

        if (isset($_POST['id'])) {
            // get theme
            $theme = $this->getTheme($_POST['theme']);
            $gallery_params = array('columns', 'size', 'targetsize', 'link', 'wpmf_orderby', 'wpmf_order', 'display_tree', 'display_tag', 'animation', 'duration', 'auto_animation');
            $oldterm = get_term((int)$_POST['id'], WPMF_GALLERY_ADDON_TAXO);
            $params = array(
                'name' => $_POST['title'],
                'parent' => (int)$_POST['parent']
            );

            $termInfos = wp_update_term((int)$_POST['id'], WPMF_GALLERY_ADDON_TAXO, $params);
            if ($termInfos instanceof WP_Error) {
                wp_send_json(array('status' => false, 'msg' => $termInfos->get_error_messages()));
            } else {
                /* update in library */
                $relationships = get_option('wpmfgrl_relationships');
                wp_update_term(
                    (int)$relationships[(int)$_POST['id']],
                    WPMF_TAXO,
                    array(
                        'name' => $_POST['title'],
                        'parent' => (int)$relationships[(int)$_POST['parent']]
                    )
                );

                /* update theme for this gallery */
                $galleries = get_option('wpmf_galleries');
                $galleries[$_POST['id']]['theme'] = $theme;
                foreach ($gallery_params as $param) {
                    if (isset($_POST[$param])) {
                        $galleries[$_POST['id']][$param] = $_POST[$param];
                    }
                }
                update_option('wpmf_galleries', $galleries);

                /* set images to gallery */
                $images = get_objects_in_term($_POST['id'], WPMF_GALLERY_ADDON_TAXO);
                $termInfos = get_term((int)$_POST['id'], WPMF_GALLERY_ADDON_TAXO);
                $termInfos->theme = $_POST['theme'];
                $termInfos->images = $images;

                // get all gallery
                $terms = $this->getAllGalleries();
                $attachment_terms = $terms['attachment_terms'];
                $terms_order = $terms['terms_order'];

                // get dropdown lists gallery html
                $dropdown_gallery = $this->dropdownGallery();

                $json = array(
                    'status' => true,
                    'dropdown_gallery' => $dropdown_gallery,
                    'items' => $termInfos,
                    'categories' => $attachment_terms,
                    'categories_order' => $terms_order
                );

                /* If update parent */
                if ((int)$oldterm->parent !== (int)$_POST['parent']) {
                    $child_id = get_term_children((int)$_POST['id'], WPMF_GALLERY_ADDON_TAXO);
                    $child_id_category = get_term_children((int)$_POST['parent'], WPMF_GALLERY_ADDON_TAXO);
                    $json['count_id'] = count($child_id);
                    $json['count_to_id'] = count($child_id_category);
                    $json['update_parent'] = 1;
                }
                wp_send_json(
                    $json
                );
            }
        }
        wp_send_json(
            array(
                'status' => false,
                'msg' => __('This gallery does not exist!', 'wp-media-folder-gallery-addon')
            )
        );
    }

    /**
     * Remove selected images from media selection
     *
     * @return void
     */
    public function deleteImgsSelected()
    {
        if (empty($_POST['wpmf_gallery_nonce'])
            || !wp_verify_nonce($_POST['wpmf_gallery_nonce'], 'wpmf_gallery_nonce')) {
            wp_send_json(
                array(
                    'status' => false
                )
            );
        }

        if (isset($_POST['ids'])) {
            $ids = explode(',', $_POST['ids']);
            if (!empty($ids)) {
                $relationships = get_option('wpmfgrl_relationships');
                foreach ($ids as $id) {
                    $metatype = get_post_meta((int)$id, 'wpmfglr_type', true);
                    if (isset($metatype) && $metatype === 'upload') {
                        wp_delete_attachment((int)$id);
                    } else {
                        /* Remove in gallery */
                        wp_remove_object_terms((int)$id, (int)$_POST['id_gallery'], WPMF_GALLERY_ADDON_TAXO);
                        /* Remove in media library gallery */
                        wp_remove_object_terms($id, (int)$relationships[$_POST['id_gallery']], WPMF_TAXO);
                    }
                }
            }

            /* get count image selection */
            $count = $this->getCountImageSelection($_POST['id_gallery']);
            $nav = $this->regenerationNav($count);
            wp_send_json(array('status' => true, 'nav' => $nav));
        }
        wp_send_json(array('status' => false));
    }

    /**
     * Get current user role
     *
     * @param integer $userId User id
     *
     * @return mixed|string
     */
    public function getRoles($userId)
    {
        if (!function_exists('get_userdata')) {
            require_once(ABSPATH . 'wp-includes/pluggable.php');
        }
        $userdata = get_userdata($userId);
        if (!empty($userdata->roles)) {
            $role = array_shift($userdata->roles);
        } else {
            $role = '';
        }
        return $role;
    }

    /**
     * Ajax upload file
     *
     * @return void
     */
    public function galleryUploadFile()
    {
        if (empty($_POST['wpmf_gallery_nonce'])
            || !wp_verify_nonce($_POST['wpmf_gallery_nonce'], 'wpmf_gallery_nonce')) {
            die();
        }

        /**
         * Filter check capability of current user to upload images to gallery
         *
         * @param boolean The current user has the given capability
         * @param string  Action name
         *
         * @return boolean
         *
         * @ignore Hook already documented
         */
        $wpmf_capability = apply_filters('wpmf_user_can', current_user_can('upload_files'), 'gallery_upload_images');
        if (!$wpmf_capability) {
            wp_send_json(false);
        }

        if (!empty($_FILES['wpmf_gallery_file'])) {
            $lists = array();
            foreach ($_FILES['wpmf_gallery_file']['name'] as $i => $file) {
                $lists[] = array(
                    'name' => $file,
                    'type' => $_FILES['wpmf_gallery_file']['type'][$i],
                    'tmp_name' => $_FILES['wpmf_gallery_file']['tmp_name'][$i],
                    'error' => $_FILES['wpmf_gallery_file']['error'][$i],
                    'size' => $_FILES['wpmf_gallery_file']['size'][$i]
                );
            }

            $allowedTypes = array('gif', 'jpg', 'JPG', 'png', 'bmp', 'jpeg', 'JPEG', 'svg');
            $upload_dir = wp_upload_dir();
            $images_html = '';
            $idsImport = array();
            ob_start();
            foreach ($lists as $list) {
                $infopath = pathinfo($list['name']);
                if (!in_array($infopath['extension'], $allowedTypes)) {
                    wp_send_json(
                        array(
                            'status' => false,
                            'msg' => __('Please upload the media with format
                             (jpg, png, gif, jpeg, bmp, svg)', 'wp-media-folder-gallery-addon')
                        )
                    );
                }

                if ($list['error'] > 0) {
                    continue;
                }

                $relationships = get_option('wpmfgrl_relationships');
                $id_selection = $relationships[(int)$_POST['up_gallery_id']];
                $file = sanitize_file_name($list['name']);
                $content = file_get_contents($list['tmp_name']);
                $title = str_replace('.' . $infopath['extension'], '', $list['name']);

                $attach_id = $this->insertAttachmentMetadata(
                    $upload_dir['path'],
                    $upload_dir['url'],
                    $list['name'],
                    $file,
                    $content,
                    $list['type'],
                    $infopath['extension'],
                    $id_selection
                );
                update_post_meta($attach_id, 'wpmfglr_type', 'upload');
                $idsImport[] = $attach_id;
                /* set images to gallery */
                wp_set_object_terms((int)$attach_id, (int)$_POST['up_gallery_id'], WPMF_GALLERY_ADDON_TAXO, true);
                /* Add to media library */
                $relationships = get_option('wpmfgrl_relationships');
                wp_set_object_terms(
                    (int)$attach_id,
                    (int)$relationships[(int)$_POST['up_gallery_id']],
                    WPMF_TAXO,
                    true
                );

                // set default order images
                update_post_meta((int)$attach_id, 'wpmf_gallery_order', 0);

                if ($attach_id) {
                    $this->generateAttachmentHtml($title, (int)$attach_id);
                }
            }
            $images_html .= ob_get_contents();
            ob_end_clean();
            /* get count image selection */
            $post_count = $this->getCountImageSelection($_POST['up_gallery_id']);
            $nav = $this->regenerationNav($post_count);
            wp_send_json(array('status' => true, 'ids' => $idsImport, 'html' => $images_html, 'nav' => $nav));
        } else {
            wp_send_json(array('status' => false, 'msg' => __('File not exist', 'wp-media-folder-gallery-addon')));
        }
    }

    /**
     * Update img per page
     *
     * @return void
     */
    public function updateImgPerpage()
    {
        if (empty($_POST['wpmf_gallery_nonce'])
            || !wp_verify_nonce($_POST['wpmf_gallery_nonce'], 'wpmf_gallery_nonce')) {
            wp_send_json(
                array(
                    'status' => false
                )
            );
        }

        /**
         * Filter check capability of current user to update image perpage
         *
         * @param boolean The current user has the given capability
         * @param string  Action name
         *
         * @return boolean
         *
         * @ignore Hook already documented
         */
        $wpmf_capability = apply_filters('wpmf_user_can', current_user_can('upload_files'), 'update_image_perpage');
        if (!$wpmf_capability) {
            wp_send_json(
                array(
                    'status' => false
                )
            );
        }

        if (isset($_POST['img_per_page']) && is_numeric($_POST['img_per_page'])) {
            update_option('wpmf_gallery_img_per_page', $_POST['img_per_page']);
        }
        wp_send_json(array('status' => true));
    }

    /**
     * Update gallery parent when draggable gallery on folder tree
     *
     * @return void
     */
    public function updateParentGallery()
    {
        if (empty($_POST['wpmf_gallery_nonce'])
            || !wp_verify_nonce($_POST['wpmf_gallery_nonce'], 'wpmf_gallery_nonce')) {
            wp_send_json(
                array(
                    'status' => false
                )
            );
        }

        /**
         * Filter check capability of current user to update parent of gallery
         *
         * @param boolean The current user has the given capability
         * @param string  Action name
         *
         * @return boolean
         *
         * @ignore Hook already documented
         */
        $wpmf_capability = apply_filters('wpmf_user_can', current_user_can('upload_files'), 'update_parent_gallery');
        if (!$wpmf_capability) {
            wp_send_json(
                array(
                    'status' => false
                )
            );
        }

        if (isset($_POST['id_gallery']) && isset($_POST['parent'])) {
            $r = wp_update_term(
                (int)$_POST['id_gallery'],
                WPMF_GALLERY_ADDON_TAXO,
                array('parent' => (int)$_POST['parent'])
            );
            if ($r instanceof WP_Error) {
                wp_send_json(array('status' => false));
            } else {
                // update gallery to media library
                $relationships = get_option('wpmfgrl_relationships');
                wp_update_term(
                    (int)$relationships[(int)$_POST['id_gallery']],
                    WPMF_TAXO,
                    array(
                        'parent' => (int)$relationships[(int)$_POST['parent']]
                    )
                );

                // get all gallery
                $terms = $this->getAllGalleries();
                $attachment_terms = $terms['attachment_terms'];
                $terms_order = $terms['terms_order'];

                // get dropdown lists gallery html
                $dropdown_gallery = $this->dropdownGallery();
                wp_send_json(
                    array(
                        'status' => true,
                        'dropdown_gallery' => $dropdown_gallery,
                        'categories' => $attachment_terms,
                        'categories_order' => $terms_order
                    )
                );
            }
        }
    }

    /**
     * Get attachment from folder Image gallery selection by nav
     *
     * @return void
     */
    public function getImgSelectionNav()
    {
        if (empty($_POST['wpmf_gallery_nonce'])
            || !wp_verify_nonce($_POST['wpmf_gallery_nonce'], 'wpmf_gallery_nonce')) {
            die();
        }

        /**
         * Filter check capability of current user to get gallery images list
         *
         * @param boolean The current user has the given capability
         * @param string  Action name
         *
         * @return boolean
         *
         * @ignore Hook already documented
         */
        $wpmf_capability = apply_filters('wpmf_user_can', current_user_can('upload_files'), 'get_gallery_images_list');
        if (!$wpmf_capability) {
            wp_send_json(false);
        }

        if (isset($_POST['id_gallery']) && isset($_POST['current_page_nav'])) {
            $id = $_POST['id_gallery'];
            /* get count page */
            $limit = get_option('wpmf_gallery_img_per_page');
            $args = array(
                'posts_per_page' => -1,
                'post_status' => 'any',
                'post_type' => array('attachment'),
                'tax_query' => array(
                    array(
                        'taxonomy' => WPMF_GALLERY_ADDON_TAXO,
                        'field' => 'term_id',
                        'terms' => $id,
                        'include_children' => false
                    ),
                ),
                'meta_key' => 'wpmf_gallery_order',
                'orderby' => 'meta_value_num',
                'order' => 'ASC'
            );
            $querycount = new WP_Query($args);
            $post_count = $querycount->post_count;
            $page_count = ceil($post_count / $limit);
            $current_page_nav = $_POST['current_page_nav'];
            if ($current_page_nav <= 0) {
                $current_page_nav = 1;
            }
            if ($current_page_nav > $page_count) {
                $current_page_nav = $page_count;
            }
            $offset = ((int)$current_page_nav - 1) * $limit;
            $args = array(
                'posts_per_page' => $limit,
                'offset' => $offset,
                'post_status' => 'any',
                'post_type' => array('attachment'),
                'tax_query' => array(
                    array(
                        'taxonomy' => WPMF_GALLERY_ADDON_TAXO,
                        'field' => 'term_id',
                        'terms' => $id,
                        'include_children' => false
                    ),
                ),
                'meta_key' => 'wpmf_gallery_order',
                'orderby' => 'meta_value_num',
                'order' => 'ASC'
            );
            $query = new WP_Query($args);
            $iSelections = $query->get_posts();

            ob_start();
            $html = '';
            foreach ($iSelections as $image) {
                $thumnailUrl = wp_get_attachment_image_url($image->ID, 'large');
                if ($thumnailUrl) {
                    require(WPMF_GALLERY_ADDON_PLUGIN_DIR . '/admin/pages/thumbnail_selection.php');
                }
            }
            $html .= ob_get_contents();

            $nav = $this->regenerationNav($post_count, $current_page_nav);
            ob_end_clean();
            wp_send_json(array('status' => true, 'html' => $html, 'nav' => $nav));
        }
    }

    /**
     * Insert a attachment to database
     *
     * @param string  $upload_path Path of file
     * @param string  $upload_url  URL of file
     * @param string  $file_title  Title of tile
     * @param string  $file        File name
     * @param string  $content     Content of file
     * @param string  $mime_type   Mime type of file
     * @param string  $ext         Extension of file
     * @param integer $term_id     Folder id
     *
     * @return boolean|integer|WP_Error
     */
    public function insertAttachmentMetadata($upload_path, $upload_url, $file_title, $file, $content, $mime_type, $ext, $term_id)
    {
        remove_filter('add_attachment', array($GLOBALS['wp_media_folder'], 'wpmf_after_upload'));
        $file = wp_unique_filename($upload_path, $file);
        $upload = file_put_contents($upload_path . '/' . $file, $content);
        if ($upload) {
            $attachment = array(
                'guid' => $upload_url . '/' . $file,
                'post_mime_type' => $mime_type,
                'post_title' => str_replace('.' . $ext, '', $file_title),
                'post_status' => 'inherit'
            );

            $image_path = $upload_path . '/' . $file;
            // Insert attachment
            $attach_id = wp_insert_attachment($attachment, $image_path);
            $attach_data = wp_generate_attachment_metadata($attach_id, $image_path);
            wp_update_attachment_metadata($attach_id, $attach_data);
            // set attachment to term
            wp_set_object_terms((int)$attach_id, (int)$term_id, WPMF_GALLERY_ADDON_TAXO, false);
            return $attach_id;
        }
        return false;
    }

    /**
     * Ajax custom order for file
     *
     * @return void
     */
    public function reorderFile()
    {
        if (empty($_POST['wpmf_gallery_nonce'])
            || !wp_verify_nonce($_POST['wpmf_gallery_nonce'], 'wpmf_gallery_nonce')) {
            die();
        }

        if (isset($_POST['order'])) {
            $orders = (array)json_decode(stripslashes_deep($_POST['order']));
            if (is_array($orders) && !empty($orders)) {
                foreach ($orders as $position => $id) {
                    update_post_meta(
                        (int)$id,
                        'wpmf_gallery_order',
                        (int)$position
                    );
                }
            }
        }
    }

    /**
     * Get custom order gallery
     *
     * @param integer $term_id Id of gallery
     *
     * @return integer|mixed
     */
    public function getOrderGallery($term_id)
    {
        $order = get_term_meta($term_id, 'wpmf_order', true);
        if (empty($order)) {
            $order = 0;
        }
        return $order;
    }

    /**
     * Ajax custom order for gallery
     *
     * @return void
     */
    public function reorderGallery()
    {
        if (empty($_POST['wpmf_gallery_nonce'])
            || !wp_verify_nonce($_POST['wpmf_gallery_nonce'], 'wpmf_gallery_nonce')) {
            wp_send_json(
                array(
                    'status' => false
                )
            );
        }

        /**
         * Filter check capability of current user to reorder gallery
         *
         * @param boolean The current user has the given capability
         * @param string  Action name
         *
         * @return boolean
         *
         * @ignore Hook already documented
         */
        $wpmf_capability = apply_filters('wpmf_user_can', current_user_can('upload_files'), 'reorder_gallery');
        if (!$wpmf_capability) {
            wp_send_json(
                array(
                    'status' => false
                )
            );
        }

        if (isset($_POST['order'])) {
            $orders = (array)json_decode(stripslashes_deep($_POST['order']));
            if (is_array($orders) && !empty($orders)) {
                foreach ($orders as $position => $id) {
                    update_term_meta(
                        (int)$id,
                        'wpmf_order',
                        (int)$position
                    );
                }
            }
        }
    }

    /**
     * Load gallery html with ajax method
     *
     * @return void
     */
    public function loadGalleryHtml()
    {
        if (empty($_REQUEST['wpmf_gallery_nonce'])
            || !wp_verify_nonce($_REQUEST['wpmf_gallery_nonce'], 'wpmf_gallery_nonce')) {
            wp_send_json(array('status' => false));
        }

        if (!empty($_REQUEST['datas'])) {
            $request_params = (array)json_decode(stripslashes($_REQUEST['datas']));
            $params = array_merge(
                array(
                    'gallery_id' => 0,
                    'display' => '',
                    'columns' => 3,
                    'gutterwidth' => 5,
                    'link' => 'post',
                    'size' => 'thumbnail',
                    'targetsize' => 'large',
                    'wpmf_orderby' => 'post__in',
                    'wpmf_order' => 'ASC',
                    'customlink' => 0,
                    'bottomspace' => 'default',
                    'hidecontrols' => 'false',
                    'class' => '',
                    'include' => '',
                    'exclude' => '',
                    'display_tree' => 0,
                    'display_tag' => 0,
                    'img_border_radius' => 0,
                    'border_width' => 0,
                    'border_color' => 'transparent',
                    'border_style' => 'solid',
                    'hoverShadowH' => 0,
                    'hoverShadowV' => 0,
                    'hoverShadowBlur' => 0,
                    'hoverShadowSpread' => 0,
                    'hoverShadowColor' => 'ccc',
                    'show_buttons' => 1,
                    'animation' => 'slide',
                    'duration' => 4000,
                    'auto_animation' => 1
                ),
                $request_params
            );

            foreach ($params as $attr_key => $attr_value) {
                ${$attr_key} = $attr_value;
            }

            $gallery = get_term($gallery_id, WPMF_GALLERY_ADDON_TAXO);
            if (empty($gallery)) {
                wp_send_json(array('status' => false));
            }

            $galleries = get_option('wpmf_galleries');
            if ($display === '') {
                $display = 'default';
                if (!empty($galleries[$gallery_id]['theme'])) {
                    $display = $galleries[$gallery_id]['theme'];
                }
            }

            if (isset($hoverShadowH, $hoverShadowV, $hoverShadowBlur, $hoverShadowSpread) && ((int)$hoverShadowH !== 0 || (int)$hoverShadowV !== 0 || (int)$hoverShadowBlur !== 0 || (int)$hoverShadowSpread !== 0)) {
                if ($hoverShadowColor !== 'transparent') {
                    $hoverShadowColor = '#' . $hoverShadowColor;
                }
                $img_shadow = $hoverShadowH . 'px ' . $hoverShadowV . 'px ' . $hoverShadowBlur . 'px ' . $hoverShadowSpread . 'px ' . $hoverShadowColor;
            } else {
                $img_shadow = '';
            }

            if ($border_color !== 'transparent') {
                $border_color = '#' . $border_color;
            }

            $shortcode = '[wpmfgallery';
            $shortcode .= ' gallery_id="' . $gallery_id . '"';
            $shortcode .= ' display="' . $display . '"';
            $shortcode .= ' size="' . $size . '"';
            $shortcode .= ' columns="' . $columns . '"';
            $shortcode .= ' targetsize="' . $targetsize . '"';
            $shortcode .= ' link="none"';
            $shortcode .= ' wpmf_orderby="' . $orderby . '"';
            $shortcode .= ' wpmf_order="' . $order . '"';
            $shortcode .= ' display_tree="' . $display_tree . '"';
            $shortcode .= ' display_tag="' . $display_tag . '"';
            $shortcode .= ' notlazyload="1"';
            $shortcode .= ' gutterwidth="' . $gutterwidth . '"';
            $shortcode .= ' img_border_radius="' . $img_border_radius . '"';
            $shortcode .= ' border_width="' . $border_width . '"';
            $shortcode .= ' border_color="' . $border_color . '"';
            $shortcode .= ' border_style="' . $border_style . '"';
            $shortcode .= ' img_shadow="' . $img_shadow . '"';
            $shortcode .= ' show_buttons="' . $show_buttons . '"';
            $shortcode .= ' animation="' . $animation . '"';
            $shortcode .= ' duration="' . $duration . '"';
            $shortcode .= ' auto_animation="' . $auto_animation . '"';

            $shortcode .= ']';

            $html = do_shortcode($shortcode, true);
            wp_send_json(array('status' => true, 'html' => $html, 'theme' => $display, 'title' => $gallery->name));
        }

        wp_send_json(array('status' => false));
    }
}
