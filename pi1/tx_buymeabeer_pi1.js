
<!-- ###TEMPLATE_JS### begin -->
jQuery(document).ready(function() {
	jQuery('####KEY### a.external').attr("target", "_blank");
	jQuery('####KEY### .buymeabeer_amounts').hide();
	jQuery('####KEY###').parent().hover(function(){
		jQuery('####KEY### .buymeabeer_amounts').stop(true, true).animate({height: 'toggle'}, 500, 'easeOutBounce');
	}, function(){
		jQuery('####KEY### .buymeabeer_amounts').stop(true, true).animate({height: 'toggle'}, 500, 'easeOutBounce');
	});
});
<!-- ###TEMPLATE_JS### end -->
