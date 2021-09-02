<?php
if (empty($duration)) {
    $duration = $gallery_configs['theme']['slider_theme']['duration'];
}

if (empty($animation)) {
    $animation = $gallery_configs['theme']['slider_theme']['animation'];
}

if (!isset($auto_animation)) {
    $auto_animation = $gallery_configs['theme']['slider_theme']['auto_animation'];
}

$lightbox_items = $this->getLightboxItems($attachments, $targetsize);
$class[] = 'gallery_addon_flexslider carousel wpmfflexslider';
$class[] = 'wpmf-has-border-radius-' . $img_border_radius;
$class[] = 'wpmf-gutterwidth-' . $gutterwidth;
if ((int)$columns === 1) {
    $class[] = 'wpmf-gg-one-columns';
} else {
    $class[] = 'wpmf-gg-multiple-columns';
}

$class = implode(' ', $class);
$shadow = 0;
$style = '';
if ($img_shadow !== '') {
    if ((int)$columns > 1) {
        $style .= '#' . $selector . ' .wpmf-gallery-item:hover {box-shadow: ' . $img_shadow . ' !important; transition: all 200ms ease;}';
        $shadow = 1;
    }
}

if ((int)$gutterwidth === 0) {
    $shadow = 0;
}
if ($border_style !== 'none') {
    if ((int)$columns === 1) {
        $style .= '#' . $selector . ' .wpmf-gallery-item img:not(.glrsocial_image) {border: ' . $border_color . ' ' . $border_width . 'px ' . $border_style . ';}';
    } else {
        $style .= '#' . $selector . ' .wpmf-gallery-item .wpmf-gallery-icon {border: ' . $border_color . ' ' . $border_width . 'px ' . $border_style . ';}';
    }
} else {
    $border_width = 0;
}

wp_add_inline_style('wpmf-gallery-style', $style);
if (isset($is_divi) && (int)$is_divi === 1) {
    // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- This variable is html
    echo '<style>' . $style . '</style>';
}
echo '<div class="wpmf-gallerys wpmf-gallerys-addon" data-theme="'. esc_attr($display) .'">';
echo '<div id="' . esc_attr($selector) . '" data-id="' . esc_attr($selector) . '" class="' . esc_attr($class) . '" data-gutterwidth="' . esc_attr($gutterwidth) . '" data-border-width="' . esc_attr($border_width) . '" data-wpmfcolumns="' . esc_attr($columns) . '"
 data-duration="' . esc_attr($duration) . '" data-animation="' . esc_attr($animation) . '" data-auto_animation="' . esc_attr($auto_animation) . '" data-lightbox-items="'. esc_attr(json_encode($lightbox_items)) .'">';
echo '<ul class="slides wpmf-slides">';
$i = 0;
foreach ($attachments as $attachment) {
    $post_title = (!empty($caption_lightbox) && $attachment->post_excerpt !== '') ? $attachment->post_excerpt : $attachment->post_title;
    $post_excerpt = esc_html($attachment->post_excerpt);
    $img_tags = get_post_meta($attachment->ID, 'wpmf_img_tags', true);
    $link_target = get_post_meta($attachment->ID, '_gallery_link_target', true);
    $custom_link = get_post_meta($attachment->ID, _WPMF_GALLERY_PREFIX . 'custom_image_link', true);
    if ($custom_link !== '') {
        $image_output = $this->galleryGetAttachmentLink($attachment->ID, $size, false);
        $icon = '<a href="' . $custom_link . '" title="' . esc_attr($post_title) . '" class="wpmf_overlay" target="' . $link_target . '"></a>';
        $icon .= $social;
    } else {
        switch ($link) {
            case 'none':
                $image_output = wp_get_attachment_image($attachment->ID, $size, false, array('data-type' => 'wpmfgalleryimg'));
                $icon = '<span class="wpmf_overlay"></span>';
                $icon .= $social;
                break;

            case 'post':
                $image_output = $this->galleryGetAttachmentLink($attachment->ID, $size, true);
                $url = get_attachment_link($attachment->ID);
                $icon = '<a href="' . esc_url($url) . '" title="' . esc_attr($post_title) . '" class="wpmf_overlay" target="' . $link_target . '"></a>';
                $icon .= $social;
                break;

            default:
                $remote_video = get_post_meta($attachment->ID, 'wpmf_remote_video_link', true);
                $image_output = $this->galleryGetAttachmentLink($attachment->ID, $size, false);
                $item_urls = wp_get_attachment_image_url($attachment->ID, $targetsize);
                $url = (!empty($remote_video)) ? $remote_video : $item_urls;
                $icon = '<a data-swipe="1" href="' . esc_url($url) . '" title="' . esc_attr($post_title) . '"
class="wpmfgalleryaddonswipe wpmf_overlay '. (!empty($remote_video) ? 'isvideo' : '') .'"></a>';
                $icon .= $social;
        }
    }

    echo '<li class="wpmf-gallery-item item" data-tags="' . esc_html($img_tags) . '">';
    echo '<div class="wpmf-gallery-icon">';
    echo $icon . $image_output; // phpcs:ignore WordPress.Security.EscapeOutput -- Content already escaped in the method
    echo '</div>';
    if (trim($attachment->post_excerpt) || trim($attachment->post_title)) {
        echo '<div class="wpmf-front-box top">';
        echo '<a>';
        if (trim($attachment->post_title)) {
            echo '<span class="title">' . esc_html($attachment->post_title) . '</span>';
        }

        if (trim($attachment->post_excerpt)) {
            echo '<span class="caption">' . esc_html($attachment->post_excerpt) . '</span>';
        }
        echo '</a>';
        echo '</div>';
    }
    echo $social; // phpcs:ignore WordPress.Security.EscapeOutput -- Content already escaped in the method
    echo '</li>';
}


echo '</ul>';
echo '</div></div>';
