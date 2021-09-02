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
        <?php echo get_admin_page_title();?>
    </h1>
    <a href="<?php echo admin_url('admin.php?page=xperts-crms-cmap&action=add');?>" class="page-title-action">Add New</a>
    <hr class="wp-header-end">

    <?php if(isset($_REQUEST['msg']) and intval($_REQUEST['msg'])): ?>
        <div id="message" class="updated notice notice-success is-dismissible">
            <p>
                <?php
                switch (intval($_REQUEST['msg'])){
                    case 1:
                        _e('Category Mapping saved successfully!','xperts-crms-wc');
                        break;
                    case 2:
                        _e('Category Mapping updated successfully!','xperts-crms-wc');
                        break;
                    case 3:
                        _e('Category Mapping deleted successfully!','xperts-crms-wc');
                        break;
                    default:
                        break;
                }
                ?>
            </p>
            <button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button>
        </div>
    <?php endif; ?>

    <form id="events-filter" method="get" action="<?php echo admin_url('admin.php'); ?>">
        <input type="hidden" name="page" value="xperts-crms-cmap">
        <?php wp_nonce_field( 'bulk-crms-category-mappings' ); ?>
        <p class="search-box">
            <label class="screen-reader-text" for="post-search-input"><?php _e('Search Events','xperts-crms-wc'); ?>:</label>
            <input type="search" id="post-search-input" name="s" value="<?php echo isset($_REQUEST['s'])?sanitize_text_field($_REQUEST['s']):''; ?>">
            <input type="submit" id="search-submit" class="button" value="<?php _e('Search Events','xperts-crms-wc'); ?>">
        </p>

        <div class="tablenav top">

            <div class="alignleft actions bulkactions">
                <label for="bulk-action-selector-top" class="screen-reader-text">Select bulk action</label>
                <select name="action" id="bulk-action-selector-top">
                    <option value="-1"><?php _e('Bulk Actions','xperts-crms-wc'); ?></option>
                    <option value="delete"><?php _e('Delete','xperts-crms-wc'); ?></option>
                </select>
                <input type="submit" id="doaction" class="button action" value="<?php _e('Apply','xperts-crms-wc'); ?>">
            </div>

            <br class="clear">

            <?php echo $this->pagination([
                'total_items' => $total_items,
                'total_pages'=> $total_pages,
            ]);?>

            <br class="clear">
        </div>

        <h2 class="screen-reader-text"><?php _e('Category Mappings List','xperts-crms-wc'); ?></h2>

    <table class="wp-list-table widefat fixed striped posts">
        <thead>
            <tr>
                <td  id='cb' class='manage-column column-cb check-column'>
                    <label class="screen-reader-text" for="cb-select-all-1"><?php _e('Select All','xperts-crms-wc'); ?></label><input id="cb-select-all-1" type="checkbox" />
                </td>
                <th scope="col" id='title' class='manage-column column-title'>
                    <?php _e('Document Layout Field Name','xperts-crms-wc'); ?>
                </th>
                <th scope="col" id='sport' class='manage-column column-title'><?php _e('Categories Mapped','xperts-crms-wc'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($category_mappings as $category_map): ?>
                <tr id="post-<?php echo $category_map->id;?>" class="iedit author-self level-0 post-<?php echo $category_map->id;?> hentry entry">
                    <th scope="row" class="check-column">
                        <input id="cb-select-<?php echo $category_map->id;?>" type="checkbox" name="post[]" value="<?php echo $category_map->id;?>" />
                    </th>
                    <td class="title column-title has-row-actions column-primary page-title">
                        <strong><a class="row-title" href="#"><?php echo $category_map->crms_category;?></a></strong>
                        <div class="row-actions">
                            <span class="edit"><a href="<?php echo admin_url('admin.php?page=xperts-crms-cmap&action=edit&mapping_id='.$category_map->id);?>"><?php _e('Edit','xperts-crms-wc');?></a> | </span>
                            <span class="trash"><a href="<?php echo esc_url( wp_nonce_url( "admin.php?page=xperts-crms-cmap&action=delete&id=$category_map->id", 'delete-mapping_'.$category_map->id ) );?>" class="submitdelete"><?php _e('Delete','xperts-crms-wc');?></a></span>
                        </div>
                    </td>
                    <td class="title column-sport has-row-actions column-primary page-title">
                        <?php echo $this->getTermNames($category_map->category_mappings);?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="tablenav bottom">

        <div class="alignleft actions bulkactions">
            <label for="bulk-action-selector-bottom" class="screen-reader-text">Select bulk action</label>
            <select name="action2" id="bulk-action-selector-bottom">
                <option value="-1"><?php _e('Bulk Actions','xperts-crms-wc'); ?></option>
                <option value="delete"><?php _e('Delete','xperts-crms-wc'); ?></option>
            </select>
            <input type="submit" id="doaction2" class="button action" value="<?php _e('Apply','xperts-crms-wc'); ?>"  />
        </div>
        <div class="alignleft actions">
        </div>
        <?php echo $this->pagination([
            'total_items' => $total_items,
            'total_pages'=> $total_pages,
        ]);?>
        <br class="clear" />
    </div>
    </form>
</div>
