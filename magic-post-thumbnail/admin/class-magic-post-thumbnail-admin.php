<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://magic-post-thumbnail.com/
 * @since      1.0.0
 *
 * @package    Magic_Post_Thumbnail
 * @subpackage Magic_Post_Thumbnail/admin
 */
/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Magic_Post_Thumbnail
 * @subpackage Magic_Post_Thumbnail/admin
 * @author     Magic Post Thumbnail <contact@magic-post-thumbnail.com>
 */
class Magic_Post_Thumbnail_Admin {
    /**
     * The ID of this plugin.
     *
     * @since    4.0.0
     * @access   private
     * @var      string    $plugin_name    The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    4.0.0
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @since    4.0.0
     * @param      string    $plugin_name       The name of this plugin.
     * @param      string    $version    The version of this plugin.
     */
    public function __construct( $plugin_name, $version ) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
        $mpt_generation = $this->MPT_generation_features();
        // Freemius SDK Integration
        if ( function_exists( '\\Magic_Post_Thumbnail_Admin\\MPT_Freemius' ) ) {
            $this->MPT_freemius()->set_basename( true, __FILE__ );
        } else {
            if ( !function_exists( '\\Magic_Post_Thumbnail_Admin\\MPT_Freemius' ) ) {
                $this->MPT_freemius();
            }
        }
        // Adds a settings link to the plugins page
        $exploded_path = explode( '/', str_replace( WP_PLUGIN_DIR . '/', '', __DIR__ ) );
        $plugin_folder_name = reset( $exploded_path );
        add_filter( 'plugin_action_links_' . $this->mpt_freemius()->get_plugin_basename(), array(&$this, 'MPT_add_link_parameters') );
        // Crons for pro version
        $cron_options = wp_parse_args( get_option( 'MPT_plugin_cron_settings' ) );
        $compatibility = wp_parse_args( get_option( 'MPT_plugin_compatibility_settings' ), $this->MPT_default_options_compatibility_settings( TRUE ) );
        // Testing APIs function with Ajax call
        add_action( 'wp_ajax_test_apis', array(&$this, 'MPT_test_apis') );
        add_action( 'wp_ajax_nopriv_test_apis', array(&$this, 'MPT_test_apis') );
        /* Gutenberg Block */
        // Scripts for Block & Translations
        add_action( 'init', array(&$this, 'MPT_register_mpt_block') );
        add_action( 'enqueue_block_editor_assets', array(&$this, 'MPT_enqueue_style_block') );
        // Gutenberg Block : Searching images with APIs
        add_action( 'wp_ajax_block_searching_images', array(&$this, 'MPT_block_searching_images') );
        add_action( 'wp_ajax_nopriv_block_searching_images', array(&$this, 'MPT_block_searching_images') );
        // Gutenberg Block : Download images from APIs
        add_action( 'wp_ajax_block_downloading_image', array(&$this, 'MPT_block_downloading_image') );
        add_action( 'wp_ajax_nopriv_block_downloading_image', array(&$this, 'MPT_block_downloading_image') );
        // Extend timeout request for wp_remote_request() with dalle
        $options_banks = wp_parse_args( get_option( 'MPT_plugin_banks_settings' ), $this->MPT_default_options_banks_settings( TRUE ) );
        if ( isset( $options_banks['api_chosen_auto'] ) && true === in_array( 'dallev1', $options_banks['api_chosen_auto'] ) ) {
            add_filter( 'http_request_timeout', array(&$this, 'MPT_custom_http_request_timeout') );
        }
        // Show & Hide notice for asking reviews
        add_action( 'admin_notices', array(&$this, 'MPT_show_notice') );
        add_action( 'wp_ajax_mpt_hide_notice', array(&$this, 'MPT_hide_notice') );
        add_action( 'wp_ajax_mpt_remind_later', array(&$this, 'MPT_remind_later') );
        // Migration v5 to v6
        add_action( 'init', array(&$this, 'MPT_migration') );
    }

    /**
     * Trigger the automatic image generation process upon saving a post
     *
     * @since    5.0.0
     * @access   public
     */
    public function MPT_trigger_save_post() {
        $log = $this->MPT_monolog_call();
        $log->info( 'Launch Automatic plugin' );
        //$automatic_generation = new Magic_Post_Thumbnail_Generation($this->plugin_name, $this->version);
        // Add a callback function to the save_post action to handle image generation
        add_action( 'save_post', function ( $post_id ) {
            // Schedule a single event with a delay to avoid conflicts
            wp_schedule_single_event( 
                time() + 5,
                // 5 seconds apart for each block
                'mpt_generate_scheduled_image',
                array($post_id)
             );
        } );
    }

    /**
     * Generate an image for a specified block when called by the scheduled event
     *
     * @since    6.0.0
     * @access   public
     */
    public function MPT_generate_scheduled_image( $post_id ) {
        $automatic_generation = new Magic_Post_Thumbnail_Generation($this->plugin_name, $this->version);
        // Retrieve main settings from the plugin options
        $main_settings = get_option( 'MPT_plugin_main_settings' );
        $img_blocks = $main_settings['image_block'];
        // Iterate through each image block to schedule image generation
        foreach ( $img_blocks as $key_img_block => $img_block ) {
            $log = $this->MPT_monolog_call();
            $log->info( 'Generating scheduled image for post ID: ' . $post_id . ', block: ' . $key_img_block );
            // Generate the image for the specific block
            $automatic_generation->MPT_create_thumb(
                $post_id,
                0,
                1,
                1,
                0,
                false,
                null,
                null,
                $key_img_block,
                true
            );
            $log->info( 'Scheduled image generation completed for block: ' . $key_img_block );
        }
    }

    /**
     * Trigger when hook wp_insert_post is fired
     *
     * @since    5.0.3
     * @access   public
     */
    public function MPT_trigger_wp_insert_post( $post_ID ) {
    }

    public function MPT_trigger_wp_automatic( $post_data ) {
        $log = $this->MPT_monolog_call();
        $log->info( 'Launch WordPress Automatic Plugin' );
        $automatic_generation = new Magic_Post_Thumbnail_Generation($this->plugin_name, $this->version);
        $main_settings = get_option( 'MPT_plugin_main_settings' );
        $img_blocks = $main_settings['image_block'];
        foreach ( $img_blocks as $key_img_block => $img_block ) {
            $automatic_generation->MPT_create_thumb(
                $post_data['post_id'],
                '0',
                '1',
                '1',
                '0',
                false,
                null,
                null,
                $key_img_block,
                false
            );
        }
    }

    /**
     * Register all of the hooks related to the admin area functionality
     * of the plugin.
     *
     * @since    4.0.0
     * @access   private
     */
    private function MPT_generation_features() {
        $mpt_generation = new Magic_Post_Thumbnail_Generation($this->plugin_name, $this->version);
        return $mpt_generation;
    }

    /**
     * Register the stylesheets for the admin area.
     *
     * @since    4.0.0
     */
    public function enqueue_styles( $hook ) {
        // Post Editor
        if ( $hook == 'post.php' || $hook == 'post-new.php' ) {
            wp_enqueue_style(
                'mpt-post',
                plugin_dir_url( __FILE__ ) . 'css/magic-post-thumbnail-post.css',
                array(),
                $this->version,
                'all'
            );
        }
        // MPT Admin Dashboard
        if ( $hook == 'toplevel_page_magic-post-thumbnail-admin-display' || $hook == 'admin_page_magic-post-thumbnail-admin-display-pricing' ) {
            wp_enqueue_style(
                $this->plugin_name,
                plugin_dir_url( __FILE__ ) . 'css/magic-post-thumbnail-admin.css',
                array(),
                $this->version,
                'all'
            );
            wp_enqueue_style(
                'plugins-bundle',
                plugin_dir_url( __FILE__ ) . 'css/plugins.bundle.css',
                array(),
                $this->version,
                'all'
            );
            wp_enqueue_style(
                'style.bundle',
                plugin_dir_url( __FILE__ ) . 'css/style.bundle.css',
                array(),
                $this->version,
                'all'
            );
            wp_enqueue_style(
                'theme-base-light',
                plugin_dir_url( __FILE__ ) . 'css/themes/layout/header/base/light.css',
                array(),
                $this->version,
                'all'
            );
            wp_enqueue_style(
                'theme-menu-light',
                plugin_dir_url( __FILE__ ) . 'css/themes/layout/header/menu/light.css',
                array(),
                $this->version,
                'all'
            );
            wp_enqueue_style(
                'theme-brand-dark',
                plugin_dir_url( __FILE__ ) . 'css/themes/layout/brand/dark.css',
                array(),
                $this->version,
                'all'
            );
            wp_enqueue_style(
                'theme-aside-dark',
                plugin_dir_url( __FILE__ ) . 'css/themes/layout/aside/dark.css',
                array(),
                $this->version,
                'all'
            );
        }
    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    4.0.0
     */
    public function enqueue_scripts( $hook ) {
        global $pagenow;
        $post_types_default = $this->MPT_default_posts_types();
        $compatibility = wp_parse_args( get_option( 'MPT_plugin_compatibility_settings' ), $this->MPT_default_options_compatibility_settings( TRUE ) );
        $block = wp_parse_args( get_option( 'MPT_plugin_block_settings' ), $this->MPT_default_options_block_settings( TRUE ) );
        $options_banks = wp_parse_args( get_option( 'MPT_plugin_banks_settings' ), $this->MPT_default_options_banks_settings( TRUE ) );
        $options_auto = wp_parse_args( get_option( 'MPT_plugin_main_settings' ), $this->MPT_default_options_main_settings( TRUE ) );
        if ( $hook == 'toplevel_page_magic-post-thumbnail-admin-display' ) {
            wp_enqueue_script(
                'prismjs-bundle',
                plugin_dir_url( __FILE__ ) . 'js/prismjs.bundle.js',
                array('jquery'),
                $this->version,
                true
            );
            wp_enqueue_script(
                'scripts-bundle',
                plugin_dir_url( __FILE__ ) . 'js/scripts.bundle.js',
                array('jquery'),
                $this->version,
                true
            );
            wp_enqueue_script(
                'common-mpt',
                plugin_dir_url( __FILE__ ) . 'js/common.js',
                array('jquery'),
                $this->version,
                true
            );
        }
        wp_enqueue_script(
            'mpt-rating',
            plugin_dir_url( __FILE__ ) . 'js/rating-admin.js',
            array('jquery'),
            $this->version,
            false
        );
        // Bulk generation
        $module = ( isset( $_GET['module'] ) ? sanitize_text_field( $_GET['module'] ) : '' );
        if ( $hook == 'toplevel_page_magic-post-thumbnail-admin-display' && ('bulk-generation' == $module || 'source' == $module || 'automatic' == $module || 'interval' == $module) ) {
            wp_enqueue_script( 'jquery-ui', plugins_url( 'js/jquery-ui/jquery-ui.js', __FILE__ ) );
            wp_enqueue_style( 'style-jquery-ui', plugins_url( 'js/jquery-ui/jquery-ui.css', __FILE__ ) );
        }
        if ( $hook == 'toplevel_page_magic-post-thumbnail-admin-display' && 'bulk-generation' == $module ) {
            wp_enqueue_script( 'images-generation', plugins_url( 'js/generation.js', __FILE__ ), array('jquery-ui') );
        }
        if ( $hook == 'toplevel_page_magic-post-thumbnail-admin-display' && 'source' == $module ) {
            wp_enqueue_script( 'source', plugins_url( 'js/source.js', __FILE__ ), array('jquery', 'jquery-ui') );
            wp_localize_script( 'source', 'apisTestingAjax', array(
                'ajaxurl'            => admin_url( 'admin-ajax.php' ),
                'nonce'              => wp_create_nonce( 'api_testing_nonce' ),
                'successful_testing' => esc_html__( 'Your API key works!', 'mpt' ),
                'error_key'          => esc_html__( "Your API key doesn't work.", 'mpt' ),
                'error_testing'      => esc_html__( 'An error has occurred with the remote API.', 'mpt' ),
            ) );
        }
        // JavaScript Variables for nonce, admin-jax.php path and translations
        $js_vars = array(
            'wp_ajax_url'  => admin_url( 'admin-ajax.php' ),
            'translations' => array(
                'successful'       => esc_html__( 'Successful generation !!', 'mpt' ),
                'error_generation' => esc_html__( 'Error with images generation', 'mpt' ),
                'error_plugin'     => esc_html__( 'Error with the plugin', 'mpt' ),
                'search_terms'     => esc_html__( 'Search Terms', 'mpt' ),
                'img_resolution'   => esc_html__( 'Image Resolution', 'mpt' ),
                'img_size'         => esc_html__( 'Image Size', 'mpt' ),
                'img_bank'         => esc_html__( 'Image Bank', 'mpt' ),
            ),
        );
        if ( !empty( $_POST['mpt'] ) || !empty( $_REQUEST['ids_mpt_generation'] ) || !empty( $_REQUEST['cats'] ) ) {
            if ( !empty( $_REQUEST['cats'] ) ) {
                $taxo_term = get_term( $_REQUEST['cats'] );
                if ( empty( $taxo_term ) ) {
                    return false;
                }
                $cpts = get_post_types( array(
                    'public' => true,
                ), 'names' );
                $post_ids = get_posts( array(
                    'numberposts' => -1,
                    'tax_query'   => array(array(
                        'taxonomy' => $taxo_term->taxonomy,
                        'field'    => 'slug',
                        'terms'    => $taxo_term->slug,
                    )),
                    'post_type'   => array(),
                    'post_status' => array(
                        'publish',
                        'draft',
                        'pending',
                        'future',
                        'private'
                    ),
                    'fields'      => 'ids',
                ) );
                $ids = '';
                foreach ( $post_ids as $post_id ) {
                    $ids .= $post_id . ',';
                }
                $_GET['ids'] = substr_replace( $ids, '', -1 );
                $_GET['ids_mpt_generation'] = $_GET['ids'];
            }
            $ids = esc_attr( $_GET['ids_mpt_generation'] );
            $ids = array_map( 'intval', explode( ',', trim( $ids, ',' ) ) );
            $count = count( $ids );
            $ids = json_encode( $ids );
            $ajax_nonce = wp_create_nonce( 'ajax_nonce_magic_post_thumbnail' );
            $counter_image_block = 1;
            if ( isset( $options_auto['bulk_generation_interval'] ) && (int) $options_auto['bulk_generation_interval'] !== 0 ) {
                $remaining_seconds = $this->cron_scheduled();
            } else {
                $remaining_seconds = 0;
            }
            $js_vars['sendposts'] = array(
                'posts'         => $ids,
                'count'         => $count,
                'block_counter' => $counter_image_block,
                'interval'      => $remaining_seconds,
                'nonce'         => $ajax_nonce,
            );
        }
        //Include Main dashboard Js
        if ( $hook == 'toplevel_page_magic-post-thumbnail-admin-display' || ($pagenow == 'index.php' || $pagenow == 'post.php' || $pagenow == 'post-new.php') && in_array( get_post_type( get_the_ID() ), $post_types_default['choosed_post_type'] ) ) {
            wp_enqueue_script(
                $this->plugin_name,
                plugin_dir_url( __FILE__ ) . 'js/magic-post-thumbnail-admin.js',
                array('jquery'),
                $this->version,
                false
            );
        }
        if ( $hook == 'toplevel_page_magic-post-thumbnail-admin-display' && 'automatic' == $module ) {
            $image_blocks = ( isset( $options_auto['image_block'] ) ? $options_auto['image_block'] : array() );
            // Calculating the current block index based on existing blocks
            $blockIndex = count( $image_blocks ) + 1;
            // Starts after the existing blocks
            wp_localize_script( $this->plugin_name, 'automaticSettings', array(
                'blockIndex' => $blockIndex,
            ) );
        }
        $current_post_ID = json_encode( array_map( 'intval', array(get_the_ID()) ) );
        $post_nounce = wp_create_nonce( 'ajax_nonce_magic_post_thumbnail' );
        // Check if dalle is the first chosen image bank
        if ( isset( $options_banks['api_chosen_auto'] ) && true === in_array( 'dallev1', $options_banks['api_chosen_auto'] ) && 'dallev1' === reset( $options_banks['api_chosen_auto'] ) ) {
            $dalle = "true";
        } else {
            $dalle = "false";
        }
        if ( TRUE === $block['enable_manual_search'] ) {
            $block['enable_manual_search'] = "true";
        }
        /* General settings */
        $post_vars['postgeneration'] = array(
            'fifu_on'           => filter_var( $compatibility['enable_FIFU'], FILTER_VALIDATE_BOOLEAN ),
            'wp_ajax_url'       => admin_url( 'admin-ajax.php' ),
            'postID'            => $current_post_ID,
            'generateImg'       => plugin_dir_url( __FILE__ ) . 'img/generate.png',
            'strGenerate'       => esc_html__( 'Generation', 'mpt' ),
            'strNoGenerate'     => esc_html__( 'Generate Automatically', 'mpt' ),
            'strDalleGenerate'  => esc_html__( 'Dall-e v3 Generation may take 20 to 40 seconds. Please be patient', 'mpt' ),
            'strManualGenerate' => esc_html__( 'Generate Manually', 'mpt' ),
            'strNoRewrite'      => esc_html__( 'Edit your overwrite settings if you want a new image', 'mpt' ),
            'manual_search'     => $block['enable_manual_search'],
            'dalle'             => $dalle,
            'nonce'             => $post_nounce,
        );
        // Do not include this file into unselected posts types
        if ( ($pagenow == 'index.php' || $pagenow == 'post.php' || $pagenow == 'post-new.php') && in_array( get_post_type( get_the_ID() ), $post_types_default['choosed_post_type'] ) ) {
            wp_localize_script( $this->plugin_name, 'generationSpecificPostJsVars', $post_vars );
        }
        /* Translation for JS file */
        $translations_var['translations'] = array(
            'pro_version'       => esc_html__( 'Only available with the pro version.', 'mpt' ),
            'one_block'         => esc_html__( 'The free version allows one block generation. Multiple blocks are available with the Pro version.', 'mpt' ),
            'only_one_featured' => esc_html__( 'Only one featured image per post is possible', 'mpt' ),
            'delete_logs'       => esc_html__( 'Are you sure to delete all logs ?', 'mpt' ),
            'no_interval'       => esc_html__( 'No interval', 'mpt' ),
            'per_minute'        => esc_html__( 'per minute', 'mpt' ),
            'per_hour'          => esc_html__( 'per hour', 'mpt' ),
            'rate_notice'       => sprintf(
                '<p>%s</p><p><a href="%s" target="_blank" class="button button-primary">%s</a> <a href="#" id="mpt-remind-later" class="button">%s</a> <a href="#" id="mpt-already-done" class="button">%s</a></p>',
                esc_html__( 'Thank you! We\'d be thrilled if you could rate us 5 stars on WordPress.org. Your positive feedback will help others find and benefit from Magic Post Thumbnail!', 'mpt' ),
                esc_url( 'https://wordpress.org/support/plugin/magic-post-thumbnail/reviews/?filter=5#new-post' ),
                esc_html__( 'Yes, I\'ll rate it with 5-stars', 'mpt' ),
                esc_html__( 'No, maybe later', 'mpt' ),
                esc_html__( 'I\'ve already done it', 'mpt' )
            ),
        );
        wp_localize_script( $this->plugin_name, 'translationsJsVars', $translations_var );
        wp_localize_script( 'common-mpt', 'translationsJsVars', $translations_var );
        wp_localize_script( 'mpt-rating', 'translationsJsVars', $translations_var );
        wp_localize_script( 'images-generation', 'generationJsVars', $js_vars );
    }

    public function cron_scheduled() {
        // interval delay
        $options = wp_parse_args( get_option( 'MPT_plugin_main_settings' ) );
        $options_interval = $options['bulk_generation_interval'];
        // Switch interval options into seconds
        switch ( $options_interval ) {
            case '0':
                return false;
            case '1':
                $interval_seconds = 20;
                break;
            case '2':
                $interval_seconds = 30;
                break;
            case '3':
                $interval_seconds = 60;
                break;
            case '4':
                $interval_seconds = 120;
                break;
            case '5':
                $interval_seconds = 240;
                break;
            case '6':
                $interval_seconds = 600;
                break;
            case '7':
                $interval_seconds = 3600;
                break;
            default:
                return false;
        }
        return $interval_seconds;
    }

    /**
     * Show main menu item
     *
     * @since    4.0.0
     */
    public function MPT_main_settings() {
        add_menu_page(
            __( 'Magic Post Thumbnail Options', 'mpt' ),
            'Magic Post Thumbnail',
            'mpt_manage',
            'magic-post-thumbnail-admin-display',
            array(&$this, 'MPT_options'),
            'dashicons-images-alt2',
            81
        );
        add_submenu_page(
            'magic-post-thumbnail-admin-display',
            __( 'Dashboard', 'mpt' ),
            __( 'Dashboard', 'mpt' ),
            'mpt_manage',
            'magic-post-thumbnail-admin-display',
            array(&$this, 'MPT_options')
        );
        add_submenu_page(
            'magic-post-thumbnail-admin-display',
            __( 'Source', 'mpt' ),
            __( 'Source', 'mpt' ),
            'mpt_manage',
            'magic-post-thumbnail-admin-display&module=source',
            array(&$this, 'MPT_options')
        );
        add_submenu_page(
            'magic-post-thumbnail-admin-display',
            __( 'Automatic Settings', 'mpt' ),
            __( 'Settings', 'mpt' ),
            'mpt_manage',
            'magic-post-thumbnail-admin-display&module=automatic',
            array(&$this, 'MPT_options')
        );
        /* Bulk Generation link for posts & custom post type */
        $post_type_availables = get_option( 'MPT_plugin_posts_settings' );
        if ( isset( $post_type_availables['choosed_post_type'] ) ) {
            if ( false == $post_type_availables['choosed_post_type'] ) {
                $post_type_availables['choosed_post_type'] = array();
            }
        } else {
            $post_types_default = get_post_types( '', 'objects' );
            unset(
                $post_types_default['attachment'],
                $post_types_default['revision'],
                $post_types_default['nav_menu_item'],
                $post_types_default['custom_css'],
                $post_types_default['customize_changeset'],
                $post_types_default['oembed_cache'],
                $post_types_default['user_request'],
                $post_types_default['wp_block'],
                $post_types_default['wp_template'],
                $post_types_default['wp_template_part'],
                $post_types_default['wp_global_styles'],
                $post_types_default['wp_navigation']
            );
            $post_type_availables = array();
            $post_type_availables['choosed_post_type'] = array();
            foreach ( $post_types_default as $post_type ) {
                $post_type_availables['choosed_post_type'][$post_type->name] = $post_type->name;
            }
        }
        foreach ( $post_type_availables['choosed_post_type'] as $screen ) {
            add_filter( 'bulk_actions-edit-' . $screen, array(&$this, 'MPT_add_bulk_actions') );
            // Text on dropdown
            add_action(
                'handle_bulk_actions-edit-' . $screen,
                array(&$this, 'MPT_bulk_action_handler'),
                10,
                3
            );
            // Redirection
        }
        // Genererate link for Categories
        add_filter(
            'category_row_actions',
            array(&$this, 'MPT_add_bulk_action_category'),
            10,
            2
        );
        // Loop with each taxo for Genrate link
        $args_taxo = array(
            'public' => true,
        );
        $taxonomies = get_taxonomies( $args_taxo );
        $taxonomies = array_diff( $taxonomies, ['post_tag', 'post_format'] );
        foreach ( $taxonomies as $taxonomy ) {
            add_filter(
                $taxonomy . '_row_actions',
                array(&$this, 'MPT_add_bulk_action_category'),
                10,
                2
            );
        }
    }

    /**
     * Add class "current" for chosen submenu 
     *
     * @since    6.0.0
     */
    public function MPT_submenu_class( $submenu_file ) {
        if ( isset( $_GET['page'] ) && $_GET['page'] === 'magic-post-thumbnail-admin-display' ) {
            if ( isset( $_GET['module'] ) ) {
                switch ( $_GET['module'] ) {
                    case 'dashboard':
                        $submenu_file = 'magic-post-thumbnail-admin-display';
                        break;
                    case 'source':
                        $submenu_file = 'magic-post-thumbnail-admin-display&module=source';
                        break;
                    case 'automatic':
                        $submenu_file = 'magic-post-thumbnail-admin-display&module=automatic';
                        break;
                }
            }
        }
        return $submenu_file;
    }

    /**
     * Main actions
     *
     * @since    4.0.0
     */
    public function MPT_main_actions() {
        if ( !current_user_can( 'mpt_manage' ) ) {
            wp_die( esc_html__( 'You do not have sufficient permissions.', 'mpt' ) );
        }
        //register_setting('MPT-plugin-posts-settings', 'MPT_plugin_posts_settings');
        register_setting( 'MPT-plugin-block-settings', 'MPT_plugin_block_settings' );
        register_setting( 'MPT-plugin-main-settings', 'MPT_plugin_main_settings' );
        register_setting( 'MPT-plugin-banks-settings', 'MPT_plugin_banks_settings' );
        register_setting( 'MPT-plugin-interval-settings', 'MPT_plugin_interval_settings' );
        register_setting( 'MPT-plugin-cron-settings', 'MPT_plugin_cron_settings' );
        register_setting( 'MPT-plugin-rights-settings', 'MPT_plugin_rights_settings' );
        register_setting( 'MPT-plugin-proxy-settings', 'MPT_plugin_proxy_settings' );
        register_setting( 'MPT-plugin-compatibility-settings', 'MPT_plugin_compatibility_settings' );
        register_setting( 'MPT-plugin-logs-settings', 'MPT_plugin_logs_settings' );
        require_once dirname( __FILE__ ) . '/partials/download_log.php';
        require_once dirname( __FILE__ ) . '/partials/delete_log.php';
        add_filter(
            'map_meta_cap',
            array(&$this, 'mpt_map_manage_options_capability'),
            10,
            4
        );
    }

    /**
     * Change to "mpt_manage" rights for all plugin forms
     *
     * @since    5.2.11
     */
    public function mpt_map_manage_options_capability(
        $caps,
        $cap,
        $user_id,
        $args
    ) {
        // Check if the capability being checked is 'manage_options'
        if ( $cap === 'manage_options' ) {
            // Check if the form is submitting a specific option related to your plugin
            if ( isset( $_POST['option_page'] ) && in_array( $_POST['option_page'], array(
                'MPT-plugin-proxy-settings',
                'MPT-plugin-main-settings',
                'MPT-plugin-block-settings',
                'MPT-plugin-compatibility-settings',
                'MPT-plugin-cron-settings',
                'MPT-plugin-logs-settings',
                'MPT-plugin-rights-settings',
                'MPT-plugin-banks-settings'
            ) ) ) {
                // If the user has the 'mpt_manage' capability, grant access to manage the options
                if ( current_user_can( 'mpt_manage' ) ) {
                    $caps = array('mpt_manage');
                }
            }
        }
        return $caps;
    }

    /**
     * Main settings page
     *
     * @since    4.0.0
     */
    public function MPT_options() {
        if ( !current_user_can( 'mpt_manage' ) ) {
            wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'mpt' ) );
        }
        do_action( 'mpt_before_options_panel' );
        require_once dirname( __FILE__ ) . '/partials/magic-post-thumbnail-admin-display.php';
    }

    /**
     * Display submenus
     *
     * @since    4.0.0
     */
    public function MPT_submenu( $title = 'Submenu', $slug = 'dashboard', $icon = 'default.png' ) {
        $url = explode( '?', esc_url_raw( add_query_arg( array() ) ) );
        $no_query_args = $url[0];
        $current_url = remove_query_arg( 'ids_mpt_generation', add_query_arg( 'module', $slug, $this->MPT_current_url() ) );
        if ( isset( $_GET['module'] ) ) {
            $current_module = sanitize_text_field( $_GET['module'] );
        } else {
            // Default Tab
            $current_module = 'dashboard';
        }
        $item_class = 'menu-item menu-item-submenu ';
        if ( $current_module == $slug ) {
            $item_class .= 'menu-item-open menu-item-here ';
        }
        // Exception with upgrade page
        if ( 'upgrade' == $slug ) {
            $current_url = get_admin_url() . 'admin.php?page=magic-post-thumbnail-admin-display-pricing';
        }
        ?>
		<li class="<?php 
        echo $item_class;
        ?>" data-menu-toggle="hover">
		    <a href="<?php 
        echo $current_url;
        ?>" class="menu-link">
		        <img src="<?php 
        echo plugin_dir_url( __FILE__ ) . '/img/' . $icon;
        ?>" class="icon-dashboard" width="24px" height="24px" />
		        <span class="menu-text"><?php 
        echo $title;
        ?></span>
		    </a>
		</li>
	<?php 
    }

    /**
     * Get current url
     *
     * @since    4.0.0
     */
    public function MPT_current_url() {
        $requested_url = ( is_ssl() ? 'https://' : 'http://' );
        $requested_url .= $_SERVER['HTTP_HOST'];
        $requested_url .= $_SERVER['REQUEST_URI'];
        return $requested_url;
    }

    /**
     * Launch Freemius
     *
     * @since    4.0.0
     */
    public function MPT_freemius() {
        $mpt_freemius = mpt_freemius();
        return $mpt_freemius;
    }

    /**
     * Default banks name
     *
     * @since    6.0.0
     */
    public function MPT_banks_name_auto() {
        /* Banks for Automatic Bulk */
        $list_api_auto = array(
            esc_html__( 'Google Image (Scraping)', 'mpt' ) => array('google_scraping', true),
            esc_html__( 'Google Image (API)', 'mpt' )      => array('google_image', true),
            esc_html__( 'DALL·E (v3)', 'mpt' )            => array('dallev1', true),
            esc_html__( 'Openverse', 'mpt' )               => array('cc_search', true),
            esc_html__( 'Flickr', 'mpt' )                  => array('flickr', true),
            esc_html__( 'Pixabay', 'mpt' )                 => array('pixabay', true),
            esc_html__( 'Youtube', 'mpt' )                 => array('youtube', false),
            esc_html__( 'Unsplash', 'mpt' )                => array('unsplash', false),
            esc_html__( 'Pexels', 'mpt' )                  => array('pexels', false),
            esc_html__( 'Envato Elements', 'mpt' )         => array('envato', false),
            esc_html__( 'Stable Diffusion', 'mpt' )        => array('stability', false),
        );
        return $list_api_auto;
    }

    /**
     * Get default posts types & categories
     *
     * @since    5.0.0
     */
    public function MPT_default_posts_types() {
        $post_types_default['choosed_post_type'] = get_post_types();
        unset(
            $post_types_default['choosed_post_type']['attachment'],
            $post_types_default['choosed_post_type']['revision'],
            $post_types_default['choosed_post_type']['nav_menu_item'],
            $post_types_default['choosed_post_type']['custom_css'],
            $post_types_default['choosed_post_type']['customize_changeset'],
            $post_types_default['choosed_post_type']['oembed_cache'],
            $post_types_default['choosed_post_type']['user_request'],
            $post_types_default['choosed_post_type']['wp_block'],
            $post_types_default['choosed_post_type']['wp_template'],
            $post_types_default['choosed_post_type']['wp_template_part'],
            $post_types_default['choosed_post_type']['wp_global_styles'],
            $post_types_default['choosed_post_type']['wp_navigation']
        );
        $categories_default = get_terms( array(
            'taxonomy'   => 'category',
            'hide_empty' => false,
        ) );
        foreach ( $categories_default as $category ) {
            $post_types_default['choosed_categories'][$category->slug] = $category->name;
        }
        return $post_types_default;
    }

    /**
     * Default values for Image admin tabs
     *
     * @since    4.0.0
     */
    public function MPT_default_options_main_settings( $never_set = FALSE ) {
        $default_options = array(
            'image_block'              => array(
                1 => array(
                    'image_location'  => 'featured',
                    'based_on'        => 'title',
                    'translation_EN'  => '',
                    'title_selection' => 'full_title',
                    'selected_image'  => 'first_result',
                ),
            ),
            'image_filename'           => 'title',
            'rewrite_featured'         => '',
            'image_reuse'              => '',
            'image_flip'               => '',
            'image_crop'               => '',
            'bulk_generation_interval' => 0,
        );
        return $default_options;
    }

    /**
     * Default values for "Source" (Banks) admin tabs
     *
     * @since    4.0.0
     */
    public function MPT_default_options_banks_settings( $never_set = FALSE ) {
        // Migration from v4 to v5
        $options_banks = get_option( 'MPT_plugin_banks_settings' );
        if ( isset( $options_banks['api_chosen'] ) && !isset( $options_banks['api_chosen_auto'] ) ) {
            // Already chosen bank as selected
            $ar_bank_auto = array($options_banks['api_chosen']);
        } else {
            // Default banks
            $ar_bank_auto = array('google_scraping', 'cc_search');
        }
        $default_options = array(
            'api_chosen_manual' => array('openverse'),
            'api_chosen_auto'   => $ar_bank_auto,
            'google_scraping'   => array(
                'search_country'      => 'en',
                'img_color'           => '',
                'rights'              => '',
                'imgsz'               => '',
                'format'              => '',
                'imgtype'             => '',
                'safe'                => 'medium',
                'restricted_domains'  => '',
                'blacklisted_domains' => '',
            ),
            'googleimage'       => array(
                'cxid'           => '',
                'apikey'         => '',
                'search_country' => 'en',
                'img_color'      => '',
                'filetype'       => '',
                'imgsz'          => '',
                'imgtype'        => '',
                'safe'           => 'moderate',
            ),
            'dallev1'           => array(
                'apikey'  => '',
                'imgsize' => '1024x1024',
            ),
            'stability'         => array(
                'apikey'        => '',
                'model'         => 'sd3.5-large-turbo',
                'aspect_ratio'  => '16:9',
                'output_format' => 'jpeg',
            ),
            'cc_search'         => array(
                'source'       => 1,
                'rights'       => '',
                'imgtype'      => '',
                'aspect_ratio' => 'tall',
            ),
            'flickr'            => array(
                'rights'  => '',
                'imgtype' => 7,
            ),
            'pixabay'           => array(
                'username'       => '',
                'apikey'         => '',
                'imgtype'        => 'all',
                'search_country' => 'en',
                'orientation'    => 'all',
                'min_width'      => 0,
                'min_height'     => 0,
                'safesearch'     => 'false',
            ),
        );
        return $default_options;
    }

    /**
     * Default values for Interval admin tabs
     *
     * @since    4.0.0
     */
    /*
    	public function MPT_default_options_interval_settings( $never_set = FALSE ) {
    
    	    $default_options = array(
    	      // Interval
    	      'bulk_generation_interval'     => 0
    	    );
    
    	    return $default_options;
    	}*/
    /**
     * Default values for Compatibility admin tabs
     *
     * @since    4.0.0
     */
    public function MPT_default_options_compatibility_settings( $never_set = FALSE ) {
        $default_options = array(
            'enable_FIFU' => false,
        );
        return $default_options;
    }

    /**
     * Default values for Gutenberg Block
     *
     * @since    4.0.0
     */
    public function MPT_default_options_block_settings( $never_set = FALSE ) {
        $default_options = array(
            'enable_manual_search' => true,
        );
        return $default_options;
    }

    /**
     * Default values for Cron admin tabs
     *
     * @since    4.0.0
     */
    public function MPT_default_options_cron_settings( $never_set = FALSE ) {
        $default_options = array(
            'enable_cron' => 'disable',
        );
        return $default_options;
    }

    /**
     * Default values for Rights admin tabs
     *
     * @since    5.2.11
     */
    public function MPT_default_options_rights_settings( $never_set = FALSE ) {
        $default_options = array(
            'rights_editor'      => '',
            'rights_author'      => '',
            'rights_contributor' => '',
            'rights_subscriber'  => '',
        );
        return $default_options;
    }

    /**
     * Default values for Proxy admin tabs
     *
     * @since    4.0.0
     */
    public function MPT_default_options_proxy_settings( $never_set = FALSE ) {
        $default_options = array(
            'enable_proxy' => 'disable',
        );
        return $default_options;
    }

    /**
     * Default values for logs admin tabs
     *
     * @since    4.0.0
     */
    public function MPT_default_options_logs_settings( $never_set = FALSE ) {
        $default_options = array(
            'logs' => '',
        );
        return $default_options;
    }

    /**
     * Create file for logs
     *
     * @since    4.0.0
     */
    private function MPT_log_file( $check = false ) {
        $filename = ABSPATH . 'wp-content/uploads/magic-post-thumbnail/logs/';
        $files = @scandir( $filename );
        $result = '';
        if ( !empty( $files ) ) {
            foreach ( $files as $key => $value ) {
                if ( !in_array( $value, array('.', '..'), true ) ) {
                    if ( !is_dir( $value ) && strstr( $value, '.log' ) ) {
                        $result = $value;
                    }
                }
            }
        }
        if ( true == $check && empty( $result ) ) {
            return false;
        }
        if ( empty( $result ) ) {
            $result = 'mpt-' . wp_generate_password( 14, false, false ) . '.log';
        }
        return $result;
    }

    /**
     * Create file for logs
     *
     * @since    4.0.0
     */
    public function MPT_monolog_call() {
        $main_settings = get_option( 'MPT_plugin_logs_settings' );
        // Check if logs enabled
        if ( !empty( $main_settings['logs'] ) && true == $main_settings['logs'] ) {
            require_once dirname( __FILE__ ) . '/partials/monolog/vendor/autoload.php';
            $log = new Monolog\Logger('mpt_logger');
            $logfile = $this->MPT_log_file();
            // Now add some handlers
            $log->pushHandler( new Monolog\Handler\StreamHandler(ABSPATH . 'wp-content/uploads/magic-post-thumbnail/logs/' . $logfile, Monolog\Logger::DEBUG) );
            $log->pushHandler( new Monolog\Handler\FirePHPHandler() );
        } else {
            require_once dirname( __FILE__ ) . '/partials/monolog/nologs.php';
            $log = new Nolog();
        }
        return $log;
    }

    /**
     * Check if interval generation is enabled
     *
     * @since    4.0.0
     */
    public function MPT_check_interval() {
        $options = wp_parse_args( get_option( 'MPT_plugin_main_settings' ), $this->MPT_default_options_main_settings( TRUE ) );
        $value_bulk_generation_interval = ( isset( $options['bulk_generation_interval'] ) ? (int) $options['bulk_generation_interval'] : 0 );
        if ( 0 == $value_bulk_generation_interval ) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Check if interval generation is enabled
     *
     * @since    4.0.0
     */
    public function MPT_do_interval_cron( $new_ids_to_add = false ) {
        // Get processing  ids
        $interval_posts_to_generate = get_transient( 'MPT_interval_generation' );
        // Check if last generation ids is done and clear it
        // Default status to "generation done"
        if ( !empty( $interval_posts_to_generate ) ) {
            $no_more_post_to_generate = true;
        }
        foreach ( $interval_posts_to_generate as $post => $post_val ) {
            $continue_loop = false;
            // Get the first post not already generated
            if ( FALSE == $interval_posts_to_generate[$post]['processed'] ) {
                // Change default status to "generation processing"
                $no_more_post_to_generate = false;
                // Generation
                $launch_MPT = new Magic_Post_Thumbnail_Generation($this->plugin_name, $this->version);
                $MPT_return = $launch_MPT->MPT_create_thumb(
                    $interval_posts_to_generate[$post]['id'],
                    '0',
                    '0',
                    '0',
                    '0'
                );
                // Get the return status
                if ( $MPT_return == null ) {
                    // Settings
                    $main_settings = get_option( 'MPT_plugin_main_settings' );
                    // Image location
                    $image_location = ( !empty( $main_settings['image_location'] ) ? $main_settings['image_location'] : 'featured' );
                    if ( has_post_thumbnail( $interval_posts_to_generate[$post]['id'] ) && "featured" === $image_location ) {
                        $interval_posts_to_generate[$post]['processed'] = 'already-exist';
                        $continue_loop = true;
                    } else {
                        // Add the status to this speficic post : Problem
                        $interval_posts_to_generate[$post]['processed'] = 'error';
                    }
                } else {
                    // Add the status to theis speficic post : ok
                    $interval_posts_to_generate[$post]['processed'] = true;
                }
                // Limit only to the first post
                if ( TRUE !== $continue_loop ) {
                    break;
                }
            }
        }
        // Generation done and new ids to generate
        if ( TRUE == $no_more_post_to_generate && $new_ids_to_add ) {
            // Delete old posts
            delete_transient( 'MPT_interval_generation' );
            foreach ( $new_ids_to_add as $id ) {
                $new_posts_to_generate[] = array(
                    'id'        => (int) $id,
                    'processed' => false,
                );
            }
            // Add news posts
            set_transient( 'MPT_interval_generation', $new_posts_to_generate );
        } elseif ( TRUE == $no_more_post_to_generate && FALSE == $new_ids_to_add ) {
            // Generation done
            // Nothing to add/do
        } elseif ( FALSE == $no_more_post_to_generate && $new_ids_to_add ) {
            // Update with new ids (added at the end)
            foreach ( $new_ids_to_add as $id ) {
                $interval_posts_to_generate[] = array(
                    'id'        => (int) $id,
                    'processed' => false,
                );
            }
            // Allow ids into the array only once. Avoid duplicate ids (Remove the last ones)
            foreach ( $interval_posts_to_generate as &$v ) {
                if ( !isset( $temp_generate_posts[$v['id']] ) ) {
                    $temp_generate_posts[$v['id']] =& $v;
                }
            }
            $interval_posts_to_generate = array_values( $temp_generate_posts );
            // Add news posts
            set_transient( 'MPT_interval_generation', $interval_posts_to_generate );
        } else {
            // Generation not finished : Updating transient
            set_transient( 'MPT_interval_generation', $interval_posts_to_generate );
        }
    }

    /**
     * Add "Generate featured images" into bulk menu for categories
     *
     * @since    4.0.0
     */
    public function MPT_add_bulk_action_category( $actions, $tag ) {
        $actions['atp'] = '<a href="admin.php?page=magic-post-thumbnail-admin-display&module=bulk-generation&cats=' . $tag->term_id . '" class="aria-button-if-js">' . esc_html__( 'Generate featured images', 'mpt' ) . '</a>';
        return $actions;
    }

    /**
     * Redirection for bulk action
     *
     * @since    4.0.0
     */
    public function MPT_bulk_action_handler( $redirect_to, $action_name, $post_ids ) {
        if ( 'bulk_regenerate_thumbnails' === $action_name ) {
            $ids = implode( ',', array_map( 'intval', $post_ids ) );
            wp_redirect( 'admin.php?page=magic-post-thumbnail-admin-display&module=bulk-generation&ids_mpt_generation=' . $ids, '301' );
            exit;
        }
        return $redirect_to;
    }

    /**
     * Add "Generate featured images" into bulk menu for posts
     *
     * @since    4.0.0
     */
    public function MPT_add_bulk_actions( $actions ) {
        ?>
	        <script type="text/javascript">
	                jQuery(document).ready(function($){
						$('select[name^="action"] option:last-child').before('<option value="bulk_regenerate_thumbnails"><?php 
        echo esc_html__( 'Generate Images (MPT)', 'mpt' );
        ?></option>');
	                });
	        </script>
	        <?php 
        return $actions;
    }

    /**
     * Testing if APIs works into settings page
     *
     * @since    5.0.0
     */
    public function MPT_test_apis() {
        check_ajax_referer( 'api_testing_nonce', 'nonce' );
        $apiKey = sanitize_text_field( $_POST['apikey'] );
        $apiBank = sanitize_text_field( $_POST['apibank'] );
        if ( 'pixabay' === $apiBank ) {
            $apiUrl = "https://pixabay.com/api/?key=" . $apiKey . "&q=exemple";
            $response = wp_remote_get( $apiUrl );
        } elseif ( 'dalle' === $apiBank ) {
            $apiUrl = "https://api.openai.com/v1/engines";
            $response = wp_remote_request( $apiUrl, [
                'method'  => 'GET',
                'headers' => [
                    'Authorization' => 'Bearer ' . $apiKey,
                    'Content-Type'  => 'application/json',
                ],
            ] );
        } elseif ( 'youtube' === $apiBank ) {
            $apiUrl = "https://www.googleapis.com/youtube/v3/search?part=snippet&maxResults=1&type=video&key={$apiKey}";
            $response = wp_remote_get( $apiUrl );
            if ( is_wp_error( $response ) ) {
                wp_send_json_error( 'Error connecting to YouTube API.' );
            }
            $body = wp_remote_retrieve_body( $response );
            $data = json_decode( $body, true );
            if ( isset( $data['items'] ) && !empty( $data['items'] ) ) {
                wp_send_json_success( $body );
            } else {
                wp_send_json_error( 'No results found or there was an error.' );
            }
        } elseif ( 'unsplash' === $apiBank ) {
            $apiUrl = "https://api.unsplash.com/search/photos?query=test&client_id={$apiKey}";
            $response = wp_remote_get( $apiUrl );
        } elseif ( 'pexels' === $apiBank ) {
            $apiUrl = "https://api.pexels.com/v1/search?query=nature&per_page=1";
            $response = wp_remote_get( $apiUrl, array(
                'headers' => array(
                    'Authorization' => $apiKey,
                ),
            ) );
        } elseif ( 'envato' === $apiBank ) {
            $url = 'https://api.extensions.envato.com/extensions/user_info';
            $response = wp_remote_get( $url, array(
                'headers' => array(
                    "Extensions-Extension-Id" => md5( get_site_url() ),
                    "Extensions-Token"        => $apiKey,
                    "Content-Type"            => "application/json",
                ),
            ) );
        } else {
            $response = '';
        }
        if ( is_wp_error( $response ) ) {
            wp_send_json_error( 'Error connecting to the API.' );
        } else {
            wp_send_json_success( $response['body'] );
        }
    }

    /**
     * Register MPT Gutenberg Block
     *
     * @since    5.0.0
     */
    public function MPT_register_mpt_block() {
        $banks = wp_parse_args( get_option( 'MPT_plugin_banks_settings' ), $this->MPT_default_options_banks_settings( TRUE ) );
        $asset_file = (include plugin_dir_path( __FILE__ ) . 'blocks/mpt-images/build/index.asset.php');
        wp_register_script(
            'mpt-images-script',
            plugins_url( 'blocks/mpt-images/build/index.js', __FILE__ ),
            $asset_file['dependencies'],
            $asset_file['version']
        );
        register_block_type( 'mpt/mpt-images', array(
            'editor_script' => 'mpt-images-script',
        ) );
        wp_localize_script( 'mpt-images-script', 'mptAjax', array(
            'ajax_url'         => admin_url( 'admin-ajax.php' ),
            'admin_url'        => admin_url(),
            'nonce'            => wp_create_nonce( 'mpt_gutenberg_block' ),
            'choosed_banks'    => $banks['api_chosen_manual'],
            'licensing_data'   => $this->mpt_freemius()->can_use_premium_code(),
            'path_default_img' => plugins_url( '/blocks/mpt-images/img/', __FILE__ ),
        ) );
        // Locate character strings
        wp_set_script_translations( 'mpt-images-script', 'mpt', plugin_dir_path( __DIR__ ) . 'languages' );
        add_action( 'admin_enqueue_scripts', function ( $hook ) {
            // Checks whether you are on an editing page
            if ( 'post.php' === $hook || 'post-new.php' === $hook ) {
                wp_enqueue_script(
                    'manual-search',
                    plugins_url( 'js/manual_search.js', __FILE__ ),
                    array('wp-blocks', 'wp-data'),
                    $this->version,
                    true
                );
            }
        } );
        /* Translations for Gutenberg block */
        load_plugin_textdomain( 'mpt', false, plugin_dir_path( __DIR__ ) . 'languages' );
    }

    /**
     * Enqueue style for MPT Gutenberg Block
     *
     * @since    5.0.0
     */
    public function MPT_enqueue_style_block() {
        wp_enqueue_style(
            'mpt-images-editor-style',
            plugins_url( 'blocks/mpt-images/build/mpt-images.css', __FILE__ ),
            array(),
            filemtime( plugin_dir_path( __FILE__ ) . 'blocks/mpt-images/build/mpt-images.css' )
        );
    }

    /**
     * Search MPT Gutenberg Block
     *
     * @since    5.0.0
     */
    public function MPT_block_searching_images() {
        // Check the nonce
        check_ajax_referer( 'mpt_gutenberg_block', 'nonce' );
        $search_term = sanitize_text_field( $_GET['search'] );
        $bank = sanitize_text_field( strtolower( $_GET['bank'] ) );
        $index = intval( $_GET['index'] );
        $id = intval( $_GET['id'] );
        if ( FALSE === get_post_status( $id ) ) {
            wp_send_json_error( 'Error with post ID.' );
        }
        $REST_generation = new Magic_Post_Thumbnail_Generation($this->plugin_name, $this->version);
        $results_thumbs = $REST_generation->MPT_create_thumb(
            $id,
            '0',
            '1',
            '1',
            '1',
            TRUE,
            $search_term,
            $bank
        );
        if ( $results_thumbs === false ) {
            wp_send_json_error( 'Error connecting to the API.' );
        } else {
            wp_send_json_success( array(
                'results' => $results_thumbs,
                'index'   => $index,
            ) );
        }
    }

    /**
     * Downloading image for MPT Gutenberg Block
     *
     * @since    5.0.0
     */
    public function MPT_block_downloading_image() {
        // Check the nonce
        check_ajax_referer( 'mpt_gutenberg_block', 'nonce' );
        $url_image = ( isset( $_POST['url_image'] ) ? sanitize_text_field( $_POST['url_image'] ) : '' );
        $search_term = ( isset( $_POST['search_term'] ) ? sanitize_text_field( $_POST['search_term'] ) : 'image' );
        $bank = ( isset( $_POST['bank'] ) ? sanitize_text_field( $_POST['bank'] ) : '' );
        $alt = '';
        $caption = '';
        // ENVATO : Additional remote request to get image url
        if ( $bank == 'envato' ) {
            $options_banks = get_option( 'MPT_plugin_banks_settings' );
            $envato_token = ( !empty( $options_banks['envato']['envato_token'] ) ? $options_banks['envato']['envato_token'] : '' );
            $url = 'https://api.extensions.envato.com/extensions/item/' . $url_image . '/download';
            $project_ags = array(
                'project_name' => get_bloginfo( 'name' ),
            );
            $result_img_envato = wp_remote_post( add_query_arg( $project_ags, $url ), array(
                'headers' => array(
                    "Extensions-Extension-Id" => md5( get_site_url() ),
                    "Extensions-Token"        => $envato_token,
                    "Content-Type"            => "application/json",
                ),
            ) );
            $result = json_decode( $result_img_envato['body'] );
            $url_image = $result->download_urls->max2000;
        }
        $tmp = download_url( $url_image );
        $file_array = array();
        // Check for error
        if ( is_wp_error( $tmp ) ) {
            wp_send_json_error( array(
                'erreur' => $tmp->get_error_message(),
            ) );
            return;
        }
        $file_array['name'] = $search_term . '.jpg';
        // Filename
        $file_array['tmp_name'] = $tmp;
        // Check to ensure the image is valid
        $check = wp_check_filetype_and_ext( $file_array['tmp_name'], $file_array['name'] );
        if ( $check["ext"] == "" ) {
            @unlink( $file_array['tmp_name'] );
            wp_send_json_error( array(
                'erreur' => 'Invalid Image',
            ) );
            return;
        }
        // wp_handle_sideload to download the image.
        $uploaded_file = wp_handle_sideload( $file_array, array(
            'test_form' => false,
        ) );
        if ( isset( $uploaded_file['error'] ) ) {
            @unlink( $file_array['tmp_name'] );
            wp_send_json_error( array(
                'error' => $uploaded_file['error'],
            ) );
            return;
        }
        // Insert image into the media library.
        $wp_upload_dir = wp_upload_dir();
        $attachment = array(
            'guid'           => $wp_upload_dir['url'] . '/' . basename( $uploaded_file['file'] ),
            'post_mime_type' => $check['type'],
            'post_title'     => preg_replace( '/\\.[^.]+$/', '', basename( $uploaded_file['file'] ) ),
            'post_content'   => '',
            'post_status'    => 'inherit',
        );
        $attach_id = wp_insert_attachment( $attachment, $uploaded_file['file'], get_the_ID() );
        // Add alt text for image
        update_post_meta( $attach_id, '_wp_attachment_image_alt', $alt );
        // Add caption text for image
        if ( !empty( $caption ) ) {
            wp_update_post( array(
                'ID'           => $attach_id,
                'post_excerpt' => $caption,
            ) );
        }
        $attach_data = wp_generate_attachment_metadata( $attach_id, $uploaded_file['file'] );
        wp_update_attachment_metadata( $attach_id, $attach_data );
        $url_media = wp_get_attachment_url( $attach_id );
        wp_send_json_success( array(
            'url_media'     => $url_media,
            'alt_image'     => $alt,
            'caption_image' => $caption,
            'id_media'      => $attach_id,
        ) );
    }

    /**
     * Extending http timeout for wp_remote_request()
     *
     * @since    5.0.0
     */
    public function MPT_custom_http_request_timeout() {
        return 40;
        // 40 seconds
    }

    /**
     * Adds a settings link to the plugins page
     *
     * @since    2.0.5
     */
    public function MPT_add_link_parameters( $links ) {
        $settings_link = '<a href="admin.php?page=magic-post-thumbnail-admin-display">' . esc_html__( 'Settings', 'mpt' ) . '</a>';
        array_push( $links, $settings_link );
        return $links;
    }

    /**
     * Adds notice to ask for a review
     *
     * @since    5.2.6
     */
    public function MPT_show_notice() {
        $activation_date = get_option( 'MPT_plugin_activation_date' );
        $hide_notice = get_option( 'mpt_hide_notice' );
        if ( !$activation_date ) {
            update_option( 'MPT_plugin_activation_date', time() );
            return false;
        }
        $days_since_activation = (time() - $activation_date) / (60 * 60 * 24);
        if ( $days_since_activation > 7 && !$hide_notice ) {
            ?>
			<div class="notice notice-success is-dismissible " id="mpt-rating-notice">
                <p><?php 
            esc_html_e( 'Do you enjoy using Magic Post Thumbnail? If so, please consider giving it a 5-star rating on WordPress.org. Your support helps others discover our plugin and means a lot to us!', 'mpt' );
            ?></p>
                <p>
                    <a href="#" id="mpt-rate" class="button button-primary"><?php 
            esc_html_e( 'Sure!', 'mpt' );
            ?></a>
                    <a href="#" id="mpt-hide-notice" class="button"><?php 
            esc_html_e( 'No, thanks', 'mpt' );
            ?></a>
                </p>
            </div>
			<?php 
        }
    }

    /**
     * Hide notice for review
     *
     * @since    5.2.6
     */
    public function MPT_hide_notice() {
        update_option( 'mpt_hide_notice', 1 );
        wp_die();
    }

    /**
     * Show notice div later
     *
     * @since    5.2.6
     */
    public function MPT_remind_later() {
        $delay = ( isset( $_POST['delay'] ) ? intval( $_POST['delay'] ) : 30 );
        $new_time = time() + $delay * 24 * 60 * 60;
        // Délai en jours
        update_option( 'MPT_plugin_activation_date', $new_time );
        wp_die();
    }

    /**
     * Upgrade plugin : options updated
     *
     * @since    6.0.0
     */
    public function MPT_migration() {
        // Retrieve existing options
        $optionstomove = get_option( 'MPT_plugin_main_settings' );
        $options_banks = get_option( 'MPT_plugin_banks_settings' );
        // Check if old values exist (migration needed)
        if ( $optionstomove && !isset( $optionstomove['image_block'][1] ) ) {
            // Fix 6.0.1 : Add image bank in options
            if ( isset( $options_banks['api_chosen_auto'] ) ) {
                $ar_bank_auto = array($options_banks['api_chosen_auto']);
                $default_bank = reset( $ar_bank_auto[0] );
            } else {
                $default_bank = 'google_scraping';
            }
            // Move old values to the new structure
            $optionstomove['image_block'][1] = array(
                'image_location'                  => ( isset( $optionstomove['image_location'] ) ? $optionstomove['image_location'] : 'featured' ),
                'image_custom_location_placement' => ( isset( $optionstomove['image_custom_location_placement'] ) ? $optionstomove['image_custom_location_placement'] : '' ),
                'image_custom_location_position'  => ( isset( $optionstomove['image_custom_location_position'] ) ? $optionstomove['image_custom_location_position'] : '' ),
                'image_custom_location_tag'       => ( isset( $optionstomove['image_custom_location_tag'] ) ? $optionstomove['image_custom_location_tag'] : '' ),
                'image_custom_image_size'         => ( isset( $optionstomove['image_custom_image_size'] ) ? $optionstomove['image_custom_image_size'] : '' ),
                'api_chosen'                      => $default_bank,
                'based_on'                        => ( isset( $optionstomove['based_on'] ) ? $optionstomove['based_on'] : 'title' ),
                'title_selection'                 => ( isset( $optionstomove['title_selection'] ) ? $optionstomove['title_selection'] : 'full_title' ),
                'title_length'                    => ( isset( $optionstomove['title_length'] ) ? $optionstomove['title_length'] : '' ),
                'text_analyser_lang'              => ( isset( $optionstomove['text_analyser_lang'] ) ? $optionstomove['text_analyser_lang'] : '' ),
                'tags'                            => ( isset( $optionstomove['tags'] ) ? $optionstomove['tags'] : '' ),
                'categories'                      => ( isset( $optionstomove['categories'] ) ? $optionstomove['categories'] : '' ),
                'custom_field'                    => ( isset( $optionstomove['custom_field'] ) ? $optionstomove['custom_field'] : '' ),
                'custom_request'                  => ( isset( $optionstomove['custom_request'] ) ? $optionstomove['custom_request'] : '' ),
                'openai_extractor_apikey'         => ( isset( $optionstomove['openai_extractor_apikey'] ) ? $optionstomove['openai_extractor_apikey'] : '' ),
                'openai_number_of_keywords'       => ( isset( $optionstomove['openai_number_of_keywords'] ) ? $optionstomove['openai_number_of_keywords'] : '' ),
                'translation_EN'                  => ( isset( $optionstomove['translation_EN'] ) ? $optionstomove['translation_EN'] : '' ),
                'selected_image'                  => ( isset( $optionstomove['selected_image'] ) ? $optionstomove['selected_image'] : 'first_result' ),
            );
            // Remove old keys to prevent redundancy (optional)
            $keys_to_remove = array(
                'image_location',
                'image_custom_location_placement',
                'image_custom_location_position',
                'image_custom_location_tag',
                'image_custom_image_size',
                'based_on',
                'title_selection',
                'title_length',
                'text_analyser_lang',
                'tags',
                'categories',
                'custom_field',
                'custom_request',
                'openai_extractor_apikey',
                'openai_number_of_keywords',
                'translation_EN',
                'selected_image'
            );
            foreach ( $keys_to_remove as $key ) {
                unset($optionstomove[$key]);
            }
            // Save updated options
            update_option( 'MPT_plugin_main_settings', $optionstomove );
        } elseif ( isset( $optionstomove['image_block'][1] ) && $optionstomove && !isset( $optionstomove['image_block'][1]['api_chosen'] ) ) {
            // Fix 6.0.1 : Add image bank in options
            if ( isset( $options_banks['api_chosen_auto'] ) ) {
                $ar_bank_auto = array($options_banks['api_chosen_auto']);
                $default_bank = reset( $ar_bank_auto[0] );
            } else {
                $default_bank = 'google_scraping';
            }
            // Retrieve current options
            $current_options = get_option( 'MPT_plugin_main_settings', array() );
            // Update only the necessary values
            $current_options['image_block'][1] = array_merge( 
                $current_options['image_block'][1] ?? array(),
                // Initialize as an empty array if not set
                array(
                    'api_chosen' => $default_bank,
                )
             );
            // Save the updated options
            update_option( 'MPT_plugin_main_settings', $current_options );
        } else {
        }
    }

}
