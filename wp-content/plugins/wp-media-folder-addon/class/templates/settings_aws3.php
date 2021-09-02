<?php
wp_enqueue_script('wpmf-popup');
wp_enqueue_style('wpmf-css-popup');
?>
<div>
    <div id="download-s3-popup" class="white-popup mfp-hide">
        <h3><?php esc_html_e('Retrieve amazon S3 media', 'wpmfAddon') ?></h3>
        <p class="description"><?php esc_html_e('This action will retrieve all your media from your Amazon S3 bucket and copy it back to your server, links to media will be reverted back to the original local image. This is useful when you want to remove the Amazon integration only.', 'wpmfAddon') ?></p>
        <div class="wpmf-process-bar-full wpmf-process-bar-download-s3-full s3_process_download_wrap">
            <div class="wpmf-process-bar wpmf-process-bar-download-s3" data-w="0"></div>
            <span>0%</span>
        </div>
        <div class="action_download_s3">
            <a class="ju-button wpmf-small-btn btn-cancel-popup-download-s3"><?php esc_html_e('Cancel', 'wpmfAddon') ?></a>
            <a class="ju-button orange-button wpmf-small-btn btn-download-s3"><?php esc_html_e('Retrieve amazon S3 media', 'wpmfAddon') ?></a>
        </div>
    </div>
    <div id="manage-bucket" class="white-popup mfp-hide">
        <div class="table-list-buckets m-b-40">
            <h3><?php esc_html_e('Select an existing bucket', 'wpmfAddon') ?></h3>
            <table class="wpmf_width_100">
                <thead>
                <tr>
                    <th style="width: 30%"><?php esc_html_e('Bucket name', 'wpmfAddon') ?></th>
                    <th style="width: 30%"><?php esc_html_e('Date created', 'wpmfAddon') ?></th>
                    <th style="width: 30%"></th>
                    <th style="width: 10%"></th>
                </tr>
                </thead>
                <tbody>
                <?php if (!empty($list_buckets['Buckets'])) :
                    foreach ($list_buckets['Buckets'] as $bucket) :
                        ?>
                        <tr class="row_bucket <?php echo (isset($aws3config['bucket']) && $aws3config['bucket'] === $bucket['Name']) ? 'bucket-selected' : 'aws3-select-bucket' ?>"
                            data-bucket="<?php echo esc_attr($bucket['Name']) ?>">
                            <td><?php echo esc_html($bucket['Name']) ?></td>
                            <td><?php echo esc_html($bucket['CreationDate']) ?></td>
                            <td>
                                <?php if (isset($aws3config['bucket']) && $aws3config['bucket'] === $bucket['Name']) : ?>
                                    <label class="btn-select-bucket">
                                        <?php esc_html_e('Selected bucket', 'wpmfAddon') ?>
                                    </label>
                                <?php else : ?>
                                    <label class="btn-select-bucket">
                                        <?php esc_html_e('Select bucket', 'wpmfAddon') ?>
                                    </label>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a class="delete-bucket wpmfqtip"
                                   data-alt="<?php esc_html_e('Delete bucket', 'wpmfAddon') ?>"
                                   data-bucket="<?php echo esc_attr($bucket['Name']) ?>"><i class="material-icons">delete_outline</i></a>
                                <img src="<?php echo esc_url(WPMFAD_PLUGIN_URL . 'assets/images/spinner.gif') ?>"
                                     class="spinner-delete-bucket">
                            </td>
                        </tr>
                        <?php
                    endforeach;
                endif; ?>
                </tbody>
            </table>
        </div>

        <div>
            <h3><?php esc_html_e('Create a new bucket', 'wpmfAddon') ?></h3>
            <div>
                <label>
                    <input type="text" class="wpmf_width_100 new-bucket-name"
                           placeholder="<?php esc_html_e('New bucket name', 'wpmfAddon') ?>">
                </label>
            </div>
        </div>

        <div>
            <h3><?php esc_html_e('Region', 'wpmfAddon') ?></h3>
            <div>
                <label>
                    <select class="new-bucket-region">
                        <?php
                        if (!empty($aws3->regions)) {
                            foreach ($aws3->regions as $k_regions => $v_region) :
                                ?>
                                <option value="<?php echo esc_attr($k_regions); ?>"><?php echo esc_html($v_region); ?></option>
                                <?php
                            endforeach;
                        }
                        ?>
                    </select>
                </label>
            </div>
        </div>

        <div class="wpmf_width_100 m-t-20 action-aws-btn">
            <button type="button"
                    class="ju-button wpmf-small-btn cancel-bucket-btn"><?php esc_html_e('Cancel', 'wpmfAddon') ?></button>
            <button type="button"
                    class="ju-button orange-button wpmf-small-btn create-bucket-btn"><?php esc_html_e('Create', 'wpmfAddon') ?></button>
            <span class="spinner create-bucket-spinner"></span>
        </div>
    </div>

    <div class="wpmf_width_100 wpmf-inline">
        <div class="ju-settings-option">
            <div class="wpmf_row_full">
                <input type="hidden" name="aws3_config[copy_files_to_bucket]" value="0">
                <label data-alt="<?php esc_html_e('When a file is uploaded to your media library, a copy will be sent to Amazon bucket. On frontend the media will be loaded from the Amazon server', 'wpmfAddon'); ?>"
                       class="ju-setting-label text"><?php esc_html_e('Copy to Amazon S3', 'wpmfAddon') ?></label>
                <div class="ju-switch-button">
                    <label class="switch">
                        <input type="checkbox" name="aws3_config[copy_files_to_bucket]"
                               value="1"
                            <?php
                            if (isset($copy_files_to_bucket) && (int) $copy_files_to_bucket === 1) {
                                echo 'checked';
                            }
                            ?>
                        >
                        <span class="slider round"></span>
                    </label>
                </div>
            </div>
        </div>

        <div class="ju-settings-option wpmf_right m-r-0">
            <div class="wpmf_row_full">
                <input type="hidden" name="aws3_config[remove_files_from_server]" value="0">
                <label data-alt="<?php esc_html_e('When a file has been uploaded to the Amazon S3 bucket, the local copy will be deleted', 'wpmfAddon'); ?>"
                       class="ju-setting-label text"><?php esc_html_e('Remove after Amazon S3 upload', 'wpmfAddon') ?></label>
                <div class="ju-switch-button">
                    <label class="switch">
                        <input type="checkbox" name="aws3_config[remove_files_from_server]"
                               value="1"
                            <?php
                            if (isset($remove_files_from_server) && (int) $remove_files_from_server === 1) {
                                echo 'checked';
                            }
                            ?>
                        >
                        <span class="slider round"></span>
                    </label>
                </div>
            </div>
        </div>

        <div class="ju-settings-option">
            <div class="wpmf_row_full">
                <input type="hidden" name="aws3_config[attachment_label]" value="0">
                <label data-alt="<?php esc_html_e('Apply a label on each media to visually see that the media is on Amazon S3', 'wpmfAddon'); ?>"
                       class="ju-setting-label text"><?php esc_html_e('Amazon attachment label', 'wpmfAddon') ?></label>
                <div class="ju-switch-button">
                    <label class="switch">
                        <input type="checkbox" name="aws3_config[attachment_label]"
                               value="1"
                            <?php
                            if (isset($attachment_label) && (int) $attachment_label === 1) {
                                echo 'checked';
                            }
                            ?>
                        >
                        <span class="slider round"></span>
                    </label>
                </div>
            </div>
        </div>
    </div>

    <div class="sync-aws3-wrap">
        <div class="s3-process-wrap">
            <div class="s3-process-left">
                <label class="status-text-s3-sync"><span><?php echo esc_html($s3_percent['s3_percent']); ?></span><?php esc_html_e('% of your Media Library has been uploaded to Amazon S3', 'wpmfAddon') ?>
                </label>
                <div class="s3-button-sync-wrap">
                    <div class="wpmf_row_full">
                        <div
                                data-enable="<?php echo !empty($aws3config['copy_files_to_bucket']) ? 1 : 0 ?>"
                                data-alt="<?php esc_attr_e('Synchronize the whole media library with Amazon S3 Note that it applies the options if checked above like removing media from local server.', 'wpmfAddon') ?>"
                                class="wpmfqtip ju-button wpmf-small-btn <?php echo ($connect) ? 'btn-dosync-s3 btn-sync-s3' : 'btn-dosync-s3-disabled' ?>">
                            <labeL><?php esc_html_e('Synchronize with Amazon S3', 'wpmfAddon') ?></labeL><span
                                    class="spinner spinner-syncS3" style="visibility: visible"></span></div>
                    </div>
                </div>
            </div>
            <div class="s3-process-right">
                <div class="syncs3-circle-bar"><strong></strong></div>
                <input type="hidden" id="progressController" value="<?php echo esc_attr($s3_percent['s3_percent']) ?>"/>
                <input type="hidden" id="s3sync_ok" value="<?php echo esc_attr($s3_percent['s3_percent']) ?>"/>
            </div>

            <div class="wpmf-process-bar-full wpmf-process-bar-syncs3-full s3_process_sync_wrap" data-local-files-count="<?php echo esc_attr($s3_percent['local_files_count']) ?>">
                <div class="wpmf-process-bar wpmf-process-bar-syncs3" data-w="0"></div>
                <span>0%</span>
            </div>

            <div style="margin-top: 20px; width: 100%; display: inline-block">
                <h4>
                    <?php esc_html_e('File type to include in synchronization', 'wpmfAddon') ?></h4>
                <label class="wpmf_width_100">
                    <textarea name="allow_syncs3_extensions" class="wpmf_width_100 allow_syncs3_extensions"><?php echo esc_html($allow_syncs3_extensions) ?></textarea>
                </label>
            </div>
        </div>
    </div>
    <div class="ju-settings-option wpmf_width_100 p-d-20 aws3-connect-wrap">
        <h4><?php esc_html_e('Access Key ID', 'wpmfAddon') ?></h4>
        <div>
            <input title="<?php esc_attr_e('Access Key ID', 'wpmfAddon') ?>" autocomplete="off"
                   name="aws3_config[credentials][key]" type="text" class="regular-text wpmf_width_100 p-lr-20"
                   value="<?php echo esc_attr($aws3config['credentials']['key']) ?>">
        </div>

        <div class="">
            <h4><?php esc_html_e('Secret Access Key', 'wpmfAddon') ?></h4>
            <div>
                <input title="<?php esc_attr_e('Secret Access Key', 'wpmfAddon') ?>" autocomplete="off"
                       name="aws3_config[credentials][secret]" type="text"
                       class="regular-text wpmf_width_100 p-lr-20"
                       value="<?php echo esc_attr($aws3config['credentials']['secret']) ?>">
            </div>
        </div>

        <?php
        if (!$connect && !empty($aws3config['credentials']['key']) && !empty($aws3config['credentials']['secret'])) {
            echo '<p class="wpmf-warning"><b>' . esc_html__('Connection failed: ', 'wpmfAddon') . '</b>' . esc_html($msg) . '</p>';
        }
        ?>

        <?php if ($connect) : ?>
            <div class="">
                <h4><?php esc_html_e('Bucket', 'wpmfAddon') ?></h4>
                <div>
                    <label>
                        <?php if (!empty($list_buckets['Buckets'])) : ?>
                            <?php if (!empty($aws3config['bucket'])) : ?>
                                <b class="current_bucket"><?php echo esc_html($aws3config['bucket']); ?></b>
                            <?php else : ?>
                                <b class="current_bucket"><?php esc_html_e('Please select an Amazon bucket to start using S3 server', 'wpmfAddon') ?></b>
                            <?php endif; ?>
                        <?php else : ?>
                            <b class="current_bucket"></b>
                        <?php endif; ?>
                        <?php
                        if (!empty($location_name) && !empty($list_buckets['Buckets'])) {
                            echo '<span class="lb-current-region">' . esc_html($location_name) . '</span>';
                        } else {
                            echo '<span class="lb-current-region"></span>';
                        }
                        ?>
                        <?php if (empty($list_buckets['Buckets'])) : ?>
                            <div class="msg-no-bucket show">
                                <label><?php esc_html_e('No bucket found, please add a bucket to be able to use this feature', 'wpmfAddon') ?></label>
                                <a class="ju-button orange-button wpmf-small-btn aws3-manage-bucket"
                                   href="#manage-bucket">
                                    <?php esc_html_e('Add bucket', 'wpmfAddon') ?>
                                </a>
                            </div>
                        <?php else : ?>
                            <div class="msg-no-bucket">
                                <label><?php esc_html_e('No bucket found, please add a bucket to be able to use this feature', 'wpmfAddon') ?></label>
                                <a class="ju-button orange-button wpmf-small-btn aws3-manage-bucket"
                                   href="#manage-bucket">
                                    <?php esc_html_e('Add bucket', 'wpmfAddon') ?>
                                </a>
                            </div>
                        <?php endif; ?>
                        <a class="ju-button orange-button wpmf-small-btn aws3-manage-bucket"
                           href="#manage-bucket">
                            <?php esc_html_e('Bucket settings and selection', 'wpmfAddon') ?>
                        </a>
                        <?php if (!empty($aws3config['bucket']) && !empty($list_buckets['Buckets'])) : ?>
                            <a class="ju-button wpmf-small-btn aws3-view-console"
                               href="https://console.aws.amazon.com/s3/buckets/<?php echo esc_html($aws3config['bucket'] . '/') ?>"
                               target="_blank"><?php esc_html_e('View console', 'wpmfAddon') ?></a>
                        <?php endif; ?>
                    </label>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <div class="ju-settings-option p-lr-20 wpmf_width_100">
        <div style="margin: 10px 0 20px 0; width: 100%; display: inline-block">
            <h4 style="padding: 0; float: left"><?php esc_html_e('Cloudfront Integration', 'wpmfAddon') ?></h4>
            <div class="ju-switch-button" style="float: left">
                <label class="switch">
                    <input type="hidden" name="aws3_config[enable_custom_domain]" value="0">
                    <input type="checkbox" name="aws3_config[enable_custom_domain]"
                           value="1"
                        <?php
                        if (isset($aws3config['enable_custom_domain']) && (int) $aws3config['enable_custom_domain'] === 1) {
                            echo 'checked';
                        }
                        ?>
                    >
                    <span class="slider round"></span>
                </label>
            </div>
            <label class="ju-setting-label text" style="padding: 0; width: 100%"><?php esc_html_e('Custom Domain (CNAME)', 'wpmfAddon') ?></label>
            <div class="wpmf_width_100">
                <input autocomplete="off"
                       name="aws3_config[custom_domain]" type="text" class="regular-text p-lr-20"
                       value="<?php echo esc_attr($aws3config['custom_domain']) ?>">
            </div>
        </div>
    </div>

    <?php if ($connect) : ?>
    <h2 style="padding: 0; float: left; font-size: 24px;"><?php esc_html_e('Advanced settings and actions', 'wpmfAddon') ?></h2>
    <div class="ju-settings-option p-lr-20 wpmf_width_100">
        <div style="margin: 10px 0 20px 0; width: 100%; display: inline-block">
            <label class="wpmf_width_100 ju-setting-label text" style="padding: 0; margin-bottom: 5px"><?php esc_html_e('Import all the folders and files from S3 server to Media library', 'wpmfAddon') ?></label>
            <select class="wpmf_bucket_import" style="margin-right: 5px">
                <?php
                if (!empty($list_buckets['Buckets'])) {
                    echo '<option value="">'. esc_html__('Choose a bucket', 'wpmfAddon') .'</option>';
                    foreach ($list_buckets['Buckets'] as $from_bucket) {
                        echo '<option value="'. esc_attr($from_bucket['Name']) .'">'. esc_html($from_bucket['Name']) .'</option>';
                    }
                }
                ?>
            </select>
            <button type="button" class="ju-button wpmf-import-s3"><?php esc_html_e('Import from S3', 'wpmfAddon') ?><span class="import-objects-bucket-spinner spinner"></span></button>
        </div>
    </div>

    <div class="ju-settings-option p-lr-20 wpmf_width_100">
        <div style="margin: 10px 0 20px 0; width: 100%; display: inline-block">
            <label class="wpmf_width_100 ju-setting-label text" style="padding: 0; margin-bottom: 5px"><?php esc_html_e('Copy all the files from a bucket to other bucket', 'wpmfAddon') ?></label>
            <select class="wpmf_from_bucket" style="margin-right: 5px">
                <?php
                if (!empty($list_buckets['Buckets'])) {
                    echo '<option value="">'. esc_html__('From bucket', 'wpmfAddon') .'</option>';
                    foreach ($list_buckets['Buckets'] as $from_bucket) {
                        echo '<option value="'. esc_attr($from_bucket['Name']) .'">'. esc_html($from_bucket['Name']) .'</option>';
                    }
                }
                ?>
            </select>

            <select class="wpmf_to_bucket" style="margin-right: 5px">
                <?php
                if (!empty($list_buckets['Buckets'])) {
                    echo '<option value="">'. esc_html__('To bucket', 'wpmfAddon') .'</option>';
                    foreach ($list_buckets['Buckets'] as $to_bucket) {
                        echo '<option value="'. esc_attr($to_bucket['Name']) .'">'. esc_html($to_bucket['Name']) .'</option>';
                    }
                }
                ?>
            </select>
            <button type="button" class="ju-button wpmf-copy-s3"><?php esc_html_e('Copy the files', 'wpmfAddon') ?><span class="copy-objects-bucket-spinner spinner"></span></button>
        </div>
    </div>

    <div class="ju-settings-option p-lr-20 wpmf_width_100">
        <div style="margin: 10px 0 20px 0; width: 100%; display: inline-block">
            <label class="wpmf_width_100 ju-setting-label text" style="padding: 0; margin-bottom: 5px"><?php esc_html_e('Retrieve back all my Amazon S3 media to the media library', 'wpmfAddon') ?></label>
            <a class="ju-button no-background waves-effect waves-light btn-open-popup-download"
               href="#download-s3-popup"
            >
                <?php esc_html_e('Retrieve amazon S3 media', 'wpmfAddon') ?>
            </a>
        </div>
    </div>
    <?php endif; ?>
    <div class="ju-settings-option p-lr-20 wpmf_width_100">
        <a target="_blank" class="ju-button no-background orange-button waves-effect waves-light"
           style="margin: 15px 0;"
           href="https://www.joomunited.com/wordpress-documentation/wp-media-folder/289-wp-media-folder-addon-amazon-s3-integration">
            <?php esc_html_e('Read the online documentation', 'wpmfAddon') ?>
        </a>
    </div>
</div>