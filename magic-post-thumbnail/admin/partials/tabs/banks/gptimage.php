<?php
if ( ! function_exists( 'add_filter' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
		exit();
}
?>

<tr valign="top">
	<td colspan="2" class="source-logo"><img alt="GPT Image Logo" src="<?php echo plugin_dir_url( __FILE__ ); ?>/img/gptimage.svg"></td>
</tr>

<tr valign="top">
	<td colspan="2">
		<div class="update-nag">
			<?php _e('<b>It\'s required</b> to provide your own <b>API key</b>. You can register on the <a target="_blank" href="https://platform.openai.com/api-keys">OpenAI API keys</a> page. Existing DALL·E keys still work.', 'magic-post-thumbnail' ); ?>
		</div>
		<div class="update-nag red-part">
			<?php _e('<b>Caution: GPT Image can take a long time</b> to generate (often 20 to 90 seconds). OpenAI may require <b>organization verification</b> before GPT Image models work.', 'magic-post-thumbnail' ); ?>
		</div>
		<div class="update-nag">
			<?php _e('GPT Image has <b>content restrictions</b>. See the <a target="_blank" href="https://openai.com/policies/usage-policies">OpenAI usage policies</a> for details.', 'magic-post-thumbnail' ); ?>
		</div>
	</td>
</tr>

<tr valign="top">
	<th scope="row">
		<label for="hseparator"><?php esc_html_e( 'API Key', 'magic-post-thumbnail' ); ?></label>
	</th>
	<td id="password-gptimage" class="password">
		<input type="password" name="MPT_plugin_banks_settings[gptimage][apikey]" class="form-control" value="<?php echo( isset( $options['gptimage']['apikey'] ) && !empty( $options['gptimage']['apikey']) )? esc_attr( $options['gptimage']['apikey'] ) : ''; ?>" >
		<i id="togglePassword"></i>
	</td>
</tr>
<tr valign="top">
	<td colspan="2">
		<button class="btn btn-primary" id="btnGptimage" onclick="return false;">
			<?php esc_html_e( 'API testing', 'magic-post-thumbnail' ); ?>
		</button>
		<span id="resultGptimage"><img src="<?php echo plugin_dir_url( __FILE__ ); ?>../../../img/loader-mpt.gif" width="32" class="hidden"/></span>
	</td>
</tr>

<tr valign="top">
	<th scope="row">
		<label for="hseparator"><?php esc_html_e( 'Image size', 'magic-post-thumbnail' ); ?></label>
	</th>
	<td>
		<select name="MPT_plugin_banks_settings[gptimage][imgsize]" class="form-control form-control-lg" >
			<?php
			$selected = isset( $options['gptimage']['imgsize'] ) ? $options['gptimage']['imgsize'] : '1024x1024';

			$sizes = array(
				esc_html__( 'Auto (model chooses)', 'magic-post-thumbnail' ) => 'auto',
				esc_html__( '1024x1024 (square)', 'magic-post-thumbnail' )   => '1024x1024',
				esc_html__( '1536x1024 (landscape)', 'magic-post-thumbnail' ) => '1536x1024',
				esc_html__( '1024x1536 (portrait)', 'magic-post-thumbnail' ) => '1024x1536',
				esc_html__( '2048x2048 (2K square)', 'magic-post-thumbnail' ) => '2048x2048',
				esc_html__( '2048x1152 (2K landscape)', 'magic-post-thumbnail' ) => '2048x1152',
			);

			foreach( $sizes as $name_size => $code_size ) {
				$choose = ( $selected == $code_size ) ? 'selected="selected"' : '';
				echo '<option '. $choose .' value="'. esc_attr( $code_size ) .'">'. $name_size .'</option>';
			}
			?>
		</select>
		<p class="description"><?php esc_html_e( 'Official gpt-image-2 sizes. 1024 is enough for most featured images. 2K costs more and is slower. 4K is not listed (experimental and expensive for bulk).', 'magic-post-thumbnail' ); ?></p>
	</td>
</tr>

<tr valign="top">
	<th scope="row">
		<label for="hseparator"><?php esc_html_e( 'Quality', 'magic-post-thumbnail' ); ?></label>
	</th>
	<td>
		<select name="MPT_plugin_banks_settings[gptimage][quality]" class="form-control form-control-lg" >
			<?php
			$selected_q = isset( $options['gptimage']['quality'] ) ? $options['gptimage']['quality'] : 'medium';

			$qualities = array(
				esc_html__( 'Auto', 'magic-post-thumbnail' )   => 'auto',
				esc_html__( 'Low (faster, cheaper)', 'magic-post-thumbnail' )    => 'low',
				esc_html__( 'Medium', 'magic-post-thumbnail' ) => 'medium',
				esc_html__( 'High (slower, more expensive)', 'magic-post-thumbnail' )   => 'high',
			);

			foreach( $qualities as $name_q => $code_q ) {
				$choose = ( $selected_q == $code_q ) ? 'selected="selected"' : '';
				echo '<option '. $choose .' value="'. esc_attr( $code_q ) .'">'. $name_q .'</option>';
			}
			?>
		</select>
		<p class="description"><?php esc_html_e( 'Low is best for bulk drafts. Medium/High cost more and take longer.', 'magic-post-thumbnail' ); ?></p>
	</td>
</tr>

<tr valign="top">
	<th scope="row">
		<label for="hseparator"><?php esc_html_e( 'Output format', 'magic-post-thumbnail' ); ?></label>
	</th>
	<td>
		<select name="MPT_plugin_banks_settings[gptimage][output_format]" class="form-control form-control-lg" >
			<?php
			$selected_f = isset( $options['gptimage']['output_format'] ) ? $options['gptimage']['output_format'] : 'png';
			if ( empty( $selected_f ) && ! empty( $options['gptimage']['convert_jpg'] ) && 'true' === $options['gptimage']['convert_jpg'] ) {
				$selected_f = 'jpeg';
			}
			$formats = array(
				esc_html__( 'PNG', 'magic-post-thumbnail' )  => 'png',
				esc_html__( 'JPEG', 'magic-post-thumbnail' ) => 'jpeg',
				esc_html__( 'WebP', 'magic-post-thumbnail' ) => 'webp',
			);
			foreach ( $formats as $name_f => $code_f ) {
				$choose = ( $selected_f == $code_f ) ? 'selected="selected"' : '';
				echo '<option '. $choose .' value="'. esc_attr( $code_f ) .'">'. $name_f .'</option>';
			}
			?>
		</select>
		<p class="description"><?php esc_html_e( 'JPEG is smaller and usually faster. PNG keeps maximum quality. WebP is a good middle ground.', 'magic-post-thumbnail' ); ?></p>
	</td>
</tr>

<tr valign="top">
	<th scope="row">
		<label for="hseparator"><?php esc_html_e( 'Moderation', 'magic-post-thumbnail' ); ?></label>
	</th>
	<td>
		<select name="MPT_plugin_banks_settings[gptimage][moderation]" class="form-control form-control-lg" >
			<?php
			$selected_m = isset( $options['gptimage']['moderation'] ) ? $options['gptimage']['moderation'] : 'auto';
			$moderations = array(
				esc_html__( 'Auto (standard)', 'magic-post-thumbnail' ) => 'auto',
				esc_html__( 'Low (less strict)', 'magic-post-thumbnail' ) => 'low',
			);
			foreach ( $moderations as $name_m => $code_m ) {
				$choose = ( $selected_m == $code_m ) ? 'selected="selected"' : '';
				echo '<option '. $choose .' value="'. esc_attr( $code_m ) .'">'. $name_m .'</option>';
			}
			?>
		</select>
		<p class="description"><?php esc_html_e( 'Use Low if standard filtering blocks too many post titles. Still follows OpenAI usage policies.', 'magic-post-thumbnail' ); ?></p>
	</td>
</tr>
