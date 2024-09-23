sendposts( generationJsVars.sendposts.posts, 1, generationJsVars.sendposts.count, generationJsVars.sendposts.interval, generationJsVars.sendposts.nonce );


function sendposts( posts, a, count, interval, nonce ) {

	// Send data to WordPress admin-ajax file
	jQuery.ajax({
			url : generationJsVars.wp_ajax_url,
			method : 'POST',
			data : {
				action             : 'generate_image',
				ids_mpt_generation : posts,
				a                  : a,
				count              : count,
				interval 					 : interval,
				nonce              : nonce
			},
			success: async function( data ) {
				if ( data.success ) {

					if(1 !== a) {
						// Update remaining seconds
						var remainingTime = interval+1;
						setInterval(oneSecondFunction, 1000);

						function oneSecondFunction() {
								if( remainingTime > 0 ) {
									remainingTime--;
									jQuery(".remaining-time").html( remainingTime + ' seconds' );
								} else {
									return;
								}
						}

						// Delay with interval
						var sleepGeneration = (delay) => new Promise((resolve) => setTimeout(resolve, delay));
						await sleepGeneration(interval*1000);

					}

					// Update posts table with new datas
					jQuery('.wp-list-mpt #post-'+data.data.id+' .empty-content').hide();
					jQuery('.wp-list-mpt #post-'+data.data.id+' .row-status .status.'+data.data.status).show();
					jQuery('.wp-list-mpt #post-'+data.data.id+' .row-actions').show();
					jQuery('.wp-list-mpt #post-'+data.data.id+' .row-image').append(data.data.fimg).show();


					var percent = data.data.percent;
					var speed   = data.data.speed;

					jQuery('.progressionbar-bar').animate({
						width: percent+'%'
					},speed);

					sleep(speed);

					jQuery('.skill-bar-percent span').empty().append( ~~percent );

					if( percent == 100 ) {
						jQuery('.successful-generation').show();
						jQuery('.dalle-wait').hide();
					}

					var scrollY;
					if( 1 === a ) {
						scrollY = 0;
					} else {
						scrollY = (a-2)*90.25;
					}
					document.getElementById( "mpt-list" ).scrollTo( 0, scrollY );

					a++;
					if ( a <= count ) {
						sendposts( posts, a, count, interval, nonce );
					}

				} else {
					jQuery("#results").append( generationJsVars.translations.error_generation );
				}
			},
			error : function( data ) {
				jQuery("#results").append( generationJsVars.translations.error_plugin );
			}
	}).responseText;
}

function sleep( milliseconds ) {
	var start = new Date().getTime();
	for ( var i = 0; i < 1e7; i++ ) {
		if ( ( new Date().getTime() - start) > milliseconds ){
			break;
		}
	}
}

jQuery(function() {
	jQuery( "#progressbar" ).progressbar({
		value: 0
	});
});

jQuery(function() {
	jQuery("#hide-before-import").css("display", "block");
	jQuery( "#progressbar" ).progressbar({
		value: 1
	});
	return false;
});
