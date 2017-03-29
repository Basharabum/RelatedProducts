require(['jquery'],function($) {

    //mouseover on Product Select
    jQuery(document).on( 'mouseover',
    					 ".admin__control-select[name='product[product_select_field]']",
    					 function() {
        var currentProductName = jQuery('.page-title').text();
        jQuery(".admin__control-select[name='product[product_select_field]'] option[data-title='"+currentProductName+"']").remove();        
    });

    //mouseover on Category1 Select
     jQuery(document).on( 'mouseover',
     					  ".admin__control-select[name='product[example_select_first_category_field]']",
    					  function() {
    	jQuery( ".admin__control-select[name='product[example_select_first_category_field]'] option:hidden" ).show();
    	var chosenProductInSecondSelect = jQuery(".admin__control-select[name='product[example_select_second_category_field]'] option:selected").text();
    	if (chosenProductInSecondSelect == 'Select...' || chosenProductInSecondSelect == '-> Remove Related Category')
    		return;
    	jQuery(".admin__control-select[name='product[example_select_first_category_field]'] option[data-title='"+chosenProductInSecondSelect+"']").hide();
	});

     //mouseover on Category2 Select
     jQuery(document).on( 'mouseover',
     					  ".admin__control-select[name='product[example_select_second_category_field]']",
    					  function() {
    	jQuery( ".admin__control-select[name='product[example_select_second_category_field]'] option:hidden" ).show();
    	var chosenProductInSecondSelect = jQuery(".admin__control-select[name='product[example_select_first_category_field]'] option:selected").text();
    	if (chosenProductInSecondSelect == 'Select...' || chosenProductInSecondSelect == '-> Remove Related Category')
    		return;
    	jQuery(".admin__control-select[name='product[example_select_second_category_field]'] option[data-title='"+chosenProductInSecondSelect+"']").hide();
	});
     
});