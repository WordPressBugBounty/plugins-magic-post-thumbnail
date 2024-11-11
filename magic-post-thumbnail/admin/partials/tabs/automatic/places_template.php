<?php

if ( !function_exists( 'add_filter' ) ) {
    header( 'Status: 403 Forbidden' );
    header( 'HTTP/1.1 403 Forbidden' );
    exit;
}
?>
<tr>
    <td colspan="2">
        <hr/>
    </td>
</tr>


<tr valign="top" class="image-location-template hidden image-block-0 top-add-block-img">
    <th scope="row">
        <?php 
esc_html_e( 'Featured Image / Inline Content', 'mpt' );
?>
    </th>
    <td class="image_location radio-list">
        <label class="radio radio-outline radio-outline-2x radio-primary">
            <input value="featured" name="MPT_plugin_main_settings[image_block][0][image_location]" type="radio" checked>
            <span></span> <?php 
esc_html_e( 'Featured Image', 'mpt' );
?>
        </label>
        <label class="radio radio-outline radio-outline-2x radio-primary">
            <input value="custom" name="MPT_plugin_main_settings[image_block][0][image_location]" type="radio">
            <span></span> <?php 
esc_html_e( 'Inline content', 'mpt' );
?>
        </label>

        <?php 
?>

        <p class="description"><i><?php 
esc_html_e( '"Inline content" allows you to generate the image anywhere in the content', 'mpt' );
?></i></p>
    </td>
</tr>


<?php 
?>

<tr valign="top" class="section_custom_image_position image-location-template hidden image-inside-content image-block-0 mid-add-block-img">
    <th scope="row">
        <label for="hseparator"><?php 
esc_html_e( 'Image position', 'mpt' );
?></label>
    </th>
    <td class="custom_image_location" valign="top">
        <label><?php 
esc_html_e( 'Insert', 'mpt' );
?>
            <select name="MPT_plugin_main_settings[image_block][0][image_custom_location_placement]" class="select-custom-location form-control">
                <option value="before"><?php 
esc_html_e( 'Before', 'mpt' );
?></option>
                <option value="after"><?php 
esc_html_e( 'After', 'mpt' );
?></option>
            </select> <?php 
esc_html_e( 'the', 'mpt' );
?>
            <select name="MPT_plugin_main_settings[image_block][0][image_custom_location_position]" class="select-custom-location form-control">
                <option value="1"><?php 
esc_html_e( 'First', 'mpt' );
?></option>
                <option value="2"><?php 
esc_html_e( 'Second', 'mpt' );
?></option>
                <option value="3"><?php 
esc_html_e( 'Third', 'mpt' );
?></option>
                <option value="4"><?php 
esc_html_e( 'Fourth', 'mpt' );
?></option>
                <option value="5"><?php 
esc_html_e( 'Fifth', 'mpt' );
?></option>
                <option value="last"><?php 
esc_html_e( 'Last', 'mpt' );
?></option>
            </select>
            <select name="MPT_plugin_main_settings[image_block][0][image_custom_location_tag]" class="select-custom-location form-control">
                <option value="p"><?php 
esc_html_e( 'paragraph (p)', 'mpt' );
?></option>
                <option value="h2">h2</option>
                <option value="h3">h3</option>
                <option value="h4">h4</option>
                <option value="h5">h5</option>
                <option value="h6">h6</option>
                <option value="div">div</option>
                <option value="a"><?php 
esc_html_e( 'link (a)', 'mpt' );
?></option>
            </select>
        </label>
    </td>
</tr>

<tr valign="top" class="section_custom_image_size image-location-template image-inside-content hidden image-block-0 mid-add-block-img">
    <th scope="row">
        <label for="hseparator"><?php 
esc_html_e( 'Image size', 'mpt' );
?></label>
    </th>
    <td class="custom_image_size" valign="top">
        <label>
            <select name="MPT_plugin_main_settings[image_block][0][image_custom_image_size]" class="select-custom-location form-control">
                <?php 
foreach ( $image_sizes as $image_size ) {
    ?>
                    <option value="<?php 
    echo $image_size;
    ?>">
                        <?php 
    echo $image_size;
    ?>
                    </option>
                <?php 
}
?>
            </select> 
        </label>
    </td>
</tr>

<tr valign="top" class="section_custom_image_bank hidden image-location-template image-block-0 mid-add-block-img">
    <th scope="row">
        <label for="hseparator"><?php 
esc_html_e( 'Image Source', 'mpt' );
?></label>
    </th>
    <td class="custom_image_size" valign="top">
        <label>
            <select name="MPT_plugin_main_settings[image_block][0][api_chosen]" class="select-custom-location form-control">
            <?php 
foreach ( $list_api_auto as $api_auto_name => $api_auto_id ) {
    if ( true === $api_auto_id[1] ) {
        ?>
                    <option value="<?php 
        echo $api_auto_id[0];
        ?>"><?php 
        echo $api_auto_name;
        ?></option>
            <?php 
    }
}
?>
            </select> 
        </label>
    </td>
</tr>



<tr valign="top" class="section_basedon image-location-template hidden image-block-0 mid-add-block-img">
        <th scope="row">
                <label for="hseparator"><?php 
esc_html_e( 'Search Based on', 'mpt' );
?></label>
        </th>
        <td class="based_on radio-list">
                <select name="MPT_plugin_main_settings[image_block][0][based_on]" class="select-custom-location form-control">
                        <option value="title" selected><?php 
esc_html_e( 'Title', 'mpt' );
?></option>
                        <option value="text_analyser"><?php 
esc_html_e( 'Text Analyzer: Full text', 'mpt' );
?></option>
                        <option value="text_analyser_previous_paragraph" class="option_analyzer hidden"><?php 
esc_html_e( 'Text Analyzer: Previous paragraph', 'mpt' );
?></option>
                        <option value="text_analyser_next_paragraph" class="option_analyzer hidden"><?php 
esc_html_e( 'Text Analyzer: Next paragraph', 'mpt' );
?></option>
                        <option value="tags" <?php 
echo $disabled;
?>><?php 
esc_html_e( 'Tags', 'mpt' );
?></option>
                        <option value="categories" <?php 
echo $disabled;
?>><?php 
esc_html_e( 'Categories', 'mpt' );
?></option>
                        <option value="custom_field" <?php 
echo $disabled;
?>><?php 
esc_html_e( 'Custom Field', 'mpt' );
?></option>
                        <option value="custom_request" <?php 
echo $disabled;
?>><?php 
esc_html_e( 'Custom Request', 'mpt' );
?></option>
                        <option value="openai_extractor" <?php 
echo $disabled;
?>><?php 
esc_html_e( 'OpenAI Keyword Extractor', 'mpt' );
?></option>
                </select>
        </td>
</tr>

<?php 
if ( true === $this->MPT_freemius()->is__premium_only() ) {
    if ( $this->mpt_freemius()->can_use_premium_code() ) {
        ?>

    <tr valign="top" class="section_tags image-location-template hidden image-block-0 mid-add-block-img">
            <th scope="row">
                    <label for="hseparator"><?php 
        esc_html_e( 'Tags', 'mpt' );
        ?></label>
            </th>
            <td class="radio-list tags">
                    <label class="radio radio-outline radio-outline-2x radio-primary"><input value="first_tag" name="MPT_plugin_main_settings[image_block][0][tags]" type="radio" checked><span></span> <?php 
        esc_html_e( 'First tag', 'mpt' );
        ?></label>
                    <label class="radio radio-outline radio-outline-2x radio-primary"><input value="last_tag" name="MPT_plugin_main_settings[image_block][0][tags]" type="radio"><span></span> <?php 
        esc_html_e( 'Last tag', 'mpt' );
        ?></label>
                    <label class="radio radio-outline radio-outline-2x radio-primary"><input value="random_tag" name="MPT_plugin_main_settings[image_block][0][tags]" type="radio"><span></span> <?php 
        esc_html_e( 'Random tag', 'mpt' );
        ?></label>
            </td>
    </tr>

    <tr valign="top" class="section_categories image-location-template hidden image-block-0 mid-add-block-img">
            <th scope="row">
                    <label for="hseparator"><?php 
        esc_html_e( 'Categories', 'mpt' );
        ?></label>
            </th>
            <td class="radio-list categories">
                    <label class="radio radio-outline radio-outline-2x radio-primary"><input value="first_category" name="MPT_plugin_main_settings[image_block][0][categories]" type="radio" checked><span></span> <?php 
        esc_html_e( 'First category', 'mpt' );
        ?></label>
                    <label class="radio radio-outline radio-outline-2x radio-primary"><input value="last_category" name="MPT_plugin_main_settings[image_block][0][categories]" type="radio"><span></span> <?php 
        esc_html_e( 'Last category', 'mpt' );
        ?></label>
                    <label class="radio radio-outline radio-outline-2x radio-primary"><input value="random_category" name="MPT_plugin_main_settings[image_block][0][categories]" type="radio"><span></span> <?php 
        esc_html_e( 'Random category', 'mpt' );
        ?></label>
            </td>
    </tr>

    <tr valign="top" class="section_custom_field image-location-template hidden image-block-0 mid-add-block-img">
            <th scope="row">
                    <label for="hseparator"><?php 
        esc_html_e( 'Custom field Name', 'mpt' );
        ?></label>
            </th>
            <td class="custom_field">
                    <label><input type="text" name="MPT_plugin_main_settings[image_block][0][custom_field]" class="form-control" value="" ></label>
            </td>
    </tr>

    <tr valign="top" class="section_custom_request image-location-template hidden image-block-0 mid-add-block-img">

            <th scope="row">
                    <label for="hseparator"><?php 
        esc_html_e( 'Custom Request', 'mpt' );
        ?></label>
            </th>
            <td class="custom_field">

                <div id="custom-request-buttons">
                    <p draggable="true"><span class="button-custom" draggable="false">Title</span></p>
                    <p draggable="true"><span class="button-custom" draggable="false">Category</span></p>
                    <p draggable="true"><span class="button-custom" draggable="false">Tag</span></p>
                    <p draggable="true"><span class="button-custom" draggable="false">Taxonomy</span></p>
                </div>

                <div class="textarea-editable" contenteditable="true"><?php 
        esc_html_e( 'This is a simple request including the %%Title%%', 'mpt' );
        ?></div>
                <label>
                    <input type="hidden" class="custom_request" name="MPT_plugin_main_settings[image_block][0][custom_request]" class="form-control" value="" >
                </label>

            </td>
    </tr>

    <tr valign="top" class="category_choice based_on_bottom image-location-template hidden image-block-0 mid-add-block-img" <?php 
        /* echo(($options['based_on'] != 'custom_request') ? 'style="display:none;"': '');*/
        ?>>
        <th scope="row">
                <label for="hseparator"><?php 
        esc_html_e( 'Category Level', 'mpt' );
        ?></label>
                <p class="description">
                    <?php 
        esc_html_e( 'Choose the category level to use.', 'mpt' );
        ?>
                </p>
        </th>
        <td class="result_position radio-inline">
            <label  class="radio radio-outline radio-outline-2x radio-primary"><input value="first_level" name="MPT_plugin_main_settings[image_block][0][category_choice] " type="radio" checked><span></span> <?php 
        esc_html_e( 'Child category', 'mpt' );
        ?></label><br/>
            <label  class="radio radio-outline radio-outline-2x radio-primary <?php 
        echo $class_disabled;
        ?>"><input value="second_level" name="MPT_plugin_main_settings[image_block][0][category_choice] " type="radio" <?php 
        echo $disabled;
        ?> ><span></span> <?php 
        esc_html_e( 'Parent category', 'mpt' );
        ?></label>
            <label  class="radio radio-outline radio-outline-2x radio-primary <?php 
        echo $class_disabled;
        ?>"><input value="third_level" name="MPT_plugin_main_settings[image_block][0][category_choice] " type="radio" <?php 
        echo $disabled;
        ?> ><span></span> <?php 
        esc_html_e( 'Grandparent category', 'mpt' );
        ?></label>                                        
        </td>
    </tr>

    <tr valign="top" class="category_choice based_on_bottom image-location-template hidden image-block-0 mid-add-block-img" <?php 
        /* echo(($block['based_on'] != 'custom_request') ? 'style="display:none;"': '');*/
        ?>>
            <th scope="row">
            <label for="hseparator"><?php 
        esc_html_e( 'Taxonomy Slug', 'mpt' );
        ?></label>
            </th>
            <td class="custom_taxo_field" valign="top">
            <input type="text" name="MPT_plugin_main_settings[image_block][0][taxonomy_field]" class="col-lg-4 col-md-9 col-sm-12 form-control" value="" >
            </td>
    </tr>

    <tr valign="top" class="category_choice based_on_bottom image-location-template hidden image-block-0 mid-add-block-img" <?php 
        /* echo(($options['based_on'] != 'custom_request') ? 'style="display:none;"': '');*/
        ?>>
        <th scope="row">
                <label for="hseparator"><?php 
        esc_html_e( 'Taxonomy Level', 'mpt' );
        ?></label>
                <p class="description">
                    <?php 
        esc_html_e( 'Choose the taxonomy level to use.', 'mpt' );
        ?>
                </p>
        </th>
        <td class="result_position radio-inline">
            <label  class="radio radio-outline radio-outline-2x radio-primary"><input value="first_level" name="MPT_plugin_main_settings[image_block][0][taxonomy_choice] " type="radio" checked><span></span> <?php 
        esc_html_e( 'Child taxonomy', 'mpt' );
        ?></label><br/>
            <label  class="radio radio-outline radio-outline-2x radio-primary <?php 
        echo $class_disabled;
        ?>"><input value="second_level" name="MPT_plugin_main_settings[image_block][0][taxonomy_choice] " type="radio" <?php 
        echo $disabled;
        ?> ><span></span> <?php 
        esc_html_e( 'Parent taxonomy', 'mpt' );
        ?></label>
            <label  class="radio radio-outline radio-outline-2x radio-primary <?php 
        echo $class_disabled;
        ?>"><input value="third_level" name="MPT_plugin_main_settings[image_block][0][taxonomy_choice] " type="radio" <?php 
        echo $disabled;
        ?> ><span></span> <?php 
        esc_html_e( 'Grandparent taxonomy', 'mpt' );
        ?></label>                                        
        </td>
    </tr>

    <tr valign="top" class="section_openai_extractor image-location-template hidden image-block-0 mid-add-block-img" <?php 
        /*echo(($options['based_on'] != 'openai_extractor') ? 'style="display:none;"': '');*/
        ?>>
            <th scope="row">
                    <label for="hseparator"><?php 
        esc_html_e( 'OpenAI API Key', 'mpt' );
        ?></label>
            </th>
            <td id="password-openai" class="password">
                <input type="password" name="MPT_plugin_main_settings[openai_extractor_apikey]" class="form-control" value="">
                <i id="togglePassword"></i>
            </td>
    </tr>

        <tr valign="top" class="section_openai_extractor image-location-template hidden image-block-0 mid-add-block-img" <?php 
        /*echo(($options['based_on'] != 'openai_extractor') ? 'style="display:none;"': ''); */
        ?>>
                <th scope="row">
                        <label for="hseparator"><?php 
        esc_html_e( 'Number of keywords to extract from title', 'mpt' );
        ?></label>
                </th>
                <td class="number_of_keywords radio-inline">
                        <label  class="radio radio-outline radio-outline-2x radio-primary"><input value="1-2" name="MPT_plugin_main_settings[image_block][0][openai_number_of_keywords] " type="radio" checked><span></span> <?php 
        esc_html_e( 'From 1 to 2 words', 'mpt' );
        ?></label><br/>
                        <label  class="radio radio-outline radio-outline-2x radio-primary"><input value="2"   name="MPT_plugin_main_settings[image_block][0][openai_number_of_keywords] " type="radio"><span></span> <?php 
        esc_html_e( '2 words', 'mpt' );
        ?></label><br/>
                        <label  class="radio radio-outline radio-outline-2x radio-primary"><input value="3"   name="MPT_plugin_main_settings[image_block][0][openai_number_of_keywords] " type="radio"><span></span> <?php 
        esc_html_e( '3 words', 'mpt' );
        ?></label>
                </td>
        </tr>

<?php 
    }
}
?>

<tr valign="top" class="section_title image-location-template hidden image-block-0 mid-add-block-img" <?php 
/* echo(($options['based_on'] != 'title') ? 'style="display:none;"': '');*/
?>>
        <th scope="row">
                <label for="hseparator"><?php 
esc_html_e( 'Title', 'mpt' );
?></label>
        </th>
        <td class="chosen_title radio-inline">
            <label class="radio radio-outline radio-outline-2x radio-primary"><input value="full_title" name="MPT_plugin_main_settings[image_block][0][title_selection] " type="radio" checked><span></span> <?php 
esc_html_e( 'Full title', 'mpt' );
?></label><br/>
                <label class="radio radio-outline radio-outline-2x radio-primary"><input value="cut_title" name="MPT_plugin_main_settings[image_block][0][title_selection] " type="radio"><span></span> <?php 
esc_html_e( 'Specific Part', 'mpt' );
?> : </label>
                <input type="number" name="MPT_plugin_main_settings[image_block][0][title_length]" min="1" class="col-lg-4 col-md-9 col-sm-12 form-control length_cut_title" value="3" disabled> <i><?php 
esc_html_e( 'first words of the title', 'mpt' );
?></i>
        </td>
</tr>

<tr valign="top" class="section_text_analyser image-location-template hidden image-block-0 mid-add-block-img" <?php 
/*echo(($options['based_on'] != 'text_analyser') ? 'style="display:none;"': '');*/
?>>
        <th scope="row">
                <label for="hseparator"><?php 
esc_html_e( 'Post Content Language', 'mpt' );
?></label>
        </th>
        <td class="text_analyser">
                <select name="MPT_plugin_main_settings[image_block][0][text_analyser_lang]" class="form-control form-control-lg" >
                    <?php 
$current_wp_lang = explode( '-', get_bloginfo( 'language' ) );
$current_wp_lang = $current_wp_lang[0];
$langs = array(
    esc_html__( '-- Default --', 'mpt' ) => '',
    esc_html__( 'Arabic', 'mpt' )        => 'ar',
    esc_html__( 'Bulgarian', 'mpt' )     => 'bg',
    esc_html__( 'Czech', 'mpt' )         => 'cs',
    esc_html__( 'Danish', 'mpt' )        => 'da',
    esc_html__( 'German', 'mpt' )        => 'de',
    esc_html__( 'Greek', 'mpt' )         => 'el',
    esc_html__( 'English', 'mpt' )       => 'en',
    esc_html__( 'Spanish', 'mpt' )       => 'es',
    esc_html__( 'Estonian', 'mpt' )      => 'et',
    esc_html__( 'Persian', 'mpt' )       => 'fa',
    esc_html__( 'Finnish', 'mpt' )       => 'fi',
    esc_html__( 'French', 'mpt' )        => 'fr',
    esc_html__( 'Hebrew', 'mpt' )        => 'he',
    esc_html__( 'Hindi', 'mpt' )         => 'hi',
    esc_html__( 'Croatian', 'mpt' )      => 'hr',
    esc_html__( 'Hungarian', 'mpt' )     => 'hu',
    esc_html__( 'Armenian', 'mpt' )      => 'hy',
    esc_html__( 'Indonesian', 'mpt' )    => 'id',
    esc_html__( 'Italian', 'mpt' )       => 'it',
    esc_html__( 'Japanese', 'mpt' )      => 'ja',
    esc_html__( 'Korean', 'mpt' )        => 'ko',
    esc_html__( 'Lithuanian', 'mpt' )    => 'lt',
    esc_html__( 'Latvian', 'mpt' )       => 'lv',
    esc_html__( 'Dutch', 'mpt' )         => 'nl',
    esc_html__( 'Norwegian', 'mpt' )     => 'no',
    esc_html__( 'Polish', 'mpt' )        => 'pl',
    esc_html__( 'Portuguese', 'mpt' )    => 'pt',
    esc_html__( 'Romanian', 'mpt' )      => 'ro',
    esc_html__( 'Russian', 'mpt' )       => 'ru',
    esc_html__( 'Slovak', 'mpt' )        => 'sk',
    esc_html__( 'Slovenian', 'mpt' )     => 'sl',
    esc_html__( 'Swedish', 'mpt' )       => 'sv',
    esc_html__( 'Thai', 'mpt' )          => 'th',
    esc_html__( 'Turkish', 'mpt' )       => 'tr',
    esc_html__( 'Vietnamese', 'mpt' )    => 'vi',
    esc_html__( 'Chinese', 'mpt' )       => 'zh',
);
ksort( $langs );
foreach ( $langs as $name_lang => $code_lang ) {
    $choose = ( $current_wp_lang == $code_lang ? 'selected="selected"' : '' );
    echo '<option ' . $choose . ' value="' . $code_lang . '">' . $name_lang . '</option>';
}
?>
            </select>
        </td>
</tr>

<tr valign="top" class="translation_EN image-location-template hidden image-block-0 mid-add-block-img">
    <th scope="row">
            <?php 
esc_html_e( 'Translate to English', 'mpt' );
?>
    </th>
    <td class="checkbox-list">
        <label class="checkbox <?php 
echo $checkbox_disabled;
?>"><input name="MPT_plugin_main_settings[image_block][0][translation_EN]" type="checkbox" value="true"> <span></span> <?php 
esc_html_e( 'Translate', 'mpt' );
?></label>
        <p class="description">
            <?php 
esc_html_e( 'The "based on" phrase /keywords will be translated into English. This helps to get better results with most image databases.', 'mpt' );
?>
        </p>
    </td>
</tr>

<tr valign="top" class="selected_image based_on_bottom image-location-template hidden image-block-0 mid-add-block-img">
        <th scope="row">
                <label for="hseparator"><?php 
esc_html_e( 'Image Selection', 'mpt' );
?></label>
        </th>
        <td class="result_position radio-inline">
            <label  class="radio radio-outline radio-outline-2x radio-primary"><input value="first_result" name="MPT_plugin_main_settings[image_block][0][selected_image] " type="radio" checked><span></span> <?php 
esc_html_e( 'First result', 'mpt' );
?></label><br/>
            <label  class="radio radio-outline radio-outline-2x radio-primary <?php 
echo $class_disabled;
?>"><input value="random_result" name="MPT_plugin_main_settings[image_block][0][selected_image] " type="radio"><span></span> <?php 
esc_html_e( 'Random result', 'mpt' );
?></label>
        </td>
</tr>



<!-- Button to remove a block -->
<tr valign="top" class="image-location-template hidden image-block-0 bottom-add-block-img">
    <td colspan="2">
        <button type="button" class="btn btn-sm font-weight-bolder btn-light-danger remove-block-btn" style="text-decoration: none;">[-] Delete</button>
    </td>
</tr>

<tr class="image-location-template hidden image-block-0">
    <td colspan="2">
        <hr/>
    </td>
</tr>