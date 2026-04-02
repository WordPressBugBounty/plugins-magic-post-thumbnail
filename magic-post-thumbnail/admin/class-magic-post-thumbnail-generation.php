<?php

/**
 * The functionalities for MPT images generation
 *
 * @package    Magic_Post_Thumbnail
 * @subpackage Magic_Post_Thumbnail/admin
 * @author     Magic Post Thumbnail <contact@magic-post-thumbnail.com>
 */
class Magic_Post_Thumbnail_Generation extends Magic_Post_Thumbnail_Admin {
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
     * Cached working TLS cipher profile for raw socket Google requests.
     *
     * @var array|null
     */
    private $raw_socket_working_profile = null;

    /**
     * Cached working cURL HTTP/2 config for Google requests.
     *
     * @var array|null
     */
    private $curl_h2_working_config = null;

    /**
     * In-memory strategy cache loaded from transient.
     *
     * @var array|null
     */
    private $google_scraping_strategy_cache = null;

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
        // Ajax calls
        add_action( 'wp_ajax_nopriv_generate_image', array(&$this, 'MPT_ajax_call') );
        add_action( 'wp_ajax_generate_image', array(&$this, 'MPT_ajax_call') );
        $main_settings = wp_parse_args( get_option( 'MPT_plugin_main_settings' ), $this->MPT_default_options_main_settings( FALSE ) );
        // Enable save_post hook
        if ( isset( $main_settings['enable_save_post_hook'] ) && 'enable' == $main_settings['enable_save_post_hook'] ) {
            add_action(
                'save_post',
                array(&$this, 'MPT_check_post_type'),
                10,
                3
            );
        }
    }

    /**
     * Ajax call for bulk generation
     *
     * @since    4.0.0
     */
    public function MPT_ajax_call() {
        // Convert the JSON-encoded post IDs into an array and sanitize them.
        $post_ids = array_map( 'absint', json_decode( $_POST['ids_mpt_generation'] ) );
        // Check if button "Generate Automatically" is clicked
        $button_autogenerate = ( isset( $_POST['buttonAutoGenerate'] ) ? boolval( $_POST['buttonAutoGenerate'] ) : false );
        // Security checks: Verify user capability and nonce for security.
        if ( !current_user_can( 'mpt_manage' ) || false === wp_verify_nonce( $_POST['nonce'], 'ajax_nonce_magic_post_thumbnail' ) ) {
            wp_send_json_error();
            // Send an error response if checks fail.
        }
        // Validate the presence of post IDs.
        if ( !isset( $_POST['ids_mpt_generation'] ) ) {
            return false;
        }
        // Exit if no post IDs are provided.
        // Count the number of posts for bulk processing.
        $count = count( $post_ids );
        // Re-index post IDs starting from 1 for consistency.
        foreach ( $post_ids as $key => $val ) {
            $post_ids_with_keys[$key + 1] = $val;
        }
        // Retrieve the current post index and ID from the AJAX request.
        $current_post_index = (int) $_POST['currentPostIndex'];
        $current_post_id = $post_ids_with_keys[$current_post_index];
        // Load plugin settings for image generation.
        $main_settings = get_option( 'MPT_plugin_main_settings' );
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
        $img_block = $img_blocks[$keys[$current_image_index]];
        // Current image block data.
        // display all infos under $img_block
        // Determine image location (featured or content).
        $image_location = ( !empty( $img_block['image_location'] ) ? $img_block['image_location'] : 'featured' );
        // Handle generation of the featured image.
        if ( has_post_thumbnail( $current_post_id ) && $rewrite_featured == false && $image_location == 'featured' ) {
            $generation_status = 'already-done';
            // Image already exists and rewriting is not needed.
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
                $button_autogenerate
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
        if ( true == $compatibility['enable_FIFU'] && 'FIFU' == $img_block['image_location'] && (is_plugin_active( 'featured-image-from-url/featured-image-from-url.php' ) || is_plugin_active( 'fifu-premium/fifu-premium.php' )) && $MPT_return != null ) {
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
        // Checks whether the capacity has already been checked for this session
        if ( get_option( 'mpt_hook_checked' ) ) {
            // Deletes the option immediately after execution
            delete_option( 'mpt_hook_checked' );
            return;
            // Exits the function if it has already been executed
        }
        // Set capacity as verified to avoid additional calls
        update_option( 'mpt_hook_checked', true );
        $transient_key = 'mpt_wp_insert_processing_post_' . $ID;
        if ( wp_is_post_revision( $ID ) || wp_is_post_autosave( $ID ) ) {
            return;
        }
        if ( get_transient( $transient_key ) ) {
            return;
            // This post is already being processed
        } else {
            // Defines the transient to indicate that the post is being processed
            set_transient( $transient_key, true, 120 );
            // Expires after 2 minutes
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
                    $button_autogenerate
                );
            }
        }
        // Delete the transient when processing is complete
        delete_transient( $transient_key );
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
        $button_autogenerate = false
    ) {
        // Launch logs
        $log = $this->MPT_monolog_call();
        $log->info( 'New generation starting', array(
            'post' => $id,
        ) );
        //Avoid revision post type
        if ( 'revision' == get_post_type( $id ) && true !== $avoid_revision ) {
            $log->error( 'This post is a revision. Avoided.', array(
                'post' => $id,
            ) );
            return false;
        }
        // Settings
        $main_settings = get_option( 'MPT_plugin_main_settings' );
        // one shot generation
        if ( null == $key_img_block ) {
            $key_img_block = array_key_first( $main_settings['image_block'] );
        }
        $img_block = $main_settings['image_block'][$key_img_block];
        if ( !isset( $img_block ) ) {
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
        if ( !current_user_can( 'mpt_manage' ) && !class_exists( 'Main_WPeMatico' ) && !class_exists( 'FeedWordPress' ) && !class_exists( 'rssPostImporter' ) && !class_exists( 'CyberSyn_Syndicator' ) && !class_exists( 'wp_automatic' ) ) {
            $log->error( 'The user does not have sufficient rights', array(
                'post' => $id,
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
            $options['api_chosen'] = $api_chosen;
            $img_block['api_chosen'] = $api_chosen;
        } else {
            $options['api_chosen'] = $img_block['api_chosen'];
        }
        $executeElseBlock = true;
        $postcategories = get_the_category( $id );
        if ( $executeElseBlock ) {
            if ( TRUE == $get_only_thumb ) {
                $search = $extracted_search_term;
            } elseif ( $img_block['based_on'] == 'text_analyser' ) {
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
        // Check if button "Generate Automatically" is clicked
        if ( true == $button_autogenerate && is_array( $options_banks['api_chosen_auto'] ) ) {
            $log->info( 'Button "Generate Automatically" clicked', array(
                'post' => $id,
            ) );
            $last_bank_element = end( $options_banks['api_chosen_auto'] );
            foreach ( $options_banks['api_chosen_auto'] as $bank ) {
                // Reset options according new image bank
                $options['api_chosen'] = $bank;
                $array_parameters = $this->MPT_Get_Parameters( $options, $options, $search );
                $api_url = $array_parameters['url'];
                unset($array_parameters['url']);
                if ( !isset( $api_url ) ) {
                    $log->error( 'API URL not provided', array(
                        'post' => $id,
                    ) );
                    continue;
                }
                /* GET THE IMAGE URL */
                list( $url_results, $file_media, $alt_img ) = $this->MPT_Generate(
                    $bank,
                    $api_url,
                    $array_parameters,
                    $img_block['selected_image'],
                    false,
                    $search
                );
                if ( !isset( $url_results ) || !isset( $file_media ) ) {
                    $log->info( 'No results with ' . $bank, array(
                        'post' => $id,
                    ) );
                    // Return false with no results with each image bank
                    if ( $bank === $last_bank_element ) {
                        $log->info( 'No image found with all banks selected', array(
                            'post' => $id,
                        ) );
                        return false;
                    }
                } else {
                    // OK
                    break;
                }
            }
        } else {
            // Process the main image block
            $result = $this->MPT_Process_Image_Block(
                $img_block,
                $options,
                $search,
                $log,
                $id
            );
            // If the first API call fails, try the second image bank
            if ( $result === false && isset( $img_block['api_chosen_2'] ) && $img_block['api_chosen_2'] !== 'none' ) {
                $log->info( 'Second image bank used', array(
                    'post' => $id,
                ) );
                $img_block['api_chosen'] = $img_block['api_chosen_2'];
                $result = $this->MPT_Process_Image_Block(
                    $img_block,
                    $options,
                    $search,
                    $log,
                    $id
                );
            }
            // If both API calls fail, return false
            if ( $result === false ) {
                return false;
            }
            // Extract results and continue processing
            extract( $result );
        }
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
            $imgextension = $path_parts['extension'];
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
        if ( $file_media['response']['code'] != '200' || empty( $file_media['body'] ) ) {
            $log->error( 'Problem with scrapping', array(
                'post' => $id,
            ) );
            return false;
        }
        if ( $file_media['body'] ) {
            /* Upload the file to wordpress directory */
            $file_upload = file_put_contents( $folder, $file_media['body'] );
            /* Convert png to jpeg for dalle */
            $png_jpg = ( !empty( $options['dallev1']['convert_jpg'] ) ? $options['dallev1']['convert_jpg'] : '' );
            //if( ( true == $png_jpg ) && ( 'dallev1' == $options['api_chosen'] ) ) {
            if ( true == $png_jpg && 'dallev1' == $img_block['api_chosen'] ) {
                $image = imagecreatefrompng( $folder );
                // Remove old png file
                unlink( $folder );
                $folder = str_replace( ".png", ".jpg", $folder );
                $filename = str_replace( ".png", ".jpg", $filename );
                imagejpeg( $image, $folder, 90 );
                imagedestroy( $image );
            }
            if ( $file_upload ) {
                $wp_filetype = wp_check_filetype( basename( $filename ), null );
                $wp_upload_dir = wp_upload_dir();
                $attachment = array(
                    'guid'           => $wp_upload_dir['url'] . '/' . urlencode( $filename ),
                    'post_mime_type' => $wp_filetype['type'],
                    'post_title'     => $search,
                    'post_content'   => '',
                    'post_status'    => 'inherit',
                );
                $attach_id = wp_insert_attachment( $attachment, $wp_upload_dir['path'] . '/' . urlencode( $filename ) );
                // Add alt text for image
                update_post_meta( $attach_id, '_wp_attachment_image_alt', $alt_img );
                // Add caption text for image
                if ( !empty( $caption_img ) ) {
                    wp_update_post( array(
                        'ID'           => $attach_id,
                        'post_excerpt' => $caption_img,
                    ) );
                }
                /* Fire filter "wp_handle_upload" for plugins like optimizers etc. */
                $img_values = array(
                    'file' => $wp_upload_dir['path'] . '/' . urlencode( $filename ),
                    'url'  => $wp_upload_dir['url'] . '/' . urlencode( $filename ),
                    'type' => $wp_filetype['type'],
                );
                apply_filters( 'wp_handle_upload', $img_values );
                require_once ABSPATH . 'wp-admin/includes/image.php';
                $attach_data = wp_generate_attachment_metadata( $attach_id, $wp_upload_dir['path'] . '/' . urlencode( $filename ) );
                $update_attach_data = wp_update_attachment_metadata( $attach_id, $attach_data );
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
                    remove_all_actions( 'save_post' );
                    wp_insert_post( array(
                        'ID'                    => $id,
                        'post_content'          => $content,
                        'post_category'         => $arr_post_category,
                        'post_title'            => $content_post->post_title,
                        'post_status'           => $content_post->post_status,
                        'post_author'           => $content_post->post_author,
                        'post_date'             => $content_post->post_date,
                        'post_date_gmt'         => $content_post->post_date_gmt,
                        'post_excerpt'          => $content_post->post_excerpt,
                        'comment_status'        => $content_post->comment_status,
                        'ping_status'           => $content_post->ping_status,
                        'post_password'         => $content_post->post_password,
                        'post_name'             => $content_post->post_name,
                        'to_ping'               => $content_post->to_ping,
                        'pinged'                => $content_post->pinged,
                        'post_modified'         => $content_post->post_modified,
                        'post_modified_gmt'     => $content_post->post_modified_gmt,
                        'post_content_filtered' => $content_post->post_content_filtered,
                        'post_parent'           => $content_post->post_parent,
                        'menu_order'            => $content_post->menu_order,
                        'post_type'             => $content_post->post_type,
                        'post_mime_type'        => $content_post->post_mime_type,
                    ) );
                    add_action( 'save_post', array(&$this, 'MPT_create_thumb') );
                    $log->info( 'Image added into the post', array(
                        'post'  => $id,
                        'image' => $attach_id,
                    ) );
                }
                do_action( 'mpt_after_create_thumb', $id, $attach_id );
                if ( true === $include_datas ) {
                    return array(
                        'id'             => $attach_id,
                        'keyword'        => $keywords_search,
                        'img_resolution' => $attach_data['width'] . 'x' . $attach_data['height'] . 'px',
                        'img_size'       => $this->MPT_get_image_size_in_bytes( $attach_id ),
                        'api_chosen'     => $img_block['api_chosen'],
                    );
                } else {
                    return $attach_id;
                }
            }
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
        // Get the image URL
        list( $url_results, $file_media, $alt_img, $caption_img ) = $this->MPT_Generate(
            $img_block['api_chosen'],
            $api_url,
            $array_parameters,
            $img_block['selected_image'],
            false,
            $search
        );
        // Check if results are valid
        if ( !isset( $url_results ) || !isset( $file_media ) ) {
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
     * Insert an image before or after a specific Gutenberg block
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
        // Check if the Classic Editor is being used
        $classic_editor = $this->MPT_is_using_classic_editor();
        $match = false;
        // Variable to track if the image was inserted successfully
        // Loop until the image insertion is successful
        while ( !$match ) {
            // Prepare the HTML block for the image depending on the editor type
            if ( $classic_editor ) {
                $match = true;
                // Set match to true since we'll insert the image for Classic Editor
                $image = wp_get_attachment_image(
                    $attach_id,
                    $image_size,
                    false,
                    array(
                        'class' => 'wp-image-' . $attach_id,
                    )
                );
                $image_block = '<p>' . $image . '</p>';
            } else {
                // Get image details for Gutenberg
                $image_src = wp_get_attachment_image_src( $attach_id, $image_size, false );
                $alt_text = get_post_meta( $attach_id, '_wp_attachment_image_alt', true );
                $image = '<img src="' . esc_url( $image_src[0] ) . '" alt="' . esc_attr( $alt_text ) . '" class="wp-image-' . esc_attr( $attach_id ) . '"/>';
                // Include caption if enabled in settings
                $options = wp_parse_args( get_option( 'MPT_plugin_main_settings' ), $this->MPT_default_options_main_settings( TRUE ) );
                if ( isset( $options['enable_caption'] ) && 'enable' == $options['enable_caption'] ) {
                    $caption_text = wp_get_attachment_caption( $attach_id );
                    $image .= '<figcaption class="wp-element-caption">' . esc_html( $caption_text ) . '</figcaption>';
                }
                // Wrap the image in a Gutenberg image block format
                $image_block = '<!-- wp:image {"id":' . $attach_id . ',"linkDestination":"none"} -->';
                $image_block .= '<figure class="wp-block-image">' . $image . '</figure>';
                $image_block .= '<!-- /wp:image -->';
            }
            // Define the tag-specific search pattern
            if ( $classic_editor ) {
                // Patterns for Classic Editor content
                $start_pattern = '<' . $tag;
                $end_pattern = '</' . $tag . '>';
                $pattern = '/(' . preg_quote( $start_pattern, '/' ) . '.*?' . preg_quote( $end_pattern, '/' ) . ')/s';
                $parts = preg_split(
                    $pattern,
                    $content,
                    -1,
                    PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY
                );
                // Remove empty content
                $filtered_array = array_filter( $parts, function ( $value ) {
                    return trim( $value ) !== '';
                } );
                // Re-indexing of table keys if necessary
                $parts = array_values( $filtered_array );
                // Restart the loop if can not find paragraphs
                if ( 'p' == $tag && count( $parts ) < 2 ) {
                    //Apply wpautop() for next loop
                    $content = wpautop( $content );
                    $match = false;
                    continue;
                }
            } else {
                // Patterns for Gutenberg Editor, ensuring separate blocks
                if ( $tag == 'p' ) {
                    $start_pattern = '<!-- wp:paragraph';
                    $end_pattern = '<\\/p>\\s*<!-- \\/wp:paragraph -->';
                } elseif ( $tag == 'a' ) {
                    $start_pattern = '<!-- wp:html -->';
                    $end_pattern = '<!-- /wp:html -->';
                } else {
                    $start_pattern = '<!-- wp:heading';
                    $end_pattern = '<\\/h[1-6]>\\s*<!-- \\/wp:heading -->';
                }
                $pattern = '/(' . preg_quote( $start_pattern, '/' ) . '.*?' . $end_pattern . ')/s';
                $parts = preg_split(
                    $pattern,
                    $content,
                    -1,
                    PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY
                );
            }
            // Initialize variables for constructing the new content
            $new_content = '';
            $counter = 0;
            $total_tags = substr_count( $content, $start_pattern );
            // Loop through each part to build the new content with the image block inserted
            foreach ( $parts as $part ) {
                // Check if part contains the target tag
                $contains_tag = preg_match( $pattern, $part );
                // Insert image before tag if 'before' placement is specified
                if ( $placement == 'before' && $contains_tag ) {
                    $counter++;
                    if ( $position == 'last' && $counter == $total_tags || $counter == $position ) {
                        if ( $classic_editor ) {
                            $new_content .= '</p>' . $image_block . '<p>';
                        } else {
                            $new_content .= $image_block;
                        }
                        $match = true;
                    }
                }
                // Add the current part to new content
                $new_content .= $part;
                // Insert image after tag if 'after' placement is specified
                if ( $placement == 'after' && $contains_tag ) {
                    $counter++;
                    if ( $position == 'last' && $counter == $total_tags || $counter == $position ) {
                        if ( $classic_editor ) {
                            $new_content .= '</p>' . $image_block . '<p>';
                        } else {
                            $new_content .= $image_block;
                        }
                        $match = true;
                    }
                }
            }
            // If no match found, switch to Classic Editor as a fallback
            if ( !$match ) {
                $classic_editor = true;
            }
        }
        return $new_content;
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
        /* GOOGLE IMAGE SCRAPING PARAMETERS */
        if ( $img_block['api_chosen'] == 'google_scraping' ) {
            $country = ( !empty( $options['google_scraping']['search_country'] ) ? $options['google_scraping']['search_country'] : 'en' );
            $img_color = ( !empty( $options['google_scraping']['img_color'] ) ? $options['google_scraping']['img_color'] : '' );
            $imgsz = ( !empty( $options['google_scraping']['imgsz'] ) ? $options['google_scraping']['imgsz'] : '' );
            $format = ( !empty( $options['google_scraping']['format'] ) ? $options['google_scraping']['format'] : '' );
            $imgtype = ( !empty( $options['google_scraping']['imgtype'] ) ? $options['google_scraping']['imgtype'] : '' );
            $rights = ( !empty( $options['google_scraping']['rights'] ) ? $options['google_scraping']['rights'] : '' );
            $safe = ( !empty( $options['google_scraping']['safe'] ) ? $options['google_scraping']['safe'] : 'medium' );
            // Old API option. Replace value for safe
            if ( $safe == 'moderate' ) {
                $safe = 'medium';
            }
            // Remove very special characters
            $search = str_replace( '…', '', $search );
            $array_parameters = array(
                'url'  => 'https://www.google.com/search',
                'tbm'  => 'isch',
                'q'    => $search,
                'hl'   => $country,
                'safe' => $safe,
                'pws'  => '0',
                'ijn'  => '0',
                'rsz'  => '3',
                'tbs'  => '',
            );
            if ( !empty( $rights ) ) {
                $array_parameters['tbs'] .= 'sur:' . $rights . ',';
            }
            if ( !empty( $imgtype ) ) {
                $array_parameters['tbs'] .= 'itp:' . $imgtype . ',';
            }
            if ( !empty( $imgsz ) ) {
                $array_parameters['tbs'] .= 'isz:' . $imgsz . ',';
            }
            if ( !empty( $format ) ) {
                $array_parameters['tbs'] .= 'iar:' . $format . ',';
            }
            if ( !empty( $img_color ) ) {
                $array_parameters['tbs'] .= 'ic:specific,isc:' . $img_color;
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
        } elseif ( $img_block['api_chosen'] == 'dallev1' ) {
            $api_key = ( !empty( $options['dallev1']['apikey'] ) ? $options['dallev1']['apikey'] : '' );
            $img_size = ( !empty( $options['dallev1']['imgsize'] ) ? $options['dallev1']['imgsize'] : '1024x1024' );
            $array_parameters = array(
                'url'         => 'https://api.openai.com/v1/images/generations',
                'redirection' => 2,
                'method'      => 'POST',
                'timeout'     => 30,
                'headers'     => array(
                    'Content-Type'  => 'application/json',
                    'Authorization' => 'Bearer ' . $api_key,
                ),
                'body'        => json_encode( array(
                    "model"   => "dall-e-3",
                    "style"   => "vivid",
                    "quality" => 'hd',
                    "prompt"  => 'Photorealistic image of ' . $search,
                    "n"       => 1,
                    "size"    => $img_size,
                ) ),
            );
        } elseif ( isset( $img_block['api_chosen'] ) && $img_block['api_chosen'] === 'replicate' ) {
        } elseif ( $img_block['api_chosen'] == 'stability' ) {
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
                'text'           => urlencode( $search ),
                'per_page'       => '16',
                'format'         => 'json',
                'nojsoncallback' => '1',
                'privacy_filter' => '1',
                'license'        => $rights,
                'sort'           => 'relevance',
                'content_type'   => $imgtype,
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
            $sources = array(
                'wordpress',
                'woc_tech',
                'wikimedia',
                'wellcome_collection',
                'thorvaldsensmuseum',
                'thingiverse',
                'svgsilh',
                'statensmuseum',
                'spacex',
                'smithsonian_zoo_and_conservation',
                'smithsonian_postal_museum',
                'smithsonian_portrait_gallery',
                'smithsonian_national_museum_of_natural_history',
                'smithsonian_libraries',
                'smithsonian_institution_archives',
                'smithsonian_hirshhorn_museum',
                'smithsonian_gardens',
                'smithsonian_freer_gallery_of_art',
                'smithsonian_cooper_hewitt_museum',
                'smithsonian_anacostia_museum',
                'smithsonian_american_indian_museum',
                'smithsonian_american_history_museum',
                'smithsonian_american_art_museum',
                'smithsonian_air_and_space_museum',
                'smithsonian_african_art_museum',
                'smithsonian_african_american_history_museum',
                'sketchfab',
                'sciencemuseum',
                'rijksmuseum',
                'rawpixel',
                'phylopic',
                'nypl',
                'nasa',
                'museumsvictoria',
                'met',
                'mccordmuseum',
                'iha',
                'geographorguk',
                'floraon',
                'flickr',
                'europeana',
                'eol',
                'digitaltmuseum',
                'deviantart',
                'clevelandmuseum',
                'brooklynmuseum',
                'bio_diversity',
                'behance',
                'animaldiversity',
                'WoRMS',
                'CAPL',
                '500px'
            );
            $sources_with_comma = implode( ",", $sources );
            $array_parameters = array(
                'url'          => 'https://api.openverse.engineering/v1/images/',
                'q'            => urlencode( $search ),
                'imgtype'      => urlencode( $imgtype ),
                'aspect_ratio' => urlencode( $aspect_ratio ),
                'source'       => $sources_with_comma,
            );
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
        $search = ''
    ) {
        $log = $this->MPT_monolog_call();
        list( $result_body, $result ) = $this->MPT_get_results( $service, $url, $url_parameters );
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
        if ( $service == 'google_image' ) {
            $loop_results = $result_body['items'];
            // TODO : Check if urls are real images or just redirections
            $url_path = 'pagemap';
        } elseif ( $service == 'google_scraping' ) {
            $loop_results = $result_body['results'];
            $url_path = 'url';
        } elseif ( $service == 'dallev1' ) {
            $loop_results = $result_body['data'];
            $url_path = 'url';
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
        } elseif ( $service == 'cc_search' ) {
            $loop_results = $result_body['results'];
            $url_path = 'url';
        } elseif ( $service == 'replicate' ) {
            $loop_results = ( isset( $result_body['output'] ) ? (array) $result_body['output'] : array() );
            $url_path = null;
        } else {
            return false;
        }
        // Check if function is launch for Gutenberg block
        /* if( ( TRUE == $get_only_thumb ) && ( $service == 'envato' ) ) { // DISABLED - Envato Elements no longer working
        		return $result_body['results']['search_query_result']['search_payload'];
        	} else */
        if ( TRUE == $get_only_thumb && 'flickr' != $service ) {
            return $result_body;
        } else {
        }
        /* Random Image */
        if ( $selected_image == 'random_result' && $service != 'dallev1' && $service != 'stability' ) {
            @shuffle( $loop_results );
        }
        // Testing images
        if ( $service == 'google_scraping' ) {
            foreach ( $loop_results as $loop_result_result => $loop_result ) {
                $img_host = wp_parse_url( $loop_result['url'], PHP_URL_HOST );
                if ( !is_string( $img_host ) || $this->MPT_google_scraping_is_private_host( $img_host ) ) {
                    unset($loop_results[$loop_result_result]);
                    continue;
                }
                $remote_img = wp_remote_head( $loop_result['url'] );
                $remote_response = wp_remote_retrieve_response_code( $remote_img );
                $log = $this->MPT_monolog_call();
                $log->info( 'Remote image', array(
                    'remote_img' => $loop_result,
                ) );
                if ( 200 !== $remote_response ) {
                    // Remove the result, image not valid
                    unset($loop_results[$loop_result_result]);
                } else {
                    // Image ok. Avoid next results.
                    break;
                }
                if ( $loop_result['url'] ) {
                    $infos_img = @getimagesize( $loop_result['url'] );
                } else {
                    $infos_img = false;
                }
                if ( false === $infos_img ) {
                    // Remove the result, image not valid
                    unset($loop_results[$loop_result_result]);
                } else {
                    // Image ok. Avoid next results.
                    break;
                }
            }
        }
        if ( !empty( $loop_results ) ) {
            $loop_count = 0;
            $numUrl = count( $loop_results );
            foreach ( $loop_results as $fetch_result_key => $fetch_result ) {
                if ( 'image' != $fetch_result_key && $service == 'stability' ) {
                    continue;
                }
                if ( $service == 'replicate' ) {
                    $url_result = $fetch_result;
                } elseif ( $service !== 'stability' ) {
                    $url_result = $fetch_result[$url_path];
                }
                // Change default url image
                if ( $service == 'google_image' ) {
                    $url_result = $url_result['cse_image'][0]['src'];
                } elseif ( $service == 'unsplash' ) {
                    $url_result = $url_result['full'];
                } elseif ( $service == 'pexels' ) {
                    $url_result = $url_result['original'];
                } elseif ( $service == 'dallev1' ) {
                    $url_result = $fetch_result[$url_path];
                    // Show revised prompt (by openAI) in logs
                    $log = $this->MPT_monolog_call();
                    $log->info( 'DALL_E', array(
                        'revised_prompt' => $fetch_result['revised_prompt'],
                    ) );
                } elseif ( $service == 'stability' ) {
                    $url_result = $fetch_result;
                } elseif ( $service == 'replicate' ) {
                    // For replicate, $fetch_result is already the final URL
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
                    } elseif ( $service == 'cc_search' ) {
                        $caption = $fetch_result['creator'];
                    } elseif ( $service == 'google_scraping' ) {
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
                    if ( 'author_bank' == $options['caption_from'] && $service != 'google_scraping' ) {
                        $caption .= esc_html__( ' from ', 'mpt' ) . ucfirst( $service );
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
                // ENVATO : Additional remote request to get image url - DISABLED (no longer working)
                /*
                if( $service == 'envato' ) {
                
                		$url 				= 'https://api.extensions.envato.com/extensions/item/' . $url_result . '/download';
                		$project_ags 		= array( 'project_name' => get_bloginfo('name') );
                		$result_img_envato 	= wp_remote_post(
                			add_query_arg($project_ags, $url),
                			array(
                				'headers' => array(
                					"Extensions-Extension-Id" 	=> md5( get_site_url() ),
                					"Extensions-Token" 			=> $url_parameters['envato_token'],
                					"Content-Type"				=> "application/json"
                				),
                			)
                		);
                		$result 			= json_decode( $result_img_envato['body'] );
                		$url_result			 = $result->download_urls->max2000;
                
                }
                */
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
                // Avoid unknown image type
                if ( $service != 'dallev1' && $service != 'stability' && $service != 'unsplash' && $service != 'pexels' ) {
                    $url_result = $url_result;
                    $wp_filetype = wp_check_filetype( $url_result );
                    if ( false == $wp_filetype['type'] ) {
                        continue;
                    }
                }
                if ( TRUE == $get_only_thumb ) {
                    $url_result_ar['photos'][]['url'] = $url_result;
                } else {
                    if ( 'stability' == $service ) {
                        if ( base64_decode( $url_result, true ) !== false ) {
                            // Decoding the image in Base64
                            $image_data = base64_decode( $url_result );
                            $options_banks = wp_parse_args( get_option( 'MPT_plugin_banks_settings' ), $this->MPT_default_options_banks_settings( TRUE ) );
                            // Specify a file path for the image in the download directory
                            $upload_dir = wp_upload_dir();
                            $file_path = $upload_dir['path'] . '/temp_mpt_stability_' . uniqid() . '.' . $options_banks['stability']['output_format'];
                            // Save the decoded image in the file
                            file_put_contents( $file_path, $image_data );
                            if ( file_exists( $file_path ) ) {
                                $file_content = file_get_contents( $file_path );
                            }
                            $file_media['response']['code'] = '200';
                            $file_media['body'] = $file_content;
                            $file_media['headers']['content-type'] = 'image/' . $options_banks['stability']['output_format'];
                            // Remove temporary file
                            unlink( $file_path );
                        }
                    } else {
                        $file_media = @wp_remote_request( $url_result );
                        if ( isset( $file_media->errors ) || $file_media['response']['code'] != 200 || strpos( $file_media['headers']['content-type'], 'text/html' ) !== false ) {
                            if ( ++$loop_count === $numUrl ) {
                                return false;
                            } else {
                                continue;
                            }
                        } else {
                            break;
                        }
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
        if ( $service == 'dallev1' || $service == 'stability' || $service == 'pexels' || $service == 'replicate' ) {
            $defaults = $url_parameters;
        } else {
            /* Retrieve 3 images as result */
            $url = add_query_arg( $url_parameters, $url );
            if ( 'google_scraping' === $service ) {
                $url = $this->MPT_google_scraping_sanitize_url( $url );
            }
            // Modern browser fingerprint (Google often blocks very old Chrome UAs).
            $defaults = $this->MPT_google_scraping_http_defaults();
        }
        // Proxy settins
        $options_proxy = get_option( 'MPT_plugin_proxy_settings' );
        if ( 'google_scraping' === $service ) {
            $log = $this->MPT_monolog_call();
            if ( $this->MPT_google_scraping_use_bing_primary() ) {
                // Skip slow / unreliable Google HTML fetch (cURL H2, raw socket, wp_remote). Bing is primary in build_results.
                $result = array(
                    'body'     => '',
                    'response' => array(
                        'code' => 200,
                    ),
                    'headers'  => array(),
                );
            } else {
                /*
                 * Legacy: full Google Images HTML fetch (often slow / blocked on shared hosting).
                 */
                $result = $this->MPT_google_scraping_curl_h2_get( $url, $defaults );
                if ( is_wp_error( $result ) || strlen( ( isset( $result['body'] ) ? $result['body'] : '' ) ) < 500 ) {
                    if ( is_wp_error( $result ) ) {
                        $log->info( 'Google scraping: cURL HTTP/2 failed for initial request, trying raw socket', array(
                            'error' => $result->get_error_message(),
                        ) );
                    } else {
                        $log->info( 'Google scraping: cURL HTTP/2 returned short body for initial request, trying raw socket', array(
                            'body_len' => strlen( ( isset( $result['body'] ) ? $result['body'] : '' ) ),
                        ) );
                    }
                    $result = $this->MPT_google_scraping_raw_socket_get( $url, $defaults );
                    if ( is_wp_error( $result ) ) {
                        $log->info( 'Google scraping: raw socket also failed, trying wp_remote_request', array(
                            'error' => $result->get_error_message(),
                        ) );
                        $result = wp_remote_request( $url, $defaults );
                    }
                }
            }
        } else {
            $result = wp_remote_request( $url, $defaults );
        }
        // If error happen
        if ( is_wp_error( $result ) ) {
            if ( 'google_scraping' === $service ) {
                $log = $this->MPT_monolog_call();
                $log->error( 'Google scraping: initial HTTP request failed', array(
                    'error' => $result->get_error_message(),
                    'url'   => $url,
                ) );
            }
            return false;
        }
        if ( 'google_scraping' === $service && $this->MPT_google_scraping_diagnostic_logging_enabled() && !$this->MPT_google_scraping_use_bing_primary() ) {
            $this->MPT_google_scraping_diagnostic_log_request(
                'initial_request',
                $url,
                $defaults,
                '',
                ''
            );
        }
        // Google Scraping : Different method (Google HTML + optional consent + Bing fallback).
        if ( $service == 'google_scraping' ) {
            $result_body = $this->MPT_google_scraping_build_results( $url, $defaults, $result );
        } else {
            $result_body = json_decode( $result['body'], true );
            $log = $this->MPT_monolog_call();
            // Dall-e: Catch the error
            if ( $service == 'dallev1' && $result_body['error']['message'] ) {
                //error_log( print_r( $result, true ) );
                $log->info( 'Problem with Dalle', array(
                    'Error message' => $result_body['error']['message'],
                ) );
            }
            // accept 200 for most services, 201 for replicate
            $code = intval( $result['response']['code'] );
            if ( $service === 'replicate' && $code !== 201 || $service !== 'replicate' && $code !== 200 ) {
                return false;
            }
        }
        return array($result_body, $result);
    }

    /**
     * Normalize site/domain restrictions before adding them to a Google query.
     *
     * @since 4.2.0
     * @param string $domains Raw textarea value from plugin settings.
     * @return array<int, string>
     */
    private function MPT_google_scraping_normalize_domain_rules( $domains ) {
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
            if ( '' === $domain ) {
                continue;
            }
            $normalized_domains[] = strtolower( $domain );
        }
        return array_values( array_unique( $normalized_domains ) );
    }

    /**
     * Banks option: use Bing Images when Google scraping yields no results (default enabled).
     *
     * @since 4.2.0
     * @return bool
     */
    private function MPT_google_scraping_bing_fallback_option_enabled() {
        $banks = wp_parse_args( get_option( 'MPT_plugin_banks_settings' ), $this->MPT_default_options_banks_settings( true ) );
        $fb = ( isset( $banks['google_scraping']['bing_fallback'] ) ? (string) $banks['google_scraping']['bing_fallback'] : '' );
        // Default on: option array is not deep-merged, so older saves may omit this key.
        return 'false' !== $fb;
    }

    /**
     * When true, skip Google HTML / headless pipeline and use Bing Images as the primary source.
     * Filter: `mpt_google_scraping_use_bing_primary` (default true until Google HTML path is viable again).
     *
     * @since 6.2.1
     * @return bool
     */
    private function MPT_google_scraping_use_bing_primary() {
        return (bool) apply_filters( 'mpt_google_scraping_use_bing_primary', true );
    }

    /**
     * HTTP defaults for Google/Bing scraping (browser-like mobile HTML request).
     *
     * @since 4.2.0
     * @return array
     */
    private function MPT_google_scraping_http_defaults() {
        $sslverify = (bool) apply_filters( 'mpt_google_scraping_sslverify', false );
        return array(
            'redirection'        => 4,
            'timeout'            => 3,
            'user-agent'         => $this->MPT_google_scraping_get_default_user_agent(),
            'headers'            => array(
                'Accept'                    => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
                'Accept-Language'           => 'en-US,en;q=0.9',
                'Upgrade-Insecure-Requests' => '1',
            ),
            'reject_unsafe_urls' => false,
            'sslverify'          => $sslverify,
        );
    }

    /**
     * First User-Agent in the candidate list (used for the initial wp_remote_request).
     *
     * @since 4.2.0
     * @return string
     */
    private function MPT_google_scraping_get_default_user_agent() {
        $candidates = $this->MPT_google_scraping_get_user_agent_candidates();
        return ( !empty( $candidates[0] ) ? $candidates[0] : 'Mozilla/5.0 (Linux; Android 6.0.1; Nexus 5X Build/MMB29P) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2272.96' );
    }

    /**
     * User-Agent strings to try in order (legacy mobile + text-mode clients). Filter: `mpt_google_scraping_user_agents`.
     * Set `mpt_google_scraping_try_alternate_user_agents` to false to only use the first entry (faster).
     *
     * @since 4.2.0
     * @return array<int, string>
     */
    private function MPT_google_scraping_get_user_agent_candidates() {
        $list = array(
            'Mozilla/5.0 (Linux; Android 6.0.1; Nexus 5X Build/MMB29P) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2272.96',
            'Mozilla/5.0 (Linux; Android 5.1.1; SM-G925F Build/LMY47X) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/45.0.2454.94 Mobile Safari/537.36',
            'Mozilla/5.0 (Linux; Android 4.4.2; Nexus 5 Build/KOT49H) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/46.0.2490.76 Mobile Safari/537.36',
            'Mozilla/5.0 (iPhone; CPU iPhone OS 10_3_3 like Mac OS X) AppleWebKit/603.3.8 (KHTML, like Gecko) Version/10.0 Mobile/14G60 Safari/602.1',
            'Mozilla/5.0 (Linux; Android 5.1; Mobile) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/50.0.2661.89 Mobile Safari/537.36',
            'Mozilla/5.0 (Linux; U; Android 4.0.3; en-gb; Galaxy Nexus Build/IML74K) AppleWebKit/534.30 (KHTML, like Gecko) Version/4.0 Mobile Safari/534.30',
            // Legacy desktop browsers (sometimes get simpler HTML).
            'Mozilla/5.0 (Windows NT 6.1; Trident/7.0; rv:11.0) like Gecko',
            // IE11
            'Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; Trident/6.0)',
            // IE10
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/42.0.2311.135 Safari/537.36 Edge/12.246',
            // Edge Legacy
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_6_8) AppleWebKit/534.57.2 (KHTML, like Gecko) Version/5.1.7 Safari/534.57.2',
            // Safari 5
            'Opera/9.80 (Android; Opera Mini/36.2.2254/191.288; U; en) Presto/2.12.423 Version/12.16',
            'Mozilla/5.0 (Android 4.4; Mobile; rv:41.0) Gecko/41.0 Firefox/41.0',
            'Lynx/2.8.9rel.1 libwww-FM/2.14 SSL-MM/1.4.1 OpenSSL/1.0.2',
            'Links (2.25; Linux; gzip)',
        );
        $list = apply_filters( 'mpt_google_scraping_user_agents', $list );
        if ( !apply_filters( 'mpt_google_scraping_try_alternate_user_agents', true ) ) {
            $list = array_slice( $list, 0, 1 );
        }
        $list = array_values( array_unique( array_filter( array_map( 'strval', $list ) ) ) );
        if ( empty( $list ) ) {
            $list = array('Mozilla/5.0 (Linux; Android 6.0.1; Nexus 5X Build/MMB29P) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2272.96');
        }
        return $list;
    }

    /**
     * Headers tuned to the current User-Agent family.
     *
     * Some very old UAs should not advertise WebP or modern Accept headers.
     *
     * @since 4.2.0
     * @param string $ua User-Agent string.
     * @return array<string, string>
     */
    private function MPT_google_scraping_header_profile_for_user_agent( $ua ) {
        $ua = (string) $ua;
        // Defaults (modern-ish HTML).
        $headers = array(
            'Accept'          => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'Accept-Language' => 'en-US,en;q=0.9',
        );
        // IE / Trident: no WebP, keep it simple.
        if ( false !== stripos( $ua, 'Trident/' ) || false !== stripos( $ua, 'MSIE' ) ) {
            $headers['Accept'] = 'text/html, application/xhtml+xml, */*';
            $headers['Accept-Language'] = 'en-US,en;q=0.5';
            return $headers;
        }
        // Opera Mini / old Android browsers: reduce modern signals.
        if ( false !== stripos( $ua, 'Opera Mini' ) || false !== stripos( $ua, 'Presto' ) ) {
            $headers['Accept'] = 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8';
            $headers['Accept-Language'] = 'en-US,en;q=0.7';
            return $headers;
        }
        // Text browsers.
        if ( false !== stripos( $ua, 'Lynx' ) || false !== stripos( $ua, 'Links' ) ) {
            $headers['Accept'] = 'text/html, */*;q=0.1';
            $headers['Accept-Language'] = 'en-US,en;q=0.5';
            return $headers;
        }
        return $headers;
    }

    /**
     * Ensure Google URLs are safe for strict URL parsers (cURL rejects spaces).
     *
     * WordPress can sometimes produce URLs that still contain literal spaces
     * when values include special operators. This helper makes them safe without
     * changing the semantics of the query.
     *
     * @since 4.2.0
     * @param string $url URL to sanitize.
     * @return string
     */
    private function MPT_google_scraping_sanitize_url( $url ) {
        $url = (string) $url;
        if ( '' === $url ) {
            return $url;
        }
        // Hard rule: spaces must never appear in a URL passed to cURL.
        $url = str_replace( ' ', '%20', $url );
        // Remove ASCII control chars that can break URL parsing.
        $url = preg_replace( '/[\\x00-\\x1F\\x7F]+/', '', $url );
        $host = wp_parse_url( $url, PHP_URL_HOST );
        if ( is_string( $host ) ) {
            $host = strtolower( $host );
            $allowed = array(
                'google.com',
                'www.google.com',
                'images.google.com',
                'consent.google.com',
                'www.bing.com',
                'bing.com'
            );
            $match = false;
            foreach ( $allowed as $domain ) {
                if ( $host === $domain || substr( $host, -strlen( '.' . $domain ) ) === '.' . $domain ) {
                    $match = true;
                    break;
                }
            }
            if ( !$match ) {
                return '';
            }
        }
        return $url;
    }

    /**
     * Cache key for Google scraping winning strategy.
     *
     * @return string
     */
    private function MPT_google_scraping_strategy_cache_key() {
        $blog_id = ( function_exists( 'get_current_blog_id' ) ? (int) get_current_blog_id() : 0 );
        return 'mpt_google_scraping_strategy_v1_' . $blog_id;
    }

    /**
     * Read cached winning strategy (UA + variant + cURL H2 config tag).
     *
     * @return array<string,mixed>
     */
    private function MPT_google_scraping_get_cached_strategy() {
        if ( null !== $this->google_scraping_strategy_cache ) {
            return ( is_array( $this->google_scraping_strategy_cache ) ? $this->google_scraping_strategy_cache : array() );
        }
        $cache = array();
        if ( function_exists( 'get_transient' ) ) {
            $stored = get_transient( $this->MPT_google_scraping_strategy_cache_key() );
            if ( is_array( $stored ) ) {
                $cache = $stored;
            }
        }
        $this->google_scraping_strategy_cache = $cache;
        return $cache;
    }

    /**
     * Persist strategy updates for faster subsequent Google requests.
     *
     * @param array<string,mixed> $updates Cache updates.
     * @return void
     */
    private function MPT_google_scraping_update_cached_strategy( $updates ) {
        if ( !is_array( $updates ) || empty( $updates ) ) {
            return;
        }
        $current = $this->MPT_google_scraping_get_cached_strategy();
        $next = array_merge( $current, $updates );
        $next['updated_at'] = time();
        $this->google_scraping_strategy_cache = $next;
        if ( function_exists( 'set_transient' ) ) {
            set_transient( $this->MPT_google_scraping_strategy_cache_key(), $next, 12 * HOUR_IN_SECONDS );
        }
        $log = $this->MPT_monolog_call();
        $log->info( 'Google scraping: cached strategy updated', array(
            'ua_hint'       => ( isset( $next['ua'] ) ? substr( (string) $next['ua'], 0, 120 ) : '' ),
            'variant_index' => ( isset( $next['variant_index'] ) ? (int) $next['variant_index'] : 0 ),
            'h2_config'     => ( isset( $next['h2_config'] ) ? (string) $next['h2_config'] : '' ),
        ) );
    }

    /**
     * Whether to log detailed Google scraping diagnostics (request/response/env) for comparison with a real browser.
     * Default: true when WP_DEBUG is on. Override with filter `mpt_google_scraping_diagnostic_logging`.
     *
     * @since 4.2.0
     * @return bool
     */
    private function MPT_google_scraping_diagnostic_logging_enabled() {
        $default = defined( 'WP_DEBUG' ) && WP_DEBUG;
        return (bool) apply_filters( 'mpt_google_scraping_diagnostic_logging', $default );
    }

    /**
     * Snapshot of PHP/HTTP stack (TLS fingerprint is not exposed by WordPress; noted in logs).
     *
     * @since 4.2.0
     * @return array<string, mixed>
     */
    private function MPT_google_scraping_get_environment_snapshot() {
        $snapshot = array(
            'php_version'            => PHP_VERSION,
            'sapi'                   => PHP_SAPI,
            'openssl_version'        => ( defined( 'OPENSSL_VERSION_TEXT' ) ? OPENSSL_VERSION_TEXT : '' ),
            'curl_extension'         => extension_loaded( 'curl' ),
            'wordpress_http_version' => ( function_exists( 'wp_http_supports' ) ? ( wp_http_supports( array('https') ) ? 'https_supported' : 'https_unknown' ) : '' ),
        );
        if ( function_exists( 'curl_version' ) ) {
            $cv = curl_version();
            if ( is_array( $cv ) ) {
                $snapshot['curl_version'] = ( isset( $cv['version'] ) ? $cv['version'] : '' );
                $snapshot['curl_ssl_version'] = ( isset( $cv['ssl_version'] ) ? $cv['ssl_version'] : '' );
            }
        }
        return $snapshot;
    }

    /**
     * Build the same header set WordPress will send for a Google scraping GET (defaults + Cookie + Referer).
     *
     * @since 4.2.0
     * @param array  $defaults   wp_remote_* args.
     * @param string $cookies    Raw Cookie header value.
     * @param string $referer    Referer URL.
     * @param string $request_url Request URL (for Sec-Fetch-Site logic).
     * @return array<string, string>
     */
    private function MPT_google_scraping_prepare_request_headers(
        $defaults,
        $cookies,
        $referer,
        $request_url
    ) {
        $headers = ( isset( $defaults['headers'] ) && is_array( $defaults['headers'] ) ? $defaults['headers'] : array() );
        $ua = ( isset( $defaults['user-agent'] ) ? (string) $defaults['user-agent'] : '' );
        // Merge an UA-specific header profile (caller headers take precedence).
        $headers = array_merge( $this->MPT_google_scraping_header_profile_for_user_agent( $ua ), $headers );
        if ( '' !== $cookies ) {
            $headers['Cookie'] = $cookies;
        }
        if ( '' !== $referer ) {
            $headers['Referer'] = $referer;
        }
        if ( isset( $headers['Sec-Fetch-Site'] ) && $referer === $request_url ) {
            $headers['Sec-Fetch-Site'] = 'same-origin';
        } elseif ( isset( $headers['Sec-Fetch-Site'] ) && '' !== $referer ) {
            $headers['Sec-Fetch-Site'] = 'same-site';
        }
        return $headers;
    }

    /**
     * Cookie names from a Cookie header (values not logged).
     *
     * @since 4.2.0
     * @param string $cookie_header Cookie header value.
     * @return array<int, string>
     */
    private function MPT_google_scraping_cookie_names_from_header( $cookie_header ) {
        $names = array();
        if ( !is_string( $cookie_header ) || '' === trim( $cookie_header ) ) {
            return $names;
        }
        foreach ( explode( ';', $cookie_header ) as $part ) {
            $part = trim( $part );
            if ( '' === $part || false === strpos( $part, '=' ) ) {
                continue;
            }
            list( $name ) = explode( '=', $part, 2 );
            $name = trim( $name );
            if ( '' !== $name ) {
                $names[] = $name;
            }
        }
        return array_values( array_unique( $names ) );
    }

    /**
     * Selected response headers for logs (Set-Cookie: names only).
     *
     * @since 4.2.0
     * @param array|\WP_HTTP_Requests_Response $response wp_remote_* response.
     * @return array<string, mixed>
     */
    private function MPT_google_scraping_response_headers_for_log( $response ) {
        $out = array();
        if ( !is_array( $response ) || empty( $response['headers'] ) ) {
            return $out;
        }
        $headers = $response['headers'];
        $interesting = array(
            'content-type',
            'server',
            'date',
            'cache-control',
            'alt-svc',
            'x-frame-options',
            'strict-transport-security',
            'location'
        );
        foreach ( $interesting as $key ) {
            if ( isset( $headers[$key] ) ) {
                $out[$key] = $headers[$key];
            }
        }
        if ( isset( $headers['set-cookie'] ) ) {
            $lines = $headers['set-cookie'];
            if ( !is_array( $lines ) ) {
                $lines = array($lines);
            }
            $set_names = array();
            foreach ( $lines as $line ) {
                if ( !is_string( $line ) ) {
                    continue;
                }
                $sem = strpos( $line, ';' );
                $pair = ( false !== $sem ? substr( $line, 0, $sem ) : $line );
                if ( false !== strpos( $pair, '=' ) ) {
                    list( $n ) = explode( '=', $pair, 2 );
                    $set_names[] = trim( $n );
                }
            }
            $out['set_cookie_count'] = count( $lines );
            $out['set_cookie_names'] = array_values( array_unique( $set_names ) );
        }
        return $out;
    }

    /**
     * Quick HTML signals to compare with a browser-saved page (e.g. f.txt).
     *
     * @since 4.2.0
     * @param string $body Response body.
     * @return array<string, int|bool>
     */
    private function MPT_google_scraping_parse_body_signals( $body ) {
        $body = ( is_string( $body ) ? $body : '' );
        $data_ou = preg_match_all( '/data-ou="https?:[^"]*"/', $body );
        return array(
            'data_ou_count'      => ( false === $data_ou ? 0 : (int) $data_ou ),
            'has_consent_google' => false !== stripos( $body, 'consent.google.com' ),
            'has_bot_challenge'  => $this->MPT_google_scraping_is_bot_challenge_page( $body ),
            'has_sg_ss'          => false !== stripos( $body, 'SG_SS=' ),
        );
    }

    /**
     * Log outgoing request fingerprint (headers as sent by WP; cookie values omitted).
     *
     * @since 4.2.0
     * @param string $stage   e.g. initial, variant, session_prime.
     * @param string $url     Request URL.
     * @param array  $defaults wp_remote_* args.
     * @param string $cookies  Cookie header string.
     * @param string $referer  Referer.
     */
    private function MPT_google_scraping_diagnostic_log_request(
        $stage,
        $url,
        $defaults,
        $cookies,
        $referer
    ) {
        if ( !$this->MPT_google_scraping_diagnostic_logging_enabled() ) {
            return;
        }
        $headers = $this->MPT_google_scraping_prepare_request_headers(
            $defaults,
            $cookies,
            $referer,
            $url
        );
        $sanitized = $headers;
        if ( !empty( $defaults['user-agent'] ) ) {
            $sanitized['User-Agent'] = $defaults['user-agent'];
        }
        if ( isset( $sanitized['Cookie'] ) ) {
            $sanitized['Cookie'] = '(cookie names: ' . implode( ', ', $this->MPT_google_scraping_cookie_names_from_header( $cookies ) ) . ')';
        }
        $log = $this->MPT_monolog_call();
        $log->info( 'Google scraping diagnostic: outgoing request (server vs browser compare)', array(
            'stage'           => $stage,
            'url'             => $url,
            'url_length'      => strlen( $url ),
            'headers_sent'    => $sanitized,
            'sslverify'       => ( isset( $defaults['sslverify'] ) ? $defaults['sslverify'] : null ),
            'redirection_max' => ( isset( $defaults['redirection'] ) ? $defaults['redirection'] : null ),
            'timeout'         => ( isset( $defaults['timeout'] ) ? $defaults['timeout'] : null ),
            'note_tls_ja3'    => 'TLS cipher suite / JA3 fingerprint are not available from wp_remote_*; compare with browser DevTools or curl -v on same host.',
        ) );
    }

    /**
     * Log response metadata and body signals.
     *
     * @since 4.2.0
     * @param string $stage    Same as outgoing.
     * @param array  $response wp_remote_* response.
     * @param string $body     Body (optional; avoids double retrieve).
     */
    private function MPT_google_scraping_diagnostic_log_response( $stage, $response, $body = null ) {
        if ( !$this->MPT_google_scraping_diagnostic_logging_enabled() ) {
            return;
        }
        if ( !is_array( $response ) ) {
            return;
        }
        if ( null === $body ) {
            $body = wp_remote_retrieve_body( $response );
        }
        $code = wp_remote_retrieve_response_code( $response );
        $log = $this->MPT_monolog_call();
        $log->info( 'Google scraping diagnostic: HTTP response', array(
            'stage'             => $stage,
            'http_code'         => (int) $code,
            'body_length'       => strlen( (string) $body ),
            'response_headers'  => $this->MPT_google_scraping_response_headers_for_log( $response ),
            'body_signals'      => $this->MPT_google_scraping_parse_body_signals( (string) $body ),
            'note_http_version' => 'WordPress/Requests does not expose HTTP/1.1 vs HTTP/2 in wp_remote_*; use server access logs or tcpdump if needed.',
        ) );
    }

    /**
     * Build image result list for google_scraping: by default Bing Images primary; legacy Google HTML path when filter disables Bing primary.
     *
     * @since 4.2.0
     * @param string $google_url Full Google search URL.
     * @param array  $defaults   wp_remote_request args.
     * @param array  $result     First HTTP response.
     * @return array{results: array<int, array{url: string, alt: string, caption: string}>}
     */
    private function MPT_google_scraping_build_results( $google_url, $defaults, $result ) {
        $log = $this->MPT_monolog_call();
        $bing_defaults = $defaults;
        if ( $this->MPT_google_scraping_use_bing_primary() ) {
            $log->info( 'Google scraping: HTML/headless pipeline skipped; Bing Images is primary (re-enable Google HTML with filter mpt_google_scraping_use_bing_primary).' );
            if ( $this->MPT_google_scraping_bing_fallback_option_enabled() ) {
                $bing = $this->MPT_google_scraping_fetch_bing_images( $google_url, $bing_defaults );
                if ( !empty( $bing ) ) {
                    return array(
                        'results' => $bing,
                    );
                }
                $log->warning( 'Google scraping: Bing primary returned no images' );
            } else {
                $log->warning( 'Google scraping: Bing primary mode but Bing is disabled in Banks settings' );
            }
            return array(
                'results' => array(),
            );
        }
        $candidates = $this->MPT_google_scraping_get_user_agent_candidates();
        $strategy = $this->MPT_google_scraping_get_cached_strategy();
        // Try last successful UA first to reduce retries on subsequent posts.
        if ( !empty( $strategy['ua'] ) && is_string( $strategy['ua'] ) ) {
            $cached_ua = $strategy['ua'];
            $ua_pos = array_search( $cached_ua, $candidates, true );
            if ( false !== $ua_pos && 0 !== $ua_pos ) {
                unset($candidates[$ua_pos]);
                array_unshift( $candidates, $cached_ua );
                $candidates = array_values( $candidates );
            }
        }
        if ( !empty( $strategy ) ) {
            $log->info( 'Google scraping: using cached strategy first', array(
                'ua_hint'       => ( isset( $strategy['ua'] ) ? substr( (string) $strategy['ua'], 0, 120 ) : '' ),
                'variant_index' => ( isset( $strategy['variant_index'] ) ? (int) $strategy['variant_index'] : 0 ),
                'h2_config'     => ( isset( $strategy['h2_config'] ) ? (string) $strategy['h2_config'] : '' ),
            ) );
        }
        if ( $this->MPT_google_scraping_diagnostic_logging_enabled() ) {
            $log->info( 'Google scraping diagnostic: server environment (compare with your desktop browser)', array(
                'environment' => $this->MPT_google_scraping_get_environment_snapshot(),
            ) );
        }
        $log->info( 'Google scraping: User-Agent rounds', array(
            'count' => count( $candidates ),
            'hint'  => 'legacy mobile + text browsers; filter mpt_google_scraping_user_agents or set mpt_google_scraping_try_alternate_user_agents to false for first UA only',
        ) );
        foreach ( $candidates as $ua_index => $ua_string ) {
            $defaults_ua = $defaults;
            $defaults_ua['user-agent'] = $ua_string;
            $round_result = $result;
            if ( $ua_index > 0 ) {
                $log->info( 'Google scraping: alternate User-Agent round', array(
                    'round'   => $ua_index + 1,
                    'of'      => count( $candidates ),
                    'ua_hint' => substr( $ua_string, 0, 120 ),
                ) );
                $round_result = $this->MPT_google_scraping_curl_h2_get( $google_url, $defaults_ua );
                $h2_body_len = ( !is_wp_error( $round_result ) && isset( $round_result['body'] ) ? strlen( $round_result['body'] ) : 0 );
                if ( is_wp_error( $round_result ) || $h2_body_len < 500 ) {
                    $log->info( 'Google scraping: cURL HTTP/2 did not yield body for alternate UA, trying raw socket', array(
                        'round'       => $ua_index + 1,
                        'h2_body_len' => $h2_body_len,
                    ) );
                    $round_result = $this->MPT_google_scraping_raw_socket_get( $google_url, $defaults_ua );
                }
                if ( is_wp_error( $round_result ) ) {
                    $log->info( 'Google scraping: all transports failed for alternate UA, falling back to wp_remote_request', array(
                        'error' => $round_result->get_error_message(),
                        'round' => $ua_index + 1,
                    ) );
                    $round_result = wp_remote_request( $google_url, $defaults_ua );
                }
                if ( is_wp_error( $round_result ) ) {
                    $log->warning( 'Google scraping: initial HTTP request failed for alternate User-Agent', array(
                        'error' => $round_result->get_error_message(),
                        'round' => $ua_index + 1,
                    ) );
                    continue;
                }
                if ( $this->MPT_google_scraping_diagnostic_logging_enabled() ) {
                    $this->MPT_google_scraping_diagnostic_log_request(
                        'initial_request_ua_' . ($ua_index + 1),
                        $google_url,
                        $defaults_ua,
                        '',
                        ''
                    );
                }
            }
            $cookies = $this->MPT_google_scraping_collect_cookies_from_response( $round_result );
            $attempts = array(array(
                'url'      => $google_url,
                'label'    => 'initial',
                'response' => $round_result,
            ));
            $primed_session = $this->MPT_google_scraping_prime_google_session( $defaults_ua, $google_url );
            if ( !empty( $primed_session['cookies'] ) ) {
                $cookies = $this->MPT_google_scraping_merge_cookie_headers( $cookies, $primed_session['cookies'] );
                $log->info( 'Google scraping: Google session primed before variant retries', array(
                    'ua_round' => $ua_index + 1,
                ) );
            }
            $variant_urls = array_values( array_filter( $this->MPT_google_scraping_build_variant_urls( $google_url ), function ( $variant_url ) use($google_url) {
                return $variant_url !== $google_url;
            } ) );
            if ( !empty( $strategy['variant_index'] ) && is_numeric( $strategy['variant_index'] ) && $defaults_ua['user-agent'] === (( isset( $strategy['ua'] ) ? $strategy['ua'] : '' )) ) {
                $preferred_attempt = (int) $strategy['variant_index'];
                $preferred_variant = $preferred_attempt - 1;
                // attempts[0]=initial, variants start at index 1.
                if ( $preferred_variant > 0 ) {
                    $preferred_variant_offset = $preferred_variant - 1;
                    // variant_urls is 0-indexed variants only.
                    if ( isset( $variant_urls[$preferred_variant_offset] ) ) {
                        $preferred_url = $variant_urls[$preferred_variant_offset];
                        unset($variant_urls[$preferred_variant_offset]);
                        array_unshift( $variant_urls, $preferred_url );
                        $variant_urls = array_values( $variant_urls );
                    }
                }
            }
            foreach ( $variant_urls as $variant_url ) {
                $attempts[] = array(
                    'url'      => $variant_url,
                    'label'    => 'variant',
                    'response' => null,
                );
            }
            if ( $this->MPT_google_scraping_diagnostic_logging_enabled() ) {
                $plan = array();
                foreach ( $attempts as $i => $a ) {
                    $plan[] = array(
                        'step'  => $i + 1,
                        'label' => $a['label'],
                        'url'   => $a['url'],
                    );
                }
                $log->info( 'Google scraping diagnostic: attempt order (initial HTML, then variants after session prime)', array(
                    'ua_round' => $ua_index + 1,
                    'attempts' => $plan,
                ) );
            }
            foreach ( $attempts as $attempt_index => $attempt ) {
                $response = $attempt['response'];
                if ( null === $response ) {
                    $response = $this->MPT_google_scraping_request_url(
                        $attempt['url'],
                        $defaults_ua,
                        $cookies,
                        $google_url,
                        'variant_attempt_' . ($ua_index + 1) . '_' . ($attempt_index + 1)
                    );
                    if ( is_wp_error( $response ) ) {
                        $log->warning( 'Google scraping: retry request failed', array(
                            'ua_round' => $ua_index + 1,
                            'attempt'  => $attempt_index + 1,
                            'label'    => $attempt['label'],
                            'url'      => $attempt['url'],
                            'error'    => $response->get_error_message(),
                        ) );
                        continue;
                    }
                }
                $body = wp_remote_retrieve_body( $response );
                $code = wp_remote_retrieve_response_code( $response );
                $this->MPT_google_scraping_diagnostic_log_response( 'ua' . ($ua_index + 1) . '_step_' . ($attempt_index + 1) . '_' . $attempt['label'], $response, $body );
                $log->info( 'Google scraping: response received', array(
                    'ua_round'    => $ua_index + 1,
                    'attempt'     => $attempt_index + 1,
                    'label'       => $attempt['label'],
                    'http_code'   => (int) $code,
                    'body_length' => strlen( $body ),
                    'url'         => $attempt['url'],
                ) );
                if ( 200 !== (int) $code || '' === $body ) {
                    $log->warning( 'Google scraping: invalid response (no HTML to parse)', array(
                        'ua_round'  => $ua_index + 1,
                        'attempt'   => $attempt_index + 1,
                        'http_code' => (int) $code,
                        'url'       => $attempt['url'],
                    ) );
                    continue;
                }
                $cookies = $this->MPT_google_scraping_merge_cookie_headers( $cookies, $this->MPT_google_scraping_collect_cookies_from_response( $response ) );
                if ( $this->MPT_google_scraping_is_consent_page( $body ) ) {
                    $log->info( 'Google scraping: EU consent page detected, submitting Reject all', array(
                        'ua_round' => $ua_index + 1,
                        'attempt'  => $attempt_index + 1,
                        'url'      => $attempt['url'],
                    ) );
                    $after_consent = $this->MPT_google_scraping_post_consent_reject(
                        $body,
                        $cookies,
                        $defaults_ua,
                        $attempt['url']
                    );
                    if ( is_array( $after_consent ) && !empty( $after_consent['body'] ) ) {
                        $body = $after_consent['body'];
                        if ( !empty( $after_consent['cookies'] ) ) {
                            $cookies = $this->MPT_google_scraping_merge_cookie_headers( $cookies, $after_consent['cookies'] );
                        }
                        $log->info( 'Google scraping: consent flow completed', array(
                            'ua_round'        => $ua_index + 1,
                            'attempt'         => $attempt_index + 1,
                            'new_body_length' => strlen( $body ),
                        ) );
                    } else {
                        $log->warning( 'Google scraping: consent POST failed or empty body after redirect; continuing with original HTML' );
                    }
                }
                if ( $this->MPT_google_scraping_is_bot_challenge_page( $body ) ) {
                    $log->warning( 'Google scraping: Google returned a JS/browser challenge page (SG_SS)', array(
                        'ua_round' => $ua_index + 1,
                        'attempt'  => $attempt_index + 1,
                        'url'      => $attempt['url'],
                    ) );
                }
                $parsed = $this->MPT_google_scraping_parse_google_html( $body );
                if ( !empty( $parsed['results'] ) ) {
                    $this->MPT_google_scraping_update_cached_strategy( array(
                        'ua'            => $ua_string,
                        'variant_index' => (int) $attempt_index + 1,
                    ) );
                    $log->info( 'Google scraping: images extracted from Google', array(
                        'ua_round' => $ua_index + 1,
                        'attempt'  => $attempt_index + 1,
                        'source'   => $parsed['source'],
                        'count'    => count( $parsed['results'] ),
                    ) );
                    return array(
                        'results' => $parsed['results'],
                    );
                }
                $log->info( 'Google scraping: no usable image URLs in Google HTML', array(
                    'ua_round'     => $ua_index + 1,
                    'attempt'      => $attempt_index + 1,
                    'parse_source' => $parsed['source'],
                ) );
            }
        }
        if ( apply_filters( 'mpt_google_scraping_use_headless_fallback', true, $google_url ) ) {
            $log->info( 'Google scraping: trying local headless browser fallback', array(
                'google_url' => $google_url,
            ) );
            $headless = $this->MPT_google_scraping_fetch_google_headless_html( $google_url );
            if ( is_array( $headless ) && !empty( $headless['body'] ) ) {
                $parsed = $this->MPT_google_scraping_parse_google_html( $headless['body'] );
                if ( !empty( $parsed['results'] ) ) {
                    $log->info( 'Google scraping: headless browser fallback succeeded', array(
                        'browser' => $headless['browser'],
                        'source'  => $parsed['source'],
                        'count'   => count( $parsed['results'] ),
                    ) );
                    return array(
                        'results' => $parsed['results'],
                    );
                }
                $log->warning( 'Google scraping: headless browser returned HTML but no parseable image URLs', array(
                    'browser'      => $headless['browser'],
                    'parse_source' => $parsed['source'],
                    'body_length'  => strlen( $headless['body'] ),
                ) );
            }
        } else {
            $log->info( 'Google scraping: headless browser fallback skipped (mpt_google_scraping_use_headless_fallback returned false)' );
        }
        if ( apply_filters( 'mpt_google_scraping_use_bing_fallback', $this->MPT_google_scraping_bing_fallback_option_enabled(), $google_url ) ) {
            $log->info( 'Google scraping: trying Bing Images fallback', array(
                'google_url' => $google_url,
            ) );
            $bing = $this->MPT_google_scraping_fetch_bing_images( $google_url, $bing_defaults );
            if ( !empty( $bing ) ) {
                $log->info( 'Google scraping: Bing fallback succeeded', array(
                    'count' => count( $bing ),
                ) );
                return array(
                    'results' => $bing,
                );
            }
            $log->warning( 'Google scraping: Bing fallback returned no images' );
        } else {
            $log->info( 'Google scraping: Bing fallback skipped (mpt_google_scraping_use_bing_fallback returned false)' );
        }
        $log->warning( 'Google scraping: no image results from Google or Bing' );
        return array(
            'results' => array(),
        );
    }

    /**
     * Warm up a more browser-like Google session before variant retries.
     *
     * @param array  $defaults   Base HTTP args.
     * @param string $google_url Original Google search URL.
     * @return array{cookies: string}
     */
    private function MPT_google_scraping_prime_google_session( $defaults, $google_url ) {
        $log = $this->MPT_monolog_call();
        $parts = wp_parse_url( $google_url );
        $query = ( isset( $parts['query'] ) ? $parts['query'] : '' );
        $args = array();
        parse_str( $query, $args );
        $home_url = add_query_arg( array(
            'hl'  => ( isset( $args['hl'] ) ? $args['hl'] : 'en' ),
            'pws' => '0',
        ), 'https://www.google.com/' );
        $response = $this->MPT_google_scraping_request_url(
            $home_url,
            $defaults,
            '',
            'https://www.google.com/',
            'session_prime'
        );
        if ( is_wp_error( $response ) ) {
            $log->warning( 'Google scraping: session prime request failed', array(
                'error' => $response->get_error_message(),
            ) );
            return array(
                'cookies' => '',
            );
        }
        $cookies = $this->MPT_google_scraping_collect_cookies_from_response( $response );
        $body = wp_remote_retrieve_body( $response );
        $this->MPT_google_scraping_diagnostic_log_response( 'session_prime', $response, $body );
        if ( $this->MPT_google_scraping_is_consent_page( $body ) ) {
            $log->info( 'Google scraping: consent page detected during session prime' );
            $after_consent = $this->MPT_google_scraping_post_consent_reject(
                $body,
                $cookies,
                $defaults,
                $home_url
            );
            if ( is_array( $after_consent ) && !empty( $after_consent['cookies'] ) ) {
                $cookies = $this->MPT_google_scraping_merge_cookie_headers( $cookies, $after_consent['cookies'] );
            }
        }
        return array(
            'cookies' => $cookies,
        );
    }

    /**
     * Build alternate Google URLs that sometimes return more parseable HTML.
     * Kept to a small set to limit sequential HTTP retries per request.
     *
     * @param string $google_url Original Google search URL.
     * @return array<int, string>
     */
    private function MPT_google_scraping_build_variant_urls( $google_url ) {
        $google_url = $this->MPT_google_scraping_sanitize_url( $google_url );
        $parts = wp_parse_url( $google_url );
        $query = ( isset( $parts['query'] ) ? $parts['query'] : '' );
        $args = array();
        parse_str( $query, $args );
        $base_args = array_merge( $args, array(
            'tbm'   => 'isch',
            'ijn'   => '0',
            'pws'   => '0',
            'start' => '0',
        ) );
        $variants = array(add_query_arg( $base_args, 'https://www.google.com/search' ), add_query_arg( array_merge( $base_args, array(
            'udm'  => '2',
            'gbv'  => '1',
            'nfpr' => '1',
        ) ), 'https://www.google.com/search' ));
        $variants = array_map( array($this, 'MPT_google_scraping_sanitize_url'), $variants );
        return array_values( array_unique( array_filter( $variants ) ) );
    }

    /**
     * Perform a Google GET request with optional cookie and referer continuity.
     * Tries the raw-socket transport first (bypasses cURL TLS fingerprint), then falls back to wp_remote_get.
     *
     * @param string $url     Target URL.
     * @param array  $defaults Base HTTP args.
     * @param string $cookies Cookie header value.
     * @param string $referer Referer URL.
     * @param string $diagnostic_stage Label for diagnostic logs.
     * @return array|\WP_Error
     */
    private function MPT_google_scraping_request_url(
        $url,
        $defaults,
        $cookies,
        $referer,
        $diagnostic_stage = 'retry_get'
    ) {
        $log = $this->MPT_monolog_call();
        $url = $this->MPT_google_scraping_sanitize_url( $url );
        $headers = $this->MPT_google_scraping_prepare_request_headers(
            $defaults,
            $cookies,
            $referer,
            $url
        );
        if ( $this->MPT_google_scraping_diagnostic_logging_enabled() ) {
            $this->MPT_google_scraping_diagnostic_log_request(
                $diagnostic_stage,
                $url,
                $defaults,
                $cookies,
                $referer
            );
        }
        $h2_response = $this->MPT_google_scraping_curl_h2_get(
            $url,
            $defaults,
            $cookies,
            $referer
        );
        if ( !is_wp_error( $h2_response ) ) {
            $h2_body = ( isset( $h2_response['body'] ) ? $h2_response['body'] : '' );
            if ( strlen( $h2_body ) > 500 ) {
                return $h2_response;
            }
            $log->info( 'Google scraping: cURL HTTP/2 returned short body, trying raw socket', array(
                'stage'    => $diagnostic_stage,
                'body_len' => strlen( $h2_body ),
            ) );
        } else {
            $log->info( 'Google scraping: cURL HTTP/2 failed, trying raw socket', array(
                'stage' => $diagnostic_stage,
                'error' => $h2_response->get_error_message(),
            ) );
        }
        $raw_response = $this->MPT_google_scraping_raw_socket_get(
            $url,
            $defaults,
            $cookies,
            $referer
        );
        if ( !is_wp_error( $raw_response ) ) {
            $raw_body = ( isset( $raw_response['body'] ) ? $raw_response['body'] : '' );
            if ( strlen( $raw_body ) > 500 ) {
                return $raw_response;
            }
            $log->info( 'Google scraping: raw socket returned short body, trying wp_remote_get', array(
                'stage'    => $diagnostic_stage,
                'body_len' => strlen( $raw_body ),
            ) );
        } else {
            $log->info( 'Google scraping: raw socket failed, trying wp_remote_get', array(
                'stage' => $diagnostic_stage,
                'error' => $raw_response->get_error_message(),
            ) );
        }
        return wp_remote_get( $url, array_merge( $defaults, array(
            'headers' => $headers,
        ) ) );
    }

    /**
     * HTTP GET via cURL with HTTP/2 + custom TLS settings.
     *
     * HTTP/2 via ALPN and TLS 1.3 produce a fundamentally different TLS fingerprint
     * compared to HTTP/1.1 over TLS 1.2, which may bypass Google's anti-bot detection.
     * Tries several config combos and returns the first that yields a non-empty body.
     *
     * @since 4.2.0
     * @param string $url      Full HTTPS URL.
     * @param array  $defaults wp_remote_* style args (user-agent, timeout, headers).
     * @param string $cookies  Cookie header value.
     * @param string $referer  Referer URL.
     * @return array|\WP_Error Response array compatible with wp_remote_get format, or WP_Error.
     */
    private function MPT_google_scraping_curl_h2_get(
        $url,
        $defaults,
        $cookies = '',
        $referer = ''
    ) {
        $log = $this->MPT_monolog_call();
        if ( !function_exists( 'curl_init' ) ) {
            return new \WP_Error('mpt_curl_h2', 'cURL extension not available.');
        }
        $is_search = false !== strpos( $url, '/search' );
        $configs = $this->MPT_google_scraping_get_curl_h2_configs();
        if ( $is_search && empty( $this->curl_h2_working_config ) ) {
            $strategy = $this->MPT_google_scraping_get_cached_strategy();
            if ( !empty( $strategy['h2_config'] ) && isset( $configs[$strategy['h2_config']] ) ) {
                $this->curl_h2_working_config = $configs[$strategy['h2_config']];
                $log->info( 'Google scraping: applying cached cURL H2 config', array(
                    'config' => (string) $strategy['h2_config'],
                ) );
            }
        }
        if ( $is_search && !empty( $this->curl_h2_working_config ) ) {
            return $this->MPT_google_scraping_curl_h2_single(
                $url,
                $defaults,
                $cookies,
                $referer,
                $this->curl_h2_working_config,
                'cached'
            );
        }
        foreach ( $configs as $config_name => $curl_extra ) {
            $result = $this->MPT_google_scraping_curl_h2_single(
                $url,
                $defaults,
                $cookies,
                $referer,
                $curl_extra,
                $config_name
            );
            if ( is_wp_error( $result ) ) {
                $log->info( 'Google scraping: cURL H2 config failed', array(
                    'config' => $config_name,
                    'error'  => $result->get_error_message(),
                ) );
                continue;
            }
            $body = ( isset( $result['body'] ) ? $result['body'] : '' );
            $code = ( isset( $result['response']['code'] ) ? (int) $result['response']['code'] : 0 );
            if ( $is_search && 200 === $code && strlen( $body ) > 1000 ) {
                $log->info( 'Google scraping: cURL H2 config succeeded for search', array(
                    'config'   => $config_name,
                    'body_len' => strlen( $body ),
                ) );
                $this->curl_h2_working_config = $curl_extra;
                $this->MPT_google_scraping_update_cached_strategy( array(
                    'h2_config' => $config_name,
                ) );
                return $result;
            }
            if ( !$is_search ) {
                return $result;
            }
            $log->info( 'Google scraping: cURL H2 config returned empty/short body', array(
                'config'    => $config_name,
                'body_len'  => strlen( $body ),
                'http_code' => $code,
            ) );
        }
        $log->info( 'Google scraping: all cURL H2 configs failed to get search body' );
        return ( isset( $result ) ? $result : new \WP_Error('mpt_curl_h2', 'All cURL HTTP/2 configurations returned empty body.') );
    }

    /**
     * cURL HTTP/2 configurations to try.
     *
     * @return array<string, array> Config name => extra cURL options.
     */
    private function MPT_google_scraping_get_curl_h2_configs() {
        $configs = array();
        $h2_available = defined( 'CURL_HTTP_VERSION_2_0' );
        $tls13_constant = ( defined( 'CURL_SSLVERSION_TLSv1_3' ) ? CURL_SSLVERSION_TLSv1_3 : 0 );
        $firefox_ciphers = implode( ':', array(
            'ECDHE-ECDSA-AES128-GCM-SHA256',
            'ECDHE-RSA-AES128-GCM-SHA256',
            'ECDHE-ECDSA-CHACHA20-POLY1305',
            'ECDHE-RSA-CHACHA20-POLY1305',
            'ECDHE-ECDSA-AES256-GCM-SHA384',
            'ECDHE-RSA-AES256-GCM-SHA384',
            'ECDHE-RSA-AES128-SHA',
            'ECDHE-RSA-AES256-SHA',
            'AES128-GCM-SHA256',
            'AES256-GCM-SHA384'
        ) );
        $chrome_ciphers = implode( ':', array(
            'ECDHE-ECDSA-AES128-GCM-SHA256',
            'ECDHE-RSA-AES128-GCM-SHA256',
            'ECDHE-ECDSA-AES256-GCM-SHA384',
            'ECDHE-RSA-AES256-GCM-SHA384',
            'ECDHE-ECDSA-CHACHA20-POLY1305',
            'ECDHE-RSA-CHACHA20-POLY1305'
        ) );
        if ( $h2_available && $tls13_constant ) {
            $configs['h2_tls13_firefox'] = array(
                CURLOPT_HTTP_VERSION    => CURL_HTTP_VERSION_2_0,
                CURLOPT_SSLVERSION      => $tls13_constant,
                CURLOPT_SSL_CIPHER_LIST => $firefox_ciphers,
            );
            $configs['h2_tls13'] = array(
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_2_0,
                CURLOPT_SSLVERSION   => $tls13_constant,
            );
        }
        if ( $h2_available ) {
            $configs['h2_firefox'] = array(
                CURLOPT_HTTP_VERSION    => CURL_HTTP_VERSION_2_0,
                CURLOPT_SSL_CIPHER_LIST => $firefox_ciphers,
            );
            $configs['h2_chrome'] = array(
                CURLOPT_HTTP_VERSION    => CURL_HTTP_VERSION_2_0,
                CURLOPT_SSL_CIPHER_LIST => $chrome_ciphers,
            );
            $configs['h2_default'] = array(
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_2_0,
            );
        }
        if ( $tls13_constant ) {
            $configs['h1_tls13'] = array(
                CURLOPT_SSLVERSION => $tls13_constant,
            );
        }
        if ( empty( $configs ) ) {
            $configs['h1_default'] = array();
        }
        return $configs;
    }

    /**
     * Execute a single cURL HTTP/2 request.
     *
     * @param string $url       Full URL.
     * @param array  $defaults  wp_remote_* style args.
     * @param string $cookies   Cookie header value.
     * @param string $referer   Referer URL.
     * @param array  $curl_extra Additional cURL options to set.
     * @param string $config_tag Label for logging.
     * @return array|\WP_Error
     */
    private function MPT_google_scraping_curl_h2_single(
        $url,
        $defaults,
        $cookies = '',
        $referer = '',
        $curl_extra = array(),
        $config_tag = ''
    ) {
        $log = $this->MPT_monolog_call();
        $timeout = ( isset( $defaults['timeout'] ) ? (int) $defaults['timeout'] : 3 );
        $ua = ( isset( $defaults['user-agent'] ) ? $defaults['user-agent'] : '' );
        $sslverify = !empty( $defaults['sslverify'] );
        $req_headers = array();
        $req_headers[] = 'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8';
        $req_headers[] = 'Accept-Language: en-US,en;q=0.5';
        if ( '' !== $cookies ) {
            $req_headers[] = "Cookie: {$cookies}";
        }
        if ( '' !== $referer ) {
            $req_headers[] = "Referer: {$referer}";
        }
        $ch = curl_init();
        curl_setopt_array( $ch, array(
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER         => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS      => 2,
            CURLOPT_TIMEOUT        => $timeout,
            CURLOPT_CONNECTTIMEOUT => min( 1, $timeout ),
            CURLOPT_USERAGENT      => $ua,
            CURLOPT_HTTPHEADER     => $req_headers,
            CURLOPT_ENCODING       => '',
            CURLOPT_SSL_VERIFYPEER => $sslverify,
            CURLOPT_SSL_VERIFYHOST => ( $sslverify ? 2 : 0 ),
        ) );
        foreach ( $curl_extra as $opt => $val ) {
            curl_setopt( $ch, $opt, $val );
        }
        $log->info( 'Google scraping: cURL H2 attempting request', array(
            'url'         => $url,
            'config'      => ( '' !== $config_tag ? $config_tag : 'cached' ),
            'ua_hint'     => substr( $ua, 0, 80 ),
            'has_cookies' => '' !== $cookies,
        ) );
        $raw = curl_exec( $ch );
        if ( false === $raw ) {
            $err = curl_error( $ch );
            curl_close( $ch );
            return new \WP_Error('mpt_curl_h2', "cURL error ({$config_tag}): {$err}");
        }
        $http_code = (int) curl_getinfo( $ch, CURLINFO_HTTP_CODE );
        $header_size = (int) curl_getinfo( $ch, CURLINFO_HEADER_SIZE );
        $http_ver = '';
        if ( defined( 'CURLINFO_HTTP_VERSION' ) ) {
            $ver_code = curl_getinfo( $ch, CURLINFO_HTTP_VERSION );
            $ver_map = array(
                1 => '1.0',
                2 => '1.1',
                3 => '2',
                4 => '3',
            );
            $http_ver = ( isset( $ver_map[$ver_code] ) ? $ver_map[$ver_code] : (string) $ver_code );
        }
        curl_close( $ch );
        $resp_headers_str = substr( $raw, 0, $header_size );
        $body = substr( $raw, $header_size );
        $safe_preview = substr( preg_replace( '/\\s+/', ' ', wp_strip_all_tags( $body ) ), 0, 200 );
        $log->info( 'Google scraping: cURL H2 response parsed', array(
            'config'       => ( '' !== $config_tag ? $config_tag : 'cached' ),
            'http_code'    => $http_code,
            'http_version' => $http_ver,
            'body_len'     => strlen( $body ),
            'body_preview' => $safe_preview,
        ) );
        $response_headers = array();
        foreach ( explode( "\r\n", $resp_headers_str ) as $line ) {
            if ( false === strpos( $line, ':' ) ) {
                continue;
            }
            list( $hname, $hvalue ) = explode( ':', $line, 2 );
            $hname = strtolower( trim( $hname ) );
            $hvalue = trim( $hvalue );
            $response_headers[$hname] = $hvalue;
        }
        return array(
            'headers'  => $response_headers,
            'body'     => $body,
            'response' => array(
                'code'    => $http_code,
                'message' => '',
            ),
            'cookies'  => array(),
        );
    }

    /**
     * HTTP GET via stream_socket_client("ssl://") with manual HTTP/1.1.
     *
     * PHP's native SSL stream produces a different TLS ClientHello fingerprint than
     * cURL/libcurl. Falls back after cURL HTTP/2 transport.
     *
     * @since 4.2.0
     * @param string $url      Full HTTPS URL.
     * @param array  $defaults wp_remote_* style args (user-agent, timeout, headers).
     * @param string $cookies  Cookie header value.
     * @param string $referer  Referer URL.
     * @return array|\WP_Error Response array compatible with wp_remote_get format, or WP_Error.
     */
    private function MPT_google_scraping_raw_socket_get(
        $url,
        $defaults,
        $cookies = '',
        $referer = ''
    ) {
        $log = $this->MPT_monolog_call();
        $is_search = false !== strpos( $url, '/search' );
        if ( $is_search && !empty( $this->raw_socket_working_profile ) ) {
            return $this->MPT_google_scraping_raw_socket_single(
                $url,
                $defaults,
                $cookies,
                $referer,
                $this->raw_socket_working_profile
            );
        }
        $profiles = $this->MPT_google_scraping_get_tls_profiles();
        foreach ( $profiles as $profile_name => $ssl_opts ) {
            $result = $this->MPT_google_scraping_raw_socket_single(
                $url,
                $defaults,
                $cookies,
                $referer,
                $ssl_opts,
                $profile_name
            );
            if ( is_wp_error( $result ) ) {
                $log->info( 'Google scraping: TLS profile connection failed', array(
                    'profile' => $profile_name,
                    'error'   => $result->get_error_message(),
                ) );
                continue;
            }
            $body = ( isset( $result['body'] ) ? $result['body'] : '' );
            $code = ( isset( $result['response']['code'] ) ? (int) $result['response']['code'] : 0 );
            if ( $is_search && 200 === $code && strlen( $body ) > 1000 ) {
                $log->info( 'Google scraping: TLS profile succeeded for search', array(
                    'profile'  => $profile_name,
                    'body_len' => strlen( $body ),
                ) );
                $this->raw_socket_working_profile = $ssl_opts;
                return $result;
            }
            if ( !$is_search ) {
                return $result;
            }
            $log->info( 'Google scraping: TLS profile returned empty/short body for search, trying next', array(
                'profile'   => $profile_name,
                'body_len'  => strlen( $body ),
                'http_code' => $code,
            ) );
        }
        $log->warning( 'Google scraping: all TLS profiles failed to get search body' );
        return ( isset( $result ) ? $result : new \WP_Error('mpt_raw_socket', 'All TLS cipher profiles failed.') );
    }

    /**
     * TLS cipher profiles to try, in order. Each profile changes the JA3 fingerprint
     * presented to Google, which may bypass their anti-bot detection.
     *
     * @return array<string, array> Profile name => SSL context options.
     */
    private function MPT_google_scraping_get_tls_profiles() {
        return array(
            'default' => array(),
        );
    }

    /**
     * Perform a single raw socket HTTP GET with a specific TLS profile.
     *
     * @param string $url         Full HTTPS URL.
     * @param array  $defaults    wp_remote_* style args.
     * @param string $cookies     Cookie header value.
     * @param string $referer     Referer URL.
     * @param array  $ssl_opts    Extra SSL context options (e.g. ciphers).
     * @param string $profile_tag Label for logging.
     * @return array|\WP_Error
     */
    private function MPT_google_scraping_raw_socket_single(
        $url,
        $defaults,
        $cookies = '',
        $referer = '',
        $ssl_opts = array(),
        $profile_tag = ''
    ) {
        $log = $this->MPT_monolog_call();
        $parsed = wp_parse_url( $url );
        if ( empty( $parsed['host'] ) || empty( $parsed['scheme'] ) || 'https' !== $parsed['scheme'] ) {
            return new \WP_Error('mpt_raw_socket', 'Only HTTPS URLs are supported by the raw socket transport.');
        }
        $host = $parsed['host'];
        $port = ( isset( $parsed['port'] ) ? (int) $parsed['port'] : 443 );
        $raw_path = ( isset( $parsed['path'] ) ? $parsed['path'] : '/' );
        $raw_query = ( isset( $parsed['query'] ) ? $parsed['query'] : '' );
        $raw_path = preg_replace_callback( '/[^A-Za-z0-9\\-._~:\\/!$&\'()*+,;=@%]/', function ( $m ) {
            return rawurlencode( $m[0] );
        }, $raw_path );
        $raw_query = preg_replace_callback( '/[^A-Za-z0-9\\-._~:\\/!$\'()*+,;=@%&?]/', function ( $m ) {
            return rawurlencode( $m[0] );
        }, $raw_query );
        $path = $raw_path . (( '' !== $raw_query ? '?' . $raw_query : '' ));
        $timeout = ( isset( $defaults['timeout'] ) ? (int) $defaults['timeout'] : 3 );
        $ua = ( isset( $defaults['user-agent'] ) ? $defaults['user-agent'] : '' );
        $sslverify = !empty( $defaults['sslverify'] );
        $header_lines = array();
        $header_lines[] = "Host: {$host}";
        if ( '' !== $ua ) {
            $header_lines[] = "User-Agent: {$ua}";
        }
        $header_lines[] = 'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8';
        $header_lines[] = 'Accept-Language: en-US,en;q=0.5';
        if ( '' !== $cookies ) {
            $header_lines[] = "Cookie: {$cookies}";
        }
        if ( '' !== $referer ) {
            $header_lines[] = "Referer: {$referer}";
        }
        $header_lines[] = 'Connection: close';
        $log->info( 'Google scraping: raw socket attempting request', array(
            'url'          => $url,
            'tls_profile'  => ( '' !== $profile_tag ? $profile_tag : 'cached' ),
            'ua_hint'      => substr( $ua, 0, 80 ),
            'has_cookies'  => '' !== $cookies,
            'header_count' => count( $header_lines ),
        ) );
        $base_ssl = array(
            'verify_peer'       => $sslverify,
            'verify_peer_name'  => $sslverify,
            'allow_self_signed' => !$sslverify,
            'SNI_enabled'       => true,
            'peer_name'         => $host,
        );
        if ( !empty( $ssl_opts ) ) {
            $base_ssl = array_merge( $base_ssl, $ssl_opts );
        }
        $ctx = stream_context_create( array(
            'ssl' => $base_ssl,
        ) );
        $errno = 0;
        $errstr = '';
        $sock = @stream_socket_client(
            "ssl://{$host}:{$port}",
            $errno,
            $errstr,
            $timeout,
            STREAM_CLIENT_CONNECT,
            $ctx
        );
        if ( !$sock ) {
            $log->warning( 'Google scraping: raw socket connection failed', array(
                'tls_profile' => $profile_tag,
                'errno'       => $errno,
                'errstr'      => $errstr,
            ) );
            return new \WP_Error('mpt_raw_socket', "stream_socket_client failed ({$profile_tag}): {$errstr} ({$errno})");
        }
        $request = "GET {$path} HTTP/1.1\r\n" . implode( "\r\n", $header_lines ) . "\r\n" . "\r\n";
        fwrite( $sock, $request );
        $response_raw = '';
        stream_set_timeout( $sock, $timeout );
        while ( !feof( $sock ) ) {
            $chunk = fread( $sock, 16384 );
            if ( false === $chunk ) {
                break;
            }
            $response_raw .= $chunk;
            $meta = stream_get_meta_data( $sock );
            if ( !empty( $meta['timed_out'] ) ) {
                $log->warning( 'Google scraping: raw socket read timed out', array(
                    'tls_profile'  => $profile_tag,
                    'bytes_so_far' => strlen( $response_raw ),
                ) );
                break;
            }
        }
        fclose( $sock );
        if ( '' === $response_raw ) {
            return new \WP_Error('mpt_raw_socket', "Empty response from Google (raw socket, profile={$profile_tag}).");
        }
        $header_end = strpos( $response_raw, "\r\n\r\n" );
        if ( false === $header_end ) {
            return new \WP_Error('mpt_raw_socket', "Malformed HTTP response, profile={$profile_tag}.");
        }
        $raw_headers = substr( $response_raw, 0, $header_end );
        $raw_body = substr( $response_raw, $header_end + 4 );
        $http_code = 0;
        if ( preg_match( '/^HTTP\\/[\\d.]+ (\\d+)/', $raw_headers, $m ) ) {
            $http_code = (int) $m[1];
        }
        $body_before_decode = strlen( $raw_body );
        $decode_steps = array();
        if ( false !== stripos( $raw_headers, 'Transfer-Encoding: chunked' ) ) {
            $raw_body = $this->MPT_google_scraping_decode_chunked( $raw_body );
            $decode_steps[] = 'chunked(' . $body_before_decode . '->' . strlen( $raw_body ) . ')';
        }
        if ( false !== stripos( $raw_headers, 'Content-Encoding: gzip' ) && function_exists( 'gzdecode' ) ) {
            $pre = strlen( $raw_body );
            $decoded = @gzdecode( $raw_body );
            if ( false !== $decoded ) {
                $raw_body = $decoded;
                $decode_steps[] = 'gzip(' . $pre . '->' . strlen( $raw_body ) . ')';
            } else {
                $decode_steps[] = 'gzip_failed(input_len=' . $pre . ')';
            }
        } elseif ( false !== stripos( $raw_headers, 'Content-Encoding: br' ) ) {
            if ( function_exists( 'brotli_uncompress' ) ) {
                $pre = strlen( $raw_body );
                $decoded = @brotli_uncompress( $raw_body );
                if ( false !== $decoded ) {
                    $raw_body = $decoded;
                    $decode_steps[] = 'brotli(' . $pre . '->' . strlen( $raw_body ) . ')';
                } else {
                    $decode_steps[] = 'brotli_failed(input_len=' . $pre . ')';
                }
            } else {
                $decode_steps[] = 'brotli_unsupported(input_len=' . strlen( $raw_body ) . ')';
            }
        } elseif ( false !== stripos( $raw_headers, 'Content-Encoding: deflate' ) ) {
            $pre = strlen( $raw_body );
            $decoded = @gzinflate( $raw_body );
            if ( false !== $decoded ) {
                $raw_body = $decoded;
                $decode_steps[] = 'deflate(' . $pre . '->' . strlen( $raw_body ) . ')';
            } else {
                $decoded = @gzuncompress( $raw_body );
                if ( false !== $decoded ) {
                    $raw_body = $decoded;
                    $decode_steps[] = 'deflate_zlib(' . $pre . '->' . strlen( $raw_body ) . ')';
                } else {
                    $decode_steps[] = 'deflate_failed(input_len=' . $pre . ')';
                }
            }
        }
        $safe_preview = substr( preg_replace( '/\\s+/', ' ', wp_strip_all_tags( $raw_body ) ), 0, 200 );
        $log->info( 'Google scraping: raw socket response parsed', array(
            'tls_profile'       => ( '' !== $profile_tag ? $profile_tag : 'cached' ),
            'http_code'         => $http_code,
            'raw_body_bytes'    => $body_before_decode,
            'decoded_body_len'  => strlen( $raw_body ),
            'decode_steps'      => ( empty( $decode_steps ) ? 'none' : implode( ' > ', $decode_steps ) ),
            'content_type'      => $this->MPT_google_scraping_extract_raw_header( $raw_headers, 'Content-Type' ),
            'content_encoding'  => $this->MPT_google_scraping_extract_raw_header( $raw_headers, 'Content-Encoding' ),
            'transfer_encoding' => $this->MPT_google_scraping_extract_raw_header( $raw_headers, 'Transfer-Encoding' ),
            'body_preview'      => $safe_preview,
        ) );
        $response_headers = array();
        $header_lines_arr = explode( "\r\n", $raw_headers );
        array_shift( $header_lines_arr );
        foreach ( $header_lines_arr as $line ) {
            if ( false === strpos( $line, ':' ) ) {
                continue;
            }
            list( $hname, $hvalue ) = explode( ':', $line, 2 );
            $hname = strtolower( trim( $hname ) );
            $hvalue = trim( $hvalue );
            if ( isset( $response_headers[$hname] ) ) {
                if ( !is_array( $response_headers[$hname] ) ) {
                    $response_headers[$hname] = array($response_headers[$hname]);
                }
                $response_headers[$hname][] = $hvalue;
            } else {
                $response_headers[$hname] = $hvalue;
            }
        }
        if ( $http_code >= 300 && $http_code < 400 && isset( $response_headers['location'] ) ) {
            $redirect_url = ( is_array( $response_headers['location'] ) ? $response_headers['location'][0] : $response_headers['location'] );
            if ( 0 === strpos( $redirect_url, '/' ) ) {
                $redirect_url = "https://{$host}" . $redirect_url;
            }
            $new_cookies = $this->MPT_google_scraping_collect_cookies_from_raw_headers( $raw_headers );
            if ( '' !== $new_cookies ) {
                $cookies = $this->MPT_google_scraping_merge_cookie_headers( $cookies, $new_cookies );
            }
            $log->info( 'Google scraping: raw socket following redirect', array(
                'to'   => $redirect_url,
                'code' => $http_code,
            ) );
            return $this->MPT_google_scraping_raw_socket_single(
                $redirect_url,
                $defaults,
                $cookies,
                $referer,
                $ssl_opts,
                $profile_tag
            );
        }
        return array(
            'headers'  => $response_headers,
            'body'     => $raw_body,
            'response' => array(
                'code'    => $http_code,
                'message' => '',
            ),
            'cookies'  => array(),
        );
    }

    /**
     * Extract a single header value from raw HTTP headers string.
     *
     * @param string $raw_headers Full raw headers string.
     * @param string $name        Header name (case-insensitive).
     * @return string Header value or empty string.
     */
    private function MPT_google_scraping_extract_raw_header( $raw_headers, $name ) {
        if ( preg_match( '/^' . preg_quote( $name, '/' ) . ':\\s*(.+)$/mi', $raw_headers, $m ) ) {
            return trim( $m[1] );
        }
        return '';
    }

    /**
     * Decode HTTP chunked transfer encoding.
     *
     * @since 4.2.0
     * @param string $body Raw chunked body.
     * @return string Decoded body.
     */
    private function MPT_google_scraping_decode_chunked( $body ) {
        $decoded = '';
        $offset = 0;
        $len = strlen( $body );
        while ( $offset < $len ) {
            $nl = strpos( $body, "\r\n", $offset );
            if ( false === $nl ) {
                break;
            }
            $chunk_size = hexdec( trim( substr( $body, $offset, $nl - $offset ) ) );
            if ( 0 === $chunk_size ) {
                break;
            }
            $decoded .= substr( $body, $nl + 2, $chunk_size );
            $offset = $nl + 2 + $chunk_size + 2;
        }
        return $decoded;
    }

    /**
     * Extract Set-Cookie pairs from raw HTTP headers.
     *
     * @since 4.2.0
     * @param string $raw_headers Raw header block.
     * @return string Cookie header value for next request.
     */
    private function MPT_google_scraping_collect_cookies_from_raw_headers( $raw_headers ) {
        $parts = array();
        if ( preg_match_all( '/^Set-Cookie:\\s*([^;\\r\\n]+)/mi', $raw_headers, $m ) ) {
            foreach ( $m[1] as $pair ) {
                $pair = trim( $pair );
                if ( false !== strpos( $pair, '=' ) ) {
                    $parts[] = $pair;
                }
            }
        }
        return implode( '; ', array_unique( $parts ) );
    }

    /**
     * Merge multiple Cookie header strings without duplicating names.
     *
     * @param string ...$cookie_headers Cookie header values.
     * @return string
     */
    private function MPT_google_scraping_merge_cookie_headers( ... $cookie_headers ) {
        $cookies = array();
        foreach ( $cookie_headers as $cookie_header ) {
            if ( !is_string( $cookie_header ) || '' === trim( $cookie_header ) ) {
                continue;
            }
            foreach ( explode( ';', $cookie_header ) as $cookie_part ) {
                $cookie_part = trim( $cookie_part );
                if ( '' === $cookie_part || false === strpos( $cookie_part, '=' ) ) {
                    continue;
                }
                list( $name, $value ) = explode( '=', $cookie_part, 2 );
                $cookies[trim( $name )] = trim( $value );
            }
        }
        $out = array();
        foreach ( $cookies as $name => $value ) {
            $out[] = $name . '=' . $value;
        }
        return implode( '; ', $out );
    }

    /**
     * @param array $response wp_remote_* response.
     * @return string Cookie header value for next request.
     */
    private function MPT_google_scraping_collect_cookies_from_response( $response ) {
        $parts = array();
        $h = wp_remote_retrieve_header( $response, 'set-cookie' );
        $lines = array();
        if ( is_array( $h ) ) {
            $lines = $h;
        } elseif ( is_string( $h ) && '' !== $h ) {
            $lines = array($h);
        }
        foreach ( $lines as $line ) {
            $sem = strpos( $line, ';' );
            $pair = ( false !== $sem ? substr( $line, 0, $sem ) : $line );
            if ( false !== strpos( $pair, '=' ) ) {
                $parts[] = trim( $pair );
            }
        }
        return implode( '; ', array_unique( $parts ) );
    }

    /**
     * @param string $body HTML body.
     * @return bool
     */
    private function MPT_google_scraping_is_consent_page( $body ) {
        return false !== stripos( $body, 'Before you continue' ) || false !== stripos( $body, 'consent.google.com' );
    }

    /**
     * @param string $body HTML body.
     * @return bool
     */
    private function MPT_google_scraping_is_bot_challenge_page( $body ) {
        return false !== stripos( $body, 'If you\'re having trouble accessing Google Search' ) || false !== stripos( $body, 'SG_SS=' ) || false !== stripos( $body, 'window.sgs' );
    }

    /**
     * Try a real local browser in headless mode before Bing fallback.
     *
     * @param string $google_url Original Google URL.
     * @return array{body: string, browser: string}|false
     */
    private function MPT_google_scraping_fetch_google_headless_html( $google_url ) {
        $log = $this->MPT_monolog_call();
        if ( !$this->MPT_google_scraping_proc_open_available() ) {
            $log->warning( 'Google scraping headless: proc_open is unavailable on this server' );
            return false;
        }
        $timeout_ms = (int) apply_filters( 'mpt_google_scraping_headless_timeout_ms', 5000, $google_url );
        if ( $timeout_ms < 2000 ) {
            $timeout_ms = 2000;
        }
        $browsers = $this->MPT_google_scraping_get_headless_browser_candidates();
        if ( empty( $browsers ) ) {
            $log->warning( 'Google scraping headless: no browser candidate configured or detected' );
            return false;
        }
        foreach ( $browsers as $browser ) {
            $browser_label = $browser;
            $temp_dir = $this->MPT_google_scraping_make_temp_dir( 'mpt-google-headless-' );
            if ( false === $temp_dir ) {
                $log->warning( 'Google scraping headless: could not create temporary browser profile directory' );
                return false;
            }
            $command = $this->MPT_google_scraping_build_headless_command(
                $browser,
                $google_url,
                $timeout_ms,
                $temp_dir
            );
            $log->info( 'Google scraping headless: launching browser', array(
                'browser'    => $browser_label,
                'timeout_ms' => $timeout_ms,
            ) );
            $result = $this->MPT_google_scraping_run_headless_command( $command, $timeout_ms + 2000 );
            $this->MPT_google_scraping_remove_temp_dir( $temp_dir );
            if ( false === $result ) {
                $log->warning( 'Google scraping headless: browser process failed', array(
                    'browser' => $browser_label,
                ) );
                continue;
            }
            if ( !empty( $result['timed_out'] ) ) {
                $log->warning( 'Google scraping headless: browser timed out', array(
                    'browser'    => $browser_label,
                    'timeout_ms' => $timeout_ms,
                ) );
                continue;
            }
            $stdout = ( isset( $result['stdout'] ) ? trim( (string) $result['stdout'] ) : '' );
            $stderr = ( isset( $result['stderr'] ) ? trim( (string) $result['stderr'] ) : '' );
            if ( '' === $stdout || false === stripos( $stdout, '<html' ) ) {
                $log->warning( 'Google scraping headless: browser returned no usable HTML', array(
                    'browser'    => $browser_label,
                    'exit_code'  => ( isset( $result['exit_code'] ) ? (int) $result['exit_code'] : -1 ),
                    'stderr'     => substr( $stderr, 0, 500 ),
                    'stdout_len' => strlen( $stdout ),
                ) );
                continue;
            }
            if ( $this->MPT_google_scraping_is_bot_challenge_page( $stdout ) ) {
                $log->warning( 'Google scraping headless: browser still received Google challenge page', array(
                    'browser' => $browser_label,
                ) );
            }
            return array(
                'body'    => $stdout,
                'browser' => $browser_label,
            );
        }
        return false;
    }

    /**
     * @return bool
     */
    private function MPT_google_scraping_proc_open_available() {
        if ( !function_exists( 'proc_open' ) || !function_exists( 'proc_get_status' ) ) {
            return false;
        }
        $disabled = (string) ini_get( 'disable_functions' );
        if ( '' === $disabled ) {
            return true;
        }
        $list = array_map( 'trim', explode( ',', $disabled ) );
        return !in_array( 'proc_open', $list, true );
    }

    /**
     * @return array<int, string>
     */
    private function MPT_google_scraping_get_headless_browser_candidates() {
        $candidates = array();
        $filter_path = apply_filters( 'mpt_google_scraping_headless_browser_path', '' );
        if ( is_string( $filter_path ) && '' !== trim( $filter_path ) ) {
            $candidates[] = trim( $filter_path );
        }
        $env_browser = getenv( 'MPT_GOOGLE_HEADLESS_BROWSER' );
        if ( is_string( $env_browser ) && '' !== trim( $env_browser ) ) {
            $candidates[] = trim( $env_browser );
        }
        if ( defined( 'MPT_GOOGLE_HEADLESS_BROWSER' ) && is_string( MPT_GOOGLE_HEADLESS_BROWSER ) && '' !== trim( MPT_GOOGLE_HEADLESS_BROWSER ) ) {
            $candidates[] = trim( MPT_GOOGLE_HEADLESS_BROWSER );
        }
        if ( '\\' === DIRECTORY_SEPARATOR ) {
            $candidates = array_merge( $candidates, array(
                'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe',
                'C:\\Program Files (x86)\\Google\\Chrome\\Application\\chrome.exe',
                'C:\\Program Files\\Microsoft\\Edge\\Application\\msedge.exe',
                'C:\\Program Files (x86)\\Microsoft\\Edge\\Application\\msedge.exe',
                'chrome',
                'msedge'
            ) );
        } elseif ( stripos( PHP_OS, 'Darwin' ) === 0 ) {
            $candidates = array_merge( $candidates, array(
                '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome',
                '/Applications/Microsoft Edge.app/Contents/MacOS/Microsoft Edge',
                'google-chrome',
                'chromium',
                'microsoft-edge'
            ) );
        } else {
            $candidates = array_merge( $candidates, array(
                'google-chrome',
                'chromium',
                'chromium-browser',
                'microsoft-edge',
                'microsoft-edge-stable'
            ) );
        }
        $candidates = array_values( array_unique( array_filter( array_map( 'trim', $candidates ) ) ) );
        $existing = array();
        $fallback = array();
        foreach ( $candidates as $candidate ) {
            if ( false !== strpos( $candidate, DIRECTORY_SEPARATOR ) || false !== strpos( $candidate, ':' ) ) {
                if ( !$this->MPT_google_scraping_is_safe_browser_path( $candidate ) ) {
                    continue;
                }
                if ( file_exists( $candidate ) ) {
                    $existing[] = $candidate;
                }
            } else {
                if ( preg_match( '/[\\/\\\\]/', $candidate ) || preg_match( '/[;&|`$]/', $candidate ) ) {
                    continue;
                }
                $fallback[] = $candidate;
            }
        }
        return array_merge( $existing, $fallback );
    }

    /**
     * Validate that a browser binary path is in a safe location and not suspicious.
     *
     * @since 6.2.0
     * @param string $path Absolute path to a browser binary.
     * @return bool
     */
    private function MPT_google_scraping_is_safe_browser_path( $path ) {
        $path = (string) $path;
        if ( '' === $path ) {
            return false;
        }
        if ( preg_match( '/[;&|`$]/', $path ) ) {
            return false;
        }
        $real = realpath( $path );
        if ( false === $real ) {
            return false;
        }
        $safe_prefixes = ( '\\' === DIRECTORY_SEPARATOR ? array('C:\\Program Files\\', 'C:\\Program Files (x86)\\') : array(
            '/usr/',
            '/opt/',
            '/snap/',
            '/Applications/'
        ) );
        $safe_prefixes = apply_filters( 'mpt_google_scraping_headless_safe_prefixes', $safe_prefixes );
        foreach ( $safe_prefixes as $prefix ) {
            if ( 0 === strncasecmp( $real, $prefix, strlen( $prefix ) ) ) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param string $browser_path Browser binary or command name.
     * @param string $google_url   Google Images URL.
     * @param int    $timeout_ms   Virtual time budget.
     * @param string $temp_dir     Temporary browser profile dir.
     * @return string
     */
    private function MPT_google_scraping_build_headless_command(
        $browser_path,
        $google_url,
        $timeout_ms,
        $temp_dir
    ) {
        $parts = array(
            $this->MPT_google_scraping_escape_shell_arg( $browser_path ),
            '--headless=new',
            '--disable-gpu',
            '--disable-extensions',
            '--disable-background-networking',
            '--disable-background-timer-throttling',
            '--disable-renderer-backgrounding',
            '--disable-sync',
            '--disable-features=AutomationControlled,Translate,OptimizationHints',
            '--disable-blink-features=AutomationControlled',
            '--hide-scrollbars',
            '--mute-audio',
            '--no-first-run',
            '--no-default-browser-check',
            '--window-size=1365,900',
            '--blink-settings=imagesEnabled=false',
            '--user-agent=' . $this->MPT_google_scraping_escape_shell_arg( 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36' ),
            '--user-data-dir=' . $this->MPT_google_scraping_escape_shell_arg( $temp_dir ),
            '--virtual-time-budget=' . (int) $timeout_ms,
            '--dump-dom',
            $this->MPT_google_scraping_escape_shell_arg( $google_url )
        );
        return implode( ' ', $parts );
    }

    /**
     * @param string $command    Shell command.
     * @param int    $timeout_ms Kill process after this timeout.
     * @return array{stdout: string, stderr: string, exit_code: int, timed_out: bool}|false
     */
    private function MPT_google_scraping_run_headless_command( $command, $timeout_ms ) {
        $descriptors = array(
            0 => array('pipe', 'r'),
            1 => array('pipe', 'w'),
            2 => array('pipe', 'w'),
        );
        $process = proc_open( $command, $descriptors, $pipes );
        if ( !is_resource( $process ) ) {
            return false;
        }
        fclose( $pipes[0] );
        stream_set_blocking( $pipes[1], false );
        stream_set_blocking( $pipes[2], false );
        $stdout = '';
        $stderr = '';
        $timed_out = false;
        $start = microtime( true );
        do {
            $stdout .= stream_get_contents( $pipes[1] );
            $stderr .= stream_get_contents( $pipes[2] );
            $status = proc_get_status( $process );
            if ( empty( $status['running'] ) ) {
                break;
            }
            if ( (microtime( true ) - $start) * 1000 > $timeout_ms ) {
                $timed_out = true;
                proc_terminate( $process );
                break;
            }
            usleep( 100000 );
        } while ( true );
        $stdout .= stream_get_contents( $pipes[1] );
        $stderr .= stream_get_contents( $pipes[2] );
        fclose( $pipes[1] );
        fclose( $pipes[2] );
        $exit_code = proc_close( $process );
        return array(
            'stdout'    => $stdout,
            'stderr'    => $stderr,
            'exit_code' => (int) $exit_code,
            'timed_out' => $timed_out,
        );
    }

    /**
     * @param string $prefix Directory prefix.
     * @return string|false
     */
    private function MPT_google_scraping_make_temp_dir( $prefix ) {
        $base = trailingslashit( sys_get_temp_dir() ) . $prefix . wp_generate_password( 12, false, false );
        return ( wp_mkdir_p( $base ) ? $base : false );
    }

    /**
     * @param string $dir Directory path.
     * @return void
     */
    private function MPT_google_scraping_remove_temp_dir( $dir ) {
        if ( !is_string( $dir ) || '' === $dir || !is_dir( $dir ) ) {
            return;
        }
        $entries = @scandir( $dir );
        if ( false !== $entries ) {
            foreach ( $entries as $entry ) {
                if ( '.' === $entry || '..' === $entry ) {
                    continue;
                }
                $path = $dir . DIRECTORY_SEPARATOR . $entry;
                if ( is_dir( $path ) ) {
                    $this->MPT_google_scraping_remove_temp_dir( $path );
                } elseif ( file_exists( $path ) ) {
                    @unlink( $path );
                }
            }
        }
        @rmdir( $dir );
    }

    /**
     * @param string $arg Shell argument.
     * @return string
     */
    private function MPT_google_scraping_escape_shell_arg( $arg ) {
        if ( '\\' === DIRECTORY_SEPARATOR ) {
            return '"' . str_replace( array('"', '%', '!'), array('""', '%%', '^!'), $arg ) . '"';
        }
        return escapeshellarg( $arg );
    }

    /**
     * POST "Reject all" on consent.google.com/save and follow redirect to search results.
     *
     * @param string $body       Consent HTML.
     * @param string $cookies    Existing Cookie header.
     * @param array  $defaults   Base request args.
     * @param string $google_url Original Google URL (for Referer).
     * @return array{body: string, cookies: string}|false
     */
    private function MPT_google_scraping_post_consent_reject(
        $body,
        $cookies,
        $defaults,
        $google_url
    ) {
        $log = $this->MPT_monolog_call();
        preg_match_all(
            '/<form[^>]+action=["\'](https:\\/\\/consent\\.google\\.com\\/save)["\'][^>]*>(.*?)<\\/form>/is',
            $body,
            $forms,
            PREG_SET_ORDER
        );
        $form_html = '';
        foreach ( $forms as $f ) {
            if ( isset( $f[2] ) && false !== strpos( $f[2], 'Reject all' ) ) {
                $form_html = $f[2];
                break;
            }
        }
        if ( '' === $form_html ) {
            $log->warning( 'Google scraping consent: no "Reject all" form found in HTML' );
            return false;
        }
        $fields = $this->MPT_google_scraping_parse_hidden_inputs( $form_html );
        if ( empty( $fields ) ) {
            $log->warning( 'Google scraping consent: could not parse hidden fields' );
            return false;
        }
        $req = array_merge( $defaults, array(
            'method'      => 'POST',
            'body'        => $fields,
            'redirection' => 0,
            'headers'     => array_merge( ( isset( $defaults['headers'] ) && is_array( $defaults['headers'] ) ? $defaults['headers'] : array() ), array(
                'Content-Type' => 'application/x-www-form-urlencoded',
                'Cookie'       => $cookies,
                'Referer'      => $google_url,
                'Origin'       => 'https://consent.google.com',
            ) ),
        ) );
        $post = wp_remote_post( 'https://consent.google.com/save', $req );
        if ( is_wp_error( $post ) || empty( $post['response'] ) ) {
            $log->error( 'Google scraping consent: POST to consent.google.com failed', array(
                'error' => ( is_wp_error( $post ) ? $post->get_error_message() : 'empty response' ),
            ) );
            return false;
        }
        $code = wp_remote_retrieve_response_code( $post );
        $merged_cookies = $this->MPT_google_scraping_merge_cookie_headers( $cookies, $this->MPT_google_scraping_collect_cookies_from_response( $post ) );
        $location = wp_remote_retrieve_header( $post, 'location' );
        $log->info( 'Google scraping consent: POST response', array(
            'http_code' => (int) $code,
            'location'  => ( $location ? $location : '' ),
        ) );
        if ( (302 === (int) $code || 303 === (int) $code || 301 === (int) $code) && $location ) {
            $follow = $this->MPT_google_scraping_request_url(
                $location,
                $defaults,
                $merged_cookies,
                $google_url,
                'consent_redirect_follow'
            );
            if ( !is_wp_error( $follow ) && 200 === (int) wp_remote_retrieve_response_code( $follow ) ) {
                $merged_cookies = $this->MPT_google_scraping_merge_cookie_headers( $merged_cookies, $this->MPT_google_scraping_collect_cookies_from_response( $follow ) );
                $follow_body = wp_remote_retrieve_body( $follow );
                $this->MPT_google_scraping_diagnostic_log_response( 'consent_redirect_follow', $follow, $follow_body );
                $log->info( 'Google scraping consent: followed redirect to search page OK' );
                return array(
                    'body'    => $follow_body,
                    'cookies' => $merged_cookies,
                );
            }
            $fl_code = ( is_wp_error( $follow ) ? 0 : (int) wp_remote_retrieve_response_code( $follow ) );
            $log->warning( 'Google scraping consent: redirect follow failed', array(
                'http_code' => $fl_code,
                'error'     => ( is_wp_error( $follow ) ? $follow->get_error_message() : '' ),
            ) );
        }
        if ( 200 === (int) $code ) {
            $log->info( 'Google scraping consent: got 200 without redirect, using POST body' );
            return array(
                'body'    => wp_remote_retrieve_body( $post ),
                'cookies' => $merged_cookies,
            );
        }
        $log->warning( 'Google scraping consent: unexpected response, giving up', array(
            'http_code' => (int) $code,
        ) );
        return false;
    }

    /**
     * @param string $html Form inner HTML.
     * @return array<string, string>
     */
    private function MPT_google_scraping_parse_hidden_inputs( $html ) {
        $fields = array();
        preg_match_all( '/<input[^>]+>/i', $html, $tags );
        foreach ( $tags[0] as $tag ) {
            if ( false === stripos( $tag, 'hidden' ) && false === stripos( $tag, 'submit' ) ) {
                continue;
            }
            $name = '';
            $value = '';
            if ( preg_match( '/name=["\']([^"\']*)["\']/', $tag, $m ) ) {
                $name = $m[1];
            }
            if ( preg_match( '/value=["\']([^"\']*)["\']/', $tag, $m ) ) {
                $value = $m[1];
            }
            if ( '' !== $name ) {
                $fields[$name] = $value;
            }
        }
        return $fields;
    }

    /**
     * Parse Google Images HTML (legacy data-* and embedded JSON fragments).
     *
     * @param string $body HTML.
     * @return array{results: array<int, array{url: string, alt: string, caption: string}>, source: string}
     */
    private function MPT_google_scraping_parse_google_html( $body ) {
        $empty = array(
            'results' => array(),
            'source'  => 'none',
        );
        preg_match_all( '/data-pt="([^"]*)"/', $body, $output_img_alts );
        preg_match_all( '/data-st="([^"]*)"/', $body, $output_img_captions );
        preg_match_all( '/data-ou="(https?:[^"]*)"/', $body, $output_img_urls );
        if ( !empty( $output_img_urls[1] ) ) {
            $n = count( $output_img_urls[1] );
            $alts = ( isset( $output_img_alts[1] ) ? array_pad( array_values( $output_img_alts[1] ), $n, '' ) : array_fill( 0, $n, '' ) );
            $captions = ( isset( $output_img_captions[1] ) ? array_pad( array_values( $output_img_captions[1] ), $n, '' ) : array_fill( 0, $n, '' ) );
            return array(
                'results' => array_map(
                    array($this, 'MPT_order_array_urls'),
                    $output_img_urls[1],
                    $alts,
                    $captions
                ),
                'source'  => 'google_data_ou',
            );
        }
        // Some responses embed image URLs in JSON-like fragments.
        preg_match_all( '/"ou":"(https?:[^"\\\\]+)"/', $body, $ou_json );
        if ( !empty( $ou_json[1] ) ) {
            $out = array();
            foreach ( $ou_json[1] as $u ) {
                if ( false !== strpos( $u, 'gstatic.com' ) ) {
                    continue;
                }
                $out[] = array(
                    'url'     => $u,
                    'alt'     => '',
                    'caption' => '',
                );
            }
            if ( !empty( $out ) ) {
                return array(
                    'results' => $out,
                    'source'  => 'google_ou_json',
                );
            }
        }
        preg_match_all( '/\\/imgres\\?[^"\']*?[?&]imgurl=([^&"\']+)/', $body, $imgres_urls );
        if ( !empty( $imgres_urls[1] ) ) {
            $from_imgres = $this->MPT_google_scraping_build_result_rows_from_urls( $imgres_urls[1] );
            if ( !empty( $from_imgres ) ) {
                return array(
                    'results' => $from_imgres,
                    'source'  => 'google_imgres_imgurl',
                );
            }
        }
        preg_match_all( '/https?:\\\\\\/\\\\\\/[^"\']+?(?:jpe?g|png|webp|gif|bmp)(?:\\?[^"\']*)?/i', $body, $escaped_image_urls );
        if ( !empty( $escaped_image_urls[0] ) ) {
            $from_escaped = $this->MPT_google_scraping_build_result_rows_from_urls( $escaped_image_urls[0] );
            if ( !empty( $from_escaped ) ) {
                return array(
                    'results' => $from_escaped,
                    'source'  => 'google_escaped_image_urls',
                );
            }
        }
        return $empty;
    }

    /**
     * Normalize candidate URLs and convert them to standard result rows.
     *
     * @param array<int, string> $urls Candidate URLs.
     * @return array<int, array{url: string, alt: string, caption: string}>
     */
    private function MPT_google_scraping_build_result_rows_from_urls( $urls ) {
        $out = array();
        $seen = array();
        foreach ( $urls as $candidate_url ) {
            $url = $this->MPT_google_scraping_normalize_candidate_url( $candidate_url );
            if ( '' === $url ) {
                continue;
            }
            $key = strtolower( $url );
            if ( isset( $seen[$key] ) ) {
                continue;
            }
            $seen[$key] = true;
            $out[] = array(
                'url'     => $url,
                'alt'     => '',
                'caption' => '',
            );
            if ( count( $out ) >= 20 ) {
                break;
            }
        }
        return $out;
    }

    /**
     * Decode and validate a candidate image URL extracted from HTML/JSON fragments.
     *
     * @param string $url Candidate URL.
     * @return string
     */
    private function MPT_google_scraping_normalize_candidate_url( $url ) {
        if ( !is_string( $url ) || '' === $url ) {
            return '';
        }
        $url = wp_specialchars_decode( $url, ENT_QUOTES );
        $url = html_entity_decode( $url, ENT_QUOTES | ENT_HTML5, 'UTF-8' );
        $url = str_replace( '\\/', '/', $url );
        $url = str_ireplace( array(
            '\\u003d',
            '\\u0026',
            '\\x3d',
            '\\x26',
            '&amp;'
        ), array(
            '=',
            '&',
            '=',
            '&',
            '&'
        ), $url );
        for ($i = 0; $i < 2; $i++) {
            $decoded = rawurldecode( $url );
            if ( $decoded === $url ) {
                break;
            }
            $url = $decoded;
        }
        $url = trim( $url );
        if ( !preg_match( '#^https?://#i', $url ) ) {
            return '';
        }
        $host = wp_parse_url( $url, PHP_URL_HOST );
        if ( !is_string( $host ) || '' === $host ) {
            return '';
        }
        $host = strtolower( $host );
        if ( false !== strpos( $host, 'gstatic.com' ) || false !== strpos( $host, 'googleusercontent.com' ) || preg_match( '/(^|\\.)google\\./', $host ) ) {
            return '';
        }
        if ( $this->MPT_google_scraping_is_private_host( $host ) ) {
            return '';
        }
        return esc_url_raw( $url );
    }

    /**
     * Check whether a hostname resolves to a private / reserved IP range (SSRF protection).
     *
     * @since 6.2.0
     * @param string $host Hostname or IP.
     * @return bool True if the host points to a private/reserved address.
     */
    private function MPT_google_scraping_is_private_host( $host ) {
        if ( filter_var( $host, FILTER_VALIDATE_IP ) ) {
            $ip = $host;
        } else {
            $ip = gethostbyname( $host );
            if ( $ip === $host ) {
                return true;
            }
        }
        return !filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE );
    }

    /**
     * Parse Google Images `tbs` query string from the same URL as the plugin builds.
     *
     * @param string $tbs Raw tbs value.
     * @return array{sur:string,itp:string,isz:string,iar:string,isc:string}
     */
    private function MPT_google_scraping_parse_google_image_tbs( $tbs ) {
        $out = array(
            'sur' => '',
            'itp' => '',
            'isz' => '',
            'iar' => '',
            'isc' => '',
        );
        if ( !is_string( $tbs ) || '' === trim( $tbs ) ) {
            return $out;
        }
        if ( preg_match( '/sur:([^,]+)/', $tbs, $m ) ) {
            $out['sur'] = $m[1];
        }
        if ( preg_match( '/itp:([^,]+)/', $tbs, $m ) ) {
            $out['itp'] = $m[1];
        }
        if ( preg_match( '/isz:([^,]+(?:,islt:[^,]+)?)/', $tbs, $m ) ) {
            $out['isz'] = $m[1];
        }
        if ( preg_match( '/iar:([^,]+)/', $tbs, $m ) ) {
            $out['iar'] = $m[1];
        }
        if ( preg_match( '/ic:specific,isc:([^,]+)/', $tbs, $m ) ) {
            $out['isc'] = $m[1];
        }
        return $out;
    }

    /**
     * Map Google Images `tbs` fragments to Bing Images `qft` (+filterui:…) tokens.
     *
     * @param array{sur:string,itp:string,isz:string,iar:string,isc:string} $parsed Parsed tbs.
     * @return string Encoded qft value for Bing (leading +filterui:… chain) or empty string.
     */
    private function MPT_google_scraping_build_bing_qft_from_google_tbs( $parsed ) {
        if ( !is_array( $parsed ) ) {
            return '';
        }
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
        $tokens = apply_filters( 'mpt_google_scraping_bing_qft_tokens', $tokens, $parsed );
        if ( empty( $tokens ) ) {
            return '';
        }
        // Bing web UI uses space-separated "filterui:…" tokens (leading space), e.g. %20filterui%3Aimagesize-large%20filterui%3Aphoto-photo
        return ' ' . implode( ' ', array_map( static function ( $t ) {
            return 'filterui:' . $t;
        }, $tokens ) );
    }

    /**
     * Fetch Bing Images HTML and parse murl/t from iusc anchors (works without JS).
     *
     * @param string $google_url Original Google URL (extract q, safe).
     * @param array  $defaults   Base HTTP args.
     * @return array<int, array{url: string, alt: string, caption: string}>
     */
    private function MPT_google_scraping_fetch_bing_images( $google_url, $defaults ) {
        $log = $this->MPT_monolog_call();
        $parts = wp_parse_url( $google_url );
        $query = ( isset( $parts['query'] ) ? $parts['query'] : '' );
        parse_str( $query, $args );
        $q = ( isset( $args['q'] ) ? rawurldecode( (string) $args['q'] ) : '' );
        if ( '' === $q ) {
            $log->warning( 'Google scraping Bing fallback: empty search query in Google URL' );
            return array();
        }
        $safe = ( isset( $args['safe'] ) ? (string) $args['safe'] : 'medium' );
        $safe_map = array(
            'off'      => 'off',
            'medium'   => 'moderate',
            'moderate' => 'moderate',
            'high'     => 'strict',
            'activate' => 'strict',
        );
        $bing_safe = ( isset( $safe_map[$safe] ) ? $safe_map[$safe] : 'moderate' );
        $tbs_parsed = $this->MPT_google_scraping_parse_google_image_tbs( ( isset( $args['tbs'] ) ? (string) $args['tbs'] : '' ) );
        $qft = $this->MPT_google_scraping_build_bing_qft_from_google_tbs( $tbs_parsed );
        $bing_args = array(
            'q'          => $q,
            'first'      => '1',
            'safeSearch' => $bing_safe,
        );
        if ( '' !== $qft ) {
            $bing_args['qft'] = $qft;
        }
        $bing_url = add_query_arg( $bing_args, 'https://www.bing.com/images/search' );
        $log->info( 'Google scraping Bing fallback: requesting Bing Images', array(
            'bing_url'    => $bing_url,
            'qft_applied' => '' !== $qft,
        ) );
        $res = wp_remote_get( $bing_url, array_merge( $defaults, array(
            'redirection' => 5,
            'headers'     => array_merge( ( isset( $defaults['headers'] ) && is_array( $defaults['headers'] ) ? $defaults['headers'] : array() ), array(
                'Referer' => 'https://www.bing.com/',
            ) ),
        ) ) );
        if ( is_wp_error( $res ) ) {
            $log->error( 'Google scraping Bing fallback: HTTP error', array(
                'error' => $res->get_error_message(),
            ) );
            return array();
        }
        $bing_code = (int) wp_remote_retrieve_response_code( $res );
        if ( 200 !== $bing_code ) {
            $log->warning( 'Google scraping Bing fallback: non-200 response', array(
                'http_code' => $bing_code,
            ) );
            return array();
        }
        $html = wp_remote_retrieve_body( $res );
        $parsed = $this->MPT_google_scraping_parse_bing_html( $html );
        $log->info( 'Google scraping Bing fallback: parsed HTML', array(
            'html_length'  => strlen( $html ),
            'images_found' => count( $parsed ),
        ) );
        return $parsed;
    }

    /**
     * @param string $html Bing Images HTML.
     * @return array<int, array{url: string, alt: string, caption: string}>
     */
    private function MPT_google_scraping_parse_bing_html( $html ) {
        $out = array();
        preg_match_all( '/&quot;murl&quot;:&quot;(https?:[^&]+)&quot;/', $html, $murls );
        preg_match_all( '/&quot;t&quot;:&quot;((?:[^&]|&amp;)+?)&quot;,&quot;mid&quot;/', $html, $titles );
        $titles_list = ( isset( $titles[1] ) ? $titles[1] : array() );
        $i = 0;
        foreach ( ( isset( $murls[1] ) ? $murls[1] : array() ) as $url ) {
            $url = str_replace( '\\/', '/', $url );
            if ( '' === $url || 0 === strpos( $url, 'https://tse' ) || 0 === strpos( $url, 'http://tse' ) ) {
                continue;
            }
            $t = ( isset( $titles_list[$i] ) ? $titles_list[$i] : '' );
            $t = wp_specialchars_decode( $t, ENT_QUOTES );
            $t = html_entity_decode( $t, ENT_QUOTES | ENT_HTML5, 'UTF-8' );
            $out[] = array(
                'url'     => $url,
                'alt'     => $t,
                'caption' => $t,
            );
            $i++;
            if ( count( $out ) >= 20 ) {
                break;
            }
        }
        return $out;
    }

    /**
     * Remove gstatic images
     *
     * @since    4.0.0
     */
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
