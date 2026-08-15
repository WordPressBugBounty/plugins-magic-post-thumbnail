<?php
if ( ! function_exists( 'add_filter' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}
$fallback_id  = ! empty( $options['fallback']['attachment_id'] ) ? (int) $options['fallback']['attachment_id'] : 0;
$fallback_src = $fallback_id ? wp_get_attachment_image_url( $fallback_id, 'medium' ) : '';
?>
<tr valign="top">
	<th scope="row">
		<label><?php esc_html_e( 'Default image', 'magic-post-thumbnail' ); ?></label>
	</th>
	<td>
		<input type="hidden" id="mpt-fallback-id" name="MPT_plugin_banks_settings[fallback][attachment_id]" value="<?php echo (int) $fallback_id; ?>" />
		<p>
			<img id="mpt-fallback-preview" src="<?php echo $fallback_src ? esc_url( $fallback_src ) : ''; ?>" alt="" style="max-width:220px;<?php echo $fallback_src ? '' : 'display:none;'; ?>" />
		</p>
		<button type="button" class="button" id="mpt-fallback-select"><?php esc_html_e( 'Select image', 'magic-post-thumbnail' ); ?></button>
		<button type="button" class="button" id="mpt-fallback-clear" <?php echo $fallback_id ? '' : 'style="display:none;"'; ?>><?php esc_html_e( 'Remove', 'magic-post-thumbnail' ); ?></button>
		<p class="description">
			<?php esc_html_e( 'Assigned when every other bank in the chain fails. Place this bank last.', 'magic-post-thumbnail' ); ?>
		</p>
	</td>
</tr>
