<?php
if ( ! function_exists( 'add_filter' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
		exit();
}

?>
<div class="wrap">

        <?php if( !$this->mpt_freemius()->is__premium_only() && ( current_time('U') < 1748210399 ) ) { ?>
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
                                Get a <strong>20% discount until May 25</strong> when you upgrade to the <a href="admin.php?page=magic-post-thumbnail-admin-display-pricing">Pro version</a> with the code: <strong>MPTMAYDISCOUNT</strong>
                        </div>
                </div>
        <?php } ?>

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
                        <div class="progressionbar-title"><span><?php esc_html_e( 'Progress', 'mpt' ); ?></span></div>
                        <div class="progressionbar-bar"></div>
                        <div class="skill-bar-percent"><span>0</span>%</div>
                </div>

                <?php if( $bulk_generation_interval && ( TRUE == $bulk_generation_interval ) ) { ?>
                        <div class="time-progress">
                                <div class="progressionbar-title">
                                        <span><?php esc_html_e( 'Time Before Next Generation', 'mpt' ); ?></span>
                                        <span class="remaining-time">
                                                <?php echo $remaining_seconds; ?>
                                                <?php esc_html_e( 'seconds', 'mpt' ); ?>
                                        </span>
                                </div>
                        </div>
                <?php } ?>

            </div>

        <?php
                if ( 
                        ( true === in_array( 'dallev1', $options_banks['api_chosen_auto'] ) ) && 
                        ( 'dallev1' === reset( $options_banks['api_chosen_auto'] ) ) 
                ) {
        ?>
            <p class="dalle-wait">
                <?php esc_html_e( 'Dall-e v3 Generation may take 20 to 40 seconds. Please be patient', 'mpt' ); ?>.
            </p>
                <?php } ?>

            <table class="wp-list-mpt wp-list-table widefat fixed striped posts">
              <thead>
                <tr>
                  <th scope="row"></th>
                  <th scope="col" id="title" class="manage-column column-primary"><?php esc_html_e( 'Title', 'mpt' ); ?></th>
                  <th scope="col" id="status" class="manage-column"><?php esc_html_e( 'Status', 'mpt' ); ?></th>
                  <th scope="col" id="categories" class="manage-column"><?php esc_html_e( 'Post Links', 'mpt' ); ?></th>
                  <th scope="col" id="tags" class="manage-column"><?php esc_html_e( 'Images', 'mpt' ); ?></th>
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
                                        <span class="status raw successful"><?php esc_html_e( 'Successful', 'mpt' ); ?></span>
                                        <span class="status raw failed"><?php esc_html_e( 'Failed', 'mpt' ); ?></span>
                                        <span class="status raw error"><?php esc_html_e( 'Error', 'mpt' ); ?></span>
                                        <span class="status raw already-done"><?php esc_html_e( 'Image Already Exists', 'mpt' ); ?></span>
                                        <span class="status raw no-rewrite"><?php esc_html_e( 'No image rewriting into post content', 'mpt' ); ?></span>
                                </div>
                                <span class="empty-content"><img src="<?php echo esc_url( get_admin_url() . 'images/spinner.gif' ); ?>" title="<?php esc_html_e( 'Waiting for generation', 'mpt' ); ?>" /></span>
                                <p class="show-image-details"><a href="#"><?php esc_html_e( 'Show Image Details', 'mpt' ); ?></a></p>
                                <p class="hide-image-details"><a href="#"><?php esc_html_e( 'Hide Image Details', 'mpt' ); ?></a></p>
                                <div class="image-details"></div>
                        </td>
                        <td class="column-edit-links">
                                <span class="empty-content">-</span>
                                <div class="row-actions">
                                        <span class="edit"><a href="<?php echo get_edit_post_link( $id ); ?>" target="_blank"> <span class="dashicons dashicons-edit"></span> <?php esc_html_e( 'Edit', 'mpt' ); ?></a> | </span>
                                        <span class="view"><a href="<?php echo get_permalink( $id ); ?>" target="_blank"> <span class="dashicons dashicons-admin-links"></span> <?php esc_html_e( 'View', 'mpt' ); ?></a></span>
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
                                <strong><?php esc_html_e( 'Successful Generation', 'mpt' ); ?></strong>
                        </td>
                </tr>
              </tbody>
            </table>

        <?php } else { ?>
        
                <p><?php esc_html_e( 'Currently no generation.', 'mpt' ); ?></p>
                <p><?php esc_html_e( 'Steps to generate images:', 'mpt' ); ?></p>
                <p><img src="<?php echo plugin_dir_url( __FILE__ ) . '../../img/generation-step-1.png'; ?>" title="<?php esc_html_e( 'Choose your posts', 'mpt' ); ?>" /></p>
                <p><img src="<?php echo plugin_dir_url( __FILE__ ) . '../../img/generation-step-2.png'; ?>" title="<?php esc_html_e( 'Generate images', 'mpt' ); ?>" /></p>
        <?php
        } ?>

</div>
