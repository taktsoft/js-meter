$(document).ready(function() {
	// Accordion
	var accordionOptions = {
		autoHeight: false,
		header: 'h2',
		collapsible: true
	};
	
	$("#profilings_accordion").accordion(accordionOptions);
	
	initDetailToggles();
});

function initDetailToggles() {
	$(".detail_toggle").toggle(
		function() {
			$(this).find(".ui-icon").removeClass("ui-icon-folder-collapsed").addClass("ui-icon-folder-open");
			$(this).parents("table:first").find("." + $(this).attr("id")).show();
		},
		function() {
			$(this).find(".ui-icon").addClass("ui-icon-folder-collapsed").removeClass("ui-icon-folder-open");
			$(this).parents("table:first").find("." + $(this).attr("id")).hide();
		}		
	);
}