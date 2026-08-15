<?php
if ( ! function_exists( 'add_filter' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
		exit();
}

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://magic-post-thumbnail.com/
 * @since      4.0.0
 *
 * @package    Magic_Post_Thumbnail
 * @subpackage Magic_Post_Thumbnail/admin/partials
 */

$include_template = true;

$module = ( isset( $_GET['module'] ) ) ? sanitize_text_field( $_GET['module'] ) : 'default';

switch ( $module ) {
    case 'posts':
        $title = esc_html__( 'Settings [Posts]', 'magic-post-thumbnail' );
        $tab   = 'posts.php';
        break;
    case 'block':
        $title = esc_html__( 'Settings [Block]', 'magic-post-thumbnail' );
        $tab   = 'block.php';
        break;
    case 'images':
        $title = esc_html__( 'Settings [Images]', 'magic-post-thumbnail' );
        $tab   = 'images.php';
        break;
    case 'automatic':
        $title = esc_html__( 'Settings [Automatic]', 'magic-post-thumbnail' );
        $tab   = 'automatic.php';
        break;
    case 'source':
        $title = esc_html__( 'Settings [Source]', 'magic-post-thumbnail' );
        $tab   = 'source.php';
        break;
    case 'interval':
        $title = esc_html__( 'Settings [Interval]', 'magic-post-thumbnail' );
        $tab   = 'interval.php';
        break;
    case 'cron':
        $title = esc_html__( 'Pro [Cron]', 'magic-post-thumbnail' );
        $tab   = 'cron.php';
        break;
    case 'proxy':
        $title = esc_html__( 'Settings [Proxy]', 'magic-post-thumbnail' );
        $tab   = 'proxy.php';
        break;
    case 'affiliation':
        $title = esc_html__( 'Miscellaneous [Affiliation]', 'magic-post-thumbnail' );
        $tab   = 'affiliation.php';
        break;
    case 'compatibility':
        $title = esc_html__( 'Miscellaneous [Compatibility]', 'magic-post-thumbnail' );
        $tab   = 'compatibility.php';
        break;
    case 'rights':
        $title = esc_html__( 'Miscellaneous [Rights]', 'magic-post-thumbnail' );
        $tab   = 'rights.php';
        break;
    case 'logs':
        $title = esc_html__( 'Miscellaneous [Logs]', 'magic-post-thumbnail' );
        $tab   = 'logs.php';
        break;
    case 'bulk-generation':
        $title = esc_html__( 'Bulk Generation', 'magic-post-thumbnail' );
        $tab   = 'generation_choice.php';
        break;
    case 'bulk-generation-interval':
        $title = esc_html__( 'Bulk Generation with interval', 'magic-post-thumbnail' );
        $tab   = 'generation_choice.php';
        break;
    case 'account':
        $title = esc_html__( 'Account', 'magic-post-thumbnail' );
        $tab   = 'account.php';
        //$include_template = false;
        break;
    case 'contact':
        $title = esc_html__( 'Contact', 'magic-post-thumbnail' );
        $tab   = 'contact.php';
        break;
    default:
        $title = esc_html__( 'Dashboard', 'magic-post-thumbnail' );
        $tab   = 'dashboard.php';
}


//Bug with account page, remove header & footer template
if( TRUE === $include_template ) {
    include_once( 'header.php' );
    include_once( 'tabs/' . $tab );
    include_once( 'footer.php' );
} else {
    include_once( 'tabs/' . $tab );
}

?>