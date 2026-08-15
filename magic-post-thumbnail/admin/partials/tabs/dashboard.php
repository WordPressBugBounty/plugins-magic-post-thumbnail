<?php
if ( ! function_exists( 'add_filter' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
		exit();
}

if( $this->mpt_freemius()->is_premium() ) {
	$path       = '<path d="M12,18 L7.91561963,20.1472858 C7.42677504,20.4042866 6.82214789,20.2163401 6.56514708,19.7274955 C6.46280801,19.5328351 6.42749334,19.309867 6.46467018,19.0931094 L7.24471742,14.545085 L3.94038429,11.3241562 C3.54490071,10.938655 3.5368084,10.3055417 3.92230962,9.91005817 C4.07581822,9.75257453 4.27696063,9.65008735 4.49459766,9.61846284 L9.06107374,8.95491503 L11.1032639,4.81698575 C11.3476862,4.32173209 11.9473121,4.11839309 12.4425657,4.36281539 C12.6397783,4.46014562 12.7994058,4.61977315 12.8967361,4.81698575 L14.9389263,8.95491503 L19.5054023,9.61846284 C20.0519472,9.69788046 20.4306287,10.2053233 20.351211,10.7518682 C20.3195865,10.9695052 20.2170993,11.1706476 20.0596157,11.3241562 L16.7552826,14.545085 L17.5353298,19.0931094 C17.6286908,19.6374458 17.263103,20.1544017 16.7187666,20.2477627 C16.5020089,20.2849396 16.2790408,20.2496249 16.0843804,20.1472858 L12,18 Z" fill="#000000"/>';

	if( $this->mpt_freemius()->can_use_premium_code() ) {
		$status     = esc_html__( 'Pro', 'magic-post-thumbnail' );
		$upgrade    = '';
	} else {
		$status     = esc_html__( 'Pro (licence expired)', 'magic-post-thumbnail' );
		$upgrade    = '<a target="_blank" href="https://magic-post-thumbnail.com/pricing/">'.esc_html__( 'Upgrade the plugin', 'magic-post-thumbnail' ) .'</a>';
	}
	
} else {
	$status     = esc_html__( 'Free', 'magic-post-thumbnail' );
    $path       = '<path d="M12,4.25932872 C12.1488635,4.25921584 12.3000368,4.29247316 12.4425657,4.36281539 C12.6397783,4.46014562 12.7994058,4.61977315 12.8967361,4.81698575 L14.9389263,8.95491503 L19.5054023,9.61846284 C20.0519472,9.69788046 20.4306287,10.2053233 20.351211,10.7518682 C20.3195865,10.9695052 20.2170993,11.1706476 20.0596157,11.3241562 L16.7552826,14.545085 L17.5353298,19.0931094 C17.6286908,19.6374458 17.263103,20.1544017 16.7187666,20.2477627 C16.5020089,20.2849396 16.2790408,20.2496249 16.0843804,20.1472858 L12,18 L12,4.25932872 Z" fill="#000000" opacity="0.3"/>
            <path d="M12,4.25932872 L12,18 L7.91561963,20.1472858 C7.42677504,20.4042866 6.82214789,20.2163401 6.56514708,19.7274955 C6.46280801,19.5328351 6.42749334,19.309867 6.46467018,19.0931094 L7.24471742,14.545085 L3.94038429,11.3241562 C3.54490071,10.938655 3.5368084,10.3055417 3.92230962,9.91005817 C4.07581822,9.75257453 4.27696063,9.65008735 4.49459766,9.61846284 L9.06107374,8.95491503 L11.1032639,4.81698575 C11.277344,4.464261 11.6315987,4.25960807 12,4.25932872 Z" fill="#000000"/>';
    $upgrade    = '<a target="_blank" href="https://magic-post-thumbnail.com/pricing/">'.esc_html__( 'Upgrade the plugin', 'magic-post-thumbnail' ) .'</a>';
}

$user       = $this->mpt_freemius()->get_user();
$version    = $this->mpt_freemius()->get_plugin_data()['Version'];

if( $user ) {
	$email      = $user->email;
	$name       = $user->first .' '. $user->last;
	$verified   = $user->is_verified;
} else {
	$email      = 'Unknown';
	$name       = 'Unknown';
	$verified   = false;
}

?>

<div class="wrap">

<?php $this->MPT_promo_black_friday_html(); ?>

<?php
$health = $this->MPT_config_health();
$overall_label = array(
	'ok'    => __( 'Ready to generate', 'magic-post-thumbnail' ),
	'warn'  => __( 'Needs attention', 'magic-post-thumbnail' ),
	'error' => __( 'Needs attention', 'magic-post-thumbnail' ),
);
?>
<div class="row mb-5">
	<div class="col-12">
		<div class="card card-custom mpt-health-card">
			<div class="card-header border-0 mpt-health-head">
				<h3 class="card-title font-weight-bolder mb-0">
					<?php esc_html_e( 'Your configuration', 'magic-post-thumbnail' ); ?>
				</h3>
				<span class="mpt-health-overall mpt-health-overall-<?php echo esc_attr( $health['overall'] ); ?>">
					<?php echo esc_html( isset( $overall_label[ $health['overall'] ] ) ? $overall_label[ $health['overall'] ] : $overall_label['warn'] ); ?>
				</span>
			</div>
			<div class="card-body pt-0 pb-4">
				<ul class="mpt-health-list">
					<?php foreach ( $health['checks'] as $check ) : ?>
						<li class="mpt-health-<?php echo esc_attr( $check['status'] ); ?>">
							<span class="mpt-health-dot" aria-hidden="true"></span>
							<div class="mpt-health-copy">
								<strong><?php echo esc_html( $check['label'] ); ?></strong>
								<span><?php echo esc_html( $check['text'] ); ?></span>
							</div>
							<?php if ( ! empty( $check['link'] ) ) : ?>
								<a class="mpt-health-link" href="<?php echo esc_url( $check['link'] ); ?>"><?php echo esc_html( $check['link_label'] ); ?></a>
							<?php endif; ?>
						</li>
					<?php endforeach; ?>
				</ul>
				<div class="mpt-health-test">
					<button type="button" class="btn btn-primary btn-sm font-weight-bolder" id="mpt-health-test" <?php disabled( empty( $health['can_test'] ) ); ?> data-ai="<?php echo ! empty( $health['test_is_ai'] ) ? '1' : '0'; ?>" title="<?php echo esc_attr( __( 'Creates a draft post so your live articles are left untouched.', 'magic-post-thumbnail' ) ); ?>">
						<?php esc_html_e( 'Generate a test image', 'magic-post-thumbnail' ); ?>
					</button>
					<span class="mpt-health-test-hint"><?php esc_html_e( 'Creates a draft post so your live articles are left untouched.', 'magic-post-thumbnail' ); ?></span>
				</div>
				<div id="mpt-health-test-result" class="mpt-health-test-result" hidden></div>
			</div>
		</div>
	</div>
</div>

<div class="row">

	<div class="col-lg-6 col-xl-4">
		<!--begin::Iconbox-->
		<div class="card card-custom wave wave-animate-slow wave-success">
			<div class="card-body">
				<div class="d-flex align-items-center p-5">
					<div class="mr-6 show-pict-desktop">
						<span class="svg-icon svg-icon-success svg-icon-4x">
							<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
								<g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
									<rect x="0" y="0" width="24" height="24"></rect>
									<polygon fill="#000000" opacity="0.3" points="5 3 19 3 23 8 1 8"></polygon>
									<polygon fill="#000000" points="23 8 12 20 1 8"></polygon>
								</g>
							</svg><!--end::Svg Icon-->
						</span>
          </div>
					<div class="d-flex flex-column">
						<span class="text-dark text-hover-primary font-weight-bold font-size-h4 mb-3">
							<?php esc_html_e( 'My account', 'magic-post-thumbnail' ); ?>
						</span>
						<div class="text-dark-75">
              <p>
                  <span class="svg-icon svg-icon-primary svg-icon-2x"><svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
                      <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                          <polygon points="0 0 24 0 24 24 0 24"/>
                          <?php echo $path; ?>
                      </g>
                  </svg><!--end::Svg Icon--></span>
                  <span class="account-status"><?php echo $status; ?></span>
              </p>

              <p>

                  <span class="svg-icon svg-icon-primary svg-icon-2x"><svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
                      <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                          <polygon points="0 0 24 0 24 24 0 24"/>
                          <path d="M12,11 C9.790861,11 8,9.209139 8,7 C8,4.790861 9.790861,3 12,3 C14.209139,3 16,4.790861 16,7 C16,9.209139 14.209139,11 12,11 Z" fill="#000000" fill-rule="nonzero" opacity="0.3"/>
                          <path d="M3.00065168,20.1992055 C3.38825852,15.4265159 7.26191235,13 11.9833413,13 C16.7712164,13 20.7048837,15.2931929 20.9979143,20.2 C21.0095879,20.3954741 20.9979143,21 20.2466999,21 C16.541124,21 11.0347247,21 3.72750223,21 C3.47671215,21 2.97953825,20.45918 3.00065168,20.1992055 Z" fill="#000000" fill-rule="nonzero"/>
                      </g>
                  </svg><!--end::Svg Icon--></span>

                  <span class="plugin-infos"><?php echo $name; ?></span>
                  <br/>

                  <span class="svg-icon svg-icon-primary svg-icon-2x"><svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
                      <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                          <rect x="0" y="0" width="24" height="24"/>
                          <path d="M5,6 L19,6 C20.1045695,6 21,6.8954305 21,8 L21,17 C21,18.1045695 20.1045695,19 19,19 L5,19 C3.8954305,19 3,18.1045695 3,17 L3,8 C3,6.8954305 3.8954305,6 5,6 Z M18.1444251,7.83964668 L12,11.1481833 L5.85557487,7.83964668 C5.4908718,7.6432681 5.03602525,7.77972206 4.83964668,8.14442513 C4.6432681,8.5091282 4.77972206,8.96397475 5.14442513,9.16035332 L11.6444251,12.6603533 C11.8664074,12.7798822 12.1335926,12.7798822 12.3555749,12.6603533 L18.8555749,9.16035332 C19.2202779,8.96397475 19.3567319,8.5091282 19.1603533,8.14442513 C18.9639747,7.77972206 18.5091282,7.6432681 18.1444251,7.83964668 Z" fill="#000000"/>
                      </g>
                  </svg><!--end::Svg Icon--></span>
                  <span class="plugin-infos"><?php echo $email; ?></span>

                  <br/>

                  <span class="svg-icon svg-icon-primary svg-icon-2x"><svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
                      <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                          <rect x="0" y="0" width="24" height="24"/>
                          <circle fill="#000000" opacity="0.3" cx="12" cy="12" r="10"/>
                          <rect fill="#000000" x="11" y="10" width="2" height="7" rx="1"/>
                          <rect fill="#000000" x="11" y="7" width="2" height="2" rx="1"/>
                      </g>
                  </svg><!--end::Svg Icon--></span>

                  <span class="plugin-infos"><?php echo esc_html__( 'Version', 'magic-post-thumbnail' ) . ': ' . $version ; ?></span>
                  <br/>

				<?php if( $verified ) { ?>
                      <span class="svg-icon svg-icon-primary svg-icon-2x"><svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
                          <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                              <rect x="0" y="0" width="24" height="24"/>
                              <circle fill="#000000" opacity="0.3" cx="12" cy="12" r="10"/>
                              <path d="M16.7689447,7.81768175 C17.1457787,7.41393107 17.7785676,7.39211077 18.1823183,7.76894473 C18.5860689,8.1457787 18.6078892,8.77856757 18.2310553,9.18231825 L11.2310553,16.6823183 C10.8654446,17.0740439 10.2560456,17.107974 9.84920863,16.7592566 L6.34920863,13.7592566 C5.92988278,13.3998345 5.88132125,12.7685345 6.2407434,12.3492086 C6.60016555,11.9298828 7.23146553,11.8813212 7.65079137,12.2407434 L10.4229928,14.616916 L16.7689447,7.81768175 Z" fill="#000000" fill-rule="nonzero"/>
                          </g>
                      </svg><!--end::Svg Icon--></span>
                      <span class="plugin-infos"><?php esc_html_e( 'Verified', 'magic-post-thumbnail' ); ?></span>
				<?php } else { ?>
                      <span class="svg-icon svg-icon-primary svg-icon-2x"><svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
                          <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                              <rect x="0" y="0" width="24" height="24"/>
                              <circle fill="#000000" opacity="0.3" cx="12" cy="12" r="10"/>
                              <path d="M12.0355339,10.6213203 L14.863961,7.79289322 C15.2544853,7.40236893 15.8876503,7.40236893 16.2781746,7.79289322 C16.6686989,8.18341751 16.6686989,8.81658249 16.2781746,9.20710678 L13.4497475,12.0355339 L16.2781746,14.863961 C16.6686989,15.2544853 16.6686989,15.8876503 16.2781746,16.2781746 C15.8876503,16.6686989 15.2544853,16.6686989 14.863961,16.2781746 L12.0355339,13.4497475 L9.20710678,16.2781746 C8.81658249,16.6686989 8.18341751,16.6686989 7.79289322,16.2781746 C7.40236893,15.8876503 7.40236893,15.2544853 7.79289322,14.863961 L10.6213203,12.0355339 L7.79289322,9.20710678 C7.40236893,8.81658249 7.40236893,8.18341751 7.79289322,7.79289322 C8.18341751,7.40236893 8.81658249,7.40236893 9.20710678,7.79289322 L12.0355339,10.6213203 Z" fill="#000000"/>
                          </g>
                      </svg><!--end::Svg Icon--></span>
                      <span class="plugin-infos"><?php esc_html_e( 'Not Verified', 'magic-post-thumbnail' ); ?></span>
				<?php }
					if( $user ) {
				?>
                      <br/>
                      <span class="svg-icon svg-icon-primary svg-icon-2x"><svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
                          <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                              <rect x="0" y="0" width="24" height="24"/>
                              <path d="M6,2 L18,2 C19.6568542,2 21,3.34314575 21,5 L21,19 C21,20.6568542 19.6568542,22 18,22 L6,22 C4.34314575,22 3,20.6568542 3,19 L3,5 C3,3.34314575 4.34314575,2 6,2 Z M12,11 C13.1045695,11 14,10.1045695 14,9 C14,7.8954305 13.1045695,7 12,7 C10.8954305,7 10,7.8954305 10,9 C10,10.1045695 10.8954305,11 12,11 Z M7.00036205,16.4995035 C6.98863236,16.6619875 7.26484009,17 7.4041679,17 C11.463736,17 14.5228466,17 16.5815,17 C16.9988413,17 17.0053266,16.6221713 16.9988413,16.5 C16.8360465,13.4332455 14.6506758,12 11.9907452,12 C9.36772908,12 7.21569918,13.5165724 7.00036205,16.4995035 Z" fill="#000000"/>
                          </g>
                      </svg><!--end::Svg Icon--></span>
                      <?php
                          $account_url    = remove_query_arg( 'ids_mpt_generation', add_query_arg( 'module', 'account', $this->MPT_current_url() ) );
                      ?>
                      <span class="plugin-infos"><a href="<?php echo $account_url ?>"><?php esc_html_e( 'Account Details', 'magic-post-thumbnail' ); ?></a></span>
				<?php 
					} 
				?>
              </p>

              <p>
                  <?php echo $upgrade; ?>
              </p>
						</div>
					</div>
				</div>
			</div>
		</div>
		<!--end::Iconbox-->
	</div>

    <div class="col-lg-6 col-xl-4 mb-10">
		<!--begin::Callout-->
		<div class="card card-custom mb-2 bg-diagonal bg-diagonal-light-primary">
			<div class="card-body">
        <div class="d-flex align-items-center justify-content-between p-4 flex-lg-wrap flex-xl-nowrap">
            <div class="d-flex flex-column mr-1">
                <span class="h4 text-dark text-hover-primary mb-5">
                    <?php esc_html_e( 'Support Forum', 'magic-post-thumbnail' ); ?>
                </span>
                <p class="text-dark-50">
                    <?php esc_html_e( 'Access to the WordPress.org support forum.', 'magic-post-thumbnail' ); ?>
                </p>
            </div>
            <div class="ml-6 ml-lg-0 ml-xxl-6 flex-shrink-0">
                <a href="https://wordpress.org/support/plugin/magic-post-thumbnail/" target="_blank" class="btn font-weight-bolder text-uppercase btn-primary py-4 px-6">
                    <?php esc_html_e( 'Support', 'magic-post-thumbnail' ); ?>
                </a>
            </div>
        </div>
			</div>
		</div>
		<!--end::Callout-->

	</div>

    <div class="col-lg-6 col-xl-4 mb-10">
		<!--begin::Callout-->
		<div class="card card-custom mb-2 bg-diagonal">
			<div class="card-body">
        <div class="d-flex align-items-center justify-content-between p-4 flex-lg-wrap flex-xl-nowrap">
            <div class="d-flex flex-column mr-1">
                    <span class="h4 text-dark text-hover-primary mb-5">
                            <?php esc_html_e( 'Get In Touch', 'magic-post-thumbnail' ); ?>
                    </span>
                    <p class="text-dark-50">
                        <?php esc_html_e( 'If you have any questions.', 'magic-post-thumbnail' ); ?>
                    </p>
            </div>
            <div class="ml-6 ml-lg-0 ml-xxl-6 flex-shrink-0">
                <?php
                    $contact_url    = remove_query_arg( 'ids_mpt_generation', add_query_arg( 'module', 'contact', $this->MPT_current_url() ) );
                ?>
                <a href="<?php echo $contact_url; ?>" target="_blank" class="btn font-weight-bolder text-uppercase btn-primary py-4 px-6">
                    <?php esc_html_e( 'Contact Us', 'magic-post-thumbnail' ); ?>
                </a>
            </div>
        </div>
			</div>
		</div>
		<!--end::Callout-->
	</div>

	<div class="col-lg-6 col-xl-4 mb-5">
		<!--begin::Iconbox-->
		<div class="card card-custom wave wave-animate-slower mb-8 mb-lg-0">
			<div class="card-body">
				<div class="d-flex align-items-center p-5">
					<div class="mr-6">
						<span class="svg-icon svg-icon-success svg-icon-4x">
							<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
                    <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                        <rect opacity="0.200000003" x="0" y="0" width="24" height="24"></rect>
                        <path d="M4.5,7 L9.5,7 C10.3284271,7 11,7.67157288 11,8.5 C11,9.32842712 10.3284271,10 9.5,10 L4.5,10 C3.67157288,10 3,9.32842712 3,8.5 C3,7.67157288 3.67157288,7 4.5,7 Z M13.5,15 L18.5,15 C19.3284271,15 20,15.6715729 20,16.5 C20,17.3284271 19.3284271,18 18.5,18 L13.5,18 C12.6715729,18 12,17.3284271 12,16.5 C12,15.6715729 12.6715729,15 13.5,15 Z" fill="#000000" opacity="0.3"></path>
                        <path d="M17,11 C15.3431458,11 14,9.65685425 14,8 C14,6.34314575 15.3431458,5 17,5 C18.6568542,5 20,6.34314575 20,8 C20,9.65685425 18.6568542,11 17,11 Z M6,19 C4.34314575,19 3,17.6568542 3,16 C3,14.3431458 4.34314575,13 6,13 C7.65685425,13 9,14.3431458 9,16 C9,17.6568542 7.65685425,19 6,19 Z" fill="#000000"></path>
                    </g>
                </svg><!--end::Svg Icon-->
							</span>
            </div>
					<div class="d-flex flex-column">
						<span class="text-dark text-hover-primary font-weight-bold font-size-h4 mb-3">
							<?php esc_html_e( 'Tutorials', 'magic-post-thumbnail' ); ?>
						</span>
						<div class="text-dark-75">
							<a href="https://magic-post-thumbnail.com/docs/" target="_blank">
	                <?php esc_html_e( 'Documentation', 'magic-post-thumbnail' ); ?>
	            </a><br/>
              <?php
				// Get language
				/*
				$locale = get_bloginfo('language');
				$lang		= explode( '-', $locale );

				// Asign a youtube video ID
				if( 'es' == $lang[0] ) {
					$youtube_ID = 'ki0PXQSYR1Q';
				} elseif( 'fr' == $lang[0] ) {
					$youtube_ID = 'NSi_QShLHZg';
				} else {
					$youtube_ID = 'HPqUEQ2MrZc';
				}*/
                printf( __( '<a href="%s" target="_blank">Youtube Tutorial</a>', 'magic-post-thumbnail' ), 'https://www.youtube.com/watch?v=mC6qimwnT4E' );
              ?><br/>
	            <a href="https://magic-post-thumbnail.com/docs/faq/" target="_blank">
	                <?php esc_html_e( 'Frequently Asked Questions', 'magic-post-thumbnail' ); ?>
	            </a>
						</div>
					</div>
				</div>
			</div>
		</div>
		<!--end::Iconbox-->
	</div>


</div>

</div>
<!--end::Content-->
