<?php

if ( !function_exists( 'add_filter' ) ) {
    header( 'Status: 403 Forbidden' );
    header( 'HTTP/1.1 403 Forbidden' );
    exit;
}
$disabled = 'disabled';
$class_disabled = 'radio-disabled';
$checkbox_disabled = 'checkbox-disabled';
?>
<div class="wrap">

    <?php 
if ( !$this->mpt_freemius()->is__premium_only() && current_time( 'U' ) < 1717192799 ) {
    ?>
        <div class="alert alert-custom alert-default" role="alert">
            <div class="alert-icon"><span class="svg-icon svg-icon-primary svg-icon-xl"><svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
                <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                    <rect x="0" y="0" width="24" height="24"></rect>
                    <path d="M7.07744993,12.3040451 C7.72444571,13.0716094 8.54044565,13.6920474 9.46808594,14.1079953 L5,23 L4.5,18 L7.07744993,12.3040451 Z M14.5865511,14.2597864 C15.5319561,13.9019016 16.375416,13.3366121 17.0614026,12.6194459 L19.5,18 L19,23 L14.5865511,14.2597864 Z M12,3.55271368e-14 C12.8284271,3.53749572e-14 13.5,0.671572875 13.5,1.5 L13.5,4 L10.5,4 L10.5,1.5 C10.5,0.671572875 11.1715729,3.56793164e-14 12,3.55271368e-14 Z" fill="#000000" opacity="0.3"></path>
                    <path d="M12,10 C13.1045695,10 14,9.1045695 14,8 C14,6.8954305 13.1045695,6 12,6 C10.8954305,6 10,6.8954305 10,8 C10,9.1045695 10.8954305,10 12,10 Z M12,13 C9.23857625,13 7,10.7614237 7,8 C7,5.23857625 9.23857625,3 12,3 C14.7614237,3 17,5.23857625 17,8 C17,10.7614237 14.7614237,13 12,13 Z" fill="#000000" fill-rule="nonzero"></path>
                </g>
            </svg><!--end::Svg Icon--></span>
            </div>
            <div class="alert-text">
                Get a <strong>20% discount until May 31</strong> when you upgrade to the <a href="admin.php?page=magic-post-thumbnail-admin-display-pricing">Pro version</a> with the code: <strong>MPT20</strong>
            </div>
        </div>
    <?php 
}
?>


    <?php 
settings_errors();
?>

    <form method="post" action="options.php" id="tabs" class="form-images">

        <?php 
settings_fields( 'MPT-plugin-main-settings' );
$options = wp_parse_args( get_option( 'MPT_plugin_main_settings' ), $this->MPT_default_options_main_settings( TRUE ) );
$options_post = get_option( 'MPT_plugin_posts_settings' );
$options_interval = wp_parse_args( get_option( 'MPT_plugin_interval_settings' ), $this->MPT_default_options_interval_settings( TRUE ) );
// Old settings v4 options
if ( isset( $options_post['enable_save_post_hook'] ) && 'enable' == $options_post['enable_save_post_hook'] ) {
    $options['enable_save_post_hook'] = 'enable';
    $settings['enable_save_post_hook'] = 'enable';
    update_option( 'MPT_plugin_main_settings', $settings );
    delete_option( 'MPT_plugin_posts_settings' );
}
if ( $options_interval['bulk_generation_interval'] && !isset( $options['bulk_generation_interval'] ) ) {
    $options['bulk_generation_interval'] = $options_interval['bulk_generation_interval'];
    $settings['bulk_generation_interval'] = $options_interval['bulk_generation_interval'];
    update_option( 'MPT_plugin_main_settings', $settings );
    delete_option( 'MPT_plugin_interval_settings' );
}
// Get the value for interval
$value_bulk_generation_interval = ( isset( $options['bulk_generation_interval'] ) ? (int) $options['bulk_generation_interval'] : 0 );
// Remove transient if we do not want anymore the interval generation
if ( 0 == $value_bulk_generation_interval ) {
    delete_transient( 'MPT_interval_generation' );
}
$image_sizes = get_intermediate_image_sizes();
array_push( $image_sizes, 'full' );
?>

        <div id="settings">
            <table id="general-options" class="form-table tabs-content">
                    <tbody>

                            <tr valign="top">
                                    <th scope="row">
                                            <?php 
esc_html_e( 'Featured or Post Image', 'mpt' );
?>
                                    </th>
                                    <td class="image_location radio-list">
                                            <label class="radio radio-outline radio-outline-2x radio-primary"><input value="featured" name="MPT_plugin_main_settings[image_location] " type="radio" <?php 
echo ( !empty( $options['image_location'] ) && $options['image_location'] == 'featured' ? 'checked' : '' );
?>><span></span> <?php 
esc_html_e( 'Featured Image', 'mpt' );
?></label>
                                            <label class="radio radio-outline radio-outline-2x radio-primary <?php 
echo $class_disabled;
?>"><input value="custom" name="MPT_plugin_main_settings[image_location] " type="radio" <?php 
echo ( !empty( $options['image_location'] ) && $options['image_location'] == 'custom' ? 'checked' : '' );
echo $disabled;
?>><span></span> <?php 
esc_html_e( 'Inside content', 'mpt' );
?></label>
                                            <p class="description"><i><?php 
esc_html_e( '"Inside content" allows you to generate the image anywhere in the content', 'mpt' );
?></i></p>
                                    </td>
                            </tr>

                            <tr valign="top" class="section_custom_image_position" <?php 
echo ( $options['image_location'] != 'custom' ? 'style="display:none;"' : '' );
?>>
                                <th scope="row">
                                        <label for="hseparator"><?php 
esc_html_e( 'Image position', 'mpt' );
?></label>
                                </th>
                                <td class="custom_image_location" valign="top">
                                    <label><?php 
esc_html_e( 'Insert', 'mpt' );
?>
                                        <select name="MPT_plugin_main_settings[image_custom_location_placement]" class="select-custom-location form-control">
                                            <option value="before" <?php 
echo ( !empty( $options['image_custom_location_placement'] ) && $options['image_custom_location_placement'] == 'before' ? 'selected' : '' );
?>><?php 
esc_html_e( 'Before', 'mpt' );
?></option>
                                            <option value="after" <?php 
echo ( !empty( $options['image_custom_location_placement'] ) && $options['image_custom_location_placement'] == 'after' ? 'selected' : '' );
?>><?php 
esc_html_e( 'After', 'mpt' );
?></option>
                                        </select> <?php 
esc_html_e( 'the', 'mpt' );
?>
                                        <select name="MPT_plugin_main_settings[image_custom_location_position]" class="select-custom-location form-control">
                                                <option value="1" <?php 
echo ( !empty( $options['image_custom_location_position'] ) && $options['image_custom_location_position'] == '1' ? 'selected' : '' );
?>><?php 
esc_html_e( 'First', 'mpt' );
?></option>
                                                <option value="2" <?php 
echo ( !empty( $options['image_custom_location_position'] ) && $options['image_custom_location_position'] == '2' ? 'selected' : '' );
?>><?php 
esc_html_e( 'Second', 'mpt' );
?></option>
                                                <option value="3" <?php 
echo ( !empty( $options['image_custom_location_position'] ) && $options['image_custom_location_position'] == '3' ? 'selected' : '' );
?>><?php 
esc_html_e( 'Third', 'mpt' );
?></option>
                                                <option value="4" <?php 
echo ( !empty( $options['image_custom_location_position'] ) && $options['image_custom_location_position'] == '4' ? 'selected' : '' );
?>><?php 
esc_html_e( 'Fourth', 'mpt' );
?></option>
                                                <option value="5" <?php 
echo ( !empty( $options['image_custom_location_position'] ) && $options['image_custom_location_position'] == '5' ? 'selected' : '' );
?>><?php 
esc_html_e( 'Fifth', 'mpt' );
?></option>
                                                <option value="last" <?php 
echo ( !empty( $options['image_custom_location_position'] ) && $options['image_custom_location_position'] == 'last' ? 'selected' : '' );
?>><?php 
esc_html_e( 'Last', 'mpt' );
?></option>
                                        </select>
                                        <select name="MPT_plugin_main_settings[image_custom_location_tag]" class="select-custom-location form-control">
                                                <option value="p" <?php 
echo ( !empty( $options['image_custom_location_tag'] ) && $options['image_custom_location_tag'] == 'p' ? 'selected' : '' );
?>><?php 
esc_html_e( 'paragraph (p)', 'mpt' );
?></option>
                                                <option value="h2" <?php 
echo ( !empty( $options['image_custom_location_tag'] ) && $options['image_custom_location_tag'] == 'h2' ? 'selected' : '' );
?>>h2</option>
                                                <option value="h3" <?php 
echo ( !empty( $options['image_custom_location_tag'] ) && $options['image_custom_location_tag'] == 'h3' ? 'selected' : '' );
?>>h3</option>
                                                <option value="h4" <?php 
echo ( !empty( $options['image_custom_location_tag'] ) && $options['image_custom_location_tag'] == 'h4' ? 'selected' : '' );
?>>h4</option>
                                                <option value="h5" <?php 
echo ( !empty( $options['image_custom_location_tag'] ) && $options['image_custom_location_tag'] == 'h5' ? 'selected' : '' );
?>>h5</option>
                                                <option value="h6" <?php 
echo ( !empty( $options['image_custom_location_tag'] ) && $options['image_custom_location_tag'] == 'h6' ? 'selected' : '' );
?>>h6</option>
                                        </select>
                                    </label>
                                </td>
                            </tr>

                            <tr valign="top" class="section_custom_image_size" <?php 
echo ( $options['image_location'] != 'custom' ? 'style="display:none;"' : '' );
?>>

                                <th scope="row">
                                        <label for="hseparator"><?php 
esc_html_e( 'Image size', 'mpt' );
?></label>
                                </th>
                                <td class="custom_image_size" valign="top">
                                    <label>
                                        <select name="MPT_plugin_main_settings[image_custom_image_size]" class="select-custom-location form-control">
                                            <?php 
foreach ( $image_sizes as $image_size ) {
    ?>
                                                <option value="<?php 
    echo $image_size;
    ?>" <?php 
    echo ( !empty( $options['image_custom_image_size'] ) && $options['image_custom_image_size'] == $image_size ? 'selected' : '' );
    ?>>
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


                            <tr valign="top">
                                <td colspan="2" class="infos">
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
esc_html_e( 'Choose where the image will be created', 'mpt' );
?>
                                        </div>
                                    </div>
                                </td>
                            </tr>

                            <tr valign="top">
                                    <th scope="row">
                                            <label for="hseparator"><?php 
esc_html_e( 'Based on', 'mpt' );
?></label>
                                    </th>
                                    <td class="based_on radio-list">
                                            <label class="radio radio-outline radio-outline-2x radio-primary"><input value="title" name="MPT_plugin_main_settings[based_on] " type="radio" <?php 
echo ( !empty( $options['based_on'] ) && $options['based_on'] == 'title' ? 'checked' : '' );
?> ><span></span> <?php 
esc_html_e( 'Title', 'mpt' );
?></label>
                                            <label class="radio radio-outline radio-outline-2x radio-primary"><input value="text_analyser" name="MPT_plugin_main_settings[based_on] " type="radio" <?php 
echo ( !empty( $options['based_on'] ) && $options['based_on'] == 'text_analyser' ? 'checked' : '' );
?>><span></span> <?php 
esc_html_e( 'Text Analyzer', 'mpt' );
?></label>
                                            <label class="radio radio-outline radio-outline-2x radio-primary <?php 
echo $class_disabled;
?>"><input value="tags" name="MPT_plugin_main_settings[based_on] " type="radio" <?php 
echo ( !empty( $options['based_on'] ) && $options['based_on'] == 'tags' ? 'checked' : '' );
echo $disabled;
?>><span></span> <?php 
esc_html_e( 'Tags', 'mpt' );
?></label>
                                            <label class="radio radio-outline radio-outline-2x radio-primary <?php 
echo $class_disabled;
?>"><input value="categories" name="MPT_plugin_main_settings[based_on] " type="radio" <?php 
echo ( !empty( $options['based_on'] ) && $options['based_on'] == 'categories' ? 'checked' : '' );
echo $disabled;
?>><span></span> <?php 
esc_html_e( 'Categories', 'mpt' );
?></label>
                                            <label class="radio radio-outline radio-outline-2x radio-primary <?php 
echo $class_disabled;
?>"><input value="custom_field" name="MPT_plugin_main_settings[based_on] " type="radio" <?php 
echo ( !empty( $options['based_on'] ) && $options['based_on'] == 'custom_field' ? 'checked' : '' );
echo $disabled;
?>><span></span> <?php 
esc_html_e( 'Custom Field', 'mpt' );
?></label>
                                            <label class="radio radio-outline radio-outline-2x radio-primary <?php 
echo $class_disabled;
?>"><input value="custom_request" name="MPT_plugin_main_settings[based_on] " type="radio" <?php 
echo ( !empty( $options['based_on'] ) && $options['based_on'] == 'custom_request' ? 'checked' : '' );
echo $disabled;
?>><span></span> <?php 
esc_html_e( 'Custom Request', 'mpt' );
?></label>
                                            <label class="radio radio-outline radio-outline-2x radio-primary <?php 
echo $class_disabled;
?>"><input value="openai_extractor" name="MPT_plugin_main_settings[based_on] " type="radio" <?php 
echo ( !empty( $options['based_on'] ) && $options['based_on'] == 'openai_extractor' ? 'checked' : '' );
echo $disabled;
?>><span></span> <?php 
esc_html_e( 'OpenAI Keyword Extractor', 'mpt' );
?></label>
                                    </td>
                            </tr>

                            <?php 
if ( true === $this->MPT_freemius()->is__premium_only() ) {
    if ( $this->mpt_freemius()->can_use_premium_code() ) {
        ?>

                                <tr valign="top" class="section_tags" <?php 
        echo ( $options['based_on'] != 'tags' ? 'style="display:none;"' : '' );
        ?>>
                                        <th scope="row">
                                                <label for="hseparator"><?php 
        esc_html_e( 'Tags', 'mpt' );
        ?></label>
                                        </th>
                                        <td class="radio-list tags">
                                                <label class="radio"><input value="first_tag" name="MPT_plugin_main_settings[tags] " type="radio" <?php 
        echo ( !empty( $options['tags'] ) && $options['tags'] == 'first_tag' ? 'checked' : '' );
        ?> ><span></span> <?php 
        esc_html_e( 'First tag', 'mpt' );
        ?></label>
                                                <label class="radio"><input value="last_tag" name="MPT_plugin_main_settings[tags] " type="radio" <?php 
        echo ( !empty( $options['tags'] ) && $options['tags'] == 'last_tag' ? 'checked' : '' );
        ?>><span></span> <?php 
        esc_html_e( 'Last tag', 'mpt' );
        ?></label>
                                                <label class="radio"><input value="random_tag" name="MPT_plugin_main_settings[tags] " type="radio" <?php 
        echo ( !empty( $options['tags'] ) && $options['tags'] == 'random_tag' ? 'checked' : '' );
        ?>><span></span> <?php 
        esc_html_e( 'Random tag', 'mpt' );
        ?></label>
                                        </td>
                                </tr>

                                <tr valign="top" class="section_categories" <?php 
        echo ( $options['based_on'] != 'categories' ? 'style="display:none;"' : '' );
        ?>>
                                        <th scope="row">
                                                <label for="hseparator"><?php 
        esc_html_e( 'Categories', 'mpt' );
        ?></label>
                                        </th>
                                        <td class="radio-list categories">
                                                <label class="radio"><input value="first_category" name="MPT_plugin_main_settings[categories] " type="radio" <?php 
        echo ( !empty( $options['categories'] ) && $options['categories'] == 'first_category' ? 'checked' : '' );
        ?> ><span></span> <?php 
        esc_html_e( 'First category', 'mpt' );
        ?></label>
                                                <label class="radio"><input value="last_category" name="MPT_plugin_main_settings[categories] " type="radio" <?php 
        echo ( !empty( $options['categories'] ) && $options['categories'] == 'last_category' ? 'checked' : '' );
        ?>><span></span> <?php 
        esc_html_e( 'Last category', 'mpt' );
        ?></label>
                                                <label class="radio"><input value="random_category" name="MPT_plugin_main_settings[categories] " type="radio" <?php 
        echo ( !empty( $options['categories'] ) && $options['categories'] == 'random_category' ? 'checked' : '' );
        ?>><span></span> <?php 
        esc_html_e( 'Random category', 'mpt' );
        ?></label>
                                        </td>
                                </tr>

                                <tr valign="top" class="section_custom_field" <?php 
        echo ( $options['based_on'] != 'custom_field' ? 'style="display:none;"' : '' );
        ?>>
                                        <th scope="row">
                                                <label for="hseparator"><?php 
        esc_html_e( 'Custom field Name', 'mpt' );
        ?></label>
                                        </th>
                                        <td class="custom_field">
                                                <label><input type="text" name="MPT_plugin_main_settings[custom_field]" class="form-control" value="<?php 
        echo ( isset( $options['custom_field'] ) && !empty( $options['custom_field'] ) ? $options['custom_field'] : '' );
        ?>" ></label>
                                        </td>
                                </tr>

                                <tr valign="top" class="section_custom_request" <?php 
        echo ( $options['based_on'] != 'custom_request' ? 'style="display:none;"' : '' );
        ?>>

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
                                            </div>

                                            <div id="textarea-editable" contenteditable="true"><?php 
        echo ( isset( $options['custom_request'] ) && !empty( $options['custom_request'] ) ? $options['custom_request'] : esc_html_e( 'This is a simple request including the %%Title%%', 'mpt' ) );
        ?></div>
                                            <label>
                                                <input type="hidden" class="custom_request" name="MPT_plugin_main_settings[custom_request]" class="form-control" value="<?php 
        echo ( isset( $options['custom_request'] ) && !empty( $options['custom_request'] ) ? esc_html( $options['custom_request'] ) : '' );
        ?>" >
                                            </label>

                                        </td>
                                </tr>

								<tr valign="top" class="section_openai_extractor" <?php 
        echo ( $options['based_on'] != 'openai_extractor' ? 'style="display:none;"' : '' );
        ?>>
                                        <th scope="row">
                                                <label for="hseparator"><?php 
        esc_html_e( 'OpenAI API Key', 'mpt' );
        ?></label>
                                        </th>
                                        <td id="password-openai" class="password">
                                            <input type="password" name="MPT_plugin_main_settings[openai_extractor_apikey]" class="form-control" value="<?php 
        echo ( isset( $options['openai_extractor_apikey'] ) && !empty( $options['openai_extractor_apikey'] ) ? $options['openai_extractor_apikey'] : '' );
        ?>">
                                            <i id="togglePassword"></i>
                                        </td>
                                </tr>

                                    <tr valign="top" class="section_openai_extractor" <?php 
        echo ( $options['based_on'] != 'openai_extractor' ? 'style="display:none;"' : '' );
        ?>>
		                                    <th scope="row">
		                                            <label for="hseparator"><?php 
        esc_html_e( 'Number of keywords to extract from title', 'mpt' );
        ?></label>
		                                    </th>
											<td class="number_of_keywords radio-inline">
		                                            <label  class="radio radio-outline radio-outline-2x radio-primary"><input value="1-2" name="MPT_plugin_main_settings[openai_number_of_keywords] " type="radio" <?php 
        echo ( !empty( $options['openai_number_of_keywords'] ) && $options['openai_number_of_keywords'] == '1-2' ? 'checked' : '' );
        ?> ><span></span> <?php 
        esc_html_e( 'From 1 to 2 words', 'mpt' );
        ?></label><br/>
		                                            <label  class="radio radio-outline radio-outline-2x radio-primary"><input value="2"   name="MPT_plugin_main_settings[openai_number_of_keywords] " type="radio" <?php 
        echo ( !empty( $options['openai_number_of_keywords'] ) && $options['openai_number_of_keywords'] == '2' ? 'checked' : '' );
        ?> ><span></span> <?php 
        esc_html_e( '2 words', 'mpt' );
        ?></label><br/>
		                                            <label  class="radio radio-outline radio-outline-2x radio-primary"><input value="3"   name="MPT_plugin_main_settings[openai_number_of_keywords] " type="radio" <?php 
        echo ( !empty( $options['openai_number_of_keywords'] ) && $options['openai_number_of_keywords'] == '3' ? 'checked' : '' );
        ?> ><span></span> <?php 
        esc_html_e( '3 words', 'mpt' );
        ?></label>
		                                    </td>
		                            </tr>

                            <?php 
    }
}
?>

                            <tr valign="top" class="section_title" <?php 
echo ( $options['based_on'] != 'title' ? 'style="display:none;"' : '' );
?>>
                                    <th scope="row">
                                            <label for="hseparator"><?php 
esc_html_e( 'Title', 'mpt' );
?></label>
                                    </th>
                                    <td class="chosen_title radio-inline">
                                        <label class="radio radio-outline radio-outline-2x radio-primary"><input value="full_title" name="MPT_plugin_main_settings[title_selection] " type="radio" <?php 
echo ( !empty( $options['title_selection'] ) && $options['title_selection'] == 'full_title' ? 'checked' : '' );
?> ><span></span> <?php 
esc_html_e( 'Full title', 'mpt' );
?></label><br/>
                                            <label class="radio radio-outline radio-outline-2x radio-primary"><input value="cut_title" name="MPT_plugin_main_settings[title_selection] " type="radio" <?php 
echo ( !empty( $options['title_selection'] ) && $options['title_selection'] == 'cut_title' ? 'checked' : '' );
?>><span></span> <?php 
esc_html_e( 'Part of the title', 'mpt' );
?> : </label>
                                            <input type="number" name="MPT_plugin_main_settings[title_length]" min="1" class="col-lg-4 col-md-9 col-sm-12 form-control" value="<?php 
echo ( isset( $options['title_length'] ) && !empty( $options['title_length'] ) ? (int) $options['title_length'] : '3' );
?>" <?php 
echo ( !empty( $options['title_selection'] ) && $options['title_selection'] == 'cut_title' ? '' : 'disabled' );
?>> <i><?php 
esc_html_e( 'first words of the title', 'mpt' );
?></i>
                                    </td>
                            </tr>

                            <tr valign="top" class="section_text_analyser" <?php 
echo ( $options['based_on'] != 'text_analyser' ? 'style="display:none;"' : '' );
?>>
                                    <th scope="row">
                                            <label for="hseparator"><?php 
esc_html_e( 'Post Content Language', 'mpt' );
?></label>
                                    </th>
                                    <td class="text_analyser">
                                         <select name="MPT_plugin_main_settings[text_analyser_lang]" class="form-control form-control-lg" >
                                                <?php 
if ( $options['text_analyser_lang'] ) {
    $current_wp_lang = $options['text_analyser_lang'];
} else {
    $current_wp_lang = explode( '-', get_bloginfo( 'language' ) );
    $current_wp_lang = $current_wp_lang[0];
}
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

                            <tr valign="top" class="translation_EN">
                                <th scope="row">
                                        <?php 
esc_html_e( 'Translate to English', 'mpt' );
?>
                                </th>
                                <td class="checkbox-list">
                                    <label class="checkbox <?php 
echo $checkbox_disabled;
?>"><input <?php 
echo ( !empty( $options['translation_EN'] ) && $options['translation_EN'] == 'true' ? 'checked' : '' );
?> name="MPT_plugin_main_settings[translation_EN]" type="checkbox" value="true"> <span></span> <?php 
esc_html_e( 'Translate', 'mpt' );
?></label>
                                    <p class="description">
                                        <?php 
esc_html_e( 'The "based on" phrase /keywords will be translated into English. This helps to get better results with most image databases.', 'mpt' );
?>
                                    </p>
                                </td>
                            </tr>

                            <tr valign="top" class="selected_image based_on_bottom">
                                    <th scope="row">
                                            <label for="hseparator"><?php 
esc_html_e( 'Selected Image ', 'mpt' );
?></label>
                                    </th>
                                    <td class="result_position radio-inline">
                                        <label  class="radio radio-outline radio-outline-2x radio-primary"><input value="first_result" name="MPT_plugin_main_settings[selected_image] " type="radio" <?php 
echo ( !empty( $options['selected_image'] ) && $options['selected_image'] == 'first_result' ? 'checked' : '' );
?> ><span></span> <?php 
esc_html_e( 'First result', 'mpt' );
?></label><br/>
                                        <label  class="radio radio-outline radio-outline-2x radio-primary <?php 
echo $class_disabled;
?>"><input value="random_result" name="MPT_plugin_main_settings[selected_image] " type="radio" <?php 
echo ( !empty( $options['selected_image'] ) && $options['selected_image'] == 'random_result' ? 'checked' : '' );
echo $disabled;
?> ><span></span> <?php 
esc_html_e( 'Random result', 'mpt' );
?></label>
                                    </td>
                            </tr>

                            <tr valign="top" class="selected_image">
                                    <th scope="row">
                                            <label for="hseparator"><?php 
esc_html_e( 'Image Filename ', 'mpt' );
?></label>
                                    </th>
                                    <td class="chosen_title radio-inline">
                                            <label  class="radio radio-outline radio-outline-2x radio-primary"><input value="title"  name="MPT_plugin_main_settings[image_filename] " type="radio" <?php 
echo ( !empty( $options['image_filename'] ) && $options['image_filename'] == 'title' ? 'checked' : '' );
?> ><span></span> <?php 
esc_html_e( 'Title', 'mpt' );
?></label><br/>
                                            <label  class="radio radio-outline radio-outline-2x radio-primary"><input value="date"   name="MPT_plugin_main_settings[image_filename] " type="radio" <?php 
echo ( !empty( $options['image_filename'] ) && $options['image_filename'] == 'date' ? 'checked' : '' );
?> ><span></span> <?php 
esc_html_e( 'Date', 'mpt' );
?></label><br/>
                                            <label  class="radio radio-outline radio-outline-2x radio-primary"><input value="random" name="MPT_plugin_main_settings[image_filename] " type="radio" <?php 
echo ( !empty( $options['image_filename'] ) && $options['image_filename'] == 'random' ? 'checked' : '' );
?> ><span></span> <?php 
esc_html_e( 'Random number', 'mpt' );
?></label>
                                    </td>
                            </tr>

                            <tr valign="top">
                                    <th scope="row">
                                            <?php 
esc_html_e( 'Overwrite existing images', 'mpt' );
?>
                                    </th>
                                    <td class="checkbox-list">
                                            <label class="checkbox"><input <?php 
echo ( !empty( $options['rewrite_featured'] ) && $options['rewrite_featured'] == 'true' ? 'checked' : '' );
?> name="MPT_plugin_main_settings[rewrite_featured]" type="checkbox" value="true"> <span></span> <?php 
esc_html_e( 'Overwrite', 'mpt' );
?></label>
                                            <p class="description">
                                                    <?php 
esc_html_e( 'Warning: This option will overwrite existing images', 'mpt' );
?>
                                            </p>
                                    </td>
                            </tr>

                            <tr valign="top" class="shuffle_image">
                                    <th scope="row">
                                            <?php 
esc_html_e( 'Image Shuffle', 'mpt' );
?>
                                    </th>
                                    <td class="checkbox-list">
                                        <label class="checkbox <?php 
echo $checkbox_disabled;
?>"><input <?php 
echo ( !empty( $options['image_flip'] ) && $options['image_flip'] == 'true' ? 'checked' : '' );
?> name="MPT_plugin_main_settings[image_flip]" type="checkbox" value="true"> <span></span> <?php 
esc_html_e( 'Flip horizontally', 'mpt' );
?></label>
                                        <label class="checkbox <?php 
echo $checkbox_disabled;
?>"><input <?php 
echo ( !empty( $options['image_crop'] ) && $options['image_crop'] == 'true' ? 'checked' : '' );
?> name="MPT_plugin_main_settings[image_crop]" type="checkbox" value="true"> <span></span> <?php 
esc_html_e( 'Crop Image by 10%	', 'mpt' );
?></label>
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
        esc_html_e( 'Add alt tag on image', 'mpt' );
        ?></label>
                                    </th>
                                    <td>
                                        <label class="checkbox">
                                            <input data-switch="true" type="checkbox" name="MPT_plugin_main_settings[enable_alt]" id="enable_alt" value="enable" <?php 
        echo ( !empty( $options['enable_alt'] ) && $options['enable_alt'] == 'enable' ? 'checked' : '' );
        ?> />
                                        </label>
                                    </td>
                                </tr>

                                <tr valign="top" class="show_alt" <?php 
        echo ( !isset( $options['enable_alt'] ) || $options['enable_alt'] != 'enable' ? 'style="display:none;"' : '' );
        ?>>
                                    <th scope="row">
                                            <label for="hseparator"><?php 
        esc_html_e( 'Alt Text from', 'mpt' );
        ?></label>
                                    </th>
                                    <td class="tags radio-list">
                                        <label class="radio">
                                            <input value="source" name="MPT_plugin_main_settings[alt_from]" type="radio" <?php 
        echo ( !empty( $options['alt_from'] ) && $options['alt_from'] == 'source' ? 'checked' : '' );
        ?>><span></span> 
                                            <?php 
        esc_html_e( 'Source', 'mpt' );
        ?>
                                        </label>
                                        <label class="radio">
                                            <input value="based_on" name="MPT_plugin_main_settings[alt_from]" type="radio" <?php 
        echo ( !empty( $options['alt_from'] ) && $options['alt_from'] == 'based_on' ? 'checked' : '' );
        ?>> <span></span>
                                            <?php 
        esc_html_e( 'Text "based_on"', 'mpt' );
        ?>
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
        esc_html_e( 'Translation', 'mpt' );
        ?>
                                    </th>
                                    <td class="checkbox-list">
                                            <label class="checkbox">
                                                <input name="MPT_plugin_main_settings[translate_alt]" type="checkbox" value="true" <?php 
        echo ( !empty( $options['translate_alt'] ) && $options['translate_alt'] == 'true' ? 'checked' : '' );
        ?>><span></span> 
                                                <?php 
        esc_html_e( 'Translate alt text from english to', 'mpt' );
        ?>:
                                            </label>

                                            <select name="MPT_plugin_main_settings[translate_alt_lang]" class="form-control form-control-lg" >
                                                <?php 
        $country_choose = array(
            __( 'Afrikaans', 'mpt' )             => 'af',
            __( 'Afrikaans', 'mpt' )             => 'af',
            __( 'Albanian', 'mpt' )              => 'sq',
            __( 'Amharic', 'mpt' )               => 'sm',
            __( 'Arabic', 'mpt' )                => 'ar',
            __( 'Azerbaijani', 'mpt' )           => 'az',
            __( 'Basque', 'mpt' )                => 'eu',
            __( 'Belarusian', 'mpt' )            => 'be',
            __( 'Bengali', 'mpt' )               => 'bn',
            __( 'Bihari', 'mpt' )                => 'bh',
            __( 'Bosnian', 'mpt' )               => 'bs',
            __( 'Bulgarian', 'mpt' )             => 'bg',
            __( 'Catalan', 'mpt' )               => 'ca',
            __( 'Chinese (Simplified)', 'mpt' )  => 'zh-CN',
            __( 'Chinese (Traditional)', 'mpt' ) => 'zh-TW',
            __( 'Croatian', 'mpt' )              => 'hr',
            __( 'Czech', 'mpt' )                 => 'cs',
            __( 'Danish', 'mpt' )                => 'da',
            __( 'Dutch', 'mpt' )                 => 'nl',
            __( 'Esperanto', 'mpt' )             => 'eo',
            __( 'Estonian', 'mpt' )              => 'et',
            __( 'Faroese', 'mpt' )               => 'fo',
            __( 'Finnish', 'mpt' )               => 'fi',
            __( 'French', 'mpt' )                => 'fr',
            __( 'Frisian', 'mpt' )               => 'fy',
            __( 'Galician', 'mpt' )              => 'gl',
            __( 'Georgian', 'mpt' )              => 'ka',
            __( 'German', 'mpt' )                => 'de',
            __( 'Greek', 'mpt' )                 => 'el',
            __( 'Gujarati', 'mpt' )              => 'gu',
            __( 'Hebrew', 'mpt' )                => 'iw',
            __( 'Hindi', 'mpt' )                 => 'hi',
            __( 'Hungarian', 'mpt' )             => 'hu',
            __( 'Icelandic', 'mpt' )             => 'is',
            __( 'Indonesian', 'mpt' )            => 'id',
            __( 'Interlingua', 'mpt' )           => 'ia',
            __( 'Irish', 'mpt' )                 => 'ga',
            __( 'Italian', 'mpt' )               => 'it',
            __( 'Japanese', 'mpt' )              => 'ja',
            __( 'Javanese', 'mpt' )              => 'jw',
            __( 'Kannada', 'mpt' )               => 'kn',
            __( 'Korean', 'mpt' )                => 'ko',
            __( 'Latin', 'mpt' )                 => 'la',
            __( 'Latvian', 'mpt' )               => 'lv',
            __( 'Lithuanian', 'mpt' )            => 'lt',
            __( 'Macedonian', 'mpt' )            => 'mk',
            __( 'Malay', 'mpt' )                 => 'ms',
            __( 'Malayam', 'mpt' )               => 'ml',
            __( 'Maltese', 'mpt' )               => 'mt',
            __( 'Marathi', 'mpt' )               => 'mr',
            __( 'Nepali', 'mpt' )                => 'ne',
            __( 'Norwegian', 'mpt' )             => 'no',
            __( 'Norwegian (Nynorsk)', 'mpt' )   => 'nn',
            __( 'Occitan', 'mpt' )               => 'oc',
            __( 'Persian', 'mpt' )               => 'fa',
            __( 'Polish', 'mpt' )                => 'pl',
            __( 'Portuguese (Brazil)', 'mpt' )   => 'pt-BR',
            __( 'Portuguese (Portugal)', 'mpt' ) => 'pt-PT',
            __( 'Punjabi', 'mpt' )               => 'pa',
            __( 'Romanian', 'mpt' )              => 'ro',
            __( 'Russian', 'mpt' )               => 'ru',
            __( 'Scots Gaelic', 'mpt' )          => 'gd',
            __( 'Serbian', 'mpt' )               => 'sr',
            __( 'Sinhalese', 'mpt' )             => 'si',
            __( 'Slovak', 'mpt' )                => 'sk',
            __( 'Slovenian', 'mpt' )             => 'sl',
            __( 'Spanish', 'mpt' )               => 'es',
            __( 'Sudanese', 'mpt' )              => 'su',
            __( 'Swahili', 'mpt' )               => 'sw',
            __( 'Swedish', 'mpt' )               => 'sv',
            __( 'Tagalog', 'mpt' )               => 'tl',
            __( 'Tamil', 'mpt' )                 => 'ta',
            __( 'Telugu', 'mpt' )                => 'te',
            __( 'Thai', 'mpt' )                  => 'th',
            __( 'Tigrinya', 'mpt' )              => 'ti',
            __( 'Turkish', 'mpt' )               => 'tr',
            __( 'Ukrainian', 'mpt' )             => 'uk',
            __( 'Urdu', 'mpt' )                  => 'ur',
            __( 'Uzbek', 'mpt' )                 => 'uz',
            __( 'Vietnamese', 'mpt' )            => 'vi',
            __( 'Welsh', 'mpt' )                 => 'cy',
            __( 'Xhosa', 'mpt' )                 => 'xh',
            __( 'Zulu', 'mpt' )                  => 'zu',
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
    esc_html_e( 'Add alt tag on image', 'mpt' );
    ?><br/>
                                            <small><?php 
    esc_html_e( 'Only available with the pro version', 'mpt' );
    ?></small>
                                        </label>
                                    </th>
                                    <td>
                                        <label class="checkbox checkbox-disabled checkbox-admin">
                                            <input disabled="disabled" data-switch="true" type="checkbox" name="MPT_plugin_main_settings[enable_alt]" id="enable_alt" value="disable" />
                                        </label>
                                    </td>
                                </tr>
                            <?php 
}
?>






                            <tr valign="top" class="based_on_bottom">
                                <td colspan="2">
                                    <p class="description">
                                        <div class="alert alert-custom alert-default" role="alert">
                                            <div class="alert-icon"><span class="svg-icon svg-icon-primary svg-icon-xl"><svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
                                                <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                                    <rect x="0" y="0" width="24" height="24"></rect>
                                                    <path d="M7.07744993,12.3040451 C7.72444571,13.0716094 8.54044565,13.6920474 9.46808594,14.1079953 L5,23 L4.5,18 L7.07744993,12.3040451 Z M14.5865511,14.2597864 C15.5319561,13.9019016 16.375416,13.3366121 17.0614026,12.6194459 L19.5,18 L19,23 L14.5865511,14.2597864 Z M12,3.55271368e-14 C12.8284271,3.53749572e-14 13.5,0.671572875 13.5,1.5 L13.5,4 L10.5,4 L10.5,1.5 C10.5,0.671572875 11.1715729,3.56793164e-14 12,3.55271368e-14 Z" fill="#000000" opacity="0.3"></path>
                                                    <path d="M12,10 C13.1045695,10 14,9.1045695 14,8 C14,6.8954305 13.1045695,6 12,6 C10.8954305,6 10,6.8954305 10,8 C10,9.1045695 10.8954305,10 12,10 Z M12,13 C9.23857625,13 7,10.7614237 7,8 C7,5.23857625 9.23857625,3 12,3 C14.7614237,3 17,5.23857625 17,8 C17,10.7614237 14.7614237,13 12,13 Z" fill="#000000" fill-rule="nonzero"></path>
                                                </g>
                                            </svg><!--end::Svg Icon--></span>
                                            </div>
                                            <div class="alert-text">
                                                <?php 
_e( 'Enable the "save_post" & "wp_insert_post" hook. <br/><br/>What does it means ? Everytime you create or update a post, an image will be generated.<br/><br/>Warning: Use it precautionary. If you don\'t understand what it is, disable it.', 'mpt' );
?>
                                            </div>
                                        </div>
                                    </p>
                                </td>
                            </tr>


                            <tr valign="top" >
                                <th scope="row">
                                    <label for="hseparator"><?php 
esc_html_e( '"Save Post" Hook', 'mpt' );
?></label>
                                </th>
                                <td>
                                    <label class="checkbox">
                                        <input data-switch="true" type="checkbox" name="MPT_plugin_main_settings[enable_save_post_hook]" id="enable_save_post_hook" value="enable" <?php 
echo ( !empty( $options['enable_save_post_hook'] ) && $options['enable_save_post_hook'] == 'enable' ? 'checked' : '' );
?> />
                                    </label>
                                </td>
                            </tr>


                            <tr valign="top" class="show_save_post_hook" <?php 
echo ( !isset( $options['enable_save_post_hook'] ) || $options['enable_save_post_hook'] != 'enable' ? 'style="display:none;"' : '' );
?>>
                                <td colspan="3" class="infos">
                                    <div class="alert alert-custom alert-default" role="alert">
                                        <div class="alert-icon"><span class="svg-icon svg-icon-primary svg-icon-xl"><svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
                                            <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                                <rect x="0" y="0" width="24" height="24"></rect>
                                                <path d="M7.07744993,12.3040451 C7.72444571,13.0716094 8.54044565,13.6920474 9.46808594,14.1079953 L5,23 L4.5,18 L7.07744993,12.3040451 Z M14.5865511,14.2597864 C15.5319561,13.9019016 16.375416,13.3366121 17.0614026,12.6194459 L19.5,18 L19,23 L14.5865511,14.2597864 Z M12,3.55271368e-14 C12.8284271,3.53749572e-14 13.5,0.671572875 13.5,1.5 L13.5,4 L10.5,4 L10.5,1.5 C10.5,0.671572875 11.1715729,3.56793164e-14 12,3.55271368e-14 Z" fill="#000000" opacity="0.3"></path>
                                                <path d="M12,10 C13.1045695,10 14,9.1045695 14,8 C14,6.8954305 13.1045695,6 12,6 C10.8954305,6 10,6.8954305 10,8 C10,9.1045695 10.8954305,10 12,10 Z M12,13 C9.23857625,13 7,10.7614237 7,8 C7,5.23857625 9.23857625,3 12,3 C14.7614237,3 17,5.23857625 17,8 C17,10.7614237 14.7614237,13 12,13 Z" fill="#000000" fill-rule="nonzero"></path>
                                            </g>
                                        </svg><!--end::Svg Icon--></span>
                                        </div>
                                        <div class="alert-text">
                                            <?php 
esc_html_e( 'Select the post type on which you want to fire the hook.', 'mpt' );
?>
                                        </div>
                                    </div>
                                </td>
                            </tr>

                            <tr valign="top" class="show_save_post_hook" <?php 
echo ( !isset( $options['enable_save_post_hook'] ) || $options['enable_save_post_hook'] != 'enable' ? 'style="display:none;"' : '' );
?>>
                                    <th scope="row">
                                        <?php 
esc_html_e( '"Save Post" Hook post type', 'mpt' );
?>
                                    </th>
                                    <td class="post-type checkbox-list">
                                            <label class="checkbox"><input type="checkbox" name="select-all-pt" id="select-all-pt"/><span></span> <?php 
esc_html_e( 'Select all', 'mpt' );
?></label>
                                            <?php 
$post_types_default = get_post_types( '', 'objects' );
unset($post_types_default['attachment'], $post_types_default['revision'], $post_types_default['nav_menu_item']);
foreach ( $post_types_default as $post_type ) {
    if ( post_type_supports( $post_type->name, 'thumbnail' ) == 'true' ) {
        if ( !$options['choosed_save_post_post_type'] ) {
            $checked = 'checked';
        } else {
            $checked = ( isset( $options['choosed_save_post_post_type'][$post_type->name] ) ? 'checked' : '' );
        }
        echo '<label class="checkbox">
                                                                            <input ' . $checked . ' name="MPT_plugin_main_settings[choosed_save_post_post_type][' . $post_type->name . ']" type="checkbox" value="' . $post_type->name . '"><span></span> ' . $post_type->labels->name . '
                                                                    </label>';
    }
}
?>
                                    </td>
                            </tr>

                            <?php 
if ( true === $this->MPT_freemius()->is__premium_only() ) {
    if ( $this->mpt_freemius()->can_use_premium_code() ) {
        ?>
                                <tr valign="top" class="wp_insert_post">
                                    <th scope="row">
                                        <label for="hseparator"><?php 
        esc_html_e( '"WP Insert Post" Hook', 'mpt' );
        ?></label>
                                    </th>
                                    <td>
                                        <label class="checkbox checkbox-admin">
                                            <input data-switch="true" type="checkbox" name="MPT_plugin_main_settings[enable_wp_insert_post_hook]" id="enable_wp_insert_post_hook" value="enable" <?php 
        echo ( !empty( $options['enable_wp_insert_post_hook'] ) && $options['enable_wp_insert_post_hook'] == 'enable' ? 'checked' : '' );
        ?> />
                                        </label>
                                    </td>
                                </tr>


                                <tr valign="top" class="show_wp_insert_post_hook" <?php 
        echo ( !isset( $options['enable_wp_insert_post_hook'] ) || $options['enable_wp_insert_post_hook'] != 'enable' ? 'style="display:none;"' : '' );
        ?>>
                                    <td colspan="3" class="infos">
                                        <div class="alert alert-custom alert-default" role="alert">
                                            <div class="alert-icon"><span class="svg-icon svg-icon-primary svg-icon-xl"><svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
                                                <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                                    <rect x="0" y="0" width="24" height="24"></rect>
                                                    <path d="M7.07744993,12.3040451 C7.72444571,13.0716094 8.54044565,13.6920474 9.46808594,14.1079953 L5,23 L4.5,18 L7.07744993,12.3040451 Z M14.5865511,14.2597864 C15.5319561,13.9019016 16.375416,13.3366121 17.0614026,12.6194459 L19.5,18 L19,23 L14.5865511,14.2597864 Z M12,3.55271368e-14 C12.8284271,3.53749572e-14 13.5,0.671572875 13.5,1.5 L13.5,4 L10.5,4 L10.5,1.5 C10.5,0.671572875 11.1715729,3.56793164e-14 12,3.55271368e-14 Z" fill="#000000" opacity="0.3"></path>
                                                    <path d="M12,10 C13.1045695,10 14,9.1045695 14,8 C14,6.8954305 13.1045695,6 12,6 C10.8954305,6 10,6.8954305 10,8 C10,9.1045695 10.8954305,10 12,10 Z M12,13 C9.23857625,13 7,10.7614237 7,8 C7,5.23857625 9.23857625,3 12,3 C14.7614237,3 17,5.23857625 17,8 C17,10.7614237 14.7614237,13 12,13 Z" fill="#000000" fill-rule="nonzero"></path>
                                                </g>
                                            </svg><!--end::Svg Icon--></span>
                                            </div>
                                            <div class="alert-text">
                                                <?php 
        esc_html_e( 'Select the post type on which you want to fire the hook.', 'mpt' );
        ?>
                                            </div>
                                        </div>
                                    </td>
                                </tr>

                                <tr valign="top" class="show_wp_insert_post_hook" <?php 
        echo ( !isset( $options['enable_wp_insert_post_hook'] ) || $options['enable_wp_insert_post_hook'] != 'enable' ? 'style="display:none;"' : '' );
        ?>>
                                        <th scope="row">
                                            <?php 
        esc_html_e( '"WP Insert Post" Hook post type', 'mpt' );
        ?>
                                        </th>
                                        <td class="post-type-2 checkbox-list">
                                                <label class="checkbox"><input type="checkbox" name="select-all-pt-2" id="select-all-pt-2"/><span></span> <?php 
        esc_html_e( 'Select all', 'mpt' );
        ?></label>
                                                <?php 
        $post_types_default = get_post_types( '', 'objects' );
        unset($post_types_default['attachment'], $post_types_default['revision'], $post_types_default['nav_menu_item']);
        foreach ( $post_types_default as $post_type ) {
            if ( post_type_supports( $post_type->name, 'thumbnail' ) == 'true' ) {
                if ( !$options['choosed_wp_insert_post_type'] ) {
                    $checked = 'checked';
                } else {
                    $checked = ( isset( $options['choosed_wp_insert_post_type'][$post_type->name] ) ? 'checked' : '' );
                }
                echo '<label class="checkbox">
                                                                                <input ' . $checked . ' name="MPT_plugin_main_settings[choosed_wp_insert_post_type][' . $post_type->name . ']" type="checkbox" value="' . $post_type->name . '"><span></span> ' . $post_type->labels->name . '
                                                                        </label>';
            }
        }
        ?>
                                        </td>
                                </tr>



                            <?php 
    }
} else {
    ?>
                                <tr valign="top" class="wp_insert_post">
                                    <th scope="row">
                                        <label for="hseparator">
                                            <?php 
    esc_html_e( '"WP Insert Post" Hook', 'mpt' );
    ?><br/>
                                            <small><?php 
    esc_html_e( 'Only available with the pro version', 'mpt' );
    ?></small>
                                        </label>
                                    </th>
                                    <td>
                                        <label class="checkbox checkbox-disabled checkbox-admin">
                                            <input disabled="disabled" data-switch="true" type="checkbox" name="MPT_plugin_main_settings[enable_wp_insert_post_hook]" id="enable_wp_insert_post_hook" value="disable" />
                                        </label>
                                    </td>
                                </tr>
                            <?php 
}
?>
                            <tr valign="top" class="based_on_bottom">
                                <th scope="row">
                                    <label for="hseparator"><?php 
esc_html_e( 'Interval', 'mpt' );
?></label>
                                </th>
                                <td class="chosen_api">
                                    <div class="form-group">
                                        <span class="tooltip-recommended"><?php 
esc_html_e( 'Recommended Interval', 'mpt' );
?></span>
                                        <input type="range" class="custom-range" min="0" max="7" id="bulk-generation-interval" value="0<?php 
//echo $value_bulk_generation_interval;
?>" name="MPT_plugin_main_settings[bulk_generation_interval]">
                                        <p class="btn btn-light-primary font-weight-bolder btn-sm paragraph-interval-value">
                                            <span id="value-interval"></span>
                                        </p>
                                    </div>
                                </td>
                            </tr>

                    </tbody>
            </table>
        </div>

        <?php 
submit_button();
?>

    </form>
</div>
