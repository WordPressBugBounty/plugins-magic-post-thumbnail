window.onload = function(){

  setTimeout(function (){
      var gutenbergEditor   = false;
      var gutenbergPostButton = jQuery('.editor-sidebar__panel-tabs'); // #post-button-generate

      if( gutenbergPostButton.length ) {
          gutenbergEditor = true;
      }

      // Insert Generation button on edit post page
      displayGenerationButton(gutenbergEditor);

      jQuery( "body" ).click(function(e) {
          setTimeout(function (){
              displayGenerationButton(gutenbergEditor);
          }, 1);
      });
  }, 1000);

};




function manualActionHandler() {
  const { select, dispatch } = wp.data;
  const block = select('core/block-editor').getSelectedBlock();

  //console.log('launch click');

  if (block && block.name === 'mpt/mpt-images') {
      dispatch('core/block-editor').updateBlockAttributes(block.clientId, { content: 'Nouveau contenu' });
  }
}

  



function displayGenerationButton(gutenbergEditor = true) {

    var uniqueButtonIdentifier = Date.now();

    var outline = '';

    if( "true" == generationSpecificPostJsVars.postgeneration.dalle ) {
      outline += '<p class="dalle-wait">'+  generationSpecificPostJsVars.postgeneration.strDalleGenerate +'.</p>';
    }
    outline +=  '<div class="button button-primary button-hero '+ uniqueButtonIdentifier +'" id="post-button-generate">';
    outline += '<img src="'+ generationSpecificPostJsVars.postgeneration.generateImg +'" class="icon-dashboard" width="24px" height="24px" /> ';
    outline += '<span class="no-generation">'+  generationSpecificPostJsVars.postgeneration.strNoGenerate +'</span>';
    outline += '<span class="generation">'+  generationSpecificPostJsVars.postgeneration.strGenerate +'</span>';
    outline += '</div>';

    if( ( "true" == generationSpecificPostJsVars.postgeneration.manual_search ) && (true === gutenbergEditor ) ) {
      outline += '<div class="button button-primary button-hero '+ uniqueButtonIdentifier +'" onClick="addMPTBlockAndOpenModal()" id="post-button-generate-manual">';
      outline += '<img src="'+ generationSpecificPostJsVars.postgeneration.generateImg +'" class="icon-dashboard" width="24px" height="24px" /> ';
      outline += '<span class="no-generation">'+ generationSpecificPostJsVars.postgeneration.strManualGenerate +'</span>';
      outline += '</div>';
    }

    var generationButton = '#post-button-generate';

    // Check if button is present

    //console.log( gutenbergEditor );
    //console.log( jQuery( generationButton ).length );

    if( ( true === gutenbergEditor ) && ( 0 === jQuery( generationButton ).length ) ) {
        // Gutenberg editor
        jQuery( ".editor-post-featured-image" ).before( outline );
    } else if( 0 === jQuery( generationButton ).length ) {
        // Classic editor
        jQuery( "#postimagediv > .inside" ).before( outline );
    } else {}

    // Generationon sidebar with classic & Gutenberg Editor
    jQuery( '#post-button-generate.'+uniqueButtonIdentifier ).click(function(e) {

        e.preventDefault();

        // Block if generation already launched
        if( jQuery(this).hasClass('generation-pending') ) {
            return;
        }

        jQuery(this).addClass('generation-pending');

        if( true === gutenbergEditor ) {
            // Save post to get the fresh title/content
            var statusAutosave = wp.data.dispatch( 'core/editor' ).savePost(); // .autoSave()
            var delayAutosave  = 1000;
        } else {
            var delayAutosave  = 1;
        }

        // Button Animation
        jQuery( '#post-button-generate.' + uniqueButtonIdentifier + ' > .icon-dashboard' ).addClass('generate');
        jQuery( '#post-button-generate.' + uniqueButtonIdentifier + ' .no-generation' ).hide();
        jQuery( '#post-button-generate.' + uniqueButtonIdentifier + ' .generation' ).show();

        jQuery( '.dalle-wait' ).show();

        // Wait 1 sec max to ensure post is saved
        setTimeout(function (){
           jQuery.ajax({
                    url : generationSpecificPostJsVars.postgeneration.wp_ajax_url,
                    method : 'POST',
                    data : {
                            action             : 'generate_image',
                            ids_mpt_generation : generationSpecificPostJsVars.postgeneration.postID,
                            a                  : 1,
                            count              : 1,
                            nonce              : generationSpecificPostJsVars.postgeneration.nonce
                    },
                    success: function( data ) {

                            // Featured image already exist
                            if( 'already-done' === data.data.status ) {
                                alert( generationSpecificPostJsVars.postgeneration.strNoRewrite );
                                return;
                            }

                            if ( data.success ) {

                                    var fifuOn              = generationSpecificPostJsVars.postgeneration.fifu_on;
                                    var classicEditorImage  = jQuery('#postimagediv .inside');
                                    var fifuPlugin          = jQuery('#fifu_image');

                                    if( (true == fifuOn) && ( fifuPlugin.length ) ) {
                                        // Fifu Plugin enabled
                                        jQuery(fifuPlugin).css({'display': 'block', 'background-image': 'url("'+ data.data.img +'")'});
                                        jQuery('#fifu_input_url').val(data.data.img);
                                    } else if( classicEditorImage.length ) {
                                        // Classic editor
                                        classicEditorImage.html(data.data.postimagediv);
                                    } else {
                                        // Gutemberg editor
                                        wp.data.dispatch( 'core/editor' ).editPost({ featured_media: data.data.thumbnail_id });
                                    }

                            } else {}
                    },
                    error : function( data ) {
                        console.log( 'error :' + data );
                    }
            }).always(function() {
                // Button Animation
                jQuery('#post-button-generate .no-generation').show();
                jQuery('#post-button-generate .generation').hide();
                jQuery( '.dalle-wait' ).hide();
                jQuery('.button-hero > .icon-dashboard').removeClass('generate');

                jQuery('#post-button-generate.'+uniqueButtonIdentifier).removeClass('generation-pending');
            }).responseText;
        }, delayAutosave);
    });
}

function copyToClipboard(element) {
  var $temp = jQuery("<input>");
  jQuery("body").append($temp);
  $temp.val(jQuery(element).text()).select();
  document.execCommand("copy");
  $temp.remove();
}


jQuery(document).ready(function() {

    // SCROLL LOG BLOCK
    var logsBlock = document.getElementById( "logs-block" );
    if( logsBlock ) {
        logsBlock.scrollTop = 100000;
    }

    // Settings Logs
    jQuery('.show-settings').click(function(e) {
      e.preventDefault();
      jQuery( ".hide-settings" ).show();
      jQuery( ".copy-settings" ).show();
      jQuery( ".show-settings" ).hide();
      jQuery( ".settings-logs" ).show();
    });

    jQuery('.hide-settings').click(function(e) {
      e.preventDefault();
      jQuery( ".hide-settings" ).hide();
      jQuery( ".copy-settings" ).hide();
      jQuery( ".show-settings" ).show();
      jQuery( ".settings-logs" ).hide();
    });

    jQuery('.copy-settings').click(function(e) {
      copyToClipboard('.settings-logs');
      jQuery( ".copied" ).show().delay(3000).fadeOut();
      e.preventDefault();
    });

    currentApi = jQuery('#general-options .chosen_api input:checked').val();


	/* SELECT ALL */
	jQuery('#select-all-pt').click(function(event) {
		if(this.checked) {
			// Iterate each checkbox
			jQuery('td.post-type :checkbox').each(function() {
				this.checked = true;
			});
		} else {
			jQuery('td.post-type :checkbox').each(function() {
				this.checked = false;
			});
		}
	});
  jQuery('#select-all-pt-2').click(function(event) {
		if(this.checked) {
			// Iterate each checkbox
			jQuery('td.post-type-2 :checkbox').each(function() {
				this.checked = true;
			});
		} else {
			jQuery('td.post-type-2 :checkbox').each(function() {
				this.checked = false;
			});
		}
	});
	jQuery('#select-all-tx').click(function(event) {
		if(this.checked) {
			// Iterate each checkbox
			jQuery('td.taxonomy :checkbox').each(function() {
				this.checked = true;
			});
		} else {
			jQuery('td.taxonomy :checkbox').each(function() {
				this.checked = false;
			});
		}
	});


    /* GENERAL - CHOSEN OPTION FREE VERSION*/
    jQuery("#general-options .chosen_api .radio-disabled, \n\
            #general-options .based_on .radio-disabled, \n\
            #general-options .image_location .radio-disabled, \n\
            #general-options .result_position .radio-disabled, \n\
            #general-options .shuffle_image .checkbox-disabled, \n\
            #general-options .translation_EN .checkbox-disabled, \n\
            #general-options .category_choice .checkbox-disabled, \n\
            #general-options .choosed_banks .checkbox-disabled"
    ).click(function(e) {
        var alertProVersion = translationsJsVars.translations.pro_version;
        alert( alertProVersion );
        return false;
    });


    /*
    jQuery("#general-options .chosen_api input").change(function(){

            var tab = '#'+jQuery(this).val();
            var link_tab = '.nav-tab-wrapper span[href="'+tab+'"]';

            jQuery(link_tab)
                .addClass("nav-tab-active")
                .css( "opacity", "1" )
                .removeAttr('disabled');
            jQuery(link_tab)
                .siblings()
                .removeClass("nav-tab-active")
                .css( "opacity", "0.4" )
                .attr('disabled', 'disabled');

            jQuery("#wpbody-content .form-table")
                .not(tab)
                .not('#general-options')
                .css("display", "none");
            jQuery(tab).fadeIn();

    });*/


    jQuery("#general-options .image_location input[type='radio']").change(function(){
      if( jQuery(this).val() == 'custom' ) {
        jQuery( ".section_custom_image_position" ).show( 'slow' );
        jQuery( ".section_custom_image_size" ).show( 'slow' );
      } else {
        jQuery( ".section_custom_image_position" ).hide( 'fast' );
        jQuery( ".section_custom_image_size" ).hide( 'fast' );
      }
    });

    jQuery("#general-options .based_on input[type='radio']").change(function(){
            if( jQuery(this).val() == 'title' ) {
                    jQuery( ".section_title" ).show( 'slow' );
                    jQuery( ".section_text_analyser" ).hide( 'fast' );
                    jQuery( ".section_tags" ).hide( 'fast' );
                    jQuery( ".section_categories" ).hide( 'fast' );
                    jQuery( ".section_custom_field" ).hide( 'fast' );
                    jQuery( ".section_custom_request" ).hide( 'fast' );
                    jQuery( ".category_choice" ).hide( 'fast' );
                    jQuery( ".section_openai_extractor" ).hide( 'fast' );
            }

            if( jQuery(this).val() == 'text_analyser' ) {
                    jQuery( ".section_text_analyser" ).show( 'slow' );
                    jQuery( ".section_title" ).hide( 'fast' );
                    jQuery( ".section_tags" ).hide( 'fast' );
                    jQuery( ".section_categories" ).hide( 'fast' );
                    jQuery( ".section_custom_field" ).hide( 'fast' );
                    jQuery( ".section_custom_request" ).hide( 'fast' );
                    jQuery( ".category_choice" ).hide( 'fast' );
                    jQuery( ".section_openai_extractor" ).hide( 'fast' );
            }

            var basedOnSelected = [ 'tags', 'categories', 'custom_field', 'custom_request', 'openai_extractor' ];

            if( basedOnSelected.indexOf(jQuery(this).val()) !== -1 ) {
                if( true === checkRights() ) {

                  if( jQuery(this).val() == 'tags' ) {
                      jQuery( ".section_title" ).hide( 'fast' );
                      jQuery( ".section_tags" ).show( 'slow' );
                      jQuery( ".section_categories" ).hide( 'fast' );
                      jQuery( ".section_custom_field" ).hide( 'fast' );
                      jQuery( ".section_text_analyser" ).hide( 'fast' );
                      jQuery( ".section_custom_request" ).hide( 'fast' );
                      jQuery( ".category_choice" ).hide( 'fast' );
                      jQuery( ".section_openai_extractor" ).hide( 'fast' );
                  }

                  if( jQuery(this).val() == 'categories' ) {
                    jQuery( ".section_title" ).hide( 'fast' );
                    jQuery( ".section_categories" ).show( 'slow' );
                    jQuery( ".section_tags" ).hide( 'fast' );
                    jQuery( ".section_custom_field" ).hide( 'fast' );
                    jQuery( ".section_text_analyser" ).hide( 'fast' );
                    jQuery( ".section_custom_request" ).hide( 'fast' );
                    jQuery( ".category_choice" ).hide( 'fast' );
                    jQuery( ".section_openai_extractor" ).hide( 'fast' );
                  }

                  if( jQuery(this).val() == 'custom_field' ) {
                    jQuery( ".section_title" ).hide( 'fast' );
                    jQuery( ".section_custom_field" ).show( 'slow' );
                    jQuery( ".section_categories" ).hide( 'fast' );
                    jQuery( ".section_tags" ).hide( 'fast' );
                    jQuery( ".section_text_analyser" ).hide( 'fast' );
                    jQuery( ".section_custom_request" ).hide( 'fast' );
                    jQuery( ".category_choice" ).hide( 'fast' );
                    jQuery( ".section_openai_extractor" ).hide( 'fast' );
                  }

                  if( jQuery(this).val() == 'custom_request' ) {
                    jQuery( ".section_title" ).hide( 'fast' );
                    jQuery( ".section_custom_field" ).hide( 'slow' );
                    jQuery( ".section_categories" ).hide( 'fast' );
                    jQuery( ".section_tags" ).hide( 'fast' );
                    jQuery( ".section_text_analyser" ).hide( 'fast' );
                    jQuery( ".section_custom_request" ).show( 'fast' );
                    jQuery( ".category_choice" ).show( 'fast' );
                    jQuery( ".section_openai_extractor" ).hide( 'fast' );
                  }

                  if( jQuery(this).val() == 'openai_extractor' ) {
                    jQuery( ".section_title" ).hide( 'fast' );
                    jQuery( ".section_custom_field" ).hide( 'slow' );
                    jQuery( ".section_categories" ).hide( 'fast' );
                    jQuery( ".section_tags" ).hide( 'fast' );
                    jQuery( ".section_text_analyser" ).hide( 'fast' );
                    jQuery( ".section_custom_request" ).hide( 'fast' );
                    jQuery( ".category_choice" ).hide( 'fast' );
                    jQuery( ".section_openai_extractor" ).show( 'fast' );
                  }
            		} else {
                  var alertProVersion = translationsJsVars.translations.pro_version;
                  alert( alertProVersion );
                  jQuery("#general-options .based_on input[value='title']").attr( 'checked', true );
                }
            }

    });

    // CRON
    checkButton( '#enable_cron', '.show_cron', false );

    // ALT
    checkButton( '#enable_alt', '.show_alt', false );

    // SAVE HOOK
    checkButton( '#enable_save_post_hook', '.show_save_post_hook', false );

    // WP INSERT POST
    checkButton( '#enable_wp_insert_post_hook', '.show_wp_insert_post_hook', true );

    // PROXY
    checkButton( '#enable_proxy', '.show_proxy', false );

    /* LOGS */
    checkButton( '#enable_logs', '.show_logs', true );

    /* TITLE SELECTION */
    jQuery("#general-options .chosen_title input[type='radio']").change(function(){
        if( jQuery(this).val() == 'cut_title' ) {
                jQuery( "input[name='MPT_plugin_main_settings[title_length]']" ).removeAttr('disabled');
        } else {
                jQuery( "input[name='MPT_plugin_main_settings[title_length]']" ).attr('disabled', 'disabled');
        }
    });

    /* CRON SELECTION */
    jQuery("#general-options input.select-all-posts[type='radio']").change(function(){
      if( jQuery(this).val() == 'interval' ) {
        jQuery( "select[name='MPT_plugin_cron_settings[posts_date_select_interval_hours]'],select[name='MPT_plugin_cron_settings[posts_date_select_interval_days]'],select[name='MPT_plugin_cron_settings[cron_interval_word]']").removeAttr('disabled');
      } else {
        jQuery( "select[name='MPT_plugin_cron_settings[posts_date_select_interval_hours]'],select[name='MPT_plugin_cron_settings[posts_date_select_interval_days]'],select[name='MPT_plugin_cron_settings[cron_interval_word]']" ).attr('disabled', 'disabled');
      }
    });

    // CRON : Interval
    jQuery("select.select-word-cron").change(function(){
      if( jQuery(this).find('option:selected').val() == 'minutes' ) {
        jQuery( "select.form-control.select-interval-minutes-cron").show();
        jQuery( "select.form-control.select-interval-hours-cron").hide();
        jQuery( "select.form-control.select-interval-days-cron").hide();
      } else if( jQuery(this).find('option:selected').val() == 'hours' ) {
        jQuery( "select.form-control.select-interval-minutes-cron").hide();
        jQuery( "select.form-control.select-interval-hours-cron").show();
        jQuery( "select.form-control.select-interval-days-cron").hide();
      } else if( jQuery(this).val() == 'days' ) {
        jQuery( "select.form-control.select-interval-minutes-cron").hide();
        jQuery( "select.form-control.select-interval-hours-cron").hide();
        jQuery( "select.form-control.select-interval-days-cron").show();
      } else {}
    });

    // CRON : Post date
    jQuery("select.select-word").change(function(){
      if( jQuery(this).find('option:selected').val() == 'hours' ) {
        jQuery( "select.form-control.select-interval-hours").show();
        jQuery( "select.form-control.select-interval-days").hide();
      } else if( jQuery(this).val() == 'days' ) {
        jQuery( "select.form-control.select-interval-hours").hide();
        jQuery( "select.form-control.select-interval-days").show();
      } else {}
    });



    /* Google scrap domains textarea */
    if( false === checkRights() ) {
      jQuery('#restricted_domains, #blacklisted_domains').click(function() {
              var alertProVersion = translationsJsVars.translations.pro_version;
              alert( alertProVersion );
      });
    }

  /* Image Banks buttons  */
  if( true === checkRights() ) {
    jQuery('.chosen_api li label.checkbox-disabled').click(function() {
            var alertProVersion = translationsJsVars.translations.pro_version;
            alert( alertProVersion );
    });
  }

    // Delete logs confirmation
    jQuery('.delete-logs').click(function(e) {
            var deleteLogsSentence = translationsJsVars.translations.delete_logs;
            var deleteLogsAction = confirm(deleteLogsSentence);
            if ( true !== deleteLogsAction ) {
                e.preventDefault();
            }

    });

    /* CUSTOM REQUEST */

    /* Drag & drop */
    document.addEventListener('dragstart', function (event) {
      var addTag      = event.target.innerHTML;
      event.dataTransfer.setData('text/html', addTag);
    });

    document.addEventListener('dragend', function (event) {
      jQuery('#textarea-editable span').prop("contenteditable", false);
    });

    /* Click & copy */
    jQuery( "#custom-request-buttons > p" ).on( "click", function() {
      var currentTextareaVal = jQuery("#textarea-editable").html();
      var contentTag = event.target;
      jQuery("#textarea-editable").append(jQuery(this).children('span').clone());
      jQuery('#textarea-editable span').prop("contenteditable", false);
    });

    /* Prevent line break */
    jQuery(document).on('keypress', '#textarea-editable', function(e){
        return e.which != 13;
    });

    /* Change value on sumit */
  	jQuery("form.form-images").on('submit', function(e){
      jQuery(".custom_request").val(jQuery("#textarea-editable").html());

      var spanTag = jQuery("input.custom_request").val();
      var regex   = /<span\s.*?>(.*?)<\/span>/gi;
      var newTag  = spanTag.replace(regex, '%%$1%%');

      jQuery("input.custom_request").val(newTag);
  	});


    /*Change value on init div */
    if (jQuery('#textarea-editable').length){
      var divTag = jQuery("#textarea-editable").html();
      var regex  = /%%(.*?)%%/g;
      var newTag = divTag.replace(regex, '<span contenteditable="false" class="button-custom" draggable="false">$1</span>');

      jQuery('#textarea-editable').html(newTag);
    }


    /* Eye to show/hide API key */
    function hideAPIKey(passwordSelector) {

      const togglePassword  = document.querySelector(passwordSelector+" #togglePassword");
      const password        = document.querySelector(passwordSelector+" input");

      if(togglePassword) {
        togglePassword.addEventListener("click", function () {
            // toggle the type attribute
            const type = password.getAttribute("type") === "password" ? "text" : "password";
            password.setAttribute("type", type);

            // toggle the icon
            this.classList.toggle("close-eye");
        });
      }
    }

    hideAPIKey("#password-unsplash");
    hideAPIKey("#password-googleAPI");
    hideAPIKey("#password-pixabay");
    hideAPIKey("#password-dalle");
    hideAPIKey("#password-youtube");
    hideAPIKey("#password-pexels");
    hideAPIKey("#password-envato");
    hideAPIKey("#password-openai");

});


function checkButton( selectorSwitch, selectorOptions, noNeedRights = false ) {
    jQuery( selectorSwitch ).on( 'switchChange.bootstrapSwitch ', function( event, state ) {
        if( ( true === checkRights() ) || ( noNeedRights ) ) {
                if( true === state ) {
                        jQuery( selectorOptions ).show( 'fast' );
                } else {
                        jQuery( selectorOptions ).hide( 'fast' );
                }
        } else {
                if( true === state ) {
                    var alertProVersion = translationsJsVars.translations.pro_version;
                    alert( alertProVersion );
                }
                jQuery( selectorSwitch ).bootstrapSwitch( 'state', false, false );
        }
    });
}



function checkRights() {
    var premium_version = false;
    

    return premium_version;
}
