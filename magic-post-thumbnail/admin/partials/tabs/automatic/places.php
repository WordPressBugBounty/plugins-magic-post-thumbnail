<?php

if ( !function_exists( 'add_filter' ) ) {
    header( 'Status: 403 Forbidden' );
    header( 'HTTP/1.1 403 Forbidden' );
    exit;
}
/* Banks for Automatic Bulk */
$list_api_auto = $this->MPT_banks_name_auto();
include_once 'places_template.php';
// Display saved blocks
if ( !empty( $image_blocks ) ) {
    $blockIndex = 1;
    foreach ( $image_blocks as $index => $block ) {
        $class_inside_image = '';
        $class_field_image = '';
        $class_analyzer = '';
        if ( 'custom' != $block['image_location'] ) {
            $class_inside_image = 'hidden';
            $class_analyzer = 'hidden';
        }
        if ( !in_array( $block['image_location'], ['cmb2', 'acf', 'metaboxio'] ) ) {
            $class_field_image = 'hidden';
        }
        ?>
            <!-- Template for displaying saved blocks -->
            <tr valign="top" class="image-location-template image-block-<?php 
        echo $blockIndex;
        ?> top-add-block-img">
                <th scope="row">
                    <?php 
        esc_html_e( 'Featured Image / Inline Content', 'magic-post-thumbnail' );
        ?>
                </th>
                <td class="image_location radio-list">
                    <label class="radio radio-outline radio-outline-2x radio-primary">
                        <input value="featured" name="MPT_plugin_main_settings[image_block][<?php 
        echo $blockIndex;
        ?>][image_location]" type="radio" <?php 
        checked( $block['image_location'], 'featured' );
        ?>>
                        <span></span> <?php 
        esc_html_e( 'Featured Image', 'magic-post-thumbnail' );
        ?>
                    </label>
                    <label class="radio radio-outline radio-outline-2x radio-primary">
                        <input value="custom" name="MPT_plugin_main_settings[image_block][<?php 
        echo $blockIndex;
        ?>][image_location]" type="radio" <?php 
        checked( $block['image_location'], 'custom' );
        ?>>
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

            <tr valign="top" class="section_custom_image_position image-location-template <?php 
        echo $class_inside_image;
        ?> image-inside-content image-block-<?php 
        echo $blockIndex;
        ?> mid-add-block-img">
                <th scope="row">
                    <label for="hseparator"><?php 
        esc_html_e( 'Image position', 'magic-post-thumbnail' );
        ?></label>
                </th>
                <td class="custom_image_location" valign="top">
                    <label><?php 
        esc_html_e( 'Insert', 'magic-post-thumbnail' );
        ?>
                        <select name="MPT_plugin_main_settings[image_block][<?php 
        echo $blockIndex;
        ?>][image_custom_location_placement]" class="select-custom-location form-control">
                            <option value="before" <?php 
        selected( ( isset( $block['image_custom_location_placement'] ) ? $block['image_custom_location_placement'] : '' ), 'before' );
        ?>><?php 
        esc_html_e( 'Before', 'magic-post-thumbnail' );
        ?></option>
                            <option value="after" <?php 
        selected( ( isset( $block['image_custom_location_placement'] ) ? $block['image_custom_location_placement'] : '' ), 'after' );
        ?>><?php 
        esc_html_e( 'After', 'magic-post-thumbnail' );
        ?></option>
                        </select> 
                        <?php 
        esc_html_e( 'the', 'magic-post-thumbnail' );
        ?>
                        <select name="MPT_plugin_main_settings[image_block][<?php 
        echo $blockIndex;
        ?>][image_custom_location_position]" class="select-custom-location form-control">
                            <option value="1" <?php 
        selected( ( isset( $block['image_custom_location_position'] ) ? $block['image_custom_location_position'] : '' ), '1' );
        ?>><?php 
        esc_html_e( 'First', 'magic-post-thumbnail' );
        ?></option>
                            <option value="2" <?php 
        selected( ( isset( $block['image_custom_location_position'] ) ? $block['image_custom_location_position'] : '' ), '2' );
        ?>><?php 
        esc_html_e( 'Second', 'magic-post-thumbnail' );
        ?></option>
                            <option value="3" <?php 
        selected( ( isset( $block['image_custom_location_position'] ) ? $block['image_custom_location_position'] : '' ), '3' );
        ?>><?php 
        esc_html_e( 'Third', 'magic-post-thumbnail' );
        ?></option>
                            <option value="4" <?php 
        selected( ( isset( $block['image_custom_location_position'] ) ? $block['image_custom_location_position'] : '' ), '4' );
        ?>><?php 
        esc_html_e( 'Fourth', 'magic-post-thumbnail' );
        ?></option>
                            <option value="5" <?php 
        selected( ( isset( $block['image_custom_location_position'] ) ? $block['image_custom_location_position'] : '' ), '5' );
        ?>><?php 
        esc_html_e( 'Fifth', 'magic-post-thumbnail' );
        ?></option>
                            <option value="6" <?php 
        selected( ( isset( $block['image_custom_location_position'] ) ? $block['image_custom_location_position'] : '' ), '6' );
        ?>><?php 
        esc_html_e( 'Sixth', 'magic-post-thumbnail' );
        ?></option>
                            <option value="7" <?php 
        selected( ( isset( $block['image_custom_location_position'] ) ? $block['image_custom_location_position'] : '' ), '7' );
        ?>><?php 
        esc_html_e( 'Seventh', 'magic-post-thumbnail' );
        ?></option>
                            <option value="8" <?php 
        selected( ( isset( $block['image_custom_location_position'] ) ? $block['image_custom_location_position'] : '' ), '8' );
        ?>><?php 
        esc_html_e( 'Eighth', 'magic-post-thumbnail' );
        ?></option>
                            <option value="9" <?php 
        selected( ( isset( $block['image_custom_location_position'] ) ? $block['image_custom_location_position'] : '' ), '9' );
        ?>><?php 
        esc_html_e( 'Ninth', 'magic-post-thumbnail' );
        ?></option>
                            <option value="10" <?php 
        selected( ( isset( $block['image_custom_location_position'] ) ? $block['image_custom_location_position'] : '' ), '10' );
        ?>><?php 
        esc_html_e( 'Tenth', 'magic-post-thumbnail' );
        ?></option>
                            <option value="last" <?php 
        selected( ( isset( $block['image_custom_location_position'] ) ? $block['image_custom_location_position'] : '' ), 'last' );
        ?>><?php 
        esc_html_e( 'Last', 'magic-post-thumbnail' );
        ?></option>
                        </select>
                        <select name="MPT_plugin_main_settings[image_block][<?php 
        echo $blockIndex;
        ?>][image_custom_location_tag]" class="select-custom-location form-control">
                            <option value="p" <?php 
        selected( ( isset( $block['image_custom_location_tag'] ) ? $block['image_custom_location_tag'] : '' ), 'p' );
        ?>><?php 
        esc_html_e( 'paragraph (p)', 'magic-post-thumbnail' );
        ?></option>
                            <option value="h2" <?php 
        selected( ( isset( $block['image_custom_location_tag'] ) ? $block['image_custom_location_tag'] : '' ), 'h2' );
        ?>>h2</option>
                            <option value="h3" <?php 
        selected( ( isset( $block['image_custom_location_tag'] ) ? $block['image_custom_location_tag'] : '' ), 'h3' );
        ?>>h3</option>
                            <option value="h4" <?php 
        selected( ( isset( $block['image_custom_location_tag'] ) ? $block['image_custom_location_tag'] : '' ), 'h4' );
        ?>>h4</option>
                            <option value="h5" <?php 
        selected( ( isset( $block['image_custom_location_tag'] ) ? $block['image_custom_location_tag'] : '' ), 'h5' );
        ?>>h5</option>
                            <option value="h6" <?php 
        selected( ( isset( $block['image_custom_location_tag'] ) ? $block['image_custom_location_tag'] : '' ), 'h6' );
        ?>>h6</option>
                            <option value="div" <?php 
        selected( ( isset( $block['image_custom_location_tag'] ) ? $block['image_custom_location_tag'] : '' ), 'div' );
        ?>>div</option>
                            <option value="a" <?php 
        selected( ( isset( $block['image_custom_location_tag'] ) ? $block['image_custom_location_tag'] : '' ), 'a' );
        ?>><?php 
        esc_html_e( 'link (a)', 'magic-post-thumbnail' );
        ?></option>
                        </select>
                    </label>
                </td>
            </tr>
            

            <tr valign="top" class="section_custom_image_size image-location-template image-inside-content <?php 
        echo $class_inside_image;
        ?> image-block-<?php 
        echo $blockIndex;
        ?> mid-add-block-img">
                <th scope="row">
                    <label for="hseparator"><?php 
        esc_html_e( 'Image size', 'magic-post-thumbnail' );
        ?></label>
                </th>
                <td class="custom_image_size" valign="top">
                    <label>
                        <select name="MPT_plugin_main_settings[image_block][<?php 
        echo $blockIndex;
        ?>][image_custom_image_size]" class="select-custom-location form-control">
                            <?php 
        foreach ( $image_sizes as $image_size ) {
            ?>
                                <option value="<?php 
            echo $image_size;
            ?>" <?php 
            selected( ( isset( $block['image_custom_image_size'] ) ? $block['image_custom_image_size'] : '' ), $image_size );
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

            <tr valign="top" class="section_custom_image_bank image-block-<?php 
        echo $blockIndex;
        ?> mid-add-block-img">
                <th scope="row">
                    <label for="hseparator"><?php 
        esc_html_e( 'Image Source', 'magic-post-thumbnail' );
        ?></label>
                </th>
                <td class="custom_image_size" valign="top">
                    <label>
                        <?php 
        $current_bank = ( isset( $block['api_chosen'] ) ? $this->MPT_normalize_bank_slug( $block['api_chosen'] ) : 'default' );
        if ( '' === $current_bank ) {
            $current_bank = 'default';
        }
        ?>
                        <select name="MPT_plugin_main_settings[image_block][<?php 
        echo $blockIndex;
        ?>][api_chosen]" class="select-custom-location form-control">
                            <option value="default" <?php 
        selected( $current_bank, 'default' );
        ?>><?php 
        esc_html_e( 'Use the chain defined in Source', 'magic-post-thumbnail' );
        ?></option>
                            <?php 
        foreach ( $list_api_auto as $api_auto_name => $api_auto_id ) {
            if ( true === $api_auto_id[1] ) {
                $opt_disabled = '';
            } else {
                $opt_disabled = 'disabled';
            }
            ?>
                                        <option <?php 
            echo $opt_disabled;
            ?> value="<?php 
            echo esc_attr( $api_auto_id[0] );
            ?>" <?php 
            selected( $current_bank, $api_auto_id[0] );
            ?>><?php 
            echo esc_html( $api_auto_name );
            ?></option>
                            <?php 
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

            <?php 
        include 'basedon.php';
        $class_remove_btn = 'remove-block-btn-hidden';
        ?>

            <!-- Button to remove a block -->
            <tr valign="top" class="image-location-template image-block-<?php 
        echo $blockIndex;
        ?> bottom-add-block-img">
                <td colspan="2">
                    <button type="button" class="btn btn-sm font-weight-bolder btn-light-danger remove-block-btn <?php 
        echo $class_remove_btn;
        ?>" style="text-decoration: none;">[-] <?php 
        esc_html_e( 'Delete', 'magic-post-thumbnail' );
        ?></button>
                </td>
            </tr>

            <tr class="image-location-template image-block-<?php 
        echo $blockIndex;
        ?>">
                <td colspan="2">
                    <hr/>
                </td>
            </tr>
            <?php 
        $blockIndex++;
        // Increment block index for the next block
    }
}
?>

    <!-- Button to add a new block dynamically -->
    <tr class="cloneBlock">
        <td colspan="2">
            <?php 
$cloneID = 'add-image-btn-disabled';
?>
            <a href="#" class="btn font-weight-bolder btn-light-primary text-uppercase py-4 px-6" style="text-decoration: none;" id="<?php 
echo $cloneID;
?>">
                [+] <?php 
esc_html_e( 'Add an image location', 'magic-post-thumbnail' );
?>
            </a>
        </td>
    </tr>



    <tr>
        <td colspan="2">
            <hr/>
        </td>
    </tr>