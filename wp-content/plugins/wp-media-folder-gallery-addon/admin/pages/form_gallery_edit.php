<?php
/* Prohibit direct script loading */
defined('ABSPATH') || die('No direct script access allowed!');
wp_enqueue_script('thickbox');
wp_enqueue_style('thickbox');
$google_photo_config = get_option('_wpmfAddon_google_photo_config', true);
global $pagenow;
$themes = array(
    'default' => array('icon' => 'view_week', 'title' => __('Default', 'wp-media-folder-gallery-addon')),
    'masonry' => array('icon' => 'view_quilt', 'title' => __('Masonry', 'wp-media-folder-gallery-addon')),
    'portfolio' => array('icon' => 'view_stream', 'title' => __('Portfolio', 'wp-media-folder-gallery-addon')),
    'slider' => array('icon' => 'view_carousel', 'title' => __('Slider', 'wp-media-folder-gallery-addon')),
    'flowslide' => array('icon' => 'vertical_split', 'title' => __('Flow slide', 'wp-media-folder-gallery-addon')),
    'square_grid' => array('icon' => 'view_module', 'title' => __('Square grid', 'wp-media-folder-gallery-addon')),
    'material' => array('icon' => 'view_headline', 'title' => __('Material', 'wp-media-folder-gallery-addon'))
);
?>
<div class="ju-left-panel-toggle">
    <i class="dashicons dashicons-leftright ju-left-panel-toggle-icon"></i>
</div>
<div id="new-gallery-popup" class="form_add_gallery white-popup mfp-hide">
    <div class="gallery-options-wrap">
        <div class="wpmf-gallery-fields">
            <div class="wpmf-gallery-field">
                <label><?php esc_html_e('Gallery name', 'wp-media-folder-gallery-addon') ?></label>
                <input type="text" size="35" class="new-gallery-name gallery_name ju-input"
                       placeholder="<?php esc_html_e('Title', 'wp-media-folder-gallery-addon') ?>">
            </div>

            <div class="wpmf-gallery-field">
                <label><?php esc_html_e('Gallery level', 'wp-media-folder-gallery-addon') ?></label>
                <div class="sl-gallery-parent-wrap">
                    <?php
                    $dropdown_options = array(
                        'show_option_none' => __('Parent gallery', 'wp-media-folder-gallery-addon'),
                        'option_none_value' => 0,
                        'hide_empty' => false,
                        'hierarchical' => true,
                        'orderby' => 'name',
                        'taxonomy' => WPMF_GALLERY_ADDON_TAXO,
                        'id' => 'new-gallery-parent',
                        'class' => 'wpmf-gallery-categories new-gallery-parent ju-select',
                        'name' => 'new-gallery-parent',
                        'selected' => 0
                    );
                    wp_dropdown_categories($dropdown_options);
                    ?>
                </div>
            </div>
        </div>

        <div class="wpmf-gallery-fields">
            <label><?php esc_html_e('Gallery theme', 'wp-media-folder-gallery-addon') ?></label>
            <?php foreach ($themes as $key => $theme) : ?>
                <div class="wpmf-gallery-field wpmf-theme-item"
                     data-theme="<?php echo esc_html($key) ?>">
                    <span class="wpmf-theme-item__start-detail white-bg" role="presentation">
                        <i class="material-icons"><?php echo esc_attr($theme['icon']) ?></i>
                    </span>
                    <span class="wpmf-theme-item__text" title="girl"><?php echo esc_html($theme['title']) ?></span>
                    <i class="material-icons ckecked-theme"> check_circle_outline </i>
                </div>
            <?php endforeach; ?>
            <input type="hidden" class="new-gallery-theme">
        </div>

        <div class="wpmf-gallery-fields">
            <button type="button" class="ju-button orange-button wpmf-save-gallery btn_create_gallery">
                <?php esc_html_e('Create', 'wp-media-folder-gallery-addon') ?>
            </button>

            <span class="spinner"></span>
        </div>
    </div>
</div>

<!-- Edit form -->
<div class="ju-right-panel form_edit_gallery">
    <div class="gallery-toolbar">
        <button type="button" class="ju-button orange-button wpmf-save-gallery btn_edit_gallery <?php echo ($type === 'iframe') ? 'wpmf-modal-save' : '' ?>">
            <?php esc_html_e('Save', 'wp-media-folder-gallery-addon') ?>
        </button>

        <?php if ($type === 'iframe') : ?>
            <button type="button"
                    class="ju-button btn_insert_gallery"><?php esc_html_e('Insert', 'wp-media-folder-gallery-addon') ?></button>
        <?php endif; ?>

        <button type="button" class="ju-button wpmf-remove-imgs-btn wpmf_open_qtip"
                data-for="<?php esc_html_e('Delete selected images', 'wp-media-folder-gallery-addon') ?>"><?php esc_html_e('Delete', 'wp-media-folder-gallery-addon') ?></button>
    </div>
    <div class="gallery-options-wrap">
        <div class="gallery-top-tabs-wrapper">
            <ul class="tabs gallery-ju-top-tabs">
                <li class="tab-link current" data-tab="main-gallery">
                    <?php esc_html_e('General', 'wp-media-folder-gallery-addon') ?>
                </li>

                <li class="tab-link" data-tab="main-gallery-settings">
                    <?php esc_html_e('Gallery display settings', 'wp-media-folder-gallery-addon') ?>
                </li>
            </ul>
        </div>

        <div id="main-gallery" class="gallery-tab-content current">
            <div class="wpmf-gallery-fields">
                <div class="wpmf-gallery-field">
                    <label><?php esc_html_e('Gallery name', 'wp-media-folder-gallery-addon') ?></label>
                    <input type="text" size="35" class="edit-gallery-name gallery_name ju-input"
                           placeholder="<?php esc_html_e('Title', 'wp-media-folder-gallery-addon') ?>">
                </div>

                <div class="wpmf-gallery-field">
                    <label><?php esc_html_e('Gallery level', 'wp-media-folder-gallery-addon') ?></label>
                    <div class="sl-gallery-parent-wrap">
                        <?php
                        $dropdown_options = array(
                            'show_option_none' => __('Parent gallery', 'wp-media-folder-gallery-addon'),
                            'option_none_value' => 0,
                            'hide_empty' => false,
                            'hierarchical' => true,
                            'orderby' => 'name',
                            'taxonomy' => WPMF_GALLERY_ADDON_TAXO,
                            'id' => 'edit-gallery-parent',
                            'class' => 'wpmf-gallery-categories edit-gallery-parent ju-select',
                            'name' => 'edit-gallery-parent',
                            'selected' => 0
                        );
                        wp_dropdown_categories($dropdown_options);
                        ?>
                    </div>
                </div>
            </div>

            <div class="wpmf-gallery-fields">
                <div class="wpmf-gallery-field">
                    <label><?php esc_html_e('Upload images', 'wp-media-folder-gallery-addon') ?></label>
                    <?php if ($pagenow === 'upload.php') : ?>
                        <button type="button" class="ju-button ju-button-orange-blur btn_import_image_fromwp">
                            <span class="dashicons dashicons-wordpress"></span>
                            <span><?php esc_html_e('From wordpress', 'wp-media-folder-gallery-addon') ?></span>
                        </button>
                    <?php else :?>
                        <a href="upload.php?page=media-folder-galleries&view=framemedia&width=0&height=0&noheader=1"
                           class="thickbox ju-button btn_modal_import_image_fromwp">
                            <?php esc_html_e('From wordpress', 'wp-media-folder-gallery-addon') ?>
                        </a>
                    <?php endif; ?>
                    <form id="wpmfglr_form_upload" method="post"
                          action="<?php echo esc_html(admin_url('admin-ajax.php')) ?>"
                          enctype="multipart/form-data">
                        <input class="hide" type="file" name="wpmf_gallery_file[]" multiple id="wpmf_gallery_file">
                        <input type="hidden" name="wpmf_gallery_nonce"
                               value="<?php echo esc_html(wp_create_nonce('wpmf_gallery_nonce')) ?>">
                        <button type="button" class="ju-button ju-button-orange-blur btn_upload_from_pc">
                            <i class="material-icons"> laptop </i>
                            <span><?php esc_html_e('From computer', 'wp-media-folder-gallery-addon') ?></span>
                        </button>
                        <input type="hidden" name="action" value="wpmfgallery">
                        <input type="hidden" name="up_gallery_id" class="up_gallery_id" value="0">
                        <input type="hidden" name="task" value="gallery_uploadfile">
                    </form>

                    <?php
                    if (!empty($google_photo_config['connected'])) :
                        ?>
                        <a href="#"
                           class="thickbox ju-button ju-button-orange-blur btn_import_from_google_photos">
                            <span class="google_photo_icon"></span>
                            <?php esc_html_e('From Google Photos', 'wp-media-folder-gallery-addon') ?>
                        </a>
                        <?php
                    endif;
                    ?>
                    <div class="wpmf-process-bar-full">
                        <div class="wpmf-process-bar" data-w="0"></div>
                    </div>
                </div>

            </div>

            <div class="wpmf-gallery-fields" style="display: inline-block; margin: 0;">
                <label style="vertical-align: middle;display: inline-block;line-height: 46px; width: auto">
                    <?php esc_html_e('Gallery images', 'wp-media-folder-gallery-addon') ?>
                </label>

                <?php
                $limit = get_option('wpmf_gallery_img_per_page');
                require_once(WPMF_GALLERY_ADDON_PLUGIN_DIR . '/admin/pages/screen_per_page.php');
                ?>
            </div>

            <div class="wpmf-gallery-selection-wrap">
                <img class="wpmf-gallery-loading" src="<?php echo esc_url(plugin_dir_url(WPMF_GALLERY_ADDON_FILE) . 'assets/images/material_design_loading.gif') ?>">
                <div class="wpmf_gallery_selection" id="wpmf_gallery_selection">

                </div>

                <div class="wpmf-gallery-image-pagging"></div>
            </div>
        </div>

        <div id="main-gallery-settings" class="gallery-tab-content" data-theme="default">
            <div class="wpmf-gallery-fields">
                <label><?php esc_html_e('Gallery theme', 'wp-media-folder-gallery-addon') ?></label>
                <?php foreach ($themes as $key => $theme) : ?>
                    <div class="wpmf-gallery-field wpmf-theme-item"
                         data-theme="<?php echo esc_html($key) ?>">
                    <span class="wpmf-theme-item__start-detail white-bg" role="presentation">
                        <i class="material-icons"><?php echo esc_attr($theme['icon']) ?></i>
                    </span>
                        <span class="wpmf-theme-item__text" title="girl"><?php echo esc_html($theme['title']) ?></span>
                        <i class="material-icons ckecked-theme"> check_circle_outline </i>
                    </div>
                <?php endforeach; ?>
                <input type="hidden" class="edit-gallery-theme">
            </div>

            <div class="wpmf-gallery-fields">
                <div class="wpmf-gallery-field">
                    <label><?php esc_html_e('Columns', 'wp-media-folder-gallery-addon') ?></label>
                    <div>
                        <select class="edit-gallery-columns ju-select" data-param="columns"
                                name="edit-gallery-columns">
                            <?php for ($i = 1; $i <= 8; $i ++) { ?>
                                <option value="<?php echo esc_html($i) ?>">
                                    <?php echo esc_html($i) ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                </div>

                <div class="wpmf-gallery-field">
                    <label><?php esc_html_e('Gallery image size', 'wp-media-folder-gallery-addon') ?></label>
                    <div>
                        <select class="edit-gallery-size ju-select" data-param="size"
                                name="edit-gallery-size">
                            <?php
                            $sizes_value = json_decode(get_option('wpmf_gallery_image_size_value'));
                            $sizes       = apply_filters('image_size_names_choose', array(
                                'thumbnail' => __('Thumbnail', 'wp-media-folder-gallery-addon'),
                                'medium'    => __('Medium', 'wp-media-folder-gallery-addon'),
                                'large'     => __('Large', 'wp-media-folder-gallery-addon'),
                                'full'      => __('Full Size', 'wp-media-folder-gallery-addon'),
                            ));
                            ?>

                            <?php foreach ($sizes_value as $key) : ?>
                                <?php if (!empty($sizes[$key])) : ?>
                                    <option value="<?php echo esc_attr($key); ?>">
                                        <?php echo esc_html($sizes[$key]); ?>
                                    </option>
                                <?php endif; ?>

                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="wpmf-gallery-field">
                    <label><?php esc_html_e('Lightbox size', 'wp-media-folder-gallery-addon') ?></label>
                    <div>
                        <select class="edit-gallery-targetsize ju-select" data-param="targetsize"
                                name="edit-gallery-targetsize">
                            <?php
                            $sizes_value = json_decode(get_option('wpmf_gallery_image_size_value'));
                            $sizes       = apply_filters('image_size_names_choose', array(
                                'thumbnail' => __('Thumbnail', 'wp-media-folder-gallery-addon'),
                                'medium'    => __('Medium', 'wp-media-folder-gallery-addon'),
                                'large'     => __('Large', 'wp-media-folder-gallery-addon'),
                                'full'      => __('Full Size', 'wp-media-folder-gallery-addon'),
                            ));
                            ?>

                            <?php foreach ($sizes_value as $key) : ?>
                                <?php if (!empty($sizes[$key])) : ?>
                                    <option value="<?php echo esc_attr($key); ?>">
                                        <?php echo esc_html($sizes[$key]); ?>
                                    </option>
                                <?php endif; ?>

                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>

            <div class="wpmf-gallery-fields">
                <div class="wpmf-gallery-field">
                    <label><?php esc_html_e('Action on click', 'wp-media-folder-gallery-addon') ?></label>
                    <div>
                        <select class="edit-gallery-link ju-select" data-param="link"
                                name="edit-gallery-link">
                            <option value="file">
                                <?php esc_html_e('Lightbox', 'wp-media-folder-gallery-addon'); ?>
                            </option>
                            <option value="post">
                                <?php esc_html_e('Attachment Page', 'wp-media-folder-gallery-addon'); ?>
                            </option>
                            <option value="none">
                                <?php esc_html_e('None', 'wp-media-folder-gallery-addon'); ?>
                            </option>
                        </select>
                    </div>
                </div>

                <div class="wpmf-gallery-field">
                    <label><?php esc_html_e('Order by', 'wp-media-folder-gallery-addon') ?></label>
                    <div>
                        <select class="edit-gallery-orderby ju-select" data-param="orderby"
                                name="edit-gallery-orderby">
                            <option value="post__in">
                                <?php esc_html_e('Custom', 'wp-media-folder-gallery-addon'); ?>
                            </option>
                            <option value="rand">
                                <?php esc_html_e('Random', 'wp-media-folder-gallery-addon'); ?>
                            </option>
                            <option value="title">
                                <?php esc_html_e('Title', 'wp-media-folder-gallery-addon'); ?>
                            </option>
                            <option value="date">
                                <?php esc_html_e('Date', 'wp-media-folder-gallery-addon'); ?>
                            </option>
                        </select>
                    </div>
                </div>

                <div class="wpmf-gallery-field">
                    <label><?php esc_html_e('Order', 'wp-media-folder-gallery-addon') ?></label>
                    <div>
                        <select class="edit-gallery-order ju-select" data-param="order"
                                name="edit-gallery-order">
                            <option value="ASC">
                                <?php esc_html_e('Ascending', 'wp-media-folder-gallery-addon'); ?>
                            </option>
                            <option value="DESC">
                                <?php esc_html_e('Descending', 'wp-media-folder-gallery-addon'); ?>
                            </option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="wpmf-gallery-fields wpmf-gallery-fields-slider">
                <div class="wpmf-gallery-field">
                    <label><?php esc_html_e('Transition type', 'wp-media-folder-gallery-addon') ?></label>
                    <div>
                        <select class="edit-gallery-animation ju-select" data-param="animation"
                                name="edit-gallery-animation">
                            <option value="slide">
                                <?php esc_html_e('Slide', 'wp-media-folder-gallery-addon'); ?>
                            </option>
                            <option value="fade">
                                <?php esc_html_e('Fade', 'wp-media-folder-gallery-addon'); ?>
                            </option>
                        </select>
                    </div>
                </div>

                <div class="wpmf-gallery-field">
                    <label><?php esc_html_e('Transition duration (ms)', 'wp-media-folder-gallery-addon') ?></label>
                    <div>
                        <input type="number" class="edit-gallery-duration ju-input" data-param="duration"
                                name="edit-gallery-duration" value="4000">
                    </div>
                </div>

                <div class="wpmf-gallery-field">
                    <label><?php esc_html_e('Automatic animation', 'wp-media-folder-gallery-addon') ?></label>
                    <select class="edit-gallery-auto_animation ju-select" data-param="auto_animation"
                            name="edit-gallery-auto_animation">
                        <option value="1">
                            <?php esc_html_e('On', 'wp-media-folder-gallery-addon'); ?>
                        </option>
                        <option value="0">
                            <?php esc_html_e('Off', 'wp-media-folder-gallery-addon'); ?>
                        </option>
                    </select>
                </div>
            </div>

            <div class="wpmf-gallery-fields wpmf-gallery-fields-switch">
                <div class="wpmf-gallery-field wpmf-gallery-fields-flowslide">
                    <label style="width: auto; margin: 0; line-height: 50px;">
                        <?php esc_html_e('Show buttons', 'wp-media-folder-gallery-addon') ?>
                    </label>
                    <div class="ju-switch-button">
                        <label class="switch">
                            <input type="checkbox" class="gallery_flow_show-buttons" value="1">
                            <span class="slider round"></span>
                        </label>
                    </div>
                </div>

                <div class="wpmf-gallery-field">
                    <label class="wpmf_open_qtip" style="width: auto; margin: 0; line-height: 50px;"
                           data-for="<?php esc_html_e('Load gallery tree navigation', 'wp-media-folder-gallery-addon') ?>">
                        <?php esc_html_e('Gallery navigation', 'wp-media-folder-gallery-addon') ?>
                    </label>
                    <div class="ju-switch-button">
                        <label class="switch">
                            <input type="checkbox" class="gallery_display_tree" value="1">
                            <span class="slider round"></span>
                        </label>
                    </div>
                </div>
                <div class="wpmf-gallery-field">
                    <label class="wpmf_open_qtip" style="width: auto; margin: 0; line-height: 50px;"
                           data-for="<?php esc_html_e('Display image
                        tags as display filter', 'wp-media-folder-gallery-addon') ?>">
                        <?php esc_html_e('Display images tags', 'wp-media-folder-gallery-addon') ?></label>
                    <div class="ju-switch-button">
                        <label class="switch">
                            <input type="checkbox" class="gallery_display_tag" value="1">
                            <span class="slider round"></span>
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="wpmf-drop-overlay" class="wpmf-drop-overlay"><div class="wpmf-overlay-inner"><?php esc_html_e('DROP IMAGES HERE TO UPLOAD', 'wp-media-folder-gallery-addon') ?></div></div>