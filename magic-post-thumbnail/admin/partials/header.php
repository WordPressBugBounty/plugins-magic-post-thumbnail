<?php
if ( ! function_exists( 'add_filter' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
		exit();
}

?>
<div class="wrap">
	<!--begin::Main-->
	<!--begin::Header Mobile-->
	<div id="kt_header_mobile" class="header-mobile align-items-center  header-mobile-fixed ">
		<!--begin::Logo--><a href="#">
		<img alt="Logo" src="<?php echo plugin_dir_url( __FILE__ ) . '../img/logo.png'; ?>"/>
	</a>
		<!--end::Logo-->
		<!--begin::Toolbar-->
		<div class="d-flex align-items-center">
			<!--begin::Aside Mobile Toggle-->
			<button class="btn p-0 burger-icon burger-icon-left" id="kt_aside_mobile_toggle"> <span></span> </button>
			<!--end::Aside Mobile Toggle-->
		</div>
		<!--end::Toolbar-->
	</div>
	<!--end::Header Mobile-->
	<div class="d-flex flex-column flex-root">
		<!--begin::Page-->
		<div class="d-flex flex-row flex-column-fluid page">
			<?php include_once( 'sidebar.php' ); ?>
				<!--begin::Wrapper-->
				<div class="d-flex flex-column flex-row-fluid wrapper" id="kt_wrapper">

	        <!--begin::Content-->
	        <div class="content  d-flex flex-column flex-column-fluid" id="kt_content">

	                <!--begin::Subheader-->
	                <div class="subheader py-2 py-lg-6  subheader-solid " id="kt_subheader">
	                    <div class=" container-fluid  d-flex align-items-center justify-content-between flex-wrap flex-sm-nowrap">
	                        <!--begin::Info-->
	                        <div class="d-flex align-items-center flex-wrap mr-1">
	                            <!--begin::Page Heading-->
	                            <div class="d-flex align-items-baseline flex-wrap mr-5">
	                                    <!--begin::Page Title-->
	                                 <h5 class="text-dark font-weight-bold my-1 mr-5">Magic Post Thumbnail :  <?php echo $title; ?></h5>
	                                    <!--end::Page Title-->
	                            </div>
	                            <!--end::Page Heading-->
	                        </div>
	                        <!--end::Info-->
	                    </div>
	                </div>
	                <!--end::Subheader-->


	                <!--begin::Entry-->
	                <div class="d-flex flex-column-fluid">
	                        <!--begin::Container-->
	                        <div class=" container ">
	                                <div class="row">
	                                    <div class="col-md-12">
	                                            <!--begin::Card-->
	                                            <div class="card card-custom">
