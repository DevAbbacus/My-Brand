<div class="content-wpmf-dropbox">
    <?php
    if (isset($dropbox_error) && $dropbox_error !== '') {
        echo '<p style="color: #f00">'. esc_html($dropbox_error) .'</p>';
    }

    do_action('cloudconnector_wpmf_display_dropbox_settings');
    ?>
    <div>
        <h4 data-alt="<?php esc_attr_e('Define the type of link use by default when you insert a cloud media in a page or post. Public link will generate a public accessible link for your file and affect the appropriate rights on the cloud file. Private link will hide the cloud link to keep the original access right of your file', 'wpmfAddon') ?>" class="wpmfqtip"><?php esc_html_e('Media link type', 'wpmfAddon') ?></h4>
        <div>
            <select name="dropbox_link_type">
                <option value="public" <?php selected($dropboxconfig['link_type'], 'public') ?>><?php esc_html_e('Public link', 'wpmfAddon') ?></option>
                <option value="private" <?php selected($dropboxconfig['link_type'], 'private') ?>><?php esc_html_e('Private link', 'wpmfAddon') ?></option>
            </select>
        </div>
    </div>

    <div class="dropbox-connector-form">
        <h4><?php esc_html_e('App Key', 'wpmfAddon') ?></h4>
        <div>
            <input title name="dropboxKey" type="text" class="regular-text wpmf_width_100 p-lr-20"
                   value="<?php echo esc_attr($dropboxconfig['dropboxKey']) ?>">
        </div>
    </div>

    <div class="dropbox-connector-form">
        <h4><?php esc_html_e('App Secret', 'wpmfAddon') ?></h4>
        <div>
            <input title name="dropboxSecret" type="text" class="regular-text wpmf_width_100 p-lr-20"
                   value="<?php echo esc_attr($dropboxconfig['dropboxSecret']) ?>">
        </div>
    </div>

    <?php if (empty($dropboxconfig['dropboxToken'])) { ?>
        <div class="dropbox-connector-form">
            <h4><?php esc_html_e('Authorization Code', 'wpmfAddon') ?></h4>
            <div>
                <input title name="dropboxAuthor" type="text" value="" class="regular-text wpmf_width_100 p-lr-20">
            </div>
        </div>
    <?php } else { ?>
        <div  class="dropbox-connector-form" style="display: none">
            <h4><?php esc_html_e('Authorization Code', 'wpmfAddon') ?></h4>
            <div>
                <input title name="dropboxAuthor" type="text" value="" class="regular-text wpmf_width_100 p-lr-20">
            </div>
        </div>
    <?php } ?>

    <a target="_blank" class="m-t-30 ju-button no-background orange-button waves-effect waves-light"
       href="https://www.joomunited.com/wordpress-documentation/wp-media-folder/521-wp-media-folder-addon-dropbox-integration">
        <?php esc_html_e('Read the online documentation', 'wpmfAddon') ?>
    </a>
</div>