<div class="content-wpmf-google-drive">
    <?php do_action('cloudconnector_wpmf_display_ggd_settings'); ?>
    <div>
        <h4 data-alt="<?php esc_attr_e('Use a personal account to connect a single Google Drive account or use a G Suite account to connect shared team Drives', 'wpmfAddon') ?>" class="wpmfqtip"><?php esc_html_e('Drive type', 'wpmfAddon') ?></h4>
        <div>
            <select name="google_drive_type" <?php echo (!empty($googleconfig['connected'])) ? 'disabled' : '' ?>>
                <option value="my_drive" <?php selected($googleconfig['drive_type'], 'my_drive') ?>><?php esc_html_e('My Drive', 'wpmfAddon') ?></option>
                <option value="team_drive" <?php selected($googleconfig['drive_type'], 'team_drive') ?>><?php esc_html_e('Shared drives', 'wpmfAddon') ?></option>
            </select>
        </div>
    </div>

    <div>
        <h4 data-alt="<?php esc_attr_e('Define the type of link use by default when you insert a cloud media in a page or post. Public link will generate a public accessible link for your file and affect the appropriate rights on the cloud file. Private link will hide the cloud link to keep the original access right of your file', 'wpmfAddon') ?>" class="wpmfqtip"><?php esc_html_e('Media link type', 'wpmfAddon') ?></h4>
        <div>
            <select name="google_link_type">
                <option value="public" <?php selected($googleconfig['link_type'], 'public') ?>><?php esc_html_e('Public link', 'wpmfAddon') ?></option>
                <option value="private" <?php selected($googleconfig['link_type'], 'private') ?>><?php esc_html_e('Private link', 'wpmfAddon') ?></option>
            </select>
        </div>
    </div>

    <div class="ggd-connector-form">
        <h4><?php esc_html_e('Google Client ID', 'wpmfAddon') ?></h4>
        <div>
            <input title name="googleClientId" type="text" class="regular-text wpmf_width_100 p-lr-20"
                   value="<?php echo esc_attr($googleconfig['googleClientId']) ?>">
            <p class="description" id="tagline-description">
                <?php esc_html_e('The Client ID for Web application available in your google Developers Console.
                     Click on documentation link below for more info', 'wpmfAddon') ?>
            </p>
        </div>
    </div>

    <div class="ggd-connector-form">
        <h4><?php esc_html_e('Google Client Secret', 'wpmfAddon') ?></h4>
        <div>
            <input title name="googleClientSecret" type="text" class="regular-text wpmf_width_100 p-lr-20"
                   value="<?php echo esc_attr($googleconfig['googleClientSecret']) ?>">
            <p class="description" id="tagline-description">
                <?php esc_html_e('The Client secret for Web application available in your google Developers Console.
                     Click on documentation link below for more info', 'wpmfAddon') ?>
            </p>
        </div>
    </div>

    <div class="ggd-connector-form">
        <h4><?php esc_html_e('JavaScript origins', 'wpmfAddon') ?></h4>
        <div>
            <input title name="javaScript_origins" type="text" id="siteurl" readonly
                   value="<?php echo esc_attr(site_url()); ?>"
                   class="regular-text wpmf_width_100 p-lr-20">
        </div>
    </div>

    <div class="ggd-connector-form">
        <div class="wpmf_row_full" style="margin: 0; position: relative;">
            <h4><?php esc_html_e('Redirect URIs', 'wpmfAddon') ?></h4>
            <div class="wpmf_copy_shortcode" data-input="redirect_uris_google_drive" style="margin: 5px 0">
                <i data-alt="<?php esc_html_e('Copy shortcode', 'wpmfAddon'); ?>"
                   class="material-icons wpmfqtip">content_copy</i>
                <label><?php esc_html_e('COPY', 'wpmfAddon'); ?></label>
            </div>
        </div>

        <div>
            <input title name="redirect_uris"
                   type="text" readonly
                   value="<?php echo esc_attr(admin_url('options-general.php?page=option-folder&task=wpmf&function=wpmf_authenticated')) ?>"
                   class="regular-text wpmf_width_100 code p-lr-20 redirect_uris_google_drive">
        </div>
    </div>

    <a target="_blank" class="m-t-30 ju-button no-background orange-button waves-effect waves-light"
       href="https://www.joomunited.com/wordpress-documentation/wp-media-folder/286-wp-media-folder-addon-google-drive-integration">
        <?php esc_html_e('Read the online documentation', 'wpmfAddon') ?>
    </a>
</div>