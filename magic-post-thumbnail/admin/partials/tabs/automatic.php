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
if ( !$this->mpt_freemius()->is__premium_only() && current_time( 'U' ) < 1764543599 ) {
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
				Get a <strong>30% discount for BLACK FRIDAY until November 30</strong> when you upgrade to the <a href="admin.php?page=magic-post-thumbnail-admin-display-pricing">Pro version</a> with the code: <strong>MPTBLACKFRIDAY25</strong>
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

        <div id="tabs" class="form-table tabs-content">
            <ul>
                <li>
                    <a href="#tab-0">
                        <?php 
esc_html_e( 'Image Placement', 'mpt' );
?>
                    </a>
                </li>
                <li>
                    <a href="#tab-1">
                        <?php 
esc_html_e( 'Post-Processing Image', 'mpt' );
?>
                    </a>
                </li>
                <li>
                    <a href="#tab-2">
                        <?php 
esc_html_e( 'Pre-Processing & Developer', 'mpt' );
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
