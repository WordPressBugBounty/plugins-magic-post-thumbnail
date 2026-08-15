<?php

if ( !function_exists( 'add_filter' ) ) {
    header( 'Status: 403 Forbidden' );
    header( 'HTTP/1.1 403 Forbidden' );
    exit;
}
?>

<tr valign="top">
	<td colspan="2" class="source-logo"><img alt="Bing Images Logo" src="<?php 
echo plugin_dir_url( __FILE__ );
?>/img/bing.svg"></td>
</tr>

<tr valign="top">
	
	<th scope="row">
		<label for="hseparator"><?php 
esc_html_e( 'Choose the language', 'magic-post-thumbnail' );
?></label>
	</th>
	<td>
		<select name="MPT_plugin_banks_settings[bing][search_country]" class="form-control form-control-lg" >
			<?php 
$selected = $options['bing']['search_country'];
$country_choose = array(
    __( 'English (default)', 'magic-post-thumbnail' )     => 'en',
    __( 'Afrikaans', 'magic-post-thumbnail' )             => 'af',
    __( 'Afrikaans', 'magic-post-thumbnail' )             => 'af',
    __( 'Albanian', 'magic-post-thumbnail' )              => 'sq',
    __( 'Amharic', 'magic-post-thumbnail' )               => 'sm',
    __( 'Arabic', 'magic-post-thumbnail' )                => 'ar',
    __( 'Azerbaijani', 'magic-post-thumbnail' )           => 'az',
    __( 'Basque', 'magic-post-thumbnail' )                => 'eu',
    __( 'Belarusian', 'magic-post-thumbnail' )            => 'be',
    __( 'Bengali', 'magic-post-thumbnail' )               => 'bn',
    __( 'Bihari', 'magic-post-thumbnail' )                => 'bh',
    __( 'Bosnian', 'magic-post-thumbnail' )               => 'bs',
    __( 'Bulgarian', 'magic-post-thumbnail' )             => 'bg',
    __( 'Catalan', 'magic-post-thumbnail' )               => 'ca',
    __( 'Chinese (Simplified)', 'magic-post-thumbnail' )  => 'zh-CN',
    __( 'Chinese (Traditional)', 'magic-post-thumbnail' ) => 'zh-TW',
    __( 'Croatian', 'magic-post-thumbnail' )              => 'hr',
    __( 'Czech', 'magic-post-thumbnail' )                 => 'cs',
    __( 'Danish', 'magic-post-thumbnail' )                => 'da',
    __( 'Dutch', 'magic-post-thumbnail' )                 => 'nl',
    __( 'English', 'magic-post-thumbnail' )               => 'en',
    __( 'Esperanto', 'magic-post-thumbnail' )             => 'eo',
    __( 'Estonian', 'magic-post-thumbnail' )              => 'et',
    __( 'Faroese', 'magic-post-thumbnail' )               => 'fo',
    __( 'Finnish', 'magic-post-thumbnail' )               => 'fi',
    __( 'French', 'magic-post-thumbnail' )                => 'fr',
    __( 'Frisian', 'magic-post-thumbnail' )               => 'fy',
    __( 'Galician', 'magic-post-thumbnail' )              => 'gl',
    __( 'Georgian', 'magic-post-thumbnail' )              => 'ka',
    __( 'German', 'magic-post-thumbnail' )                => 'de',
    __( 'Greek', 'magic-post-thumbnail' )                 => 'el',
    __( 'Gujarati', 'magic-post-thumbnail' )              => 'gu',
    __( 'Hebrew', 'magic-post-thumbnail' )                => 'iw',
    __( 'Hindi', 'magic-post-thumbnail' )                 => 'hi',
    __( 'Hungarian', 'magic-post-thumbnail' )             => 'hu',
    __( 'Icelandic', 'magic-post-thumbnail' )             => 'is',
    __( 'Indonesian', 'magic-post-thumbnail' )            => 'id',
    __( 'Interlingua', 'magic-post-thumbnail' )           => 'ia',
    __( 'Irish', 'magic-post-thumbnail' )                 => 'ga',
    __( 'Italian', 'magic-post-thumbnail' )               => 'it',
    __( 'Japanese', 'magic-post-thumbnail' )              => 'ja',
    __( 'Javanese', 'magic-post-thumbnail' )              => 'jw',
    __( 'Kannada', 'magic-post-thumbnail' )               => 'kn',
    __( 'Korean', 'magic-post-thumbnail' )                => 'ko',
    __( 'Latin', 'magic-post-thumbnail' )                 => 'la',
    __( 'Latvian', 'magic-post-thumbnail' )               => 'lv',
    __( 'Lithuanian', 'magic-post-thumbnail' )            => 'lt',
    __( 'Macedonian', 'magic-post-thumbnail' )            => 'mk',
    __( 'Malay', 'magic-post-thumbnail' )                 => 'ms',
    __( 'Malayam', 'magic-post-thumbnail' )               => 'ml',
    __( 'Maltese', 'magic-post-thumbnail' )               => 'mt',
    __( 'Marathi', 'magic-post-thumbnail' )               => 'mr',
    __( 'Nepali', 'magic-post-thumbnail' )                => 'ne',
    __( 'Norwegian', 'magic-post-thumbnail' )             => 'no',
    __( 'Norwegian (Nynorsk)', 'magic-post-thumbnail' )   => 'nn',
    __( 'Occitan', 'magic-post-thumbnail' )               => 'oc',
    __( 'Persian', 'magic-post-thumbnail' )               => 'fa',
    __( 'Polish', 'magic-post-thumbnail' )                => 'pl',
    __( 'Portuguese (Brazil)', 'magic-post-thumbnail' )   => 'pt-BR',
    __( 'Portuguese (Portugal)', 'magic-post-thumbnail' ) => 'pt-PT',
    __( 'Punjabi', 'magic-post-thumbnail' )               => 'pa',
    __( 'Romanian', 'magic-post-thumbnail' )              => 'ro',
    __( 'Russian', 'magic-post-thumbnail' )               => 'ru',
    __( 'Scots Gaelic', 'magic-post-thumbnail' )          => 'gd',
    __( 'Serbian', 'magic-post-thumbnail' )               => 'sr',
    __( 'Sinhalese', 'magic-post-thumbnail' )             => 'si',
    __( 'Slovak', 'magic-post-thumbnail' )                => 'sk',
    __( 'Slovenian', 'magic-post-thumbnail' )             => 'sl',
    __( 'Spanish', 'magic-post-thumbnail' )               => 'es',
    __( 'Sudanese', 'magic-post-thumbnail' )              => 'su',
    __( 'Swahili', 'magic-post-thumbnail' )               => 'sw',
    __( 'Swedish', 'magic-post-thumbnail' )               => 'sv',
    __( 'Tagalog', 'magic-post-thumbnail' )               => 'tl',
    __( 'Tamil', 'magic-post-thumbnail' )                 => 'ta',
    __( 'Telugu', 'magic-post-thumbnail' )                => 'te',
    __( 'Thai', 'magic-post-thumbnail' )                  => 'th',
    __( 'Tigrinya', 'magic-post-thumbnail' )              => 'ti',
    __( 'Turkish', 'magic-post-thumbnail' )               => 'tr',
    __( 'Ukrainian', 'magic-post-thumbnail' )             => 'uk',
    __( 'Urdu', 'magic-post-thumbnail' )                  => 'ur',
    __( 'Uzbek', 'magic-post-thumbnail' )                 => 'uz',
    __( 'Vietnamese', 'magic-post-thumbnail' )            => 'vi',
    __( 'Welsh', 'magic-post-thumbnail' )                 => 'cy',
    __( 'Xhosa', 'magic-post-thumbnail' )                 => 'xh',
    __( 'Zulu', 'magic-post-thumbnail' )                  => 'zu',
);
//ksort( $country_choose );
foreach ( $country_choose as $name_country => $code_country ) {
    $choose = ( $selected == $code_country ? 'selected="selected"' : '' );
    echo '<option ' . $choose . ' value="' . $code_country . '">' . $name_country . '</option>';
}
?>
		</select>
	</td>
</tr>

<tr valign="top">
	<th scope="row">
		<label for="hseparator"><?php 
esc_html_e( 'Specified color predominantly', 'magic-post-thumbnail' );
?></label>
	</th>
	<td>
		<select name="MPT_plugin_banks_settings[bing][img_color]" class="form-control form-control-lg" >
			<?php 
$selected = $options['bing']['img_color'];
$img_color = array(
    __( '-- Default --', 'magic-post-thumbnail' ) => '',
    __( 'Black', 'magic-post-thumbnail' )         => 'black',
    __( 'Blue', 'magic-post-thumbnail' )          => 'blue',
    __( 'Brown', 'magic-post-thumbnail' )         => 'brown',
    __( 'Gray', 'magic-post-thumbnail' )          => 'gray',
    __( 'Green', 'magic-post-thumbnail' )         => 'green',
    __( 'Pink', 'magic-post-thumbnail' )          => 'pink',
    __( 'Purple', 'magic-post-thumbnail' )        => 'purple',
    __( 'Teal', 'magic-post-thumbnail' )          => 'teal',
    __( 'White', 'magic-post-thumbnail' )         => 'white',
    __( 'Yellow', 'magic-post-thumbnail' )        => 'yellow',
);
ksort( $img_color );
foreach ( $img_color as $name_color => $code_color ) {
    $choose = ( $selected == $code_color ? 'selected="selected"' : '' );
    echo '<option ' . $choose . ' value="' . $code_color . '">' . $name_color . '</option>';
}
?>
		</select>
		<br/>
		<p class="description">
			<i><?php 
esc_html_e( 'Experimental', 'magic-post-thumbnail' );
?></i> -
			<?php 
esc_html_e( 'Restricts results to images that contain a specified color predominantly', 'magic-post-thumbnail' );
?>
		</p>
	</td>
</tr>

<tr valign="top">
	<th scope="row">
		<label for="hseparator"><?php 
esc_html_e( 'Rights', 'magic-post-thumbnail' );
?></label>
	</th>
	<td>
		<p class="description">
			<?php 
esc_html_e( 'Choose these options can reduce relevance of results, but permit to use free-to-use images.', 'magic-post-thumbnail' );
?>
		</p>
		<select name="MPT_plugin_banks_settings[bing][rights]" class="form-control form-control-lg" >
			<?php 
$selected = $options['bing']['rights'];
$rights = array(
    __( 'Not filtered by license (default)', 'magic-post-thumbnail' )                 => '',
    __( 'Labeled for reuse with modification', 'magic-post-thumbnail' )               => 'fmc',
    __( 'Labeled for reuse', 'magic-post-thumbnail' )                                 => 'fc',
    __( 'Labeled for noncommercial reuse with modification', 'magic-post-thumbnail' ) => 'fm',
    __( 'Labeled for noncommercial reuse', 'magic-post-thumbnail' )                   => 'f',
);
foreach ( $rights as $name_rights => $code_rights ) {
    $choose = ( $selected == $code_rights ? 'selected="selected"' : '' );
    echo '<option ' . $choose . ' value="' . $code_rights . '">' . $name_rights . '</option>';
}
?>
		</select>
	</td>
</tr>

<tr valign="top">
	<th scope="row">
		<label for="hseparator"><?php 
esc_html_e( 'Image size', 'magic-post-thumbnail' );
?></label>
	</th>
	<td>
		<select name="MPT_plugin_banks_settings[bing][imgsz]" class="form-control form-control-lg" >
			<?php 
$selected = $options['bing']['imgsz'];
$imgsz = array(
    __( '-- Default --', 'magic-post-thumbnail' )      => '',
    __( 'icon', 'magic-post-thumbnail' )               => 'i',
    __( 'medium', 'magic-post-thumbnail' )             => 'm',
    __( 'large', 'magic-post-thumbnail' )              => 'l',
    __( 'More than 400x300', 'magic-post-thumbnail' )  => 'lt,islt:qsvga',
    __( 'More than 640x480', 'magic-post-thumbnail' )  => 'lt,islt:vga',
    __( 'More than 800x600', 'magic-post-thumbnail' )  => 'lt,islt:svga',
    __( 'More than 1024x768', 'magic-post-thumbnail' ) => 'lt,islt:xga',
    __( 'More than 2Mpx', 'magic-post-thumbnail' )     => 'lt,islt:2mp',
    __( 'More than 4Mpx', 'magic-post-thumbnail' )     => 'lt,islt:4mp',
    __( 'More than 6Mpx', 'magic-post-thumbnail' )     => 'lt,islt:6mp',
    __( 'More than 8Mpx', 'magic-post-thumbnail' )     => 'lt,islt:8mp',
    __( 'More than 10Mpx', 'magic-post-thumbnail' )    => 'lt,islt:10mp',
);
foreach ( $imgsz as $name_imgsz => $code_imgsz ) {
    $choose = ( $selected == $code_imgsz ? 'selected="selected"' : '' );
    echo '<option ' . $choose . ' value="' . $code_imgsz . '">' . $name_imgsz . '</option>';
}
?>
		</select>
	</td>
</tr>

<tr valign="top">
	<th scope="row">
		<label for="hseparator"><?php 
esc_html_e( 'Image format', 'magic-post-thumbnail' );
?></label>
	</th>
	<td>
		<select name="MPT_plugin_banks_settings[bing][format]" class="form-control form-control-lg" >
			<?php 
$selected = $options['bing']['format'];
$formats = array(
    __( '-- Default --', 'magic-post-thumbnail' ) => '',
    __( 'Portrait', 'magic-post-thumbnail' )      => 't',
    __( 'Square', 'magic-post-thumbnail' )        => 's',
    __( 'Landscape', 'magic-post-thumbnail' )     => 'w',
    __( 'Panoramic', 'magic-post-thumbnail' )     => 'xw',
);
// format
foreach ( $formats as $name_format => $code_format ) {
    $choose = ( $selected == $code_format ? 'selected="selected"' : '' );
    echo '<option ' . $choose . ' value="' . $code_format . '">' . $name_format . '</option>';
}
?>
		</select>
	</td>
</tr>


<tr valign="top">
	<th scope="row">
		<label for="hseparator"><?php 
esc_html_e( 'Image Type', 'magic-post-thumbnail' );
?></label>
	</th>
	<td>
		<select name="MPT_plugin_banks_settings[bing][imgtype]" class="form-control form-control-lg" >
			<?php 
$selected = $options['bing']['imgtype'];
$imgtype = array(
    __( '-- Default --', 'magic-post-thumbnail' ) => '',
    __( 'Face', 'magic-post-thumbnail' )          => 'face',
    __( 'Photo', 'magic-post-thumbnail' )         => 'photo',
    __( 'Clipart', 'magic-post-thumbnail' )       => 'clipart',
    __( 'Lineart', 'magic-post-thumbnail' )       => 'lineart',
    __( 'Animated', 'magic-post-thumbnail' )      => 'animated',
);
foreach ( $imgtype as $name_imgtype => $code_imgtype ) {
    $choose = ( $selected == $code_imgtype ? 'selected="selected"' : '' );
    echo '<option ' . $choose . ' value="' . $code_imgtype . '">' . $name_imgtype . '</option>';
}
?>
		</select>
	</td>
</tr>

<tr valign="top">
	<th scope="row">
		<label for="hseparator"><?php 
esc_html_e( 'Safety level', 'magic-post-thumbnail' );
?></label>
	</th>
	<td>
		<select name="MPT_plugin_banks_settings[bing][safe]" class="form-control form-control-lg" >
			<?php 
$selected = $options['bing']['safe'];
$safe = array(
    __( 'Moderate (default)', 'magic-post-thumbnail' ) => 'moderate',
    __( 'Active', 'magic-post-thumbnail' )             => 'activate',
    __( 'Off', 'magic-post-thumbnail' )                => 'off',
);
foreach ( $safe as $name_safe => $code_safe ) {
    $choose = ( $selected == $code_safe ? 'selected="selected"' : '' );
    echo '<option ' . $choose . ' value="' . $code_safe . '">' . $name_safe . '</option>';
}
?>
		</select>
	</td>
</tr>

<?php 
$executeElseBlock = true;
if ( $executeElseBlock ) {
    $restricted_domains = '';
    $readonly = 'readonly';
}
?>
	<tr valign="top">
		<th scope="row">
			<label for="hseparator"><?php 
esc_html_e( 'Restricted domains', 'magic-post-thumbnail' );
?></label>
			<p class="description">
				<?php 
esc_html_e( 'One domain per line', 'magic-post-thumbnail' );
?><br/>
				<?php 
esc_html_e( 'Leave empty to disable it', 'magic-post-thumbnail' );
?>
			</p>
		</th>
		<td>
      <p class="description">
              <?php 
_e( 'Add domains here if you want image <strong>only</strong> from these domains.', 'magic-post-thumbnail' );
?>
      </p>
			<textarea class="form-control form-control-solid" id="restricted_domains" name="MPT_plugin_banks_settings[bing][restricted_domains]" <?php 
echo $readonly;
?> placeholder="domain-one.com&#13;&#10;domain-two.net&#13;&#10;domain-three.info" rows="8" cols="40"><?php 
echo esc_attr( $restricted_domains );
?></textarea>
		</td>
	</tr>

<?php 
if ( $executeElseBlock ) {
    $blacklisted_domains = '';
    $readonly = 'readonly';
}
?>
	<tr valign="top">
		<th scope="row">
			<label for="hseparator"><?php 
esc_html_e( 'Blacklisted domains', 'magic-post-thumbnail' );
?></label>
			<p class="description">
				<?php 
esc_html_e( 'One domain per line', 'magic-post-thumbnail' );
?><br/>
				<?php 
esc_html_e( 'Leave empty to disable it', 'magic-post-thumbnail' );
?>
			</p>
		</th>
		<td>
	    <p class="description">
	            <?php 
esc_html_e( 'Add domains here if you want to blacklist them from results.', 'magic-post-thumbnail' );
?>
	    </p>
			<textarea class="form-control form-control-solid" id="blacklisted_domains" name="MPT_plugin_banks_settings[bing][blacklisted_domains]" <?php 
echo $readonly;
?> placeholder="domain-one.com&#13;&#10;domain-two.net&#13;&#10;domain-three.info" rows="8" cols="40"><?php 
echo esc_attr( $blacklisted_domains );
?></textarea>
		</td>
	</tr>

