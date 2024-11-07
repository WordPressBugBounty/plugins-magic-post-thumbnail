<?php
if ( ! function_exists( 'add_filter' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
		exit();
}
?>

<tr valign="top">
	<td colspan="2" class="source-logo"><img alt="Dall-e Logo" src="<?php echo plugin_dir_url( __FILE__ ); ?>/img/dalle.png"></td>
</tr>

<tr valign="top">
	<td colspan="2">
		<div class="update-nag">
			<?php _e('<b>It\'s required</b> to provide your own <b>api key</b>. You can register in OpenAI API website <a target="_blank" href="https://openai.com/">here</a> and get api key.', 'mpt' ); ?>
		</div>
		<div class="update-nag red-part">
			<?php _e('<b>Caution: DALL-E API can take a long time</b> to generate. About 20 seconds to get a 1024x1024 image.', 'mpt' ); ?>
		</div>
		<div class="update-nag">
			<?php _e('DALL-E API has some <b>restricted words</b> that will not generate any images. Please refer to the <a href="https://labs.openai.com/policies/content-policy">content policy</a> if you want more details.', 'mpt' ); ?>
		</div>
	</td>
</tr>

<tr valign="top">
	<th scope="row">
		<label for="hseparator"><?php esc_html_e( 'API Key', 'mpt' ); ?></label>
	</th>
	<td id="password-dalle" class="password">
		<input type="password" name="MPT_plugin_banks_settings[dallev1][apikey]" class="form-control" value="<?php echo( isset( $options['dallev1']['apikey'] ) && !empty( $options['dallev1']['apikey']) )? esc_attr( $options['dallev1']['apikey'] ) : ''; ?>" >
		<i id="togglePassword"></i>
	</td>
</tr>
<tr valign="top">
	<td colspan="2">
		<button class="btn btn-primary" id="btnDalle" onclick="return false;">
			<?php esc_html_e( 'API testing', 'mpt' ); ?>
		</button>
		<span id="resultDalle"><img src="<?php echo plugin_dir_url( __FILE__ ); ?>../../../img/loader-mpt.gif" width="32" class="hidden"/></span>
	</td>
</tr>



<tr valign="top">
	<th scope="row">
		<label for="hseparator"><?php esc_html_e( 'Image size', 'mpt' ); ?></label>
	</th>
	<td>
		<select name="MPT_plugin_banks_settings[dallev1][imgsize]" class="form-control form-control-lg" >
			<?php
			$selected = $options['dallev1']['imgsize'];

			$sizes = array(
				esc_html__( '1024x1024', 'mpt' )   => '1024x1024',
				esc_html__( '1792x1024', 'mpt' )   => '1792x1024',
				esc_html__( '1024x1792', 'mpt' )   => '1024x1792',
			);
			ksort( $filetype );

			foreach( $sizes as $name_size => $code_size ) {
				$choose=($selected == $code_size)?'selected="selected"': '';
				echo '<option '. $choose .' value="'. $code_size .'">'. $name_size .'</option>';
			}
			?>
		</select>
	</td>
</tr>

<tr valign="top">
	<th scope="row">
		<label for="hseparator"><?php esc_html_e( 'PNG to JPEG', 'mpt' ); ?></label>
	</th>
	<td class="checkbox-list">
		<label class="checkbox ">
			<input name="MPT_plugin_banks_settings[dallev1][convert_jpg]" type="checkbox" value="true" <?php echo( !empty( $options['dallev1']['convert_jpg'] ) && $options['dallev1']['convert_jpg'] == 'true' )? 'checked': ''; ?>><span></span>
			<?php esc_html_e( 'Convert to Jpeg image extension', 'mpt' ); ?>
		</label>
		<p class="description"><?php esc_html_e( 'DALL-E will create a PNG file, click this option if you want to convert the file to a Jpeg image.', 'mpt' ); ?></p>
	</td>
</tr>
