<?php
if ( ! function_exists( 'add_filter' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
		exit();
}
?>
<tr valign="top">
	<td colspan="2">
		<div class="update-nag">
			<?php _e('From now on, <b>it\'s required</b> to provide your own Google <i>Search engine ID</i> and <b>API Key</b>. You must follow the process <a target="_blank" href="https://developers.google.com/custom-search/json-api/v1/overview#prerequisites">here</a>. You must get both valid <strong>Search engine ID</strong> and <strong>API Key</strong>', 'mpt' ); ?>
		</div>
	</td>
</tr>

<tr valign="top">
	<th scope="row">
		<label for="hseparator"><?php esc_html_e( 'Custom Search Engine ID', 'mpt' ); ?></label>
	</th>
	<td>
		<input type="text" name="MPT_plugin_banks_settings[googleimage][cxid]" class="form-control" value="<?php echo( isset( $options['googleimage']['cxid'] ) && !empty( $options['googleimage']['cxid'] ) )? trim( $options['googleimage']['cxid'] ): ''; ?>" >
	</td>
</tr>
<tr valign="top">
	<th scope="row">
		<label for="hseparator"><?php esc_html_e( 'Google API Key', 'mpt' ); ?></label>
	</th>
	<td id="password-googleAPI" class="password">
		<input type="password" name="MPT_plugin_banks_settings[googleimage][apikey]" class="form-control" value="<?php echo( isset( $options['googleimage']['apikey'] ) && !empty( $options['googleimage']['apikey']) )? trim( $options['googleimage']['apikey'] ): ''; ?>" >
		<i id="togglePassword"></i>
	</td>
</tr>

<tr valign="top">
	<td colspan="2">
		<hr/>
	</td>
</tr>

<tr valign="top">
	<th scope="row">
		<label for="hseparator"><?php esc_html_e( 'Choose the language', 'mpt' ); ?></label>
	</th>
	<td>
		<select name="MPT_plugin_banks_settings[googleimage][search_country]" class="form-control form-control-lg" >
			<?php
				$selected = $options['googleimage']['search_country'];
				$country_choose = array(
					esc_html__( 'English (default)', 'mpt' )     => 'en',
					esc_html__( 'Afrikaans', 'mpt' )             => 'af',
					esc_html__( 'Afrikaans', 'mpt' )             => 'af',
					esc_html__( 'Albanian', 'mpt' )              => 'sq',
					esc_html__( 'Amharic', 'mpt' )               => 'sm',
					esc_html__( 'Arabic', 'mpt' )                => 'ar',
					esc_html__( 'Azerbaijani', 'mpt' )           => 'az',
					esc_html__( 'Basque', 'mpt' )                => 'eu',
					esc_html__( 'Belarusian', 'mpt' )            => 'be',
					esc_html__( 'Bengali', 'mpt' )               => 'bn',
					esc_html__( 'Bihari', 'mpt' )                => 'bh',
					esc_html__( 'Bosnian', 'mpt' )               => 'bs',
					esc_html__( 'Bulgarian', 'mpt' )             => 'bg',
					esc_html__( 'Catalan', 'mpt' )               => 'ca',
					esc_html__( 'Chinese (Simplified)', 'mpt' )  => 'zh-CN',
					esc_html__( 'Chinese (Traditional)', 'mpt' ) => 'zh-TW',
					esc_html__( 'Croatian', 'mpt' )              => 'hr',
					esc_html__( 'Czech', 'mpt' )                 => 'cs',
					esc_html__( 'Danish', 'mpt' )                => 'da',
					esc_html__( 'Dutch', 'mpt' )                 => 'nl',
					esc_html__( 'English', 'mpt' )               => 'en',
					esc_html__( 'Esperanto', 'mpt' )             => 'eo',
					esc_html__( 'Estonian', 'mpt' )              => 'et',
					esc_html__( 'Faroese', 'mpt' )               => 'fo',
					esc_html__( 'Finnish', 'mpt' )               => 'fi',
					esc_html__( 'French', 'mpt' )                => 'fr',
					esc_html__( 'Frisian', 'mpt' )               => 'fy',
					esc_html__( 'Galician', 'mpt' )              => 'gl',
					esc_html__( 'Georgian', 'mpt' )              => 'ka',
					esc_html__( 'German', 'mpt' )                => 'de',
					esc_html__( 'Greek', 'mpt' )                 => 'el',
					esc_html__( 'Gujarati', 'mpt' )              => 'gu',
					esc_html__( 'Hebrew', 'mpt' )                => 'iw',
					esc_html__( 'Hindi', 'mpt' )                 => 'hi',
					esc_html__( 'Hungarian', 'mpt' )             => 'hu',
					esc_html__( 'Icelandic', 'mpt' )             => 'is',
					esc_html__( 'Indonesian', 'mpt' )            => 'id',
					esc_html__( 'Interlingua', 'mpt' )           => 'ia',
					esc_html__( 'Irish', 'mpt' )                 => 'ga',
					esc_html__( 'Italian', 'mpt' )               => 'it',
					esc_html__( 'Japanese', 'mpt' )              => 'ja',
					esc_html__( 'Javanese', 'mpt' )              => 'jw',
					esc_html__( 'Kannada', 'mpt' )               => 'kn',
					esc_html__( 'Korean', 'mpt' )                => 'ko',
					esc_html__( 'Latin', 'mpt' )                 => 'la',
					esc_html__( 'Latvian', 'mpt' )               => 'lv',
					esc_html__( 'Lithuanian', 'mpt' )            => 'lt',
					esc_html__( 'Macedonian', 'mpt' )            => 'mk',
					esc_html__( 'Malay', 'mpt' )                 => 'ms',
					esc_html__( 'Malayam', 'mpt' )               => 'ml',
					esc_html__( 'Maltese', 'mpt' )               => 'mt',
					esc_html__( 'Marathi', 'mpt' )               => 'mr',
					esc_html__( 'Nepali', 'mpt' )                => 'ne',
					esc_html__( 'Norwegian', 'mpt' )             => 'no',
					esc_html__( 'Norwegian (Nynorsk)', 'mpt' )   => 'nn',
					esc_html__( 'Occitan', 'mpt' )               => 'oc',
					esc_html__( 'Persian', 'mpt' )               => 'fa',
					esc_html__( 'Polish', 'mpt' )                => 'pl',
					esc_html__( 'Portuguese (Brazil)', 'mpt' )   => 'pt-BR',
					esc_html__( 'Portuguese (Portugal)', 'mpt' ) => 'pt-PT',
					esc_html__( 'Punjabi', 'mpt' )               => 'pa',
					esc_html__( 'Romanian', 'mpt' )              => 'ro',
					esc_html__( 'Russian', 'mpt' )               => 'ru',
					esc_html__( 'Scots Gaelic', 'mpt' )          => 'gd',
					esc_html__( 'Serbian', 'mpt' )               => 'sr',
					esc_html__( 'Sinhalese', 'mpt' )             => 'si',
					esc_html__( 'Slovak', 'mpt' )                => 'sk',
					esc_html__( 'Slovenian', 'mpt' )             => 'sl',
					esc_html__( 'Spanish', 'mpt' )               => 'es',
					esc_html__( 'Sudanese', 'mpt' )              => 'su',
					esc_html__( 'Swahili', 'mpt' )               => 'sw',
					esc_html__( 'Swedish', 'mpt' )               => 'sv',
					esc_html__( 'Tagalog', 'mpt' )               => 'tl',
					esc_html__( 'Tamil', 'mpt' )                 => 'ta',
					esc_html__( 'Telugu', 'mpt' )                => 'te',
					esc_html__( 'Thai', 'mpt' )                  => 'th',
					esc_html__( 'Tigrinya', 'mpt' )              => 'ti',
					esc_html__( 'Turkish', 'mpt' )               => 'tr',
					esc_html__( 'Ukrainian', 'mpt' )             => 'uk',
					esc_html__( 'Urdu', 'mpt' )                  => 'ur',
					esc_html__( 'Uzbek', 'mpt' )                 => 'uz',
					esc_html__( 'Vietnamese', 'mpt' )            => 'vi',
					esc_html__( 'Welsh', 'mpt' )                 => 'cy',
					esc_html__( 'Xhosa', 'mpt' )                 => 'xh',
					esc_html__( 'Zulu', 'mpt' )                  => 'zu',
				);
				//ksort( $country_choose );

				foreach( $country_choose as $name_country => $code_country ) {
					$choose = ( $selected == $code_country) ? 'selected="selected"': '';
					echo '<option '. $choose .' value="'. $code_country .'">'. $name_country .'</option>';
				}
			?>
		</select>
	</td>
</tr>

<tr valign="top">
	<th scope="row">
		<label for="hseparator"><?php esc_html_e( 'Specified color predominantly', 'mpt' ); ?></label>
	</th>
	<td>
		<select name="MPT_plugin_banks_settings[googleimage][img_color]" class="form-control form-control-lg" >
			<?php
			$selected = $options['googleimage']['img_color'];

			$img_color = array(
				esc_html__( '-- Default --', 'mpt' ) => '',
				esc_html__( 'Black', 'mpt' )         => 'black',
				esc_html__( 'Blue', 'mpt' )          => 'blue',
				esc_html__( 'Brown', 'mpt' )         => 'brown',
				esc_html__( 'Gray', 'mpt' )          => 'gray',
				esc_html__( 'Green', 'mpt' )         => 'green',
				esc_html__( 'Pink', 'mpt' )          => 'pink',
				esc_html__( 'Purple', 'mpt' )        => 'purple',
				esc_html__( 'Teal', 'mpt' )          => 'teal',
				esc_html__( 'White', 'mpt' )         => 'white',
				esc_html__( 'Yellow', 'mpt' )        => 'yellow',
			);
			ksort( $img_color );

			foreach( $img_color as $name_color => $code_color ) {
				$choose=($selected == $code_color)?'selected="selected"': '';
				echo '<option '. $choose .' value="'. $code_color .'">'. $name_color .'</option>';
			}
			?>
		</select>
		<br/>
		<p class="description">
			<i><?php esc_html_e( 'Experimental', 'mpt' ); ?></i> -
			<?php esc_html_e( 'Restricts results to images that contain a specified color predominantly', 'mpt' ); ?>
		</p>
	</td>
</tr>

<tr valign="top">
	<th scope="row">
		<label for="hseparator"><?php esc_html_e( 'Filetype', 'mpt' ); ?></label>
	</th>
	<td>
		<select name="MPT_plugin_banks_settings[googleimage][filetype]" class="form-control form-control-lg" >
			<?php
			$selected = $options['googleimage']['filetype'];

			$filetype = array(
				esc_html__( '-- Default --', 'mpt' ) => '',
				esc_html__( 'jpg', 'mpt' )           => 'jpg',
				esc_html__( 'png', 'mpt' )           => 'png',
				esc_html__( 'gif', 'mpt' )           => 'gif',
				esc_html__( 'bmp', 'mpt' )           => 'bmp',
				esc_html__( 'webp', 'mpt' )          => 'webp'
			);
			ksort( $filetype );

			foreach( $filetype as $name_filetype => $code_filetype ) {
				$choose=($selected == $code_filetype)?'selected="selected"': '';
				echo '<option '. $choose .' value="'. $code_filetype .'">'. $name_filetype .'</option>';
			}
			?>
		</select>
		<br/>
		<p class="description">
			<?php esc_html_e( 'Restricts image search to one of the following specific file types', 'mpt' ); ?>
		</p>
	</td>
</tr>

<tr valign="top">
	<th scope="row">
		<label for="hseparator"><?php esc_html_e( 'Rights', 'mpt' ); ?></label>
	</th>
	<td class="checkbox-list">
		<p class="description">
			<?php esc_html_e( 'Choose these options can reduce relevance of results, but permit to use free-to-use images.', 'mpt' ); ?>
		</p>
		<?php
			$rights_array = array(
				__( 'Publicdomain - <i>restricts search results to images with the publicdomain label.</i>', 'mpt' ) 		=> 'cc_publicdomain',
				__( 'Attribute - <i>restricts search results to images with the attribute label.</i>', 'mpt' )          => 'cc_attribute',
				__( 'Sharealike - <i>restricts search results to images with the sharealike label.</i>', 'mpt' )        => 'cc_sharealike',
				__( 'Noncommercial - <i>restricts search results to images with the noncomercial label.</i>', 'mpt' )   => 'cc_noncommercial',
				__( 'Nonderived - <i>restricts search results to images with the nonderived label.</i>', 'mpt' )     		=> 'cc_nonderived',
			);


			foreach ( $rights_array  as $right => $right_code ) {
				$checked= ( isset( $options['googleimage']['rights'] ) && !empty( $options['googleimage']['rights'] ) && in_array( $right_code, $options['googleimage']['rights'] ) )? 'checked="checked""' : '';
				echo '<label class="checkbox">
					<input '. $checked .' name="MPT_plugin_banks_settings[googleimage][rights]['. $right_code .']" type="checkbox" value="'. $right_code .'"> <span></span> '. $right .'
				</label>';
			}
		?>

	</td>
</tr>

<tr valign="top">
	<th scope="row">
		<label for="hseparator"><?php esc_html_e( 'Image size', 'mpt' ); ?></label>
	</th>
	<td>
		<select name="MPT_plugin_banks_settings[googleimage][imgsz]" class="form-control form-control-lg" >
			<?php
			$selected = $options['googleimage']['imgsz'];

			$imgsz = array(
				esc_html__( '-- Default --', 'mpt' ) => '',
				esc_html__( 'icon', 'mpt' )          => 'icon',
				esc_html__( 'small', 'mpt' )         => 'small',
				esc_html__( 'medium', 'mpt' )        => 'medium',
				esc_html__( 'large', 'mpt' )         => 'large',
				esc_html__( 'xlarge', 'mpt' )        => 'xlarge',
				esc_html__( 'xxlarge', 'mpt' )       => 'xxlarge',
				esc_html__( 'huge', 'mpt' )          => 'huge',
			);

			foreach( $imgsz as $name_imgsz => $code_imgsz ) {
				$choose=($selected == $code_imgsz)?'selected="selected"': '';
				echo '<option '. $choose .' value="'. $code_imgsz .'">'. $name_imgsz .'</option>';
			}
			?>
		</select>
	</td>
</tr>


<tr valign="top">
	<th scope="row">
		<label for="hseparator"><?php esc_html_e( 'Image Type', 'mpt' ); ?></label>
	</th>
	<td>
		<select name="MPT_plugin_banks_settings[googleimage][imgtype]" class="form-control form-control-lg" >
			<?php
			$selected = $options['googleimage']['imgtype'];

			$imgtype = array(
				esc_html__( '-- Default --', 'mpt' ) => '',
				esc_html__( 'Face', 'mpt' )          => 'face',
				esc_html__( 'Photo', 'mpt' )         => 'photo',
				esc_html__( 'Clipart', 'mpt' )       => 'clipart',
				esc_html__( 'Lineart', 'mpt' )       => 'lineart',
			);

			foreach( $imgtype as $name_imgtype => $code_imgtype ) {
				$choose=($selected == $code_imgtype)?'selected="selected"': '';
				echo '<option '. $choose .' value="'. $code_imgtype .'">'. $name_imgtype .'</option>';
			}
			?>
		</select>
	</td>
</tr>

<tr valign="top">
	<th scope="row">
		<label for="hseparator"><?php esc_html_e( 'Safety level', 'mpt' ); ?></label>
	</th>
	<td>
		<select name="MPT_plugin_banks_settings[googleimage][safe]" class="form-control form-control-lg" >
			<?php
			$selected = $options['googleimage']['safe'];

			$safe = array(
				esc_html__( 'Moderate (default)', 'mpt' ) => 'moderate',
				esc_html__( 'Active', 'mpt' )             => 'activate',
				esc_html__( 'Off', 'mpt' )                => 'off',
			);

			foreach( $safe as $name_safe => $code_safe ) {
				$choose = ( $selected == $code_safe ) ? 'selected="selected"' : '';
				echo '<option '. $choose .' value="'. $code_safe .'">'. $name_safe .'</option>';
			}
			?>
		</select>
	</td>
</tr>
