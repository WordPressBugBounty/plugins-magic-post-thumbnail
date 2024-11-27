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
        esc_html_e( 'Featured Image / Inline Content', 'mpt' );
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
        esc_html_e( 'Featured Image', 'mpt' );
        ?>
                    </label>
                    <label class="radio radio-outline radio-outline-2x radio-primary">
                        <input value="custom" name="MPT_plugin_main_settings[image_block][<?php 
        echo $blockIndex;
        ?>][image_location]" type="radio" <?php 
        checked( $block['image_location'], 'custom' );
        ?>>
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

            <tr valign="top" class="section_custom_image_position image-location-template <?php 
        echo $class_inside_image;
        ?> image-inside-content image-block-<?php 
        echo $blockIndex;
        ?> mid-add-block-img">
                <th scope="row">
                    <label for="hseparator"><?php 
        esc_html_e( 'Image position', 'mpt' );
        ?></label>
                </th>
                <td class="custom_image_location" valign="top">
                    <label><?php 
        esc_html_e( 'Insert', 'mpt' );
        ?>
                        <select name="MPT_plugin_main_settings[image_block][<?php 
        echo $blockIndex;
        ?>][image_custom_location_placement]" class="select-custom-location form-control">
                            <option value="before" <?php 
        selected( ( isset( $block['image_custom_location_placement'] ) ? $block['image_custom_location_placement'] : '' ), 'before' );
        ?>><?php 
        esc_html_e( 'Before', 'mpt' );
        ?></option>
                            <option value="after" <?php 
        selected( ( isset( $block['image_custom_location_placement'] ) ? $block['image_custom_location_placement'] : '' ), 'after' );
        ?>><?php 
        esc_html_e( 'After', 'mpt' );
        ?></option>
                        </select> 
                        <?php 
        esc_html_e( 'the', 'mpt' );
        ?>
                        <select name="MPT_plugin_main_settings[image_block][<?php 
        echo $blockIndex;
        ?>][image_custom_location_position]" class="select-custom-location form-control">
                            <option value="1" <?php 
        selected( ( isset( $block['image_custom_location_position'] ) ? $block['image_custom_location_position'] : '' ), '1' );
        ?>><?php 
        esc_html_e( 'First', 'mpt' );
        ?></option>
                            <option value="2" <?php 
        selected( ( isset( $block['image_custom_location_position'] ) ? $block['image_custom_location_position'] : '' ), '2' );
        ?>><?php 
        esc_html_e( 'Second', 'mpt' );
        ?></option>
                            <option value="3" <?php 
        selected( ( isset( $block['image_custom_location_position'] ) ? $block['image_custom_location_position'] : '' ), '3' );
        ?>><?php 
        esc_html_e( 'Third', 'mpt' );
        ?></option>
                            <option value="4" <?php 
        selected( ( isset( $block['image_custom_location_position'] ) ? $block['image_custom_location_position'] : '' ), '4' );
        ?>><?php 
        esc_html_e( 'Fourth', 'mpt' );
        ?></option>
                            <option value="5" <?php 
        selected( ( isset( $block['image_custom_location_position'] ) ? $block['image_custom_location_position'] : '' ), '5' );
        ?>><?php 
        esc_html_e( 'Fifth', 'mpt' );
        ?></option>
                            <option value="last" <?php 
        selected( ( isset( $block['image_custom_location_position'] ) ? $block['image_custom_location_position'] : '' ), 'last' );
        ?>><?php 
        esc_html_e( 'Last', 'mpt' );
        ?></option>
                        </select>
                        <select name="MPT_plugin_main_settings[image_block][<?php 
        echo $blockIndex;
        ?>][image_custom_location_tag]" class="select-custom-location form-control">
                            <option value="p" <?php 
        selected( ( isset( $block['image_custom_location_tag'] ) ? $block['image_custom_location_tag'] : '' ), 'p' );
        ?>><?php 
        esc_html_e( 'paragraph (p)', 'mpt' );
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
        esc_html_e( 'link (a)', 'mpt' );
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
        esc_html_e( 'Image size', 'mpt' );
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
        esc_html_e( 'Image Source', 'mpt' );
        ?></label>
                </th>
                <td class="custom_image_size" valign="top">
                    <label>
                        <select name="MPT_plugin_main_settings[image_block][<?php 
        echo $blockIndex;
        ?>][api_chosen]" class="select-custom-location form-control">
                            <?php 
        foreach ( $list_api_auto as $api_auto_name => $api_auto_id ) {
            if ( true === $api_auto_id[1] ) {
                $disabled = '';
            } else {
                $disabled = 'disabled';
            }
            ?>
                                        <option <?php 
            echo $disabled;
            ?> value="<?php 
            echo $api_auto_id[0];
            ?>" <?php 
            echo ( isset( $block['api_chosen'] ) ? selected( $block['api_chosen'], $api_auto_id[0], false ) : '' );
            ?>><?php 
            echo $api_auto_name;
            ?></option>
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
        esc_html_e( 'Second Image Source', 'mpt' );
        ?></label>
                </th>
                <td class="custom_image_size" valign="top">
                    <label>
                        <select name="MPT_plugin_main_settings[image_block][<?php 
        echo $blockIndex;
        ?>][api_chosen_2]" class="select-custom-location form-control">
                            <option value="none"><?php 
        esc_html_e( 'None', 'mpt' );
        ?></option>
                            <?php 
        foreach ( $list_api_auto as $api_auto_name => $api_auto_id ) {
            if ( true === $api_auto_id[1] ) {
                $disabled = '';
            } else {
                $disabled = 'disabled';
            }
            ?>
                                        <option <?php 
            echo $disabled;
            ?> value="<?php 
            echo $api_auto_id[0];
            ?>" <?php 
            echo ( isset( $block['api_chosen_2'] ) ? selected( $block['api_chosen_2'], $api_auto_id[0], false ) : '' );
            ?>><?php 
            echo $api_auto_name;
            ?></option>
                            <?php 
        }
        ?>
                        </select> 
                        <i><?php 
        esc_html_e( 'Second Image Source, just in case the first one doesn\'t work', 'mpt' );
        ?></i>
                    </label>
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
        esc_html_e( 'Delete', 'mpt' );
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
esc_html_e( 'Add an image location', 'mpt' );
?>
            </a>
        </td>
    </tr>



    <tr>
        <td colspan="2">
            <hr/>
        </td>
    </tr>