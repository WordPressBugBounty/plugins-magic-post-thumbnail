<?php
if ( ! function_exists( 'add_filter' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
		exit();
}

?>
<div class="wrap">

        <?php $this->MPT_promo_black_friday_html(); ?>

        <?php

                $options_banks = wp_parse_args( get_option( 'MPT_plugin_banks_settings' ), $this->MPT_default_options_banks_settings( TRUE ) );
                $value_bulk_generation_interval = ( isset( $options['bulk_generation_interval'] ) )? (int)$options['bulk_generation_interval'] : 0;

                $bulk_generation_interval = false;
                if( 
                        ( 'bulk-generation' === $module ) && ( 0 !== $value_bulk_generation_interval ) ||
                        ( 'bulk-generation-interval' === $module )
                ) {
                        $bulk_generation_interval	= true;
                        $remaining_seconds 		= $this->cron_scheduled();
                }

                if (
                        ( ! empty( $_POST['mpt'] ) || ! empty( $_REQUEST['ids_mpt_generation'] ) || ! empty( $_REQUEST['cats'] ) ) &&
                        ( empty( $_REQUEST['settings-updated'] ) || $_REQUEST['settings-updated'] != 'true' )
                ) {

                if( !empty( $_REQUEST['cats'] ) ) {

                $taxo_term = get_term( $_REQUEST['cats'] );
                if( empty( $taxo_term ) )
                        return false;

                $cpts = get_post_types( array( 'public' => true ), 'names' );

                $post_ids = get_posts( array(
                        'numberposts'   => -1, // get all posts.
                        'tax_query'			=> array(
                                array(
                                'taxonomy' => $taxo_term->taxonomy,
                                'field'    => 'slug',
                                'terms'    => $taxo_term->slug,
                                ),
                        ),
                        'post_type'     => array(),
                        'post_status'		=> array( 'publish', 'draft', 'pending', 'future', 'private' ),
                        'fields'        => 'ids', // Only get post IDs
                ) );

                $ids = '';
                foreach( $post_ids as $post_id ) {
                        $ids .= $post_id.',';
                }

                $_GET['ids']                    = substr_replace($ids ,'', -1);
                $_REQUEST['ids_mpt_generation'] = $_GET['ids'];
                }

                $ids_mpt_generation = esc_attr( $_REQUEST['ids_mpt_generation'] );
                $ids_mpt_generation = explode( ',', $ids_mpt_generation );
    ?>
            <div id="hide-before-import" style="display:none">

                <div class="progressionbar clearfix ">
                        <div class="progressionbar-title"><span><?php esc_html_e( 'Progress', 'magic-post-thumbnail' ); ?></span></div>
                        <div class="progressionbar-bar"></div>
                        <div class="skill-bar-percent"><span>0</span>%</div>
                </div>

                <?php if( $bulk_generation_interval && ( TRUE == $bulk_generation_interval ) ) { ?>
                        <div class="time-progress">
                                <div class="progressionbar-title">
                                        <span><?php esc_html_e( 'Time Before Next Generation', 'magic-post-thumbnail' ); ?></span>
                                        <span class="remaining-time">
                                                <?php echo $remaining_seconds; ?>
                                                <?php esc_html_e( 'seconds', 'magic-post-thumbnail' ); ?>
                                        </span>
                                </div>
                        </div>
                <?php } ?>

            </div>

        <?php
                $slow_ai     = $this->MPT_ai_bank_slugs();
                $main_opts   = wp_parse_args( get_option( 'MPT_plugin_main_settings' ), $this->MPT_default_options_main_settings( true ) );
                $first_block = array( 'api_chosen' => 'default' );
                if ( isset( $main_opts['image_block'] ) && is_array( $main_opts['image_block'] ) ) {
                        $maybe = reset( $main_opts['image_block'] );
                        if ( is_array( $maybe ) ) {
                                $first_block = $maybe;
                        }
                }
                $chain      = $this->MPT_resolve_bank_chain( $first_block, $options_banks );
                $first_auto = reset( $chain );
                if ( in_array( $first_auto, $slow_ai, true ) ) {
        ?>
            <p class="dalle-wait">
                <?php esc_html_e( 'AI image generation may take 10 to 60 seconds. Please be patient', 'magic-post-thumbnail' ); ?>.
            </p>
                <?php } ?>

            <table class="wp-list-mpt wp-list-table widefat fixed striped posts">
              <thead>
                <tr>
                  <th scope="row"></th>
                  <th scope="col" id="title" class="manage-column column-primary"><?php esc_html_e( 'Title', 'magic-post-thumbnail' ); ?></th>
                  <th scope="col" id="status" class="manage-column"><?php esc_html_e( 'Status', 'magic-post-thumbnail' ); ?></th>
                  <th scope="col" id="categories" class="manage-column"><?php esc_html_e( 'Post Links', 'magic-post-thumbnail' ); ?></th>
                  <th scope="col" id="tags" class="manage-column"><?php esc_html_e( 'Images', 'magic-post-thumbnail' ); ?></th>
                </tr>
              </thead>

              <tbody id="mpt-list">
                <?php
                  foreach( $ids_mpt_generation as $id ) {
                          if( !get_post_status( $id ) )
                                  continue;
                ?>
                <tr id="post-<?php echo $id; ?>" class="post-<?php echo $id; ?>">
                        <th scope="row"></th>
                        <td class="column-title">
                                <strong>
                                        <span class="row-title"><?php echo get_the_title( $id ); ?></span>
                                </strong>
                        </td>
                        <td class="column-status column-primary">
                                <div class="row-status">
                                        <span class="status raw successful"><?php esc_html_e( 'Successful', 'magic-post-thumbnail' ); ?></span>
                                        <span class="status raw failed"><?php esc_html_e( 'Failed', 'magic-post-thumbnail' ); ?></span>
                                        <span class="status raw error"><?php esc_html_e( 'Error', 'magic-post-thumbnail' ); ?></span>
                                        <span class="status raw already-done"><?php esc_html_e( 'Image Already Exists', 'magic-post-thumbnail' ); ?></span>
                                        <span class="status raw no-rewrite"><?php esc_html_e( 'No image rewriting into post content', 'magic-post-thumbnail' ); ?></span>
                                </div>
                                <span class="empty-content"><img src="<?php echo esc_url( get_admin_url() . 'images/spinner.gif' ); ?>" title="<?php esc_html_e( 'Waiting for generation', 'magic-post-thumbnail' ); ?>" /></span>
                                <p class="show-image-details"><a href="#"><?php esc_html_e( 'Show Image Details', 'magic-post-thumbnail' ); ?></a></p>
                                <p class="hide-image-details"><a href="#"><?php esc_html_e( 'Hide Image Details', 'magic-post-thumbnail' ); ?></a></p>
                                <div class="image-details"></div>
                        </td>
                        <td class="column-edit-links">
                                <span class="empty-content">-</span>
                                <div class="row-actions">
                                        <span class="edit"><a href="<?php echo get_edit_post_link( $id ); ?>" target="_blank"> <span class="dashicons dashicons-edit"></span> <?php esc_html_e( 'Edit', 'magic-post-thumbnail' ); ?></a> | </span>
                                        <span class="view"><a href="<?php echo get_permalink( $id ); ?>" target="_blank"> <span class="dashicons dashicons-admin-links"></span> <?php esc_html_e( 'View', 'magic-post-thumbnail' ); ?></a></span>
                                </div>
                        </td>
                        <td class="column-image">
                                <span class="empty-content">-</span>
                                <div class="row-image">
                                </div>
                        </td>
                </tr>
                <?php } ?>

                <tr class="successful-generation">
                        <th scope="row"></th>
                        <td class="title has-row-actions column-primary" colspan="4">
                                <strong><?php esc_html_e( 'Successful Generation', 'magic-post-thumbnail' ); ?></strong>
                        </td>
                </tr>
              </tbody>
            </table>

        <?php } else { ?>
        
                <p><?php esc_html_e( 'Currently no generation.', 'magic-post-thumbnail' ); ?></p>
                <p><?php esc_html_e( 'Steps to generate images:', 'magic-post-thumbnail' ); ?></p>
                <p><img src="<?php echo plugin_dir_url( __FILE__ ) . '../../img/generation-step-1.png'; ?>" title="<?php esc_html_e( 'Choose your posts', 'magic-post-thumbnail' ); ?>" /></p>
                <p><img src="<?php echo plugin_dir_url( __FILE__ ) . '../../img/generation-step-2.png'; ?>" title="<?php esc_html_e( 'Generate images', 'magic-post-thumbnail' ); ?>" /></p>
        <?php
        } ?>

</div>
