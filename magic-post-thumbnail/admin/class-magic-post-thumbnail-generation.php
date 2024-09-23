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
        $post_ids = array_map( 'absint', json_decode( $_POST['ids_mpt_generation'] ) );
        // Security checks & Check if user has rights
        if ( !current_user_can( 'mpt_manage' ) || false === wp_verify_nonce( $_POST['nonce'], 'ajax_nonce_magic_post_thumbnail' ) ) {
            wp_send_json_error();
        }
        if ( !isset( $_POST['ids_mpt_generation'] ) ) {
            return false;
        }
        $count = count( $post_ids );
        foreach ( $post_ids as $key => $val ) {
            $ids[$key + 1] = $val;
        }
        $a = (int) $_POST['a'];
        $id = $ids[$a];
        $main_settings = get_option( 'MPT_plugin_main_settings' );
        if ( isset( $main_settings['rewrite_featured'] ) && $main_settings['rewrite_featured'] == true ) {
            $rewrite_featured = true;
        } else {
            $rewrite_featured = false;
        }
        $speed = '500';
        // Image location
        $image_location = ( !empty( $main_settings['image_location'] ) ? $main_settings['image_location'] : 'featured' );
        if ( "featured" !== $image_location ) {
        } else {
            if ( has_post_thumbnail( $id ) && $rewrite_featured == false ) {
                $status = 'already-done';
            } elseif ( (!has_post_thumbnail( $id ) || $rewrite_featured == true) && $id != 0 ) {
                $MPT_return = $this->MPT_create_thumb(
                    $id,
                    '0',
                    '0',
                    '0',
                    $rewrite_featured
                );
                if ( $MPT_return == null ) {
                    $status = 'failed';
                } else {
                    $status = 'successful';
                    $speed = '500';
                }
            } else {
                $status = 'error';
            }
        }
        $percent = 100 * $a / $count;
        $compatibility = wp_parse_args( get_option( 'MPT_plugin_compatibility_settings' ), $this->MPT_default_options_compatibility_settings( TRUE ) );
        $img = '';
        if ( true == $compatibility['enable_FIFU'] && is_plugin_active( 'featured-image-from-url/featured-image-from-url.php' ) ) {
        } elseif ( ($status == 'already-done' || $status == 'successful') && !empty( $MPT_return ) ) {
            // Generation successful
            $new_image = wp_get_attachment_image_src( $MPT_return, array(70, 70) );
            $fimg = '<a class="generated-img" target="_blank" href="' . admin_url() . 'upload.php?item=' . $MPT_return . '"><img src="' . $new_image[0] . '" width="70" height="70" /></a>';
            $datas['thumbnail_id'] = $MPT_return;
            $datas['postimagediv'] = _wp_post_thumbnail_html( $MPT_return, $id );
        } elseif ( $status == 'already-done' && "featured" === $image_location ) {
            $fimg = '<a class="generated-img" target="_blank" href="' . admin_url() . 'upload.php?item=' . get_post_thumbnail_id( $id ) . '">' . get_the_post_thumbnail( $id, array('70', '70') ) . '</a>';
        } else {
            $fimg = '<img src="' . plugins_url( 'img/no-image.jpg', __FILE__ ) . '" />';
        }
        $datas['percent'] = $percent;
        $datas['id'] = $id;
        $datas['status'] = $status;
        $datas['img'] = $img;
        $datas['fimg'] = $fimg;
        $datas['speed'] = $speed;
        // Send data to JavaScript
        if ( !empty( $datas['id'] ) && !empty( $datas['percent'] ) ) {
            wp_send_json_success( $datas );
        } else {
            wp_send_json_error( $datas );
        }
    }

    public function MPT_check_post_type( $ID, $post, $update ) {
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
            $this->MPT_create_thumb(
                $ID,
                '0',
                '1',
                '0',
                $rewrite_featured
            );
        }
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
        $api_chosen = null
    ) {
        // Launch logs
        $log = $this->MPT_monolog_call();
        $log->info( 'New generation starting', array(
            'post' => $id,
        ) );
        //Avoid revision post type
        if ( 'revision' == get_post_type( $id ) ) {
            $log->error( 'This post is a revision. Avoided.', array(
                'post' => $id,
            ) );
            return false;
        }
        // Settings
        $main_settings = get_option( 'MPT_plugin_main_settings' );
        // Image location
        $image_location = ( !empty( $main_settings['image_location'] ) ? $main_settings['image_location'] : 'featured' );
        // Check if thumbnail already exists
        if ( has_post_thumbnail( $id ) && $rewrite_featured == false && "featured" === $image_location ) {
            $log->error( 'Featured image already exists', array(
                'post' => $id,
            ) );
            return false;
        }
        //$posts_settings          = $this->MPT_default_options_posts_settings();
        $main_settings = $this->MPT_default_options_main_settings();
        $compatibility_settings = $this->MPT_default_options_compatibility_settings();
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
        }
        $executeElseBlock = true;
        if ( $executeElseBlock ) {
            if ( TRUE == $get_only_thumb ) {
                $search = $extracted_search_term;
            } elseif ( $options['based_on'] == 'text_analyser' ) {
                require_once dirname( __FILE__ ) . '/../includes/text-miner/TextMiner.php';
                $content = get_the_content( '', false, $id );
                $tm = new TextMiner();
                $content = str_replace( "&nbsp;", '', $content );
                $tm->addText( wp_strip_all_tags( $content ) );
                $tm->convertToLower = TRUE;
                // optional
                //$tm->includeLowerNGrams = TRUE;
                $tm->process();
                //should be called before accessing keywords
                $search = $tm->getTopNGrams( 1, false );
                $log->info( 'Extracted search term', array(
                    'post'        => $id,
                    'search term' => $search,
                ) );
            } else {
                $search = get_post_field( 'post_title', $id, 'raw' );
            }
        }
        if ( isset( $options['title_selection'] ) && $options['title_selection'] == 'cut_title' && isset( $options['title_length'] ) ) {
            $length_title = (int) $options['title_length'] - 1;
            $search = preg_replace( '/((\\w+\\W*){' . $length_title . '}(\\w+))(.*)/', '${1}', $search );
        }
        if ( TRUE == $get_only_thumb ) {
            /* SET ALL PARAMETERS */
            $array_parameters = $this->MPT_Get_Parameters( $options, $search );
            $api_url = $array_parameters['url'];
            unset($array_parameters['url']);
            if ( !isset( $api_url ) ) {
                $log->error( 'API URL not provided', array(
                    'post' => $id,
                ) );
                return false;
            }
            $result_body = $this->MPT_Generate(
                $options['api_chosen'],
                $api_url,
                $array_parameters,
                $options['selected_image'],
                $get_only_thumb,
                $search
            );
            return $result_body;
        }
        // Check if bank option set with array (version MPT 5) or single result
        if ( is_array( $options['api_chosen_auto'] ) ) {
            $last_bank_element = end( $options['api_chosen_auto'] );
            foreach ( $options['api_chosen_auto'] as $bank ) {
                $log->info( 'Search with bank ' . $bank, array(
                    'post' => $id,
                ) );
                // Reset options according new image bank
                $options['api_chosen'] = $bank;
                $array_parameters = $this->MPT_Get_Parameters( $options, $search );
                $api_url = $array_parameters['url'];
                /* GET THE IMAGE URL */
                list( $url_results, $file_media, $alt_img ) = $this->MPT_Generate(
                    $bank,
                    $api_url,
                    $array_parameters,
                    $options['selected_image'],
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
            /* SET ALL PARAMETERS */
            $array_parameters = $this->MPT_Get_Parameters( $options, $search );
            $api_url = $array_parameters['url'];
            unset($array_parameters['url']);
            if ( !isset( $api_url ) ) {
                $log->error( 'API URL not provided', array(
                    'post' => $id,
                ) );
                return false;
            }
            /* GET THE IMAGE URL */
            list( $url_results, $file_media, $alt_img ) = $this->MPT_Generate(
                $options['api_chosen'],
                $api_url,
                $array_parameters,
                $options['selected_image'],
                false,
                $search
            );
            if ( !isset( $url_results ) || !isset( $file_media ) ) {
                if ( !isset( $url_results ) || !isset( $file_media ) ) {
                    $log->error( 'No results', array(
                        'post' => $id,
                    ) );
                    return false;
                }
            }
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
        /* Image filename : title extension */
        $search = str_replace( '%', '', sanitize_title( $search ) );
        // Remove % for non-latin characters
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
            if ( true == $png_jpg && 'dallev1' == $options['api_chosen'] ) {
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
                /* Fire filter "wp_handle_upload" for plugins like optimizers etc. */
                $img_values = array(
                    'file' => $wp_upload_dir['path'] . '/' . urlencode( $filename ),
                    'url'  => $wp_upload_dir['url'] . '/' . urlencode( $filename ),
                    'type' => $wp_filetype['type'],
                );
                apply_filters( 'wp_handle_upload', $img_values );
                require_once ABSPATH . 'wp-admin/includes/image.php';
                $attach_data = wp_generate_attachment_metadata( $attach_id, $wp_upload_dir['path'] . '/' . urlencode( $filename ) );
                $var = wp_update_attachment_metadata( $attach_id, $attach_data );
                $executeElseBlock = true;
                if ( $executeElseBlock ) {
                    $log->info( 'Featured image added', array(
                        'post'  => $id,
                        'image' => $attach_id,
                    ) );
                    set_post_thumbnail( $id, $attach_id );
                }
                // Link the media to the post
                $media_link_uploaded_to = wp_update_post( array(
                    'ID'          => $attach_id,
                    'post_parent' => $id,
                ), true );
                do_action( 'mpt_after_create_thumb', $id, $attach_id );
                return $attach_id;
            }
        }
    }

    /**
     * Get all settings from user
     *
     * @since    4.0.0
     */
    private function MPT_Get_Parameters( $options, $search ) {
        /* GOOGLE IMAGE SCRAPING PARAMETERS */
        if ( $options['api_chosen'] == 'google_scraping' ) {
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
                'url'  => 'http://www.google.com/search',
                'tbm'  => 'isch',
                'q'    => urlencode( $search ),
                'hl'   => $country,
                'safe' => $safe,
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
        } elseif ( $options['api_chosen'] == 'google_image' ) {
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
        } elseif ( $options['api_chosen'] == 'dallev1' ) {
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
                    "model"  => "dall-e-3",
                    "prompt" => $search,
                    "n"      => 1,
                    "size"   => $img_size,
                ) ),
            );
        } elseif ( $options['api_chosen'] == 'flickr' ) {
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
        } elseif ( $options['api_chosen'] == 'pixabay' ) {
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
        } elseif ( $options['api_chosen'] == 'youtube' ) {
        } elseif ( $options['api_chosen'] == 'pexels' ) {
        } elseif ( $options['api_chosen'] == 'envato' ) {
        } elseif ( $options['api_chosen'] == 'unsplash' ) {
        } elseif ( $options['api_chosen'] == 'cc_search' || $options['api_chosen'] == 'openverse' ) {
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
        } elseif ( $service == 'flickr' ) {
            $loop_results = $result_body['photos']['photo'];
            $url_path = 'id';
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
        } elseif ( $service == 'envato' ) {
            $loop_results = $result_body['results']['search_query_result']['search_payload']['items'];
            $url_path = 'humane_id';
        } else {
            return false;
        }
        // Check if function is launch for Gutnberg block
        if ( TRUE == $get_only_thumb && $service == 'envato' ) {
            return $result_body['results']['search_query_result']['search_payload'];
        } else {
            if ( TRUE == $get_only_thumb && 'flickr' != $service ) {
                return $result_body;
            } else {
            }
        }
        // Testing images
        if ( $service == 'google_scraping' ) {
            foreach ( $loop_results as $loop_result_result => $loop_result ) {
                $remote_img = wp_remote_head( $loop_result['url'] );
                $remote_response = wp_remote_retrieve_response_code( $remote_img );
                $log = $this->MPT_monolog_call();
                $log->info( 'remote_img', array(
                    'remote_img' => $loop_result,
                ) );
                if ( 200 !== $remote_response ) {
                    // Remove the result, image not valid
                    unset($loop_results[$loop_result_result]);
                } else {
                    // Image ok. Avoid next results.
                    break;
                }
                $infos_img = @getimagesize( $loop_result['url'] );
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
            foreach ( $loop_results as $fetch_result ) {
                $url_result = $fetch_result[$url_path];
                // Change default url image
                if ( $service == 'google_image' ) {
                    $url_result = $url_result['cse_image'][0]['src'];
                } elseif ( $service == 'unsplash' ) {
                    $url_result = $url_result['full'];
                } elseif ( $service == 'pexels' ) {
                    $url_result = $url_result['original'];
                } elseif ( $service == 'dallev1' ) {
                    // Show revised prompt (by openAI) in logs
                    $log = $this->MPT_monolog_call();
                    $log->info( 'DALL_E', array(
                        'revised_prompt' => $fetch_result['revised_prompt'],
                    ) );
                } else {
                    $url_result = $fetch_result[$url_path];
                }
                $options = wp_parse_args( get_option( 'MPT_plugin_main_settings' ), $this->MPT_default_options_main_settings( TRUE ) );
                $alt = '';
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
                // ENVATO : Additional remote request to get image url
                if ( $service == 'envato' ) {
                    $url = 'https://api.extensions.envato.com/extensions/item/' . $url_result . '/download';
                    $project_ags = array(
                        'project_name' => get_bloginfo( 'name' ),
                    );
                    $result_img_envato = wp_remote_post( add_query_arg( $project_ags, $url ), array(
                        'headers' => array(
                            "Extensions-Extension-Id" => md5( get_site_url() ),
                            "Extensions-Token"        => $url_parameters['envato_token'],
                            "Content-Type"            => "application/json",
                        ),
                    ) );
                    $result = json_decode( $result_img_envato['body'] );
                    $url_result = $result->download_urls->max2000;
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
                // Avoid unknown image type
                if ( $service != 'dallev1' && $service != 'unsplash' && $service != 'pexels' && $service != 'envato' ) {
                    $url_result = $url_result;
                    $wp_filetype = wp_check_filetype( $url_result );
                    if ( false == $wp_filetype['type'] ) {
                        continue;
                    }
                }
                if ( TRUE == $get_only_thumb ) {
                    $url_result_ar['photos'][]['url'] = $url_result;
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
            if ( TRUE == $get_only_thumb ) {
                return $url_result_ar;
            }
        } else {
            return false;
        }
        return array($url_result, $file_media, $alt);
    }

    /**
     * Image results to get thumbnails
     *
     * @since    4.0.0
     */
    private function MPT_get_results( $service, $url, $url_parameters ) {
        if ( $service == 'dallev1' || $service == 'pexels' ) {
            $defaults = $url_parameters;
        } else {
            /* Retrieve 3 images as result */
            $url = add_query_arg( $url_parameters, $url );
            // Simulate Default Browser
            $defaults = array(
                'redirection'        => 9,
                'user-agent'         => 'Mozilla/5.0 (Linux; Android 6.0.1; Nexus 5X Build/MMB29P) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2272.96',
                'reject_unsafe_urls' => false,
                'sslverify'          => false,
            );
        }
        // Proxy settins
        $options_proxy = get_option( 'MPT_plugin_proxy_settings' );
        $result = wp_remote_request( $url, $defaults );
        // If error happen
        if ( !empty( $result->errors['http_request_failed'] ) ) {
            return false;
        }
        // Google Scraping : Different method
        if ( $service == 'google_scraping' ) {
            // Get all alts from Google
            preg_match_all( '/data-pt="([^"]*)"/', $result['body'], $output_img_alts );
            // Get all images from Google
            //preg_match_all( '/,\["http[^"]((?!gstatic).)*",\d+?,\d+?\]/', $result['body'], $output_img_urls );
            preg_match_all( '/data-ou="(http[^"]*)"/', $result['body'], $output_img_urls );
            $result_body['results'] = array_map( array(&$this, 'MPT_order_array_urls'), $output_img_urls[1], $output_img_alts[1] );
        } else {
            $result_body = json_decode( $result['body'], true );
            if ( $result['response']['code'] != '200' ) {
                return false;
            }
        }
        return array($result_body, $result);
    }

    /**
     * Remove gstatic images
     *
     * @since    4.0.0
     */
    public function MPT_order_array_urls( $str, $str_alt ) {
        // Get only the url and exclude Google image url (domain gstatic)
        $pattern = '/,\\["(http[^"]((?!gstatic).)*)",\\d+?,\\d+?\\]/';
        $replacement = '$1';
        $real_url = preg_replace( $pattern, $replacement, $str );
        return array(
            'url' => $real_url,
            'alt' => $str_alt,
        );
    }

}
