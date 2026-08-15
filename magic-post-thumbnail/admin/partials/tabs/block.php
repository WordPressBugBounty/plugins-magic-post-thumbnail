<?php

if ( !function_exists( 'add_filter' ) ) {
    header( 'Status: 403 Forbidden' );
    header( 'HTTP/1.1 403 Forbidden' );
    exit;
}
?>
<div class="wrap">

	<?php 
settings_errors();
?>

    <form method="post" action="options.php" id="tabs">

	      <?php 
settings_fields( 'MPT-plugin-block-settings' );
$options = wp_parse_args( get_option( 'MPT_plugin_block_settings' ), $this->MPT_default_options_block_settings( TRUE ) );
$banks_options = wp_parse_args( get_option( 'MPT_plugin_banks_settings' ), $this->MPT_default_options_banks_settings( false ) );
$list_api_manual = $this->MPT_banks_name_manual();
$source_url = remove_query_arg( 'ids_mpt_generation', add_query_arg( 'module', 'source', $this->MPT_current_url() ) );
$saved_manual = array();
if ( !empty( $banks_options['api_chosen_manual'] ) && is_array( $banks_options['api_chosen_manual'] ) ) {
    foreach ( $banks_options['api_chosen_manual'] as $saved_slug ) {
        $saved_manual[] = $this->MPT_normalize_bank_slug( ( is_string( $saved_slug ) ? $saved_slug : '' ) );
    }
}
$order_manual = array();
if ( !empty( $banks_options['api_chosen_manual'] ) && is_array( $banks_options['api_chosen_manual'] ) ) {
    foreach ( array_keys( $banks_options['api_chosen_manual'] ) as $saved_key ) {
        $order_manual[] = $this->MPT_normalize_bank_slug( $saved_key );
    }
}
?>
	          <table id="general-options" class="form-table tabs-content">
	              <tbody>
	                  <tr>
	                    <td>
                          <?php 
esc_html_e( 'Display manual search for featured image ', 'magic-post-thumbnail' );
?>
	                    </td>
	                    <td>
	                      <label class="checkbox">
	                          <input data-switch="true" type="checkbox" name="MPT_plugin_block_settings[enable_manual_search]" id="enable_manual_search" value="true" <?php 
echo ( !empty( $options['enable_manual_search'] ) && $options['enable_manual_search'] == 'true' ? 'checked' : '' );
?> />
	                      </label>
	                    </td>
	                  </tr>

					  <tr valign="top">
						<th scope="row">
							<label><?php 
esc_html_e( 'Gutenberg block (manual search)', 'magic-post-thumbnail' );
?></label>
						</th>
						<td class="chosen_api checkbox-list">
							<p class="description"><?php 
esc_html_e( 'Banks shown as tabs when you search images from the Gutenberg block.', 'magic-post-thumbnail' );
?></p>
							<p class="description">
								<?php 
printf( 
    /* translators: %s: link to the Source settings page */
    esc_html__( 'API keys, sizes and filters are configured in %s.', 'magic-post-thumbnail' ),
    '<a href="' . esc_url( $source_url ) . '">' . esc_html__( 'Source', 'magic-post-thumbnail' ) . '</a>'
 );
?>
							</p>
							<ul class="radio-list" id="mpt-sortable-manual">
								<?php 
$default_bank_order = 99;
$ar_list_banks_manual = array();
foreach ( $list_api_manual as $api => $api_code ) {
    $slug = $api_code[0];
    $checked = ( in_array( $slug, $saved_manual, true ) ? 'checked="checked"' : '' );
    $key_api_chosen = array_search( $slug, $order_manual, true );
    if ( false === $key_api_chosen ) {
        $key_api_chosen = $default_bank_order;
        $default_bank_order++;
    }
    $disabled = 'disabled';
    $class_disabled = 'checkbox-disabled';
    if ( true === $api_code[1] ) {
        $disabled = '';
        $class_disabled = '';
    }
    if ( 'disabled' === $disabled ) {
        $checked = '';
    }
    $ar_list_banks_manual[$key_api_chosen] = '<li><label class="checkbox ' . $class_disabled . '"><input class="ordered-checkbox" data-order="' . (int) $key_api_chosen . '" type="checkbox" ' . $checked . ' ' . $disabled . ' value="' . esc_attr( $slug ) . '" name="MPT_plugin_banks_settings[api_chosen_manual][' . esc_attr( $slug ) . ']"> <span></span> ' . esc_html( $api ) . '</label></li>';
}
ksort( $ar_list_banks_manual );
foreach ( $ar_list_banks_manual as $ar_list_banks_val ) {
    echo $ar_list_banks_val;
    // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}
?>
							</ul>
						</td>
					  </tr>

					  <?php 
// Alt Tag
if ( true === $this->MPT_freemius()->is__premium_only() ) {
    if ( $this->mpt_freemius()->can_use_premium_code() ) {
        ?>

							<tr valign="top" class="based_on_bottom">
								<th scope="row">
									<label for="hseparator"><?php 
        esc_html_e( 'Add alt tag on image', 'magic-post-thumbnail' );
        ?></label>
								</th>
								<td>
									<label class="checkbox">
										<input data-switch="true" type="checkbox" name="MPT_plugin_block_settings[enable_alt]" id="enable_alt" value="enable" <?php 
        echo ( !empty( $options['enable_alt'] ) && $options['enable_alt'] == 'enable' ? 'checked' : '' );
        ?> />
									</label>
								</td>
							</tr>

							<?php 
        if ( !isset( $options['translate_alt_lang'] ) ) {
            $wp_lang = get_bloginfo( 'language' );
            $alt_lang = substr( $wp_lang, 0, 2 );
        } else {
            $alt_lang = $options['translate_alt_lang'];
        }
        ?>

							<tr valign="top" class="show_alt" <?php 
        echo ( isset( $options['enable_alt'] ) && $options['enable_alt'] != 'enable' ? 'style="display:none;"' : '' );
        ?>>
								<th scope="row">
										<?php 
        esc_html_e( 'Translation', 'magic-post-thumbnail' );
        ?>
								</th>
								<td class="checkbox-list">
										<label class="checkbox">
											<input name="MPT_plugin_block_settings[translate_alt]" type="checkbox" value="true" <?php 
        echo ( !empty( $options['translate_alt'] ) && $options['translate_alt'] == 'true' ? 'checked' : '' );
        ?>><span></span> 
											<?php 
        esc_html_e( 'Translate alt text from english to', 'magic-post-thumbnail' );
        ?>:
										</label>

										<select name="MPT_plugin_block_settings[translate_alt_lang]" class="form-control form-control-lg" >
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
        foreach ( $country_choose as $name_country => $code_country ) {
            $choose = ( $alt_lang == $code_country ? 'selected="selected"' : '' );
            echo '<option ' . $choose . ' value="' . $code_country . '">' . $name_country . '</option>';
        }
        ?>
										</select>

								</td>
							</tr>


						<?php 
    }
} else {
    ?>
							<tr valign="top" class="based_on_bottom">
								<th scope="row">
									<label for="hseparator">
										<?php 
    esc_html_e( 'Add alt tag on image', 'magic-post-thumbnail' );
    ?><br/>
										<small><?php 
    esc_html_e( 'Only available with the pro version', 'magic-post-thumbnail' );
    ?></small>
									</label>
								</th>
								<td>
									<label class="checkbox checkbox-disabled checkbox-admin">
										<input disabled="disabled" data-switch="true" type="checkbox" name="MPT_plugin_block_settings[enable_alt]" id="enable_alt" value="disable" />
									</label>
								</td>
							</tr>
						<?php 
}
?>


						<?php 
// Caption Tag
if ( true === $this->MPT_freemius()->is__premium_only() ) {
    if ( $this->mpt_freemius()->can_use_premium_code() ) {
        ?>

							<tr valign="top" class="based_on_bottom">
								<th scope="row">
									<label for="hseparator"><?php 
        esc_html_e( 'Add caption tag on image', 'magic-post-thumbnail' );
        ?></label>
								</th>
								<td>
									<label class="checkbox">
										<input data-switch="true" type="checkbox" name="MPT_plugin_block_settings[enable_caption]" id="enable_caption" value="enable" <?php 
        echo ( !empty( $options['enable_caption'] ) && $options['enable_caption'] == 'enable' ? 'checked' : '' );
        ?> />
									</label>
								</td>
							</tr>

						<?php 
    }
}
?>


	              </tbody>
	          </table>
	      <?php 
submit_button();
?>
						
    </form>
</div>
