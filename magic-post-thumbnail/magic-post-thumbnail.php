<?php

/**
 *
 * @link              https://magic-post-thumbnail.com/
 * @since             1.0.0
 * @package           Magic_Post_Thumbnail
 *
 * @wordpress-plugin
 * Plugin Name:       Magic Post Thumbnail
 * Plugin URI:        http://wordpress.org/plugins/magic-post-thumbnail/
 * Description:       Transform your posts with stunning images effortlessly! Magic Post Thumbnail includes many image banks to automatically get featured images for your posts.
 * Version:           5.2.10
 * Author:            Magic Post Thumbnail
 * Author URI:        https://magic-post-thumbnail.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       magic-post-thumbnail
 * Domain Path:       /languages
 *
 *
 *
 *
 */
// If this file is called directly, abort.
if ( !defined( 'WPINC' ) ) {
    die;
}
if ( function_exists( 'mpt_freemius' ) ) {
    mpt_freemius()->set_basename( false, __FILE__ );
} else {
    if ( !function_exists( 'mpt_freemius' ) ) {
        function mpt_freemius() {
            global $mpt_freemius;
            if ( !isset( $mpt_freemius ) ) {
                // Include Freemius SDK.
                require_once dirname( __FILE__ ) . '/admin/partials/freemius/start.php';
                $mpt_freemius = fs_dynamic_init( array(
                    'id'               => '2891',
                    'slug'             => 'magic-post-thumbnail',
                    'type'             => 'plugin',
                    'public_key'       => 'pk_0842b408e487a0001e564a31d3a37',
                    'is_premium'       => false,
                    'premium_suffix'   => 'Pro',
                    'has_addons'       => false,
                    'has_paid_plans'   => true,
                    'has_affiliation'  => 'selected',
                    'is_org_compliant' => true,
                    'menu'             => array(
                        'slug'        => 'magic-post-thumbnail-admin-display',
                        'first-path'  => 'admin.php?page=magic-post-thumbnail-admin-display',
                        'contact'     => false,
                        'support'     => false,
                        'affiliation' => false,
                        'account'     => false,
                        'addons'      => false,
                    ),
                    'navigation'       => 'tabs',
                    'is_live'          => true,
                ) );
            }
            return $mpt_freemius;
        }

        // Init Freemius.
        mpt_freemius();
        // Signal that SDK was initiated.
        do_action( 'mpt_freemius_loaded' );
    }
    /**
     * Currently plugin version.
     * Start at version 1.0.0 and use SemVer - https://semver.org
     * Rename this for your plugin and update it as you release new versions.
     */
    define( 'MAGIC_POST_THUMBNAIL_VERSION', '5.2.10' );
    /**
     * The code that runs during plugin activation.
     * This action is documented in includes/class-magic-post-thumbnail-activator.php
     */
    function activate_magic_post_thumbnail() {
        require_once plugin_dir_path( __FILE__ ) . 'includes/class-magic-post-thumbnail-activator.php';
        Magic_Post_Thumbnail_Activator::activate();
    }

    /**
     * The code that runs during plugin deactivation.
     * This action is documented in includes/class-magic-post-thumbnail-deactivator.php
     */
    function deactivate_magic_post_thumbnail() {
        require_once plugin_dir_path( __FILE__ ) . 'includes/class-magic-post-thumbnail-deactivator.php';
        Magic_Post_Thumbnail_Deactivator::deactivate();
    }

    register_activation_hook( __FILE__, 'activate_magic_post_thumbnail' );
    register_deactivation_hook( __FILE__, 'deactivate_magic_post_thumbnail' );
    /**
     * The core plugin class that is used to define internationalization,
     * admin-specific hooks, and public-facing site hooks.
     */
    require plugin_dir_path( __FILE__ ) . 'includes/class-magic-post-thumbnail.php';
    /**
     * Add capabilities
     */
    function MPT_add_capability() {
        $role = get_role( 'administrator' );
        // Add capabilitiy.
        $role->add_cap( 'mpt_manage', true );
    }

    /**
     * Check capabilities before launching the plugin main features
     */
    function MPT_check_capability() {
        $additional_check = false;
        // add capability for the cron
        if ( wp_doing_cron() || true == $additional_check ) {
            global $current_user;
            $current_user->add_cap( 'mpt_manage' );
        }
        if ( current_user_can( 'mpt_manage' ) ) {
            $plugin = new Magic_Post_Thumbnail();
            $plugin->run();
        }
    }

    /**
     * Begins execution of the plugin.
     *
     * Since everything within the plugin is registered via hooks,
     * then kicking off the plugin from this point in the file does
     * not affect the page life cycle.
     *
     * @since    4.0.0
     */
    function run_magic_post_thumbnail() {
        // User role & capacity
        add_action( 'init', 'MPT_add_capability', 1 );
        add_action( 'init', 'MPT_check_capability', 2 );
    }

    run_magic_post_thumbnail();
    // Fired when the plugin is uninstalled.
    mpt_freemius()->add_action( 'after_uninstall', 'mpt_freemius_uninstall_cleanup' );
    function mpt_freemius_uninstall_cleanup() {
        // Remove logs
        define( "MPT_FREEMIUS_UNINSTALL", true );
        require_once dirname( __FILE__ ) . '/admin/partials/delete_log.php';
        // Delete all plugin options
        delete_option( 'MPT_plugin_posts_settings' );
        delete_option( 'MPT_plugin_main_settings' );
        delete_option( 'MPT_plugin_banks_settings' );
        delete_option( 'MPT_plugin_interval_settings' );
        delete_option( 'MPT_plugin_cron_settings' );
        delete_option( 'MPT_plugin_proxy_settings' );
        delete_option( 'MPT_plugin_compatibility_settings' );
        delete_option( 'MPT_plugin_logs_settings' );
    }

}