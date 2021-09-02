<?php
/* Prohibit direct script loading */
defined('ABSPATH') || die('No direct script access allowed!');
?>
<div data-id="<?php echo esc_html($image->ID) ?>" class="gallery-attachment">
    <div class="wpmfglr-attachment-preview">
        <img src="<?php echo esc_html($thumnailUrl) ?>">
        <div class="hover_img">
            <div class="action_images">
                <a data-id="<?php echo esc_html($image->ID) ?>" class="edit_image_selection">
                    <i data-id="<?php echo esc_html($image->ID) ?>" class="material-icons"> tune </i>
                </a>
                <a data-id="<?php echo esc_html($image->ID) ?>" class="delete_image_selection">
                    <i data-id="<?php echo esc_html($image->ID) ?>" class="material-icons"> delete_outline </i>
                </a>
            </div>
        </div>
        <i class="material-icons img-checked"> check_circle_outline </i>
    </div>
</div>