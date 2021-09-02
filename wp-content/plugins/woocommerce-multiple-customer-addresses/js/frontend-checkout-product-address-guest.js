var wcmca_address_preview_value = {};
var wcmca_address_pick_up_value = {};
var wcmca_address_note_value = {};
var wcmca_current_guest_product_id = 0;
jQuery(document).ready(function()
{
	jQuery(document).on('update_checkout', wmca_checkout_updated);
	jQuery(document).on('updated_checkout', wmca_restore_selected);
	jQuery(document).on('click', '.wcmca_add_new_product_address_guest_button', wcmca_save_current_guest_product_id);
	jQuery(document).on('click', '.wcmca_remove_address_button', wcmca_reset_address_for_product_guest);
	jQuery(document).on('keyup', '.wcmca_product_field_note', wcmca_save_note_value);
	jQuery(document).on('click', '.wcmca_collect_from_store', wcmca_collect_from_store);
	wmca_checkout_updated(null);
	wcmca_init_select_menus();
	//wcmca_move_product_shipping_boxes_after_variation();
});
function wcmca_save_note_value(event)
{
	wcmca_address_note_value[jQuery(event.currentTarget).attr('id')] = jQuery(event.currentTarget).val();
}
function wmca_checkout_updated(event)
{
	setTimeout(wcmca_init_add_new_addresses_button, 2500);
	
	//Must be re registered in this way otherwise it won't work
	jQuery('.wcmca_add_new_product_address_guest_button').on('click', wcmca_save_current_guest_product_id);
}
function wcmca_init_select_menus()
{
	wcmca_update_product_handling_fee_counter(0); //no additional addresses
}
function wcmca_save_current_guest_product_id(event)
{
	/* In some scenarion this will prevent the handler to be invoked after a "update_checkout" event
	event.stopImmediatePropagation();
	event.preventDefault(); */
	wcmca_current_guest_product_id = jQuery(event.currentTarget).data('cart-item-id');
	
	return false;
}
function wmca_restore_selected()
{
	//console.log(wcmca_address_preview_value);
	jQuery(".wcmca_collect_from_store").prop('disabled', false);
	jQuery('.wcmca_product_address').each(function(index,elem)
	{
		var curr_elem_id = jQuery(elem).data('unique-id');
		//console.log(curr_elem_id);
		if(wcmca_address_preview_value[curr_elem_id])
		{
			toggle_reset_product_address_for_guest_button(curr_elem_id);
			jQuery(elem).html(wcmca_address_preview_value[curr_elem_id]);
			jQuery('#product_address_for_guest_'+curr_elem_id).val(curr_elem_id);
			//is pick up from store?
			if(wcmca_address_pick_up_value.hasOwnProperty(curr_elem_id))
			{
				jQuery('#product_address_for_guest_'+curr_elem_id).val('collect_from_store');
				jQuery("#wcmca_actions_container_"+curr_elem_id).fadeOut(0);
				jQuery("#wcmca_collect_from_store_checkbox_"+curr_elem_id).prop('checked', true);
			}
		}
		else 
		{
			jQuery('#product_address_for_guest_'+curr_elem_id).val('same_as_billing');
		}
		
	});
	
	jQuery('.wcmca_product_field_note').each(function(index,elem)
	{
		if(wcmca_address_note_value[jQuery(elem).attr('id')])
			jQuery(elem).val(wcmca_address_note_value[jQuery(elem).attr('id')]);
	});
} 
function wcmca_load_guest_address_preview(data)
{
	
	//UI	
	wcmca_hide_saving_loader();
	toggle_reset_product_address_for_guest_button(wcmca_current_guest_product_id);

	jQuery('#product_address_for_guest_'+wcmca_current_guest_product_id).val(wcmca_current_guest_product_id);
	
	jQuery('#wcmca_product_address_'+wcmca_current_guest_product_id).html(data);
	wcmca_address_preview_value[wcmca_current_guest_product_id] = data;
	
	wcmca_update_product_handling_fee_counter(1);
}
function wcmca_reset_address_for_product_guest(event)
{
	event.stopImmediatePropagation();
	event.preventDefault();
	
	var id = jQuery(event.currentTarget).data('cart-item-id');
	
	wcmca_update_product_handling_fee_counter(-1);
	jQuery('#product_address_for_guest_'+id).val('same_as_billing');
	
	//UI
	toggle_reset_product_address_for_guest_button(id);	
	
	jQuery('#wcmca_product_address_'+id).html(wcmca_guest.default_address_message);
	delete wcmca_address_preview_value[id];
	
	return false;
}
function wcmca_collect_from_store(event)
{
	var id = jQuery(event.currentTarget).data('cart-item-id');
	wcmca_reset_address_for_product_guest(event);
	//Force UI
	//jQuery("#wcmca_collect_from_store_checkbox_"+id).prop('checked', event.currentTarget.checked);
	jQuery(".wcmca_collect_from_store").prop('disabled', true);
	if(event.currentTarget.checked)
	{
		jQuery("#wcmca_actions_container_"+id).fadeOut();
		jQuery('#product_address_for_guest_'+id).val('collect_from_store');
		jQuery('#wcmca_product_address_'+id).html(wcmca_guest.collect_from_store_message);
		wcmca_address_preview_value[id] = wcmca_guest.collect_from_store_message;
		wcmca_address_pick_up_value[id] = true;
	}
	else 
	{
		delete wcmca_address_pick_up_value[id];
		jQuery("#wcmca_actions_container_"+id).fadeIn();
	}
	return false;
}