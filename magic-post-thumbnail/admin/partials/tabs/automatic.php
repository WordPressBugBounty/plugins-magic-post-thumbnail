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
$this->MPT_promo_black_friday_html();
?>


    <?php 
settings_errors();
?>

    <form method="post" action="options.php" class="form-images">

        <?php 
settings_fields( 'MPT-plugin-main-settings' );
$options = wp_parse_args( get_option( 'MPT_plugin_main_settings' ), $this->MPT_default_options_main_settings( TRUE ) );
$options_compatibility = wp_parse_args( get_option( 'MPT_plugin_compatibility_settings' ), $this->MPT_default_options_compatibility_settings( TRUE ) );
$image_blocks = ( isset( $options['image_block'] ) ? $options['image_block'] : array() );
$executeElseBlock = true;
if ( $executeElseBlock ) {
    // Keep only the first key in the table
    $image_blocks = array_slice(
        $image_blocks,
        0,
        1,
        true
    );
}
// Calculating the current block index based on existing blocks
$blockIndex = count( $image_blocks ) + 1;
// Starts after the existing blocks
// Get the value for interval
$value_bulk_generation_interval = ( isset( $options['bulk_generation_interval'] ) ? (int) $options['bulk_generation_interval'] : 0 );
// Remove transient if we do not want anymore the interval generation
if ( 0 == $value_bulk_generation_interval ) {
    delete_transient( 'MPT_interval_generation' );
}
$image_sizes = get_intermediate_image_sizes();
array_push( $image_sizes, 'full' );
?>

        <div id="mpt-automatic-tabs" class="form-table tabs-content">
            <ul>
                <li>
                    <a href="#tab-0">
                        <?php 
esc_html_e( 'Image Placement', 'magic-post-thumbnail' );
?>
                    </a>
                </li>
                <li>
                    <a href="#tab-1">
                        <?php 
esc_html_e( 'Post-Processing Image', 'magic-post-thumbnail' );
?>
                    </a>
                </li>
                <li>
                    <a href="#tab-2">
                        <?php 
esc_html_e( 'Pre-Processing & Developer', 'magic-post-thumbnail' );
?>
                    </a>
                </li>
            </ul>
            
            <table id="tab-0" class="form-table">
                <tbody>
                    <?php 
include_once 'automatic/places.php';
?>
                </tbody>
            </table>
            <table id="tab-1" class="form-table" style="display: none;">
                <tbody>
                    <?php 
include_once 'automatic/alteration.php';
?>
                </tbody>
            </table>

            <table id="tab-2" class="form-table" style="display: none;">
                <tbody>
                    <?php 
include_once 'automatic/developer.php';
?>
                </tbody>
            </table>
        </div>

        <?php 
submit_button( '', 'button-primary button-hero' );
?>

    </form>
</div>
