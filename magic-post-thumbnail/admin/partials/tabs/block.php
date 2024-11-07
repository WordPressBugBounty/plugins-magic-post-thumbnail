<?php
if ( ! function_exists( 'add_filter' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
		exit();
}

?>
<div class="wrap">

	<?php settings_errors(); ?>

    <form method="post" action="options.php" id="tabs">

	      <?php
	        settings_fields( 'MPT-plugin-block-settings' );
	        $options = wp_parse_args( get_option( 'MPT_plugin_block_settings' ), $this->MPT_default_options_block_settings( TRUE ) );
			//$options = get_option( 'MPT_plugin_block_settings' );
	      ?>
	          <table id="general-options" class="form-table tabs-content">
	              <tbody>
	                  <tr>
	                    <td>
                          <?php esc_html_e( 'Display manual search for featured image ', 'mpt' ); ?>
	                    </td>
	                    <td>
	                      <label class="checkbox">
	                          <input data-switch="true" type="checkbox" name="MPT_plugin_block_settings[enable_manual_search]" id="enable_manual_search" value="true" <?php echo( !empty( $options['enable_manual_search']) && $options['enable_manual_search'] == 'true' )? 'checked': ''; ?> />
	                      </label>
	                    </td>
	                  </tr>

					  <?php 
							// Alt Tag

							if ( true === $this->MPT_freemius()->is__premium_only() ) { 
								if ( $this->mpt_freemius()->can_use_premium_code() ) {
						?>

							<tr valign="top" class="based_on_bottom">
								<th scope="row">
									<label for="hseparator"><?php esc_html_e( 'Add alt tag on image', 'mpt' ); ?></label>
								</th>
								<td>
									<label class="checkbox">
										<input data-switch="true" type="checkbox" name="MPT_plugin_block_settings[enable_alt]" id="enable_alt" value="enable" <?php echo( !empty( $options['enable_alt']) && $options['enable_alt'] == 'enable' )? 'checked': ''; ?> />
									</label>
								</td>
							</tr>

							<?php 
									if( !isset( $options['translate_alt_lang'] ) ) {
										$wp_lang    = get_bloginfo('language');
										$alt_lang   = substr( $wp_lang, 0, 2 );
									} else {
										$alt_lang   = $options['translate_alt_lang'];
									}
							?>

							<tr valign="top" class="show_alt" <?php echo (isset($options['enable_alt']) && $options['enable_alt'] != 'enable') ? 'style="display:none;"' : ''; ?>>
								<th scope="row">
										<?php esc_html_e( 'Translation', 'mpt' ); ?>
								</th>
								<td class="checkbox-list">
										<label class="checkbox">
											<input name="MPT_plugin_block_settings[translate_alt]" type="checkbox" value="true" <?php echo( !empty( $options['translate_alt']) && $options['translate_alt'] == 'true' )? 'checked': ''; ?>><span></span> 
											<?php esc_html_e( 'Translate alt text from english to', 'mpt' ); ?>:
										</label>

										<select name="MPT_plugin_block_settings[translate_alt_lang]" class="form-control form-control-lg" >
											<?php
												
												$country_choose = array(
													__( 'Afrikaans', 'mpt' )             => 'af',
													__( 'Afrikaans', 'mpt' )             => 'af',
													__( 'Albanian', 'mpt' )              => 'sq',
													__( 'Amharic', 'mpt' )               => 'sm',
													__( 'Arabic', 'mpt' )                => 'ar',
													__( 'Azerbaijani', 'mpt' )           => 'az',
													__( 'Basque', 'mpt' )                => 'eu',
													__( 'Belarusian', 'mpt' )            => 'be',
													__( 'Bengali', 'mpt' )               => 'bn',
													__( 'Bihari', 'mpt' )                => 'bh',
													__( 'Bosnian', 'mpt' )               => 'bs',
													__( 'Bulgarian', 'mpt' )             => 'bg',
													__( 'Catalan', 'mpt' )               => 'ca',
													__( 'Chinese (Simplified)', 'mpt' )  => 'zh-CN',
													__( 'Chinese (Traditional)', 'mpt' ) => 'zh-TW',
													__( 'Croatian', 'mpt' )              => 'hr',
													__( 'Czech', 'mpt' )                 => 'cs',
													__( 'Danish', 'mpt' )                => 'da',
													__( 'Dutch', 'mpt' )                 => 'nl',
													__( 'Esperanto', 'mpt' )             => 'eo',
													__( 'Estonian', 'mpt' )              => 'et',
													__( 'Faroese', 'mpt' )               => 'fo',
													__( 'Finnish', 'mpt' )               => 'fi',
													__( 'French', 'mpt' )                => 'fr',
													__( 'Frisian', 'mpt' )               => 'fy',
													__( 'Galician', 'mpt' )              => 'gl',
													__( 'Georgian', 'mpt' )              => 'ka',
													__( 'German', 'mpt' )                => 'de',
													__( 'Greek', 'mpt' )                 => 'el',
													__( 'Gujarati', 'mpt' )              => 'gu',
													__( 'Hebrew', 'mpt' )                => 'iw',
													__( 'Hindi', 'mpt' )                 => 'hi',
													__( 'Hungarian', 'mpt' )             => 'hu',
													__( 'Icelandic', 'mpt' )             => 'is',
													__( 'Indonesian', 'mpt' )            => 'id',
													__( 'Interlingua', 'mpt' )           => 'ia',
													__( 'Irish', 'mpt' )                 => 'ga',
													__( 'Italian', 'mpt' )               => 'it',
													__( 'Japanese', 'mpt' )              => 'ja',
													__( 'Javanese', 'mpt' )              => 'jw',
													__( 'Kannada', 'mpt' )               => 'kn',
													__( 'Korean', 'mpt' )                => 'ko',
													__( 'Latin', 'mpt' )                 => 'la',
													__( 'Latvian', 'mpt' )               => 'lv',
													__( 'Lithuanian', 'mpt' )            => 'lt',
													__( 'Macedonian', 'mpt' )            => 'mk',
													__( 'Malay', 'mpt' )                 => 'ms',
													__( 'Malayam', 'mpt' )               => 'ml',
													__( 'Maltese', 'mpt' )               => 'mt',
													__( 'Marathi', 'mpt' )               => 'mr',
													__( 'Nepali', 'mpt' )                => 'ne',
													__( 'Norwegian', 'mpt' )             => 'no',
													__( 'Norwegian (Nynorsk)', 'mpt' )   => 'nn',
													__( 'Occitan', 'mpt' )               => 'oc',
													__( 'Persian', 'mpt' )               => 'fa',
													__( 'Polish', 'mpt' )                => 'pl',
													__( 'Portuguese (Brazil)', 'mpt' )   => 'pt-BR',
													__( 'Portuguese (Portugal)', 'mpt' ) => 'pt-PT',
													__( 'Punjabi', 'mpt' )               => 'pa',
													__( 'Romanian', 'mpt' )              => 'ro',
													__( 'Russian', 'mpt' )               => 'ru',
													__( 'Scots Gaelic', 'mpt' )          => 'gd',
													__( 'Serbian', 'mpt' )               => 'sr',
													__( 'Sinhalese', 'mpt' )             => 'si',
													__( 'Slovak', 'mpt' )                => 'sk',
													__( 'Slovenian', 'mpt' )             => 'sl',
													__( 'Spanish', 'mpt' )               => 'es',
													__( 'Sudanese', 'mpt' )              => 'su',
													__( 'Swahili', 'mpt' )               => 'sw',
													__( 'Swedish', 'mpt' )               => 'sv',
													__( 'Tagalog', 'mpt' )               => 'tl',
													__( 'Tamil', 'mpt' )                 => 'ta',
													__( 'Telugu', 'mpt' )                => 'te',
													__( 'Thai', 'mpt' )                  => 'th',
													__( 'Tigrinya', 'mpt' )              => 'ti',
													__( 'Turkish', 'mpt' )               => 'tr',
													__( 'Ukrainian', 'mpt' )             => 'uk',
													__( 'Urdu', 'mpt' )                  => 'ur',
													__( 'Uzbek', 'mpt' )                 => 'uz',
													__( 'Vietnamese', 'mpt' )            => 'vi',
													__( 'Welsh', 'mpt' )                 => 'cy',
													__( 'Xhosa', 'mpt' )                 => 'xh',
													__( 'Zulu', 'mpt' )                  => 'zu',
												);
												ksort( $country_choose );

												foreach( $country_choose as $name_country => $code_country ) {
													$choose = ( $alt_lang == $code_country) ? 'selected="selected"': '';
													echo '<option '. $choose .' value="'. $code_country .'">'. $name_country .'</option>';
												}
											?>
										</select>

								</td>
							</tr>


						<?php } } else { ?>
							<tr valign="top" class="based_on_bottom">
								<th scope="row">
									<label for="hseparator">
										<?php esc_html_e( 'Add alt tag on image', 'mpt' ); ?><br/>
										<small><?php esc_html_e( 'Only available with the pro version', 'mpt' ); ?></small>
									</label>
								</th>
								<td>
									<label class="checkbox checkbox-disabled checkbox-admin">
										<input disabled="disabled" data-switch="true" type="checkbox" name="MPT_plugin_block_settings[enable_alt]" id="enable_alt" value="disable" />
									</label>
								</td>
							</tr>
						<?php } ?>


						<?php 
							// Caption Tag

							if ( true === $this->MPT_freemius()->is__premium_only() ) { 
								if ( $this->mpt_freemius()->can_use_premium_code() ) {
						?>

							<tr valign="top" class="based_on_bottom">
								<th scope="row">
									<label for="hseparator"><?php esc_html_e( 'Add caption tag on image', 'mpt' ); ?></label>
								</th>
								<td>
									<label class="checkbox">
										<input data-switch="true" type="checkbox" name="MPT_plugin_block_settings[enable_caption]" id="enable_caption" value="enable" <?php echo( !empty( $options['enable_caption']) && $options['enable_caption'] == 'enable' )? 'checked': ''; ?> />
									</label>
								</td>
							</tr>

						<?php } } ?>


	              </tbody>
	          </table>
	      <?php submit_button(); ?>
						
    </form>
</div>
