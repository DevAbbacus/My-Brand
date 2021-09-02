function wcmca_loading_address_start(formType)
{
	jQuery('footer, .fusion-header-wrapper').fadeOut();
	jQuery('#wcmca_loader_image_'+formType).fadeIn();
	//jQuery('#wcmca_form_popup_container').css('top', jQuery(document).scrollTop()  -  (jQuery('#wcmca_form_popup_container').height()/2) + 60);
	//jQuery('#wcmca_form_popup_container .woocommerce, #wcmca_form_popup_container .woocommerce #wcmca_address_form, #wcmca_form_popup_container .woocommerce #wcmca_address_form #wcmca_address_form_fieldset').css('top', jQuery(document).scrollTop()  -  (jQuery('#wcmca_form_popup_container').height()/2) + 60);
	
	//select menu
	/*jQuery('.wcmca_address_select_menu').selectWoo({ width: 'resolve' });	
	 jQuery(document).on('updated_checkout', function()
	{		
		jQuery('.wcmca_product_address_select_menu').selectWoo({ width: '500px' });
	}); */
		
	jQuery('html, body').animate({
          scrollTop: jQuery('#wcmca_form_popup_container_billing').offset().top
        }, 1000)
}
function wcmca_loading_address_end(formType)
{
	jQuery('footer, .fusion-header-wrapper').fadeIn();
	jQuery('#wcmca_loader_image_'+formType).fadeOut();
}