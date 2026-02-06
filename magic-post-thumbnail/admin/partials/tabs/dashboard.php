<?php
if ( ! function_exists( 'add_filter' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
		exit();
}

if( $this->mpt_freemius()->is_premium() ) {
	$path       = '<path d="M12,18 L7.91561963,20.1472858 C7.42677504,20.4042866 6.82214789,20.2163401 6.56514708,19.7274955 C6.46280801,19.5328351 6.42749334,19.309867 6.46467018,19.0931094 L7.24471742,14.545085 L3.94038429,11.3241562 C3.54490071,10.938655 3.5368084,10.3055417 3.92230962,9.91005817 C4.07581822,9.75257453 4.27696063,9.65008735 4.49459766,9.61846284 L9.06107374,8.95491503 L11.1032639,4.81698575 C11.3476862,4.32173209 11.9473121,4.11839309 12.4425657,4.36281539 C12.6397783,4.46014562 12.7994058,4.61977315 12.8967361,4.81698575 L14.9389263,8.95491503 L19.5054023,9.61846284 C20.0519472,9.69788046 20.4306287,10.2053233 20.351211,10.7518682 C20.3195865,10.9695052 20.2170993,11.1706476 20.0596157,11.3241562 L16.7552826,14.545085 L17.5353298,19.0931094 C17.6286908,19.6374458 17.263103,20.1544017 16.7187666,20.2477627 C16.5020089,20.2849396 16.2790408,20.2496249 16.0843804,20.1472858 L12,18 Z" fill="#000000"/>';

	if( $this->mpt_freemius()->can_use_premium_code() ) {
		$status     = esc_html__( 'Pro', 'mpt' );
		$upgrade    = '';
	} else {
		$status     = esc_html__( 'Pro (licence expired)', 'mpt' );
		$upgrade    = '<a target="_blank" href="https://magic-post-thumbnail.com/pricing/">'.esc_html__( 'Upgrade the plugin', 'mpt' ) .'</a>';
	}
	
} else {
	$status     = esc_html__( 'Free', 'mpt' );
    $path       = '<path d="M12,4.25932872 C12.1488635,4.25921584 12.3000368,4.29247316 12.4425657,4.36281539 C12.6397783,4.46014562 12.7994058,4.61977315 12.8967361,4.81698575 L14.9389263,8.95491503 L19.5054023,9.61846284 C20.0519472,9.69788046 20.4306287,10.2053233 20.351211,10.7518682 C20.3195865,10.9695052 20.2170993,11.1706476 20.0596157,11.3241562 L16.7552826,14.545085 L17.5353298,19.0931094 C17.6286908,19.6374458 17.263103,20.1544017 16.7187666,20.2477627 C16.5020089,20.2849396 16.2790408,20.2496249 16.0843804,20.1472858 L12,18 L12,4.25932872 Z" fill="#000000" opacity="0.3"/>
            <path d="M12,4.25932872 L12,18 L7.91561963,20.1472858 C7.42677504,20.4042866 6.82214789,20.2163401 6.56514708,19.7274955 C6.46280801,19.5328351 6.42749334,19.309867 6.46467018,19.0931094 L7.24471742,14.545085 L3.94038429,11.3241562 C3.54490071,10.938655 3.5368084,10.3055417 3.92230962,9.91005817 C4.07581822,9.75257453 4.27696063,9.65008735 4.49459766,9.61846284 L9.06107374,8.95491503 L11.1032639,4.81698575 C11.277344,4.464261 11.6315987,4.25960807 12,4.25932872 Z" fill="#000000"/>';
    $upgrade    = '<a target="_blank" href="https://magic-post-thumbnail.com/pricing/">'.esc_html__( 'Upgrade the plugin', 'mpt' ) .'</a>';
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

<?php if( !$this->mpt_freemius()->is__premium_only() && ( current_time('U') < 1764543599 ) ) { ?>
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
<?php } ?>

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
							<?php esc_html_e( 'My account', 'mpt' ); ?>
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

                  <span class="plugin-infos"><?php echo esc_html__( 'Version', 'mpt' ) . ': ' . $version ; ?></span>
                  <br/>

				<?php if( $verified ) { ?>
                      <span class="svg-icon svg-icon-primary svg-icon-2x"><svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
                          <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                              <rect x="0" y="0" width="24" height="24"/>
                              <circle fill="#000000" opacity="0.3" cx="12" cy="12" r="10"/>
                              <path d="M16.7689447,7.81768175 C17.1457787,7.41393107 17.7785676,7.39211077 18.1823183,7.76894473 C18.5860689,8.1457787 18.6078892,8.77856757 18.2310553,9.18231825 L11.2310553,16.6823183 C10.8654446,17.0740439 10.2560456,17.107974 9.84920863,16.7592566 L6.34920863,13.7592566 C5.92988278,13.3998345 5.88132125,12.7685345 6.2407434,12.3492086 C6.60016555,11.9298828 7.23146553,11.8813212 7.65079137,12.2407434 L10.4229928,14.616916 L16.7689447,7.81768175 Z" fill="#000000" fill-rule="nonzero"/>
                          </g>
                      </svg><!--end::Svg Icon--></span>
                      <span class="plugin-infos"><?php esc_html_e( 'Verified', 'mpt' ); ?></span>
				<?php } else { ?>
                      <span class="svg-icon svg-icon-primary svg-icon-2x"><svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
                          <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                              <rect x="0" y="0" width="24" height="24"/>
                              <circle fill="#000000" opacity="0.3" cx="12" cy="12" r="10"/>
                              <path d="M12.0355339,10.6213203 L14.863961,7.79289322 C15.2544853,7.40236893 15.8876503,7.40236893 16.2781746,7.79289322 C16.6686989,8.18341751 16.6686989,8.81658249 16.2781746,9.20710678 L13.4497475,12.0355339 L16.2781746,14.863961 C16.6686989,15.2544853 16.6686989,15.8876503 16.2781746,16.2781746 C15.8876503,16.6686989 15.2544853,16.6686989 14.863961,16.2781746 L12.0355339,13.4497475 L9.20710678,16.2781746 C8.81658249,16.6686989 8.18341751,16.6686989 7.79289322,16.2781746 C7.40236893,15.8876503 7.40236893,15.2544853 7.79289322,14.863961 L10.6213203,12.0355339 L7.79289322,9.20710678 C7.40236893,8.81658249 7.40236893,8.18341751 7.79289322,7.79289322 C8.18341751,7.40236893 8.81658249,7.40236893 9.20710678,7.79289322 L12.0355339,10.6213203 Z" fill="#000000"/>
                          </g>
                      </svg><!--end::Svg Icon--></span>
                      <span class="plugin-infos"><?php esc_html_e( 'Not Verified', 'mpt' ); ?></span>
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
                      <span class="plugin-infos"><a href="<?php echo $account_url ?>"><?php esc_html_e( 'Account Details', 'mpt' ); ?></a></span>
				<?php 
					} 
					if( $this->mpt_freemius()->is_premium() ) {
						if( $this->mpt_freemius()->can_use_premium_code() ) {
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
					<span class="plugin-infos">
						<a href="https://magic-post-thumbnail.com/account/" target="_blank" title="<?php esc_html_e( 'Connect to pro account' ); ?>">
							<?php esc_html_e( 'Pro Account', 'mpt' ); ?>
						</a>
					</span>

				<?php } } ?>
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
                    <?php esc_html_e( 'Support Forum', 'mpt' ); ?>
                </span>
                <p class="text-dark-50">
                    <?php esc_html_e( 'Access to the WordPress.org support forum.', 'mpt' ); ?>
                </p>
            </div>
            <div class="ml-6 ml-lg-0 ml-xxl-6 flex-shrink-0">
                <a href="https://wordpress.org/support/plugin/magic-post-thumbnail/" target="_blank" class="btn font-weight-bolder text-uppercase btn-primary py-4 px-6">
                    <?php esc_html_e( 'Support', 'mpt' ); ?>
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
                            <?php esc_html_e( 'Get In Touch', 'mpt' ); ?>
                    </span>
                    <p class="text-dark-50">
                        <?php esc_html_e( 'If you have any questions.', 'mpt' ); ?>
                    </p>
            </div>
            <div class="ml-6 ml-lg-0 ml-xxl-6 flex-shrink-0">
                <?php
                    $contact_url    = remove_query_arg( 'ids_mpt_generation', add_query_arg( 'module', 'contact', $this->MPT_current_url() ) );
                ?>
                <a href="<?php echo $contact_url; ?>" target="_blank" class="btn font-weight-bolder text-uppercase btn-primary py-4 px-6">
                    <?php esc_html_e( 'Contact Us', 'mpt' ); ?>
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
							<?php esc_html_e( 'Tutorials', 'mpt' ); ?>
						</span>
						<div class="text-dark-75">
							<a href="https://magic-post-thumbnail.com/docs/" target="_blank">
	                <?php esc_html_e( 'Documentation', 'mpt' ); ?>
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
                printf( __( '<a href="%s" target="_blank">Youtube Tutorial</a>', 'mpt' ), 'https://www.youtube.com/watch?v=mC6qimwnT4E' );
              ?><br/>
	            <a href="https://magic-post-thumbnail.com/docs/faq/" target="_blank">
	                <?php esc_html_e( 'Frequently Asked Questions', 'mpt' ); ?>
	            </a>
						</div>
					</div>
				</div>
			</div>
		</div>
		<!--end::Iconbox-->
	</div>

        <div class="col-lg-6 col-xl-4 mb-5">
		<!--begin::Iconbox-->
		<div class="card card-custom wave wave-animate-slower mb-8 mb-lg-0">
			<div class="card-body">
				<div class="d-flex align-items-center p-5">
					<div class="mr-6">
						<span class="svg-icon svg-icon-primary svg-icon-4x"><svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
                      <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                          <polygon points="0 0 24 0 24 24 0 24"/>
                          <path d="M12,18 L7.91561963,20.1472858 C7.42677504,20.4042866 6.82214789,20.2163401 6.56514708,19.7274955 C6.46280801,19.5328351 6.42749334,19.309867 6.46467018,19.0931094 L7.24471742,14.545085 L3.94038429,11.3241562 C3.54490071,10.938655 3.5368084,10.3055417 3.92230962,9.91005817 C4.07581822,9.75257453 4.27696063,9.65008735 4.49459766,9.61846284 L9.06107374,8.95491503 L11.1032639,4.81698575 C11.3476862,4.32173209 11.9473121,4.11839309 12.4425657,4.36281539 C12.6397783,4.46014562 12.7994058,4.61977315 12.8967361,4.81698575 L14.9389263,8.95491503 L19.5054023,9.61846284 C20.0519472,9.69788046 20.4306287,10.2053233 20.351211,10.7518682 C20.3195865,10.9695052 20.2170993,11.1706476 20.0596157,11.3241562 L16.7552826,14.545085 L17.5353298,19.0931094 C17.6286908,19.6374458 17.263103,20.1544017 16.7187666,20.2477627 C16.5020089,20.2849396 16.2790408,20.2496249 16.0843804,20.1472858 L12,18 Z" fill="#000000"/>
                      </g>
                  </svg><!--end::Svg Icon--></span>
          </div>
					<div class="d-flex flex-column">
						<span class="text-dark text-hover-primary font-weight-bold font-size-h4 mb-3">
							<?php esc_html_e( 'Reviews', 'mpt' ); ?>
						</span>
						<div class="text-dark-75">
                <?php esc_html_e( 'Like this plugin? Leave a 5-star review on WordPress.', 'mpt' ); ?>
								<div class="stars-review">
									<a href="https://wordpress.org/support/plugin/magic-post-thumbnail/reviews/?filter=5#new-post" title="<?php esc_html_e( 'Write a review', 'mpt' ); ?>" target="_blank" >
										<span class="svg-icon svg-icon-primary svg-icon-2x"><svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
		                      <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
		                          <polygon points="0 0 24 0 24 24 0 24"/>
		                          <path d="M12,18 L7.91561963,20.1472858 C7.42677504,20.4042866 6.82214789,20.2163401 6.56514708,19.7274955 C6.46280801,19.5328351 6.42749334,19.309867 6.46467018,19.0931094 L7.24471742,14.545085 L3.94038429,11.3241562 C3.54490071,10.938655 3.5368084,10.3055417 3.92230962,9.91005817 C4.07581822,9.75257453 4.27696063,9.65008735 4.49459766,9.61846284 L9.06107374,8.95491503 L11.1032639,4.81698575 C11.3476862,4.32173209 11.9473121,4.11839309 12.4425657,4.36281539 C12.6397783,4.46014562 12.7994058,4.61977315 12.8967361,4.81698575 L14.9389263,8.95491503 L19.5054023,9.61846284 C20.0519472,9.69788046 20.4306287,10.2053233 20.351211,10.7518682 C20.3195865,10.9695052 20.2170993,11.1706476 20.0596157,11.3241562 L16.7552826,14.545085 L17.5353298,19.0931094 C17.6286908,19.6374458 17.263103,20.1544017 16.7187666,20.2477627 C16.5020089,20.2849396 16.2790408,20.2496249 16.0843804,20.1472858 L12,18 Z" fill="#000000"/>
		                      </g>
		                  </svg><!--end::Svg Icon--></span>
											<span class="svg-icon svg-icon-primary svg-icon-2x"><svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
			                      <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
			                          <polygon points="0 0 24 0 24 24 0 24"/>
			                          <path d="M12,18 L7.91561963,20.1472858 C7.42677504,20.4042866 6.82214789,20.2163401 6.56514708,19.7274955 C6.46280801,19.5328351 6.42749334,19.309867 6.46467018,19.0931094 L7.24471742,14.545085 L3.94038429,11.3241562 C3.54490071,10.938655 3.5368084,10.3055417 3.92230962,9.91005817 C4.07581822,9.75257453 4.27696063,9.65008735 4.49459766,9.61846284 L9.06107374,8.95491503 L11.1032639,4.81698575 C11.3476862,4.32173209 11.9473121,4.11839309 12.4425657,4.36281539 C12.6397783,4.46014562 12.7994058,4.61977315 12.8967361,4.81698575 L14.9389263,8.95491503 L19.5054023,9.61846284 C20.0519472,9.69788046 20.4306287,10.2053233 20.351211,10.7518682 C20.3195865,10.9695052 20.2170993,11.1706476 20.0596157,11.3241562 L16.7552826,14.545085 L17.5353298,19.0931094 C17.6286908,19.6374458 17.263103,20.1544017 16.7187666,20.2477627 C16.5020089,20.2849396 16.2790408,20.2496249 16.0843804,20.1472858 L12,18 Z" fill="#000000"/>
			                      </g>
			                  </svg><!--end::Svg Icon--></span>
												<span class="svg-icon svg-icon-primary svg-icon-2x"><svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
				                      <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
				                          <polygon points="0 0 24 0 24 24 0 24"/>
				                          <path d="M12,18 L7.91561963,20.1472858 C7.42677504,20.4042866 6.82214789,20.2163401 6.56514708,19.7274955 C6.46280801,19.5328351 6.42749334,19.309867 6.46467018,19.0931094 L7.24471742,14.545085 L3.94038429,11.3241562 C3.54490071,10.938655 3.5368084,10.3055417 3.92230962,9.91005817 C4.07581822,9.75257453 4.27696063,9.65008735 4.49459766,9.61846284 L9.06107374,8.95491503 L11.1032639,4.81698575 C11.3476862,4.32173209 11.9473121,4.11839309 12.4425657,4.36281539 C12.6397783,4.46014562 12.7994058,4.61977315 12.8967361,4.81698575 L14.9389263,8.95491503 L19.5054023,9.61846284 C20.0519472,9.69788046 20.4306287,10.2053233 20.351211,10.7518682 C20.3195865,10.9695052 20.2170993,11.1706476 20.0596157,11.3241562 L16.7552826,14.545085 L17.5353298,19.0931094 C17.6286908,19.6374458 17.263103,20.1544017 16.7187666,20.2477627 C16.5020089,20.2849396 16.2790408,20.2496249 16.0843804,20.1472858 L12,18 Z" fill="#000000"/>
				                      </g>
				                  </svg><!--end::Svg Icon--></span>
													<span class="svg-icon svg-icon-primary svg-icon-2x"><svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
					                      <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
					                          <polygon points="0 0 24 0 24 24 0 24"/>
					                          <path d="M12,18 L7.91561963,20.1472858 C7.42677504,20.4042866 6.82214789,20.2163401 6.56514708,19.7274955 C6.46280801,19.5328351 6.42749334,19.309867 6.46467018,19.0931094 L7.24471742,14.545085 L3.94038429,11.3241562 C3.54490071,10.938655 3.5368084,10.3055417 3.92230962,9.91005817 C4.07581822,9.75257453 4.27696063,9.65008735 4.49459766,9.61846284 L9.06107374,8.95491503 L11.1032639,4.81698575 C11.3476862,4.32173209 11.9473121,4.11839309 12.4425657,4.36281539 C12.6397783,4.46014562 12.7994058,4.61977315 12.8967361,4.81698575 L14.9389263,8.95491503 L19.5054023,9.61846284 C20.0519472,9.69788046 20.4306287,10.2053233 20.351211,10.7518682 C20.3195865,10.9695052 20.2170993,11.1706476 20.0596157,11.3241562 L16.7552826,14.545085 L17.5353298,19.0931094 C17.6286908,19.6374458 17.263103,20.1544017 16.7187666,20.2477627 C16.5020089,20.2849396 16.2790408,20.2496249 16.0843804,20.1472858 L12,18 Z" fill="#000000"/>
					                      </g>
					                  </svg><!--end::Svg Icon--></span>
														<span class="svg-icon svg-icon-primary svg-icon-2x"><svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
						                      <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
						                          <polygon points="0 0 24 0 24 24 0 24"/>
						                          <path d="M12,18 L7.91561963,20.1472858 C7.42677504,20.4042866 6.82214789,20.2163401 6.56514708,19.7274955 C6.46280801,19.5328351 6.42749334,19.309867 6.46467018,19.0931094 L7.24471742,14.545085 L3.94038429,11.3241562 C3.54490071,10.938655 3.5368084,10.3055417 3.92230962,9.91005817 C4.07581822,9.75257453 4.27696063,9.65008735 4.49459766,9.61846284 L9.06107374,8.95491503 L11.1032639,4.81698575 C11.3476862,4.32173209 11.9473121,4.11839309 12.4425657,4.36281539 C12.6397783,4.46014562 12.7994058,4.61977315 12.8967361,4.81698575 L14.9389263,8.95491503 L19.5054023,9.61846284 C20.0519472,9.69788046 20.4306287,10.2053233 20.351211,10.7518682 C20.3195865,10.9695052 20.2170993,11.1706476 20.0596157,11.3241562 L16.7552826,14.545085 L17.5353298,19.0931094 C17.6286908,19.6374458 17.263103,20.1544017 16.7187666,20.2477627 C16.5020089,20.2849396 16.2790408,20.2496249 16.0843804,20.1472858 L12,18 Z" fill="#000000"/>
						                      </g>
						                  </svg><!--end::Svg Icon--></span>
									</a>
								</div>
            </div>
					</div>
				</div>
			</div>
		</div>
		<!--end::Iconbox-->
	</div>

        <div class="col-lg-6 col-xl-4 mb-5">
		<!--begin::Iconbox-->
		<div class="card card-custom wave wave-animate-slower mb-8 mb-lg-0">
			<div class="card-body">
				<div class="d-flex align-items-center p-5">
					<div class="mr-6">
						<span class="svg-icon svg-icon-primary svg-icon svg-icon-7X"><svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
		                    <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
		                        <rect x="0" y="0" width="24" height="24"/>
		                        <rect fill="#000000" opacity="0.3" x="11.5" y="2" width="2" height="4" rx="1"/>
		                        <rect fill="#000000" opacity="0.3" x="11.5" y="16" width="2" height="5" rx="1"/>
		                        <path d="M15.493,8.044 C15.2143319,7.68933156 14.8501689,7.40750104 14.4005,7.1985 C13.9508311,6.98949895 13.5170021,6.885 13.099,6.885 C12.8836656,6.885 12.6651678,6.90399981 12.4435,6.942 C12.2218322,6.98000019 12.0223342,7.05283279 11.845,7.1605 C11.6676658,7.2681672 11.5188339,7.40749914 11.3985,7.5785 C11.2781661,7.74950085 11.218,7.96799867 11.218,8.234 C11.218,8.46200114 11.2654995,8.65199924 11.3605,8.804 C11.4555005,8.95600076 11.5948324,9.08899943 11.7785,9.203 C11.9621676,9.31700057 12.1806654,9.42149952 12.434,9.5165 C12.6873346,9.61150047 12.9723317,9.70966616 13.289,9.811 C13.7450023,9.96300076 14.2199975,10.1308324 14.714,10.3145 C15.2080025,10.4981676 15.6576646,10.7419985 16.063,11.046 C16.4683354,11.3500015 16.8039987,11.7268311 17.07,12.1765 C17.3360013,12.6261689 17.469,13.1866633 17.469,13.858 C17.469,14.6306705 17.3265014,15.2988305 17.0415,15.8625 C16.7564986,16.4261695 16.3733357,16.8916648 15.892,17.259 C15.4106643,17.6263352 14.8596698,17.8986658 14.239,18.076 C13.6183302,18.2533342 12.97867,18.342 12.32,18.342 C11.3573285,18.342 10.4263378,18.1741683 9.527,17.8385 C8.62766217,17.5028317 7.88033631,17.0246698 7.285,16.404 L9.413,14.238 C9.74233498,14.6433354 10.176164,14.9821653 10.7145,15.2545 C11.252836,15.5268347 11.7879973,15.663 12.32,15.663 C12.5606679,15.663 12.7949989,15.6376669 13.023,15.587 C13.2510011,15.5363331 13.4504991,15.4540006 13.6215,15.34 C13.7925009,15.2259994 13.9286662,15.0740009 14.03,14.884 C14.1313338,14.693999 14.182,14.4660013 14.182,14.2 C14.182,13.9466654 14.1186673,13.7313342 13.992,13.554 C13.8653327,13.3766658 13.6848345,13.2151674 13.4505,13.0695 C13.2161655,12.9238326 12.9248351,12.7908339 12.5765,12.6705 C12.2281649,12.5501661 11.8323355,12.420334 11.389,12.281 C10.9583312,12.141666 10.5371687,11.9770009 10.1255,11.787 C9.71383127,11.596999 9.34650161,11.3531682 9.0235,11.0555 C8.70049838,10.7578318 8.44083431,10.3968355 8.2445,9.9725 C8.04816568,9.54816454 7.95,9.03200304 7.95,8.424 C7.95,7.67666293 8.10199848,7.03700266 8.406,6.505 C8.71000152,5.97299734 9.10899753,5.53600171 9.603,5.194 C10.0970025,4.85199829 10.6543302,4.60183412 11.275,4.4435 C11.8956698,4.28516587 12.5226635,4.206 13.156,4.206 C13.9160038,4.206 14.6918294,4.34533194 15.4835,4.624 C16.2751706,4.90266806 16.9686637,5.31433061 17.564,5.859 L15.493,8.044 Z" fill="#000000"/>
		                    </g>
		                </svg><!--end::Svg Icon--></span>
		        </div>
					<div class="d-flex flex-column">
						<span class="text-dark text-hover-primary font-weight-bold font-size-h4 mb-3">
							<?php esc_html_e( 'Donate', 'mpt' ); ?>
						</span>
						<div class="text-dark-75">
                <a href="https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=contact%40magic-post-thumbnail.com&item_name=Donation+for+Magic+Post+Thumbnail&currency_code=EUR&source=url" target="_blank" >
                    <?php esc_html_e( 'Make a donation', 'mpt' ); ?>
                </a><br/>
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
