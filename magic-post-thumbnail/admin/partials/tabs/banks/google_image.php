<?php
if ( ! function_exists( 'add_filter' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
		exit();
}
?>

<tr valign="top">
	<td colspan="2" class="source-logo"><img alt="Google Images Logo" src="<?php echo plugin_dir_url( __FILE__ ); ?>/img/google_images.png"></td>
</tr>

<tr valign="top">
	<td colspan="2">
		<div class="update-nag">
			<?php _e('From now on, <b>it\'s required</b> to provide your own Google <i>Search engine ID</i> and <b>API Key</b>. You must follow the process <a target="_blank" href="https://developers.google.com/custom-search/json-api/v1/overview#prerequisites">here</a>. You must get both valid <strong>Search engine ID</strong> and <strong>API Key</strong>', 'magic-post-thumbnail' ); ?>
		</div>
	</td>
</tr>

<tr valign="top">
	<th scope="row">
		<label for="hseparator"><?php esc_html_e( 'Custom Search Engine ID', 'magic-post-thumbnail' ); ?></label>
	</th>
	<td>
		<input type="text" name="MPT_plugin_banks_settings[googleimage][cxid]" class="form-control" value="<?php echo( isset( $options['googleimage']['cxid'] ) && !empty( $options['googleimage']['cxid'] ) )? esc_attr( $options['googleimage']['cxid'] ): ''; ?>" >
	</td>
</tr>
<tr valign="top">
	<th scope="row">
		<label for="hseparator"><?php esc_html_e( 'Google API Key', 'magic-post-thumbnail' ); ?></label>
	</th>
	<td id="password-googleAPI" class="password">
		<input type="password" name="MPT_plugin_banks_settings[googleimage][apikey]" class="form-control" value="<?php echo( isset( $options['googleimage']['apikey'] ) && !empty( $options['googleimage']['apikey']) )? esc_attr( $options['googleimage']['apikey'] ): ''; ?>" >
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
		<label for="hseparator"><?php esc_html_e( 'Choose the language', 'magic-post-thumbnail' ); ?></label>
	</th>
	<td>
		<select name="MPT_plugin_banks_settings[googleimage][search_country]" class="form-control form-control-lg" >
			<?php
				$selected = $options['googleimage']['search_country'];
				$country_choose = array(
					esc_html__( 'English (default)', 'magic-post-thumbnail' )     => 'en',
					esc_html__( 'Afrikaans', 'magic-post-thumbnail' )             => 'af',
					esc_html__( 'Afrikaans', 'magic-post-thumbnail' )             => 'af',
					esc_html__( 'Albanian', 'magic-post-thumbnail' )              => 'sq',
					esc_html__( 'Amharic', 'magic-post-thumbnail' )               => 'sm',
					esc_html__( 'Arabic', 'magic-post-thumbnail' )                => 'ar',
					esc_html__( 'Azerbaijani', 'magic-post-thumbnail' )           => 'az',
					esc_html__( 'Basque', 'magic-post-thumbnail' )                => 'eu',
					esc_html__( 'Belarusian', 'magic-post-thumbnail' )            => 'be',
					esc_html__( 'Bengali', 'magic-post-thumbnail' )               => 'bn',
					esc_html__( 'Bihari', 'magic-post-thumbnail' )                => 'bh',
					esc_html__( 'Bosnian', 'magic-post-thumbnail' )               => 'bs',
					esc_html__( 'Bulgarian', 'magic-post-thumbnail' )             => 'bg',
					esc_html__( 'Catalan', 'magic-post-thumbnail' )               => 'ca',
					esc_html__( 'Chinese (Simplified)', 'magic-post-thumbnail' )  => 'zh-CN',
					esc_html__( 'Chinese (Traditional)', 'magic-post-thumbnail' ) => 'zh-TW',
					esc_html__( 'Croatian', 'magic-post-thumbnail' )              => 'hr',
					esc_html__( 'Czech', 'magic-post-thumbnail' )                 => 'cs',
					esc_html__( 'Danish', 'magic-post-thumbnail' )                => 'da',
					esc_html__( 'Dutch', 'magic-post-thumbnail' )                 => 'nl',
					esc_html__( 'English', 'magic-post-thumbnail' )               => 'en',
					esc_html__( 'Esperanto', 'magic-post-thumbnail' )             => 'eo',
					esc_html__( 'Estonian', 'magic-post-thumbnail' )              => 'et',
					esc_html__( 'Faroese', 'magic-post-thumbnail' )               => 'fo',
					esc_html__( 'Finnish', 'magic-post-thumbnail' )               => 'fi',
					esc_html__( 'French', 'magic-post-thumbnail' )                => 'fr',
					esc_html__( 'Frisian', 'magic-post-thumbnail' )               => 'fy',
					esc_html__( 'Galician', 'magic-post-thumbnail' )              => 'gl',
					esc_html__( 'Georgian', 'magic-post-thumbnail' )              => 'ka',
					esc_html__( 'German', 'magic-post-thumbnail' )                => 'de',
					esc_html__( 'Greek', 'magic-post-thumbnail' )                 => 'el',
					esc_html__( 'Gujarati', 'magic-post-thumbnail' )              => 'gu',
					esc_html__( 'Hebrew', 'magic-post-thumbnail' )                => 'iw',
					esc_html__( 'Hindi', 'magic-post-thumbnail' )                 => 'hi',
					esc_html__( 'Hungarian', 'magic-post-thumbnail' )             => 'hu',
					esc_html__( 'Icelandic', 'magic-post-thumbnail' )             => 'is',
					esc_html__( 'Indonesian', 'magic-post-thumbnail' )            => 'id',
					esc_html__( 'Interlingua', 'magic-post-thumbnail' )           => 'ia',
					esc_html__( 'Irish', 'magic-post-thumbnail' )                 => 'ga',
					esc_html__( 'Italian', 'magic-post-thumbnail' )               => 'it',
					esc_html__( 'Japanese', 'magic-post-thumbnail' )              => 'ja',
					esc_html__( 'Javanese', 'magic-post-thumbnail' )              => 'jw',
					esc_html__( 'Kannada', 'magic-post-thumbnail' )               => 'kn',
					esc_html__( 'Korean', 'magic-post-thumbnail' )                => 'ko',
					esc_html__( 'Latin', 'magic-post-thumbnail' )                 => 'la',
					esc_html__( 'Latvian', 'magic-post-thumbnail' )               => 'lv',
					esc_html__( 'Lithuanian', 'magic-post-thumbnail' )            => 'lt',
					esc_html__( 'Macedonian', 'magic-post-thumbnail' )            => 'mk',
					esc_html__( 'Malay', 'magic-post-thumbnail' )                 => 'ms',
					esc_html__( 'Malayam', 'magic-post-thumbnail' )               => 'ml',
					esc_html__( 'Maltese', 'magic-post-thumbnail' )               => 'mt',
					esc_html__( 'Marathi', 'magic-post-thumbnail' )               => 'mr',
					esc_html__( 'Nepali', 'magic-post-thumbnail' )                => 'ne',
					esc_html__( 'Norwegian', 'magic-post-thumbnail' )             => 'no',
					esc_html__( 'Norwegian (Nynorsk)', 'magic-post-thumbnail' )   => 'nn',
					esc_html__( 'Occitan', 'magic-post-thumbnail' )               => 'oc',
					esc_html__( 'Persian', 'magic-post-thumbnail' )               => 'fa',
					esc_html__( 'Polish', 'magic-post-thumbnail' )                => 'pl',
					esc_html__( 'Portuguese (Brazil)', 'magic-post-thumbnail' )   => 'pt-BR',
					esc_html__( 'Portuguese (Portugal)', 'magic-post-thumbnail' ) => 'pt-PT',
					esc_html__( 'Punjabi', 'magic-post-thumbnail' )               => 'pa',
					esc_html__( 'Romanian', 'magic-post-thumbnail' )              => 'ro',
					esc_html__( 'Russian', 'magic-post-thumbnail' )               => 'ru',
					esc_html__( 'Scots Gaelic', 'magic-post-thumbnail' )          => 'gd',
					esc_html__( 'Serbian', 'magic-post-thumbnail' )               => 'sr',
					esc_html__( 'Sinhalese', 'magic-post-thumbnail' )             => 'si',
					esc_html__( 'Slovak', 'magic-post-thumbnail' )                => 'sk',
					esc_html__( 'Slovenian', 'magic-post-thumbnail' )             => 'sl',
					esc_html__( 'Spanish', 'magic-post-thumbnail' )               => 'es',
					esc_html__( 'Sudanese', 'magic-post-thumbnail' )              => 'su',
					esc_html__( 'Swahili', 'magic-post-thumbnail' )               => 'sw',
					esc_html__( 'Swedish', 'magic-post-thumbnail' )               => 'sv',
					esc_html__( 'Tagalog', 'magic-post-thumbnail' )               => 'tl',
					esc_html__( 'Tamil', 'magic-post-thumbnail' )                 => 'ta',
					esc_html__( 'Telugu', 'magic-post-thumbnail' )                => 'te',
					esc_html__( 'Thai', 'magic-post-thumbnail' )                  => 'th',
					esc_html__( 'Tigrinya', 'magic-post-thumbnail' )              => 'ti',
					esc_html__( 'Turkish', 'magic-post-thumbnail' )               => 'tr',
					esc_html__( 'Ukrainian', 'magic-post-thumbnail' )             => 'uk',
					esc_html__( 'Urdu', 'magic-post-thumbnail' )                  => 'ur',
					esc_html__( 'Uzbek', 'magic-post-thumbnail' )                 => 'uz',
					esc_html__( 'Vietnamese', 'magic-post-thumbnail' )            => 'vi',
					esc_html__( 'Welsh', 'magic-post-thumbnail' )                 => 'cy',
					esc_html__( 'Xhosa', 'magic-post-thumbnail' )                 => 'xh',
					esc_html__( 'Zulu', 'magic-post-thumbnail' )                  => 'zu',
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
		<label for="hseparator"><?php esc_html_e( 'Specified color predominantly', 'magic-post-thumbnail' ); ?></label>
	</th>
	<td>
		<select name="MPT_plugin_banks_settings[googleimage][img_color]" class="form-control form-control-lg" >
			<?php
			$selected = $options['googleimage']['img_color'];

			$img_color = array(
				esc_html__( '-- Default --', 'magic-post-thumbnail' ) => '',
				esc_html__( 'Black', 'magic-post-thumbnail' )         => 'black',
				esc_html__( 'Blue', 'magic-post-thumbnail' )          => 'blue',
				esc_html__( 'Brown', 'magic-post-thumbnail' )         => 'brown',
				esc_html__( 'Gray', 'magic-post-thumbnail' )          => 'gray',
				esc_html__( 'Green', 'magic-post-thumbnail' )         => 'green',
				esc_html__( 'Pink', 'magic-post-thumbnail' )          => 'pink',
				esc_html__( 'Purple', 'magic-post-thumbnail' )        => 'purple',
				esc_html__( 'Teal', 'magic-post-thumbnail' )          => 'teal',
				esc_html__( 'White', 'magic-post-thumbnail' )         => 'white',
				esc_html__( 'Yellow', 'magic-post-thumbnail' )        => 'yellow',
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
			<i><?php esc_html_e( 'Experimental', 'magic-post-thumbnail' ); ?></i> -
			<?php esc_html_e( 'Restricts results to images that contain a specified color predominantly', 'magic-post-thumbnail' ); ?>
		</p>
	</td>
</tr>

<tr valign="top">
	<th scope="row">
		<label for="hseparator"><?php esc_html_e( 'Filetype', 'magic-post-thumbnail' ); ?></label>
	</th>
	<td>
		<select name="MPT_plugin_banks_settings[googleimage][filetype]" class="form-control form-control-lg" >
			<?php
			$selected = $options['googleimage']['filetype'];

			$filetype = array(
				esc_html__( '-- Default --', 'magic-post-thumbnail' ) => '',
				esc_html__( 'jpg', 'magic-post-thumbnail' )           => 'jpg',
				esc_html__( 'png', 'magic-post-thumbnail' )           => 'png',
				esc_html__( 'gif', 'magic-post-thumbnail' )           => 'gif',
				esc_html__( 'bmp', 'magic-post-thumbnail' )           => 'bmp',
				esc_html__( 'webp', 'magic-post-thumbnail' )          => 'webp'
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
			<?php esc_html_e( 'Restricts image search to one of the following specific file types', 'magic-post-thumbnail' ); ?>
		</p>
	</td>
</tr>

<tr valign="top">
	<th scope="row">
		<label for="hseparator"><?php esc_html_e( 'Rights', 'magic-post-thumbnail' ); ?></label>
	</th>
	<td class="checkbox-list">
		<p class="description">
			<?php esc_html_e( 'Choose these options can reduce relevance of results, but permit to use free-to-use images.', 'magic-post-thumbnail' ); ?>
		</p>
		<?php
			$rights_array = array(
				__( 'Publicdomain - <i>restricts search results to images with the publicdomain label.</i>', 'magic-post-thumbnail' ) 		=> 'cc_publicdomain',
				__( 'Attribute - <i>restricts search results to images with the attribute label.</i>', 'magic-post-thumbnail' )          => 'cc_attribute',
				__( 'Sharealike - <i>restricts search results to images with the sharealike label.</i>', 'magic-post-thumbnail' )        => 'cc_sharealike',
				__( 'Noncommercial - <i>restricts search results to images with the noncomercial label.</i>', 'magic-post-thumbnail' )   => 'cc_noncommercial',
				__( 'Nonderived - <i>restricts search results to images with the nonderived label.</i>', 'magic-post-thumbnail' )     		=> 'cc_nonderived',
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
		<label for="hseparator"><?php esc_html_e( 'Image size', 'magic-post-thumbnail' ); ?></label>
	</th>
	<td>
		<select name="MPT_plugin_banks_settings[googleimage][imgsz]" class="form-control form-control-lg" >
			<?php
			$selected = $options['googleimage']['imgsz'];

			$imgsz = array(
				esc_html__( '-- Default --', 'magic-post-thumbnail' ) => '',
				esc_html__( 'icon', 'magic-post-thumbnail' )          => 'icon',
				esc_html__( 'small', 'magic-post-thumbnail' )         => 'small',
				esc_html__( 'medium', 'magic-post-thumbnail' )        => 'medium',
				esc_html__( 'large', 'magic-post-thumbnail' )         => 'large',
				esc_html__( 'xlarge', 'magic-post-thumbnail' )        => 'xlarge',
				esc_html__( 'xxlarge', 'magic-post-thumbnail' )       => 'xxlarge',
				esc_html__( 'huge', 'magic-post-thumbnail' )          => 'huge',
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
		<label for="hseparator"><?php esc_html_e( 'Image Type', 'magic-post-thumbnail' ); ?></label>
	</th>
	<td>
		<select name="MPT_plugin_banks_settings[googleimage][imgtype]" class="form-control form-control-lg" >
			<?php
			$selected = $options['googleimage']['imgtype'];

			$imgtype = array(
				esc_html__( '-- Default --', 'magic-post-thumbnail' ) => '',
				esc_html__( 'Face', 'magic-post-thumbnail' )          => 'face',
				esc_html__( 'Photo', 'magic-post-thumbnail' )         => 'photo',
				esc_html__( 'Clipart', 'magic-post-thumbnail' )       => 'clipart',
				esc_html__( 'Lineart', 'magic-post-thumbnail' )       => 'lineart',
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
		<label for="hseparator"><?php esc_html_e( 'Safety level', 'magic-post-thumbnail' ); ?></label>
	</th>
	<td>
		<select name="MPT_plugin_banks_settings[googleimage][safe]" class="form-control form-control-lg" >
			<?php
			$selected = $options['googleimage']['safe'];

			$safe = array(
				esc_html__( 'Moderate (default)', 'magic-post-thumbnail' ) => 'moderate',
				esc_html__( 'Active', 'magic-post-thumbnail' )             => 'activate',
				esc_html__( 'Off', 'magic-post-thumbnail' )                => 'off',
			);

			foreach( $safe as $name_safe => $code_safe ) {
				$choose = ( $selected == $code_safe ) ? 'selected="selected"' : '';
				echo '<option '. $choose .' value="'. $code_safe .'">'. $name_safe .'</option>';
			}
			?>
		</select>
	</td>
</tr>
