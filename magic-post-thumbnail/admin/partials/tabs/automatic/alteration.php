<?php
if ( ! function_exists( 'add_filter' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
		exit();
}
?>
<tr valign="top" class="selected_image">
        <th scope="row">
                <label for="hseparator"><?php esc_html_e( 'Image Naming Convention', 'magic-post-thumbnail' ); ?></label>
        </th>
        <td class="chosen_title radio-inline">
                <label  class="radio radio-outline radio-outline-2x radio-primary"><input value="title"  name="MPT_plugin_main_settings[image_filename] " type="radio" <?php echo( !empty( $options['image_filename']) && $options['image_filename'] == 'title'  )? 'checked': ''; ?> ><span></span> <?php esc_html_e( 'Title', 'magic-post-thumbnail' ); ?></label><br/>
                <label  class="radio radio-outline radio-outline-2x radio-primary"><input value="date"   name="MPT_plugin_main_settings[image_filename] " type="radio" <?php echo( !empty( $options['image_filename']) && $options['image_filename'] == 'date'   )? 'checked': ''; ?> ><span></span> <?php esc_html_e( 'Date', 'magic-post-thumbnail' ); ?></label><br/>
                <label  class="radio radio-outline radio-outline-2x radio-primary"><input value="random" name="MPT_plugin_main_settings[image_filename] " type="radio" <?php echo( !empty( $options['image_filename']) && $options['image_filename'] == 'random' )? 'checked': ''; ?> ><span></span> <?php esc_html_e( 'Random number', 'magic-post-thumbnail' ); ?></label>
        </td>
</tr>

<tr valign="top">
        <th scope="row">
                <?php esc_html_e( 'Overwrite featured images', 'magic-post-thumbnail' ); ?>
        </th>
        <td class="checkbox-list">
                <label class="checkbox"><input <?php echo( !empty( $options['rewrite_featured']) && $options['rewrite_featured'] == 'true' )? 'checked': ''; ?> name="MPT_plugin_main_settings[rewrite_featured]" type="checkbox" value="true"> <span></span> <?php esc_html_e( 'Overwrite', 'magic-post-thumbnail' ); ?></label>
                <p class="description">
                        <?php esc_html_e( 'Warning: This option will overwrite existing featured images', 'magic-post-thumbnail' ); ?>
                </p>
        </td>
</tr>

<tr valign="top">
        <th scope="row">
                <?php esc_html_e( 'Image reuse', 'magic-post-thumbnail' ); ?>
        </th>
        <td class="checkbox-list">
                <label class="checkbox"><input <?php echo( !empty( $options['image_reuse']) && $options['image_reuse'] == 'true' )? 'checked': ''; ?> name="MPT_plugin_main_settings[image_reuse]" type="checkbox" value="true"> <span></span> <?php esc_html_e( 'Enable', 'magic-post-thumbnail' ); ?></label>
                <p class="description">
                        <?php esc_html_e( 'Check for existing images media before downloading new ones to prevent duplicates and save storage space. Reuse is based on filename.', 'magic-post-thumbnail' ); ?>
                </p>
        </td>
</tr>

<tr valign="top" class="shuffle_image">
        <th scope="row">
                <?php esc_html_e( 'Image Modifications', 'magic-post-thumbnail' ); ?>
        </th>
        <td class="checkbox-list">
            <label class="checkbox <?php echo $checkbox_disabled; ?>"><input <?php echo( !empty( $options['image_flip']) && $options['image_flip'] == 'true' )? 'checked': ''; ?> name="MPT_plugin_main_settings[image_flip]" type="checkbox" value="true"> <span></span> <?php esc_html_e( 'Flip horizontally', 'magic-post-thumbnail' ); ?></label>
            <label class="checkbox <?php echo $checkbox_disabled; ?>"><input <?php echo( !empty( $options['image_crop']) && $options['image_crop'] == 'true' )? 'checked': ''; ?> name="MPT_plugin_main_settings[image_crop]" type="checkbox" value="true"> <span></span> <?php esc_html_e( 'Crop Image by 10%	', 'magic-post-thumbnail' ); ?></label>
        </td>
</tr>

<tr valign="top" class="mpt-overlay-row">
        <th scope="row">
                <label for="hseparator"><?php esc_html_e( 'Brand overlay', 'magic-post-thumbnail' ); ?></label>
        </th>
        <td>
                <?php $overlay_mode = isset( $options['image_overlay'] ) ? $options['image_overlay'] : ''; ?>
                <select name="MPT_plugin_main_settings[image_overlay]" id="mpt-overlay-mode" class="form-control">
                        <option value="" <?php selected( $overlay_mode, '' ); ?>><?php esc_html_e( 'Disabled', 'magic-post-thumbnail' ); ?></option>
                        <option value="title" <?php selected( $overlay_mode, 'title' ); ?>><?php esc_html_e( 'Title bar', 'magic-post-thumbnail' ); ?></option>
                        <option value="title_logo" <?php selected( $overlay_mode, 'title_logo' ); ?>><?php esc_html_e( 'Title and logo', 'magic-post-thumbnail' ); ?></option>
                </select>
                <p class="description">
                        <?php esc_html_e( 'Draws a bar with the post title on the generated image. Disabled by default.', 'magic-post-thumbnail' ); ?>
                </p>
        </td>
</tr>

<tr valign="top" class="mpt-overlay-options" <?php echo in_array( $overlay_mode, array( 'title', 'title_logo' ), true ) ? '' : 'style="display:none;"'; ?>>
        <th scope="row">
                <?php esc_html_e( 'Overlay look', 'magic-post-thumbnail' ); ?>
        </th>
        <td>
                <p>
                        <label><?php esc_html_e( 'Bar color', 'magic-post-thumbnail' ); ?><br/>
                        <input type="text" name="MPT_plugin_main_settings[image_overlay_bg]" value="<?php echo isset( $options['image_overlay_bg'] ) ? esc_attr( $options['image_overlay_bg'] ) : '#111111'; ?>" class="small-text" maxlength="7" />
                        </label>
                        &nbsp;
                        <label><?php esc_html_e( 'Bar opacity', 'magic-post-thumbnail' ); ?><br/>
                        <input type="number" min="10" max="90" name="MPT_plugin_main_settings[image_overlay_opacity]" value="<?php echo isset( $options['image_overlay_opacity'] ) ? (int) $options['image_overlay_opacity'] : 55; ?>" class="small-text" /> %
                        </label>
                        &nbsp;
                        <label><?php esc_html_e( 'Text color', 'magic-post-thumbnail' ); ?><br/>
                        <input type="text" name="MPT_plugin_main_settings[image_overlay_text]" value="<?php echo isset( $options['image_overlay_text'] ) ? esc_attr( $options['image_overlay_text'] ) : '#ffffff'; ?>" class="small-text" maxlength="7" />
                        </label>
                </p>
                <p>
                        <label class="checkbox">
                                <input name="MPT_plugin_main_settings[image_overlay_category]" type="checkbox" value="true" <?php checked( ! empty( $options['image_overlay_category'] ) && 'true' === (string) $options['image_overlay_category'] ); ?> />
                                <span></span> <?php esc_html_e( 'Also show the first category', 'magic-post-thumbnail' ); ?>
                        </label>
                </p>
        </td>
</tr>

<tr valign="top" class="mpt-overlay-logo" <?php echo ( 'title_logo' === $overlay_mode ) ? '' : 'style="display:none;"'; ?>>
        <th scope="row">
                <?php esc_html_e( 'Overlay logo', 'magic-post-thumbnail' ); ?>
        </th>
        <td>
                <?php
                $logo_id  = isset( $options['image_overlay_logo'] ) ? (int) $options['image_overlay_logo'] : 0;
                $logo_src = $logo_id ? wp_get_attachment_image_url( $logo_id, 'medium' ) : '';
                ?>
                <input type="hidden" id="mpt-overlay-logo-id" name="MPT_plugin_main_settings[image_overlay_logo]" value="<?php echo (int) $logo_id; ?>" />
                <p>
                        <img id="mpt-overlay-logo-preview" src="<?php echo $logo_src ? esc_url( $logo_src ) : ''; ?>" alt="" style="max-height:64px;<?php echo $logo_src ? '' : 'display:none;'; ?>" />
                </p>
                <button type="button" class="button" id="mpt-overlay-logo-select"><?php esc_html_e( 'Select logo', 'magic-post-thumbnail' ); ?></button>
                <button type="button" class="button" id="mpt-overlay-logo-clear" <?php echo $logo_id ? '' : 'style="display:none;"'; ?>><?php esc_html_e( 'Remove', 'magic-post-thumbnail' ); ?></button>
        </td>
</tr>


<?php 
    // Alt Tag

    if ( true === $this->MPT_freemius()->is__premium_only() ) { 
        if ( $this->mpt_freemius()->can_use_premium_code() ) {
?>

    <tr valign="top" class="based_on_bottom">
        <th scope="row">
            <label for="hseparator"><?php esc_html_e( 'Add ALT Attribute to Image', 'magic-post-thumbnail' ); ?></label>
        </th>
        <td>
            <label class="checkbox">
                <input data-switch="true" type="checkbox" name="MPT_plugin_main_settings[enable_alt]" id="enable_alt" value="enable" <?php echo( !empty( $options['enable_alt']) && $options['enable_alt'] == 'enable' )? 'checked': ''; ?> />
            </label>
        </td>
    </tr>

    <tr valign="top" class="show_alt" <?php echo( !isset( $options['enable_alt'] ) || ( $options['enable_alt'] != 'enable' ) ? 'style="display:none;"': ''); ?>>
        <th scope="row">
                <label for="hseparator"><?php esc_html_e( 'Alt Text from', 'magic-post-thumbnail' ); ?></label>
        </th>
        <td class="tags radio-list">
            <label class="radio">
                <input value="source" name="MPT_plugin_main_settings[alt_from]" type="radio" <?php echo( !empty( $options['alt_from']) && $options['alt_from'] == 'source' )? 'checked': ''; ?>><span></span> 
                <?php esc_html_e( 'Source', 'magic-post-thumbnail' ); ?>
            </label>
            <label class="radio">
                <input value="based_on" name="MPT_plugin_main_settings[alt_from]" type="radio" <?php echo( !empty( $options['alt_from']) && $options['alt_from'] == 'based_on' )? 'checked': ''; ?>> <span></span>
                <?php esc_html_e( 'Text "based_on"', 'magic-post-thumbnail' ); ?>
            </label>
        </td>
    </tr>

    <?php 
            if( !isset( $options['translate_alt_lang'] ) ) {
                $wp_lang    = get_bloginfo('language');
                $alt_lang   = substr( $wp_lang, 0, 2 );
            } else {
                $alt_lang   = $options['translate_alt_lang'];
            }
    ?>

    <tr valign="top" class="show_alt" <?php echo(isset($options['enable_alt']) && ($options['enable_alt'] != 'enable') ? 'style="display:none;"': ''); ?>>
        <th scope="row">
                <?php esc_html_e( 'ALT Text Translation', 'magic-post-thumbnail' ); ?>
        </th>
        <td class="checkbox-list">
                <label class="checkbox">
                    <input name="MPT_plugin_main_settings[translate_alt]" type="checkbox" value="true" <?php echo( !empty( $options['translate_alt']) && $options['translate_alt'] == 'true' )? 'checked': ''; ?>><span></span> 
                    <?php esc_html_e( 'Translate alt text from english to', 'magic-post-thumbnail' ); ?>:
                </label>

                <select name="MPT_plugin_main_settings[translate_alt_lang]" class="form-control form-control-lg" >
                    <?php
                        
                        $country_choose = array(
                            __( 'Afrikaans', 'magic-post-thumbnail' )             => 'af',
                            __( 'Afrikaans', 'magic-post-thumbnail' )             => 'af',
                            __( 'Albanian', 'magic-post-thumbnail' )              => 'sq',
                            __( 'Amharic', 'magic-post-thumbnail' )               => 'sm',
                            __( 'Arabic', 'magic-post-thumbnail' )                => 'ar',
                            __( 'Azerbaijani', 'magic-post-thumbnail' )           => 'az',
                            __( 'Basque', 'magic-post-thumbnail' )                => 'eu',
                            __( 'Belarusian', 'magic-post-thumbnail' )            => 'be',
                            __( 'Bengali', 'magic-post-thumbnail' )               => 'bn',
                            __( 'Bihari', 'magic-post-thumbnail' )                => 'bh',
                            __( 'Bosnian', 'magic-post-thumbnail' )               => 'bs',
                            __( 'Bulgarian', 'magic-post-thumbnail' )             => 'bg',
                            __( 'Catalan', 'magic-post-thumbnail' )               => 'ca',
                            __( 'Chinese (Simplified)', 'magic-post-thumbnail' )  => 'zh-CN',
                            __( 'Chinese (Traditional)', 'magic-post-thumbnail' ) => 'zh-TW',
                            __( 'Croatian', 'magic-post-thumbnail' )              => 'hr',
                            __( 'Czech', 'magic-post-thumbnail' )                 => 'cs',
                            __( 'Danish', 'magic-post-thumbnail' )                => 'da',
                            __( 'Dutch', 'magic-post-thumbnail' )                 => 'nl',
                            __( 'Esperanto', 'magic-post-thumbnail' )             => 'eo',
                            __( 'Estonian', 'magic-post-thumbnail' )              => 'et',
                            __( 'Faroese', 'magic-post-thumbnail' )               => 'fo',
                            __( 'Finnish', 'magic-post-thumbnail' )               => 'fi',
                            __( 'French', 'magic-post-thumbnail' )                => 'fr',
                            __( 'Frisian', 'magic-post-thumbnail' )               => 'fy',
                            __( 'Galician', 'magic-post-thumbnail' )              => 'gl',
                            __( 'Georgian', 'magic-post-thumbnail' )              => 'ka',
                            __( 'German', 'magic-post-thumbnail' )                => 'de',
                            __( 'Greek', 'magic-post-thumbnail' )                 => 'el',
                            __( 'Gujarati', 'magic-post-thumbnail' )              => 'gu',
                            __( 'Hebrew', 'magic-post-thumbnail' )                => 'iw',
                            __( 'Hindi', 'magic-post-thumbnail' )                 => 'hi',
                            __( 'Hungarian', 'magic-post-thumbnail' )             => 'hu',
                            __( 'Icelandic', 'magic-post-thumbnail' )             => 'is',
                            __( 'Indonesian', 'magic-post-thumbnail' )            => 'id',
                            __( 'Interlingua', 'magic-post-thumbnail' )           => 'ia',
                            __( 'Irish', 'magic-post-thumbnail' )                 => 'ga',
                            __( 'Italian', 'magic-post-thumbnail' )               => 'it',
                            __( 'Japanese', 'magic-post-thumbnail' )              => 'ja',
                            __( 'Javanese', 'magic-post-thumbnail' )              => 'jw',
                            __( 'Kannada', 'magic-post-thumbnail' )               => 'kn',
                            __( 'Korean', 'magic-post-thumbnail' )                => 'ko',
                            __( 'Latin', 'magic-post-thumbnail' )                 => 'la',
                            __( 'Latvian', 'magic-post-thumbnail' )               => 'lv',
                            __( 'Lithuanian', 'magic-post-thumbnail' )            => 'lt',
                            __( 'Macedonian', 'magic-post-thumbnail' )            => 'mk',
                            __( 'Malay', 'magic-post-thumbnail' )                 => 'ms',
                            __( 'Malayam', 'magic-post-thumbnail' )               => 'ml',
                            __( 'Maltese', 'magic-post-thumbnail' )               => 'mt',
                            __( 'Marathi', 'magic-post-thumbnail' )               => 'mr',
                            __( 'Nepali', 'magic-post-thumbnail' )                => 'ne',
                            __( 'Norwegian', 'magic-post-thumbnail' )             => 'no',
                            __( 'Norwegian (Nynorsk)', 'magic-post-thumbnail' )   => 'nn',
                            __( 'Occitan', 'magic-post-thumbnail' )               => 'oc',
                            __( 'Persian', 'magic-post-thumbnail' )               => 'fa',
                            __( 'Polish', 'magic-post-thumbnail' )                => 'pl',
                            __( 'Portuguese (Brazil)', 'magic-post-thumbnail' )   => 'pt-BR',
                            __( 'Portuguese (Portugal)', 'magic-post-thumbnail' ) => 'pt-PT',
                            __( 'Punjabi', 'magic-post-thumbnail' )               => 'pa',
                            __( 'Romanian', 'magic-post-thumbnail' )              => 'ro',
                            __( 'Russian', 'magic-post-thumbnail' )               => 'ru',
                            __( 'Scots Gaelic', 'magic-post-thumbnail' )          => 'gd',
                            __( 'Serbian', 'magic-post-thumbnail' )               => 'sr',
                            __( 'Sinhalese', 'magic-post-thumbnail' )             => 'si',
                            __( 'Slovak', 'magic-post-thumbnail' )                => 'sk',
                            __( 'Slovenian', 'magic-post-thumbnail' )             => 'sl',
                            __( 'Spanish', 'magic-post-thumbnail' )               => 'es',
                            __( 'Sudanese', 'magic-post-thumbnail' )              => 'su',
                            __( 'Swahili', 'magic-post-thumbnail' )               => 'sw',
                            __( 'Swedish', 'magic-post-thumbnail' )               => 'sv',
                            __( 'Tagalog', 'magic-post-thumbnail' )               => 'tl',
                            __( 'Tamil', 'magic-post-thumbnail' )                 => 'ta',
                            __( 'Telugu', 'magic-post-thumbnail' )                => 'te',
                            __( 'Thai', 'magic-post-thumbnail' )                  => 'th',
                            __( 'Tigrinya', 'magic-post-thumbnail' )              => 'ti',
                            __( 'Turkish', 'magic-post-thumbnail' )               => 'tr',
                            __( 'Ukrainian', 'magic-post-thumbnail' )             => 'uk',
                            __( 'Urdu', 'magic-post-thumbnail' )                  => 'ur',
                            __( 'Uzbek', 'magic-post-thumbnail' )                 => 'uz',
                            __( 'Vietnamese', 'magic-post-thumbnail' )            => 'vi',
                            __( 'Welsh', 'magic-post-thumbnail' )                 => 'cy',
                            __( 'Xhosa', 'magic-post-thumbnail' )                 => 'xh',
                            __( 'Zulu', 'magic-post-thumbnail' )                  => 'zu',
                        );
                        ksort( $country_choose );

                        foreach( $country_choose as $name_country => $code_country ) {
                            $choose = ( $alt_lang == $code_country) ? 'selected="selected"': '';
                            echo '<option '. $choose .' value="'. $code_country .'">'. $name_country .'</option>';
                        }
                    ?>
                </select>

        </td>
    </tr>


<?php } } else { ?>
    <tr valign="top" class="based_on_bottom">
        <th scope="row">
            <label for="hseparator">
                <?php esc_html_e( 'Add ALT Attribute to Image', 'magic-post-thumbnail' ); ?><br/>
                <small><?php esc_html_e( 'Only available with the pro version', 'magic-post-thumbnail' ); ?></small>
            </label>
        </th>
        <td>
            <label class="checkbox checkbox-disabled checkbox-admin">
                <input disabled="disabled" data-switch="true" type="checkbox" name="MPT_plugin_main_settings[enable_alt]" id="enable_alt" value="disable" />
            </label>
        </td>
    </tr>
<?php } ?>


<tr valign="top" class="based_on_bottom">
    <th scope="row">
        <label for="hseparator"><?php esc_html_e( 'Add caption tag on image', 'magic-post-thumbnail' ); ?></label>
    </th>
    <td>
        <label class="checkbox">
            <input data-switch="true" type="checkbox" name="MPT_plugin_main_settings[enable_caption]" id="enable_caption" value="enable" <?php echo( !empty( $options['enable_caption']) && $options['enable_caption'] == 'enable' )? 'checked': ''; ?> />
        </label>
    </td>
</tr>

<tr valign="top" class="show_caption" <?php echo( !isset( $options['enable_caption'] ) || ( $options['enable_caption'] != 'enable' ) ? 'style="display:none;"': ''); ?>>
    <th scope="row">
            <label for="hseparator"><?php esc_html_e( 'Caption Format', 'magic-post-thumbnail' ); ?></label>
    </th>
    <td class="tags radio-list">
        <label class="radio">
            <input value="author" name="MPT_plugin_main_settings[caption_from]" type="radio" <?php echo( !empty( $options['caption_from']) && $options['caption_from'] == 'author' )? 'checked': ''; ?>><span></span> 
            <?php esc_html_e( 'Image Author', 'magic-post-thumbnail' ); ?>
        </label>
        <label class="radio">
            <input value="author_bank" name="MPT_plugin_main_settings[caption_from]" type="radio" <?php echo( !empty( $options['caption_from']) && $options['caption_from'] == 'author_bank' )? 'checked': ''; ?>> <span></span>
            <?php _e( 'Image Author + Name of Image Bank (Example: <em>Author Name from Pixabay</em>)', 'magic-post-thumbnail' ); ?>
        </label>
    </td>
</tr>