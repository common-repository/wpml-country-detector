/* Submit Script */

jQuery(document).ready(function($) {

	
    // Handle the AJAX field save action
    $('#wcd_cs_options').on('submit', function(e) {
        e.preventDefault();
		
        fadeInAjxResp('#wcd_ajx_response_cso', icl_ajxloaderimg);
        $.post(ajaxurl, {
            action: 'wcd_store_ajax',
            showInFooter: $('input[name="wcd_show_in_footer"]:checked').val(),
            redirect: $('#wcd_redirect:checked').val(), 
			redirect_always: $('#wcd_redirect_always:checked').val(),
            _wpnonce :$('#_wcd_nonce').val()
		            
        }, function(status) {
            if(status==7){
                fadeInAjxResp('#wcd_ajx_response_cso',icl_ajx_saved);                  
            }else{                        
                fadeInAjxResp('#wcd_ajx_response_cso',icl_ajx_error);    
            }
        }
        );
    });
	
	
});