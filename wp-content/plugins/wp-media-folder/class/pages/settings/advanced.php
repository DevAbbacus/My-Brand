<?php
/* Prohibit direct script loading */
defined('ABSPATH') || die('No direct script access allowed!');
?>
<div id="advanced" class="tab-content">
    <div class="content-box content-wpmf-advanced">
        <div class="ju-settings-option">
            <div class="wpmf_row_full">
                <label data-alt="<?php esc_html_e('You can reduce the background task processing by changing this parameter. It could be necessary when WP Media Folder is installed on small servers instances but requires consequent task processing', 'wpmf'); ?>"
                       class="ju-setting-label text"><?php esc_html_e('Background tasks speed', 'wpmf') ?></label>
                <label class="line-height-50 wpmf_right p-r-20">
                    <select name="tasks_speed">
                        <option
                            <?php selected($tasks_speed, 100); ?> value="100">
                            <?php esc_html_e('100%', 'wpmf') ?>
                        </option>
                        <option
                            <?php selected($tasks_speed, 75); ?> value="75">
                            <?php esc_html_e('75%', 'wpmf') ?>
                        </option>
                        <option
                            <?php selected($tasks_speed, 50); ?> value="50">
                            <?php esc_html_e('50%', 'wpmf') ?>
                        </option>
                        <option
                            <?php selected($tasks_speed, 25); ?> value="25">
                            <?php esc_html_e('25%', 'wpmf') ?>
                        </option>
                    </select>
                </label>
            </div>
        </div>
    </div>
</div>
