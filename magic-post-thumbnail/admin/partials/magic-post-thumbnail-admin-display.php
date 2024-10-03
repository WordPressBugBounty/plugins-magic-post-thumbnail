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
        $title = esc_html__( 'Settings [Posts]', 'mpt' );
        $tab   = 'posts.php';
        break;
    case 'block':
        $title = esc_html__( 'Settings [Block]', 'mpt' );
        $tab   = 'block.php';
        break;
    case 'images':
        $title = esc_html__( 'Settings [Images]', 'mpt' );
        $tab   = 'images.php';
        break;
    case 'automatic':
        $title = esc_html__( 'Settings [Automatic]', 'mpt' );
        $tab   = 'automatic.php';
        break;
    case 'source':
        $title = esc_html__( 'Settings [Source]', 'mpt' );
        $tab   = 'source.php';
        break;
    case 'interval':
        $title = esc_html__( 'Settings [Interval]', 'mpt' );
        $tab   = 'interval.php';
        break;
    case 'cron':
        $title = esc_html__( 'Pro [Cron]', 'mpt' );
        $tab   = 'cron.php';
        break;
    case 'proxy':
        $title = esc_html__( 'Settings [Proxy]', 'mpt' );
        $tab   = 'proxy.php';
        break;
    case 'affiliation':
        $title = esc_html__( 'Miscellaneous [Affiliation]', 'mpt' );
        $tab   = 'affiliation.php';
        break;
    case 'compatibility':
        $title = esc_html__( 'Miscellaneous [Compatibility]', 'mpt' );
        $tab   = 'compatibility.php';
        break;
    case 'rights':
        $title = esc_html__( 'Miscellaneous [Rights]', 'mpt' );
        $tab   = 'rights.php';
        break;
    case 'logs':
        $title = esc_html__( 'Miscellaneous [Logs]', 'mpt' );
        $tab   = 'logs.php';
        break;
    case 'bulk-generation':
        $title = esc_html__( 'Bulk Generation', 'mpt' );
        $tab   = 'generation_choice.php';
        break;
    case 'bulk-generation-interval':
        $title = esc_html__( 'Bulk Generation with interval', 'mpt' );
        $tab   = 'generation_choice.php';
        break;
    case 'account':
        $title = esc_html__( 'Account', 'mpt' );
        $tab   = 'account.php';
        //$include_template = false;
        break;
    case 'contact':
        $title = esc_html__( 'Contact', 'mpt' );
        $tab   = 'contact.php';
        break;
    default:
        $title = esc_html__( 'Dashboard', 'mpt' );
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