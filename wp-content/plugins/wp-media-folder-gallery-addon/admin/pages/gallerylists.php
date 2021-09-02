<?php
/* Prohibit direct script loading */
defined('ABSPATH') || die('No direct script access allowed!');

// phpcs:disable WordPress.Security.NonceVerification.Recommended -- No action, nonce is not required
$params = array(
    'idblock' => '',
    'gallery_id' => 0,
    'display' => 'default',
    'display_tree' => 0,
    'display_tag' => 0,
    'columns' => 3,
    'size' => 'medium',
    'targetsize' => 'large',
    'link' => 'file',
    'wpmf_orderby' => 'post__in',
    'wpmf_order' => 'ASC',
    'animation' => 'slide',
    'duration' => 4000,
    'auto_animation' => 1,
    'show_buttons' => 1
);

foreach ($params as $key => &$default) {
    if (isset($_GET[$key])) {
        $default = $_GET[$key];
    }
}
// phpcs:enable
?>
<div id="WpmfGalleryList" data-idblock="<?php echo isset($idblock) ? esc_attr($idblock) : '' ?>"
     class="<?php echo (isset($editor_type) && $editor_type === 'wpmfgutenberg') ? 'WpmfGalleryList wpmfgutenberg ju-main-wrapper' : 'WpmfGalleryList ju-main-wrapper' ?>">
    <?php wp_nonce_field('wpmfgallery', '_wpnonce', true, true); ?>
    <div id="gallerylist" class="gallerylist ju-left-panel"
         data-edited="<?php echo esc_attr(json_encode($params)) ?>"
    >
        <div class="topbtn">
            <a href="#new-gallery-popup" class="new-gallery-popup ju-button ju-rect-button">
                <i class="zmdi zmdi-plus"></i>
                <span><?php esc_html_e('Gallery', 'wp-media-folder-gallery-addon') ?></span>
            </a>
            <button class="ju-button ju-rect-button btn_import_fromwp">
                <i class="zmdi zmdi-folder-outline"></i>
                <span><?php esc_html_e('Gallery from folder', 'wp-media-folder-gallery-addon') ?></span>
            </button>
        </div>
        <div class="tree_view"></div>
    </div>

    <?php
    require_once(WPMF_GALLERY_ADDON_PLUGIN_DIR . '/admin/pages/form_gallery_edit.php');
    ?>
</div>