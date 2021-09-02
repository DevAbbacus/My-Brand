<?php
$class[] = 'wpmf-gallerys wpmf-gallerys-addon wpmf-flipster';
$class[] = 'wpmf-has-border-radius-' . $img_border_radius;
if (count($attachments) === 1) {
    $class[] = 'wpmf-single-item';
}
$class = implode(' ', $class);
$style = '';
if ($img_shadow !== '') {
    $style .= '#' . $selector . ' .wpmf-gallery-item.flipster__item--current img:not(.glrsocial_image):hover, #' . $selector . ' .wpmf-gallery-item.flipster__item--current .wpmf_overlay {box-shadow: ' . $img_shadow . ' !important; transition: all 200ms ease;}';
}

if ($border_style !== 'none') {
    $style .= '#' . $selector . ' .wpmf-gallery-item img:not(.glrsocial_image) {border: ' . $border_color . ' ' . $border_width . 'px ' . $border_style . '}';
}

wp_add_inline_style('wpmf-gallery-style', $style);
if (isset($is_divi) && (int)$is_divi === 1) {
    // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- This variable is html
    echo '<style>' . $style . '</style>';
}
$lightbox_items = $this->getLightboxItems($attachments, $targetsize);
echo '<div class="' . esc_attr($class) . '" data-theme="'. esc_attr($display) .'" data-id="' . esc_attr($id) . '" data-lightbox-items="'. esc_attr(json_encode($lightbox_items)) .'">';
echo '<div id="' . esc_attr($selector) . '" class="flipster" data-button="' . esc_attr($show_buttons) . '">';
echo '<ul>';
foreach ($attachments as $attachment) {
    $post_title = (!empty($caption_lightbox) && $attachment->post_excerpt !== '') ? $attachment->post_excerpt : $attachment->post_title;
    $link_target = get_post_meta($attachment->ID, '_gallery_link_target', true);
    $img_tags = get_post_meta($attachment->ID, 'wpmf_img_tags', true);
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
                $icon = '<a data-swipe="1" href="' . esc_url($url) . '" data-title="' . esc_attr($post_title) . '"
class="wpmfgalleryaddonswipe wpmf_gallery_lightbox wpmf_overlay '. (!empty($remote_video) ? 'isvideo' : '') .'"></a>';
                $icon .= $social;
        }
    }

    echo '<li class="wpmf-gallery-item wpmf-gallery-icon" data-tags="' . esc_html($img_tags) . '">';
    echo $icon . $image_output; // phpcs:ignore WordPress.Security.EscapeOutput -- Content already escaped in the method
    echo '<div class="flipster_infos">';
    if (trim($attachment->post_title) !== '') {
        // phpcs:ignore WordPress.Security.EscapeOutput -- Content already escaped in the method
        echo '<div class="h5">' . wptexturize($attachment->post_title) . '</div>';
    }

    if (trim($attachment->post_excerpt) !== '') {
        // phpcs:ignore WordPress.Security.EscapeOutput -- Content already escaped in the method
        echo '<span>' . wptexturize($attachment->post_excerpt) . '</span>';
    }
    echo '</div>';
    echo '</li>';
}
echo "</ul></div></div>\n";
