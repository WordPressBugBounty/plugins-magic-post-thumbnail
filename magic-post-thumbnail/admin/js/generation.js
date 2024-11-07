sendposts( generationJsVars.sendposts.posts, generationJsVars.sendposts.block_counter, 1, generationJsVars.sendposts.count, 0, generationJsVars.sendposts.interval, generationJsVars.sendposts.nonce, 0 );


function sendposts( postIds, totalBlocks, currentPostIndex, count, imageCounter, interval, nonce, blockIndex ) {

	// Send data to WordPress admin-ajax file
	jQuery.ajax({
			url : generationJsVars.wp_ajax_url,
			method : 'POST',
			data : {	
				action				: 'generate_image',
				ids_mpt_generation	: postIds,
				currentPostIndex	: currentPostIndex, // Current index of the post being processed
				count				: count, // Total number of posts
				totalBlocks			: totalBlocks, // Total number of image blocks
				imageCounter		: imageCounter, // Counter for generated images
				interval			: interval, // Time interval between requests
				nonce				: nonce, // Security nonce
				blockIndex			: blockIndex // Index of the current block being processed
			}, 
			success: async function( data ) {
				if ( data.success ) {
					// Increment the image counter after a successful generation
					imageCounter++;

					// Calculate progress percentage based on completed images
					var percent = 100*(imageCounter/(count*totalBlocks));

					if(1 !== currentPostIndex) {
						// Display the remaining time in seconds for the current interval
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

						// Delay the process for the defined interval
						var sleepGeneration = (delay) => new Promise((resolve) => setTimeout(resolve, delay));
						await sleepGeneration(interval*1000);

					}

					// Construct the status message for the current generation step
					var statusGeneration = data.data.blockIndex + '/' + totalBlocks + ' : ';

					if( 'successful' == data.data.status ) {
						hiddenDatas = '<hr/>';
						hiddenDatas += '<strong>' + statusGeneration + '</strong><br>';
						if (data.data.keyword !== undefined) {
							hiddenDatas += generationJsVars.translations.search_terms + ' : ' + data.data.keyword + '<br>';
						}
						if (data.data.img_resolution !== undefined) {
							hiddenDatas += generationJsVars.translations.img_resolution + ' : ' + data.data.img_resolution + '<br>';
						}
						if (data.data.img_size !== undefined) {
							hiddenDatas += generationJsVars.translations.img_size + ' : ' + data.data.img_size + '<br>';
						}
						if (data.data.api_chosen !== undefined) {
							hiddenDatas += generationJsVars.translations.img_bank + ' : ' + data.data.api_chosen + '<br>';
						}
						jQuery('.wp-list-mpt #post-' + data.data.id + ' .image-details').append(hiddenDatas);
					}

					// If there is no next post to process
					if( false === data.data.nextPost ) {

						// Update posts table with new datas
						jQuery('.wp-list-mpt #post-'+data.data.id+' .empty-content').hide();

						var statusGenerationDisplay = jQuery('.wp-list-mpt #post-' + data.data.id + ' .row-status .raw.status.' + data.data.status).clone();
						statusGenerationDisplay.removeClass('raw').prepend(statusGeneration).append('<br>');
						jQuery('.wp-list-mpt #post-' + data.data.id + ' .row-status').append(statusGenerationDisplay);
						statusGenerationDisplay.show();

						// Display the generated image and other updates
						jQuery('.wp-list-mpt #post-'+data.data.id+' .row-actions').show();
						jQuery('.wp-list-mpt #post-'+data.data.id+' .row-image').append(data.data.fimg).show();

						// Animate the progress bar based on the current percentage
						var speed   = data.data.speed;
						jQuery('.progressionbar-bar').animate({
							width: percent+'%'
						},speed);
						sleep(speed);

						// Update the displayed progress percentage
						jQuery('.skill-bar-percent span').empty().append( ~~percent );

						// Show success message if progress is complete
						if( percent == 100 ) {
							jQuery('.successful-generation').show();
							jQuery('.dalle-wait').hide();
						}

						// Scroll to the current post being processed
						var scrollY = (currentPostIndex === 1) ? 0 : (currentPostIndex - 2) * 90.25;
						document.getElementById( "mpt-list" ).scrollTo( 0, scrollY );

						// Move to the next post if available
						currentPostIndex++;
						if ( currentPostIndex <= count ) {
							sendposts( postIds, totalBlocks, currentPostIndex, count, imageCounter, interval, nonce, 0 );
						}

						// Show the image details
						jQuery('.wp-list-mpt #post-'+data.data.id+' .show-image-details').show();

					} else {
						// Handle the case when processing a specific block within the same post
						if( data.data.blockIndex ) {

							jQuery('.wp-list-mpt #post-'+data.data.id+' .row-image').append(data.data.fimg).show();

							var statusGenerationDisplay = jQuery('.wp-list-mpt #post-' + data.data.id + ' .row-status .raw.status.' + data.data.status).clone();
							statusGenerationDisplay.removeClass('raw').prepend(statusGeneration).append('<br>');
							jQuery('.wp-list-mpt #post-' + data.data.id + ' .row-status').append(statusGenerationDisplay);
							statusGenerationDisplay.show();

							var speed   = data.data.speed;
	
							jQuery('.progressionbar-bar').animate({
								width: percent+'%'
							},speed);

							sleep(speed);

							jQuery('.skill-bar-percent span').empty().append( ~~percent );
							
							// Continue processing the next block of the current post
							sendposts( postIds, totalBlocks, currentPostIndex, count, imageCounter, interval, nonce, data.data.blockIndex );
						}
					}
				} else {
					// Display an error message if the generation fails
					jQuery("#results").append( generationJsVars.translations.error_generation );
				}
			},
			error : function( data ) {
				// Display a general plugin error message
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
