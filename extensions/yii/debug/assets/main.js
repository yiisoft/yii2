jQuery(document).ready(function(){
	// set body padding depending on toolbar height
	var toolbar = jQuery('#yii-debug-toolbar');
	var toolbarHeight = toolbar.css('height');
	jQuery('body').css('padding-top', parseInt(toolbarHeight) + 10 + 'px');	
	jQuery('#yii-debug-toolbar [data-toggle=tooltip]').tooltip();	
	jQuery(window).resize(function(){		
		toolbarHeight = toolbar.css('height');
		jQuery('body').css('padding-top', parseInt(toolbarHeight) + 10 + 'px');
	});
});
