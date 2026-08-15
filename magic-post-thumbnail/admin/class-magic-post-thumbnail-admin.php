<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @package    Magic_Post_Thumbnail
 * @subpackage Magic_Post_Thumbnail/admin
 */
class Magic_Post_Thumbnail_Admin extends Magic_Post_Thumbnail_Helpers {
    protected static $instance = null;

    protected static $admin_automation_registered = false;

    public function __construct( $plugin_name, $version ) {
        parent::__construct( $plugin_name, $version );
    }

    public static function MPT_instance( $plugin_name, $version ) {
        if ( null === self::$instance ) {
            self::$instance = new self($plugin_name, $version);
        }
        return self::$instance;
    }

    public static function MPT_register_automation( $plugin_name, $version ) {
        $admin = self::MPT_instance( $plugin_name, $version );
        $admin->MPT_register_automation_hooks();
    }

    public function MPT_register_automation_hooks() {
        if ( !empty( self::$admin_automation_registered ) ) {
            return;
        }
        self::$admin_automation_registered = true;
        $cron_options = wp_parse_args( get_option( 'MPT_plugin_cron_settings' ), $this->MPT_default_options_cron_settings( true ) );
        $compatibility = wp_parse_args( get_option( 'MPT_plugin_compatibility_settings' ), $this->MPT_default_options_compatibility_settings( true ) );
        add_action( 'wp_ajax_test_apis', array($this, 'MPT_test_apis') );
        add_action( 'wp_ajax_mpt_preview_search', array($this, 'MPT_preview_search') );
        add_action( 'wp_ajax_mpt_generate_from_list', array($this, 'MPT_generate_from_list') );
        add_action( 'wp_ajax_mpt_set_featured_from_list', array($this, 'MPT_set_featured_from_list') );
        add_action( 'wp_ajax_mpt_dashboard_test_generate', array($this, 'MPT_dashboard_test_generate') );
        add_action( 'init', array($this, 'MPT_register_mpt_block') );
        add_action( 'enqueue_block_editor_assets', array($this, 'MPT_enqueue_style_block') );
        add_action( 'wp_ajax_block_searching_images', array($this, 'MPT_block_searching_images') );
        add_action( 'wp_ajax_block_downloading_image', array($this, 'MPT_block_downloading_image') );
        add_action( 'wp_ajax_mpt_hide_notice', array($this, 'MPT_hide_notice') );
        add_action( 'wp_ajax_mpt_remind_later', array($this, 'MPT_remind_later') );
        add_action( 'admin_post_downloadlog', array($this, 'MPT_handle_download_log') );
        add_action( 'admin_post_deletelog', array($this, 'MPT_handle_delete_log') );
        add_action( 'admin_init', array($this, 'MPT_maybe_handle_log_actions') );
        add_action( 'admin_notices', array($this, 'MPT_show_notice') );
        add_action( 'init', array($this, 'MPT_migration') );
        add_filter( 'cron_schedules', array($this, 'MPT_cron_schedules') );
    }

    public function MPT_register_ui_hooks() {
        add_filter( 'plugin_action_links_' . $this->mpt_freemius()->get_plugin_basename(), array($this, 'MPT_add_link_parameters') );
    }

    public function MPT_cron_schedules( $schedules ) {
        $options = wp_parse_args( get_option( 'MPT_plugin_cron_settings' ), $this->MPT_default_options_cron_settings( true ) );
        $default_timestamp_cron = 600;
        if ( isset( $options['cron_word'] ) ) {
            if ( 'minutes' === $options['cron_word'] ) {
                $interval = ( isset( $options['cron_number_minutes'] ) ? $options['cron_number_minutes'] : $default_timestamp_cron );
            } elseif ( 'hours' === $options['cron_word'] ) {
                $interval = ( isset( $options['cron_number_hours'] ) ? $options['cron_number_hours'] : $default_timestamp_cron );
            } elseif ( 'days' === $options['cron_word'] ) {
                $interval = ( isset( $options['cron_number_days'] ) ? $options['cron_number_days'] : $default_timestamp_cron );
            } else {
                $interval = $default_timestamp_cron;
            }
        } elseif ( isset( $options['cron_interval'] ) ) {
            $old_interval = absint( $options['cron_interval'] );
            $interval = ( $default_timestamp_cron > $old_interval ? $default_timestamp_cron : $old_interval );
        } else {
            $interval = $default_timestamp_cron;
        }
        $schedules['mptcronpro'] = array(
            'interval' => (int) $interval,
            'display'  => esc_html__( 'Magic Post Thumbnail Pro - Cron', 'magic-post-thumbnail' ),
        );
        return $schedules;
    }

    public function MPT_trigger_save_post() {
        $log = $this->MPT_monolog_call();
        $source = ( 'feedwordpress_update' === current_filter() ? 'feedwordpress' : 'wpematico' );
        $log->info( 'Launch Automatic plugin', array(
            'source' => $source,
        ) );
        add_action( 'save_post', function ( $post_id ) use($source) {
            wp_schedule_single_event( time() + 5, 'mpt_generate_scheduled_image', array($post_id, $source) );
        } );
    }

    public function MPT_generate_scheduled_image( $post_id, $source = 'wpematico' ) {
        $source = ( is_string( $source ) ? $source : 'wpematico' );
        $automatic_generation = Magic_Post_Thumbnail_Generation::MPT_instance( $this->plugin_name, $this->version );
        $main_settings = get_option( 'MPT_plugin_main_settings' );
        $img_blocks = ( isset( $main_settings['image_block'] ) ? $main_settings['image_block'] : array() );
        foreach ( $img_blocks as $key_img_block => $img_block ) {
            $log = $this->MPT_monolog_call();
            $log->info( 'Generating scheduled image for post ID: ' . $post_id . ', block: ' . $key_img_block, array(
                'source' => $source,
            ) );
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
                true,
                null,
                false,
                $source
            );
        }
    }

    public function MPT_trigger_wp_automatic( $post_data ) {
        $log = $this->MPT_monolog_call();
        $log->info( 'Launch WordPress Automatic Plugin' );
        $automatic_generation = Magic_Post_Thumbnail_Generation::MPT_instance( $this->plugin_name, $this->version );
        $main_settings = get_option( 'MPT_plugin_main_settings' );
        $img_blocks = ( isset( $main_settings['image_block'] ) ? $main_settings['image_block'] : array() );
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
                false,
                null,
                false,
                'wp_automatic'
            );
        }
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
        if ( $hook == 'toplevel_page_magic-post-thumbnail-admin-display' && ('bulk-generation' == $module || 'source' == $module || 'automatic' == $module || 'interval' == $module || 'block' == $module) ) {
            wp_enqueue_script( 'jquery-ui', plugins_url( 'js/jquery-ui/jquery-ui.js', __FILE__ ) );
            wp_enqueue_style( 'style-jquery-ui', plugins_url( 'js/jquery-ui/jquery-ui.css', __FILE__ ) );
        }
        if ( $hook == 'toplevel_page_magic-post-thumbnail-admin-display' && 'bulk-generation' == $module ) {
            wp_enqueue_script( 'images-generation', plugins_url( 'js/generation.js', __FILE__ ), array('jquery-ui') );
        }
        if ( $hook == 'toplevel_page_magic-post-thumbnail-admin-display' && ('source' == $module || 'automatic' == $module) ) {
            wp_enqueue_media();
        }
        if ( $hook == 'toplevel_page_magic-post-thumbnail-admin-display' && 'source' == $module ) {
            wp_enqueue_script(
                'source',
                plugins_url( 'js/source.js', __FILE__ ),
                array('jquery', 'jquery-ui'),
                $this->version
            );
            $preview_banks = wp_parse_args( get_option( 'MPT_plugin_banks_settings' ), $this->MPT_default_options_banks_settings( true ) );
            $preview_chain = $this->MPT_resolve_bank_chain( array(
                'api_chosen' => 'default',
            ), $preview_banks );
            wp_localize_script( 'source', 'apisTestingAjax', array(
                'ajaxurl'             => admin_url( 'admin-ajax.php' ),
                'nonce'               => wp_create_nonce( 'api_testing_nonce' ),
                'preview_nonce'       => wp_create_nonce( 'mpt_preview_search' ),
                'successful_testing'  => esc_html__( 'Your API key works!', 'magic-post-thumbnail' ),
                'error_key'           => esc_html__( "Your API key doesn't work.", 'magic-post-thumbnail' ),
                'error_testing'       => esc_html__( 'An error has occurred with the remote API.', 'magic-post-thumbnail' ),
                'error_billing'       => esc_html__( 'Your API key is recognized, but xAI rejected the request (no credits or billing). Add prepaid credits in the xAI console.', 'magic-post-thumbnail' ),
                'error_unexpected'    => esc_html__( 'Unexpected error:', 'magic-post-thumbnail' ),
                'preview_loading'     => esc_html__( 'Loading preview…', 'magic-post-thumbnail' ),
                'preview_error'       => esc_html__( 'Could not load a preview for this bank.', 'magic-post-thumbnail' ),
                'preview_empty'       => esc_html__( 'No images returned for this keyword.', 'magic-post-thumbnail' ),
                'preview_ai_loading'  => esc_html__( 'Generating with AI, this can take up to 40 seconds…', 'magic-post-thumbnail' ),
                'preview_ai_confirm'  => esc_html__( 'This preview will call a generative AI service and consume tokens. Continue?', 'magic-post-thumbnail' ),
                'preview_ai_warning'  => esc_html__( 'This bank (%s) is a generative AI service. Preview will consume tokens.', 'magic-post-thumbnail' ),
                'preview_saved_chain' => implode( ',', array_values( $preview_chain ) ),
                'badge_labels'        => array(
                    'ready'    => esc_html__( 'Ready', 'magic-post-thumbnail' ),
                    'missing'  => esc_html__( 'No key', 'magic-post-thumbnail' ),
                    'untested' => esc_html__( 'Untested', 'magic-post-thumbnail' ),
                    'valid'    => esc_html__( 'Key OK', 'magic-post-thumbnail' ),
                    'invalid'  => esc_html__( 'Key failed', 'magic-post-thumbnail' ),
                ),
            ) );
        }
        if ( $hook == 'toplevel_page_magic-post-thumbnail-admin-display' && ('' === $module || 'dashboard' === $module) ) {
            wp_enqueue_script(
                'mpt-dashboard',
                plugins_url( 'js/dashboard.js', __FILE__ ),
                array('jquery'),
                $this->version,
                true
            );
            wp_localize_script( 'mpt-dashboard', 'mptDashboard', array(
                'ajaxurl'    => admin_url( 'admin-ajax.php' ),
                'nonce'      => wp_create_nonce( 'mpt_dashboard_test' ),
                'generating' => esc_html__( 'Generating…', 'magic-post-thumbnail' ),
                'error'      => esc_html__( 'Generation failed', 'magic-post-thumbnail' ),
                'aiConfirm'  => esc_html__( 'This test will call a generative AI service and consume tokens. Continue?', 'magic-post-thumbnail' ),
            ) );
        }
        if ( 'edit.php' === $pagenow && current_user_can( 'mpt_manage' ) ) {
            wp_enqueue_media();
            wp_enqueue_script(
                'mpt-posts-list',
                plugins_url( 'js/posts-list.js', __FILE__ ),
                array('jquery'),
                $this->version,
                true
            );
            wp_localize_script( 'mpt-posts-list', 'mptPostsList', array(
                'ajaxurl'    => admin_url( 'admin-ajax.php' ),
                'nonce'      => wp_create_nonce( 'mpt_generate_from_list' ),
                'generating' => esc_html__( 'Generating…', 'magic-post-thumbnail' ),
                'error'      => esc_html__( 'Generation failed', 'magic-post-thumbnail' ),
            ) );
            wp_enqueue_style(
                'mpt-posts-list',
                plugin_dir_url( __FILE__ ) . 'css/posts-list.css',
                array(),
                $this->version,
                'all'
            );
        }
        // JavaScript Variables for nonce, admin-jax.php path and translations
        $js_vars = array(
            'wp_ajax_url'  => admin_url( 'admin-ajax.php' ),
            'translations' => array(
                'successful'       => esc_html__( 'Successful generation !!', 'magic-post-thumbnail' ),
                'error_generation' => esc_html__( 'Error with images generation', 'magic-post-thumbnail' ),
                'error_plugin'     => esc_html__( 'Error with the plugin', 'magic-post-thumbnail' ),
                'search_terms'     => esc_html__( 'Search Terms', 'magic-post-thumbnail' ),
                'img_resolution'   => esc_html__( 'Image Resolution', 'magic-post-thumbnail' ),
                'img_size'         => esc_html__( 'Image Size', 'magic-post-thumbnail' ),
                'img_bank'         => esc_html__( 'Image Bank', 'magic-post-thumbnail' ),
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
                array(
                    'strategy' => 'defer',
                )
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
        $slow_ai = $this->MPT_ai_bank_slugs();
        $first_block = array(
            'api_chosen' => 'default',
        );
        if ( isset( $options_auto['image_block'] ) && is_array( $options_auto['image_block'] ) ) {
            $first_block = reset( $options_auto['image_block'] );
            if ( !is_array( $first_block ) ) {
                $first_block = array(
                    'api_chosen' => 'default',
                );
            }
        }
        $chain = $this->MPT_resolve_bank_chain( $first_block, $options_banks );
        $first_auto = reset( $chain );
        $dalle = ( in_array( $first_auto, $slow_ai, true ) ? 'true' : 'false' );
        if ( TRUE === $block['enable_manual_search'] ) {
            $block['enable_manual_search'] = "true";
        }
        /* General settings */
        $post_vars['postgeneration'] = array(
            'fifu_on'           => filter_var( $compatibility['enable_FIFU'], FILTER_VALIDATE_BOOLEAN ),
            'wp_ajax_url'       => admin_url( 'admin-ajax.php' ),
            'postID'            => $current_post_ID,
            'generateImg'       => plugin_dir_url( __FILE__ ) . 'img/generate.png',
            'strGenerate'       => esc_html__( 'Generation', 'magic-post-thumbnail' ),
            'strNoGenerate'     => esc_html__( 'Generate Automatically', 'magic-post-thumbnail' ),
            'strDalleGenerate'  => esc_html__( 'AI image generation may take 10 to 60 seconds. Please be patient', 'magic-post-thumbnail' ),
            'strManualGenerate' => esc_html__( 'Generate Manually', 'magic-post-thumbnail' ),
            'strNoRewrite'      => esc_html__( 'Edit your overwrite settings if you want a new image', 'magic-post-thumbnail' ),
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
            'pro_version'       => esc_html__( 'Only available with the pro version.', 'magic-post-thumbnail' ),
            'one_block'         => esc_html__( 'The free version allows one block generation. Multiple blocks are available with the Pro version.', 'magic-post-thumbnail' ),
            'only_one_featured' => esc_html__( 'Only one featured image per post is possible', 'magic-post-thumbnail' ),
            'delete_logs'       => esc_html__( 'Are you sure to delete all logs ?', 'magic-post-thumbnail' ),
            'no_interval'       => esc_html__( 'No interval', 'magic-post-thumbnail' ),
            'per_minute'        => esc_html__( 'per minute', 'magic-post-thumbnail' ),
            'per_hour'          => esc_html__( 'per hour', 'magic-post-thumbnail' ),
            'unsaved_title'     => esc_html__( 'Unsaved changes', 'magic-post-thumbnail' ),
            'unsaved_text'      => esc_html__( 'You have unsaved settings. Save them before leaving this page?', 'magic-post-thumbnail' ),
            'unsaved_save'      => esc_html__( 'Save changes', 'magic-post-thumbnail' ),
            'unsaved_leave'     => esc_html__( 'Leave without saving', 'magic-post-thumbnail' ),
            'unsaved_stay'      => esc_html__( 'Cancel', 'magic-post-thumbnail' ),
            'rate_notice'       => sprintf(
                '<p>%s</p><p><a href="%s" target="_blank" class="button button-primary">%s</a> <a href="#" id="mpt-remind-later" class="button">%s</a> <a href="#" id="mpt-already-done" class="button">%s</a></p>',
                esc_html__( 'Thank you! We\'d be thrilled if you could rate us 5 stars on WordPress.org. Your positive feedback will help others find and benefit from Magic Post Thumbnail!', 'magic-post-thumbnail' ),
                esc_url( 'https://wordpress.org/support/plugin/magic-post-thumbnail/reviews/?filter=5#new-post' ),
                esc_html__( 'Yes, I\'ll rate it with 5-stars', 'magic-post-thumbnail' ),
                esc_html__( 'No, maybe later', 'magic-post-thumbnail' ),
                esc_html__( 'I\'ve already done it', 'magic-post-thumbnail' )
            ),
        );
        $translations_var['nonce'] = wp_create_nonce( 'mpt_notice' );
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
            __( 'Magic Post Thumbnail Options', 'magic-post-thumbnail' ),
            'Magic Post Thumbnail',
            'mpt_manage',
            'magic-post-thumbnail-admin-display',
            array(&$this, 'MPT_options'),
            'dashicons-images-alt2',
            81
        );
        add_submenu_page(
            'magic-post-thumbnail-admin-display',
            __( 'Dashboard', 'magic-post-thumbnail' ),
            __( 'Dashboard', 'magic-post-thumbnail' ),
            'mpt_manage',
            'magic-post-thumbnail-admin-display',
            array(&$this, 'MPT_options')
        );
        add_submenu_page(
            'magic-post-thumbnail-admin-display',
            __( 'Source', 'magic-post-thumbnail' ),
            __( 'Source', 'magic-post-thumbnail' ),
            'mpt_manage',
            'magic-post-thumbnail-admin-display&module=source',
            array(&$this, 'MPT_options')
        );
        add_submenu_page(
            'magic-post-thumbnail-admin-display',
            __( 'Automatic Settings', 'magic-post-thumbnail' ),
            __( 'Settings', 'magic-post-thumbnail' ),
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
            add_filter( 'manage_' . $screen . '_posts_columns', array($this, 'MPT_posts_list_column') );
            add_action(
                'manage_' . $screen . '_posts_custom_column',
                array($this, 'MPT_posts_list_column_content'),
                10,
                2
            );
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
        if ( !current_user_can( 'mpt_manage' ) && !current_user_can( 'manage_options' ) ) {
            return;
        }
        $setting_groups = array(
            'MPT-plugin-block-settings',
            'MPT-plugin-main-settings',
            'MPT-plugin-banks-settings',
            'MPT-plugin-interval-settings',
            'MPT-plugin-cron-settings',
            'MPT-plugin-rights-settings',
            'MPT-plugin-proxy-settings',
            'MPT-plugin-compatibility-settings',
            'MPT-plugin-logs-settings'
        );
        foreach ( $setting_groups as $group ) {
            add_filter( 'option_page_capability_' . $group, array($this, 'MPT_option_page_capability') );
        }
        register_setting( 'MPT-plugin-block-settings', 'MPT_plugin_block_settings', array(
            'sanitize_callback' => array($this, 'MPT_sanitize_block_settings'),
        ) );
        register_setting( 'MPT-plugin-main-settings', 'MPT_plugin_main_settings', array(
            'sanitize_callback' => array($this, 'MPT_sanitize_main_settings'),
        ) );
        register_setting( 'MPT-plugin-banks-settings', 'MPT_plugin_banks_settings', array(
            'sanitize_callback' => array($this, 'MPT_sanitize_banks_settings'),
        ) );
        register_setting( 'MPT-plugin-interval-settings', 'MPT_plugin_interval_settings', array(
            'sanitize_callback' => array($this, 'MPT_sanitize_text_settings'),
        ) );
        register_setting( 'MPT-plugin-cron-settings', 'MPT_plugin_cron_settings', array(
            'sanitize_callback' => array($this, 'MPT_sanitize_cron_settings'),
        ) );
        register_setting( 'MPT-plugin-rights-settings', 'MPT_plugin_rights_settings', array(
            'sanitize_callback' => array($this, 'MPT_sanitize_rights_settings'),
        ) );
        register_setting( 'MPT-plugin-proxy-settings', 'MPT_plugin_proxy_settings', array(
            'sanitize_callback' => array($this, 'MPT_sanitize_proxy_settings'),
        ) );
        register_setting( 'MPT-plugin-compatibility-settings', 'MPT_plugin_compatibility_settings', array(
            'sanitize_callback' => array($this, 'MPT_sanitize_compatibility_settings'),
        ) );
        register_setting( 'MPT-plugin-logs-settings', 'MPT_plugin_logs_settings', array(
            'sanitize_callback' => array($this, 'MPT_sanitize_logs_settings'),
        ) );
    }

    /**
     * Allow users with plugin access to save MPT settings.
     *
     * @return string
     */
    public function MPT_option_page_capability() {
        return 'mpt_manage';
    }

    /**
     * Main settings page
     *
     * @since    4.0.0
     */
    public function MPT_options() {
        $module = ( isset( $_GET['module'] ) ? sanitize_text_field( wp_unslash( $_GET['module'] ) ) : 'dashboard' );
        if ( !$this->MPT_user_can_access_module( $module ) ) {
            wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'magic-post-thumbnail' ) );
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
        if ( !$this->MPT_user_can_access_module( $slug ) ) {
            return;
        }
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
     * Add "Generate featured images" into bulk menu for categories
     *
     * @since    4.0.0
     */
    public function MPT_add_bulk_action_category( $actions, $tag ) {
        $actions['atp'] = '<a href="admin.php?page=magic-post-thumbnail-admin-display&module=bulk-generation&cats=' . $tag->term_id . '" class="aria-button-if-js">' . esc_html__( 'Generate featured images', 'magic-post-thumbnail' ) . '</a>';
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
        echo esc_html__( 'Generate Images (MPT)', 'magic-post-thumbnail' );
        ?></option>');
	                });
	        </script>
	        <?php 
        return $actions;
    }

    /**
     * Featured-image column on post lists.
     *
     * @since 7.0.0
     * @param array $columns Columns.
     * @return array
     */
    public function MPT_posts_list_column( $columns ) {
        $columns['mpt_image'] = 'MPT';
        return $columns;
    }

    /**
     * Cell content for the MPT column.
     *
     * @since 7.0.0
     * @param string $column  Column id.
     * @param int    $post_id Post ID.
     */
    public function MPT_posts_list_column_content( $column, $post_id ) {
        if ( 'mpt_image' !== $column ) {
            return;
        }
        $post_id = (int) $post_id;
        $thumb = get_the_post_thumbnail( $post_id, array(48, 48) );
        $has = has_post_thumbnail( $post_id );
        $thumb_id = (int) get_post_thumbnail_id( $post_id );
        $label = ( $has ? __( 'Regenerate', 'magic-post-thumbnail' ) : __( 'Generate', 'magic-post-thumbnail' ) );
        echo '<div class="mpt-list-cell" data-post="' . esc_attr( $post_id ) . '" data-thumb="' . esc_attr( $thumb_id ) . '">';
        echo '<button type="button" class="mpt-list-thumb" title="' . esc_attr__( 'Open in Media Library', 'magic-post-thumbnail' ) . '">';
        if ( $thumb ) {
            echo $thumb;
            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        } else {
            echo '<span class="mpt-list-empty"></span>';
        }
        echo '</button>';
        echo '<button type="button" class="button-link mpt-list-generate">' . esc_html( $label ) . '</button>';
        echo '</div>';
    }

    /**
     * One-click generate from the posts list.
     *
     * @since 7.0.0
     */
    public function MPT_generate_from_list() {
        if ( !current_user_can( 'mpt_manage' ) ) {
            wp_send_json_error( 'forbidden', 403 );
        }
        check_ajax_referer( 'mpt_generate_from_list', 'nonce' );
        $post_id = ( isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0 );
        if ( $post_id < 1 || !current_user_can( 'edit_post', $post_id ) ) {
            wp_send_json_error( 'forbidden', 403 );
        }
        $rewrite = ( has_post_thumbnail( $post_id ) ? 1 : 0 );
        $generation = Magic_Post_Thumbnail_Generation::MPT_instance( $this->plugin_name, $this->version );
        $result = $generation->MPT_create_thumb(
            $post_id,
            0,
            1,
            1,
            $rewrite,
            false,
            null,
            null,
            null,
            null,
            null,
            false,
            'manual'
        );
        if ( false === $result ) {
            wp_send_json_error( 'failed' );
        }
        $html = get_the_post_thumbnail( $post_id, array(48, 48) );
        wp_send_json_success( array(
            'html'  => ( $html ? $html : '' ),
            'label' => __( 'Regenerate', 'magic-post-thumbnail' ),
            'thumb' => (int) get_post_thumbnail_id( $post_id ),
        ) );
    }

    /**
     * Set featured image from the posts list media modal.
     *
     * @since 7.0.0
     */
    public function MPT_set_featured_from_list() {
        if ( !current_user_can( 'mpt_manage' ) ) {
            wp_send_json_error( 'forbidden', 403 );
        }
        check_ajax_referer( 'mpt_generate_from_list', 'nonce' );
        $post_id = ( isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0 );
        $thumb_id = ( isset( $_POST['thumbnail_id'] ) ? absint( $_POST['thumbnail_id'] ) : 0 );
        if ( $post_id < 1 || !current_user_can( 'edit_post', $post_id ) ) {
            wp_send_json_error( 'forbidden', 403 );
        }
        if ( $thumb_id < 1 || !wp_attachment_is_image( $thumb_id ) ) {
            wp_send_json_error( 'invalid' );
        }
        if ( !current_user_can( 'upload_files' ) || !current_user_can( 'read_post', $thumb_id ) ) {
            wp_send_json_error( 'forbidden', 403 );
        }
        set_post_thumbnail( $post_id, $thumb_id );
        $html = get_the_post_thumbnail( $post_id, array(48, 48) );
        wp_send_json_success( array(
            'html'  => ( $html ? $html : '' ),
            'label' => __( 'Regenerate', 'magic-post-thumbnail' ),
            'thumb' => $thumb_id,
        ) );
    }

    /**
     * Generate a featured image on a draft test post.
     *
     * @since 7.0.0
     */
    public function MPT_dashboard_test_generate() {
        if ( !current_user_can( 'mpt_manage' ) ) {
            wp_send_json_error( 'forbidden', 403 );
        }
        check_ajax_referer( 'mpt_dashboard_test', 'nonce' );
        $health = $this->MPT_config_health();
        if ( empty( $health['can_test'] ) ) {
            wp_send_json_error( 'not_ready' );
        }
        if ( function_exists( 'set_time_limit' ) ) {
            @set_time_limit( 90 );
        }
        $existing = get_posts( array(
            'post_type'      => 'post',
            'post_status'    => array('draft', 'private'),
            'meta_key'       => '_mpt_health_test',
            'meta_value'     => '1',
            'posts_per_page' => 1,
            'fields'         => 'ids',
        ) );
        if ( !empty( $existing ) ) {
            $post_id = (int) $existing[0];
        } else {
            $post_id = wp_insert_post( array(
                'post_title'   => __( 'Magic Post Thumbnail — test', 'magic-post-thumbnail' ),
                'post_content' => __( 'This draft was created to verify image generation. You can delete it.', 'magic-post-thumbnail' ),
                'post_status'  => 'draft',
                'post_type'    => 'post',
            ), true );
            if ( is_wp_error( $post_id ) || $post_id < 1 ) {
                wp_send_json_error( 'create_failed' );
            }
            update_post_meta( $post_id, '_mpt_health_test', '1' );
        }
        $generation = Magic_Post_Thumbnail_Generation::MPT_instance( $this->plugin_name, $this->version );
        $result = $generation->MPT_create_thumb(
            $post_id,
            0,
            0,
            0,
            1,
            false,
            'cat',
            null,
            null,
            null,
            null,
            false,
            'manual'
        );
        if ( false === $result ) {
            wp_send_json_error( 'failed' );
        }
        $html = get_the_post_thumbnail( $post_id, 'medium' );
        wp_send_json_success( array(
            'html'     => ( $html ? $html : '' ),
            'edit_url' => get_edit_post_link( $post_id, 'raw' ),
            'view'     => __( 'Open the test draft', 'magic-post-thumbnail' ),
        ) );
    }

    /**
     * Testing if APIs works into settings page
     *
     * @since    5.0.0
     */
    public function MPT_test_apis() {
        if ( !current_user_can( 'mpt_manage' ) ) {
            wp_send_json_error( 'forbidden', 403 );
        }
        check_ajax_referer( 'api_testing_nonce', 'nonce' );
        $api_key = ( isset( $_POST['apikey'] ) ? sanitize_text_field( wp_unslash( $_POST['apikey'] ) ) : '' );
        $api_bank = ( isset( $_POST['apibank'] ) ? sanitize_key( wp_unslash( $_POST['apibank'] ) ) : '' );
        $allowed = array(
            'pixabay',
            'dalle',
            'gptimage',
            'youtube',
            'unsplash',
            'pexels',
            'replicate',
            'ideogram',
            'flux2',
            'gemini',
            'grok',
            'stability'
        );
        if ( '' === $api_key || !in_array( $api_bank, $allowed, true ) ) {
            wp_send_json_error( 'invalid' );
        }
        $args = $this->MPT_http_defaults();
        $args['headers']['Accept'] = 'application/json';
        $ok = false;
        switch ( $api_bank ) {
            case 'pixabay':
                $response = wp_remote_get( add_query_arg( array(
                    'key' => $api_key,
                    'q'   => 'exemple',
                ), 'https://pixabay.com/api/' ), $args );
                $ok = $this->MPT_test_api_json_has( $response, 'hits' );
                break;
            case 'dalle':
            case 'gptimage':
                $args['headers']['Authorization'] = 'Bearer ' . $api_key;
                $args['headers']['Content-Type'] = 'application/json';
                $response = wp_remote_get( 'https://api.openai.com/v1/models', $args );
                $ok = $this->MPT_test_api_json_has( $response, 'data' );
                break;
            case 'youtube':
                $response = wp_remote_get( add_query_arg( array(
                    'part'       => 'snippet',
                    'maxResults' => 1,
                    'type'       => 'video',
                    'key'        => $api_key,
                ), 'https://www.googleapis.com/youtube/v3/search' ), $args );
                $ok = $this->MPT_test_api_json_has( $response, 'items' );
                break;
            case 'unsplash':
                $response = wp_remote_get( add_query_arg( array(
                    'query'     => 'test',
                    'client_id' => $api_key,
                ), 'https://api.unsplash.com/search/photos' ), $args );
                $ok = $this->MPT_test_api_json_has( $response, 'results' );
                break;
            case 'pexels':
                $args['headers']['Authorization'] = $api_key;
                $response = wp_remote_get( add_query_arg( array(
                    'query'    => 'nature',
                    'per_page' => 1,
                ), 'https://api.pexels.com/v1/search' ), $args );
                $ok = $this->MPT_test_api_json_has( $response, 'photos' );
                break;
            case 'stability':
                $args['timeout'] = 15;
                $args['headers']['Authorization'] = 'Bearer ' . $api_key;
                unset($args['headers']['Content-Type']);
                $response = wp_remote_get( 'https://api.stability.ai/v1/user/account', $args );
                if ( is_wp_error( $response ) ) {
                    $this->MPT_store_bank_key_status( 'stability', 'invalid' );
                    wp_send_json_error( array(
                        'reason' => 'connection',
                    ) );
                }
                $ok = 200 === (int) wp_remote_retrieve_response_code( $response );
                break;
            case 'replicate':
                $args['timeout'] = 10;
                $args['headers']['Authorization'] = 'Token ' . $api_key;
                $args['headers']['Content-Type'] = 'application/json';
                $response = wp_remote_get( 'https://api.replicate.com/v1/models', $args );
                $code = ( is_wp_error( $response ) ? 0 : (int) wp_remote_retrieve_response_code( $response ) );
                if ( 401 === $code ) {
                    $this->MPT_store_bank_key_status( 'replicate', 'invalid' );
                    wp_send_json_error( 'unauthorized', 401 );
                }
                $ok = 200 === $code;
                break;
            case 'ideogram':
                $boundary = '----WebKitFormBoundary' . md5( uniqid( '', true ) );
                $args['timeout'] = 15;
                $args['method'] = 'POST';
                $args['headers']['Api-Key'] = $api_key;
                $args['headers']['Content-Type'] = 'multipart/form-data; boundary=' . $boundary;
                $args['body'] = '--' . $boundary . "--\r\n";
                $response = wp_remote_request( 'https://api.ideogram.ai/v1/ideogram-v4/generate', $args );
                $code = ( is_wp_error( $response ) ? 0 : (int) wp_remote_retrieve_response_code( $response ) );
                $ok = 401 !== $code && 403 !== $code && $code >= 400 && $code < 500;
                break;
            case 'flux2':
                $args['timeout'] = 10;
                $args['headers']['x-key'] = $api_key;
                $args['headers']['accept'] = 'application/json';
                $response = wp_remote_get( 'https://api.bfl.ai/v1/credits', $args );
                $code = ( is_wp_error( $response ) ? 0 : (int) wp_remote_retrieve_response_code( $response ) );
                $ok = 200 === $code;
                break;
            case 'gemini':
                $response = wp_remote_get( add_query_arg( array(
                    'key' => $api_key,
                ), 'https://generativelanguage.googleapis.com/v1beta/models' ), $args );
                $ok = $this->MPT_test_api_json_has( $response, 'models' );
                break;
            case 'grok':
                $args['timeout'] = 15;
                $args['headers']['Authorization'] = 'Bearer ' . $api_key;
                unset($args['headers']['Content-Type']);
                $response = wp_remote_get( 'https://api.x.ai/v1/api-key', $args );
                if ( is_wp_error( $response ) ) {
                    $this->MPT_store_bank_key_status( 'grok', 'invalid' );
                    wp_send_json_error( array(
                        'reason' => 'connection',
                    ) );
                }
                $code = (int) wp_remote_retrieve_response_code( $response );
                $raw = wp_remote_retrieve_body( $response );
                $body = json_decode( $raw, true );
                if ( !is_array( $body ) ) {
                    $body = array();
                }
                $haystack = strtolower( (string) $raw );
                if ( 200 === $code ) {
                    $blocked = false;
                    foreach ( array(
                        'api_key_blocked',
                        'api_key_disabled',
                        'team_blocked',
                        'apiKeyBlocked',
                        'apiKeyDisabled',
                        'teamBlocked'
                    ) as $flag ) {
                        if ( isset( $body[$flag] ) && true === $body[$flag] ) {
                            $blocked = true;
                            break;
                        }
                    }
                    if ( $blocked ) {
                        $this->MPT_store_bank_key_status( 'grok', 'invalid' );
                        wp_send_json_error( array(
                            'reason' => 'billing',
                        ) );
                    }
                    $ok = true;
                    break;
                }
                if ( in_array( $code, array(402, 403), true ) || false !== strpos( $haystack, 'credit' ) || false !== strpos( $haystack, 'spending' ) || false !== strpos( $haystack, 'billing' ) ) {
                    $this->MPT_store_bank_key_status( 'grok', 'invalid' );
                    wp_send_json_error( array(
                        'reason' => 'billing',
                    ) );
                }
                $ok = false;
                break;
        }
        if ( $ok ) {
            $this->MPT_store_bank_key_status( $api_bank, 'valid' );
            wp_send_json_success( array(
                'ok' => true,
            ) );
        }
        $this->MPT_store_bank_key_status( $api_bank, 'invalid' );
        wp_send_json_error( 'invalid_key' );
    }

    /**
     * Whether a test API response contains a non-empty JSON array key.
     *
     * @param mixed  $response wp_remote response.
     * @param string $key      JSON key.
     * @return bool
     */
    private function MPT_test_api_json_has( $response, $key ) {
        if ( is_wp_error( $response ) || 200 !== (int) wp_remote_retrieve_response_code( $response ) ) {
            return false;
        }
        $data = json_decode( wp_remote_retrieve_body( $response ), true );
        return is_array( $data ) && !empty( $data[$key] );
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
        wp_set_script_translations( 'mpt-images-script', 'magic-post-thumbnail', plugin_dir_path( __DIR__ ) . 'languages' );
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
        load_plugin_textdomain( 'magic-post-thumbnail', false, dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages' );
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
     * Preview the bank currently open in Source settings (no post required).
     *
     * @since 7.1.0
     */
    public function MPT_preview_search() {
        if ( !current_user_can( 'mpt_manage' ) ) {
            wp_send_json_error( 'forbidden', 403 );
        }
        check_ajax_referer( 'mpt_preview_search', 'nonce' );
        $keyword = ( isset( $_POST['keyword'] ) ? sanitize_text_field( wp_unslash( $_POST['keyword'] ) ) : '' );
        if ( '' === $keyword ) {
            $keyword = 'sunset';
        }
        $allowed = array();
        foreach ( $this->MPT_banks_name_auto() as $api_code ) {
            if ( empty( $api_code[1] ) || empty( $api_code[0] ) ) {
                continue;
            }
            $allowed[] = $this->MPT_normalize_bank_slug( $api_code[0] );
        }
        $bank = ( isset( $_POST['bank'] ) ? $this->MPT_normalize_bank_slug( sanitize_text_field( wp_unslash( $_POST['bank'] ) ) ) : '' );
        if ( '' === $bank || !in_array( $bank, $allowed, true ) ) {
            wp_send_json_error( 'no_bank' );
        }
        $generation = Magic_Post_Thumbnail_Generation::MPT_instance( $this->plugin_name, $this->version );
        $allow_ai = !empty( $_POST['confirm_ai'] );
        $result = $generation->MPT_preview_search_results( $bank, $keyword, $allow_ai );
        if ( false === $result ) {
            wp_send_json_error( 'preview_failed' );
        }
        $items = array();
        if ( !empty( $result['items'] ) && is_array( $result['items'] ) ) {
            foreach ( $result['items'] as $item ) {
                if ( empty( $item['url'] ) || !is_string( $item['url'] ) ) {
                    continue;
                }
                $url = $item['url'];
                if ( 0 === strpos( $url, 'data:image/' ) ) {
                    if ( !preg_match( '#^data:image/(png|jpeg|jpg|webp|gif);base64,[A-Za-z0-9+/=\\s]+$#', $url ) ) {
                        continue;
                    }
                } else {
                    $url = esc_url_raw( $url );
                    if ( empty( $url ) ) {
                        continue;
                    }
                }
                $items[] = array(
                    'url'   => $url,
                    'title' => ( isset( $item['title'] ) ? sanitize_text_field( $item['title'] ) : '' ),
                );
            }
        }
        wp_send_json_success( array(
            'ai'            => !empty( $result['ai'] ),
            'needs_confirm' => !empty( $result['needs_confirm'] ),
            'bank'          => $bank,
            'bank_label'    => $this->MPT_bank_label( $bank ),
            'items'         => $items,
            'message'       => ( !empty( $result['message'] ) ? sanitize_text_field( $result['message'] ) : '' ),
        ) );
    }

    /**
     * Search MPT Gutenberg Block
     *
     * @since    5.0.0
     */
    public function MPT_block_searching_images() {
        check_ajax_referer( 'mpt_gutenberg_block', 'nonce' );
        if ( !current_user_can( 'mpt_manage' ) ) {
            wp_send_json_error( 'forbidden', 403 );
        }
        $search_term = ( isset( $_GET['search'] ) ? sanitize_text_field( wp_unslash( $_GET['search'] ) ) : '' );
        $bank = ( isset( $_GET['bank'] ) ? sanitize_text_field( strtolower( wp_unslash( $_GET['bank'] ) ) ) : '' );
        $index = ( isset( $_GET['index'] ) ? intval( $_GET['index'] ) : 0 );
        $id = ( isset( $_GET['id'] ) ? intval( $_GET['id'] ) : 0 );
        if ( FALSE === get_post_status( $id ) ) {
            wp_send_json_error( 'Error with post ID.' );
        }
        if ( !current_user_can( 'edit_post', $id ) ) {
            wp_send_json_error( 'forbidden', 403 );
        }
        if ( !$this->MPT_is_gutenberg_bank_allowed( $bank ) ) {
            wp_send_json_error( 'invalid_bank', 403 );
        }
        $REST_generation = Magic_Post_Thumbnail_Generation::MPT_instance( $this->plugin_name, $this->version );
        $results_thumbs = $REST_generation->MPT_create_thumb(
            $id,
            '0',
            '1',
            '1',
            '1',
            TRUE,
            $search_term,
            $bank,
            null,
            null,
            null,
            false,
            'manual'
        );
        if ( $results_thumbs === false ) {
            wp_send_json_error( 'Error connecting to the API.' );
        } else {
            $this->MPT_gutenberg_remember_urls( $id, $results_thumbs, $bank );
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
        check_ajax_referer( 'mpt_gutenberg_block', 'nonce' );
        if ( !current_user_can( 'mpt_manage' ) ) {
            wp_send_json_error( 'forbidden', 403 );
        }
        $post_id = ( isset( $_POST['id'] ) ? absint( $_POST['id'] ) : 0 );
        if ( !$post_id || false === get_post_status( $post_id ) || !current_user_can( 'edit_post', $post_id ) ) {
            wp_send_json_error( 'forbidden', 403 );
        }
        $url_image_raw = ( isset( $_POST['url_image'] ) ? wp_unslash( $_POST['url_image'] ) : '' );
        $url_thumb_raw = ( isset( $_POST['url_thumbnail'] ) ? wp_unslash( $_POST['url_thumbnail'] ) : '' );
        $url_image = $this->MPT_validate_remote_url( $url_image_raw );
        $url_thumbnail = $this->MPT_validate_remote_url( $url_thumb_raw );
        if ( empty( $url_image ) ) {
            wp_send_json_error( array(
                'erreur' => 'Invalid URL',
            ) );
        }
        $search_term = ( isset( $_POST['search_term'] ) ? sanitize_text_field( $_POST['search_term'] ) : 'image' );
        $bank = ( isset( $_POST['bank'] ) ? sanitize_text_field( wp_unslash( $_POST['bank'] ) ) : '' );
        if ( !$this->MPT_is_gutenberg_bank_allowed( $bank ) ) {
            wp_send_json_error( array(
                'erreur' => 'Invalid bank',
            ), 403 );
        }
        if ( !$this->MPT_gutenberg_url_is_allowed( $post_id, $url_image, $bank ) ) {
            wp_send_json_error( array(
                'erreur' => 'Invalid URL',
            ) );
        }
        if ( !empty( $url_thumbnail ) && !$this->MPT_gutenberg_url_is_allowed( $post_id, $url_thumbnail, $bank ) ) {
            $url_thumbnail = '';
        }
        // Openverse thumbnail URLs have ?format=json which returns JSON, not an image - strip it
        if ( !empty( $url_thumbnail ) && strpos( $url_thumbnail, 'api.openverse.org' ) !== false ) {
            $url_thumbnail = preg_replace( '/\\?format=json$/', '', $url_thumbnail );
        }
        $alt = '';
        $caption = '';
        // For Openverse: try main image first (thumbnail proxy often fails)
        $url_to_try = $url_image;
        // Extend HTTP timeout and add User-Agent for large/restricted images (Openverse, Flickr, etc.)
        add_filter( 'http_request_timeout', array($this, 'MPT_custom_http_request_timeout') );
        $this->block_downloading_url = $url_to_try;
        add_filter(
            'http_request_args',
            array($this, 'MPT_filter_http_request_args_for_block_download'),
            10,
            2
        );
        $tmp = download_url( $url_to_try );
        remove_filter( 'http_request_args', array($this, 'MPT_filter_http_request_args_for_block_download'), 10 );
        $this->block_downloading_url = null;
        remove_filter( 'http_request_timeout', array($this, 'MPT_custom_http_request_timeout') );
        // If main URL failed, try thumbnail as fallback (for Openverse)
        if ( is_wp_error( $tmp ) && !empty( $url_thumbnail ) && $url_thumbnail !== $url_image ) {
            add_filter( 'http_request_timeout', array($this, 'MPT_custom_http_request_timeout') );
            $this->block_downloading_url = $url_thumbnail;
            add_filter(
                'http_request_args',
                array($this, 'MPT_filter_http_request_args_for_block_download'),
                10,
                2
            );
            $tmp = download_url( $url_thumbnail );
            remove_filter( 'http_request_args', array($this, 'MPT_filter_http_request_args_for_block_download'), 10 );
            $this->block_downloading_url = null;
            remove_filter( 'http_request_timeout', array($this, 'MPT_custom_http_request_timeout') );
        }
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
        $attach_id = wp_insert_attachment( $attachment, $uploaded_file['file'], $post_id );
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
     * Add User-Agent for block image download (fixes 404 from Openverse/Flickr etc.)
     *
     * @since    5.0.0
     * @param array  $args Request arguments.
     * @param string $url  Request URL.
     * @return array Modified arguments.
     */
    public function MPT_filter_http_request_args_for_block_download( $args, $url ) {
        if ( $this->block_downloading_url !== null ) {
            $args['user-agent'] = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36 (compatible; WordPress/' . get_bloginfo( 'version' ) . '; MPT)';
            $args['timeout'] = 40;
            $args['reject_unsafe_urls'] = true;
            $args['redirection'] = 3;
            $args['_mpt'] = true;
        }
        return $args;
    }

    /**
     * Adds a settings link to the plugins page
     *
     * @since    2.0.5
     */
    public function MPT_add_link_parameters( $links ) {
        $settings_link = '<a href="admin.php?page=magic-post-thumbnail-admin-display">' . esc_html__( 'Settings', 'magic-post-thumbnail' ) . '</a>';
        array_push( $links, $settings_link );
        return $links;
    }

    /**
     * Adds notice to ask for a review
     *
     * @since    5.2.6
     */
    public function MPT_show_notice() {
        if ( '1' === (string) get_option( 'mpt_show_bank_chain_notice' ) ) {
            ?>
			<div class="notice notice-info is-dismissible">
				<p><?php 
            esc_html_e( 'Magic Post Thumbnail now uses a single image-bank chain for every generation path (editor button, bulk, cron and imports). Configure it under Source. Each Automatic placement can still override that chain.', 'magic-post-thumbnail' );
            ?></p>
			</div>
			<?php 
            update_option( 'mpt_show_bank_chain_notice', '0', false );
        }
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
            esc_html_e( 'Do you enjoy using Magic Post Thumbnail? If so, please consider giving it a 5-star rating on WordPress.org. Your support helps others discover our plugin and means a lot to us!', 'magic-post-thumbnail' );
            ?></p>
                <p>
                    <a href="#" id="mpt-rate" class="button button-primary"><?php 
            esc_html_e( 'Sure!', 'magic-post-thumbnail' );
            ?></a>
                    <a href="#" id="mpt-hide-notice" class="button"><?php 
            esc_html_e( 'No, thanks', 'magic-post-thumbnail' );
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
        check_ajax_referer( 'mpt_notice', 'nonce' );
        if ( !current_user_can( 'mpt_manage' ) ) {
            wp_die( -1, 403 );
        }
        update_option( 'mpt_hide_notice', 1 );
        wp_die();
    }

    public function MPT_remind_later() {
        check_ajax_referer( 'mpt_notice', 'nonce' );
        if ( !current_user_can( 'mpt_manage' ) ) {
            wp_die( -1, 403 );
        }
        $delay = ( isset( $_POST['delay'] ) ? intval( $_POST['delay'] ) : 30 );
        $new_time = time() + $delay * 24 * 60 * 60;
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
                $default_bank = 'bing';
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
                $default_bank = 'bing';
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
        }
        $this->MPT_migrate_google_scraping_to_bing();
        $this->MPT_migrate_dallev1_to_gptimage();
        $this->MPT_migrate_bank_chain_v71();
    }

    /**
     * One-shot migration: google_scraping bank becomes bing.
     *
     * @since 7.0.0
     */
    public function MPT_migrate_google_scraping_to_bing() {
        $flag = get_option( 'mpt_migrated_bing_v7' );
        if ( '1' === (string) $flag ) {
            return;
        }
        $banks = get_option( 'MPT_plugin_banks_settings', array() );
        if ( !is_array( $banks ) ) {
            $banks = array();
        }
        $replace_bank = static function ( $value ) {
            if ( 'google_scraping' === $value ) {
                return 'bing';
            }
            return $value;
        };
        foreach ( array('api_chosen_auto', 'api_chosen_manual') as $key ) {
            if ( empty( $banks[$key] ) || !is_array( $banks[$key] ) ) {
                continue;
            }
            $next = array();
            foreach ( $banks[$key] as $k => $v ) {
                $new_k = ( 'google_scraping' === (string) $k ? 'bing' : $k );
                $new_v = ( is_string( $v ) ? $replace_bank( $v ) : $v );
                $next[$new_k] = $new_v;
            }
            $banks[$key] = array_unique( array_filter( $next ) );
            unset($banks[$key]['envato'], $banks[$key]['google_scraping']);
        }
        if ( isset( $banks['api_chosen'] ) && 'google_scraping' === $banks['api_chosen'] ) {
            $banks['api_chosen'] = 'bing';
        }
        if ( isset( $banks['google_scraping'] ) && is_array( $banks['google_scraping'] ) ) {
            $copied = $banks['google_scraping'];
            unset($copied['bing_fallback']);
            if ( empty( $banks['bing'] ) || !is_array( $banks['bing'] ) ) {
                $banks['bing'] = $copied;
            } else {
                $banks['bing'] = array_merge( $copied, $banks['bing'] );
            }
            unset($banks['google_scraping']);
        }
        unset($banks['envato']);
        update_option( 'MPT_plugin_banks_settings', $banks );
        $main = get_option( 'MPT_plugin_main_settings', array() );
        if ( is_array( $main ) && !empty( $main['image_block'] ) && is_array( $main['image_block'] ) ) {
            foreach ( $main['image_block'] as $key => $block ) {
                if ( isset( $block['api_chosen'] ) && 'google_scraping' === $block['api_chosen'] ) {
                    $main['image_block'][$key]['api_chosen'] = 'bing';
                }
                if ( isset( $block['api_chosen_2'] ) && 'google_scraping' === $block['api_chosen_2'] ) {
                    $main['image_block'][$key]['api_chosen_2'] = 'bing';
                }
            }
            update_option( 'MPT_plugin_main_settings', $main );
        }
        global $wpdb;
        $needle = $wpdb->esc_like( 'mpt_google_scraping_strategy_v1_' );
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $wpdb->query( $wpdb->prepare(
            "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s OR option_name LIKE %s",
            $needle . '%',
            '_transient_' . $needle . '%',
            '_transient_timeout_' . $needle . '%'
        ) );
        if ( is_multisite() ) {
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
            $wpdb->query( $wpdb->prepare(
                "DELETE FROM {$wpdb->sitemeta} WHERE meta_key LIKE %s OR meta_key LIKE %s OR meta_key LIKE %s",
                $needle . '%',
                '_transient_' . $needle . '%',
                '_transient_timeout_' . $needle . '%'
            ) );
        }
        update_option( 'mpt_migrated_bing_v7', '1', false );
    }

    /**
     * One-shot migration: DALL·E 3 bank becomes GPT Image.
     *
     * @since 7.0.0
     */
    public function MPT_migrate_dallev1_to_gptimage() {
        $flag = get_option( 'mpt_migrated_gptimage_v7' );
        if ( '1' === (string) $flag ) {
            return;
        }
        $banks = get_option( 'MPT_plugin_banks_settings', array() );
        if ( !is_array( $banks ) ) {
            $banks = array();
        }
        $replace_bank = static function ( $value ) {
            if ( 'dallev1' === $value ) {
                return 'gptimage';
            }
            return $value;
        };
        foreach ( array('api_chosen_auto', 'api_chosen_manual') as $key ) {
            if ( empty( $banks[$key] ) || !is_array( $banks[$key] ) ) {
                continue;
            }
            $next = array();
            foreach ( $banks[$key] as $k => $v ) {
                $new_k = ( 'dallev1' === (string) $k ? 'gptimage' : $k );
                $new_v = ( is_string( $v ) ? $replace_bank( $v ) : $v );
                $next[$new_k] = $new_v;
            }
            $banks[$key] = array_unique( array_filter( $next ) );
            unset($banks[$key]['dallev1']);
        }
        if ( isset( $banks['api_chosen'] ) && 'dallev1' === $banks['api_chosen'] ) {
            $banks['api_chosen'] = 'gptimage';
        }
        if ( isset( $banks['dallev1'] ) && is_array( $banks['dallev1'] ) ) {
            $copied = $banks['dallev1'];
            $size_map = array(
                '1792x1024' => '1536x1024',
                '1024x1792' => '1024x1536',
            );
            if ( !empty( $copied['imgsize'] ) && isset( $size_map[$copied['imgsize']] ) ) {
                $copied['imgsize'] = $size_map[$copied['imgsize']];
            }
            if ( empty( $copied['quality'] ) ) {
                $copied['quality'] = 'medium';
            }
            if ( empty( $banks['gptimage'] ) || !is_array( $banks['gptimage'] ) ) {
                $banks['gptimage'] = $copied;
            } else {
                if ( empty( $banks['gptimage']['apikey'] ) && !empty( $copied['apikey'] ) ) {
                    $banks['gptimage']['apikey'] = $copied['apikey'];
                }
                if ( empty( $banks['gptimage']['imgsize'] ) && !empty( $copied['imgsize'] ) ) {
                    $banks['gptimage']['imgsize'] = $copied['imgsize'];
                }
                if ( empty( $banks['gptimage']['convert_jpg'] ) && !empty( $copied['convert_jpg'] ) ) {
                    $banks['gptimage']['convert_jpg'] = $copied['convert_jpg'];
                }
            }
            unset($banks['dallev1']);
        }
        update_option( 'MPT_plugin_banks_settings', $banks );
        $main = get_option( 'MPT_plugin_main_settings', array() );
        if ( is_array( $main ) && !empty( $main['image_block'] ) && is_array( $main['image_block'] ) ) {
            foreach ( $main['image_block'] as $key => $block ) {
                if ( isset( $block['api_chosen'] ) && 'dallev1' === $block['api_chosen'] ) {
                    $main['image_block'][$key]['api_chosen'] = 'gptimage';
                }
                if ( isset( $block['api_chosen_2'] ) && 'dallev1' === $block['api_chosen_2'] ) {
                    $main['image_block'][$key]['api_chosen_2'] = 'gptimage';
                }
            }
            update_option( 'MPT_plugin_main_settings', $main );
        }
        update_option( 'mpt_migrated_gptimage_v7', '1', false );
    }

    /**
     * Unify Source chain and Automatic bank selects (v7.1).
     *
     * @since 7.1.0
     */
    public function MPT_migrate_bank_chain_v71() {
        $flag = get_option( 'mpt_migrated_bank_chain_v71' );
        if ( '1' === (string) $flag ) {
            return;
        }
        $banks = get_option( 'MPT_plugin_banks_settings', array() );
        $main = get_option( 'MPT_plugin_main_settings', array() );
        if ( !is_array( $banks ) ) {
            $banks = array();
        }
        if ( !is_array( $main ) ) {
            $main = array();
        }
        $old_auto = array();
        if ( !empty( $banks['api_chosen_auto'] ) && is_array( $banks['api_chosen_auto'] ) ) {
            foreach ( $banks['api_chosen_auto'] as $slug ) {
                $old_auto[] = $this->MPT_normalize_bank_slug( ( is_string( $slug ) ? $slug : '' ) );
            }
        }
        $block1 = ( isset( $main['image_block'][1] ) && is_array( $main['image_block'][1] ) ? $main['image_block'][1] : array() );
        $primary = ( isset( $block1['api_chosen'] ) ? $this->MPT_normalize_bank_slug( $block1['api_chosen'] ) : '' );
        $second = ( isset( $block1['api_chosen_2'] ) ? $this->MPT_normalize_bank_slug( $block1['api_chosen_2'] ) : '' );
        $chain = array();
        if ( $primary && 'default' !== $primary ) {
            $chain[] = $primary;
        }
        if ( $second && 'none' !== $second && 'default' !== $second ) {
            $chain[] = $second;
        }
        foreach ( $old_auto as $slug ) {
            $chain[] = $slug;
        }
        $free_slugs = array(
            'bing',
            'google_image',
            'gptimage',
            'openverse',
            'flickr',
            'pixabay'
        );
        $is_pro = false;
        $clean = array();
        foreach ( $chain as $slug ) {
            if ( '' === $slug || 'default' === $slug || 'none' === $slug ) {
                continue;
            }
            if ( !in_array( $slug, $this->MPT_allowed_bank_slugs(), true ) ) {
                continue;
            }
            if ( !$is_pro && !in_array( $slug, $free_slugs, true ) ) {
                continue;
            }
            $clean[$slug] = $slug;
        }
        if ( empty( $clean ) ) {
            $clean = array(
                'bing'      => 'bing',
                'openverse' => 'openverse',
            );
        }
        $banks['api_chosen_auto'] = $clean;
        if ( !empty( $banks['api_chosen_manual'] ) && is_array( $banks['api_chosen_manual'] ) ) {
            $manual = array();
            foreach ( $banks['api_chosen_manual'] as $k => $v ) {
                $slug = $this->MPT_normalize_bank_slug( ( is_string( $v ) && '' !== $v ? $v : $k ) );
                $manual[$slug] = $slug;
            }
            $banks['api_chosen_manual'] = $manual;
        }
        update_option( 'MPT_plugin_banks_settings', $banks );
        if ( !empty( $main['image_block'] ) && is_array( $main['image_block'] ) ) {
            foreach ( $main['image_block'] as $key => $block ) {
                if ( !is_array( $block ) ) {
                    continue;
                }
                if ( isset( $block['api_chosen'] ) ) {
                    $main['image_block'][$key]['api_chosen'] = $this->MPT_normalize_bank_slug( $block['api_chosen'] );
                }
                if ( 1 === (int) $key ) {
                    $main['image_block'][$key]['api_chosen'] = 'default';
                }
            }
            update_option( 'MPT_plugin_main_settings', $main );
        }
        update_option( 'mpt_migrated_bank_chain_v71', '1', false );
        update_option( 'mpt_show_bank_chain_notice', '1', false );
    }

    /**
     * Trim option array keys (forms sometimes send trailing spaces in names).
     *
     * @param mixed $input Raw input.
     * @return array
     */
    private function MPT_trim_option_keys( $input ) {
        if ( !is_array( $input ) ) {
            return array();
        }
        $out = array();
        foreach ( $input as $key => $value ) {
            $clean_key = ( is_string( $key ) ? trim( $key ) : $key );
            if ( is_array( $value ) ) {
                $value = $this->MPT_trim_option_keys( $value );
            }
            $out[$clean_key] = $value;
        }
        return $out;
    }

    /**
     * Recursively sanitize scalars and arrays.
     *
     * @param mixed $value Value.
     * @param int   $depth Depth.
     * @return mixed
     */
    private function MPT_sanitize_tree( $value, $depth = 0 ) {
        if ( $depth > 6 ) {
            return ( is_array( $value ) ? array() : '' );
        }
        if ( is_array( $value ) ) {
            $clean = array();
            foreach ( $value as $key => $item ) {
                if ( is_int( $key ) ) {
                    $clean_key = $key;
                } else {
                    $clean_key = sanitize_key( trim( (string) $key ) );
                    if ( '' === $clean_key ) {
                        continue;
                    }
                }
                $clean[$clean_key] = $this->MPT_sanitize_tree( $item, $depth + 1 );
            }
            return $clean;
        }
        if ( is_bool( $value ) || is_int( $value ) || is_float( $value ) ) {
            return $value;
        }
        return sanitize_textarea_field( (string) $value );
    }

    /**
     * Allowed bank option keys.
     *
     * @return array
     */
    private function MPT_allowed_bank_keys() {
        return array(
            'bing',
            'googleimage',
            'gptimage',
            'dallev1',
            'stability',
            'replicate',
            'ideogram',
            'flux2',
            'gemini',
            'grok',
            'cc_search',
            'flickr',
            'pixabay',
            'youtube',
            'unsplash',
            'pexels',
            'fallback'
        );
    }

    /**
     * Allowed bank slugs for api_chosen lists.
     *
     * @return array
     */
    private function MPT_allowed_bank_slugs() {
        return array(
            'bing',
            'google_image',
            'gptimage',
            'dallev1',
            'cc_search',
            'flickr',
            'pixabay',
            'youtube',
            'unsplash',
            'pexels',
            'stability',
            'replicate',
            'ideogram',
            'flux2',
            'gemini',
            'grok',
            'openverse',
            'content',
            'fallback'
        );
    }

    /**
     * Sanitize banks settings.
     *
     * @since 4.2.0
     * @param mixed $input Raw input.
     * @return array
     */
    public function MPT_sanitize_banks_settings( $input ) {
        if ( !is_array( $input ) ) {
            return array();
        }
        $input = $this->MPT_trim_option_keys( $input );
        $out = array();
        foreach ( array('api_chosen_auto', 'api_chosen_manual') as $key ) {
            if ( !isset( $input[$key] ) || !is_array( $input[$key] ) ) {
                continue;
            }
            $clean = array();
            foreach ( $input[$key] as $k => $v ) {
                $slug = sanitize_key( ( is_string( $v ) && '' !== $v ? $v : $k ) );
                if ( 'envato' === $slug || '' === $slug ) {
                    continue;
                }
                if ( 'google_scraping' === $slug ) {
                    $slug = 'bing';
                }
                if ( 'dallev1' === $slug ) {
                    $slug = 'gptimage';
                }
                $slug = $this->MPT_normalize_bank_slug( $slug );
                if ( !in_array( $slug, $this->MPT_allowed_bank_slugs(), true ) ) {
                    continue;
                }
                $clean[$slug] = $slug;
            }
            $out[$key] = $clean;
        }
        // Gutenberg banks are edited on the Block page. Keep the saved list when Source is submitted.
        if ( !isset( $out['api_chosen_manual'] ) ) {
            $existing_banks = get_option( 'MPT_plugin_banks_settings' );
            if ( is_array( $existing_banks ) && isset( $existing_banks['api_chosen_manual'] ) ) {
                $out['api_chosen_manual'] = $existing_banks['api_chosen_manual'];
            }
        }
        foreach ( $this->MPT_allowed_bank_keys() as $bank ) {
            if ( empty( $input[$bank] ) || !is_array( $input[$bank] ) ) {
                continue;
            }
            $out[$bank] = $this->MPT_sanitize_tree( $input[$bank] );
            if ( 'pixabay' === $bank ) {
                if ( isset( $out[$bank]['min_width'] ) ) {
                    $out[$bank]['min_width'] = absint( $out[$bank]['min_width'] );
                }
                if ( isset( $out[$bank]['min_height'] ) ) {
                    $out[$bank]['min_height'] = absint( $out[$bank]['min_height'] );
                }
            }
            if ( 'cc_search' === $bank && isset( $out[$bank]['source'] ) ) {
                $out[$bank]['source'] = absint( $out[$bank]['source'] );
            }
            if ( 'fallback' === $bank && isset( $out[$bank]['attachment_id'] ) ) {
                $out[$bank]['attachment_id'] = absint( $out[$bank]['attachment_id'] );
            }
        }
        return $out;
    }

    public function MPT_sanitize_main_settings( $input ) {
        if ( !is_array( $input ) ) {
            return array();
        }
        $input = $this->MPT_sanitize_tree( $this->MPT_trim_option_keys( $input ) );
        if ( isset( $input['image_block'] ) && is_array( $input['image_block'] ) ) {
            $blocks = array();
            foreach ( $input['image_block'] as $key => $block ) {
                if ( !is_array( $block ) ) {
                    continue;
                }
                if ( isset( $block['api_chosen'] ) ) {
                    $slug = $this->MPT_normalize_bank_slug( $block['api_chosen'] );
                    if ( 'default' !== $slug && !in_array( $slug, $this->MPT_allowed_bank_slugs(), true ) ) {
                        $slug = 'default';
                    }
                    $block['api_chosen'] = $slug;
                }
                if ( isset( $block['api_chosen_2'] ) ) {
                    $block['api_chosen_2'] = $this->MPT_normalize_bank_slug( $block['api_chosen_2'] );
                }
                if ( isset( $block['title_length'] ) ) {
                    $block['title_length'] = absint( $block['title_length'] );
                }
                $allowed_styles = array_keys( $this->MPT_ai_style_presets() );
                if ( isset( $block['ai_style'] ) && !in_array( $block['ai_style'], $allowed_styles, true ) ) {
                    $block['ai_style'] = '';
                }
                if ( isset( $block['ai_style_custom'] ) ) {
                    $block['ai_style_custom'] = sanitize_text_field( $block['ai_style_custom'] );
                }
                $block['ai_no_text'] = ( isset( $block['ai_no_text'] ) && 'true' === (string) $block['ai_no_text'] ? 'true' : '' );
                $blocks[absint( $key )] = $block;
            }
            $input['image_block'] = $blocks;
        }
        if ( isset( $input['bulk_generation_interval'] ) ) {
            $input['bulk_generation_interval'] = absint( $input['bulk_generation_interval'] );
        }
        $overlay = ( isset( $input['image_overlay'] ) ? $input['image_overlay'] : '' );
        $input['image_overlay'] = ( in_array( $overlay, array('title', 'title_logo'), true ) ? $overlay : '' );
        $input['image_overlay_logo'] = ( isset( $input['image_overlay_logo'] ) ? absint( $input['image_overlay_logo'] ) : 0 );
        $input['image_overlay_opacity'] = ( isset( $input['image_overlay_opacity'] ) ? max( 10, min( 90, absint( $input['image_overlay_opacity'] ) ) ) : 55 );
        $input['image_overlay_category'] = ( isset( $input['image_overlay_category'] ) && 'true' === (string) $input['image_overlay_category'] ? 'true' : '' );
        foreach ( array(
            'image_overlay_bg'   => '#111111',
            'image_overlay_text' => '#ffffff',
        ) as $hex_key => $hex_default ) {
            $hex = ( isset( $input[$hex_key] ) ? ltrim( (string) $input[$hex_key], '#' ) : '' );
            $input[$hex_key] = ( 6 === strlen( $hex ) && ctype_xdigit( $hex ) ? '#' . strtolower( $hex ) : $hex_default );
        }
        foreach ( array('choosed_save_post_post_type', 'choosed_wp_insert_post_type') as $list_key ) {
            if ( empty( $input[$list_key] ) || !is_array( $input[$list_key] ) ) {
                continue;
            }
            $clean = array();
            foreach ( $input[$list_key] as $k => $v ) {
                $slug = sanitize_key( ( is_string( $v ) && '' !== $v ? $v : $k ) );
                if ( '' !== $slug ) {
                    $clean[$slug] = $slug;
                }
            }
            $input[$list_key] = $clean;
        }
        return $input;
    }

    public function MPT_sanitize_block_settings( $input ) {
        if ( !is_array( $input ) ) {
            $input = array();
        }
        $input = $this->MPT_trim_option_keys( $input );
        $this->MPT_save_gutenberg_banks_from_request();
        return array(
            'enable_manual_search' => !empty( $input['enable_manual_search'] ),
            'enable_alt'           => ( isset( $input['enable_alt'] ) && 'enable' === $input['enable_alt'] ? 'enable' : '' ),
            'translate_alt'        => ( isset( $input['translate_alt'] ) && 'true' === $input['translate_alt'] ? 'true' : '' ),
            'translate_alt_lang'   => ( isset( $input['translate_alt_lang'] ) ? sanitize_text_field( $input['translate_alt_lang'] ) : '' ),
            'enable_caption'       => ( isset( $input['enable_caption'] ) && 'enable' === $input['enable_caption'] ? 'enable' : '' ),
        );
    }

    /**
     * Persist Gutenberg bank checkboxes posted from the Block settings form.
     * Field names stay on MPT_plugin_banks_settings[api_chosen_manual].
     *
     * @return void
     */
    private function MPT_save_gutenberg_banks_from_request() {
        if ( !isset( $_POST['MPT_plugin_banks_settings'] ) || !is_array( $_POST['MPT_plugin_banks_settings'] ) ) {
            return;
        }
        $posted = array();
        if ( isset( $_POST['MPT_plugin_banks_settings']['api_chosen_manual'] ) && is_array( $_POST['MPT_plugin_banks_settings']['api_chosen_manual'] ) ) {
            $posted = wp_unslash( $_POST['MPT_plugin_banks_settings']['api_chosen_manual'] );
        }
        $allowed = array();
        foreach ( $this->MPT_banks_name_manual() as $api_code ) {
            if ( !empty( $api_code[0] ) ) {
                $allowed[] = $api_code[0];
            }
        }
        $clean = array();
        foreach ( $posted as $k => $v ) {
            $slug = sanitize_key( ( is_string( $v ) && '' !== $v ? $v : $k ) );
            $slug = $this->MPT_normalize_bank_slug( $slug );
            if ( '' === $slug || !in_array( $slug, $allowed, true ) ) {
                continue;
            }
            $clean[$slug] = $slug;
        }
        $banks = get_option( 'MPT_plugin_banks_settings' );
        if ( !is_array( $banks ) ) {
            $banks = array();
        }
        $banks = wp_parse_args( $banks, $this->MPT_default_options_banks_settings( true ) );
        $banks['api_chosen_manual'] = $clean;
        update_option( 'MPT_plugin_banks_settings', $banks );
    }

    public function MPT_sanitize_text_settings( $input ) {
        if ( !is_array( $input ) ) {
            return array();
        }
        return $this->MPT_sanitize_tree( $this->MPT_trim_option_keys( $input ) );
    }

    public function MPT_sanitize_cron_settings( $input ) {
        if ( !is_array( $input ) ) {
            $input = array();
        }
        $input = $this->MPT_sanitize_tree( $this->MPT_trim_option_keys( $input ) );
        $input['enable_cron'] = ( isset( $input['enable_cron'] ) && 'enable' === $input['enable_cron'] ? 'enable' : 'disable' );
        $input['number_posts'] = ( isset( $input['number_posts'] ) ? absint( $input['number_posts'] ) : 0 );
        if ( isset( $input['cron_post_type'] ) && is_array( $input['cron_post_type'] ) ) {
            $clean = array();
            foreach ( $input['cron_post_type'] as $k => $v ) {
                $slug = sanitize_key( ( is_string( $v ) && '' !== $v ? $v : $k ) );
                if ( '' !== $slug ) {
                    $clean[$slug] = $slug;
                }
            }
            $input['cron_post_type'] = $clean;
        }
        if ( isset( $input['cron_post_status'] ) && is_array( $input['cron_post_status'] ) ) {
            $allowed = array('publish', 'draft', 'pending');
            $clean = array();
            foreach ( $input['cron_post_status'] as $k => $v ) {
                $status = sanitize_key( ( is_string( $v ) && '' !== $v ? $v : $k ) );
                if ( in_array( $status, $allowed, true ) ) {
                    $clean[$status] = $status;
                }
            }
            $input['cron_post_status'] = $clean;
        }
        wp_clear_scheduled_hook( 'mpt_scheduled_cron' );
        if ( 'enable' === $input['enable_cron'] && $this->mpt_freemius()->is__premium_only() && $this->mpt_freemius()->can_use_premium_code() ) {
            require_once dirname( __FILE__ ) . '/inc/crons.php';
            if ( !wp_next_scheduled( 'mpt_scheduled_cron' ) ) {
                wp_schedule_event( time(), 'mptcronpro', 'mpt_scheduled_cron' );
            }
        }
        return $input;
    }

    public function MPT_sanitize_rights_settings( $input ) {
        if ( !is_array( $input ) ) {
            return array();
        }
        $keys = array(
            'rights_editor',
            'rights_author',
            'rights_contributor',
            'rights_subscriber'
        );
        foreach ( $keys as $key ) {
            $input[$key] = ( isset( $input[$key] ) && 'true' === $input[$key] ? 'true' : '' );
        }
        return $input;
    }

    public function MPT_sanitize_proxy_settings( $input ) {
        if ( !is_array( $input ) ) {
            return array();
        }
        $input['enable_proxy'] = ( isset( $input['enable_proxy'] ) && 'enable' === $input['enable_proxy'] ? 'enable' : 'disable' );
        $input['proxy_adress'] = ( isset( $input['proxy_adress'] ) ? sanitize_text_field( $input['proxy_adress'] ) : '' );
        $input['proxy_port'] = ( isset( $input['proxy_port'] ) ? absint( $input['proxy_port'] ) : '' );
        $input['proxy_username'] = ( isset( $input['proxy_username'] ) ? sanitize_text_field( $input['proxy_username'] ) : '' );
        $input['proxy_password'] = ( isset( $input['proxy_password'] ) ? sanitize_text_field( $input['proxy_password'] ) : '' );
        return $input;
    }

    public function MPT_sanitize_compatibility_settings( $input ) {
        if ( !is_array( $input ) ) {
            return array();
        }
        $keys = array(
            'enable_wpai',
            'enable_REST',
            'enable_FIFU',
            'enable_wpematico',
            'enable_feedwordpress',
            'enable_wpautomatic',
            'enable_cmb2',
            'enable_acf',
            'enable_metaboxio'
        );
        foreach ( $keys as $key ) {
            $input[$key] = !empty( $input[$key] );
        }
        return $input;
    }

    public function MPT_sanitize_logs_settings( $input ) {
        if ( !is_array( $input ) ) {
            return array();
        }
        $input['logs'] = ( isset( $input['logs'] ) && 'true' === $input['logs'] ? 'true' : '' );
        return $input;
    }

    public function MPT_maybe_handle_log_actions() {
        if ( empty( $_GET['action'] ) || empty( $_GET['page'] ) ) {
            return;
        }
        if ( 'magic-post-thumbnail-admin-display' !== $_GET['page'] ) {
            return;
        }
        $action = sanitize_key( wp_unslash( $_GET['action'] ) );
        if ( 'downloadlog' === $action ) {
            $this->MPT_handle_download_log();
        } elseif ( 'deletelog' === $action ) {
            $this->MPT_handle_delete_log();
        }
    }

    public function MPT_handle_download_log() {
        if ( !current_user_can( 'mpt_manage' ) ) {
            wp_die( esc_html__( 'You do not have sufficient permissions.', 'magic-post-thumbnail' ) );
        }
        check_admin_referer( 'download_log' );
        $path = $this->MPT_log_file_path( true );
        if ( !$path || !is_readable( $path ) ) {
            wp_die( esc_html__( 'No log yet', 'magic-post-thumbnail' ) );
        }
        $contents = $this->MPT_log_readable_contents( $path );
        nocache_headers();
        header( 'Content-Type: application/octet-stream' );
        header( 'Content-Disposition: attachment; filename="mpt-logs.log"' );
        header( 'Content-Length: ' . strlen( $contents ) );
        echo $contents;
        exit;
    }

    public function MPT_handle_delete_log() {
        if ( defined( 'MPT_FREEMIUS_UNINSTALL' ) ) {
            $path = $this->MPT_log_file_path( true );
            if ( $path && is_writable( $path ) ) {
                @unlink( $path );
            }
            return;
        }
        if ( !current_user_can( 'mpt_manage' ) ) {
            wp_die( esc_html__( 'You do not have sufficient permissions.', 'magic-post-thumbnail' ) );
        }
        check_admin_referer( 'delete_log' );
        $path = $this->MPT_log_file_path( true );
        if ( $path && is_writable( $path ) ) {
            @unlink( $path );
        }
        wp_safe_redirect( remove_query_arg( array('action', '_wpnonce') ) );
        exit;
    }

}
