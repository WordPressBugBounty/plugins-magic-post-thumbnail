<?php

/**
 * Shared helpers for admin UI and generation.
 *
 * @package    Magic_Post_Thumbnail
 * @subpackage Magic_Post_Thumbnail/admin
 */
class Magic_Post_Thumbnail_Helpers {
    /**
     * @var string
     */
    protected $plugin_name;

    /**
     * @var string
     */
    protected $version;

    /**
     * URL being downloaded in Gutenberg block (for http_request_args filter).
     *
     * @var string|null
     */
    protected $block_downloading_url = null;

    /**
     * In-request generation depth per post ID.
     *
     * @var array
     */
    protected static $lock_depth = array();

    /**
     * In-request dedupe for save_post / wp_insert_post.
     *
     * @var array
     */
    protected static $in_request_posts = array();

    /**
     * Whether AJAX/automation hooks were registered.
     *
     * @var bool
     */
    protected static $automation_registered = false;

    /**
     * Whether current HTTP request is marked as MPT.
     *
     * @var bool
     */
    protected static $mpt_http_request = false;

    const LOCK_TTL = 120;

    /**
     * @param string $plugin_name Plugin slug.
     * @param string $version     Plugin version.
     */
    public function __construct( $plugin_name, $version ) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    /**
     * Freemius instance.
     *
     * @since 4.0.0
     * @return object
     */
    public function MPT_freemius() {
        return mpt_freemius();
    }

    /**
     * Default banks name for automatic generation.
     *
     * @since 6.0.0
     * @return array
     */
    public function MPT_banks_name_auto() {
        $list_api_auto = array(
            esc_html__( 'Bing Images', 'magic-post-thumbnail' )            => array('bing', true),
            esc_html__( 'First image in content', 'magic-post-thumbnail' ) => array('content', true),
            esc_html__( 'Default image', 'magic-post-thumbnail' )          => array('fallback', true),
            esc_html__( 'Google Image (API)', 'magic-post-thumbnail' )     => array('google_image', true),
            esc_html__( 'GPT Image', 'magic-post-thumbnail' )              => array('gptimage', true),
            esc_html__( 'Openverse', 'magic-post-thumbnail' )              => array('openverse', true),
            esc_html__( 'Flickr', 'magic-post-thumbnail' )                 => array('flickr', true),
            esc_html__( 'Pixabay', 'magic-post-thumbnail' )                => array('pixabay', true),
            esc_html__( 'Youtube', 'magic-post-thumbnail' )                => array('youtube', false),
            esc_html__( 'Unsplash', 'magic-post-thumbnail' )               => array('unsplash', false),
            esc_html__( 'Pexels', 'magic-post-thumbnail' )                 => array('pexels', false),
            esc_html__( 'Stable Diffusion', 'magic-post-thumbnail' )       => array('stability', false),
            esc_html__( 'Replicate', 'magic-post-thumbnail' )              => array('replicate', false),
            esc_html__( 'Ideogram', 'magic-post-thumbnail' )               => array('ideogram', false),
            esc_html__( 'Flux 2', 'magic-post-thumbnail' )                 => array('flux2', false),
            esc_html__( 'Gemini (Nano Banana)', 'magic-post-thumbnail' )   => array('gemini', false),
            esc_html__( 'Grok', 'magic-post-thumbnail' )                   => array('grok', false),
        );
        return $list_api_auto;
    }

    /**
     * Photo banks available as Gutenberg block search tabs.
     *
     * @return array Label => array( slug, enabled )
     */
    public function MPT_banks_name_manual() {
        $list_api_manual = array(
            esc_html__( 'Openverse', 'magic-post-thumbnail' ) => array('openverse', true),
            esc_html__( 'Flickr', 'magic-post-thumbnail' )    => array('flickr', true),
            esc_html__( 'Pixabay', 'magic-post-thumbnail' )   => array('pixabay', true),
            esc_html__( 'Youtube', 'magic-post-thumbnail' )   => array('youtube', false),
            esc_html__( 'Unsplash', 'magic-post-thumbnail' )  => array('unsplash', false),
            esc_html__( 'Pexels', 'magic-post-thumbnail' )    => array('pexels', false),
        );
        return $list_api_manual;
    }

    /**
     * Default public post types and categories.
     *
     * @since 5.0.0
     * @return array
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
        $post_types_default['choosed_categories'] = array();
        $categories_default = get_terms( array(
            'taxonomy'   => 'category',
            'hide_empty' => false,
        ) );
        if ( !is_wp_error( $categories_default ) && is_array( $categories_default ) ) {
            foreach ( $categories_default as $category ) {
                $post_types_default['choosed_categories'][$category->slug] = $category->name;
            }
        }
        return $post_types_default;
    }

    /**
     * Default main settings.
     *
     * @since 4.0.0
     * @param bool $never_set Unused, kept for BC.
     * @return array
     */
    public function MPT_default_options_main_settings( $never_set = false ) {
        return array(
            'image_block'              => array(
                1 => array(
                    'image_location'  => 'featured',
                    'based_on'        => 'title',
                    'translation_EN'  => '',
                    'title_selection' => 'full_title',
                    'selected_image'  => 'first_result',
                    'api_chosen'      => 'default',
                    'ai_style'        => '',
                    'ai_style_custom' => '',
                    'ai_no_text'      => '',
                ),
            ),
            'image_filename'           => 'title',
            'rewrite_featured'         => '',
            'image_reuse'              => '',
            'image_flip'               => '',
            'image_crop'               => '',
            'image_overlay'            => '',
            'image_overlay_logo'       => 0,
            'image_overlay_bg'         => '#111111',
            'image_overlay_opacity'    => 55,
            'image_overlay_text'       => '#ffffff',
            'image_overlay_category'   => '',
            'bulk_generation_interval' => 0,
        );
    }

    /**
     * Default banks settings.
     *
     * @since 4.0.0
     * @param bool $never_set Unused, kept for BC.
     * @return array
     */
    public function MPT_default_options_banks_settings( $never_set = false ) {
        $options_banks = get_option( 'MPT_plugin_banks_settings' );
        if ( isset( $options_banks['api_chosen'] ) && !isset( $options_banks['api_chosen_auto'] ) ) {
            $ar_bank_auto = array($options_banks['api_chosen']);
        } else {
            $ar_bank_auto = array('bing', 'openverse');
        }
        $default_options = array(
            'api_chosen_manual' => array('openverse'),
            'api_chosen_auto'   => $ar_bank_auto,
            'bing'              => array(
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
            'gptimage'          => array(
                'apikey'        => '',
                'imgsize'       => '1024x1024',
                'quality'       => 'medium',
                'output_format' => 'png',
                'moderation'    => 'auto',
                'convert_jpg'   => '',
            ),
            'ideogram'          => array(
                'apikey' => '',
                'ratio'  => '16:9',
                'speed'  => 'DEFAULT',
            ),
            'flux2'             => array(
                'apikey' => '',
                'model'  => 'flux-2-pro',
                'ratio'  => '16:9',
            ),
            'gemini'            => array(
                'apikey' => '',
                'model'  => 'gemini-3.1-flash-image',
                'ratio'  => '16:9',
            ),
            'grok'              => array(
                'apikey' => '',
                'ratio'  => '16:9',
            ),
            'stability'         => array(
                'apikey'        => '',
                'model'         => 'core',
                'aspect_ratio'  => '16:9',
                'output_format' => 'jpeg',
                'style_preset'  => 'photographic',
            ),
            'replicate'         => array(
                'apikey'        => '',
                'model'         => 'black-forest-labs/flux-schnell',
                'output_format' => 'webp',
                'aspect_ratio'  => '16:9',
                'quality'       => 'auto',
                'moderation'    => 'auto',
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
            'fallback'          => array(
                'attachment_id' => 0,
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
     * Banks that generate an image (not a search result list).
     *
     * @since 7.0.0
     * @return array
     */
    public function MPT_ai_bank_slugs() {
        return array(
            'gptimage',
            'stability',
            'replicate',
            'ideogram',
            'flux2',
            'gemini',
            'grok'
        );
    }

    /**
     * Photo / search banks (as opposed to generative AI).
     *
     * @since 7.1.0
     * @return array
     */
    public function MPT_photo_bank_slugs() {
        return array(
            'bing',
            'google_image',
            'openverse',
            'flickr',
            'pixabay',
            'youtube',
            'unsplash',
            'pexels'
        );
    }

    /**
     * Local banks: no remote API, pixels come from the site.
     *
     * @since 7.0.0
     * @return array
     */
    public function MPT_local_bank_slugs() {
        return array('content', 'fallback');
    }

    /**
     * AI style presets (prompt suffixes). Empty key keeps the historical default.
     *
     * @since 7.0.0
     * @return array slug => array( label, suffix )
     */
    public function MPT_ai_style_presets() {
        return array(
            ''             => array(
                'label'  => __( 'Default', 'magic-post-thumbnail' ),
                'suffix' => '',
            ),
            'photo'        => array(
                'label'  => __( 'Photorealistic', 'magic-post-thumbnail' ),
                'suffix' => 'photorealistic photography, natural lighting, high detail',
            ),
            'illustration' => array(
                'label'  => __( 'Illustration', 'magic-post-thumbnail' ),
                'suffix' => 'digital illustration, clean shapes, vibrant colors',
            ),
            'painting'     => array(
                'label'  => __( 'Oil painting', 'magic-post-thumbnail' ),
                'suffix' => 'oil painting, visible brush strokes, rich texture',
            ),
            'watercolor'   => array(
                'label'  => __( 'Watercolor', 'magic-post-thumbnail' ),
                'suffix' => 'watercolor painting, soft edges, paper texture',
            ),
            'cinematic'    => array(
                'label'  => __( 'Cinematic', 'magic-post-thumbnail' ),
                'suffix' => 'cinematic still, dramatic lighting, shallow depth of field',
            ),
            'render3d'     => array(
                'label'  => __( '3D render', 'magic-post-thumbnail' ),
                'suffix' => '3D render, octane, studio lighting',
            ),
            'minimal'      => array(
                'label'  => __( 'Minimal', 'magic-post-thumbnail' ),
                'suffix' => 'minimalist composition, simple shapes, lots of negative space',
            ),
            'anime'        => array(
                'label'  => __( 'Anime', 'magic-post-thumbnail' ),
                'suffix' => 'anime style, clean line art',
            ),
            'custom'       => array(
                'label'  => __( 'Custom', 'magic-post-thumbnail' ),
                'suffix' => '',
            ),
        );
    }

    /**
     * Build the prompt sent to generative AI banks.
     *
     * @since 7.0.0
     * @param string $search    Keyword from "Search based on".
     * @param array  $img_block Image block settings.
     * @return string
     */
    public function MPT_build_ai_prompt( $search, $img_block ) {
        $search = trim( (string) $search );
        $style = ( isset( $img_block['ai_style'] ) ? sanitize_key( $img_block['ai_style'] ) : '' );
        $presets = $this->MPT_ai_style_presets();
        if ( !isset( $presets[$style] ) ) {
            $style = '';
        }
        $suffix = '';
        if ( 'custom' === $style ) {
            $suffix = ( isset( $img_block['ai_style_custom'] ) ? sanitize_text_field( $img_block['ai_style_custom'] ) : '' );
        } elseif ( '' !== $style ) {
            $suffix = $presets[$style]['suffix'];
        }
        if ( '' === $style ) {
            $prompt = 'Photorealistic image of ' . $search;
        } else {
            $prompt = $search;
            if ( '' !== $suffix ) {
                $prompt .= ', ' . $suffix;
            }
        }
        if ( !empty( $img_block['ai_no_text'] ) && 'true' === (string) $img_block['ai_no_text'] ) {
            $prompt .= ', no text, no letters, no watermark, no caption, no typography';
        }
        return $prompt;
    }

    /**
     * Normalize a bank slug (legacy aliases included).
     *
     * @since 7.1.0
     * @param string $slug Raw slug.
     * @return string
     */
    public function MPT_normalize_bank_slug( $slug ) {
        $slug = sanitize_key( (string) $slug );
        if ( 'cc_search' === $slug ) {
            return 'openverse';
        }
        if ( 'google_scraping' === $slug ) {
            return 'bing';
        }
        if ( 'dallev1' === $slug || 'dalle' === $slug ) {
            return 'gptimage';
        }
        return $slug;
    }

    /**
     * Settings PHP file slug for a bank (Openverse still lives in cc_search.php).
     *
     * @since 7.1.0
     * @param string $slug Bank slug.
     * @return string
     */
    public function MPT_bank_settings_file( $slug ) {
        $slug = $this->MPT_normalize_bank_slug( $slug );
        if ( 'openverse' === $slug ) {
            return 'cc_search';
        }
        return $slug;
    }

    /**
     * Option-array key used to store a bank's credentials.
     *
     * @since 7.1.0
     * @param string $slug Bank slug.
     * @return string
     */
    public function MPT_bank_option_key( $slug ) {
        $slug = $this->MPT_normalize_bank_slug( $slug );
        if ( 'google_image' === $slug ) {
            return 'googleimage';
        }
        if ( 'openverse' === $slug ) {
            return 'cc_search';
        }
        return $slug;
    }

    /**
     * Translated label for a bank slug.
     *
     * @since 7.1.0
     * @param string $slug Bank slug.
     * @return string
     */
    public function MPT_bank_label( $slug ) {
        $slug = $this->MPT_normalize_bank_slug( $slug );
        foreach ( $this->MPT_banks_name_auto() as $label => $data ) {
            if ( isset( $data[0] ) && $data[0] === $slug ) {
                return $label;
            }
        }
        return $slug;
    }

    /**
     * Resolve the ordered fallback chain for one image block.
     *
     * 'default' (or empty) uses the global Source chain. Any other slug is an
     * override, followed by a legacy api_chosen_2 then the rest of the global chain.
     *
     * @since 7.1.0
     * @param array      $img_block     Image block settings.
     * @param array|null $options_banks Banks settings. Loaded if null.
     * @return array Ordered unique slugs.
     */
    public function MPT_resolve_bank_chain( $img_block, $options_banks = null ) {
        if ( !is_array( $img_block ) ) {
            $img_block = array();
        }
        if ( null === $options_banks ) {
            $options_banks = wp_parse_args( get_option( 'MPT_plugin_banks_settings' ), $this->MPT_default_options_banks_settings( true ) );
        }
        $global = array();
        if ( !empty( $options_banks['api_chosen_auto'] ) && is_array( $options_banks['api_chosen_auto'] ) ) {
            foreach ( $options_banks['api_chosen_auto'] as $slug ) {
                $slug = $this->MPT_normalize_bank_slug( ( is_string( $slug ) ? $slug : '' ) );
                if ( '' !== $slug && 'default' !== $slug ) {
                    $global[] = $slug;
                }
            }
        }
        $override = ( isset( $img_block['api_chosen'] ) ? $this->MPT_normalize_bank_slug( $img_block['api_chosen'] ) : '' );
        if ( empty( $override ) || 'default' === $override ) {
            $chain = $global;
        } else {
            $chain = array($override);
            if ( !empty( $img_block['api_chosen_2'] ) && 'none' !== $img_block['api_chosen_2'] ) {
                $second = $this->MPT_normalize_bank_slug( $img_block['api_chosen_2'] );
                if ( '' !== $second && 'default' !== $second && $second !== $override ) {
                    $chain[] = $second;
                }
            }
            foreach ( $global as $slug ) {
                if ( !in_array( $slug, $chain, true ) ) {
                    $chain[] = $slug;
                }
            }
        }
        $chain = array_values( array_unique( array_filter( $chain ) ) );
        if ( empty( $chain ) ) {
            $chain = array('bing');
        }
        return $chain;
    }

    /**
     * Human-readable global chain, e.g. "Bing Images → GPT Image".
     *
     * @since 7.1.0
     * @param array|null $options_banks Banks settings.
     * @return string
     */
    public function MPT_format_bank_chain_labels( $options_banks = null ) {
        $chain = $this->MPT_resolve_bank_chain( array(
            'api_chosen' => 'default',
        ), $options_banks );
        $labels = array();
        foreach ( $chain as $slug ) {
            $labels[] = $this->MPT_bank_label( $slug );
        }
        return implode( ' → ', $labels );
    }

    /**
     * Whether a bank typically bills per generated image.
     *
     * @since 7.1.0
     * @param string $slug Bank slug.
     * @return bool
     */
    public function MPT_bank_is_paid( $slug ) {
        return in_array( $this->MPT_normalize_bank_slug( $slug ), $this->MPT_ai_bank_slugs(), true );
    }

    /**
     * Stored API-key test status: ready|missing|untested|valid|invalid.
     *
     * @since 7.1.0
     * @param string     $slug          Bank slug.
     * @param array|null $options_banks Banks settings.
     * @return string
     */
    public function MPT_bank_key_status( $slug, $options_banks = null ) {
        $slug = $this->MPT_normalize_bank_slug( $slug );
        $needs_key = array(
            'google_image',
            'gptimage',
            'pixabay',
            'youtube',
            'unsplash',
            'pexels',
            'stability',
            'replicate',
            'ideogram',
            'flux2',
            'gemini',
            'grok'
        );
        if ( !in_array( $slug, $needs_key, true ) ) {
            return 'ready';
        }
        if ( null === $options_banks ) {
            $options_banks = wp_parse_args( get_option( 'MPT_plugin_banks_settings' ), $this->MPT_default_options_banks_settings( true ) );
        }
        $opt_key = $this->MPT_bank_option_key( $slug );
        $has_key = !empty( $options_banks[$opt_key]['apikey'] );
        if ( 'google_image' === $slug ) {
            $has_key = $has_key && !empty( $options_banks[$opt_key]['cxid'] );
        }
        if ( !$has_key ) {
            return 'missing';
        }
        $stored = get_option( 'mpt_bank_key_status', array() );
        if ( isset( $stored[$slug]['status'] ) && in_array( $stored[$slug]['status'], array('valid', 'invalid'), true ) ) {
            return $stored[$slug]['status'];
        }
        return 'untested';
    }

    /**
     * Persist an API-key test result.
     *
     * @since 7.1.0
     * @param string $slug   Bank slug.
     * @param string $status valid|invalid.
     */
    public function MPT_store_bank_key_status( $slug, $status ) {
        $slug = $this->MPT_normalize_bank_slug( $slug );
        $all = get_option( 'mpt_bank_key_status', array() );
        if ( !is_array( $all ) ) {
            $all = array();
        }
        $all[$slug] = array(
            'status'    => ( 'valid' === $status ? 'valid' : 'invalid' ),
            'tested_at' => time(),
        );
        update_option( 'mpt_bank_key_status', $all, false );
    }

    /**
     * Admin URL for an MPT settings module.
     *
     * @since 7.0.0
     * @param string $module Module slug.
     * @return string
     */
    public function MPT_admin_module_url( $module ) {
        $args = array(
            'page' => 'magic-post-thumbnail-admin-display',
        );
        if ( '' !== $module && 'dashboard' !== $module ) {
            $args['module'] = $module;
        }
        return add_query_arg( $args, admin_url( 'admin.php' ) );
    }

    /**
     * Banks saved in the Source chain (no silent Bing fallback).
     *
     * @since 7.0.0
     * @param array|null $options_banks Banks settings.
     * @return array
     */
    public function MPT_saved_auto_chain( $options_banks = null ) {
        if ( null === $options_banks ) {
            $options_banks = wp_parse_args( get_option( 'MPT_plugin_banks_settings' ), $this->MPT_default_options_banks_settings( true ) );
        }
        $chain = array();
        if ( empty( $options_banks['api_chosen_auto'] ) || !is_array( $options_banks['api_chosen_auto'] ) ) {
            return $chain;
        }
        foreach ( $options_banks['api_chosen_auto'] as $slug ) {
            $slug = $this->MPT_normalize_bank_slug( ( is_string( $slug ) ? $slug : '' ) );
            if ( '' !== $slug && 'default' !== $slug && !in_array( $slug, $chain, true ) ) {
                $chain[] = $slug;
            }
        }
        return $chain;
    }

    /**
     * Whether a bank is enabled on the current plan.
     *
     * @since 7.0.0
     * @param string $slug Bank slug.
     * @return bool
     */
    public function MPT_bank_enabled_for_plan( $slug ) {
        $slug = $this->MPT_normalize_bank_slug( $slug );
        foreach ( $this->MPT_banks_name_auto() as $data ) {
            if ( isset( $data[0] ) && $data[0] === $slug ) {
                return !empty( $data[1] );
            }
        }
        return false;
    }

    /**
     * Dashboard health checks: chain, keys, placement, overlay.
     *
     * @since 7.0.0
     * @return array{overall:string,checks:array,can_test:bool,test_is_ai:bool}
     */
    public function MPT_config_health() {
        $banks = wp_parse_args( get_option( 'MPT_plugin_banks_settings' ), $this->MPT_default_options_banks_settings( true ) );
        $main = wp_parse_args( get_option( 'MPT_plugin_main_settings' ), $this->MPT_default_options_main_settings( true ) );
        $chain = $this->MPT_saved_auto_chain( $banks );
        $checks = array();
        $source_url = $this->MPT_admin_module_url( 'source' );
        $automatic_url = $this->MPT_admin_module_url( 'automatic' );
        $configure = __( 'Configure', 'magic-post-thumbnail' );
        if ( empty( $chain ) ) {
            $checks[] = array(
                'id'         => 'chain',
                'status'     => 'error',
                'label'      => __( 'Automatic bank chain', 'magic-post-thumbnail' ),
                'text'       => __( 'No bank selected. Add at least one bank below, otherwise no image can be generated.', 'magic-post-thumbnail' ),
                'link'       => $source_url,
                'link_label' => $configure,
            );
        } else {
            $labels = array();
            foreach ( $chain as $slug ) {
                $labels[] = $this->MPT_bank_label( $slug );
            }
            $checks[] = array(
                'id'         => 'chain',
                'status'     => 'ok',
                'label'      => __( 'Automatic bank chain', 'magic-post-thumbnail' ),
                'text'       => implode( ' → ', $labels ),
                'link'       => $source_url,
                'link_label' => $configure,
            );
        }
        $key_status = 'ok';
        $key_bits = array();
        $first = ( !empty( $chain ) ? $chain[0] : '' );
        if ( !empty( $chain ) ) {
            foreach ( $chain as $i => $slug ) {
                $is_first = 0 === $i;
                if ( !$this->MPT_bank_enabled_for_plan( $slug ) ) {
                    $key_bits[] = sprintf( 
                        /* translators: %s: bank name */
                        __( '%s requires the Pro version', 'magic-post-thumbnail' ),
                        $this->MPT_bank_label( $slug )
                     );
                    $key_status = 'error';
                    continue;
                }
                if ( 'fallback' === $slug ) {
                    $aid = ( !empty( $banks['fallback']['attachment_id'] ) ? (int) $banks['fallback']['attachment_id'] : 0 );
                    if ( $aid < 1 || !wp_attachment_is_image( $aid ) ) {
                        $key_bits[] = __( 'Choose a default image in the settings on the left.', 'magic-post-thumbnail' );
                        $key_status = ( $is_first ? 'error' : (( 'error' === $key_status ? 'error' : 'warn' )) );
                    }
                    continue;
                }
                $st = $this->MPT_bank_key_status( $slug, $banks );
                if ( 'ready' === $st || 'valid' === $st ) {
                    continue;
                }
                if ( 'missing' === $st ) {
                    $key_bits[] = sprintf( 
                        /* translators: %s: bank name */
                        __( '%s: no API key', 'magic-post-thumbnail' ),
                        $this->MPT_bank_label( $slug )
                     );
                    if ( $is_first ) {
                        $key_status = 'error';
                    } elseif ( 'error' !== $key_status ) {
                        $key_status = 'warn';
                    }
                } elseif ( 'invalid' === $st ) {
                    $key_bits[] = sprintf( 
                        /* translators: %s: bank name */
                        __( '%s: key failed', 'magic-post-thumbnail' ),
                        $this->MPT_bank_label( $slug )
                     );
                    $key_status = 'error';
                } elseif ( 'untested' === $st ) {
                    $key_bits[] = sprintf( 
                        /* translators: %s: bank name */
                        __( '%s: key not tested yet', 'magic-post-thumbnail' ),
                        $this->MPT_bank_label( $slug )
                     );
                    if ( 'error' !== $key_status ) {
                        $key_status = 'warn';
                    }
                }
            }
        }
        if ( empty( $chain ) ) {
            $key_text = __( 'Add a bank to the chain first.', 'magic-post-thumbnail' );
            $key_status = 'error';
        } elseif ( empty( $key_bits ) ) {
            $key_text = __( 'All banks in the chain are ready.', 'magic-post-thumbnail' );
        } else {
            $key_text = implode( ' · ', $key_bits );
        }
        $checks[] = array(
            'id'         => 'keys',
            'status'     => $key_status,
            'label'      => __( 'API keys', 'magic-post-thumbnail' ),
            'text'       => $key_text,
            'link'       => $source_url,
            'link_label' => $configure,
        );
        $blocks = ( !empty( $main['image_block'] ) && is_array( $main['image_block'] ) ? $main['image_block'] : array() );
        $places = array();
        foreach ( $blocks as $block ) {
            if ( empty( $block['image_location'] ) ) {
                continue;
            }
            if ( 'featured' === $block['image_location'] ) {
                $places[] = __( 'Featured Image', 'magic-post-thumbnail' );
            } elseif ( 'custom' === $block['image_location'] ) {
                $places[] = __( 'Inline content', 'magic-post-thumbnail' );
            } else {
                $places[] = $block['image_location'];
            }
        }
        $places = array_unique( $places );
        if ( empty( $places ) ) {
            $checks[] = array(
                'id'         => 'placement',
                'status'     => 'error',
                'label'      => __( 'Image placement', 'magic-post-thumbnail' ),
                'text'       => __( 'No image placement configured.', 'magic-post-thumbnail' ),
                'link'       => $automatic_url,
                'link_label' => $configure,
            );
        } else {
            $checks[] = array(
                'id'         => 'placement',
                'status'     => 'ok',
                'label'      => __( 'Image placement', 'magic-post-thumbnail' ),
                'text'       => implode( ', ', $places ),
                'link'       => $automatic_url,
                'link_label' => $configure,
            );
        }
        $overlay_on = $this->MPT_overlay_enabled( $main );
        if ( $overlay_on ) {
            $overlay_status = 'ok';
            $overlay_text = __( 'Title bar will be drawn on generated images.', 'magic-post-thumbnail' );
            if ( !function_exists( 'imagecreatefromstring' ) ) {
                $overlay_status = 'error';
                $overlay_text = __( 'PHP GD is missing; the overlay cannot be drawn.', 'magic-post-thumbnail' );
            } elseif ( !function_exists( 'imagettftext' ) || '' === $this->MPT_overlay_font_path() ) {
                $overlay_status = 'warn';
                $overlay_text = __( 'Overlay is on but this server cannot draw TrueType text. Accents may break.', 'magic-post-thumbnail' );
            }
            $checks[] = array(
                'id'         => 'overlay',
                'status'     => $overlay_status,
                'label'      => __( 'Brand overlay', 'magic-post-thumbnail' ),
                'text'       => $overlay_text,
                'link'       => $automatic_url,
                'link_label' => $configure,
            );
        } else {
            $checks[] = array(
                'id'         => 'overlay',
                'status'     => 'ok',
                'label'      => __( 'Brand overlay', 'magic-post-thumbnail' ),
                'text'       => __( 'Disabled', 'magic-post-thumbnail' ),
                'link'       => $automatic_url,
                'link_label' => $configure,
            );
        }
        $overall = 'ok';
        foreach ( $checks as $check ) {
            if ( 'error' === $check['status'] ) {
                $overall = 'error';
                break;
            }
            if ( 'warn' === $check['status'] ) {
                $overall = 'warn';
            }
        }
        $can_test = !empty( $chain ) && $this->MPT_bank_enabled_for_plan( $first );
        if ( $can_test && in_array( $this->MPT_bank_key_status( $first, $banks ), array('missing', 'invalid'), true ) ) {
            $can_test = false;
        }
        if ( $can_test && 'fallback' === $first ) {
            $aid = ( !empty( $banks['fallback']['attachment_id'] ) ? (int) $banks['fallback']['attachment_id'] : 0 );
            $can_test = $aid > 0 && wp_attachment_is_image( $aid );
        }
        return array(
            'overall'    => $overall,
            'checks'     => $checks,
            'can_test'   => $can_test,
            'test_is_ai' => '' !== $first && $this->MPT_bank_is_paid( $first ),
        );
    }

    /**
     * Black Friday promo banner (free build, before expiry).
     *
     * @since 7.1.0
     */
    public function MPT_promo_black_friday_html() {
        if ( (int) current_time( 'U' ) >= 1764543599 ) {
            return;
        }
        $pricing = admin_url( 'admin.php?page=magic-post-thumbnail-admin-display-pricing' );
        ?>
		<div class="alert alert-custom alert-default" role="alert">
			<div class="alert-icon"><span class="svg-icon svg-icon-primary svg-icon-xl"><svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
				<g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
				<rect x="0" y="0" width="24" height="24"></rect>
				<path d="M7.07744993,12.3040451 C7.72444571,13.0716094 8.54044565,13.6920474 9.46808594,14.1079953 L5,23 L4.5,18 L7.07744993,12.3040451 Z M14.5865511,14.2597864 C15.5319561,13.9019016 16.375416,13.3366121 17.0614026,12.6194459 L19.5,18 L19,23 L14.5865511,14.2597864 Z M12,3.55271368e-14 C12.8284271,3.53749572e-14 13.5,0.671572875 13.5,1.5 L13.5,4 L10.5,4 L10.5,1.5 C10.5,0.671572875 11.1715729,3.56793164e-14 12,3.55271368e-14 Z" fill="#000000" opacity="0.3"></path>
				<path d="M12,10 C13.1045695,10 14,9.1045695 14,8 C14,6.8954305 13.1045695,6 12,6 C10.8954305,6 10,6.8954305 10,8 C10,9.1045695 10.8954305,10 12,10 Z M12,13 C9.23857625,13 7,10.7614237 7,8 C7,5.23857625 9.23857625,3 12,3 C14.7614237,3 17,5.23857625 17,8 C17,10.7614237 14.7614237,13 12,13 Z" fill="#000000" fill-rule="nonzero"></path>
				</g>
			</svg></span>
			</div>
			<div class="alert-text">
				<?php 
        echo wp_kses( sprintf( 
            /* translators: 1: pricing admin URL, 2: coupon code */
            __( 'Get a <strong>30%% discount for BLACK FRIDAY until November 30</strong> when you upgrade to the <a href="%1$s">Pro version</a> with the code: <strong>%2$s</strong>', 'magic-post-thumbnail' ),
            esc_url( $pricing ),
            'MPTBLACKFRIDAY25'
         ), array(
            'strong' => array(),
            'a'      => array(
                'href' => array(),
            ),
        ) );
        ?>
			</div>
		</div>
		<?php 
    }

    /**
     * Banks that return a base64 payload instead of a downloadable URL.
     *
     * @since 7.0.0
     * @return array
     */
    public function MPT_b64_bank_slugs() {
        return array('gptimage', 'stability', 'gemini');
    }

    /**
     * Default compatibility settings.
     *
     * @since 4.0.0
     * @param bool $never_set Unused, kept for BC.
     * @return array
     */
    public function MPT_default_options_compatibility_settings( $never_set = false ) {
        return array(
            'enable_FIFU' => false,
        );
    }

    /**
     * Default Gutenberg block settings.
     *
     * @since 4.0.0
     * @param bool $never_set Unused, kept for BC.
     * @return array
     */
    public function MPT_default_options_block_settings( $never_set = false ) {
        return array(
            'enable_manual_search' => true,
        );
    }

    /**
     * Default cron settings.
     *
     * @since 4.0.0
     * @param bool $never_set Unused, kept for BC.
     * @return array
     */
    public function MPT_default_options_cron_settings( $never_set = false ) {
        return array(
            'enable_cron' => 'disable',
        );
    }

    /**
     * Default rights settings.
     *
     * @since 5.2.11
     * @param bool $never_set Unused, kept for BC.
     * @return array
     */
    public function MPT_default_options_rights_settings( $never_set = false ) {
        return array(
            'rights_editor'      => '',
            'rights_author'      => '',
            'rights_contributor' => '',
            'rights_subscriber'  => '',
        );
    }

    /**
     * Default proxy settings.
     *
     * @since 4.0.0
     * @param bool $never_set Unused, kept for BC.
     * @return array
     */
    public function MPT_default_options_proxy_settings( $never_set = false ) {
        return array(
            'enable_proxy' => 'disable',
        );
    }

    /**
     * Default logs settings.
     *
     * @since 4.0.0
     * @param bool $never_set Unused, kept for BC.
     * @return array
     */
    public function MPT_default_options_logs_settings( $never_set = false ) {
        return array(
            'logs' => '',
        );
    }

    /**
     * Trusted automated sources that skip the manual capability check.
     *
     * @return array
     */
    public function MPT_trusted_sources() {
        return array(
            'cron',
            'rest',
            'wp_insert_post',
            'save_post',
            'wpematico',
            'feedwordpress',
            'wp_automatic',
            'wp_all_import'
        );
    }

    /**
     * Whether the current request is a human saving a post in wp-admin.
     *
     * REST, cron, CLI, XML-RPC and importers are excluded so those paths
     * keep generating without requiring mpt_manage.
     *
     * @return bool
     */
    public function MPT_is_interactive_admin_save() {
        if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
            return false;
        }
        if ( function_exists( 'wp_doing_cron' ) && wp_doing_cron() ) {
            return false;
        }
        if ( defined( 'DOING_CRON' ) && DOING_CRON ) {
            return false;
        }
        if ( defined( 'WP_CLI' ) && WP_CLI ) {
            return false;
        }
        if ( defined( 'XMLRPC_REQUEST' ) && XMLRPC_REQUEST ) {
            return false;
        }
        if ( defined( 'WP_IMPORTING' ) && WP_IMPORTING ) {
            return false;
        }
        if ( !is_admin() ) {
            return false;
        }
        global $pagenow;
        return in_array( $pagenow, array('post.php', 'post-new.php'), true );
    }

    /**
     * Whether the current user may run generation for this source.
     *
     * Manual actions need mpt_manage. REST / cron / imports stay trusted and
     * do not require that capability. Classic editor save_post does, so a
     * contributor cannot burn API credits from the post screen.
     *
     * @param int    $post_id Post ID.
     * @param string $source  Generation source.
     * @return bool
     */
    public function MPT_generation_user_is_allowed( $post_id, $source ) {
        $post_id = absint( $post_id );
        $source = ( is_string( $source ) ? $source : 'manual' );
        if ( 'manual' === $source ) {
            return current_user_can( 'mpt_manage' ) && current_user_can( 'edit_post', $post_id );
        }
        if ( 'save_post' === $source && $this->MPT_is_interactive_admin_save() ) {
            return current_user_can( 'mpt_manage' ) && current_user_can( 'edit_post', $post_id );
        }
        return in_array( $source, $this->MPT_trusted_sources(), true );
    }

    /**
     * Gutenberg block banks (Source → Gutenberg list).
     *
     * @return array
     */
    public function MPT_gutenberg_allowed_banks() {
        $banks = wp_parse_args( get_option( 'MPT_plugin_banks_settings' ), $this->MPT_default_options_banks_settings( true ) );
        $manual = ( isset( $banks['api_chosen_manual'] ) && is_array( $banks['api_chosen_manual'] ) ? $banks['api_chosen_manual'] : array() );
        $out = array();
        foreach ( $manual as $k => $v ) {
            $slug = $this->MPT_normalize_bank_slug( ( is_string( $v ) && '' !== $v ? $v : $k ) );
            if ( '' !== $slug && !in_array( $slug, $out, true ) ) {
                $out[] = $slug;
            }
        }
        return $out;
    }

    /**
     * Whether a Gutenberg search/download bank is in the configured list.
     *
     * @param string $bank Bank slug (cc_search accepted as openverse).
     * @return bool
     */
    public function MPT_is_gutenberg_bank_allowed( $bank ) {
        $bank = $this->MPT_normalize_bank_slug( $bank );
        if ( '' === $bank ) {
            return false;
        }
        return in_array( $bank, $this->MPT_gutenberg_allowed_banks(), true );
    }

    /**
     * Writable logs directory.
     *
     * Optional MPT_LOG_DIR for advanced setups. Default: random folder under uploads.
     *
     * @since 7.0.0
     * @return string
     */
    public function MPT_get_log_dir() {
        if ( defined( 'MPT_LOG_DIR' ) && MPT_LOG_DIR ) {
            $dir = trailingslashit( MPT_LOG_DIR );
        } else {
            $secret = get_option( 'mpt_logs_dirname' );
            if ( !is_string( $secret ) || '' === $secret ) {
                $secret = 'mpt-logs-' . wp_generate_password( 12, false, false );
                update_option( 'mpt_logs_dirname', $secret, false );
            }
            $uploads = wp_upload_dir();
            $base = ( !empty( $uploads['basedir'] ) ? $uploads['basedir'] : WP_CONTENT_DIR . '/uploads' );
            $dir = trailingslashit( $base ) . 'magic-post-thumbnail/' . $secret . '/';
        }
        if ( !is_dir( $dir ) ) {
            wp_mkdir_p( $dir );
        }
        $this->MPT_protect_log_dir( $dir );
        return $dir;
    }

    /**
     * Drop index.php / .htaccess / web.config into the log directory.
     *
     * @param string $dir Directory path.
     */
    protected function MPT_protect_log_dir( $dir ) {
        $index = $dir . 'index.php';
        if ( !file_exists( $index ) ) {
            @file_put_contents( $index, "<?php\n// Silence is golden.\n" );
        }
        $htaccess = $dir . '.htaccess';
        if ( !file_exists( $htaccess ) ) {
            @file_put_contents( $htaccess, "Require all denied\n<IfModule mod_authz_core.c>\nRequire all denied\n</IfModule>\n<IfModule !mod_authz_core.c>\nDeny from all\n</IfModule>\n" );
        }
        $webconfig = $dir . 'web.config';
        if ( !file_exists( $webconfig ) ) {
            @file_put_contents( $webconfig, "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<configuration>\n  <system.webServer>\n    <authorization>\n      <deny users=\"*\" />\n    </authorization>\n  </system.webServer>\n</configuration>\n" );
        }
    }

    /**
     * Whether a filename is an MPT log file (not index.php / .htaccess).
     *
     * @param string $filename Basename.
     * @return bool
     */
    protected function MPT_is_plugin_log_file( $filename ) {
        if ( 'index.php' === $filename ) {
            return false;
        }
        if ( 0 === strpos( $filename, 'mpt-' ) && '.php' === substr( $filename, -4 ) ) {
            return true;
        }
        return (bool) preg_match( '/\\.log$/', $filename );
    }

    /**
     * Seed a PHP log file so a direct HTTP hit exits immediately.
     *
     * @param string $path Absolute path.
     */
    protected function MPT_ensure_log_file_guard( $path ) {
        if ( file_exists( $path ) ) {
            return;
        }
        @file_put_contents( $path, "<?php exit; ?>\n" );
    }

    /**
     * Log contents without the PHP guard line.
     *
     * @param string $path Absolute path.
     * @return string
     */
    public function MPT_log_readable_contents( $path ) {
        $contents = (string) file_get_contents( $path );
        if ( 0 === strpos( $contents, "<?php exit; ?>\n" ) ) {
            return (string) substr( $contents, strlen( "<?php exit; ?>\n" ) );
        }
        if ( 0 === strpos( $contents, '<?php exit; ?>' ) ) {
            return ltrim( (string) substr( $contents, strlen( '<?php exit; ?>' ) ) );
        }
        return $contents;
    }

    /**
     * Current log filename (not the full path).
     *
     * @since 4.0.0
     * @param bool $check Return false when no file exists.
     * @return string|false
     */
    public function MPT_log_file( $check = false ) {
        $dir = $this->MPT_get_log_dir();
        $files = @scandir( $dir );
        $result = '';
        if ( !empty( $files ) ) {
            foreach ( $files as $value ) {
                if ( in_array( $value, array('.', '..'), true ) ) {
                    continue;
                }
                if ( is_dir( $dir . $value ) ) {
                    continue;
                }
                if ( $this->MPT_is_plugin_log_file( $value ) ) {
                    $result = $value;
                }
            }
        }
        if ( true === $check && empty( $result ) ) {
            return false;
        }
        if ( empty( $result ) ) {
            $result = 'mpt-' . wp_generate_password( 14, false, false ) . '.php';
            $this->MPT_ensure_log_file_guard( $dir . $result );
        }
        return $result;
    }

    /**
     * Absolute path of the current log file.
     *
     * @param bool $check Return false when no file exists.
     * @return string|false
     */
    public function MPT_log_file_path( $check = false ) {
        $file = $this->MPT_log_file( $check );
        if ( false === $file ) {
            return false;
        }
        return $this->MPT_get_log_dir() . $file;
    }

    /**
     * Monolog or no-op logger.
     *
     * @since 4.0.0
     * @return object
     */
    public function MPT_monolog_call() {
        $main_settings = get_option( 'MPT_plugin_logs_settings' );
        if ( !empty( $main_settings['logs'] ) && true == $main_settings['logs'] ) {
            require_once dirname( __FILE__ ) . '/partials/monolog/vendor/autoload.php';
            $log = new Monolog\Logger('mpt_logger');
            $logfile = $this->MPT_log_file();
            $log->pushHandler( new Monolog\Handler\StreamHandler($this->MPT_get_log_dir() . $logfile, Monolog\Logger::DEBUG) );
        } else {
            require_once dirname( __FILE__ ) . '/partials/monolog/nologs.php';
            $log = new Nolog();
        }
        return $log;
    }

    /**
     * Mask secrets in dumped settings.
     *
     * @param mixed $data Settings dump.
     * @return mixed
     */
    public function MPT_redact_settings( $data ) {
        $secret_keys = array(
            'apikey',
            'token',
            'proxy_password',
            'openai_extractor_apikey',
            'envato_token',
            'cxid'
        );
        if ( is_array( $data ) ) {
            foreach ( $data as $key => $value ) {
                if ( in_array( (string) $key, $secret_keys, true ) && !is_array( $value ) ) {
                    $data[$key] = ( '' === (string) $value ? '' : '********' );
                } else {
                    $data[$key] = $this->MPT_redact_settings( $value );
                }
            }
        }
        return $data;
    }

    /**
     * Acquire an atomic per-post lock.
     *
     * @param int $post_id Post ID.
     * @return bool
     */
    public function MPT_acquire_post_lock( $post_id ) {
        $post_id = absint( $post_id );
        if ( $post_id < 1 ) {
            return false;
        }
        $key = 'mpt_lock_' . $post_id;
        if ( wp_using_ext_object_cache() ) {
            $added = wp_cache_add(
                $key,
                time(),
                'mpt',
                self::LOCK_TTL
            );
            if ( $added ) {
                return true;
            }
            $existing = wp_cache_get( $key, 'mpt' );
            if ( $existing && time() - (int) $existing > self::LOCK_TTL ) {
                wp_cache_delete( $key, 'mpt' );
                return (bool) wp_cache_add(
                    $key,
                    time(),
                    'mpt',
                    self::LOCK_TTL
                );
            }
            return false;
        }
        global $wpdb;
        $now = time();
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $lock = $wpdb->query( $wpdb->prepare( "INSERT IGNORE INTO `{$wpdb->options}` ( `option_name`, `option_value`, `autoload` ) VALUES (%s, %s, 'no')", $key, (string) $now ) );
        if ( $lock ) {
            wp_cache_delete( $key, 'options' );
            return true;
        }
        $existing = get_option( $key );
        if ( $existing && $now - (int) $existing > self::LOCK_TTL ) {
            delete_option( $key );
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
            $lock = $wpdb->query( $wpdb->prepare( "INSERT IGNORE INTO `{$wpdb->options}` ( `option_name`, `option_value`, `autoload` ) VALUES (%s, %s, 'no')", $key, (string) $now ) );
            if ( $lock ) {
                wp_cache_delete( $key, 'options' );
                return true;
            }
        }
        return false;
    }

    /**
     * Release a per-post lock.
     *
     * @param int $post_id Post ID.
     */
    public function MPT_release_post_lock( $post_id ) {
        $post_id = absint( $post_id );
        $key = 'mpt_lock_' . $post_id;
        if ( wp_using_ext_object_cache() ) {
            wp_cache_delete( $key, 'mpt' );
        }
        delete_option( $key );
    }

    /**
     * Begin a nested generation for a post. Acquires the lock on the first level.
     *
     * @param int $post_id Post ID.
     * @return bool
     */
    public function MPT_begin_post_generation( $post_id ) {
        $post_id = absint( $post_id );
        if ( !isset( self::$lock_depth[$post_id] ) ) {
            self::$lock_depth[$post_id] = 0;
        }
        if ( 0 === self::$lock_depth[$post_id] ) {
            if ( !$this->MPT_acquire_post_lock( $post_id ) ) {
                return false;
            }
        }
        self::$lock_depth[$post_id]++;
        return true;
    }

    /**
     * End a nested generation for a post. Releases the lock on the last level.
     *
     * @param int $post_id Post ID.
     */
    public function MPT_end_post_generation( $post_id ) {
        $post_id = absint( $post_id );
        if ( empty( self::$lock_depth[$post_id] ) ) {
            return;
        }
        self::$lock_depth[$post_id]--;
        if ( self::$lock_depth[$post_id] <= 0 ) {
            unset(self::$lock_depth[$post_id]);
            $this->MPT_release_post_lock( $post_id );
        }
    }

    /**
     * Whether this post was already handled in the current request.
     *
     * @param int $post_id Post ID.
     * @return bool
     */
    public function MPT_mark_in_request( $post_id ) {
        $post_id = absint( $post_id );
        if ( isset( self::$in_request_posts[$post_id] ) ) {
            return false;
        }
        self::$in_request_posts[$post_id] = true;
        return true;
    }

    /**
     * Generic HTTP defaults for MPT remote requests.
     *
     * @since 7.0.0
     * @return array
     */
    public function MPT_http_defaults() {
        $sslverify = apply_filters( 'mpt_http_sslverify', true );
        if ( has_filter( 'mpt_google_scraping_sslverify' ) ) {
            if ( function_exists( '_deprecated_hook' ) ) {
                _deprecated_hook( 'mpt_google_scraping_sslverify', '7.0.0', 'mpt_http_sslverify' );
            }
            $sslverify = apply_filters( 'mpt_google_scraping_sslverify', $sslverify );
        }
        return array(
            'redirection'        => 3,
            'timeout'            => 15,
            'user-agent'         => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            'headers'            => array(
                'Accept' => 'application/json, text/plain, */*',
            ),
            'reject_unsafe_urls' => true,
            'sslverify'          => (bool) $sslverify,
            '_mpt'               => true,
        );
    }

    /**
     * Translate text. Never throws: returns the original string if every provider fails.
     *
     * The old translate.googleapis.com?client=gtx gateway is rate-limited / 429'd
     * by Google since mid-2026. Primary path is the Translate Element gateway
     * (translate-pa), then MyMemory.
     *
     * @since 7.0.1
     * @param string $text   Text to translate.
     * @param string $source Source language code, or "auto".
     * @param string $target Target language code.
     * @return string
     */
    public function MPT_translate( $text, $source = 'auto', $target = 'en' ) {
        $text = ( is_string( $text ) ? trim( $text ) : '' );
        if ( '' === $text ) {
            return $text;
        }
        $source = $this->MPT_sanitize_lang_code( $source, true );
        $target = $this->MPT_sanitize_lang_code( $target, false );
        if ( '' === $target ) {
            return $text;
        }
        if ( 'auto' !== $source && $source === $target ) {
            return $text;
        }
        $translated = $this->MPT_translate_google_pa( $text, $source, $target );
        if ( false !== $translated ) {
            return $translated;
        }
        $translated = $this->MPT_translate_mymemory( $text, $source, $target );
        if ( false !== $translated ) {
            return $translated;
        }
        $log = $this->MPT_monolog_call();
        $log->error( 'Translation failed, keeping original text', array(
            'source' => $source,
            'target' => $target,
        ) );
        return $text;
    }

    /**
     * Sanitize a language code used in translation requests.
     *
     * @param string $code       Raw code.
     * @param bool   $allow_auto Allow "auto".
     * @return string
     */
    protected function MPT_sanitize_lang_code( $code, $allow_auto = false ) {
        $code = strtolower( trim( (string) $code ) );
        if ( $allow_auto && ('auto' === $code || 'autodetect' === $code) ) {
            return 'auto';
        }
        if ( !preg_match( '/^[a-z]{2,3}(?:-[a-z]{2,8})?$/', $code ) ) {
            return '';
        }
        return $code;
    }

    /**
     * Google Translate Element gateway (translate-pa). Replaces client=gtx.
     *
     * @param string $text   Text to translate.
     * @param string $source Source language or "auto".
     * @param string $target Target language.
     * @return string|false
     */
    protected function MPT_translate_google_pa( $text, $source, $target ) {
        $escaped = str_replace( array('&', '<', '>'), array('&amp;', '&lt;', '&gt;'), $text );
        $args = $this->MPT_http_defaults();
        $args['method'] = 'POST';
        $args['timeout'] = 12;
        $args['redirection'] = 2;
        $args['headers'] = array(
            'Accept'         => 'application/json+protobuf, application/json, */*',
            'Content-Type'   => 'application/json+protobuf',
            'X-Goog-API-Key' => 'AIzaSyATBXajvzQLTDHEQbcpq0Ihe0vWDHmO520',
        );
        $args['body'] = wp_json_encode( array(array(array($escaped), $source, $target), 'te_lib') );
        $response = wp_remote_request( 'https://translate-pa.googleapis.com/v1/translateHtml', $args );
        if ( is_wp_error( $response ) || 200 !== (int) wp_remote_retrieve_response_code( $response ) ) {
            return false;
        }
        $data = json_decode( wp_remote_retrieve_body( $response ), true );
        if ( !is_array( $data ) || !isset( $data[0][0] ) || !is_string( $data[0][0] ) ) {
            return false;
        }
        $translated = html_entity_decode( $data[0][0], ENT_QUOTES | ENT_HTML5, 'UTF-8' );
        $translated = trim( $translated );
        return ( '' !== $translated ? $translated : false );
    }

    /**
     * MyMemory free translation API (fallback).
     *
     * @param string $text   Text to translate.
     * @param string $source Source language or "auto".
     * @param string $target Target language.
     * @return string|false
     */
    protected function MPT_translate_mymemory( $text, $source, $target ) {
        $pair = (( 'auto' === $source ? 'Autodetect' : $source )) . '|' . $target;
        $url = add_query_arg( array(
            'q'        => $text,
            'langpair' => $pair,
        ), 'https://api.mymemory.translated.net/get' );
        $args = $this->MPT_http_defaults();
        $args['timeout'] = 12;
        $response = wp_remote_request( $url, $args );
        if ( is_wp_error( $response ) || 200 !== (int) wp_remote_retrieve_response_code( $response ) ) {
            return false;
        }
        $data = json_decode( wp_remote_retrieve_body( $response ), true );
        if ( !is_array( $data ) || empty( $data['responseData']['translatedText'] ) ) {
            return false;
        }
        if ( isset( $data['responseStatus'] ) && 200 !== (int) $data['responseStatus'] ) {
            return false;
        }
        $translated = trim( (string) $data['responseData']['translatedText'] );
        if ( '' === $translated || false !== stripos( $translated, 'MYMEMORY WARNING' ) ) {
            return false;
        }
        return $translated;
    }

    /**
     * Define proxy constants early and scope them to MPT requests.
     *
     * @since 7.0.0
     */
    public static function MPT_maybe_define_proxy_constants() {
        if ( !function_exists( 'mpt_freemius' ) || !mpt_freemius()->is__premium_only() || !mpt_freemius()->can_use_premium_code() ) {
            return;
        }
        $options_proxy = get_option( 'MPT_plugin_proxy_settings' );
        if ( !is_array( $options_proxy ) ) {
            return;
        }
        if ( empty( $options_proxy['enable_proxy'] ) || 'enable' !== $options_proxy['enable_proxy'] || empty( $options_proxy['proxy_adress'] ) || empty( $options_proxy['proxy_port'] ) ) {
            return;
        }
        if ( !defined( 'WP_PROXY_HOST' ) ) {
            define( 'WP_PROXY_HOST', $options_proxy['proxy_adress'] );
        }
        if ( !defined( 'WP_PROXY_PORT' ) ) {
            define( 'WP_PROXY_PORT', $options_proxy['proxy_port'] );
        }
        if ( !empty( $options_proxy['proxy_username'] ) && !defined( 'WP_PROXY_USERNAME' ) ) {
            define( 'WP_PROXY_USERNAME', $options_proxy['proxy_username'] );
        }
        if ( !empty( $options_proxy['proxy_password'] ) && !defined( 'WP_PROXY_PASSWORD' ) ) {
            define( 'WP_PROXY_PASSWORD', $options_proxy['proxy_password'] );
        }
        add_filter(
            'pre_http_send_through_proxy',
            array(__CLASS__, 'MPT_filter_pre_http_send_through_proxy'),
            10,
            4
        );
        add_filter(
            'http_request_args',
            array(__CLASS__, 'MPT_track_mpt_http_request'),
            5,
            2
        );
    }

    /**
     * Remember whether the current HTTP request is an MPT one.
     *
     * @param array  $args Request args.
     * @param string $url  URL.
     * @return array
     */
    public static function MPT_track_mpt_http_request( $args, $url ) {
        self::$mpt_http_request = !empty( $args['_mpt'] );
        return $args;
    }

    /**
     * Only send MPT-marked requests through the plugin proxy.
     *
     * @param bool|null $override Current override.
     * @param string    $uri      Request URI.
     * @param array     $check    Parsed URL.
     * @param string    $home     Home URL.
     * @return bool|null
     */
    public static function MPT_filter_pre_http_send_through_proxy(
        $override,
        $uri,
        $check,
        $home
    ) {
        if ( self::$mpt_http_request ) {
            return null;
        }
        return false;
    }

    /**
     * Whether a hostname or IP is private / reserved / localhost.
     *
     * @param string $host Hostname or IP.
     * @return bool
     */
    public function MPT_is_private_host( $host ) {
        $host = strtolower( trim( (string) $host ) );
        if ( '' === $host ) {
            return true;
        }
        $host = trim( $host, '[]' );
        $blocked_names = array('localhost', 'localhost.localdomain', 'metadata.google.internal');
        if ( in_array( $host, $blocked_names, true ) ) {
            return true;
        }
        if ( $this->MPT_is_private_ip( $host ) ) {
            return true;
        }
        $ips = array();
        if ( function_exists( 'dns_get_record' ) ) {
            $records = @dns_get_record( $host, DNS_A + (( defined( 'DNS_AAAA' ) ? DNS_AAAA : 0 )) );
            if ( is_array( $records ) ) {
                foreach ( $records as $record ) {
                    if ( !empty( $record['ip'] ) ) {
                        $ips[] = $record['ip'];
                    }
                    if ( !empty( $record['ipv6'] ) ) {
                        $ips[] = $record['ipv6'];
                    }
                }
            }
        }
        if ( empty( $ips ) ) {
            $resolved = gethostbynamel( $host );
            if ( is_array( $resolved ) ) {
                $ips = $resolved;
            } else {
                $ip = gethostbyname( $host );
                if ( $ip === $host ) {
                    return true;
                }
                $ips = array($ip);
            }
        }
        foreach ( array_unique( $ips ) as $ip ) {
            if ( $this->MPT_is_private_ip( $ip ) ) {
                return true;
            }
        }
        return false;
    }

    /**
     * Whether an IP address is private, reserved, link-local or loopback.
     *
     * @param string $ip IP address.
     * @return bool
     */
    public function MPT_is_private_ip( $ip ) {
        $ip = strtolower( trim( (string) $ip ) );
        if ( '' === $ip ) {
            return true;
        }
        $ip = trim( $ip, '[]' );
        if ( false !== strpos( $ip, '%' ) ) {
            $ip = substr( $ip, 0, strpos( $ip, '%' ) );
        }
        if ( !filter_var( $ip, FILTER_VALIDATE_IP ) ) {
            return false;
        }
        if ( !filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE ) ) {
            return true;
        }
        if ( 0 === strpos( $ip, '169.254.' ) || 0 === strpos( $ip, 'fe80:' ) || 0 === strpos( $ip, '::ffff:169.254.' ) ) {
            return true;
        }
        if ( '::1' === $ip || '0:0:0:0:0:0:0:1' === $ip ) {
            return true;
        }
        return false;
    }

    /**
     * Validate a remote URL before download (scheme, host, port, DNS, private IPs).
     *
     * @param string $url URL.
     * @return string|false Sanitized URL or false.
     */
    public function MPT_validate_remote_url( $url ) {
        $url = trim( (string) $url );
        if ( '' === $url ) {
            return false;
        }
        $parts = wp_parse_url( $url );
        if ( empty( $parts['scheme'] ) || empty( $parts['host'] ) ) {
            return false;
        }
        $scheme = strtolower( $parts['scheme'] );
        if ( !in_array( $scheme, array('http', 'https'), true ) ) {
            return false;
        }
        if ( !empty( $parts['port'] ) && !in_array( (int) $parts['port'], array(80, 443), true ) ) {
            return false;
        }
        if ( $this->MPT_is_private_host( $parts['host'] ) ) {
            return false;
        }
        if ( function_exists( 'wp_http_validate_url' ) && false === wp_http_validate_url( $url ) ) {
            return false;
        }
        return esc_url_raw( $url );
    }

    /**
     * Transient key for Gutenberg search result URLs (per user + post + bank).
     *
     * One key per bank: Gutenberg searches all tabs in parallel, so a shared
     * list would race and drop the URLs of the tab the user actually picks.
     *
     * @param int    $post_id Post ID.
     * @param string $bank    Bank slug.
     * @return string
     */
    public function MPT_gutenberg_urls_transient_key( $post_id, $bank = '' ) {
        $bank = $this->MPT_normalize_bank_slug( $bank );
        $base = 'mpt_gb_urls_' . get_current_user_id() . '_' . absint( $post_id );
        return ( '' === $bank ? $base : $base . '_' . $bank );
    }

    /**
     * Official domains for a Gutenberg bank.
     *
     * All subdomains are allowed (cdn.pixabay.com matches pixabay.com).
     * Separate roots stay listed when they are not a subdomain of the main site
     * (staticflickr.com, ytimg.com).
     *
     * @param string $bank Bank slug.
     * @return array
     */
    public function MPT_gutenberg_bank_hosts( $bank ) {
        $bank = $this->MPT_normalize_bank_slug( $bank );
        $map = array(
            'pixabay'   => array('pixabay.com'),
            'unsplash'  => array('unsplash.com'),
            'pexels'    => array('pexels.com'),
            'flickr'    => array('flickr.com', 'staticflickr.com'),
            'youtube'   => array('youtube.com', 'ytimg.com', 'youtu.be'),
            'openverse' => array('openverse.org', 'wordpress.org'),
        );
        return ( isset( $map[$bank] ) ? $map[$bank] : array() );
    }

    /**
     * Whether a host is the official domain or one of its subdomains.
     *
     * @param string $host   Host.
     * @param string $suffix Official domain (pixabay.com).
     * @return bool
     */
    protected function MPT_host_matches_suffix( $host, $suffix ) {
        $host = strtolower( preg_replace( '/^www\\./', '', (string) $host ) );
        $suffix = strtolower( preg_replace( '/^www\\./', '', (string) $suffix ) );
        if ( '' === $host || '' === $suffix ) {
            return false;
        }
        return $host === $suffix || substr( $host, -strlen( '.' . $suffix ) ) === '.' . $suffix;
    }

    /**
     * Collect http(s) URLs from a Gutenberg search payload.
     *
     * @param mixed $data Search results.
     * @return array
     */
    public function MPT_gutenberg_collect_urls( $data ) {
        $urls = array();
        $this->MPT_gutenberg_walk_urls( $data, $urls );
        return array_values( array_unique( $urls ) );
    }

    /**
     * Walk a nested value and collect remote URL strings.
     *
     * @param mixed $data Value.
     * @param array $urls Collected URLs (by ref).
     */
    protected function MPT_gutenberg_walk_urls( $data, &$urls ) {
        if ( is_string( $data ) ) {
            if ( 0 === stripos( $data, 'http://' ) || 0 === stripos( $data, 'https://' ) ) {
                $urls[] = $data;
                $valid = $this->MPT_validate_remote_url( $data );
                if ( !empty( $valid ) ) {
                    $urls[] = $valid;
                }
            }
            return;
        }
        if ( is_array( $data ) || is_object( $data ) ) {
            foreach ( $data as $item ) {
                $this->MPT_gutenberg_walk_urls( $item, $urls );
            }
        }
    }

    /**
     * Remember URLs returned by a Gutenberg search so download cannot sideload arbitrary hosts.
     *
     * @param int    $post_id Post ID.
     * @param mixed  $results Search results.
     * @param string $bank    Bank slug.
     */
    public function MPT_gutenberg_remember_urls( $post_id, $results, $bank = '' ) {
        $key = $this->MPT_gutenberg_urls_transient_key( $post_id, $bank );
        $merged = $this->MPT_gutenberg_collect_urls( $results );
        if ( count( $merged ) > 400 ) {
            $merged = array_slice( $merged, -400 );
        }
        set_transient( $key, $merged, 30 * MINUTE_IN_SECONDS );
    }

    /**
     * Whether a Gutenberg download URL was returned by a recent search for this user/post/bank.
     *
     * @param int    $post_id Post ID.
     * @param string $url     Candidate URL (already validated or raw).
     * @param string $bank    Bank slug.
     * @return bool
     */
    public function MPT_gutenberg_url_is_allowed( $post_id, $url, $bank = '' ) {
        $validated = $this->MPT_validate_remote_url( $url );
        if ( empty( $validated ) ) {
            return false;
        }
        $host = strtolower( (string) wp_parse_url( $validated, PHP_URL_HOST ) );
        $path = (string) wp_parse_url( $validated, PHP_URL_PATH );
        foreach ( $this->MPT_gutenberg_bank_hosts( $bank ) as $suffix ) {
            if ( $this->MPT_host_matches_suffix( $host, $suffix ) ) {
                return true;
            }
        }
        $allowed = get_transient( $this->MPT_gutenberg_urls_transient_key( $post_id, $bank ) );
        if ( !is_array( $allowed ) ) {
            $allowed = array();
        }
        $legacy = get_transient( $this->MPT_gutenberg_urls_transient_key( $post_id, '' ) );
        if ( is_array( $legacy ) ) {
            $allowed = array_merge( $allowed, $legacy );
        }
        if ( empty( $allowed ) ) {
            return false;
        }
        $candidates = array($validated, $url);
        $stripped = preg_replace( '/\\?format=json$/', '', $validated );
        if ( $stripped !== $validated ) {
            $candidates[] = $stripped;
        }
        foreach ( $candidates as $candidate ) {
            $candidate = ( is_string( $candidate ) ? $candidate : '' );
            if ( '' === $candidate ) {
                continue;
            }
            foreach ( $allowed as $known ) {
                if ( !is_string( $known ) || '' === $known ) {
                    continue;
                }
                if ( $candidate === $known || untrailingslashit( $candidate ) === untrailingslashit( $known ) ) {
                    return true;
                }
                $known_host = strtolower( (string) wp_parse_url( $known, PHP_URL_HOST ) );
                $known_path = (string) wp_parse_url( $known, PHP_URL_PATH );
                if ( $host && $path && $host === $known_host && $path === $known_path ) {
                    return true;
                }
                if ( $host && $known_host && $this->MPT_host_matches_suffix( $host, $known_host ) ) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Whether the current user can open a dashboard module.
     *
     * Granting mpt_manage is the plugin access right: menus and settings included.
     *
     * @param string $module Module slug.
     * @return bool
     */
    public function MPT_user_can_access_module( $module ) {
        return current_user_can( 'mpt_manage' );
    }

    /**
     * Mark an HTTP request as MPT-owned (proxy scope, TLS defaults).
     *
     * @param array  $args Request args.
     * @param string $url  URL.
     * @return array
     */
    public function MPT_mark_download_http_args( $args, $url ) {
        $defaults = $this->MPT_http_defaults();
        $args['_mpt'] = true;
        $args['sslverify'] = $defaults['sslverify'];
        $args['reject_unsafe_urls'] = true;
        if ( empty( $args['timeout'] ) || (int) $args['timeout'] < 15 ) {
            $args['timeout'] = 15;
        }
        return $args;
    }

    /**
     * Download a remote image to a temp file (SSRF-safe).
     *
     * @param string $url Remote URL.
     * @return string|false Absolute temp path.
     */
    public function MPT_download_to_temp( $url ) {
        $url = $this->MPT_validate_remote_url( $url );
        if ( empty( $url ) ) {
            return false;
        }
        require_once ABSPATH . 'wp-admin/includes/file.php';
        add_filter(
            'http_request_args',
            array($this, 'MPT_mark_download_http_args'),
            5,
            2
        );
        $tmp = download_url( $url, 15 );
        remove_filter( 'http_request_args', array($this, 'MPT_mark_download_http_args'), 5 );
        if ( is_wp_error( $tmp ) || !is_readable( $tmp ) ) {
            return false;
        }
        $mime = ( function_exists( 'wp_get_image_mime' ) ? wp_get_image_mime( $tmp ) : '' );
        if ( !$mime || 0 !== strpos( $mime, 'image/' ) ) {
            @unlink( $tmp );
            return false;
        }
        return $tmp;
    }

    /**
     * Sideload image bytes or a temp file into the Media Library.
     *
     * @param string $filename Desired basename (unique name applied by WP).
     * @param int    $post_id  Parent post ID.
     * @param array  $args     bytes|tmp, title, alt, caption, convert_png_jpg, flip, crop, overlay, overlay_post_id.
     * @return int|false Attachment ID.
     */
    public function MPT_sideload_image( $filename, $post_id, $args = array() ) {
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';
        require_once ABSPATH . 'wp-admin/includes/image.php';
        $tmp = null;
        $created_tmp = false;
        try {
            if ( !empty( $args['tmp'] ) && is_readable( $args['tmp'] ) ) {
                $tmp = $args['tmp'];
            } elseif ( isset( $args['bytes'] ) && '' !== $args['bytes'] && false !== $args['bytes'] ) {
                $tmp = wp_tempnam( $filename );
                if ( !$tmp ) {
                    return false;
                }
                $created_tmp = true;
                if ( false === file_put_contents( $tmp, $args['bytes'] ) ) {
                    return false;
                }
            } else {
                return false;
            }
            if ( !empty( $args['convert_png_jpg'] ) ) {
                $converted = $this->MPT_convert_temp_png_to_jpg( $tmp );
                if ( $converted ) {
                    if ( $tmp !== $converted && ($created_tmp || !empty( $args['tmp'] )) ) {
                        @unlink( $tmp );
                    }
                    $tmp = $converted;
                    $created_tmp = true;
                    $filename = preg_replace( '/\\.png$/i', '.jpg', $filename );
                }
            }
            if ( !empty( $args['flip'] ) || !empty( $args['crop'] ) ) {
                $this->MPT_edit_temp_image( $tmp, !empty( $args['flip'] ), !empty( $args['crop'] ) );
            }
            if ( !empty( $args['overlay'] ) && !empty( $args['overlay_post_id'] ) ) {
                $this->MPT_overlay_temp_image( $tmp, (int) $args['overlay_post_id'] );
            }
            $file_array = array(
                'name'     => $filename,
                'tmp_name' => $tmp,
            );
            $post_data = array();
            if ( !empty( $args['title'] ) ) {
                $post_data['post_title'] = $args['title'];
            }
            $attach_id = media_handle_sideload(
                $file_array,
                (int) $post_id,
                null,
                $post_data
            );
            if ( is_wp_error( $attach_id ) ) {
                return false;
            }
            $tmp = null;
            if ( !empty( $args['alt'] ) ) {
                update_post_meta( $attach_id, '_wp_attachment_image_alt', $args['alt'] );
            }
            if ( !empty( $args['caption'] ) ) {
                wp_update_post( array(
                    'ID'           => $attach_id,
                    'post_excerpt' => $args['caption'],
                ) );
            }
            return (int) $attach_id;
        } finally {
            if ( $tmp && file_exists( $tmp ) ) {
                @unlink( $tmp );
            }
        }
    }

    /**
     * Convert a PNG temp file to JPEG.
     *
     * @param string $tmp Path.
     * @return string|false New path or false.
     */
    protected function MPT_convert_temp_png_to_jpg( $tmp ) {
        if ( !function_exists( 'imagecreatefrompng' ) ) {
            return false;
        }
        $image = @imagecreatefrompng( $tmp );
        if ( !$image ) {
            return false;
        }
        $jpg = $tmp . '.jpg';
        imagejpeg( $image, $jpg, 90 );
        imagedestroy( $image );
        return ( is_readable( $jpg ) ? $jpg : false );
    }

    /**
     * Flip and/or crop a temp image in place.
     *
     * @param string $tmp  Path.
     * @param bool   $flip Horizontal flip.
     * @param bool   $crop Crop 10%.
     */
    protected function MPT_edit_temp_image( $tmp, $flip, $crop ) {
        $editor = wp_get_image_editor( $tmp );
        if ( is_wp_error( $editor ) ) {
            return;
        }
        if ( $flip ) {
            $editor->flip( false, true );
        }
        if ( $crop ) {
            $size = $editor->get_size();
            $width = $size['width'] * 0.9;
            $height = $size['height'] * 0.9;
            $x = $size['width'] * 0.05;
            $y = $size['height'] * 0.05;
            $editor->crop(
                (int) $x,
                (int) $y,
                (int) $width,
                (int) $height
            );
        }
        $editor->save( $tmp );
    }

    /**
     * Normalize a remote image URL for regenerate-skip comparisons.
     *
     * @param string $url Remote URL.
     * @return string Empty if not an http(s) URL.
     */
    protected function MPT_normalize_source_url( $url ) {
        if ( !is_string( $url ) || '' === $url ) {
            return '';
        }
        if ( 0 !== strpos( $url, 'http://' ) && 0 !== strpos( $url, 'https://' ) ) {
            return '';
        }
        $parts = wp_parse_url( $url );
        if ( !is_array( $parts ) || empty( $parts['host'] ) ) {
            return $url;
        }
        $norm = strtolower( $parts['scheme'] ) . '://' . strtolower( $parts['host'] );
        $norm .= ( isset( $parts['port'] ) ? ':' . $parts['port'] : '' );
        $norm .= ( isset( $parts['path'] ) ? $parts['path'] : '' );
        $norm .= ( isset( $parts['query'] ) ? '?' . $parts['query'] : '' );
        return $norm;
    }

    /**
     * Source URLs already used for a post (so regenerate can pick another).
     *
     * @param int $post_id Post ID.
     * @return array
     */
    protected function MPT_get_used_source_urls( $post_id ) {
        $post_id = (int) $post_id;
        if ( $post_id < 1 ) {
            return array();
        }
        $urls = get_post_meta( $post_id, '_mpt_used_source_urls', true );
        return ( is_array( $urls ) ? $urls : array() );
    }

    /**
     * Whether this remote URL was already used on the post.
     *
     * @param int    $post_id Post ID.
     * @param string $url     Remote URL.
     * @return bool
     */
    protected function MPT_is_used_source_url( $post_id, $url ) {
        $norm = $this->MPT_normalize_source_url( $url );
        if ( '' === $norm ) {
            return false;
        }
        return in_array( $norm, $this->MPT_get_used_source_urls( $post_id ), true );
    }

    /**
     * Remember a source URL after a successful generation.
     *
     * @param int    $post_id Post ID.
     * @param string $url     Remote URL.
     */
    protected function MPT_remember_source_url( $post_id, $url ) {
        $post_id = (int) $post_id;
        $norm = $this->MPT_normalize_source_url( $url );
        if ( $post_id < 1 || '' === $norm ) {
            return;
        }
        $urls = $this->MPT_get_used_source_urls( $post_id );
        if ( in_array( $norm, $urls, true ) ) {
            return;
        }
        $urls[] = $norm;
        if ( count( $urls ) > 20 ) {
            $urls = array_slice( $urls, -20 );
        }
        update_post_meta( $post_id, '_mpt_used_source_urls', $urls );
    }

    /**
     * Whether the brand overlay is enabled.
     *
     * @since 7.0.0
     * @param array|null $options Main settings.
     * @return bool
     */
    public function MPT_overlay_enabled( $options = null ) {
        if ( null === $options ) {
            $options = wp_parse_args( get_option( 'MPT_plugin_main_settings' ), $this->MPT_default_options_main_settings( true ) );
        }
        $mode = ( isset( $options['image_overlay'] ) ? $options['image_overlay'] : '' );
        return in_array( $mode, array('title', 'title_logo'), true );
    }

    /**
     * Draw a title/logo bar on a temp image.
     *
     * @since 7.0.0
     * @param string $tmp     Image path.
     * @param int    $post_id Post whose title/category to paint.
     */
    protected function MPT_overlay_temp_image( $tmp, $post_id ) {
        $options = wp_parse_args( get_option( 'MPT_plugin_main_settings' ), $this->MPT_default_options_main_settings( true ) );
        if ( !$this->MPT_overlay_enabled( $options ) ) {
            return;
        }
        if ( !function_exists( 'imagecreatefromstring' ) ) {
            return;
        }
        $bytes = @file_get_contents( $tmp );
        if ( false === $bytes ) {
            return;
        }
        $im = @imagecreatefromstring( $bytes );
        if ( !$im ) {
            return;
        }
        $w = imagesx( $im );
        $h = imagesy( $im );
        if ( $w < 32 || $h < 32 ) {
            imagedestroy( $im );
            return;
        }
        imagesavealpha( $im, true );
        imagealphablending( $im, true );
        $bar_h = max( 72, (int) round( $h * 0.18 ) );
        $bar_h = min( $bar_h, (int) round( $h * 0.24 ) );
        $y0 = $h - $bar_h;
        $opacity = ( isset( $options['image_overlay_opacity'] ) ? (int) $options['image_overlay_opacity'] : 55 );
        $opacity = max( 10, min( 90, $opacity ) );
        $bg = $this->MPT_hex_to_rgb( ( isset( $options['image_overlay_bg'] ) ? $options['image_overlay_bg'] : '#111111' ) );
        $fg = $this->MPT_hex_to_rgb( ( isset( $options['image_overlay_text'] ) ? $options['image_overlay_text'] : '#ffffff' ) );
        $alpha = (int) round( 127 * (100 - $opacity) / 100 );
        $bar = imagecolorallocatealpha(
            $im,
            $bg[0],
            $bg[1],
            $bg[2],
            $alpha
        );
        imagefilledrectangle(
            $im,
            0,
            $y0,
            $w,
            $h,
            $bar
        );
        $pad = max( 16, (int) round( $w * 0.035 ) );
        $text_x = $pad;
        $logo_id = ( !empty( $options['image_overlay_logo'] ) ? (int) $options['image_overlay_logo'] : 0 );
        $mode = $options['image_overlay'];
        if ( 'title_logo' === $mode && $logo_id > 0 ) {
            $logo_file = get_attached_file( $logo_id );
            if ( $logo_file && is_readable( $logo_file ) ) {
                $logo_bytes = @file_get_contents( $logo_file );
                $logo = ( $logo_bytes ? @imagecreatefromstring( $logo_bytes ) : false );
                if ( $logo ) {
                    $lw = imagesx( $logo );
                    $lh = imagesy( $logo );
                    $target = (int) round( $bar_h * 0.58 );
                    if ( $lh > 0 && $lw > 0 ) {
                        $nw = max( 1, (int) round( $lw * ($target / $lh) ) );
                        $nh = $target;
                        $dx = $pad;
                        $dy = $y0 + (int) round( ($bar_h - $nh) / 2 );
                        imagecopyresampled(
                            $im,
                            $logo,
                            $dx,
                            $dy,
                            0,
                            0,
                            $nw,
                            $nh,
                            $lw,
                            $lh
                        );
                        $text_x = $dx + $nw + (int) round( $pad * 0.65 );
                    }
                    imagedestroy( $logo );
                }
            }
        }
        $title = get_the_title( $post_id );
        $title = wp_strip_all_tags( html_entity_decode( (string) $title, ENT_QUOTES | ENT_HTML5, 'UTF-8' ) );
        if ( function_exists( 'wp_check_invalid_utf8' ) ) {
            $title = wp_check_invalid_utf8( $title, true );
        }
        if ( !empty( $options['image_overlay_category'] ) && 'true' === (string) $options['image_overlay_category'] ) {
            $cats = get_the_category( $post_id );
            if ( !empty( $cats[0]->name ) ) {
                $cat_name = wp_strip_all_tags( html_entity_decode( (string) $cats[0]->name, ENT_QUOTES | ENT_HTML5, 'UTF-8' ) );
                $title = $cat_name . '  ·  ' . $title;
            }
        }
        if ( function_exists( 'mb_strlen' ) && mb_strlen( $title, 'UTF-8' ) > 80 ) {
            $title = mb_substr(
                $title,
                0,
                77,
                'UTF-8'
            ) . '…';
        } elseif ( strlen( $title ) > 80 ) {
            $title = substr( $title, 0, 77 ) . '...';
        }
        $font = $this->MPT_overlay_font_path();
        $text_col = imagecolorallocate(
            $im,
            $fg[0],
            $fg[1],
            $fg[2]
        );
        $max_w = $w - $text_x - $pad;
        $drawn = false;
        if ( $font && function_exists( 'imagettftext' ) && $max_w > 20 ) {
            $font_size = max( 22, (int) round( $bar_h * 0.38 ) );
            $font_size = min( $font_size, (int) round( $w * 0.045 ) );
            for ($try = 0; $try < 12; $try++) {
                $box = @imagettfbbox(
                    $font_size,
                    0,
                    $font,
                    $title
                );
                $tw = ( $box ? abs( $box[2] - $box[0] ) : $max_w + 1 );
                if ( $tw <= $max_w || $font_size <= 18 ) {
                    break;
                }
                $font_size -= 2;
            }
            $box = @imagettfbbox(
                $font_size,
                0,
                $font,
                $title
            );
            if ( $box ) {
                $text_h = abs( $box[7] - $box[1] );
                $text_y = $y0 + (int) round( ($bar_h + $text_h) / 2 ) - (int) round( $text_h * 0.08 );
                $shadow = imagecolorallocatealpha(
                    $im,
                    0,
                    0,
                    0,
                    70
                );
                @imagettftext(
                    $im,
                    $font_size,
                    0,
                    $text_x + 1,
                    $text_y + 1,
                    $shadow,
                    $font,
                    $title
                );
                $drawn = false !== @imagettftext(
                    $im,
                    $font_size,
                    0,
                    $text_x,
                    $text_y,
                    $text_col,
                    $font,
                    $title
                );
            }
        }
        if ( !$drawn ) {
            $legacy = $title;
            if ( function_exists( 'mb_convert_encoding' ) ) {
                $converted = @mb_convert_encoding( $title, 'ISO-8859-1', 'UTF-8' );
                if ( false !== $converted && '' !== $converted ) {
                    $legacy = $converted;
                }
            }
            imagestring(
                $im,
                5,
                $text_x,
                $y0 + (int) round( ($bar_h - 14) / 2 ),
                $legacy,
                $text_col
            );
        }
        $mime = ( function_exists( 'wp_get_image_mime' ) ? wp_get_image_mime( $tmp ) : '' );
        if ( 'image/png' === $mime ) {
            imagepng( $im, $tmp );
        } elseif ( 'image/webp' === $mime && function_exists( 'imagewebp' ) ) {
            imagewebp( $im, $tmp, 90 );
        } else {
            imagejpeg( $im, $tmp, 90 );
        }
        imagedestroy( $im );
    }

    /**
     * TTF usable for overlay text (accents need FreeType).
     *
     * @since 7.0.0
     * @return string Empty if none.
     */
    protected function MPT_overlay_font_path() {
        $candidates = array(
            dirname( __FILE__ ) . '/fonts/SourceSans3-Semibold.ttf',
            'C:\\Windows\\Fonts\\segoeui.ttf',
            'C:\\Windows\\Fonts\\arial.ttf',
            '/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf',
            '/usr/share/fonts/truetype/liberation/LiberationSans-Regular.ttf',
            '/usr/share/fonts/truetype/freefont/FreeSans.ttf',
            '/System/Library/Fonts/Supplemental/Arial.ttf',
            '/Library/Fonts/Arial.ttf'
        );
        foreach ( $candidates as $path ) {
            if ( is_readable( $path ) ) {
                return str_replace( '\\', '/', $path );
            }
        }
        return '';
    }

    /**
     * @param string $hex Color.
     * @return array{0:int,1:int,2:int}
     */
    protected function MPT_hex_to_rgb( $hex ) {
        $hex = ltrim( (string) $hex, '#' );
        if ( 3 === strlen( $hex ) ) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }
        if ( 6 !== strlen( $hex ) || !ctype_xdigit( $hex ) ) {
            return array(17, 17, 17);
        }
        return array(hexdec( substr( $hex, 0, 2 ) ), hexdec( substr( $hex, 2, 2 ) ), hexdec( substr( $hex, 4, 2 ) ));
    }

    /**
     * First usable image already in the post content.
     *
     * @since 7.0.0
     * @param int $post_id Post ID.
     * @return array{attach_id?:int,url?:string}|false
     */
    protected function MPT_first_content_image( $post_id ) {
        $post = get_post( $post_id );
        if ( !$post || '' === (string) $post->post_content ) {
            return false;
        }
        $content = $post->post_content;
        $featured_id = (int) get_post_thumbnail_id( $post_id );
        if ( preg_match( '/wp:image\\s*\\{[^}]*"id"\\s*:\\s*(\\d+)/', $content, $m ) ) {
            $id = (int) $m[1];
            if ( $id > 0 && $id !== $featured_id && wp_attachment_is_image( $id ) ) {
                return array(
                    'attach_id' => $id,
                );
            }
        }
        if ( preg_match( '/wp-image-(\\d+)/', $content, $m ) ) {
            $id = (int) $m[1];
            if ( $id > 0 && $id !== $featured_id && wp_attachment_is_image( $id ) ) {
                return array(
                    'attach_id' => $id,
                );
            }
        }
        if ( preg_match( '/<img[^>]+src=["\']([^"\']+)["\']/', $content, $m ) ) {
            $src = $m[1];
            if ( 0 === strpos( $src, 'data:' ) ) {
                return false;
            }
            $attach_id = 0;
            if ( function_exists( 'attachment_url_to_postid' ) ) {
                $attach_id = (int) attachment_url_to_postid( $src );
            }
            if ( $attach_id > 0 && $attach_id !== $featured_id && wp_attachment_is_image( $attach_id ) ) {
                return array(
                    'attach_id' => $attach_id,
                );
            }
            $validated = $this->MPT_validate_remote_url( $src );
            if ( $validated ) {
                return array(
                    'url' => $validated,
                );
            }
        }
        return false;
    }

}
