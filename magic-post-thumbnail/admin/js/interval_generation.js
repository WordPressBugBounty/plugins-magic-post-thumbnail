
jQuery(function() {
	jQuery("#hide-before-import").css("display", "block");

	jQuery( "#progressbar" ).progressbar({
		value: 1
	});


	var percent = generationJsVars.counter;
        var speed   = '500';

        jQuery('.progressionbar-bar').animate({
                width: percent+'%'
        },speed);

        jQuery('.skill-bar-percent span').empty().append( ~~percent );
});
