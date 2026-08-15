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
esc_html_e( 'Featured Image / Inline Content', 'magic-post-thumbnail' );
?>
    </th>
    <td class="image_location radio-list">
        <label class="radio radio-outline radio-outline-2x radio-primary">
            <input value="featured" name="MPT_plugin_main_settings[image_block][0][image_location]" type="radio" checked>
            <span></span> <?php 
esc_html_e( 'Featured Image', 'magic-post-thumbnail' );
?>
        </label>
        <label class="radio radio-outline radio-outline-2x radio-primary">
            <input value="custom" name="MPT_plugin_main_settings[image_block][0][image_location]" type="radio">
            <span></span> <?php 
esc_html_e( 'Inline content', 'magic-post-thumbnail' );
?>
        </label>

        <?php 
?>

        <p class="description"><i><?php 
esc_html_e( '"Inline content" allows you to generate the image anywhere in the content', 'magic-post-thumbnail' );
?></i></p>
    </td>
</tr>


<?php 
?>

<tr valign="top" class="section_custom_image_position image-location-template hidden image-inside-content image-block-0 mid-add-block-img">
    <th scope="row">
        <label for="hseparator"><?php 
esc_html_e( 'Image position', 'magic-post-thumbnail' );
?></label>
    </th>
    <td class="custom_image_location" valign="top">
        <label><?php 
esc_html_e( 'Insert', 'magic-post-thumbnail' );
?>
            <select name="MPT_plugin_main_settings[image_block][0][image_custom_location_placement]" class="select-custom-location form-control">
                <option value="before"><?php 
esc_html_e( 'Before', 'magic-post-thumbnail' );
?></option>
                <option value="after"><?php 
esc_html_e( 'After', 'magic-post-thumbnail' );
?></option>
            </select> <?php 
esc_html_e( 'the', 'magic-post-thumbnail' );
?>
            <select name="MPT_plugin_main_settings[image_block][0][image_custom_location_position]" class="select-custom-location form-control">
                <option value="1"><?php 
esc_html_e( 'First', 'magic-post-thumbnail' );
?></option>
                <option value="2"><?php 
esc_html_e( 'Second', 'magic-post-thumbnail' );
?></option>
                <option value="3"><?php 
esc_html_e( 'Third', 'magic-post-thumbnail' );
?></option>
                <option value="4"><?php 
esc_html_e( 'Fourth', 'magic-post-thumbnail' );
?></option>
                <option value="5"><?php 
esc_html_e( 'Fifth', 'magic-post-thumbnail' );
?></option>
                <option value="6"><?php 
esc_html_e( 'Sixth', 'magic-post-thumbnail' );
?></option>
                <option value="7"><?php 
esc_html_e( 'Seventh', 'magic-post-thumbnail' );
?></option>
                <option value="8"><?php 
esc_html_e( 'Eighth', 'magic-post-thumbnail' );
?></option>
                <option value="9"><?php 
esc_html_e( 'Ninth', 'magic-post-thumbnail' );
?></option>
                <option value="10"><?php 
esc_html_e( 'Tenth', 'magic-post-thumbnail' );
?></option>
                <option value="last"><?php 
esc_html_e( 'Last', 'magic-post-thumbnail' );
?></option>
            </select>
            <select name="MPT_plugin_main_settings[image_block][0][image_custom_location_tag]" class="select-custom-location form-control">
                <option value="p"><?php 
esc_html_e( 'paragraph (p)', 'magic-post-thumbnail' );
?></option>
                <option value="h2">h2</option>
                <option value="h3">h3</option>
                <option value="h4">h4</option>
                <option value="h5">h5</option>
                <option value="h6">h6</option>
                <option value="div">div</option>
                <option value="a"><?php 
esc_html_e( 'link (a)', 'magic-post-thumbnail' );
?></option>
            </select>
        </label>
    </td>
</tr>

<tr valign="top" class="section_custom_image_size image-location-template image-inside-content hidden image-block-0 mid-add-block-img">
    <th scope="row">
        <label for="hseparator"><?php 
esc_html_e( 'Image size', 'magic-post-thumbnail' );
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
esc_html_e( 'Image Source', 'magic-post-thumbnail' );
?></label>
    </th>
    <td class="custom_image_size" valign="top">
        <label>
            <select name="MPT_plugin_main_settings[image_block][0][api_chosen]" class="select-custom-location form-control">
                <option value="default" selected><?php 
esc_html_e( 'Use the chain defined in Source', 'magic-post-thumbnail' );
?></option>
            <?php 
foreach ( $list_api_auto as $api_auto_name => $api_auto_id ) {
    if ( true === $api_auto_id[1] ) {
        ?>
                    <option value="<?php 
        echo esc_attr( $api_auto_id[0] );
        ?>"><?php 
        echo esc_html( $api_auto_name );
        ?></option>
            <?php 
    }
}
?>
            </select>
        </label>
        <p class="description mpt-chain-reminder">
            <?php 
echo esc_html( sprintf( 
    /* translators: %s: ordered bank names */
    __( 'Source chain: %s', 'magic-post-thumbnail' ),
    $this->MPT_format_bank_chain_labels()
 ) );
?>
        </p>
    </td>
</tr>



<tr valign="top" class="section_basedon image-location-template hidden image-block-0 mid-add-block-img">
        <th scope="row">
                <label for="hseparator"><?php 
esc_html_e( 'Search Based on', 'magic-post-thumbnail' );
?></label>
        </th>
        <td class="based_on radio-list">
                <select name="MPT_plugin_main_settings[image_block][0][based_on]" class="select-custom-location form-control">
                        <option value="title" selected><?php 
esc_html_e( 'Title', 'magic-post-thumbnail' );
?></option>
                        <option value="text_analyser"><?php 
esc_html_e( 'Text Analyzer: Full text', 'magic-post-thumbnail' );
?></option>
                        <option value="text_analyser_previous_paragraph" class="option_analyzer hidden"><?php 
esc_html_e( 'Text Analyzer: Previous paragraph', 'magic-post-thumbnail' );
?></option>
                        <option value="text_analyser_next_paragraph" class="option_analyzer hidden"><?php 
esc_html_e( 'Text Analyzer: Next paragraph', 'magic-post-thumbnail' );
?></option>
                        <option value="tags" <?php 
echo $disabled;
?>><?php 
esc_html_e( 'Tags', 'magic-post-thumbnail' );
?></option>
                        <option value="categories" <?php 
echo $disabled;
?>><?php 
esc_html_e( 'Categories', 'magic-post-thumbnail' );
?></option>
                        <option value="custom_field" <?php 
echo $disabled;
?>><?php 
esc_html_e( 'Custom Field', 'magic-post-thumbnail' );
?></option>
                        <option value="custom_request" <?php 
echo $disabled;
?>><?php 
esc_html_e( 'Custom Request', 'magic-post-thumbnail' );
?></option>
                        <option value="openai_extractor" <?php 
echo $disabled;
?>><?php 
esc_html_e( 'OpenAI Keyword Extractor', 'magic-post-thumbnail' );
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
        esc_html_e( 'Tags', 'magic-post-thumbnail' );
        ?></label>
            </th>
            <td class="radio-list tags">
                    <label class="radio radio-outline radio-outline-2x radio-primary"><input value="first_tag" name="MPT_plugin_main_settings[image_block][0][tags]" type="radio" checked><span></span> <?php 
        esc_html_e( 'First tag', 'magic-post-thumbnail' );
        ?></label>
                    <label class="radio radio-outline radio-outline-2x radio-primary"><input value="last_tag" name="MPT_plugin_main_settings[image_block][0][tags]" type="radio"><span></span> <?php 
        esc_html_e( 'Last tag', 'magic-post-thumbnail' );
        ?></label>
                    <label class="radio radio-outline radio-outline-2x radio-primary"><input value="random_tag" name="MPT_plugin_main_settings[image_block][0][tags]" type="radio"><span></span> <?php 
        esc_html_e( 'Random tag', 'magic-post-thumbnail' );
        ?></label>
            </td>
    </tr>

    <tr valign="top" class="section_categories image-location-template hidden image-block-0 mid-add-block-img">
            <th scope="row">
                    <label for="hseparator"><?php 
        esc_html_e( 'Categories', 'magic-post-thumbnail' );
        ?></label>
            </th>
            <td class="radio-list categories">
                    <label class="radio radio-outline radio-outline-2x radio-primary"><input value="first_category" name="MPT_plugin_main_settings[image_block][0][categories]" type="radio" checked><span></span> <?php 
        esc_html_e( 'First category', 'magic-post-thumbnail' );
        ?></label>
                    <label class="radio radio-outline radio-outline-2x radio-primary"><input value="last_category" name="MPT_plugin_main_settings[image_block][0][categories]" type="radio"><span></span> <?php 
        esc_html_e( 'Last category', 'magic-post-thumbnail' );
        ?></label>
                    <label class="radio radio-outline radio-outline-2x radio-primary"><input value="random_category" name="MPT_plugin_main_settings[image_block][0][categories]" type="radio"><span></span> <?php 
        esc_html_e( 'Random category', 'magic-post-thumbnail' );
        ?></label>
            </td>
    </tr>

    <tr valign="top" class="section_custom_field image-location-template hidden image-block-0 mid-add-block-img">
            <th scope="row">
                    <label for="hseparator"><?php 
        esc_html_e( 'Custom field Name', 'magic-post-thumbnail' );
        ?></label>
            </th>
            <td class="custom_field">
                    <label><input type="text" name="MPT_plugin_main_settings[image_block][0][custom_field]" class="form-control" value="" ></label>
            </td>
    </tr>

    <tr valign="top" class="section_custom_request image-location-template hidden image-block-0 mid-add-block-img">

            <th scope="row">
                    <label for="hseparator"><?php 
        esc_html_e( 'Custom Request', 'magic-post-thumbnail' );
        ?></label>
            </th>
            <td class="custom_field">

                <div id="custom-request-buttons">
                    <p draggable="true"><span class="button-custom" draggable="false" data-token="Title"><?php 
        esc_html_e( 'Title', 'magic-post-thumbnail' );
        ?></span></p>
                    <p draggable="true"><span class="button-custom" draggable="false" data-token="Category"><?php 
        esc_html_e( 'Category', 'magic-post-thumbnail' );
        ?></span></p>
                    <p draggable="true"><span class="button-custom" draggable="false" data-token="Tag"><?php 
        esc_html_e( 'Tag', 'magic-post-thumbnail' );
        ?></span></p>
                    <p draggable="true"><span class="button-custom" draggable="false" data-token="Taxonomy"><?php 
        esc_html_e( 'Taxonomy', 'magic-post-thumbnail' );
        ?></span></p>
                </div>

                <div class="textarea-editable" contenteditable="true"><?php 
        esc_html_e( 'This is a simple request including the %%Title%%', 'magic-post-thumbnail' );
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
        esc_html_e( 'Category Level', 'magic-post-thumbnail' );
        ?></label>
                <p class="description">
                    <?php 
        esc_html_e( 'Choose the category level to use.', 'magic-post-thumbnail' );
        ?>
                </p>
        </th>
        <td class="result_position radio-inline">
            <label  class="radio radio-outline radio-outline-2x radio-primary"><input value="first_level" name="MPT_plugin_main_settings[image_block][0][category_choice] " type="radio" checked><span></span> <?php 
        esc_html_e( 'Child category', 'magic-post-thumbnail' );
        ?></label><br/>
            <label  class="radio radio-outline radio-outline-2x radio-primary <?php 
        echo $class_disabled;
        ?>"><input value="second_level" name="MPT_plugin_main_settings[image_block][0][category_choice] " type="radio" <?php 
        echo $disabled;
        ?> ><span></span> <?php 
        esc_html_e( 'Parent category', 'magic-post-thumbnail' );
        ?></label>
            <label  class="radio radio-outline radio-outline-2x radio-primary <?php 
        echo $class_disabled;
        ?>"><input value="third_level" name="MPT_plugin_main_settings[image_block][0][category_choice] " type="radio" <?php 
        echo $disabled;
        ?> ><span></span> <?php 
        esc_html_e( 'Grandparent category', 'magic-post-thumbnail' );
        ?></label>                                        
        </td>
    </tr>

    <tr valign="top" class="category_choice based_on_bottom image-location-template hidden image-block-0 mid-add-block-img" <?php 
        /* echo(($block['based_on'] != 'custom_request') ? 'style="display:none;"': '');*/
        ?>>
            <th scope="row">
            <label for="hseparator"><?php 
        esc_html_e( 'Taxonomy Slug', 'magic-post-thumbnail' );
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
        esc_html_e( 'Taxonomy Level', 'magic-post-thumbnail' );
        ?></label>
                <p class="description">
                    <?php 
        esc_html_e( 'Choose the taxonomy level to use.', 'magic-post-thumbnail' );
        ?>
                </p>
        </th>
        <td class="result_position radio-inline">
            <label  class="radio radio-outline radio-outline-2x radio-primary"><input value="first_level" name="MPT_plugin_main_settings[image_block][0][taxonomy_choice] " type="radio" checked><span></span> <?php 
        esc_html_e( 'Child taxonomy', 'magic-post-thumbnail' );
        ?></label><br/>
            <label  class="radio radio-outline radio-outline-2x radio-primary <?php 
        echo $class_disabled;
        ?>"><input value="second_level" name="MPT_plugin_main_settings[image_block][0][taxonomy_choice] " type="radio" <?php 
        echo $disabled;
        ?> ><span></span> <?php 
        esc_html_e( 'Parent taxonomy', 'magic-post-thumbnail' );
        ?></label>
            <label  class="radio radio-outline radio-outline-2x radio-primary <?php 
        echo $class_disabled;
        ?>"><input value="third_level" name="MPT_plugin_main_settings[image_block][0][taxonomy_choice] " type="radio" <?php 
        echo $disabled;
        ?> ><span></span> <?php 
        esc_html_e( 'Grandparent taxonomy', 'magic-post-thumbnail' );
        ?></label>                                        
        </td>
    </tr>

    <tr valign="top" class="section_openai_extractor image-location-template hidden image-block-0 mid-add-block-img" <?php 
        /*echo(($options['based_on'] != 'openai_extractor') ? 'style="display:none;"': '');*/
        ?>>
            <th scope="row">
                    <label for="hseparator"><?php 
        esc_html_e( 'OpenAI API Key', 'magic-post-thumbnail' );
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
        esc_html_e( 'Number of keywords to extract from title', 'magic-post-thumbnail' );
        ?></label>
                </th>
                <td class="number_of_keywords radio-inline">
                        <label  class="radio radio-outline radio-outline-2x radio-primary"><input value="1-2" name="MPT_plugin_main_settings[image_block][0][openai_number_of_keywords] " type="radio" checked><span></span> <?php 
        esc_html_e( 'From 1 to 2 words', 'magic-post-thumbnail' );
        ?></label><br/>
                        <label  class="radio radio-outline radio-outline-2x radio-primary"><input value="2"   name="MPT_plugin_main_settings[image_block][0][openai_number_of_keywords] " type="radio"><span></span> <?php 
        esc_html_e( '2 words', 'magic-post-thumbnail' );
        ?></label><br/>
                        <label  class="radio radio-outline radio-outline-2x radio-primary"><input value="3"   name="MPT_plugin_main_settings[image_block][0][openai_number_of_keywords] " type="radio"><span></span> <?php 
        esc_html_e( '3 words', 'magic-post-thumbnail' );
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
esc_html_e( 'Title', 'magic-post-thumbnail' );
?></label>
        </th>
        <td class="chosen_title radio-inline">
            <label class="radio radio-outline radio-outline-2x radio-primary"><input value="full_title" name="MPT_plugin_main_settings[image_block][0][title_selection] " type="radio" checked><span></span> <?php 
esc_html_e( 'Full title', 'magic-post-thumbnail' );
?></label><br/>
                <label class="radio radio-outline radio-outline-2x radio-primary"><input value="cut_title" name="MPT_plugin_main_settings[image_block][0][title_selection] " type="radio"><span></span> <?php 
esc_html_e( 'Specific Part', 'magic-post-thumbnail' );
?> : </label>
                <input type="number" name="MPT_plugin_main_settings[image_block][0][title_length]" min="1" class="col-lg-4 col-md-9 col-sm-12 form-control length_cut_title" value="3" disabled> <i><?php 
esc_html_e( 'first words of the title', 'magic-post-thumbnail' );
?></i>
        </td>
</tr>

<tr valign="top" class="section_text_analyser image-location-template hidden image-block-0 mid-add-block-img" <?php 
/*echo(($options['based_on'] != 'text_analyser') ? 'style="display:none;"': '');*/
?>>
        <th scope="row">
                <label for="hseparator"><?php 
esc_html_e( 'Post Content Language', 'magic-post-thumbnail' );
?></label>
        </th>
        <td class="text_analyser">
                <select name="MPT_plugin_main_settings[image_block][0][text_analyser_lang]" class="form-control form-control-lg" >
                    <?php 
$current_wp_lang = explode( '-', get_bloginfo( 'language' ) );
$current_wp_lang = $current_wp_lang[0];
$langs = array(
    esc_html__( '-- Default --', 'magic-post-thumbnail' ) => '',
    esc_html__( 'Arabic', 'magic-post-thumbnail' )        => 'ar',
    esc_html__( 'Bulgarian', 'magic-post-thumbnail' )     => 'bg',
    esc_html__( 'Czech', 'magic-post-thumbnail' )         => 'cs',
    esc_html__( 'Danish', 'magic-post-thumbnail' )        => 'da',
    esc_html__( 'German', 'magic-post-thumbnail' )        => 'de',
    esc_html__( 'Greek', 'magic-post-thumbnail' )         => 'el',
    esc_html__( 'English', 'magic-post-thumbnail' )       => 'en',
    esc_html__( 'Spanish', 'magic-post-thumbnail' )       => 'es',
    esc_html__( 'Estonian', 'magic-post-thumbnail' )      => 'et',
    esc_html__( 'Persian', 'magic-post-thumbnail' )       => 'fa',
    esc_html__( 'Finnish', 'magic-post-thumbnail' )       => 'fi',
    esc_html__( 'French', 'magic-post-thumbnail' )        => 'fr',
    esc_html__( 'Hebrew', 'magic-post-thumbnail' )        => 'he',
    esc_html__( 'Hindi', 'magic-post-thumbnail' )         => 'hi',
    esc_html__( 'Croatian', 'magic-post-thumbnail' )      => 'hr',
    esc_html__( 'Hungarian', 'magic-post-thumbnail' )     => 'hu',
    esc_html__( 'Armenian', 'magic-post-thumbnail' )      => 'hy',
    esc_html__( 'Indonesian', 'magic-post-thumbnail' )    => 'id',
    esc_html__( 'Italian', 'magic-post-thumbnail' )       => 'it',
    esc_html__( 'Japanese', 'magic-post-thumbnail' )      => 'ja',
    esc_html__( 'Korean', 'magic-post-thumbnail' )        => 'ko',
    esc_html__( 'Lithuanian', 'magic-post-thumbnail' )    => 'lt',
    esc_html__( 'Latvian', 'magic-post-thumbnail' )       => 'lv',
    esc_html__( 'Dutch', 'magic-post-thumbnail' )         => 'nl',
    esc_html__( 'Norwegian', 'magic-post-thumbnail' )     => 'no',
    esc_html__( 'Polish', 'magic-post-thumbnail' )        => 'pl',
    esc_html__( 'Portuguese', 'magic-post-thumbnail' )    => 'pt',
    esc_html__( 'Romanian', 'magic-post-thumbnail' )      => 'ro',
    esc_html__( 'Russian', 'magic-post-thumbnail' )       => 'ru',
    esc_html__( 'Slovak', 'magic-post-thumbnail' )        => 'sk',
    esc_html__( 'Slovenian', 'magic-post-thumbnail' )     => 'sl',
    esc_html__( 'Swedish', 'magic-post-thumbnail' )       => 'sv',
    esc_html__( 'Thai', 'magic-post-thumbnail' )          => 'th',
    esc_html__( 'Turkish', 'magic-post-thumbnail' )       => 'tr',
    esc_html__( 'Vietnamese', 'magic-post-thumbnail' )    => 'vi',
    esc_html__( 'Chinese', 'magic-post-thumbnail' )       => 'zh',
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
esc_html_e( 'Translate to English', 'magic-post-thumbnail' );
?>
    </th>
    <td class="checkbox-list">
        <label class="checkbox <?php 
echo $checkbox_disabled;
?>"><input name="MPT_plugin_main_settings[image_block][0][translation_EN]" type="checkbox" value="true"> <span></span> <?php 
esc_html_e( 'Translate', 'magic-post-thumbnail' );
?></label>
        <p class="description">
            <?php 
esc_html_e( 'The "based on" phrase /keywords will be translated into English. This helps to get better results with most image databases.', 'magic-post-thumbnail' );
?>
        </p>
    </td>
</tr>

<?php 
$blockIndex = 0;
$block = array();
include dirname( __FILE__ ) . '/style.php';
?>

<tr valign="top" class="selected_image based_on_bottom image-location-template hidden image-block-0 mid-add-block-img">
        <th scope="row">
                <label for="hseparator"><?php 
esc_html_e( 'Image Selection', 'magic-post-thumbnail' );
?></label>
        </th>
        <td class="result_position radio-inline">
            <label  class="radio radio-outline radio-outline-2x radio-primary"><input value="first_result" name="MPT_plugin_main_settings[image_block][0][selected_image] " type="radio" checked><span></span> <?php 
esc_html_e( 'First result', 'magic-post-thumbnail' );
?></label><br/>
            <label  class="radio radio-outline radio-outline-2x radio-primary <?php 
echo $class_disabled;
?>"><input value="random_result" name="MPT_plugin_main_settings[image_block][0][selected_image] " type="radio"><span></span> <?php 
esc_html_e( 'Random result', 'magic-post-thumbnail' );
?></label>
        </td>
</tr>



<!-- Button to remove a block -->
<tr valign="top" class="image-location-template hidden image-block-0 bottom-add-block-img">
    <td colspan="2">
        <button type="button" class="btn btn-sm font-weight-bolder btn-light-danger remove-block-btn" style="text-decoration: none;">[-] <?php 
esc_html_e( 'Delete', 'magic-post-thumbnail' );
?></button>
    </td>
</tr>

<tr class="image-location-template hidden image-block-0">
    <td colspan="2">
        <hr/>
    </td>
</tr>