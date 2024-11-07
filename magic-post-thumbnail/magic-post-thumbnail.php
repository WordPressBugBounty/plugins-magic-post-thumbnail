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
 * Description:       Add stunning images to your posts effortlessly, as featured images or within content. Magic Post Thumbnail sources them automatically from multiple image banks.
 * Version:           6.0.0
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
    define( 'MAGIC_POST_THUMBNAIL_VERSION', '6.0.0' );
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
        $options = get_option( 'MPT_plugin_rights_settings' );
        // Administrators always have the capability
        $admin_role = get_role( 'administrator' );
        if ( $admin_role ) {
            $admin_role->add_cap( 'mpt_manage', true );
        }
        // Manage other roles by adding or removing capabilities according to options
        $roles = array(
            'editor'      => 'rights_editor',
            'author'      => 'rights_author',
            'contributor' => 'rights_contributor',
            'subscriber'  => 'rights_subscriber',
        );
        foreach ( $roles as $role_name => $option_key ) {
            $role = get_role( $role_name );
            if ( $role ) {
                if ( isset( $options[$option_key] ) && $options[$option_key] === 'true' ) {
                    // Adds capacity if the option is enabled
                    $role->add_cap( 'mpt_manage', true );
                } else {
                    // Removes the capacity if the option is deactivated
                    $role->remove_cap( 'mpt_manage' );
                }
            }
        }
    }

    /**
     * Check hook wp_insert_post to fire functionalities
     *
     * @since    6.0.0
     */
    function MPT_check_hook() {
        // Checks whether the capacity has already been checked for this session
        if ( get_option( 'mpt_hook_checked' ) ) {
            // Deletes the option immediately after execution
            delete_option( 'mpt_hook_checked' );
            return;
            // Exits the function if it has already been executed
        }
        // Set capacity as verified to avoid additional calls
        update_option( 'mpt_hook_checked', true );
    }

    /**
     * Check capabilities before launching the plugin main features
     *
     * @since    5.0.0
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
        // Features with hooks
        add_action( 'init', 'MPT_check_hook', 5 );
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
        delete_option( 'MPT_plugin_rights_settings' );
        delete_option( 'MPT_plugin_proxy_settings' );
        delete_option( 'MPT_plugin_compatibility_settings' );
        delete_option( 'MPT_plugin_logs_settings' );
    }

}