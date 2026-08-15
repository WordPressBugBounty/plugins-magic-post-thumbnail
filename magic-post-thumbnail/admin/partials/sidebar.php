<!--begin::Aside-->
<div class="aside aside-left  aside-fixed  d-flex flex-column flex-row-auto" id="kt_aside">
	<!--begin::Brand-->
	<div class="brand flex-column-auto " id="kt_brand">
		<!--begin::Logo-->
    <a href="#" class="brand-logo">
        <!--<img alt="Logo" src="<?php echo plugin_dir_url( __FILE__ ) . '../img/icon-settings.png'; ?>" />-->
        <img alt="Logo Magic Post Thumbnail"  src="<?php echo plugin_dir_url( __FILE__ ) . '../img/logo.png'; ?>" />

    </a>
		<!--end::Logo-->
	</div>
	<!--end::Brand-->
	<!--begin::Aside Menu-->
	<div class="aside-menu-wrapper flex-column-fluid">
		<!--begin::Menu Container-->
		<div class="aside-menu my-4 " data-menu-vertical="1" data-menu-scroll="1" data-menu-dropdown-timeout="500">
			<!--begin::Menu Nav-->
			<ul class="menu-nav ">

      			<?php $this->MPT_submenu( esc_html__( 'Dashboard', 'magic-post-thumbnail' ), 'dashboard', 'home.png' ); ?>

				<li class="menu-section ">
					<h4 class="menu-text"><?php esc_html_e( 'Settings', 'magic-post-thumbnail' ); ?></h4> <i class="menu-icon ki ki-bold-more-hor icon-md"></i>
				</li>

				<?php $this->MPT_submenu( esc_html__( 'Source', 'magic-post-thumbnail' ), 'source', 'source.png' ); ?>
				<?php $this->MPT_submenu( esc_html__( 'Automatic', 'magic-post-thumbnail' ), 'automatic', 'automatic.png' ); ?>
				<?php $this->MPT_submenu( esc_html__( 'Gutenberg Block', 'magic-post-thumbnail' ), 'block', 'block.png' ); ?>

				<li class="menu-section ">
					<h4 class="menu-text"><?php esc_html_e( 'Pro', 'magic-post-thumbnail' ); ?></h4>
				</li>

				<?php $this->MPT_submenu( esc_html__( 'Compatibility', 'magic-post-thumbnail' ), 'compatibility', 'compatibility.png' ); ?>
				<?php $this->MPT_submenu( esc_html__( 'Cron', 'magic-post-thumbnail' ), 'cron', 'cron.png' ); ?>
				<?php $this->MPT_submenu( esc_html__( 'Proxy', 'magic-post-thumbnail' ), 'proxy', 'proxy.png' ); ?>

				<li class="menu-section ">
					<h4 class="menu-text"><?php esc_html_e( 'Miscellaneous', 'magic-post-thumbnail' ); ?></h4>
				</li>

				<?php $this->MPT_submenu( esc_html__( 'Rights', 'magic-post-thumbnail' ), 'rights', 'rights.png' ); ?>
				<?php $this->MPT_submenu( esc_html__( 'Affiliation', 'magic-post-thumbnail' ), 'affiliation', 'affiliation.png' ); ?>
				<?php $this->MPT_submenu( esc_html__( 'Upgrade', 'magic-post-thumbnail' ), 'upgrade', 'upgrade.png' ); ?>
				<?php $this->MPT_submenu( esc_html__( 'Logs', 'magic-post-thumbnail' ), 'logs', 'logs.png' ); ?>

				<li class="menu-section ">
					<h4 class="menu-text"><?php esc_html_e( 'Generation', 'magic-post-thumbnail' ); ?></h4>
				</li>

				<?php
					$options = wp_parse_args( get_option( 'MPT_plugin_main_settings' ), $this->MPT_default_options_main_settings( TRUE ) );
					$value_bulk_generation_interval = ( isset( $options['bulk_generation_interval'] ) )? (int)$options['bulk_generation_interval'] : 0;

					if( ( 0 !== $value_bulk_generation_interval ) ) {
						$bulk_url = 'bulk-generation-interval';
					} else {
						$bulk_url = 'bulk-generation';
					}
				?>

        		<?php $this->MPT_submenu(esc_html__( 'Bulk Generation', 'magic-post-thumbnail' ), $bulk_url, 'generate.png' ); ?>

			</ul>
			<!--end::Menu Nav-->
		</div>
		<!--end::Menu Container-->
	</div>
	<!--end::Aside Menu-->
</div>
<!--end::Aside-->
