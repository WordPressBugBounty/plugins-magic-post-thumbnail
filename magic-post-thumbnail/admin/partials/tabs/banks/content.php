<?php
if ( ! function_exists( 'add_filter' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}
?>
<tr valign="top">
	<td colspan="2">
		<p class="description">
			<?php esc_html_e( 'No API key. During generation, the first image already present in the post content is used if previous banks in the chain failed.', 'magic-post-thumbnail' ); ?>
		</p>
	</td>
</tr>
