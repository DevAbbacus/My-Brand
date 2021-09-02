function wcmca_update_product_handling_fee_counter(value_to_increase)
{
	var formData = new FormData();
	formData.append('action', 'wcmca_update_product_handling_fee_counter');
	formData.append('increase', value_to_increase);
	
	jQuery.ajax({
		url: wcmca_ajax_url,
		type: 'POST',
		data: formData,
		async: true,
		success: function (data) 
		{
			jQuery( document.body ).trigger( 'update_checkout' );		
		},
		error: function (data) 
		{
			//console.log(data);
			//alert("Error: "+data);
		},
		cache: false,
		contentType: false,
		processData: false
	});
}