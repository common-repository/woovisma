(function( $ ) {
	'use strict';

	/**
	 * All of the code for your admin-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed you will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 *
	 * This enables you to define handlers, for when the DOM is ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * When the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and/or other possibilities.
	 *
	 * Ideally, it is not considered best practise to attach more than a
	 * single DOM-ready or window-load handler for a particular page.
	 * Although scripts in the WordPress core, Plugins and Themes may be
	 * practising this, we should strive to set a better example in our own work.
	 */

})( jQuery );
function send_support_mail(form) {
    var data = jQuery('form#'+form).serialize();
    jQuery.post(ajaxurl, data, function(response) {
        if(response == "success"){
			alert("Message sent successfully!");
		}else{
			alert("Problem sending message, please try again later.");
		}
    });
}
function submitSettings()
{
    showHideTestButton();
    //alert(jQuery(".nossl:checked").val());
    //jQuery("#settings").submit();
}
function showHideTestButton()
{
    var nossl=jQuery(".nossl:checked").val();
    if(nossl>0)
    {
        jQuery('#save_button').attr('disabled',false);
        jQuery('#test_button').show();
        jQuery('#client_id').attr('disabled','disabled');
        jQuery('#client_secret').attr('disabled','disabled');
        jQuery('#redirect_uri').attr('disabled','disabled');
        jQuery("#errmsg").text("");
    }
    else
    {
        jQuery('#client_id').attr('disabled',false);
        jQuery('#client_secret').attr('disabled',false);
        jQuery('#redirect_uri').attr('disabled',false);
        var client_id=jQuery("#client_id").val();
        var client_secret=jQuery("#client_secret").val();
        var redirect_uri=jQuery("#redirect_uri").val();
        if(client_id=="" || client_secret=="" || redirect_uri=="")  
        {
            jQuery("#errmsg").text("Invalid Settings");
            jQuery("#test_button").hide();
            jQuery('#save_button').attr('disabled','disabled');
        }
        else if(client_id=="Your Visma Client ID" || client_secret=="Your Visma Client Secret" || redirect_uri=="https://your/registered/base/url/")
        {
             jQuery("#errmsg").text("Invalid Settings");
            jQuery("#test_button").hide();
            jQuery('#save_button').attr('disabled','disabled');
        }
        else   
        {
            jQuery("#errmsg").text("");
            jQuery("#test_button").show();
            jQuery('#save_button').removeAttr('disabled');
        }
    }
}