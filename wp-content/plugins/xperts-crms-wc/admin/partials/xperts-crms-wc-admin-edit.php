<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       http://techxperts.co.in/
 * @since      1.0.0
 *
 * @package    Xperts_Crms_Wc
 * @subpackage Xperts_Crms_Wc/admin/partials
 */
?>
<!-- This file should primarily consist of HTML with a little bit of PHP. -->
<div class="wrap">
    <h1 class="wp-heading-inline">
        <?php echo get_admin_page_title();?> :: Edit Mapping
    </h1>
    <hr class="wp-header-end">

    <form method="post" action="<?php echo admin_url('admin.php?page=xperts-crms-cmap&action=edit');?>">
        <?php wp_nonce_field('xperts_crms_cmap_update','xperts_crms_cmap_update'); ?>
        <input type="hidden" name="mapping_id" value="<?php echo $crms_category_map->id;?>">
        <?php
        if(count((array) $xperts_form_errors))
        {
            ?>
            <div id="setting-error-invalid_siteurl" class="error settings-error notice is-dismissible">
                <?php foreach ($xperts_form_errors->get_error_messages() as $error): ?>
                    <p><strong><?php echo $error;?></strong></p>
                <?php endforeach; ?>
                <button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button>
            </div>
            <?php
        }
        ?>
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="crms_category_slug"><?php _e('Document Layout Field Name','xperts-run-events');?></label>
                </th>
                <td>
                    <input name="crms_category" type="text" id="crms_category" value="<?php echo $this->get_form_field('crms_category',$crms_category_map->crms_category);?>" class="regular-text">
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="category_mappings"><?php _e('WooCommerce Category Mapping','xperts-run-events');?></label>
                </th>
                <td>
                    <?php
                        echo wc_product_dropdown_categories([
                                'name'=>'category_mappings',
                                'multiple'=>true,
                                'hide_empty'=>false,
                                'value_field'=>'id',
                                'show_option_none'=>'',
                                'selected' => (isset($_POST['category_mappings'])?$_POST['category_mappings']:unserialize($crms_category_map->category_mappings)),
                        ]);
                    ?>
                </td>
            </tr>
        </table>
        <p class="submit">
            <input type="submit" name="submit" id="submit" class="button button-primary" value="Submit">
        </p>
    </form>

</div>
