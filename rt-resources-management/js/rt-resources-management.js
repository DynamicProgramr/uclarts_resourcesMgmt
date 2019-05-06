/* this file loads all the custom js for this plugin  rt-resources-management */
jQuery(document).ready(function($)
{
	// not currently used -> var animation = $("#loading-animation");
	
	$(".datepicker").datepicker(
		{
			dateFormat: "mm/dd/yy",
			showOn: "both",
			buttonImage: "/wp-content/plugins/gravityforms/images/calendar.png",
			buttonImageOnly: true,
			buttonText: "open calendar"
		}
	);
});