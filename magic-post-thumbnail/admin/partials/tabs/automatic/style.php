<?php
if ( ! function_exists( 'add_filter' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}
$block    = isset( $block ) && is_array( $block ) ? $block : array();
$ai_style = isset( $block['ai_style'] ) ? $block['ai_style'] : '';
$presets  = $this->MPT_ai_style_presets();
$row_extra = ( 0 === (int) $blockIndex ) ? ' hidden' : '';
?>
<tr valign="top" class="section_ai_style image-location-template image-block-<?php echo (int) $blockIndex; ?> mid-add-block-img<?php echo $row_extra; ?>">
	<th scope="row">
		<label for="hseparator"><?php esc_html_e( 'Style', 'magic-post-thumbnail' ); ?></label>
	</th>
	<td>
		<select name="MPT_plugin_main_settings[image_block][<?php echo (int) $blockIndex; ?>][ai_style]" class="form-control mpt-ai-style">
			<?php foreach ( $presets as $slug => $preset ) : ?>
				<option value="<?php echo esc_attr( $slug ); ?>" <?php selected( $ai_style, $slug ); ?>><?php echo esc_html( $preset['label'] ); ?></option>
			<?php endforeach; ?>
		</select>
		<p class="description">
			<?php esc_html_e( 'Applied only when generating with an AI bank. Photo banks (Bing, Pexels…) ignore this.', 'magic-post-thumbnail' ); ?>
		</p>
	</td>
</tr>
<tr valign="top" class="section_ai_style_custom image-location-template image-block-<?php echo (int) $blockIndex; ?> mid-add-block-img<?php echo $row_extra; ?>" <?php echo ( 'custom' === $ai_style ) ? '' : 'style="display:none;"'; ?>>
	<th scope="row">
		<label for="hseparator"><?php esc_html_e( 'Custom style', 'magic-post-thumbnail' ); ?></label>
	</th>
	<td>
		<input type="text" class="form-control regular-text" name="MPT_plugin_main_settings[image_block][<?php echo (int) $blockIndex; ?>][ai_style_custom]" value="<?php echo isset( $block['ai_style_custom'] ) ? esc_attr( $block['ai_style_custom'] ) : ''; ?>" placeholder="<?php esc_attr_e( 'e.g. editorial product shot, soft daylight', 'magic-post-thumbnail' ); ?>" />
		<p class="description">
			<?php esc_html_e( 'Extra instructions appended to the AI prompt.', 'magic-post-thumbnail' ); ?>
		</p>
	</td>
</tr>
<tr valign="top" class="section_ai_no_text image-location-template image-block-<?php echo (int) $blockIndex; ?> mid-add-block-img<?php echo $row_extra; ?>">
	<th scope="row">
		<?php esc_html_e( 'No text on the image', 'magic-post-thumbnail' ); ?>
	</th>
	<td class="checkbox-list">
		<label class="checkbox">
			<input name="MPT_plugin_main_settings[image_block][<?php echo (int) $blockIndex; ?>][ai_no_text]" type="checkbox" value="true" <?php checked( ! empty( $block['ai_no_text'] ) && 'true' === (string) $block['ai_no_text'] ); ?> />
			<span></span> <?php esc_html_e( 'Ask the AI not to render captions, titles or watermarks.', 'magic-post-thumbnail' ); ?>
		</label>
	</td>
</tr>
