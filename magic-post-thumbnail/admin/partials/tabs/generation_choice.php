<?php
    $options = wp_parse_args( get_option( 'MPT_plugin_main_settings' ), $this->MPT_default_options_main_settings( TRUE ) );
    $value_bulk_generation_interval = ( isset( $options['bulk_generation_interval'] ) )? (int)$options['bulk_generation_interval'] : 0;
    
    include_once('bulk_generation.php');
?>
