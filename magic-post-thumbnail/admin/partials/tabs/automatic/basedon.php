<?php
if ( ! function_exists( 'add_filter' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
		exit();


}
?>
<tr valign="top" class="section_basedon image-location-template image-block-<?php echo $blockIndex; ?> mid-add-block-img">
        <th scope="row">
                <label for="hseparator"><?php esc_html_e( 'Search Based on', 'magic-post-thumbnail' ); ?></label>
        </th>

        <td class="based_on radio-list">
                <select name="MPT_plugin_main_settings[image_block][<?php echo $blockIndex; ?>][based_on]" class="select-custom-location form-control">
                        <option value="title" <?php selected(isset($block['based_on']) ? $block['based_on'] : '', 'title'); ?>><?php esc_html_e( 'Title', 'magic-post-thumbnail' ); ?></option>
                        <option value="text_analyser" <?php selected(isset($block['based_on']) ? $block['based_on'] : '', 'text_analyser'); ?>><?php esc_html_e( 'Text Analyzer: Full text', 'magic-post-thumbnail' ); ?></option>
                        <option value="text_analyser_previous_paragraph" <?php selected(isset($block['based_on']) ? $block['based_on'] : '', 'text_analyser_previous_paragraph'); ?> <?php echo $disabled; ?> class="option_analyzer <?php echo $class_analyzer; ?>"><?php esc_html_e( 'Text Analyzer: Previous paragraph', 'magic-post-thumbnail' ); ?></option>
                        <option value="text_analyser_next_paragraph" <?php selected(isset($block['based_on']) ? $block['based_on'] : '', 'text_analyser_next_paragraph'); ?> <?php echo $disabled; ?> class="option_analyzer <?php echo $class_analyzer; ?>"><?php esc_html_e( 'Text Analyzer: Next paragraph', 'magic-post-thumbnail' ); ?></option>
                        <option value="tags" <?php selected(isset($block['based_on']) ? $block['based_on'] : '', 'tags'); ?> <?php echo $disabled; ?>><?php esc_html_e( 'Tags', 'magic-post-thumbnail' ); ?></option>
                        <option value="categories" <?php selected(isset($block['based_on']) ? $block['based_on'] : '', 'categories'); ?> <?php echo $disabled; ?>><?php esc_html_e( 'Categories', 'magic-post-thumbnail' ); ?></option>
                        <option value="custom_field" <?php selected(isset($block['based_on']) ? $block['based_on'] : '', 'custom_field'); ?> <?php echo $disabled; ?>><?php esc_html_e( 'Custom Field', 'magic-post-thumbnail' ); ?></option>
                        <option value="custom_request" <?php selected(isset($block['based_on']) ? $block['based_on'] : '', 'custom_request'); ?> <?php echo $disabled; ?>><?php esc_html_e( 'Custom Request', 'magic-post-thumbnail' ); ?></option>
                        <option value="openai_extractor" <?php selected(isset($block['based_on']) ? $block['based_on'] : '', 'openai_extractor'); ?> <?php echo $disabled; ?>><?php esc_html_e( 'OpenAI Keyword Extractor', 'magic-post-thumbnail' ); ?></option>
                </select>
        </td>
</tr>

<?php 
    if ( true === $this->MPT_freemius()->is__premium_only() ) { 
        if ( $this->mpt_freemius()->can_use_premium_code() ) {
?>

    <tr valign="top" class="section_tags image-location-template image-block-<?php echo $blockIndex; ?> mid-add-block-img" <?php echo(($block['based_on'] != 'tags') ? 'style="display:none;"': ''); ?>>
            <th scope="row">
                    <label for="hseparator"><?php esc_html_e( 'Tags', 'magic-post-thumbnail' ); ?></label>
            </th>
            <td class="radio-list tags">
                    <label class="radio radio-outline radio-outline-2x radio-primary"><input value="first_tag" name="MPT_plugin_main_settings[image_block][<?php echo $blockIndex; ?>][tags]" type="radio" <?php echo( !empty( $block['tags']) && $block['tags'] == 'first_tag' )? 'checked': ''; ?> ><span></span> <?php esc_html_e( 'First tag', 'magic-post-thumbnail' ); ?></label>
                    <label class="radio radio-outline radio-outline-2x radio-primary"><input value="last_tag" name="MPT_plugin_main_settings[image_block][<?php echo $blockIndex; ?>][tags]" type="radio" <?php echo( !empty( $block['tags']) && $block['tags'] == 'last_tag' )? 'checked': ''; ?>><span></span> <?php esc_html_e( 'Last tag', 'magic-post-thumbnail' ); ?></label>
                    <label class="radio radio-outline radio-outline-2x radio-primary"><input value="random_tag" name="MPT_plugin_main_settings[image_block][<?php echo $blockIndex; ?>][tags]" type="radio" <?php echo( !empty( $block['tags']) && $block['tags'] == 'random_tag' )? 'checked': ''; ?>><span></span> <?php esc_html_e( 'Random tag', 'magic-post-thumbnail' ); ?></label>
            </td>
    </tr>

    <tr valign="top" class="section_categories image-location-template image-block-<?php echo $blockIndex; ?> mid-add-block-img" <?php echo(($block['based_on'] != 'categories') ? 'style="display:none;"': ''); ?>>
            <th scope="row">
                    <label for="hseparator"><?php esc_html_e( 'Categories', 'magic-post-thumbnail' ); ?></label>
            </th>
            <td class="radio-list categories">
                    <label class="radio radio-outline radio-outline-2x radio-primary"><input value="first_category" name="MPT_plugin_main_settings[image_block][<?php echo $blockIndex; ?>][categories]" type="radio" <?php echo( !empty( $block['categories']) && $block['categories'] == 'first_category' )? 'checked': ''; ?> ><span></span> <?php esc_html_e( 'First category', 'magic-post-thumbnail' ); ?></label>
                    <label class="radio radio-outline radio-outline-2x radio-primary"><input value="last_category" name="MPT_plugin_main_settings[image_block][<?php echo $blockIndex; ?>][categories]" type="radio" <?php echo( !empty( $block['categories']) && $block['categories'] == 'last_category' )? 'checked': ''; ?>><span></span> <?php esc_html_e( 'Last category', 'magic-post-thumbnail' ); ?></label>
                    <label class="radio radio-outline radio-outline-2x radio-primary"><input value="random_category" name="MPT_plugin_main_settings[image_block][<?php echo $blockIndex; ?>][categories]" type="radio" <?php echo( !empty( $block['categories']) && $block['categories'] == 'random_category' )? 'checked': ''; ?>><span></span> <?php esc_html_e( 'Random category', 'magic-post-thumbnail' ); ?></label>
            </td>
    </tr>

    <tr valign="top" class="section_custom_field image-location-template image-block-<?php echo $blockIndex; ?> mid-add-block-img" <?php echo(($block['based_on'] != 'custom_field') ? 'style="display:none;"': ''); ?>>
            <th scope="row">
                    <label for="hseparator"><?php esc_html_e( 'Custom field Name', 'magic-post-thumbnail' ); ?></label>
            </th>
            <td class="custom_field">
                    <label><input type="text" name="MPT_plugin_main_settings[image_block][<?php echo $blockIndex; ?>][custom_field]" class="form-control" value="<?php echo( isset( $block['custom_field'] ) && !empty( $block['custom_field']) )? esc_attr( $block['custom_field']): ''; ?>" ></label>
            </td>
    </tr>

    <tr valign="top" class="section_custom_request image-location-template image-block-<?php echo $blockIndex; ?> mid-add-block-img" <?php echo(($block['based_on'] != 'custom_request') ? 'style="display:none;"': ''); ?>>

            <th scope="row">
                    <label for="hseparator"><?php esc_html_e( 'Custom Request', 'magic-post-thumbnail' ); ?></label>
            </th>
            <td class="custom_field">

                <div id="custom-request-buttons">
                    <p draggable="true"><span class="button-custom" draggable="false" data-token="Title"><?php esc_html_e( 'Title', 'magic-post-thumbnail' ); ?></span></p>
                    <p draggable="true"><span class="button-custom" draggable="false" data-token="Category"><?php esc_html_e( 'Category', 'magic-post-thumbnail' ); ?></span></p>
                    <p draggable="true"><span class="button-custom" draggable="false" data-token="Tag"><?php esc_html_e( 'Tag', 'magic-post-thumbnail' ); ?></span></p>
                    <p draggable="true"><span class="button-custom" draggable="false" data-token="Taxonomy"><?php esc_html_e( 'Taxonomy', 'magic-post-thumbnail' ); ?></span></p>
                </div>

                <div class="textarea-editable" contenteditable="true"><?php echo( isset( $block['custom_request'] ) && !empty( $block['custom_request']) )? $block['custom_request']: esc_html_e( 'This is a simple request including the %%Title%%', 'magic-post-thumbnail' ); ?></div>
                <label>
                    <input type="hidden" class="custom_request" name="MPT_plugin_main_settings[image_block][<?php echo $blockIndex; ?>][custom_request]" class="form-control" value="<?php echo( isset( $block['custom_request'] ) && !empty( $block['custom_request']) )? esc_html( $block['custom_request'] ): ''; ?>" >
                </label>

            </td>
    </tr>

    <tr valign="top" class="category_choice based_on_bottom image-location-template image-block-<?php echo $blockIndex; ?> mid-add-block-img" <?php echo(($block['based_on'] != 'custom_request') ? 'style="display:none;"': ''); ?>>
        <th scope="row">
                <label for="hseparator"><?php esc_html_e( 'Category Level', 'magic-post-thumbnail' ); ?></label>
                <p class="description">
                    <?php esc_html_e( 'Choose the category level to use.', 'magic-post-thumbnail' ); ?>
                </p>
        </th>
        <td class="result_position radio-inline">
            <label  class="radio radio-outline radio-outline-2x radio-primary"><input value="first_level" name="MPT_plugin_main_settings[image_block][<?php echo $blockIndex; ?>][category_choice] " type="radio" <?php echo( !empty( $block['category_choice']) && $block['category_choice'] == 'first_level' )? 'checked': ''; ?> ><span></span> <?php esc_html_e( 'Child category', 'magic-post-thumbnail' ); ?></label><br/>
            <label  class="radio radio-outline radio-outline-2x radio-primary <?php echo $class_disabled; ?>"><input value="second_level" name="MPT_plugin_main_settings[image_block][<?php echo $blockIndex; ?>][category_choice] " type="radio" <?php echo( !empty( $block['category_choice']) && $block['category_choice'] == 'second_level' )? 'checked': ''; echo $disabled ?> ><span></span> <?php esc_html_e( 'Parent category', 'magic-post-thumbnail' ); ?></label>
            <label  class="radio radio-outline radio-outline-2x radio-primary <?php echo $class_disabled; ?>"><input value="third_level" name="MPT_plugin_main_settings[image_block][<?php echo $blockIndex; ?>][category_choice] " type="radio" <?php echo( !empty( $block['category_choice']) && $block['category_choice'] == 'third_level' )? 'checked': ''; echo $disabled ?> ><span></span> <?php esc_html_e( 'Grandparent category', 'magic-post-thumbnail' ); ?></label>                                        
        </td>
    </tr>


        <tr valign="top" class="category_choice based_on_bottom image-location-template image-block-<?php echo $blockIndex; ?> mid-add-block-img" <?php echo(($block['based_on'] != 'custom_request') ? 'style="display:none;"': ''); ?>>
                <th scope="row">
                <label for="hseparator"><?php esc_html_e( 'Taxonomy Slug', 'magic-post-thumbnail' ); ?></label>
                </th>
                <td class="custom_taxo_field" valign="top">
                <input type="text" name="MPT_plugin_main_settings[image_block][<?php echo $blockIndex; ?>][taxonomy_field]" class="col-lg-4 col-md-9 col-sm-12 form-control" value="<?php echo( isset( $block['taxonomy_field'] ) && !empty( $block['taxonomy_field']) )? esc_attr( $block['taxonomy_field']): ''; ?>" >
                </td>
        </tr>

    

    <tr valign="top" class="category_choice based_on_bottom image-location-template image-block-<?php echo $blockIndex; ?> mid-add-block-img" <?php echo(($block['based_on'] != 'custom_request') ? 'style="display:none;"': ''); ?>>
        <th scope="row">
                <label for="hseparator"><?php esc_html_e( 'Taxonomy Level', 'magic-post-thumbnail' ); ?></label>
                <p class="description">
                    <?php esc_html_e( 'Choose the taxonomy level to use.', 'magic-post-thumbnail' ); ?>
                </p>
        </th>
        <td class="result_position radio-inline">
            <label  class="radio radio-outline radio-outline-2x radio-primary"><input value="first_level" name="MPT_plugin_main_settings[image_block][<?php echo $blockIndex; ?>][taxonomy_choice] " type="radio" <?php echo( !empty( $block['taxonomy_choice']) && $block['taxonomy_choice'] == 'first_level' )? 'checked': ''; ?> ><span></span> <?php esc_html_e( 'Child taxonomy', 'magic-post-thumbnail' ); ?></label><br/>
            <label  class="radio radio-outline radio-outline-2x radio-primary <?php echo $class_disabled; ?>"><input value="second_level" name="MPT_plugin_main_settings[image_block][<?php echo $blockIndex; ?>][taxonomy_choice] " type="radio" <?php echo( !empty( $block['taxonomy_choice']) && $block['taxonomy_choice'] == 'second_level' )? 'checked': ''; echo $disabled ?> ><span></span> <?php esc_html_e( 'Parent taxonomy', 'magic-post-thumbnail' ); ?></label>
            <label  class="radio radio-outline radio-outline-2x radio-primary <?php echo $class_disabled; ?>"><input value="third_level" name="MPT_plugin_main_settings[image_block][<?php echo $blockIndex; ?>][taxonomy_choice] " type="radio" <?php echo( !empty( $block['taxonomy_choice']) && $block['taxonomy_choice'] == 'third_level' )? 'checked': ''; echo $disabled ?> ><span></span> <?php esc_html_e( 'Grandparent taxonomy', 'magic-post-thumbnail' ); ?></label>                                        
        </td>
    </tr>

    <tr valign="top" class="section_openai_extractor image-location-template image-block-<?php echo $blockIndex; ?> mid-add-block-img" <?php echo(($block['based_on'] != 'openai_extractor') ? 'style="display:none;"': ''); ?>>
            <th scope="row">
                    <label for="hseparator"><?php esc_html_e( 'OpenAI API Key', 'magic-post-thumbnail' ); ?></label>
            </th>
            <td id="password-openai" class="password">
                <input type="password" name="MPT_plugin_main_settings[image_block][<?php echo $blockIndex; ?>][openai_extractor_apikey]" class="form-control" value="<?php echo( isset( $block['openai_extractor_apikey'] ) && !empty( $block['openai_extractor_apikey']) )? esc_attr( $block['openai_extractor_apikey'] ): ''; ?>">
                <i id="togglePassword"></i>
            </td>
    </tr>

        <tr valign="top" class="section_openai_extractor image-location-template image-block-<?php echo $blockIndex; ?> mid-add-block-img" <?php echo(($block['based_on'] != 'openai_extractor') ? 'style="display:none;"': ''); ?>>
                <th scope="row">
                        <label for="hseparator"><?php esc_html_e( 'Number of keywords to extract from title', 'magic-post-thumbnail' ); ?></label>
                </th>
                <td class="number_of_keywords radio-inline">
                        <label  class="radio radio-outline radio-outline-2x radio-primary"><input value="1-2" name="MPT_plugin_main_settings[image_block][<?php echo $blockIndex; ?>][openai_number_of_keywords] " type="radio" <?php echo( !empty( $block['openai_number_of_keywords']) && $block['openai_number_of_keywords'] == '1-2'  )? 'checked': ''; ?> ><span></span> <?php esc_html_e( 'From 1 to 2 words', 'magic-post-thumbnail' ); ?></label><br/>
                        <label  class="radio radio-outline radio-outline-2x radio-primary"><input value="2"   name="MPT_plugin_main_settings[image_block][<?php echo $blockIndex; ?>][openai_number_of_keywords] " type="radio" <?php echo( !empty( $block['openai_number_of_keywords']) && $block['openai_number_of_keywords'] == '2'   )?  'checked': ''; ?> ><span></span> <?php esc_html_e( '2 words', 'magic-post-thumbnail' ); ?></label><br/>
                        <label  class="radio radio-outline radio-outline-2x radio-primary"><input value="3"   name="MPT_plugin_main_settings[image_block][<?php echo $blockIndex; ?>][openai_number_of_keywords] " type="radio" <?php echo( !empty( $block['openai_number_of_keywords']) && $block['openai_number_of_keywords'] == '3' )?    'checked': ''; ?> ><span></span> <?php esc_html_e( '3 words', 'magic-post-thumbnail' ); ?></label>
                </td>
        </tr>

<?php 
        } 
    }
?>

<tr valign="top" class="section_title image-location-template image-block-<?php echo $blockIndex; ?> mid-add-block-img" <?php echo(($block['based_on'] != 'title') ? 'style="display:none;"': ''); ?>>
        <th scope="row">
                <label for="hseparator"><?php esc_html_e( 'Title', 'magic-post-thumbnail' ); ?></label>
        </th>
        <td class="chosen_title radio-inline">
                <label class="radio radio-outline radio-outline-2x radio-primary"><input value="full_title" name="MPT_plugin_main_settings[image_block][<?php echo $blockIndex; ?>][title_selection] " type="radio" <?php echo( !empty( $block['title_selection']) && $block['title_selection'] == 'full_title' )? 'checked': ''; ?> ><span></span> <?php esc_html_e( 'Full title', 'magic-post-thumbnail' ); ?></label><br/>
                <label class="radio radio-outline radio-outline-2x radio-primary"><input value="cut_title" name="MPT_plugin_main_settings[image_block][<?php echo $blockIndex; ?>][title_selection] " type="radio" <?php echo( !empty( $block['title_selection']) && $block['title_selection'] == 'cut_title' )? 'checked': ''; ?>><span></span> <?php esc_html_e( 'Specific Part', 'magic-post-thumbnail' ); ?> : </label>
                <input type="number" name="MPT_plugin_main_settings[image_block][<?php echo $blockIndex; ?>][title_length]" min="1" class="col-lg-4 col-md-9 col-sm-12 form-control length_cut_title" value="<?php echo( isset( $block['title_length'] ) && !empty( $block['title_length']) )? (int)$block['title_length']: '3'; ?>" <?php echo( !empty( $block['title_selection']) && $block['title_selection'] == 'cut_title' )? '': 'disabled'; ?>> <i><?php esc_html_e( 'first words of the title', 'magic-post-thumbnail' ); ?></i>
        </td>
</tr>

<tr valign="top" class="section_text_analyser image-location-template image-block-<?php echo $blockIndex; ?> mid-add-block-img" 
<?php echo(($block['based_on'] != 'text_analyser') && ($block['based_on'] != 'text_analyser_previous_paragraph') && ($block['based_on'] != 'text_analyser_next_paragraph') ? 'style="display:none;"': ''); ?>>
        <th scope="row">
                <label for="hseparator"><?php esc_html_e( 'Post Content Language', 'magic-post-thumbnail' ); ?></label>
        </th>
        <td class="text_analyser">
                <select name="MPT_plugin_main_settings[image_block][<?php echo $blockIndex; ?>][text_analyser_lang]" class="form-control form-control-lg" >
                    <?php
                    if( isset( $block['text_analyser_lang'] ) && $block['text_analyser_lang'] ) {
                        $current_wp_lang = $block['text_analyser_lang'];
                    } else {
                        $current_wp_lang = explode('-', get_bloginfo('language'));
                        $current_wp_lang = $current_wp_lang[0];
                    }

                    $langs = array(
                            esc_html__( '-- Default --', 'magic-post-thumbnail' )    => '',
                            esc_html__( 'Arabic', 'magic-post-thumbnail' )           => 'ar',
                            esc_html__( 'Bulgarian', 'magic-post-thumbnail' )        => 'bg',
                            esc_html__( 'Czech', 'magic-post-thumbnail' )            => 'cs',
                            esc_html__( 'Danish', 'magic-post-thumbnail' )           => 'da',
                            esc_html__( 'German', 'magic-post-thumbnail' )           => 'de',
                            esc_html__( 'Greek', 'magic-post-thumbnail' )            => 'el',
                            esc_html__( 'English', 'magic-post-thumbnail' )          => 'en',
                            esc_html__( 'Spanish', 'magic-post-thumbnail' )          => 'es',
                            esc_html__( 'Estonian', 'magic-post-thumbnail' )         => 'et',
                            esc_html__( 'Persian', 'magic-post-thumbnail' )          => 'fa',
                            esc_html__( 'Finnish', 'magic-post-thumbnail' )          => 'fi',
                            esc_html__( 'French', 'magic-post-thumbnail' )           => 'fr',
                            esc_html__( 'Hebrew', 'magic-post-thumbnail' )           => 'he',
                            esc_html__( 'Hindi', 'magic-post-thumbnail' )            => 'hi',
                            esc_html__( 'Croatian', 'magic-post-thumbnail' )         => 'hr',
                            esc_html__( 'Hungarian', 'magic-post-thumbnail' )        => 'hu',
                            esc_html__( 'Armenian', 'magic-post-thumbnail' )         => 'hy',
                            esc_html__( 'Indonesian', 'magic-post-thumbnail' )       => 'id',
                            esc_html__( 'Italian', 'magic-post-thumbnail' )          => 'it',
                            esc_html__( 'Japanese', 'magic-post-thumbnail' )         => 'ja',
                            esc_html__( 'Korean', 'magic-post-thumbnail' )           => 'ko',
                            esc_html__( 'Lithuanian', 'magic-post-thumbnail' )       => 'lt',
                            esc_html__( 'Latvian', 'magic-post-thumbnail' )          => 'lv',
                            esc_html__( 'Dutch', 'magic-post-thumbnail' )            => 'nl',
                            esc_html__( 'Norwegian', 'magic-post-thumbnail' )        => 'no',
                            esc_html__( 'Polish', 'magic-post-thumbnail' )           => 'pl',
                            esc_html__( 'Portuguese', 'magic-post-thumbnail' )       => 'pt',
                            esc_html__( 'Romanian', 'magic-post-thumbnail' )         => 'ro',
                            esc_html__( 'Russian', 'magic-post-thumbnail' )          => 'ru',
                            esc_html__( 'Slovak', 'magic-post-thumbnail' )           => 'sk',
                            esc_html__( 'Slovenian', 'magic-post-thumbnail' )        => 'sl',
                            esc_html__( 'Swedish', 'magic-post-thumbnail' )          => 'sv',
                            esc_html__( 'Thai', 'magic-post-thumbnail' )             => 'th',
                            esc_html__( 'Turkish', 'magic-post-thumbnail' )          => 'tr',
                            esc_html__( 'Vietnamese', 'magic-post-thumbnail' )       => 'vi',
                            esc_html__( 'Chinese', 'magic-post-thumbnail' )          => 'zh',
                    );
                    ksort( $langs );

                    foreach( $langs as $name_lang => $code_lang ) {
                            $choose=($current_wp_lang == $code_lang)?'selected="selected"': '';
                            echo '<option '. $choose .' value="'. $code_lang .'">'. $name_lang .'</option>';
                    }
                    ?>
            </select>
        </td>
</tr>

<tr valign="top" class="translation_EN image-location-template image-block-<?php echo $blockIndex; ?> mid-add-block-img">
    <th scope="row">
            <?php esc_html_e( 'Translate to English', 'magic-post-thumbnail' ); ?>
    </th>
    <td class="checkbox-list">
        <label class="checkbox <?php echo $checkbox_disabled; ?>"><input <?php echo( !empty( $block['translation_EN']) && $block['translation_EN'] == 'true' )? 'checked': ''; ?> name="MPT_plugin_main_settings[image_block][<?php echo $blockIndex; ?>][translation_EN]" type="checkbox" value="true"> <span></span> <?php esc_html_e( 'Translate', 'magic-post-thumbnail' ); ?></label>
        <p class="description">
            <?php esc_html_e( 'The "based on" phrase /keywords will be translated into English. This helps to get better results with most image databases.', 'magic-post-thumbnail' ); ?>
        </p>
    </td>
</tr>

<?php include dirname( __FILE__ ) . '/style.php'; ?>

<tr valign="top" class="selected_image based_on_bottom image-location-template image-block-<?php echo $blockIndex; ?> mid-add-block-img">
        <th scope="row">
                <label for="hseparator"><?php esc_html_e( 'Image Selection', 'magic-post-thumbnail' ); ?></label>
        </th>
        <td class="result_position radio-inline">
            <label  class="radio radio-outline radio-outline-2x radio-primary"><input value="first_result" name="MPT_plugin_main_settings[image_block][<?php echo $blockIndex; ?>][selected_image] " type="radio" <?php echo( !empty( $block['selected_image']) && $block['selected_image'] == 'first_result' )? 'checked': ''; ?> ><span></span> <?php esc_html_e( 'First result', 'magic-post-thumbnail' ); ?></label><br/>
            <label  class="radio radio-outline radio-outline-2x radio-primary"><input value="random_result" name="MPT_plugin_main_settings[image_block][<?php echo $blockIndex; ?>][selected_image] " type="radio" <?php echo( !empty( $block['selected_image']) && $block['selected_image'] == 'random_result' )? 'checked': ''; ?> ><span></span> <?php esc_html_e( 'Random result', 'magic-post-thumbnail' ); ?></label>
        </td>
</tr>