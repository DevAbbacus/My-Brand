<div class="content-wpmf-google-drive">
    <?php do_action('cloudconnector_wpmf_display_gpt_settings'); ?>
    <div class="gpt-connector-form">
        <h4><?php esc_html_e('Google Client ID', 'wpmfAddon') ?></h4>
        <div>
            <input title name="googlePhotoClientId" type="text" class="regular-text wpmf_width_100 p-lr-20"
                   value="<?php echo esc_attr($google_photo_config['googleClientId']) ?>">
            <p class="description" id="tagline-description">
                <?php esc_html_e('The Client ID for Web application available in your google Developers Console.
                     Click on documentation link below for more info', 'wpmfAddon') ?>
            </p>
        </div>
    </div>

    <div class="gpt-connector-form">
        <h4><?php esc_html_e('Google Client Secret', 'wpmfAddon') ?></h4>
        <div>
            <input title name="googlePhotoClientSecret" type="text" class="regular-text wpmf_width_100 p-lr-20"
                   value="<?php echo esc_attr($google_photo_config['googleClientSecret']) ?>">
            <p class="description" id="tagline-description">
                <?php esc_html_e('The Client secret for Web application available in your google Developers Console.
                     Click on documentation link below for more info', 'wpmfAddon') ?>
            </p>
        </div>
    </div>

    <div class="gpt-connector-form">
        <h4><?php esc_html_e('JavaScript origins', 'wpmfAddon') ?></h4>
        <div>
            <input title name="javaScript_origins" type="text" id="siteurl" readonly
                   value="<?php echo esc_attr(site_url()); ?>"
                   class="regular-text wpmf_width_100 p-lr-20">
        </div>
    </div>

    <div class="gpt-connector-form">
        <div class="wpmf_row_full" style="margin: 0; position: relative;">
            <h4><?php esc_html_e('Redirect URIs', 'wpmfAddon') ?></h4>
            <div class="wpmf_copy_shortcode" data-input="redirect_uris_google_photo" style="margin: 5px 0">
                <i data-alt="<?php esc_html_e('Copy shortcode', 'wpmfAddon'); ?>"
                   class="material-icons wpmfqtip">content_copy</i>
                <label><?php esc_html_e('COPY', 'wpmfAddon'); ?></label>
            </div>
        </div>

        <div>
            <input title name="redirect_uris"
                   type="text" readonly
                   value="<?php echo esc_attr(admin_url('options-general.php?page=option-folder&task=wpmf&function=wpmf_google_photo_authenticated')) ?>"
                   class="regular-text wpmf_width_100 code p-lr-20 redirect_uris_google_photo">
        </div>
    </div>

    <a target="_blank" class="m-t-30 ju-button no-background orange-button waves-effect waves-light"
       href="https://www.joomunited.com/documentation/wp-media-folder-cloud-addon#toc-ii-connect-google-photo">
        <?php esc_html_e('Read the online documentation', 'wpmfAddon') ?>
    </a>
</div>