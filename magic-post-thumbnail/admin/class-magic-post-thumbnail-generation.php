<?php

/**
 * The functionalities for MPT images generation
 *
 * @package    Magic_Post_Thumbnail
 * @subpackage Magic_Post_Thumbnail/admin
 * @author     Magic Post Thumbnail <contact@magic-post-thumbnail.com>
 */
class Magic_Post_Thumbnail_Generation extends Magic_Post_Thumbnail_Helpers {
    /**
     * Shared generation instance.
     *
     * @var Magic_Post_Thumbnail_Generation|null
     */
    protected static $generation_instance = null;

    /**
     * Whether generation automation hooks were registered.
     *
     * @var bool
     */
    protected static $generation_hooks_registered = false;

    /**
     * Initialize the class and set its properties.
     *
     * @since    4.0.0
     * @param      string    $plugin_name       The name of this plugin.
     * @param      string    $version    The version of this plugin.
     */
    public function __construct( $plugin_name, $version ) {
        parent::__construct( $plugin_name, $version );
    }

    /**
     * Singleton.
     *
     * @param string $plugin_name Plugin slug.
     * @param string $version     Plugin version.
     * @return Magic_Post_Thumbnail_Generation
     */
    public static function MPT_instance( $plugin_name, $version ) {
        if ( null === self::$generation_instance ) {
            self::$generation_instance = new self($plugin_name, $version);
        }
        return self::$generation_instance;
    }

    /**
     * Register AJAX, save_post, REST and async generation once.
     *
     * @param string $plugin_name Plugin slug.
     * @param string $version     Plugin version.
     */
    public static function MPT_register_automation( $plugin_name, $version ) {
        $gen = self::MPT_instance( $plugin_name, $version );
        if ( self::$generation_hooks_registered ) {
            return;
        }
        self::$generation_hooks_registered = true;
        add_action( 'wp_ajax_generate_image', array($gen, 'MPT_ajax_call') );
        add_action(
            'mpt_generate_async',
            array($gen, 'MPT_run_async_generation'),
            10,
            2
        );
        $main_settings = wp_parse_args( get_option( 'MPT_plugin_main_settings' ), $gen->MPT_default_options_main_settings( false ) );
        if ( isset( $main_settings['enable_save_post_hook'] ) && 'enable' === $main_settings['enable_save_post_hook'] ) {
            add_action(
                'save_post',
                array($gen, 'MPT_check_post_type'),
                10,
                3
            );
        }
        if ( function_exists( 'mpt_freemius' ) && mpt_freemius()->is__premium_only() && mpt_freemius()->can_use_premium_code() ) {
            $compatibility = get_option( 'MPT_plugin_compatibility_settings' );
            if ( !empty( $compatibility['enable_REST'] ) ) {
                $post_types = get_post_types( array(
                    'public' => true,
                ) );
                foreach ( $post_types as $post_type ) {
                    add_action(
                        'rest_after_insert_' . $post_type,
                        array($gen, 'MPT_rest_after_insert'),
                        10,
                        3
                    );
                }
            }
            if ( isset( $main_settings['enable_wp_insert_post_hook'] ) && 'enable' === $main_settings['enable_wp_insert_post_hook'] ) {
                add_action(
                    'wp_insert_post',
                    array($gen, 'MPT_on_wp_insert_post'),
                    15,
                    3
                );
            }
        }
    }

    /**
     * REST create callback: schedule async generation.
     *
     * @param WP_Post         $post     Post object.
     * @param WP_REST_Request $request  Request.
     * @param bool            $creating Creating.
     */
    public function MPT_rest_after_insert( $post, $request, $creating ) {
        if ( !$creating || empty( $post->ID ) ) {
            return;
        }
        $this->MPT_schedule_generation( (int) $post->ID, 'rest' );
    }

    /**
     * wp_insert_post compatibility hook.
     *
     * @param int     $post_id Post ID.
     * @param WP_Post $post    Post.
     * @param bool    $update  Update.
     */
    public function MPT_on_wp_insert_post( $post_id, $post, $update ) {
        if ( $update || wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) {
            return;
        }
        if ( !$this->MPT_mark_in_request( $post_id ) ) {
            return;
        }
        $main_settings = get_option( 'MPT_plugin_main_settings' );
        $allowed = ( isset( $main_settings['choosed_wp_insert_post_type'] ) ? $main_settings['choosed_wp_insert_post_type'] : array() );
        if ( empty( $allowed ) ) {
            $defaults = $this->MPT_default_posts_types();
            $allowed = $defaults['choosed_post_type'];
        }
        if ( !in_array( get_post_type( $post_id ), (array) $allowed, true ) ) {
            return;
        }
        $this->MPT_run_async_generation( $post_id, 'wp_insert_post' );
    }

    /**
     * Schedule or run generation.
     *
     * @param int    $post_id Post ID.
     * @param string $source  Source.
     */
    public function MPT_schedule_generation( $post_id, $source ) {
        $post_id = absint( $post_id );
        if ( defined( 'DISABLE_WP_CRON' ) && DISABLE_WP_CRON ) {
            $this->MPT_run_async_generation( $post_id, $source );
            return;
        }
        if ( !wp_next_scheduled( 'mpt_generate_async', array($post_id, $source) ) ) {
            wp_schedule_single_event( time(), 'mpt_generate_async', array($post_id, $source) );
        }
        if ( function_exists( 'spawn_cron' ) ) {
            spawn_cron();
        }
    }

    /**
     * Run image blocks for a post.
     *
     * @param int    $post_id Post ID.
     * @param string $source  Source.
     */
    public function MPT_run_async_generation( $post_id, $source = 'rest' ) {
        $post_id = absint( $post_id );
        $log = $this->MPT_monolog_call();
        $log->info( 'Starting async generation', array(
            'post'   => $post_id,
            'source' => $source,
        ) );
        $main_defaults = $this->MPT_default_options_main_settings( true );
        $main_settings = wp_parse_args( get_option( 'MPT_plugin_main_settings' ), $main_defaults );
        if ( empty( $main_settings['image_block'] ) || !is_array( $main_settings['image_block'] ) ) {
            $main_settings['image_block'] = $main_defaults['image_block'];
        }
        foreach ( $main_settings['image_block'] as $key_img_block => $img_block ) {
            $this->MPT_create_thumb(
                $post_id,
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
                $source
            );
        }
    }

    /**
     * Ajax call for bulk generation
     *
     * @since    4.0.0
     */
    public function MPT_ajax_call() {
        if ( !current_user_can( 'mpt_manage' ) || empty( $_POST['nonce'] ) || false === wp_verify_nonce( $_POST['nonce'], 'ajax_nonce_magic_post_thumbnail' ) ) {
            wp_send_json_error();
        }
        if ( !isset( $_POST['ids_mpt_generation'] ) ) {
            wp_send_json_error();
        }
        $raw = wp_unslash( $_POST['ids_mpt_generation'] );
        $decoded = json_decode( $raw, true );
        if ( is_numeric( $decoded ) ) {
            $decoded = array((int) $decoded);
        } elseif ( !is_array( $decoded ) ) {
            $decoded = array_map( 'absint', explode( ',', (string) $raw ) );
        }
        $post_ids = array_values( array_filter( array_map( 'absint', $decoded ) ) );
        if ( empty( $post_ids ) ) {
            wp_send_json_error();
        }
        $button_autogenerate = ( isset( $_POST['buttonAutoGenerate'] ) ? boolval( $_POST['buttonAutoGenerate'] ) : false );
        // Count the number of posts for bulk processing.
        $count = count( $post_ids );
        // Re-index post IDs starting from 1 for consistency.
        $post_ids_with_keys = array();
        foreach ( $post_ids as $key => $val ) {
            $post_ids_with_keys[$key + 1] = $val;
        }
        // Retrieve the current post index and ID from the AJAX request.
        $current_post_index = ( isset( $_POST['currentPostIndex'] ) ? (int) $_POST['currentPostIndex'] : 1 );
        if ( !isset( $post_ids_with_keys[$current_post_index] ) ) {
            wp_send_json_error();
        }
        $current_post_id = $post_ids_with_keys[$current_post_index];
        if ( empty( $current_post_id ) || !current_user_can( 'edit_post', $current_post_id ) ) {
            wp_send_json_error();
        }
        $log = $this->MPT_monolog_call();
        $log->info( 'Ajax generation request', array(
            'post' => $current_post_id,
        ) );
        // Load plugin settings for image generation.
        $main_defaults = $this->MPT_default_options_main_settings( true );
        $main_settings = wp_parse_args( get_option( 'MPT_plugin_main_settings' ), $main_defaults );
        if ( empty( $main_settings['image_block'] ) || !is_array( $main_settings['image_block'] ) ) {
            $main_settings['image_block'] = $main_defaults['image_block'];
        }
        // Check if the 'rewrite featured image' option is enabled.
        if ( isset( $main_settings['rewrite_featured'] ) && $main_settings['rewrite_featured'] == true ) {
            $rewrite_featured = true;
        } else {
            $rewrite_featured = false;
        }
        $int_blockIndex = 0;
        $counter_blocks = 1;
        $img_blocks = $main_settings['image_block'];
        $speed = '500';
        // Default speed for image generation.
        $current_image_index = $int_blockIndex;
        // Current image block index.
        $counter_img = $counter_blocks;
        // Total number of image blocks.
        $keys = array_keys( $img_blocks );
        $MPT_return = null;
        $image_generation_result = null;
        $datas = array();
        if ( !isset( $keys[$current_image_index] ) ) {
            $log->error( 'Image block missing', array(
                'post' => $current_post_id,
            ) );
            wp_send_json_error();
        }
        $img_block = $img_blocks[$keys[$current_image_index]];
        if ( !is_array( $img_block ) ) {
            $img_block = array();
        }
        // Determine image location (featured or content).
        $image_location = ( !empty( $img_block['image_location'] ) ? $img_block['image_location'] : 'featured' );
        // Handle generation of the featured image.
        if ( has_post_thumbnail( $current_post_id ) && $rewrite_featured == false && $image_location == 'featured' ) {
            $generation_status = 'already-done';
            // Image already exists and rewriting is not needed.
            $MPT_return = (int) get_post_thumbnail_id( $current_post_id );
        } elseif ( (!has_post_thumbnail( $current_post_id ) || $rewrite_featured == true || $image_location != 'featured') && $current_post_id != 0 ) {
            // Generate featured image if not present or if rewriting is enabled.
            $image_generation_result = $this->MPT_create_thumb(
                $current_post_id,
                '0',
                '0',
                '0',
                $rewrite_featured,
                false,
                null,
                null,
                $keys[$current_image_index],
                false,
                true,
                $button_autogenerate,
                'manual'
            );
            // Check result and set generation status.
            $MPT_return = ( is_array( $image_generation_result ) ? $image_generation_result['id'] : $image_generation_result );
            if ( $MPT_return == null ) {
                $generation_status = 'failed';
            } else {
                $generation_status = 'successful';
                $speed = '500';
            }
        } else {
            $generation_status = 'error';
            // An error occurred during generation.
        }
        // Load compatibility settings for third-party plugins.
        $compatibility = wp_parse_args( get_option( 'MPT_plugin_compatibility_settings' ), $this->MPT_default_options_compatibility_settings( TRUE ) );
        $thumbnail_url = '';
        // Handle image preview when using the FIFU plugin.
        if ( true == $compatibility['enable_FIFU'] && 'FIFU' == $image_location && (is_plugin_active( 'featured-image-from-url/featured-image-from-url.php' ) || is_plugin_active( 'fifu-premium/fifu-premium.php' )) && $MPT_return != null ) {
        } elseif ( ($generation_status == 'already-done' || $generation_status == 'successful') && !empty( $MPT_return ) ) {
            // Display the newly generated image.
            $new_image = wp_get_attachment_image_src( $MPT_return, array(70, 70) );
            $thumbnail_preview_html = '<a class="generated-img" target="_blank" href="' . admin_url() . 'upload.php?item=' . $MPT_return . '"><img src="' . $new_image[0] . '" width="70" height="70" /></a>';
            $datas['thumbnail_id'] = $MPT_return;
            $datas['postimagediv'] = _wp_post_thumbnail_html( $MPT_return, $current_post_id );
        } elseif ( $generation_status == 'already-done' && "featured" === $image_location ) {
            // Display existing featured image.
            $thumbnail_preview_html = '<a class="generated-img" target="_blank" href="' . admin_url() . 'upload.php?item=' . get_post_thumbnail_id( $current_post_id ) . '">' . get_the_post_thumbnail( $current_post_id, array('70', '70') ) . '</a>';
        } else {
            // Display a placeholder image if generation fails.
            $thumbnail_preview_html = '<img src="' . plugins_url( 'img/no-image.jpg', __FILE__ ) . '" />';
        }
        // Prepare data for the next image block iteration.
        $current_image_index++;
        $datas['id'] = $current_post_id;
        $datas['status'] = $generation_status;
        $datas['img'] = $thumbnail_url;
        $datas['fimg'] = $thumbnail_preview_html;
        $datas['speed'] = $speed;
        $datas['blockIndex'] = $current_image_index;
        if ( is_array( $image_generation_result ) ) {
            $datas['keyword'] = ( isset( $image_generation_result['keyword'] ) ? $image_generation_result['keyword'] : '' );
            $datas['img_resolution'] = ( isset( $image_generation_result['img_resolution'] ) ? $image_generation_result['img_resolution'] : '' );
            $datas['img_size'] = ( isset( $image_generation_result['img_size'] ) ? $image_generation_result['img_size'] : '' );
            $datas['api_chosen'] = ( isset( $image_generation_result['api_chosen'] ) ? $image_generation_result['api_chosen'] : '' );
        }
        // If more image blocks are remaining, continue processing.
        if ( $current_image_index < $counter_img ) {
            $datas['nextPost'] = true;
            wp_send_json_success( $datas );
            // Send successful response for next iteration.
        } else {
            $datas['nextPost'] = false;
            // Send final response or error if data is incomplete.
            if ( !empty( $datas['id'] ) ) {
                wp_send_json_success( $datas );
            } else {
                wp_send_json_error( $datas );
            }
        }
    }

    public function MPT_check_post_type( $ID, $post, $update ) {
        if ( wp_is_post_revision( $ID ) || wp_is_post_autosave( $ID ) ) {
            return;
        }
        if ( !$this->MPT_mark_in_request( $ID ) ) {
            return;
        }
        global $pagenow;
        // Avoid not selected post types
        $main_settings = wp_parse_args( get_option( 'MPT_plugin_main_settings' ), $this->MPT_default_options_main_settings( FALSE ) );
        if ( empty( $main_settings['choosed_save_post_post_type'] ) ) {
            $choosed_post_type = $this->MPT_default_posts_types();
            $main_settings['choosed_save_post_post_type'] = $choosed_post_type["choosed_post_type"];
        }
        if ( !in_array( get_post_type( $ID ), $main_settings['choosed_save_post_post_type'] ) ) {
            return false;
        }
        //$active_posts_types	= wp_parse_args( get_option( 'MPT_plugin_posts_settings' ), $this->MPT_default_options_posts_settings( FALSE ) );
        $active_posts_types = $this->MPT_default_posts_types();
        // Avoid generation when click "Add New"
        if ( ($pagenow == 'index.php' || $pagenow == 'post.php') && in_array( get_post_type( $ID ), $active_posts_types['choosed_post_type'] ) ) {
            $main_settings = get_option( 'MPT_plugin_main_settings' );
            if ( isset( $main_settings['rewrite_featured'] ) && $main_settings['rewrite_featured'] == true ) {
                $rewrite_featured = true;
            } else {
                $rewrite_featured = false;
            }
            $img_blocks = $main_settings['image_block'];
            foreach ( $img_blocks as $key_img_block => $img_block ) {
                $this->MPT_create_thumb(
                    $ID,
                    '0',
                    '1',
                    '0',
                    $rewrite_featured,
                    false,
                    null,
                    null,
                    $key_img_block,
                    false,
                    false,
                    false,
                    'save_post'
                );
            }
        }
    }

    /**
     * Checks if an image already exists in the WordPress media library
     * 
     * This function searches for an existing image in the media library 
     * based on the provided title and filename. It first cleans the filename
     * by removing numbers and extension, then performs a search in the 
     * WordPress database.
     *
     * @since    6.0.5
     * @param string $title The title of the image to search for
     * @param string $filename The filename of the image to search for
     * 
     * @return int|false Returns the attachment ID if found, false otherwise
     *
     * @access private
     */
    private function MPT_check_existing_image( $title, $filename ) {
        // Clean the base filename (remove extension and numbers)
        $base_filename = preg_replace( '/[0-9]+\\./', '.', $filename );
        $base_filename = pathinfo( $base_filename, PATHINFO_FILENAME );
        // Search in media library
        $args = array(
            'post_type'      => 'attachment',
            'post_status'    => 'inherit',
            'posts_per_page' => 1,
            'meta_query'     => array(array(
                'key'     => '_wp_attached_file',
                'value'   => $base_filename,
                'compare' => 'LIKE',
            )),
        );
        $query = new WP_Query($args);
        if ( $query->have_posts() ) {
            // If image already exists, return its ID
            return $query->posts[0]->ID;
        }
        return false;
    }

    /**
     * Retrieve Image from Database, save it into Media Library, and attach it to the post as featured image
     *
     * @since    4.0.0
     */
    public function MPT_create_thumb(
        $id,
        $check_value_enable = 0,
        $check_post_type = 1,
        $check_category = 1,
        $rewrite_featured = 0,
        $get_only_thumb = false,
        $extracted_search_term = null,
        $api_chosen = null,
        $key_img_block = null,
        $avoid_revision = null,
        $include_datas = null,
        $button_autogenerate = false,
        // Deprecated since 7.1: all entry points use MPT_resolve_bank_chain().
        $source = 'manual'
    ) {
        $source = ( is_string( $source ) ? $source : 'manual' );
        if ( !in_array( $source, array_merge( array('manual'), $this->MPT_trusted_sources() ), true ) ) {
            $source = 'manual';
        }
        // Gutenberg searches are read-only and fire all banks in parallel.
        // The post lock is only needed when writing an image to a post.
        if ( true != $get_only_thumb ) {
            if ( !$this->MPT_begin_post_generation( $id ) ) {
                $log = $this->MPT_monolog_call();
                $log->info( 'Generation skipped: post lock held', array(
                    'post' => $id,
                ) );
                return false;
            }
        }
        try {
            // Launch logs
            $log = $this->MPT_monolog_call();
            $log->info( 'New generation starting', array(
                'post'   => $id,
                'source' => $source,
            ) );
            //Avoid revision post type
            if ( 'revision' == get_post_type( $id ) && true !== $avoid_revision ) {
                $log->error( 'This post is a revision. Avoided.', array(
                    'post' => $id,
                ) );
                return false;
            }
            // Settings (fresh installs have no saved option yet).
            $main_defaults = $this->MPT_default_options_main_settings( true );
            $main_settings = wp_parse_args( get_option( 'MPT_plugin_main_settings' ), $main_defaults );
            if ( empty( $main_settings['image_block'] ) || !is_array( $main_settings['image_block'] ) ) {
                $main_settings['image_block'] = $main_defaults['image_block'];
            }
            $default_block = ( isset( $main_defaults['image_block'][1] ) && is_array( $main_defaults['image_block'][1] ) ? $main_defaults['image_block'][1] : array(
                'image_location'  => 'featured',
                'based_on'        => 'title',
                'title_selection' => 'full_title',
                'selected_image'  => 'first_result',
                'api_chosen'      => 'default',
            ) );
            // one shot generation
            if ( null == $key_img_block ) {
                $key_img_block = array_key_first( $main_settings['image_block'] );
            }
            $img_block = array();
            if ( isset( $main_settings['image_block'][$key_img_block] ) && is_array( $main_settings['image_block'][$key_img_block] ) ) {
                $img_block = $main_settings['image_block'][$key_img_block];
            } else {
                $first = reset( $main_settings['image_block'] );
                if ( is_array( $first ) ) {
                    $img_block = $first;
                }
            }
            $img_block = wp_parse_args( $img_block, $default_block );
            if ( empty( $img_block ) ) {
                $log->error( 'Image block settings missing', array(
                    'post' => $id,
                ) );
                return false;
            }
            // Image location
            $image_location = ( !empty( $img_block['image_location'] ) ? $img_block['image_location'] : 'featured' );
            if ( "featured" !== $image_location ) {
                // Check if image generation into content is already done
                $content_post = get_post( $id );
                /*$image_generated = strpos( $content_post->post_content, 'mpt-img' ) ? true : false;
                		if( $image_generated ) {
                			return false;
                		}*/
            }
            // Check if thumbnail already exists
            if ( has_post_thumbnail( $id ) && $rewrite_featured == false && "featured" === $image_location ) {
                $log->error( 'Featured image already exists', array(
                    'post' => $id,
                ) );
                return false;
            }
            /* Action 'save_post' triggered when deleting posts. Check if post not trashed */
            if ( 'trash' == get_post_status( $id ) ) {
                $log->error( 'Post is in the trash', array(
                    'post' => $id,
                ) );
                return false;
            }
            if ( !$this->MPT_generation_user_is_allowed( $id, $source ) ) {
                $log->error( 'The user does not have sufficient rights', array(
                    'post'   => $id,
                    'source' => $source,
                ) );
                return false;
            }
            // Choosed Post types
            $post_types_default = $this->MPT_default_posts_types();
            $post_type_availables = $post_types_default['choosed_post_type'];
            $categories_availables = $post_types_default['choosed_categories'];
            /*
            		$post_type_availables  = ( ! empty( $posts_settings['choosed_post_type'] ) )  ? $posts_settings['choosed_post_type']  : '';
            		// Choosed Categories
            		$categories_availables = ( ! empty( $posts_settings['choosed_categories'] ) ) ? $posts_settings['choosed_categories'] : '';*/
            if ( !empty( $post_type_availables ) && $check_post_type ) {
                if ( !in_array( get_post_type( $id ), $post_type_availables ) ) {
                    $log->error( 'The post is not in selected post types', array(
                        'post' => $id,
                    ) );
                    return false;
                }
            }
            // Check if category match and is a post
            if ( !empty( $categories_availables ) && $check_category && 'post' == get_post_type( $id ) ) {
                if ( !in_category( $categories_availables, $id ) ) {
                    $log->error( 'The post is not in selected categories', array(
                        'post' => $id,
                    ) );
                    return false;
                }
            }
            $options = wp_parse_args( get_option( 'MPT_plugin_main_settings' ), $this->MPT_default_options_main_settings( TRUE ) );
            $options_banks = wp_parse_args( get_option( 'MPT_plugin_banks_settings' ), $this->MPT_default_options_banks_settings( TRUE ) );
            $options_cron = wp_parse_args( get_option( 'MPT_plugin_cron_settings' ), $this->MPT_default_options_cron_settings( TRUE ) );
            $options = array_merge( $options, $options_banks, $options_cron );
            if ( isset( $api_chosen ) ) {
                $options['api_chosen'] = $this->MPT_normalize_bank_slug( $api_chosen );
                $img_block['api_chosen'] = $options['api_chosen'];
            } else {
                $options['api_chosen'] = ( isset( $img_block['api_chosen'] ) ? $this->MPT_normalize_bank_slug( $img_block['api_chosen'] ) : 'default' );
                $img_block['api_chosen'] = $options['api_chosen'];
            }
            $executeElseBlock = true;
            $postcategories = get_the_category( $id );
            if ( $executeElseBlock ) {
                if ( TRUE == $get_only_thumb ) {
                    $search = $extracted_search_term;
                } elseif ( !empty( $img_block['based_on'] ) && $img_block['based_on'] == 'text_analyser' ) {
                    $content = get_the_content( '', false, $id );
                    $content = str_replace( "&nbsp;", ' ', $content );
                    // Get lang option
                    $selected_lang = $img_block['text_analyser_lang'];
                    $search = $this->get_extracted_term( $content, $selected_lang );
                    $log->info( 'Extracted search term', array(
                        'post'        => $id,
                        'search term' => $search,
                    ) );
                } else {
                    $search = get_post_field( 'post_title', $id, 'raw' );
                }
            }
            if ( isset( $img_block['title_selection'] ) && $img_block['title_selection'] == 'cut_title' && isset( $img_block['title_length'] ) ) {
                $length_title = (int) $img_block['title_length'] - 1;
                $search = preg_replace( '/((\\w+\\W*){' . $length_title . '}(\\w+))(.*)/', '${1}', $search );
            }
            if ( is_string( $extracted_search_term ) && '' !== trim( $extracted_search_term ) ) {
                $search = sanitize_text_field( $extracted_search_term );
            }
            if ( TRUE == $get_only_thumb ) {
                /* SET ALL PARAMETERS */
                $array_parameters = $this->MPT_Get_Parameters( $img_block, $options, $search );
                $api_url = $array_parameters['url'];
                unset($array_parameters['url']);
                if ( !isset( $api_url ) ) {
                    $log->error( 'API URL not provided', array(
                        'post' => $id,
                    ) );
                    return false;
                }
                $result_body = $this->MPT_Generate(
                    //$options['api_chosen'],
                    $img_block['api_chosen'],
                    $api_url,
                    $array_parameters,
                    $img_block['selected_image'],
                    $get_only_thumb,
                    $search
                );
                return $result_body;
            }
            $bank_chain = $this->MPT_resolve_bank_chain( $img_block, $options_banks );
            $log->info( 'Bank chain', array(
                'post'  => $id,
                'chain' => $bank_chain,
            ) );
            $result = false;
            foreach ( $bank_chain as $bank ) {
                $img_block['api_chosen'] = $bank;
                $options['api_chosen'] = $bank;
                $result = $this->MPT_Process_Image_Block(
                    $img_block,
                    $options,
                    $search,
                    $log,
                    $id
                );
                if ( false !== $result ) {
                    break;
                }
                $log->info( 'No results with ' . $bank, array(
                    'post' => $id,
                ) );
            }
            if ( false === $result ) {
                $log->info( 'No image found with all banks selected', array(
                    'post' => $id,
                ) );
                return false;
            }
            extract( $result );
            $compatibility = wp_parse_args( get_option( 'MPT_plugin_compatibility_settings' ), $this->MPT_default_options_compatibility_settings( TRUE ) );
            $path_parts = pathinfo( $url_results );
            $filename = $path_parts['basename'];
            $wp_upload_dir = wp_upload_dir();
            /* Get the good file extension */
            $filetype = array(
                'image/png',
                'image/jpeg',
                'image/gif',
                'image/bmp',
                'image/vnd.microsoft.icon',
                'image/tiff',
                'image/svg+xml',
                'image/svg+xml',
                'image/webp'
            );
            $extensions = array(
                'png',
                'jpg',
                'gif',
                'bmp',
                'ico',
                'tif',
                'svg',
                'svgz',
                'webp'
            );
            if ( isset( $file_media['headers']['content-type'] ) ) {
                $imgextension = str_replace(
                    $filetype,
                    $extensions,
                    $file_media['headers']['content-type'],
                    $count_extension
                );
                /* Default type if not found : jpg */
                if ( (int) $count_extension == 0 ) {
                    $imgextension = 'jpg';
                }
            } else {
                $imgextension = ( !empty( $path_parts['extension'] ) ? $path_parts['extension'] : 'jpg' );
            }
            $keywords_search = $search;
            $log->info( 'Search term', array(
                'post'        => $id,
                'Search term' => $keywords_search,
            ) );
            /* Image filename : title extension */
            $search = str_replace( '%', '', sanitize_title( $search ) );
            // Remove % for non-latin characters
            // Check if the 'resuse image' option is enabled.
            if ( isset( $main_settings['image_reuse'] ) && $main_settings['image_reuse'] == true ) {
                // Check if image file already exist if option is set
                $proposed_filename = sanitize_title( $search );
                $existing_image_id = $this->MPT_check_existing_image( get_the_title( $id ), $proposed_filename );
                if ( $existing_image_id ) {
                    // Use the existing image instead of downloading a new one
                    $log->info( 'Featured image with existing image (option set)', array(
                        'post'  => $id,
                        'image' => $existing_image_id,
                    ) );
                    set_post_thumbnail( $id, $existing_image_id );
                    do_action( 'mpt_after_create_thumb', $id, $existing_image_id );
                    return $existing_image_id;
                }
            }
            if ( $options['image_filename'] == 'date' ) {
                $current_time = current_time( 'Y-m-d' );
                $filename = wp_unique_filename( $wp_upload_dir['path'], $current_time . '.' . $imgextension );
            } elseif ( $options['image_filename'] == 'random' ) {
                $filename_rand = wp_rand( 1, 999999 );
                $filename = wp_unique_filename( $wp_upload_dir['path'], $filename_rand . '.' . $imgextension );
            } else {
                $filename = wp_unique_filename( $wp_upload_dir['path'], $search . '.' . $imgextension );
            }
            $folder = $wp_upload_dir['path'] . '/' . $filename;
            $has_tmp = !empty( $file_media['_mpt_tmp'] ) && is_readable( $file_media['_mpt_tmp'] );
            $has_body = isset( $file_media['body'] ) && '' !== $file_media['body'] && false !== $file_media['body'];
            if ( empty( $file_media['response']['code'] ) || $file_media['response']['code'] != '200' || !$has_tmp && !$has_body ) {
                $log->error( 'Problem with scrapping', array(
                    'post' => $id,
                ) );
                if ( $has_tmp ) {
                    @unlink( $file_media['_mpt_tmp'] );
                }
                return false;
            }
            if ( $has_tmp || $has_body ) {
                $png_jpg = false;
                if ( 'gptimage' === $img_block['api_chosen'] ) {
                    $gpt_format = ( !empty( $options['gptimage']['output_format'] ) ? $options['gptimage']['output_format'] : 'png' );
                    $png_jpg = 'jpeg' !== $gpt_format && !empty( $options['gptimage']['convert_jpg'] ) && 'true' === $options['gptimage']['convert_jpg'];
                }
                $do_flip = false;
                $do_crop = false;
                if ( $this->mpt_freemius()->is__premium_only() && $this->mpt_freemius()->can_use_premium_code() ) {
                    $do_flip = isset( $options['image_flip'] ) && true == $options['image_flip'];
                    $do_crop = isset( $options['image_crop'] ) && true == $options['image_crop'];
                }
                $attach_id = $this->MPT_sideload_image( $filename, $id, array(
                    'tmp'             => ( $has_tmp ? $file_media['_mpt_tmp'] : '' ),
                    'bytes'           => ( $has_tmp ? '' : $file_media['body'] ),
                    'title'           => $search,
                    'alt'             => $alt_img,
                    'caption'         => ( isset( $caption_img ) ? $caption_img : '' ),
                    'convert_png_jpg' => true == $png_jpg && 'gptimage' === $img_block['api_chosen'],
                    'flip'            => $do_flip,
                    'crop'            => $do_crop,
                    'overlay'         => $this->MPT_overlay_enabled( $options ),
                    'overlay_post_id' => $id,
                ) );
                if ( $do_flip && $attach_id ) {
                    $log->info( 'Image flipped horizontally', array(
                        'post' => $id,
                    ) );
                }
                if ( $do_crop && $attach_id ) {
                    $log->info( 'Image cropped by 10%', array(
                        'post' => $id,
                    ) );
                }
                if ( $attach_id ) {
                    $attach_data = wp_get_attachment_metadata( $attach_id );
                    if ( !is_array( $attach_data ) ) {
                        $attach_data = array();
                    }
                    $executeElseBlock = true;
                    $plugin_desactivated = false;
                    $missing_field = false;
                    // If a field plugin is disabled and should be used
                    if ( true === $plugin_desactivated || true === $missing_field ) {
                        return false;
                    }
                    if ( $executeElseBlock ) {
                        if ( "custom" === $image_location ) {
                            $tag = $img_block['image_custom_location_tag'];
                            $placement = $img_block['image_custom_location_placement'];
                            $position = $img_block['image_custom_location_position'];
                            $image_size = $img_block['image_custom_image_size'];
                            $content = $this->MPT_insert_content_image(
                                $content_post->post_content,
                                $attach_id,
                                $tag,
                                $placement,
                                $position,
                                $image_size
                            );
                        } else {
                            $log->info( 'Featured image added', array(
                                'post'  => $id,
                                'image' => $attach_id,
                            ) );
                            set_post_thumbnail( $id, $attach_id );
                        }
                    }
                    // Link the media to the post
                    $media_link_uploaded_to = wp_update_post( array(
                        'ID'          => $attach_id,
                        'post_parent' => $id,
                    ), true );
                    if ( "custom" === $image_location ) {
                        // Array of post category ids
                        $arr_post_category = array();
                        foreach ( $postcategories as $postcategory ) {
                            $arr_post_category[] = (int) $postcategory->cat_ID;
                        }
                        /*
                         * Remove save_post Action to avoid infinite loop.
                         * cf https://developer.wordpress.org/reference/hooks/save_post/#avoiding-infinite-loops
                         * Use "remove_all_actions" instead of "remove_action". Otherwise the log file bug
                         * remove_action( 'save_post', array( &$this, 'MPT_create_thumb' ) );
                         */
                        $gen = self::MPT_instance( $this->plugin_name, $this->version );
                        remove_action( 'save_post', array($gen, 'MPT_check_post_type'), 10 );
                        wp_update_post( array(
                            'ID'           => $id,
                            'post_content' => $content,
                        ) );
                        add_action(
                            'save_post',
                            array($gen, 'MPT_check_post_type'),
                            10,
                            3
                        );
                        $log->info( 'Image added into the post', array(
                            'post'  => $id,
                            'image' => $attach_id,
                        ) );
                    }
                    do_action( 'mpt_after_create_thumb', $id, $attach_id );
                    $this->MPT_remember_source_url( $id, $url_results );
                    if ( true === $include_datas ) {
                        return array(
                            'id'             => $attach_id,
                            'keyword'        => $keywords_search,
                            'img_resolution' => (( isset( $attach_data['width'] ) ? $attach_data['width'] : '' )) . 'x' . (( isset( $attach_data['height'] ) ? $attach_data['height'] : '' )) . 'px',
                            'img_size'       => $this->MPT_get_image_size_in_bytes( $attach_id ),
                            'api_chosen'     => $img_block['api_chosen'],
                        );
                    } else {
                        return $attach_id;
                    }
                }
            }
        } catch ( \Throwable $e ) {
            $log = $this->MPT_monolog_call();
            $log->error( 'Generation aborted', array(
                'post'  => $id,
                'error' => $e->getMessage(),
                'file'  => $e->getFile(),
                'line'  => $e->getLine(),
            ) );
            return false;
        } finally {
            $this->MPT_end_post_generation( $id );
        }
    }

    /**
     * Generate an image and handle related errors
     * 
     * @param array $img_block Image block settings
     * @param array $options Plugin options
     * @param string $search Search term
     * @param object $log Logger instance
     * @param int $id Post ID
     * @return array|false Returns generated image data or false on failure
     */
    private function MPT_Process_Image_Block(
        $img_block,
        $options,
        $search,
        $log,
        $id
    ) {
        if ( empty( $img_block['selected_image'] ) ) {
            $img_block['selected_image'] = 'first_result';
        }
        $bank = ( isset( $img_block['api_chosen'] ) ? $this->MPT_normalize_bank_slug( $img_block['api_chosen'] ) : '' );
        if ( in_array( $bank, $this->MPT_local_bank_slugs(), true ) ) {
            return $this->MPT_process_local_bank( $bank, $id, $log );
        }
        // Set all parameters
        $array_parameters = $this->MPT_Get_Parameters( $img_block, $options, $search );
        $api_url = ( isset( $array_parameters['url'] ) ? $array_parameters['url'] : null );
        unset($array_parameters['url']);
        // Check if API URL is provided
        if ( !$api_url ) {
            $log->error( 'API URL not provided', array(
                'post' => $id,
            ) );
            return false;
        }
        $generated = $this->MPT_Generate(
            $img_block['api_chosen'],
            $api_url,
            $array_parameters,
            $img_block['selected_image'],
            false,
            $search,
            $id
        );
        if ( !is_array( $generated ) || count( $generated ) < 2 ) {
            $log->error( 'No results', array(
                'post' => $id,
            ) );
            return false;
        }
        list( $url_results, $file_media, $alt_img, $caption_img ) = $generated;
        if ( empty( $url_results ) || empty( $file_media ) ) {
            $log->error( 'No results', array(
                'post' => $id,
            ) );
            return false;
        }
        // Return the generated image data
        return compact(
            'url_results',
            'file_media',
            'alt_img',
            'caption_img'
        );
    }

    /**
     * Content / default-image banks (no remote search).
     *
     * @since 7.0.0
     * @param string $bank content|fallback.
     * @param int    $id   Post ID.
     * @param object $log  Logger.
     * @return array|false
     */
    private function MPT_process_local_bank( $bank, $id, $log ) {
        $attach_id = 0;
        $url = '';
        if ( 'content' === $bank ) {
            $found = $this->MPT_first_content_image( $id );
            if ( !$found ) {
                $log->info( 'No image in post content', array(
                    'post' => $id,
                ) );
                return false;
            }
            if ( !empty( $found['attach_id'] ) ) {
                $attach_id = (int) $found['attach_id'];
            } elseif ( !empty( $found['url'] ) ) {
                $url = $found['url'];
            }
        } else {
            $banks = wp_parse_args( get_option( 'MPT_plugin_banks_settings' ), $this->MPT_default_options_banks_settings( true ) );
            $attach_id = ( !empty( $banks['fallback']['attachment_id'] ) ? (int) $banks['fallback']['attachment_id'] : 0 );
            if ( $attach_id < 1 || !wp_attachment_is_image( $attach_id ) ) {
                $log->info( 'Default image is not set', array(
                    'post' => $id,
                ) );
                return false;
            }
        }
        $tmp = '';
        if ( $attach_id > 0 ) {
            $file = get_attached_file( $attach_id );
            if ( !$file || !is_readable( $file ) ) {
                return false;
            }
            $tmp = wp_tempnam( basename( $file ) );
            if ( !$tmp || !@copy( $file, $tmp ) ) {
                return false;
            }
            $url = wp_get_attachment_url( $attach_id );
        } elseif ( $url ) {
            $tmp = $this->MPT_download_to_temp( $url );
            if ( !$tmp ) {
                return false;
            }
        } else {
            return false;
        }
        $alt_img = ( $attach_id ? (string) get_post_meta( $attach_id, '_wp_attachment_image_alt', true ) : '' );
        $caption_img = '';
        $file_media = array(
            'response' => array(
                'code' => 200,
            ),
            '_mpt_tmp' => $tmp,
            'headers'  => array(
                'content-type' => 'image/jpeg',
            ),
        );
        $url_results = ( $url ? $url : (string) $attach_id );
        $log->info( 'Local bank used', array(
            'post' => $id,
            'bank' => $bank,
        ) );
        return compact(
            'url_results',
            'file_media',
            'alt_img',
            'caption_img'
        );
    }

    /**
     * Preview search results for a bank without attaching an image.
     *
     * @since 7.1.0
     * @param string $bank     Bank slug.
     * @param string $search   Keyword.
     * @param bool   $allow_ai Whether the user confirmed an AI generation.
     * @return array|false List of items with url/title, or false.
     */
    public function MPT_preview_search_results( $bank, $search, $allow_ai = false ) {
        $bank = $this->MPT_normalize_bank_slug( $bank );
        if ( 'content' === $bank ) {
            return array(
                'ai'      => false,
                'local'   => true,
                'items'   => array(),
                'message' => __( 'This bank does not support keyword preview. During generation it uses the first image already in the post.', 'magic-post-thumbnail' ),
            );
        }
        if ( 'fallback' === $bank ) {
            $banks = wp_parse_args( get_option( 'MPT_plugin_banks_settings' ), $this->MPT_default_options_banks_settings( true ) );
            $aid = ( !empty( $banks['fallback']['attachment_id'] ) ? (int) $banks['fallback']['attachment_id'] : 0 );
            $url = ( $aid ? wp_get_attachment_image_url( $aid, 'medium' ) : '' );
            if ( !$url ) {
                return array(
                    'ai'      => false,
                    'local'   => true,
                    'items'   => array(),
                    'message' => __( 'Choose a default image in the settings on the left.', 'magic-post-thumbnail' ),
                );
            }
            return array(
                'ai'    => false,
                'items' => array(array(
                    'url'   => $url,
                    'title' => '',
                )),
            );
        }
        $is_ai = in_array( $bank, $this->MPT_ai_bank_slugs(), true );
        if ( $is_ai && !$allow_ai ) {
            return array(
                'ai'            => true,
                'needs_confirm' => true,
                'items'         => array(),
            );
        }
        $options = array_merge( wp_parse_args( get_option( 'MPT_plugin_main_settings' ), $this->MPT_default_options_main_settings( true ) ), wp_parse_args( get_option( 'MPT_plugin_banks_settings' ), $this->MPT_default_options_banks_settings( true ) ) );
        $img_block = array(
            'api_chosen'     => $bank,
            'selected_image' => 'first_result',
        );
        if ( !empty( $options['image_block'] ) && is_array( $options['image_block'] ) ) {
            $first = reset( $options['image_block'] );
            if ( is_array( $first ) ) {
                foreach ( array('ai_style', 'ai_style_custom', 'ai_no_text') as $style_key ) {
                    if ( isset( $first[$style_key] ) ) {
                        $img_block[$style_key] = $first[$style_key];
                    }
                }
            }
        }
        $array_parameters = $this->MPT_Get_Parameters( $img_block, $options, $search );
        if ( empty( $array_parameters['url'] ) ) {
            return false;
        }
        $api_url = $array_parameters['url'];
        unset($array_parameters['url']);
        $body = $this->MPT_Generate(
            $bank,
            $api_url,
            $array_parameters,
            'first_result',
            true,
            $search
        );
        if ( false === $body || !is_array( $body ) ) {
            return false;
        }
        if ( $is_ai ) {
            $ai_item = $this->MPT_preview_ai_item( $bank, $body );
            if ( false === $ai_item ) {
                return false;
            }
            return array(
                'ai'    => true,
                'items' => array($ai_item),
            );
        }
        $items = array();
        if ( 'flickr' === $bank && !empty( $body['photos'] ) && is_array( $body['photos'] ) ) {
            foreach ( $body['photos'] as $photo ) {
                if ( empty( $photo['url'] ) ) {
                    continue;
                }
                $items[] = array(
                    'url'   => $photo['url'],
                    'title' => ( isset( $photo['title'] ) ? $photo['title'] : '' ),
                );
                if ( count( $items ) >= 3 ) {
                    break;
                }
            }
            return array(
                'ai'    => false,
                'items' => $items,
            );
        }
        $rows = array();
        if ( !empty( $body['value'] ) && is_array( $body['value'] ) ) {
            $rows = $body['value'];
        } elseif ( !empty( $body['items'] ) && is_array( $body['items'] ) ) {
            $rows = $body['items'];
        } elseif ( !empty( $body['hits'] ) && is_array( $body['hits'] ) ) {
            $rows = $body['hits'];
        } elseif ( !empty( $body['results'] ) && is_array( $body['results'] ) ) {
            $rows = $body['results'];
        } elseif ( !empty( $body['photos'] ) && is_array( $body['photos'] ) ) {
            $rows = $body['photos'];
        }
        foreach ( $rows as $row ) {
            if ( !is_array( $row ) ) {
                continue;
            }
            $url = '';
            $title = '';
            if ( !empty( $row['src']['medium'] ) ) {
                $url = $row['src']['medium'];
            } elseif ( !empty( $row['src']['small'] ) ) {
                $url = $row['src']['small'];
            } elseif ( !empty( $row['src']['tiny'] ) ) {
                $url = $row['src']['tiny'];
            } elseif ( !empty( $row['urls']['small'] ) ) {
                $url = $row['urls']['small'];
            } elseif ( !empty( $row['urls']['thumb'] ) ) {
                $url = $row['urls']['thumb'];
            } elseif ( !empty( $row['thumbnailUrl'] ) ) {
                $url = $row['thumbnailUrl'];
            } elseif ( !empty( $row['thumbnail'] ) ) {
                $url = $row['thumbnail'];
            } elseif ( !empty( $row['previewURL'] ) ) {
                $url = $row['previewURL'];
            } elseif ( !empty( $row['contentUrl'] ) ) {
                $url = $row['contentUrl'];
            } elseif ( !empty( $row['snippet']['thumbnails']['medium']['url'] ) ) {
                $url = $row['snippet']['thumbnails']['medium']['url'];
            } elseif ( !empty( $row['link'] ) ) {
                $url = $row['link'];
            } elseif ( !empty( $row['url'] ) && is_string( $row['url'] ) && $this->MPT_preview_url_is_image( $row['url'] ) ) {
                $url = $row['url'];
            }
            if ( !empty( $row['title'] ) ) {
                $title = $row['title'];
            } elseif ( 'pexels' !== $bank && !empty( $row['alt'] ) ) {
                $title = $row['alt'];
            } elseif ( !empty( $row['snippet']['title'] ) ) {
                $title = $row['snippet']['title'];
            }
            if ( '' === $url ) {
                continue;
            }
            $items[] = array(
                'url'   => $url,
                'title' => $title,
            );
            if ( count( $items ) >= 3 ) {
                break;
            }
        }
        return array(
            'ai'    => false,
            'items' => $items,
        );
    }

    /**
     * Whether a URL looks like a direct image, not a gallery HTML page.
     *
     * Pexels `url` is https://www.pexels.com/photo/... (a page). The file is in `src`.
     *
     * @since 7.1.0
     * @param string $url URL.
     * @return bool
     */
    private function MPT_preview_url_is_image( $url ) {
        $host = strtolower( (string) wp_parse_url( $url, PHP_URL_HOST ) );
        $path = (string) wp_parse_url( $url, PHP_URL_PATH );
        if ( '' === $host ) {
            return false;
        }
        if ( preg_match( '/(^|\\.)pexels\\.com$/', $host ) && 0 !== strpos( $host, 'images.' ) ) {
            return false;
        }
        if ( preg_match( '/(^|\\.)unsplash\\.com$/', $host ) && 0 !== strpos( $host, 'images.' ) ) {
            return false;
        }
        if ( preg_match( '/\\/photo\\//', $path ) && !preg_match( '/\\.(jpe?g|png|gif|webp)(\\?|$)/i', $path ) ) {
            return false;
        }
        return true;
    }

    /**
     * Build a single preview item from an AI bank API payload.
     *
     * @since 7.1.0
     * @param string $bank Bank slug.
     * @param array  $body Decoded API body.
     * @return array|false
     */
    private function MPT_preview_ai_item( $bank, $body ) {
        $url = '';
        if ( 'gemini' === $bank ) {
            $b64 = $this->MPT_gemini_extract_b64( $body );
            if ( $b64 ) {
                $url = 'data:image/png;base64,' . $b64;
            }
        } elseif ( 'gptimage' === $bank ) {
            if ( !empty( $body['data'][0]['b64_json'] ) ) {
                $url = 'data:image/png;base64,' . $body['data'][0]['b64_json'];
            } elseif ( !empty( $body['data'][0]['url'] ) ) {
                $url = $body['data'][0]['url'];
            }
        } elseif ( 'stability' === $bank ) {
            if ( !empty( $body['image'] ) && is_string( $body['image'] ) ) {
                $url = 'data:image/png;base64,' . $body['image'];
            }
        } elseif ( 'replicate' === $bank || 'flux2' === $bank ) {
            if ( !empty( $body['output'] ) ) {
                $output = $body['output'];
                if ( is_array( $output ) ) {
                    $output = reset( $output );
                }
                if ( is_string( $output ) ) {
                    $url = $output;
                }
            }
        } elseif ( !empty( $body['data'][0]['url'] ) ) {
            $url = $body['data'][0]['url'];
        }
        if ( '' === $url ) {
            return false;
        }
        return array(
            'url'   => $url,
            'title' => '',
        );
    }

    /**
     * Get Image size in ko
     *
     * @since 6.0.0
     */
    private function MPT_get_image_size_in_bytes( $attachment_id ) {
        // Full path of the image
        $image_path = get_attached_file( $attachment_id );
        if ( $image_path && file_exists( $image_path ) ) {
            // File size in bytes
            $image_size = filesize( $image_path );
        }
        // Convert to kilobytes
        $image_size_kb = round( $image_size / 1024, 2 );
        return $image_size_kb . 'ko';
    }

    /**
     * Insert an image before or after a specific Gutenberg block or Classic tag.
     *
     * Gutenberg uses parse_blocks(); Classic keeps HTML regex. At most two attempts
     * (Gutenberg then Classic). If nothing matches, the image is appended.
     *
     * @since 5.2.0
     */
    private function MPT_insert_content_image(
        $content,
        $attach_id,
        $tag = 'p',
        $placement = 'after',
        $position = '1',
        $image_size = 'large'
    ) {
        $classic_editor = $this->MPT_is_using_classic_editor();
        $log = $this->MPT_monolog_call();
        if ( !$classic_editor ) {
            $inserted = $this->MPT_insert_content_image_gutenberg(
                $content,
                $attach_id,
                $tag,
                $placement,
                $position,
                $image_size
            );
            if ( false !== $inserted ) {
                return $inserted;
            }
            $log->info( 'Gutenberg insert missed target, falling back to classic HTML' );
        }
        $classic = $this->MPT_insert_content_image_classic(
            $content,
            $attach_id,
            $tag,
            $placement,
            $position,
            $image_size
        );
        if ( false !== $classic ) {
            return $classic;
        }
        $log->info( 'No matching tag for content image, appending at end' );
        if ( $classic_editor ) {
            return $content . "\n\n" . $this->MPT_classic_image_markup( $attach_id, $image_size );
        }
        $blocks = parse_blocks( (string) $content );
        $blocks[] = $this->MPT_make_gutenberg_image_block( $attach_id, $image_size );
        return serialize_blocks( $blocks );
    }

    /**
     * Gutenberg image block array for parse_blocks / serialize_blocks.
     *
     * @param int    $attach_id  Attachment ID.
     * @param string $image_size Size slug.
     * @return array
     */
    private function MPT_make_gutenberg_image_block( $attach_id, $image_size ) {
        $image_src = wp_get_attachment_image_src( $attach_id, $image_size, false );
        $src = ( is_array( $image_src ) && !empty( $image_src[0] ) ? $image_src[0] : '' );
        $alt_text = get_post_meta( $attach_id, '_wp_attachment_image_alt', true );
        $image = '<img src="' . esc_url( $src ) . '" alt="' . esc_attr( $alt_text ) . '" class="wp-image-' . esc_attr( $attach_id ) . '"/>';
        $options = wp_parse_args( get_option( 'MPT_plugin_main_settings' ), $this->MPT_default_options_main_settings( true ) );
        if ( isset( $options['enable_caption'] ) && 'enable' === $options['enable_caption'] ) {
            $caption_text = wp_get_attachment_caption( $attach_id );
            if ( $caption_text ) {
                $image .= '<figcaption class="wp-element-caption">' . esc_html( $caption_text ) . '</figcaption>';
            }
        }
        $html = '<figure class="wp-block-image size-' . esc_attr( $image_size ) . '">' . $image . '</figure>';
        return array(
            'blockName'    => 'core/image',
            'attrs'        => array(
                'id'              => (int) $attach_id,
                'sizeSlug'        => $image_size,
                'linkDestination' => 'none',
            ),
            'innerBlocks'  => array(),
            'innerHTML'    => $html,
            'innerContent' => array($html),
        );
    }

    /**
     * Map settings tag to a core block name.
     *
     * @param string $tag p|a|heading.
     * @return string
     */
    private function MPT_gutenberg_target_block_name( $tag ) {
        if ( 'p' === $tag ) {
            return 'core/paragraph';
        }
        if ( 'a' === $tag ) {
            return 'core/html';
        }
        return 'core/heading';
    }

    /**
     * Count blocks of a given name, including innerBlocks.
     *
     * @param array  $blocks Blocks.
     * @param string $name   Block name.
     * @return int
     */
    private function MPT_count_blocks_of_name( $blocks, $name ) {
        $n = 0;
        foreach ( $blocks as $block ) {
            if ( !empty( $block['blockName'] ) && $block['blockName'] === $name ) {
                $n++;
            }
            if ( !empty( $block['innerBlocks'] ) && is_array( $block['innerBlocks'] ) ) {
                $n += $this->MPT_count_blocks_of_name( $block['innerBlocks'], $name );
            }
        }
        return $n;
    }

    /**
     * Insert an image block before/after the Nth matching block (recursive).
     *
     * @param array  $blocks      Blocks (by ref).
     * @param string $name        Target block name.
     * @param array  $image_block Image block.
     * @param string $placement   before|after.
     * @param int    $target      1-based index.
     * @param int    $seen        Running count (by ref).
     * @return bool
     */
    private function MPT_insert_block_at_target(
        &$blocks,
        $name,
        $image_block,
        $placement,
        $target,
        &$seen
    ) {
        $out = array();
        $did = false;
        foreach ( $blocks as $block ) {
            $is_target = !empty( $block['blockName'] ) && $block['blockName'] === $name;
            if ( $is_target ) {
                $seen++;
                if ( $seen === $target && 'before' === $placement ) {
                    $out[] = $image_block;
                    $did = true;
                }
            }
            if ( !empty( $block['innerBlocks'] ) && is_array( $block['innerBlocks'] ) ) {
                $inner = $block['innerBlocks'];
                $inner_did = $this->MPT_insert_block_at_target(
                    $inner,
                    $name,
                    $image_block,
                    $placement,
                    $target,
                    $seen
                );
                $block['innerBlocks'] = $inner;
                $did = $did || $inner_did;
            }
            $out[] = $block;
            if ( $is_target && $seen === $target && 'after' === $placement ) {
                $out[] = $image_block;
                $did = true;
            }
        }
        $blocks = $out;
        return $did;
    }

    /**
     * Insert via parse_blocks / serialize_blocks.
     *
     * @return string|false
     */
    private function MPT_insert_content_image_gutenberg(
        $content,
        $attach_id,
        $tag,
        $placement,
        $position,
        $image_size
    ) {
        if ( !function_exists( 'parse_blocks' ) || !function_exists( 'serialize_blocks' ) ) {
            return false;
        }
        $blocks = parse_blocks( (string) $content );
        $name = $this->MPT_gutenberg_target_block_name( $tag );
        $total = $this->MPT_count_blocks_of_name( $blocks, $name );
        if ( $total < 1 ) {
            return false;
        }
        $target = ( 'last' === $position ? $total : (int) $position );
        if ( $target < 1 || $target > $total ) {
            return false;
        }
        $image_block = $this->MPT_make_gutenberg_image_block( $attach_id, $image_size );
        $seen = 0;
        $did = $this->MPT_insert_block_at_target(
            $blocks,
            $name,
            $image_block,
            $placement,
            $target,
            $seen
        );
        if ( !$did ) {
            return false;
        }
        return serialize_blocks( $blocks );
    }

    /**
     * Classic Editor HTML markup for an inserted image.
     *
     * @param int    $attach_id  Attachment ID.
     * @param string $image_size Size slug.
     * @return string
     */
    private function MPT_classic_image_markup( $attach_id, $image_size ) {
        $image = wp_get_attachment_image(
            $attach_id,
            $image_size,
            false,
            array(
                'class' => 'wp-image-' . $attach_id,
            )
        );
        return '<p>' . $image . '</p>';
    }

    /**
     * Classic Editor insertion (HTML regex). One attempt; wpautop once if needed.
     *
     * @return string|false
     */
    private function MPT_insert_content_image_classic(
        $content,
        $attach_id,
        $tag,
        $placement,
        $position,
        $image_size
    ) {
        $image_block = $this->MPT_classic_image_markup( $attach_id, $image_size );
        $start_pattern = '<' . $tag;
        $end_pattern = '</' . $tag . '>';
        $pattern = '/(' . preg_quote( $start_pattern, '/' ) . '.*?' . preg_quote( $end_pattern, '/' ) . ')/s';
        $parts = preg_split(
            $pattern,
            $content,
            -1,
            PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY
        );
        $parts = array_values( array_filter( $parts, function ( $value ) {
            return trim( $value ) !== '';
        } ) );
        if ( 'p' === $tag && count( $parts ) < 2 ) {
            $content = wpautop( $content );
            $parts = preg_split(
                $pattern,
                $content,
                -1,
                PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY
            );
            $parts = array_values( array_filter( $parts, function ( $value ) {
                return trim( $value ) !== '';
            } ) );
        }
        $new_content = '';
        $counter = 0;
        $total_tags = substr_count( $content, $start_pattern );
        $match = false;
        foreach ( $parts as $part ) {
            $contains_tag = preg_match( $pattern, $part );
            if ( 'before' === $placement && $contains_tag ) {
                $counter++;
                if ( 'last' === $position && $counter === $total_tags || (string) $counter === (string) $position ) {
                    $new_content .= '</p>' . $image_block . '<p>';
                    $match = true;
                }
            }
            $new_content .= $part;
            if ( 'after' === $placement && $contains_tag ) {
                $counter++;
                if ( 'last' === $position && $counter === $total_tags || (string) $counter === (string) $position ) {
                    $new_content .= '</p>' . $image_block . '<p>';
                    $match = true;
                }
            }
        }
        return ( $match ? $new_content : false );
    }

    /**
     * Extract a specific paragraph from the post content.
     *
     * @since    6.0.0
     */
    private function MPT_extract_adjacent_element(
        $content,
        $element = 'p',
        $element_number = 1,
        $direction = 'text_analyser_previous_paragraph'
    ) {
        // Define the pattern to match the specified element
        $pattern = '/<(' . preg_quote( $element, '/' ) . ')(.*?)>(.*?)<\\/\\1>/s';
        // Find all matches of the specified element
        preg_match_all( $pattern, $content, $matches );
        // Check if the element_number is "last"
        if ( $element_number === 'last' ) {
            // Ensure the index is valid and points to the last element
            $index = count( $matches[0] ) - 1;
        } else {
            // Convert to zero-based index
            $index = $element_number - 1;
        }
        // Check if we have a valid index
        if ( isset( $matches[0][$index] ) ) {
            if ( $direction === 'text_analyser_previous_paragraph' || $element_number === 'last' ) {
                // Get the position of the target element in the content
                if ( 'p' == $element ) {
                    $target_position = strpos( $content, $matches[0][$index + 1] );
                } else {
                    $target_position = strpos( $content, $matches[0][$index] );
                }
                // Get the content before the target element
                $content_before = substr( $content, 0, $target_position );
                // Match all paragraphs before the target element
                $paragraph_pattern = '/<p\\b[^>]*>(.*?)<\\/p>/s';
                preg_match_all( $paragraph_pattern, $content_before, $paragraph_matches );
                // Remove too shorts paragraphs. For exemple "<p>&nbsp;</p>"
                if ( strlen( $paragraph_matches[0][0] ) < 15 ) {
                    unset($paragraph_matches[0][0]);
                    $paragraph_matches[0] = array_values( $paragraph_matches[0] );
                }
                // Return the last paragraph found before the target element
                if ( !empty( $paragraph_matches[0] ) ) {
                    // Return only the last paragraph, cleaned of HTML tags
                    return ( is_string( $paragraph_matches[0][count( $paragraph_matches[0] ) - 1] ) ? trim( strip_tags( $paragraph_matches[0][count( $paragraph_matches[0] ) - 1] ) ) : '' );
                }
            } elseif ( $direction === 'text_analyser_next_paragraph' ) {
                // Get the position of the target element in the content
                $target_position = strpos( $content, $matches[0][$index] );
                // Get the content before the target element
                $content_before = substr( $content, 0, $target_position );
                // Get the content after the target element
                $content_after = substr( $content, $target_position + strlen( $matches[0][$index] ) );
                // Match the first paragraph after the target element
                $paragraph_pattern = '/<p\\b[^>]*>(.*?)<\\/p>/s';
                preg_match_all( $paragraph_pattern, $content_after, $paragraph_matches );
                // Remove too shorts paragraphs. For exemple "<p>&nbsp;</p>"
                if ( strlen( $paragraph_matches[0][0] ) < 15 ) {
                    unset($paragraph_matches[0][0]);
                    $paragraph_matches[0] = array_values( $paragraph_matches[0] );
                }
                // Return the first paragraph found after the target element
                if ( !empty( $paragraph_matches[0] ) ) {
                    return ( is_string( $paragraph_matches[0][0] ) ? trim( strip_tags( $paragraph_matches[0][0] ) ) : '' );
                }
            }
        }
        return '';
        // Return empty if not found
    }

    /**
     * Check Classic Editor plugin
     *
     * @since    5.2.0
     */
    private function MPT_is_using_classic_editor() {
        // Include the is_plugin_active function if it's not already defined
        if ( !function_exists( 'is_plugin_active' ) ) {
            include_once ABSPATH . 'wp-admin/includes/plugin.php';
        }
        // Check if the Classic Editor plugin is active
        return is_plugin_active( 'classic-editor/classic-editor.php' );
    }

    /**
     * Get all settings from user
     *
     * @since    4.0.0
     */
    private function MPT_Get_Parameters( $img_block, $options, $search ) {
        if ( isset( $img_block['api_chosen'] ) && in_array( $this->MPT_normalize_bank_slug( $img_block['api_chosen'] ), $this->MPT_ai_bank_slugs(), true ) ) {
            $search = $this->MPT_build_ai_prompt( $search, $img_block );
        }
        /* BING IMAGES PARAMETERS */
        if ( $img_block['api_chosen'] == 'bing' ) {
            $country = ( !empty( $options['bing']['search_country'] ) ? $options['bing']['search_country'] : 'en' );
            $img_color = ( !empty( $options['bing']['img_color'] ) ? $options['bing']['img_color'] : '' );
            $imgsz = ( !empty( $options['bing']['imgsz'] ) ? $options['bing']['imgsz'] : '' );
            $format = ( !empty( $options['bing']['format'] ) ? $options['bing']['format'] : '' );
            $imgtype = ( !empty( $options['bing']['imgtype'] ) ? $options['bing']['imgtype'] : '' );
            $rights = ( !empty( $options['bing']['rights'] ) ? $options['bing']['rights'] : '' );
            $safe = ( !empty( $options['bing']['safe'] ) ? $options['bing']['safe'] : 'medium' );
            // Old API option. Replace value for safe
            if ( $safe == 'moderate' ) {
                $safe = 'medium';
            }
            // Remove very special characters
            $search = str_replace( '…', '', $search );
            $safe_map = array(
                'off'      => 'off',
                'medium'   => 'moderate',
                'moderate' => 'moderate',
                'high'     => 'strict',
                'activate' => 'strict',
            );
            $bing_safe = ( isset( $safe_map[$safe] ) ? $safe_map[$safe] : 'strict' );
            // HTML scrape without Bing cookies ignores adlt; keep Off, otherwise force strict.
            if ( 'off' !== $bing_safe ) {
                $bing_safe = 'strict';
            }
            $qft = $this->MPT_bing_build_qft( array(
                'rights'    => $rights,
                'imgtype'   => $imgtype,
                'imgsz'     => $imgsz,
                'format'    => $format,
                'img_color' => $img_color,
            ) );
            // Bing serves unrelated tiles unless the request looks like the Images UI
            // (form/tsc). safeSearch is the Azure API name; the HTML endpoint uses adlt.
            $array_parameters = array(
                'url'     => 'https://www.bing.com/images/search',
                'q'       => $search,
                'first'   => '1',
                'form'    => 'HDRSC2',
                'tsc'     => 'ImageHoverTitle',
                'adlt'    => $bing_safe,
                'setlang' => $country,
            );
            if ( '' !== $qft ) {
                $array_parameters['qft'] = $qft;
            }
        } elseif ( $img_block['api_chosen'] == 'google_image' ) {
            if ( empty( $options['googleimage']['cxid'] ) || empty( $options['googleimage']['apikey'] ) ) {
                return false;
            }
            $country = ( !empty( $options['googleimage']['search_country'] ) ? $options['googleimage']['search_country'] : 'en' );
            $img_color = ( !empty( $options['googleimage']['img_color'] ) ? $options['googleimage']['img_color'] : '' );
            $filetype = ( !empty( $options['googleimage']['filetype'] ) ? $options['googleimage']['filetype'] : '' );
            $imgsz = ( !empty( $options['googleimage']['imgsz'] ) ? $options['googleimage']['imgsz'] : 'large' );
            $imgtype = ( !empty( $options['googleimage']['imgtype'] ) ? $options['googleimage']['imgtype'] : '' );
            $safe = ( !empty( $options['googleimage']['safe'] ) ? $options['googleimage']['safe'] : 'medium' );
            // Old API option. Replace value for safe
            if ( $safe == 'moderate' ) {
                $safe = 'medium';
            }
            if ( isset( $options['googleimage']['rights'] ) && !empty( $options['googleimage']['rights'] ) ) {
                $rights = '(';
                $last_right = array_keys( $options['googleimage']['rights'] );
                $last_right = end( $last_right );
                foreach ( $options['googleimage']['rights'] as $rights_into_searching ) {
                    $rights .= $rights_into_searching;
                    if ( $rights_into_searching != $last_right ) {
                        $rights .= '|';
                    }
                }
                $rights .= ')';
            } else {
                $rights = '';
            }
            $array_parameters = array(
                'url'      => 'https://www.googleapis.com/customsearch/v1',
                'imgSize'  => $imgsz,
                'rights'   => $rights,
                'imgtype'  => $imgtype,
                'hl'       => $country,
                'filetype' => $filetype,
                'safe'     => $safe,
                'rsz'      => '3',
                'q'        => urlencode( $search ),
                'userip'   => $_SERVER['SERVER_ADDR'],
                'cx'       => trim( $options['googleimage']['cxid'] ),
                'key'      => trim( $options['googleimage']['apikey'] ),
            );
            if ( !empty( $img_color ) ) {
                $array_parameters['imgDominantColor'] = $img_color;
            }
        } elseif ( $img_block['api_chosen'] == 'gptimage' ) {
            $api_key = ( !empty( $options['gptimage']['apikey'] ) ? $options['gptimage']['apikey'] : '' );
            $img_size = ( !empty( $options['gptimage']['imgsize'] ) ? $options['gptimage']['imgsize'] : '1024x1024' );
            $quality = ( !empty( $options['gptimage']['quality'] ) ? $options['gptimage']['quality'] : 'medium' );
            $output_format = ( !empty( $options['gptimage']['output_format'] ) ? $options['gptimage']['output_format'] : 'png' );
            $moderation = ( !empty( $options['gptimage']['moderation'] ) ? $options['gptimage']['moderation'] : 'auto' );
            if ( 'png' === $output_format && !empty( $options['gptimage']['convert_jpg'] ) && 'true' === $options['gptimage']['convert_jpg'] ) {
                $output_format = 'jpeg';
            }
            $allowed_sizes = array(
                'auto',
                '1024x1024',
                '1536x1024',
                '1024x1536',
                '2048x2048',
                '2048x1152'
            );
            if ( !in_array( $img_size, $allowed_sizes, true ) ) {
                $img_size = '1024x1024';
            }
            if ( !in_array( $quality, array(
                'auto',
                'low',
                'medium',
                'high'
            ), true ) ) {
                $quality = 'medium';
            }
            if ( !in_array( $output_format, array('png', 'jpeg', 'webp'), true ) ) {
                $output_format = 'png';
            }
            if ( !in_array( $moderation, array('auto', 'low'), true ) ) {
                $moderation = 'auto';
            }
            $body_gpt = array(
                'model'         => 'gpt-image-2',
                'prompt'        => $search,
                'n'             => 1,
                'size'          => $img_size,
                'quality'       => $quality,
                'output_format' => $output_format,
                'moderation'    => $moderation,
            );
            $array_parameters = array(
                'url'         => 'https://api.openai.com/v1/images/generations',
                'redirection' => 2,
                'method'      => 'POST',
                'timeout'     => 90,
                'headers'     => array(
                    'Content-Type'  => 'application/json',
                    'Authorization' => 'Bearer ' . $api_key,
                ),
                'body'        => wp_json_encode( $body_gpt ),
            );
        } elseif ( isset( $img_block['api_chosen'] ) && $img_block['api_chosen'] === 'replicate' ) {
        } elseif ( $img_block['api_chosen'] == 'stability' ) {
        } elseif ( $img_block['api_chosen'] == 'ideogram' ) {
        } elseif ( $img_block['api_chosen'] == 'flux2' ) {
        } elseif ( $img_block['api_chosen'] == 'gemini' ) {
        } elseif ( $img_block['api_chosen'] == 'grok' ) {
        } elseif ( $img_block['api_chosen'] == 'flickr' ) {
            $api_key = '63d9c292b9e2dfacd3a73908779d6d6f';
            $imgtype = ( !empty( $options['flickr']['imgtype'] ) ? $options['flickr']['imgtype'] : '7' );
            if ( isset( $options['flickr']['rights'] ) && !empty( $options['flickr']['rights'] ) ) {
                $rights = '';
                $last_right = array_keys( $options['flickr']['rights'] );
                $last_right = end( $last_right );
                foreach ( $options['flickr']['rights'] as $rights_into_searching ) {
                    $rights .= $rights_into_searching;
                    if ( $rights_into_searching != $last_right ) {
                        $rights .= ',';
                    }
                }
            } else {
                $rights = '0,1,2,3,4,5,6,7,8';
            }
            $array_parameters = array(
                'url'            => 'https://api.flickr.com/services/rest/',
                'method'         => 'flickr.photos.search',
                'api_key'        => $api_key,
                'text'           => $search,
                'per_page'       => '16',
                'format'         => 'json',
                'nojsoncallback' => '1',
                'privacy_filter' => '1',
                'license'        => $rights,
                'sort'           => 'relevance',
                'content_type'   => $imgtype,
                'extras'         => 'url_l,url_m,url_n,url_s,owner_name',
            );
        } elseif ( $img_block['api_chosen'] == 'pixabay' ) {
            $pixabay_username = ( !empty( $options['pixabay']['username'] ) ? $options['pixabay']['username'] : '' );
            $api_key = ( !empty( $options['pixabay']['apikey'] ) ? $options['pixabay']['apikey'] : '' );
            $imgtype = ( !empty( $options['pixabay']['imgtype'] ) ? $options['pixabay']['imgtype'] : 'all' );
            $country = ( !empty( $options['pixabay']['search_country'] ) ? $options['pixabay']['search_country'] : 'en' );
            $orientation = ( !empty( $options['pixabay']['orientation'] ) ? $options['pixabay']['orientation'] : 'all' );
            $safe = ( !empty( $options['pixabay']['safesearch'] ) ? $options['pixabay']['safesearch'] : 'false' );
            $min_width = ( !empty( $options['pixabay']['min_width'] ) ? (int) $options['pixabay']['min_width'] : '0' );
            $min_height = ( !empty( $options['pixabay']['min_height'] ) ? (int) $options['pixabay']['min_height'] : '0' );
            $array_parameters = array(
                'url'         => 'https://pixabay.com/api/',
                'username'    => $pixabay_username,
                'key'         => $api_key,
                'lang'        => $country,
                'q'           => urlencode( $search ),
                'image_type'  => $imgtype,
                'per_page'    => '200',
                'orientation' => $orientation,
                'safesearch'  => $safe,
                'min_width'   => $min_width,
                'min_height'  => $min_height,
            );
        } elseif ( $img_block['api_chosen'] == 'youtube' ) {
        } elseif ( $img_block['api_chosen'] == 'pexels' ) {
        } elseif ( $img_block['api_chosen'] == 'unsplash' ) {
        } elseif ( $img_block['api_chosen'] == 'cc_search' || $img_block['api_chosen'] == 'openverse' ) {
            $imgtype = ( !empty( $options['cc_search']['imgtype'] ) ? $options['cc_search']['imgtype'] : '' );
            $aspect_ratio = ( !empty( $options['cc_search']['aspect_ratio'] ) ? $options['cc_search']['aspect_ratio'] : '' );
            $source_mode = ( isset( $options['cc_search']['source'] ) ? (int) $options['cc_search']['source'] : 1 );
            $array_parameters = array(
                'url' => 'https://api.openverse.org/v1/images/',
                'q'   => $search,
            );
            if ( '' !== $imgtype ) {
                $array_parameters['category'] = $imgtype;
            }
            if ( '' !== $aspect_ratio ) {
                $array_parameters['aspect_ratio'] = $aspect_ratio;
            }
            if ( 2 === $source_mode ) {
                $array_parameters['excluded_source'] = 'flickr';
            }
            if ( !empty( $options['cc_search']['rights'] ) && is_array( $options['cc_search']['rights'] ) ) {
                $licenses = array();
                foreach ( $options['cc_search']['rights'] as $license ) {
                    $license = strtolower( sanitize_text_field( $license ) );
                    if ( '' !== $license ) {
                        $licenses[] = $license;
                    }
                }
                if ( !empty( $licenses ) ) {
                    $array_parameters['license'] = implode( ',', $licenses );
                }
            }
        } else {
            return false;
        }
        return $array_parameters;
    }

    /**
     * Generate image process
     *
     * @since    4.0.0
     */
    public function MPT_Generate(
        $service,
        $url,
        $url_parameters,
        $selected_image,
        $get_only_thumb = false,
        $search = '',
        $post_id = 0
    ) {
        $log = $this->MPT_monolog_call();
        $fetched = $this->MPT_get_results( $service, $url, $url_parameters );
        if ( !is_array( $fetched ) || count( $fetched ) < 2 ) {
            return false;
        }
        list( $result_body, $result ) = $fetched;
        // In case of API problem
        if ( empty( $result_body ) ) {
            return false;
        }
        $log = $this->MPT_monolog_call();
        $log->info( 'Source used', array(
            'Service' => $service,
        ) );
        // ── INSERT REPLICATE POLLING HERE ────────────────────────────────────────
        if ( $service === 'replicate' ) {
            // extract prediction ID and status URL
            $predictionId = $result_body['id'];
            $getUrl = $result_body['urls']['get'];
            // retrieve API token from settings
            $options = wp_parse_args( get_option( 'MPT_plugin_banks_settings' ), $this->MPT_default_options_banks_settings( true ) );
            $apiToken = ( !empty( $options['replicate']['apikey'] ) ? $options['replicate']['apikey'] : '' );
            // poll until prediction is complete
            do {
                usleep( 500000 );
                // wait 0.5s
                $resp = wp_remote_get( $getUrl, array(
                    'headers' => array(
                        'Authorization' => 'Token ' . $apiToken,
                        'Content-Type'  => 'application/json',
                    ),
                    'timeout' => 10,
                ) );
                // DEBUG
                /*
                			$log->info( 'Replicate polling status', [
                				'http_code' => wp_remote_retrieve_response_code( $resp ),
                				'body'      => substr( wp_remote_retrieve_body( $resp ), 0, 100 ) // Start of JSON
                			] );*/
                if ( is_wp_error( $resp ) ) {
                    return false;
                }
                $body = json_decode( wp_remote_retrieve_body( $resp ), true );
                $status = $body['status'] ?? '';
            } while ( in_array( $status, array('starting', 'processing'), true ) );
            // on success, replace output so subsequent logic sees the final URL
            if ( $status === 'succeeded' && !empty( $body['output'][0] ) ) {
                $result_body['output'] = $body['output'];
            } else {
                return false;
            }
        }
        // ── END OF REPLICATE POLLING ────────────────────────────────────────────
        if ( $service === 'flux2' ) {
            $poll_url = ( !empty( $result_body['polling_url'] ) ? $result_body['polling_url'] : '' );
            if ( '' === $poll_url ) {
                return false;
            }
            $options = wp_parse_args( get_option( 'MPT_plugin_banks_settings' ), $this->MPT_default_options_banks_settings( true ) );
            $api_key = ( !empty( $options['flux2']['apikey'] ) ? $options['flux2']['apikey'] : '' );
            $status = '';
            $body = array();
            $attempts = 0;
            do {
                usleep( 500000 );
                $attempts++;
                $resp = wp_remote_get( $poll_url, array(
                    'headers' => array(
                        'accept' => 'application/json',
                        'x-key'  => $api_key,
                    ),
                    'timeout' => 15,
                ) );
                if ( is_wp_error( $resp ) ) {
                    return false;
                }
                $body = json_decode( wp_remote_retrieve_body( $resp ), true );
                $status = ( is_array( $body ) && isset( $body['status'] ) ? $body['status'] : '' );
                if ( in_array( $status, array('Error', 'Failed'), true ) ) {
                    return false;
                }
            } while ( 'Ready' !== $status && $attempts < 120 );
            if ( 'Ready' === $status && !empty( $body['result']['sample'] ) ) {
                $result_body['output'] = array($body['result']['sample']);
            } else {
                return false;
            }
        }
        if ( $service == 'google_image' ) {
            $loop_results = $result_body['items'];
            // TODO : Check if urls are real images or just redirections
            $url_path = 'pagemap';
        } elseif ( $service == 'bing' ) {
            $loop_results = $result_body['results'];
            $url_path = 'url';
        } elseif ( $service == 'gptimage' ) {
            $loop_results = ( isset( $result_body['data'] ) ? $result_body['data'] : array() );
            $url_path = 'b64_json';
        } elseif ( $service == 'ideogram' ) {
            $loop_results = ( isset( $result_body['data'] ) ? $result_body['data'] : array() );
            $url_path = 'url';
            if ( is_array( $loop_results ) ) {
                foreach ( $loop_results as $item_key => $item ) {
                    if ( !is_array( $item ) ) {
                        unset($loop_results[$item_key]);
                        continue;
                    }
                    if ( isset( $item['is_image_safe'] ) && false === $item['is_image_safe'] ) {
                        unset($loop_results[$item_key]);
                        continue;
                    }
                    if ( empty( $item['url'] ) || !is_string( $item['url'] ) ) {
                        unset($loop_results[$item_key]);
                    }
                }
            }
        } elseif ( $service == 'grok' ) {
            $loop_results = ( isset( $result_body['data'] ) ? $result_body['data'] : array() );
            $url_path = 'url';
        } elseif ( $service == 'gemini' ) {
            $gemini_b64 = $this->MPT_gemini_extract_b64( $result_body );
            if ( false === $gemini_b64 ) {
                return false;
            }
            $loop_results = array(
                'image' => $gemini_b64,
            );
            $url_path = 'image';
        } elseif ( $service == 'stability' ) {
            $loop_results = $result_body;
            $url_path = 'image';
        } elseif ( $service == 'flickr' ) {
            $loop_results = $result_body['photos']['photo'];
            $url_path = 'id';
            $url_caption = 'owner';
        } elseif ( $service == 'pixabay' ) {
            $loop_results = $result_body['hits'];
            $url_path = 'largeImageURL';
        } elseif ( $service == 'youtube' ) {
            $loop_results = $result_body['items'];
            $url_path = 'id';
        } elseif ( $service == 'pexels' ) {
            $loop_results = $result_body['photos'];
            $url_path = 'src';
        } elseif ( $service == 'unsplash' ) {
            $loop_results = $result_body['results'];
            $url_path = 'urls';
        } elseif ( $service == 'cc_search' || $service == 'openverse' ) {
            $loop_results = $result_body['results'];
            $url_path = 'url';
        } elseif ( $service == 'replicate' || $service == 'flux2' ) {
            $loop_results = ( isset( $result_body['output'] ) ? (array) $result_body['output'] : array() );
            $url_path = null;
        } else {
            return false;
        }
        // Check if function is launch for Gutenberg block
        if ( TRUE == $get_only_thumb ) {
            if ( 'flickr' === $service ) {
                $photos = array();
                if ( !empty( $loop_results ) && is_array( $loop_results ) ) {
                    foreach ( $loop_results as $photo ) {
                        $url = '';
                        foreach ( array(
                            'url_l',
                            'url_m',
                            'url_n',
                            'url_s'
                        ) as $size_key ) {
                            if ( !empty( $photo[$size_key] ) ) {
                                $url = $photo[$size_key];
                                break;
                            }
                        }
                        if ( '' === $url ) {
                            continue;
                        }
                        $photo['url'] = $url;
                        $photos[] = $photo;
                    }
                }
                return array(
                    'photos' => $photos,
                );
            }
            return $result_body;
        }
        /* Random Image */
        if ( $selected_image == 'random_result' && !in_array( $service, $this->MPT_ai_bank_slugs(), true ) ) {
            @shuffle( $loop_results );
        }
        // Testing images
        if ( $service == 'bing' ) {
            foreach ( $loop_results as $loop_result_result => $loop_result ) {
                if ( (int) $post_id > 0 && !empty( $loop_result['url'] ) && $this->MPT_is_used_source_url( $post_id, $loop_result['url'] ) ) {
                    unset($loop_results[$loop_result_result]);
                    continue;
                }
                $img_host = wp_parse_url( $loop_result['url'], PHP_URL_HOST );
                if ( !is_string( $img_host ) || $this->MPT_is_private_host( $img_host ) ) {
                    unset($loop_results[$loop_result_result]);
                    continue;
                }
                $log = $this->MPT_monolog_call();
                $log->info( 'Remote image', array(
                    'remote_img' => $loop_result,
                ) );
                $remote_img = wp_remote_head( $loop_result['url'], array(
                    'timeout'     => 8,
                    'redirection' => 3,
                ) );
                $remote_response = (int) wp_remote_retrieve_response_code( $remote_img );
                if ( in_array( $remote_response, array(404, 410), true ) ) {
                    unset($loop_results[$loop_result_result]);
                    continue;
                }
                // Many CDNs reject HEAD (403/405). Keep the candidate; download validates the file.
                break;
            }
        }
        if ( !empty( $loop_results ) ) {
            $loop_count = 0;
            $numUrl = count( $loop_results );
            foreach ( $loop_results as $fetch_result_key => $fetch_result ) {
                if ( 'image' != $fetch_result_key && ($service == 'stability' || $service == 'gemini') ) {
                    continue;
                }
                if ( $service == 'replicate' || $service == 'flux2' ) {
                    $url_result = $fetch_result;
                } elseif ( !in_array( $service, $this->MPT_b64_bank_slugs(), true ) ) {
                    $url_result = $fetch_result[$url_path];
                }
                // Change default url image
                if ( $service == 'google_image' ) {
                    $url_result = $url_result['cse_image'][0]['src'];
                } elseif ( $service == 'unsplash' ) {
                    $url_result = $url_result['full'];
                } elseif ( $service == 'pexels' ) {
                    $url_result = $url_result['original'];
                } elseif ( $service == 'gptimage' ) {
                    $url_result = ( isset( $fetch_result['b64_json'] ) ? $fetch_result['b64_json'] : '' );
                } elseif ( in_array( $service, $this->MPT_b64_bank_slugs(), true ) ) {
                    $url_result = $fetch_result;
                } elseif ( $service == 'replicate' || $service == 'flux2' ) {
                    $url_result = $fetch_result;
                } else {
                    $url_result = $fetch_result[$url_path];
                }
                $options = wp_parse_args( get_option( 'MPT_plugin_main_settings' ), $this->MPT_default_options_main_settings( TRUE ) );
                $alt = '';
                // Caption texts
                if ( isset( $options['enable_caption'] ) && 'enable' == $options['enable_caption'] ) {
                    if ( $service == 'pixabay' ) {
                        $caption = $fetch_result['user'];
                    } elseif ( $service == 'unsplash' ) {
                        $caption = $fetch_result['user']['name'];
                    } elseif ( $service == 'pexels' ) {
                        $caption = $fetch_result['photographer'];
                    } elseif ( $service == 'cc_search' || $service == 'openverse' ) {
                        $caption = $fetch_result['creator'];
                    } elseif ( $service == 'bing' ) {
                        $caption = $fetch_result['caption'];
                    } elseif ( $service == 'flickr' ) {
                        // FLICKR : Additional remote request to get image url
                        $url_result_owner = $fetch_result[$url_caption];
                        $api_key = '63d9c292b9e2dfacd3a73908779d6d6f';
                        $url = 'https://api.flickr.com/services/rest/?method=flickr.people.getInfo&api_key=' . $api_key . '&user_id=' . $url_result_owner . '&format=json&nojsoncallback=1';
                        $result_img_flickr = wp_remote_request( $url );
                        $result_img_body_flickr = json_decode( $result_img_flickr['body'], true );
                        if ( !empty( $result_img_body_flickr['person']['realname']['_content'] ) ) {
                            $caption = ucwords( $result_img_body_flickr['person']['realname']['_content'] );
                        } else {
                            $caption = ucwords( $result_img_body_flickr['person']['username']['_content'] );
                        }
                    } else {
                        $caption = '';
                    }
                    if ( 'author_bank' == $options['caption_from'] && $service != 'bing' ) {
                        $caption .= esc_html__( ' from ', 'magic-post-thumbnail' ) . ucfirst( $service );
                    }
                } else {
                    $caption = '';
                }
                // FLICKR : Additional remote request to get image url
                if ( $service == 'flickr' ) {
                    $api_key = '63d9c292b9e2dfacd3a73908779d6d6f';
                    $url = 'https://api.flickr.com/services/rest/?method=flickr.photos.getSizes&api_key=' . $api_key . '&photo_id=' . $url_result . '&format=json&nojsoncallback=1';
                    $result_img_flickr = wp_remote_request( $url );
                    $result_img_body_flickr = json_decode( $result_img_flickr['body'], true );
                    $result = end( $result_img_body_flickr['sizes']['size'] );
                    $url_result = $result['source'];
                    //$url_result_sizes       = $result_img_body_flickr['sizes'];
                }
                // YOUTUBE : Additional remote request to get thumbnail
                if ( $service == 'youtube' ) {
                    $api_key = $url_parameters['key'];
                    $url = 'https://www.googleapis.com/youtube/v3/videos?key=' . $api_key . '&part=snippet&id=' . $fetch_result['id']['videoId'];
                    $result_img_yt = wp_remote_request( $url );
                    $result_img_body_yt = json_decode( $result_img_yt['body'], true );
                    $hdimg = end( $result_img_body_yt['items'][0]['snippet']['thumbnails'] );
                    $url_result = $hdimg['url'];
                }
                if ( empty( $url_result ) ) {
                    continue;
                }
                if ( !$get_only_thumb && (int) $post_id > 0 && $this->MPT_is_used_source_url( $post_id, $url_result ) ) {
                    $log->info( 'Skipping previously used image', array(
                        'post' => $post_id,
                        'url'  => $url_result,
                    ) );
                    continue;
                }
                // Avoid unknown image type
                if ( !in_array( $service, $this->MPT_ai_bank_slugs(), true ) && $service != 'unsplash' && $service != 'pexels' && $service != 'bing' ) {
                    $url_result = $url_result;
                    $wp_filetype = wp_check_filetype( $url_result );
                    if ( false == $wp_filetype['type'] ) {
                        continue;
                    }
                }
                if ( TRUE == $get_only_thumb ) {
                    $url_result_ar['photos'][]['url'] = $url_result;
                } else {
                    if ( in_array( $service, $this->MPT_b64_bank_slugs(), true ) ) {
                        if ( base64_decode( $url_result, true ) !== false ) {
                            $image_data = base64_decode( $url_result );
                            $options_banks = wp_parse_args( get_option( 'MPT_plugin_banks_settings' ), $this->MPT_default_options_banks_settings( TRUE ) );
                            $format = 'png';
                            if ( 'stability' === $service ) {
                                $format = ( !empty( $options_banks['stability']['output_format'] ) ? $options_banks['stability']['output_format'] : 'png' );
                            } elseif ( 'gptimage' === $service ) {
                                $format = ( !empty( $options_banks['gptimage']['output_format'] ) ? $options_banks['gptimage']['output_format'] : 'png' );
                                if ( 'jpeg' !== $format && !empty( $options_banks['gptimage']['convert_jpg'] ) && 'true' === $options_banks['gptimage']['convert_jpg'] ) {
                                    $format = 'jpeg';
                                }
                                if ( !in_array( $format, array('png', 'jpeg', 'webp'), true ) ) {
                                    $format = 'png';
                                }
                            }
                            $file_media = array(
                                'response' => array(
                                    'code' => '200',
                                ),
                                'body'     => $image_data,
                                'headers'  => array(
                                    'content-type' => 'image/' . $format,
                                ),
                            );
                            break;
                        }
                    } else {
                        $tmp = $this->MPT_download_to_temp( $url_result );
                        if ( !$tmp ) {
                            if ( ++$loop_count === $numUrl ) {
                                return false;
                            } else {
                                continue;
                            }
                        }
                        $file_media = array(
                            'response' => array(
                                'code' => '200',
                            ),
                            'body'     => '',
                            '_mpt_tmp' => $tmp,
                            'headers'  => array(
                                'content-type' => (string) wp_get_image_mime( $tmp ),
                            ),
                        );
                        break;
                    }
                }
            }
            if ( TRUE == $get_only_thumb ) {
                return $url_result_ar;
            }
        } else {
            return false;
        }
        return array(
            $url_result,
            $file_media,
            $alt,
            $caption
        );
    }

    /**
     * Image results to get thumbnails
     *
     * @since    4.0.0
     */
    private function MPT_get_results( $service, $url, $url_parameters ) {
        if ( in_array( $service, $this->MPT_ai_bank_slugs(), true ) || $service == 'pexels' ) {
            $defaults = $url_parameters;
        } else {
            /* Retrieve 3 images as result */
            $url = add_query_arg( $url_parameters, $url );
            $defaults = $this->MPT_http_defaults();
            if ( 'bing' === $service ) {
                $lang = ( !empty( $url_parameters['setlang'] ) ? str_replace( '_', '-', (string) $url_parameters['setlang'] ) : 'en' );
                $parts = explode( '-', $lang, 2 );
                $primary = strtolower( $parts[0] );
                if ( '' === $primary ) {
                    $primary = 'en';
                    $lang = 'en';
                }
                $defaults['headers']['Accept'] = 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8';
                $defaults['headers']['Accept-Language'] = $lang . ',' . $primary . ';q=0.9,en;q=0.8';
                $defaults['headers']['Upgrade-Insecure-Requests'] = '1';
                $defaults['headers']['Referer'] = 'https://www.bing.com/images/search';
                $defaults['headers']['Cookie'] = $this->MPT_bing_safesearch_cookie( $url_parameters );
            }
            if ( 'unsplash' === $service ) {
                $defaults['headers']['Accept-Version'] = 'v1';
            }
        }
        $result = wp_remote_request( $url, $defaults );
        $log = $this->MPT_monolog_call();
        if ( is_wp_error( $result ) ) {
            $log->error( 'API request failed', array(
                'service' => $service,
                'error'   => $result->get_error_message(),
            ) );
            return false;
        }
        if ( $service == 'bing' ) {
            $html = wp_remote_retrieve_body( $result );
            $search_q = ( isset( $url_parameters['q'] ) ? (string) $url_parameters['q'] : '' );
            $parsed = $this->MPT_bing_parse_html( $html, $search_q );
            if ( $this->MPT_bing_needs_async_fallback( $parsed, $search_q ) ) {
                $async_args = $url_parameters;
                $async_args['count'] = '35';
                $async_args['mmasync'] = '1';
                $async_url = add_query_arg( $async_args, 'https://www.bing.com/images/async' );
                $async_res = wp_remote_request( $async_url, $defaults );
                if ( !is_wp_error( $async_res ) ) {
                    $async_html = wp_remote_retrieve_body( $async_res );
                    $parsed = $this->MPT_bing_parse_html( $html . $async_html, $search_q );
                }
            }
            $result_body = array(
                'results' => $parsed,
            );
        } else {
            $raw_body = wp_remote_retrieve_body( $result );
            $result_body = json_decode( $raw_body, true );
            if ( !is_array( $result_body ) ) {
                $result_body = array();
            }
            if ( !empty( $result_body['error']['message'] ) ) {
                $log->error( 'API error', array(
                    'service' => $service,
                    'error'   => $result_body['error']['message'],
                ) );
            }
            // accept 200 for most services, 201 for replicate
            $code = (int) wp_remote_retrieve_response_code( $result );
            if ( $service === 'replicate' && $code !== 201 || $service !== 'replicate' && $code !== 200 ) {
                $log->error( 'API HTTP error', array(
                    'service' => $service,
                    'http'    => $code,
                    'body'    => substr( wp_strip_all_tags( (string) $raw_body ), 0, 400 ),
                ) );
                return false;
            }
        }
        return array($result_body, $result);
    }

    /**
     * Map UI aspect ratio to an Ideogram 4.0 resolution preset.
     *
     * v4 generate has no aspect_ratio / num_images fields; resolution is WIDTHxHEIGHT.
     *
     * @since 7.0.0
     * @param string $ratio Aspect ratio from settings.
     * @return string
     */
    private function MPT_ideogram_resolution( $ratio ) {
        $map = array(
            '1:1'  => '1024x1024',
            '16:9' => '1920x1088',
            '9:16' => '1024x1792',
        );
        if ( isset( $map[$ratio] ) ) {
            return $map[$ratio];
        }
        return '1920x1088';
    }

    /**
     * Map a Flux 2 aspect ratio to width/height (multiples of 16).
     *
     * @since 7.0.0
     * @param string $ratio Aspect ratio.
     * @return array
     */
    private function MPT_flux2_dimensions( $ratio ) {
        $map = array(
            '1:1'  => array(1024, 1024),
            '16:9' => array(1280, 720),
            '9:16' => array(720, 1280),
        );
        if ( isset( $map[$ratio] ) ) {
            return $map[$ratio];
        }
        return array(1280, 720);
    }

    /**
     * Map a UI ratio (16:9) to Gemini v1 ImageResponseFormat.AspectRatio enum.
     *
     * @since 7.1.0
     * @param string $ratio UI aspect ratio.
     * @return string
     */
    private function MPT_gemini_aspect_ratio_enum( $ratio ) {
        $map = array(
            '1:1'  => 'ASPECT_RATIO_ONE_BY_ONE',
            '16:9' => 'ASPECT_RATIO_SIXTEEN_BY_NINE',
            '9:16' => 'ASPECT_RATIO_NINE_BY_SIXTEEN',
            '3:2'  => 'ASPECT_RATIO_THREE_BY_TWO',
            '2:3'  => 'ASPECT_RATIO_TWO_BY_THREE',
            '4:3'  => 'ASPECT_RATIO_FOUR_BY_THREE',
            '3:4'  => 'ASPECT_RATIO_THREE_BY_FOUR',
            '4:5'  => 'ASPECT_RATIO_FOUR_BY_FIVE',
            '5:4'  => 'ASPECT_RATIO_FIVE_BY_FOUR',
            '21:9' => 'ASPECT_RATIO_TWENTY_ONE_BY_NINE',
        );
        if ( isset( $map[$ratio] ) ) {
            return $map[$ratio];
        }
        return 'ASPECT_RATIO_SIXTEEN_BY_NINE';
    }

    /**
     * Extract inline base64 image data from a Gemini generateContent response.
     *
     * @since 7.0.0
     * @param array $result_body Decoded JSON.
     * @return string|false
     */
    private function MPT_gemini_extract_b64( $result_body ) {
        if ( empty( $result_body['candidates'] ) || !is_array( $result_body['candidates'] ) ) {
            return false;
        }
        foreach ( $result_body['candidates'] as $candidate ) {
            $parts = array();
            if ( isset( $candidate['content']['parts'] ) && is_array( $candidate['content']['parts'] ) ) {
                $parts = $candidate['content']['parts'];
            }
            foreach ( $parts as $part ) {
                if ( !empty( $part['inlineData']['data'] ) ) {
                    return $part['inlineData']['data'];
                }
                if ( !empty( $part['inline_data']['data'] ) ) {
                    return $part['inline_data']['data'];
                }
            }
        }
        return false;
    }

    /**
     * Bing Images helpers (HTML fetch/parse/qft).
     *
     * v7.1+ candidate: extract a thin bank adapter `fetch( $search, $options )`
     * starting with Bing only — not before Generation has slimmed via sideload
     * and parse_blocks. Do not split the remaining banks in the same pass.
     *
     * @since 7.0.0
     * @param string $domains Raw textarea value from plugin settings.
     * @return array
     */
    private function MPT_bing_normalize_domain_rules( $domains ) {
        $normalized_domains = array();
        $domain_lines = preg_split( '/\\r\\n|\\r|\\n/', (string) $domains );
        if ( empty( $domain_lines ) || !is_array( $domain_lines ) ) {
            return $normalized_domains;
        }
        foreach ( $domain_lines as $domain ) {
            $domain = trim( wp_strip_all_tags( $domain ) );
            if ( '' === $domain ) {
                continue;
            }
            $domain = preg_replace( '#^https?://#i', '', $domain );
            $domain = preg_replace( '#^//#', '', $domain );
            $domain = preg_replace( '#/.*$#', '', $domain );
            $domain = trim( $domain, " \t\n\r\x00\v." );
            if ( '' !== $domain ) {
                $normalized_domains[] = $domain;
            }
        }
        return array_values( array_unique( $normalized_domains ) );
    }

    private function MPT_bing_build_qft( $options ) {
        $parsed = array(
            'sur' => ( isset( $options['rights'] ) ? (string) $options['rights'] : '' ),
            'itp' => ( isset( $options['imgtype'] ) ? (string) $options['imgtype'] : '' ),
            'isz' => ( isset( $options['imgsz'] ) ? (string) $options['imgsz'] : '' ),
            'iar' => ( isset( $options['format'] ) ? (string) $options['format'] : '' ),
            'isc' => ( isset( $options['img_color'] ) ? (string) $options['img_color'] : '' ),
        );
        $tokens = array();
        $isz_map = array(
            'i'             => 'imagesize-small',
            'm'             => 'imagesize-medium',
            'l'             => 'imagesize-large',
            'lt,islt:qsvga' => 'imagesize-large',
            'lt,islt:vga'   => 'imagesize-xlarge',
            'lt,islt:svga'  => 'imagesize-xxlarge',
            'lt,islt:xga'   => 'imagesize-xxlarge',
            'lt,islt:2mp'   => 'imagesize-wallpaper',
            'lt,islt:4mp'   => 'imagesize-wallpaper',
            'lt,islt:6mp'   => 'imagesize-wallpaper',
            'lt,islt:8mp'   => 'imagesize-wallpaper',
            'lt,islt:10mp'  => 'imagesize-wallpaper',
        );
        if ( !empty( $parsed['isz'] ) && isset( $isz_map[$parsed['isz']] ) ) {
            $tokens[] = $isz_map[$parsed['isz']];
        }
        $iar_map = array(
            't'  => 'aspect-tall',
            's'  => 'aspect-square',
            'w'  => 'aspect-wide',
            'xw' => 'aspect-wide',
        );
        if ( !empty( $parsed['iar'] ) && isset( $iar_map[$parsed['iar']] ) ) {
            $tokens[] = $iar_map[$parsed['iar']];
        }
        $itp_map = array(
            'face'     => 'face-face',
            'photo'    => 'photo-photo',
            'clipart'  => 'photo-clipart',
            'lineart'  => 'photo-lineart',
            'animated' => 'photo-animatedgif',
        );
        if ( !empty( $parsed['itp'] ) && isset( $itp_map[$parsed['itp']] ) ) {
            $tokens[] = $itp_map[$parsed['itp']];
        }
        $isc_map = array(
            'black'  => 'color2-FGcls_BLACK',
            'blue'   => 'color2-FGcls_BLUE',
            'brown'  => 'color2-FGcls_BROWN',
            'gray'   => 'color2-FGcls_GRAY',
            'green'  => 'color2-FGcls_GREEN',
            'pink'   => 'color2-FGcls_PINK',
            'purple' => 'color2-FGcls_PURPLE',
            'teal'   => 'color2-FGcls_TEAL',
            'white'  => 'color2-FGcls_WHITE',
            'yellow' => 'color2-FGcls_YELLOW',
        );
        if ( !empty( $parsed['isc'] ) ) {
            $c = strtolower( (string) $parsed['isc'] );
            if ( isset( $isc_map[$c] ) ) {
                $tokens[] = 'color2-color';
                $tokens[] = $isc_map[$c];
            }
        }
        $sur_map = array(
            'fc'  => 'license-L2_L3',
            'fmc' => 'license-L5_L6',
            'fm'  => 'license-L4_L5',
            'f'   => 'license-L2',
        );
        if ( !empty( $parsed['sur'] ) && isset( $sur_map[$parsed['sur']] ) ) {
            $tokens[] = $sur_map[$parsed['sur']];
        }
        $tokens = array_values( array_filter( array_unique( $tokens ) ) );
        $tokens = apply_filters( 'mpt_bing_qft_tokens', $tokens, $parsed );
        if ( has_filter( 'mpt_google_scraping_bing_qft_tokens' ) ) {
            if ( function_exists( '_deprecated_hook' ) ) {
                _deprecated_hook( 'mpt_google_scraping_bing_qft_tokens', '7.0.0', 'mpt_bing_qft_tokens' );
            }
            $tokens = apply_filters( 'mpt_google_scraping_bing_qft_tokens', $tokens, $parsed );
        }
        if ( empty( $tokens ) ) {
            return '';
        }
        return ' ' . implode( ' ', array_map( static function ( $t ) {
            return 'filterui:' . $t;
        }, $tokens ) );
    }

    /**
     * Cookie Bing uses for SafeSearch on the HTML endpoint (adlt alone is ignored).
     *
     * @since 7.1.0
     * @param array $url_parameters Request query args.
     * @return string
     */
    private function MPT_bing_safesearch_cookie( $url_parameters ) {
        $adlt = ( isset( $url_parameters['adlt'] ) ? strtolower( (string) $url_parameters['adlt'] ) : 'strict' );
        $level = ( 'off' === $adlt ? 'OFF' : 'STRICT' );
        return 'SRCHHPGUSR=ADLT=' . $level;
    }

    /**
     * Whether the first HTML parse looks too thin or off-topic for the query.
     *
     * @since 7.1.0
     * @param array  $rows    Parsed rows.
     * @param string $search  Search query.
     * @return bool
     */
    private function MPT_bing_needs_async_fallback( $rows, $search ) {
        if ( !is_array( $rows ) || count( $rows ) < 5 ) {
            return true;
        }
        $relevant = 0;
        foreach ( $rows as $row ) {
            if ( is_array( $row ) && $this->MPT_bing_row_is_relevant( $row, $search ) ) {
                $relevant++;
            }
        }
        return $relevant < 3;
    }

    private function MPT_bing_parse_html( $html, $search = '' ) {
        $rows = array();
        $rows = array_merge( $rows, $this->MPT_bing_collect_iusc_rows( $html ) );
        $rows = array_merge( $rows, $this->MPT_bing_collect_json_murl_rows( $html ) );
        $rows = array_merge( $rows, $this->MPT_bing_collect_entity_murl_rows( $html ) );
        return $this->MPT_bing_rank_rows( $rows, $search );
    }

    /**
     * Collect tiles from class="iusc" regardless of attribute order.
     *
     * @since 7.1.0
     * @param string $html Page HTML.
     * @return array
     */
    private function MPT_bing_collect_iusc_rows( $html ) {
        $out = array();
        if ( !preg_match_all( '/<[^>]+class="[^"]*\\biusc\\b[^"]*"[^>]*>/i', (string) $html, $tags ) ) {
            return $out;
        }
        foreach ( $tags[0] as $tag ) {
            if ( !preg_match( '/\\sm="([^"]*)"/', $tag, $m ) ) {
                continue;
            }
            $row = $this->MPT_bing_row_from_tile( $m[1] );
            if ( false !== $row ) {
                $out[] = $row;
            }
        }
        return $out;
    }

    /**
     * Collect murl entries from raw JSON embedded in the page.
     *
     * @since 7.1.0
     * @param string $html Page HTML.
     * @return array
     */
    private function MPT_bing_collect_json_murl_rows( $html ) {
        $out = array();
        $offset = 0;
        $html = (string) $html;
        while ( preg_match(
            '/"murl"\\s*:\\s*"(https?:[^"]+)"/',
            $html,
            $m,
            PREG_OFFSET_CAPTURE,
            $offset
        ) ) {
            $url = str_replace( '\\/', '/', $m[1][0] );
            $pos = (int) $m[0][1];
            $window = substr( $html, max( 0, $pos - 500 ), 1000 );
            $title = '';
            if ( preg_match( '/"t"\\s*:\\s*"((?:\\\\.|[^"\\\\])*)"/', $window, $tm ) ) {
                $decoded = json_decode( '"' . $tm[1] . '"' );
                $title = ( is_string( $decoded ) ? $decoded : $tm[1] );
            }
            $row = $this->MPT_bing_row_from_url_and_title( $url, $title );
            if ( false !== $row ) {
                $out[] = $row;
            }
            $offset = $pos + strlen( $m[0][0] );
            if ( count( $out ) >= 40 ) {
                break;
            }
        }
        return $out;
    }

    /**
     * Collect HTML-entity encoded murl/title pairs (JS-heavy pages).
     *
     * @since 7.1.0
     * @param string $html Page HTML.
     * @return array
     */
    private function MPT_bing_collect_entity_murl_rows( $html ) {
        $out = array();
        preg_match_all( '/&quot;murl&quot;:&quot;(https?:[^&]+)&quot;/', (string) $html, $murls );
        preg_match_all( '/&quot;t&quot;:&quot;((?:[^&]|&amp;)+?)&quot;,&quot;mid&quot;/', (string) $html, $titles );
        $titles_list = ( isset( $titles[1] ) ? $titles[1] : array() );
        $i = 0;
        foreach ( ( isset( $murls[1] ) ? $murls[1] : array() ) as $url ) {
            $row = $this->MPT_bing_row_from_url_and_title( $url, ( isset( $titles_list[$i] ) ? $titles_list[$i] : '' ) );
            $i++;
            if ( false !== $row ) {
                $out[] = $row;
            }
            if ( count( $out ) >= 40 ) {
                break;
            }
        }
        return $out;
    }

    /**
     * Dedupe Bing rows and prefer those that match the search terms.
     *
     * @since 7.1.0
     * @param array  $rows   Candidate rows.
     * @param string $search Search query.
     * @return array
     */
    private function MPT_bing_rank_rows( $rows, $search ) {
        $relevant = array();
        $other = array();
        $seen = array();
        foreach ( $rows as $row ) {
            if ( !is_array( $row ) || empty( $row['url'] ) ) {
                continue;
            }
            if ( isset( $seen[$row['url']] ) ) {
                continue;
            }
            $seen[$row['url']] = true;
            if ( $this->MPT_bing_row_is_relevant( $row, $search ) ) {
                $relevant[] = $row;
            } else {
                $other[] = $row;
            }
        }
        $out = $relevant;
        if ( count( $out ) < 3 ) {
            $out = array_merge( $out, $other );
        }
        return array_slice( $out, 0, 20 );
    }

    /**
     * Whether a Bing tile title/URL mentions a significant search token.
     *
     * @since 7.1.0
     * @param array  $row    Parsed row.
     * @param string $search Search query.
     * @return bool
     */
    private function MPT_bing_row_is_relevant( $row, $search ) {
        $search = preg_replace( '/\\s+-?site:\\S+/i', '', (string) $search );
        $search = strtolower( trim( wp_strip_all_tags( $search ) ) );
        $search = trim( $search, " \t\n\r\x00\v\"" );
        if ( strlen( $search ) < 2 ) {
            return true;
        }
        $hay = strtolower( (( isset( $row['alt'] ) ? (string) $row['alt'] : '' )) . ' ' . (( isset( $row['url'] ) ? (string) $row['url'] : '' )) . ' ' . (( isset( $row['page'] ) ? (string) $row['page'] : '' )) );
        $tokens = preg_split(
            '/[^\\p{L}\\p{N}]+/u',
            $search,
            -1,
            PREG_SPLIT_NO_EMPTY
        );
        if ( !is_array( $tokens ) || empty( $tokens ) ) {
            return true;
        }
        $significant = array();
        foreach ( $tokens as $token ) {
            if ( strlen( $token ) >= 3 ) {
                $significant[] = $token;
            }
        }
        if ( empty( $significant ) ) {
            $significant = $tokens;
        }
        foreach ( $significant as $token ) {
            if ( false !== strpos( $hay, $token ) ) {
                return true;
            }
        }
        return false;
    }

    /**
     * Decode one Bing image tile (`iusc` m= JSON).
     *
     * @param string $encoded HTML-encoded JSON from the m attribute.
     * @return array|false
     */
    private function MPT_bing_row_from_tile( $encoded ) {
        $json = html_entity_decode( (string) $encoded, ENT_QUOTES | ENT_HTML5, 'UTF-8' );
        $data = json_decode( $json, true );
        if ( !is_array( $data ) ) {
            return false;
        }
        $url = ( isset( $data['murl'] ) ? (string) $data['murl'] : '' );
        $title = ( isset( $data['t'] ) ? (string) $data['t'] : '' );
        $row = $this->MPT_bing_row_from_url_and_title( $url, $title );
        if ( false === $row ) {
            return false;
        }
        if ( !empty( $data['purl'] ) ) {
            $row['page'] = (string) $data['purl'];
        } elseif ( !empty( $data['turl'] ) ) {
            $row['page'] = (string) $data['turl'];
        }
        return $row;
    }

    /**
     * Validate a Bing result URL and normalize its caption.
     *
     * @param string $url   Source image URL.
     * @param string $title Image title from Bing.
     * @return array|false
     */
    private function MPT_bing_row_from_url_and_title( $url, $title ) {
        $url = str_replace( '\\/', '/', (string) $url );
        if ( '' === $url || 0 === strpos( $url, 'https://tse' ) || 0 === strpos( $url, 'http://tse' ) ) {
            return false;
        }
        $validated = $this->MPT_validate_remote_url( $url );
        if ( !$validated ) {
            return false;
        }
        $t = wp_specialchars_decode( (string) $title, ENT_QUOTES );
        $t = html_entity_decode( $t, ENT_QUOTES | ENT_HTML5, 'UTF-8' );
        return array(
            'url'     => $validated,
            'alt'     => $t,
            'caption' => $t,
        );
    }

    public function MPT_order_array_urls( $str, $str_alt, $str_caption ) {
        // Get only the url and exclude Google image url (domain gstatic)
        $pattern = '/,\\["(http[^"]((?!gstatic).)*)",\\d+?,\\d+?\\]/';
        $replacement = '$1';
        // Check if $str is not null before using preg_replace
        $real_url = ( $str !== null ? preg_replace( $pattern, $replacement, $str ) : '' );
        return array(
            'url'     => $real_url,
            'alt'     => $str_alt,
            'caption' => $str_caption,
        );
    }

    /**
     * Function to get the depth of a category
     *
     * @since    5.2.11
     */
    private function get_category_depth( $cat_id, $current_depth = 0 ) {
        $category = get_category( $cat_id );
        if ( $category->parent == 0 ) {
            return $current_depth;
        } else {
            return $this->get_category_depth( $category->parent, $current_depth + 1 );
        }
    }

    /**
     * Function to get the desired category based on the user's choice
     *
     * @since    5.2.11
     */
    private function get_desired_category( $post_id, $category_choice ) {
        // Retrieve the categories of the post
        $postcategories = get_the_category( $post_id );
        $categories_with_depth = array();
        // Check if the post has any categories
        if ( empty( $postcategories ) ) {
            // Handle the case where no categories are associated
            return null;
        }
        // Get the depth of each category associated with the post
        foreach ( $postcategories as $category ) {
            $depth = $this->get_category_depth( $category->term_id );
            // Store the category with its depth as the key
            $categories_with_depth[$depth] = $category;
        }
        // Find the category with the greatest depth (most specific)
        $max_depth = max( array_keys( $categories_with_depth ) );
        $child_category = $categories_with_depth[$max_depth];
        // Determine the category to use based on the user's choice
        $desired_category = $child_category;
        if ( $category_choice && $child_category ) {
            if ( $category_choice == 'second_level' ) {
                if ( $child_category->parent ) {
                    $desired_category = get_category( $child_category->parent );
                }
            } elseif ( $category_choice == 'third_level' ) {
                if ( $child_category->parent ) {
                    $parent_category = get_category( $child_category->parent );
                    if ( $parent_category->parent ) {
                        $desired_category = get_category( $parent_category->parent );
                    } else {
                        $desired_category = $parent_category;
                    }
                }
            }
        }
        return $desired_category;
    }

    /**
     * Function to get the desired term based on the user's choice and a custom taxonomy
     *
     * @since    6.0.0
     */
    private function get_desired_taxonomy( $post_id, $taxonomy, $term_choice ) {
        // Retrieve the terms of the post in the specified taxonomy
        $post_terms = get_the_terms( $post_id, $taxonomy );
        $terms_with_depth = array();
        // Check if the post has any terms in the taxonomy
        if ( empty( $post_terms ) || is_wp_error( $post_terms ) ) {
            // Handle the case where no terms are associated or there's an error
            return null;
        }
        // Get the depth of each term associated with the post
        foreach ( $post_terms as $term ) {
            $depth = $this->get_term_depth( $term->term_id, $taxonomy );
            // Store the term with its depth as the key
            $terms_with_depth[$depth] = $term;
        }
        // Find the term with the greatest depth (most specific)
        $max_depth = max( array_keys( $terms_with_depth ) );
        $child_term = $terms_with_depth[$max_depth];
        // Determine the term to use based on the user's choice
        $desired_term = $child_term;
        if ( $term_choice && $child_term ) {
            if ( $term_choice == 'second_level' ) {
                if ( $child_term->parent ) {
                    $desired_term = get_term( $child_term->parent, $taxonomy );
                }
            } elseif ( $term_choice == 'third_level' ) {
                if ( $child_term->parent ) {
                    $parent_term = get_term( $child_term->parent, $taxonomy );
                    if ( $parent_term->parent ) {
                        $desired_term = get_term( $parent_term->parent, $taxonomy );
                    } else {
                        $desired_term = $parent_term;
                    }
                }
            }
        }
        return $desired_term;
    }

    /**
     * Helper function to get the depth of a term in a custom taxonomy.
     *
     * @since    6.0.0
     */
    private function get_term_depth( $term_id, $taxonomy ) {
        $depth = 0;
        $term = get_term( $term_id, $taxonomy );
        while ( $term && $term->parent ) {
            $term = get_term( $term->parent, $taxonomy );
            $depth++;
        }
        return $depth;
    }

    /**
     * Helper function to get the depth of a term in a custom taxonomy.
     *
     * @since    6.0.0
     */
    private function get_extracted_term( $text, $selected_lang = 'en' ) {
        require_once dirname( __FILE__ ) . '/../includes/php-ml/index.php';
        $extractor = new KeywordExtractor($selected_lang);
        $keywords = $extractor->extractKeywords( $text );
        return $keywords[0];
    }

    /**
     * Calculate dimensions from aspect ratio for models that use width/height
     *
     * @since    6.0.0
     * @param string $aspect_ratio The aspect ratio (e.g., '16:9', '4:3')
     * @param int $max_resolution Maximum resolution for the model
     * @return array Array with 'width' and 'height' keys
     */
    private function calculate_dimensions_from_aspect_ratio( $aspect_ratio, $max_resolution = 2048 ) {
        // Seedream-4 specific dimensions as per official documentation
        $seedream_dimensions = array(
            '1:1'  => array(
                'width'  => 2048,
                'height' => 2048,
            ),
            '4:3'  => array(
                'width'  => 2304,
                'height' => 1728,
            ),
            '16:9' => array(
                'width'  => 2560,
                'height' => 1440,
            ),
            '3:2'  => array(
                'width'  => 2304,
                'height' => 1536,
            ),
            '9:16' => array(
                'width'  => 1440,
                'height' => 2560,
            ),
        );
        // Check if we have specific dimensions for this aspect ratio
        if ( isset( $seedream_dimensions[$aspect_ratio] ) ) {
            return $seedream_dimensions[$aspect_ratio];
        }
        // Fallback to calculated dimensions for other ratios
        $ratio_parts = explode( ':', $aspect_ratio );
        if ( count( $ratio_parts ) !== 2 ) {
            // Default to 16:9 if invalid format
            $ratio_parts = array(16, 9);
        }
        $ratio_width = (int) $ratio_parts[0];
        $ratio_height = (int) $ratio_parts[1];
        // Calculate base dimensions
        $base_width = $max_resolution;
        $base_height = round( $max_resolution * $ratio_height / $ratio_width );
        // Ensure we don't exceed max resolution
        if ( $base_height > $max_resolution ) {
            $base_height = $max_resolution;
            $base_width = round( $max_resolution * $ratio_width / $ratio_height );
        }
        // Round to even numbers for better compatibility
        $width = $base_width - $base_width % 2;
        $height = $base_height - $base_height % 2;
        return array(
            'width'  => $width,
            'height' => $height,
        );
    }

}
