<div class="content-wpmf-onedrive">
    <?php
    $appInfo = $onedriveBusinessDrive->getClient();
    $hasToken = $onedriveBusinessDrive->loadToken();
    $btnconnect = '';

    if (is_wp_error($appInfo)) {
        echo '<div id="message" class="error"><p>' . esc_html($appInfo->get_error_message()) . '</p></div>';
        return false;
    }

    if ($appInfo) {
        $authUrl = $onedriveBusinessDrive->getAuthUrl();
        if (!is_wp_error($authUrl)) {
            $btnconnect = '<a class="ju-button orange-button waves-effect waves-light btndrive wpmf_onedrive_login odb-connector-button" href="#"
         onclick="window.location.assign(\'' . $authUrl . '\',\'foo\',\'width=600,height=600\');return false;">' . __('Connect OneDrive Business', 'wpmfAddon') . '</a>';
        }
    }

    ?>

    <div id="config_onedrive_business" class="div_list wpmf_width_100">
        <?php
        do_action('cloudconnector_wpmf_display_onedrive_business_connect_button');

        if (!empty($business_config['OneDriveClientId']) && !empty($business_config['OneDriveClientSecret'])) {
            if (isset($business_config['connected']) && (int)$business_config['connected'] === 1) {
                $client = $onedriveBusinessDrive->startClient();
                $btndisconnect = '<a class="ju-button no-background orange-button waves-effect waves-light btndrive wpmf_onedrive_business_logout odb-connector-button">' . __('Disconnect OneDrive Business', 'wpmfAddon') . '</a>';
                $driveInfo = $onedriveBusinessDrive->getDriveInfo();
                // phpcs:disable WordPress.Security.EscapeOutput -- Content already escaped in the method
                if (!$driveInfo || is_wp_error($driveInfo)) {
                    echo $btnconnect;
                } else {
                    echo $btndisconnect;
                }
                // phpcs:enable
            } else {
                echo $btnconnect; // phpcs:disable WordPress.Security.EscapeOutput -- Content already escaped in the method
            }
        }

        do_action('cloudconnector_wpmf_display_onedrive_business_settings');
        ?>
        <div class="wpmf_width_100 ju-settings-option box-shadow-none m-b-0">
            <div>
                <h4 data-alt="<?php esc_attr_e('Define the type of link use by default when you insert a cloud media in a page or post. Public link will generate a public accessible link for your file and affect the appropriate rights on the cloud file. Private link will hide the cloud link to keep the original access right of your file', 'wpmfAddon') ?>" class="wpmfqtip"><?php esc_html_e('Media link type', 'wpmfAddon') ?></h4>
                <div>
                    <select name="onedrive_business_link_type">
                        <option value="public" <?php selected($business_config['link_type'], 'public') ?>><?php esc_html_e('Public link', 'wpmfAddon') ?></option>
                        <option value="private" <?php selected($business_config['link_type'], 'private') ?>><?php esc_html_e('Private link', 'wpmfAddon') ?></option>
                    </select>
                </div>
            </div>

            <div class="odb-connector-form">
                <h4><?php esc_html_e('OneDrive Client ID', 'wpmfAddon') ?></h4>
                <div>
                    <input title name="OneDriveBusinessClientId" type="text"
                           class="onedrivebusinessconfig regular-text wpmf_width_100 p-lr-20"
                           value="<?php echo esc_attr($business_config['OneDriveClientId']) ?>">
                    <p class="description" id="tagline-description">
                        <?php esc_html_e('Insert your OneDrive Application Id here.
                     You can find this Id in the OneDrive dev center', 'wpmfAddon') ?>
                    </p>
                </div>
            </div>

            <div class="odb-connector-form">
                <h4><?php esc_html_e('OneDrive Client Secret', 'wpmfAddon') ?></h4>
                <div>
                    <input title name="OneDriveBusinessClientSecret" type="text"
                           class="onedrivebusinessconfig regular-text wpmf_width_100 p-lr-20"
                           value="<?php echo esc_attr($business_config['OneDriveClientSecret']) ?>">
                    <p class="description" id="tagline-description">
                        <?php esc_html_e('Insert your OneDrive Secret here.
                     You can find this secret in the OneDrive dev center', 'wpmfAddon') ?>
                    </p>
                </div>
            </div>

            <div class="odb-connector-form">
                <div class="wpmf_row_full" style="margin: 0; position: relative;">
                    <h4><?php esc_html_e('Redirect URIs', 'wpmfAddon') ?></h4>
                    <div class="wpmf_copy_shortcode" data-input="redirect_uris_onedrive_bussiness" style="margin: 5px 0">
                        <i data-alt="<?php esc_html_e('Copy shortcode', 'wpmfAddon'); ?>"
                           class="material-icons wpmfqtip">content_copy</i>
                        <label><?php esc_html_e('COPY', 'wpmfAddon'); ?></label>
                    </div>
                </div>

                <div>
                    <input title name="redirect_uris" type="text" readonly
                           value="<?php echo esc_attr(admin_url('upload.php')); ?>"
                           class="regular-text wpmf_width_100 p-lr-20 code redirect_uris_onedrive_bussiness">
                </div>
            </div>

            <a target="_blank" class="m-t-30 ju-button no-background orange-button waves-effect waves-light"
               href="https://www.joomunited.com/wordpress-documentation/wp-media-folder/288-wp-media-folder-addon-onedrive-business-integration">
                <?php esc_html_e('Read the online documentation', 'wpmfAddon') ?>
            </a>
        </div>
    </div>
</div>

<script type="text/javascript">
    jQuery(document).ready(function ($) {
        $('.wpmf_onedrive_business_logout').click(function () {
            $.ajax({
                url: ajaxurl,
                method: 'POST',
                dataType: 'json',
                data: {
                    action: 'wpmf_onedrive_business_logout'
                },
                success: function (response) {
                    location.reload(true);
                }
            });
        });
    });
</script>