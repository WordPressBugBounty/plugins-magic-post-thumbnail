<?php
/**
 * Legacy include. Log downloads are handled by Magic_Post_Thumbnail_Admin::MPT_handle_download_log().
 */
if ( ! function_exists( 'add_filter' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}
