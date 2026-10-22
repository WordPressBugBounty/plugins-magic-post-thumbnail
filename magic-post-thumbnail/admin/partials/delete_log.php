<?php
/**
 * Uninstall helper. Admin UI deletions go through Magic_Post_Thumbnail_Admin::MPT_handle_delete_log().
 */
if ( ! defined( 'MPT_FREEMIUS_UNINSTALL' ) ) {
	if ( ! function_exists( 'add_filter' ) ) {
		header( 'Status: 403 Forbidden' );
		header( 'HTTP/1.1 403 Forbidden' );
		exit();
	}
	return;
}

if ( ! class_exists( 'Magic_Post_Thumbnail_Helpers' ) ) {
	$helpers_file = dirname( __FILE__ ) . '/../class-magic-post-thumbnail-helpers.php';
	if ( is_readable( $helpers_file ) ) {
		require_once $helpers_file;
	}
}

if ( class_exists( 'Magic_Post_Thumbnail_Helpers' ) ) {
	$version = defined( 'MAGIC_POST_THUMBNAIL_VERSION' ) ? MAGIC_POST_THUMBNAIL_VERSION : '7.0.2';
	$helpers = new Magic_Post_Thumbnail_Helpers( 'magic-post-thumbnail', $version );
	$path    = $helpers->MPT_log_file_path( true );
	if ( $path && is_writable( $path ) ) {
		@unlink( $path );
	}
}
