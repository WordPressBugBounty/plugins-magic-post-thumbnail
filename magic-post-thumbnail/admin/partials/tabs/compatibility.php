<?php

if ( !function_exists( 'add_filter' ) ) {
    header( 'Status: 403 Forbidden' );
    header( 'HTTP/1.1 403 Forbidden' );
    exit;
}
?>
<div class="wrap">

		<?php 
settings_errors();
?>

    <form method="post" action="options.php" id="tabs">

		<?php 
settings_fields( 'MPT-plugin-compatibility-settings' );
$options = wp_parse_args( get_option( 'MPT_plugin_compatibility_settings' ), $this->MPT_default_options_compatibility_settings( TRUE ) );
$checkbox_disabled = '';
$disabled = '';
$executeElseBlock = true;
if ( $executeElseBlock ) {
    $checkbox_disabled = 'checkbox-disabled';
    $disabled = 'disabled="disabled"';
    ?>
				<table id="general-options" class="form-table tabs-content">
					<tbody>
						<tr valign="top">
								<td colspan="2" class="infos">
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
													<?php 
    esc_html_e( 'Only available with the pro version', 'mpt' );
    ?>
												</div>
										</div>
								</td>
						</tr>
					</tbody>
				</table>
		<?php 
}
?>
			<table id="general-options" class="form-table tabs-content">
				<tbody>
				<tr>
					<td>
							<?php 
esc_html_e( 'Compatibility with REST Requests', 'mpt' );
?>
					</td>
					<td>
						<label class="checkbox <?php 
echo $checkbox_disabled;
?>">
							<input <?php 
echo $disabled;
?> data-switch="true" type="checkbox" name="MPT_plugin_compatibility_settings[enable_REST]" id="enable_rest" value="true" <?php 
echo ( !empty( $options['enable_REST'] ) && $options['enable_REST'] == 'true' ? 'checked' : '' );
?> />
						</label>
					</td>
					<td>
						<p class="description">
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
												<?php 
_e( 'Enable compatibility with REST requests.<br><br> This can be useful, for example, if external services (such as AI posts) allow you to create posts directly in your WordPress.', 'mpt' );
?>
										</div>
								</div>
						</p>
					</td>
				</tr>
				<tr>
					<td colspan="3">
						<hr/>
					</td>
				<tr>
					<td>
						<?php 
esc_html_e( 'Compatibility with WP All Import', 'mpt' );
?>
					</td>
					<td>
						<label class="checkbox" <?php 
echo $checkbox_disabled;
?>>
							<input <?php 
echo $disabled;
?> data-switch="true" type="checkbox" name="MPT_plugin_compatibility_settings[enable_wpai]" id="enable_logs" value="true" <?php 
echo ( !empty( $options['enable_wpai'] ) && $options['enable_wpai'] == 'true' ? 'checked' : '' );
?> />
						</label>
					</td>
						<td>
						<p class="description">
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
									<?php 
esc_html_e( 'Enable compatibility with the plugin "WP All Import".', 'mpt' );
?>
								</div>
							</div>
						</p>
					</td>
					</tr>
									<tr>
					<td>
						<?php 
esc_html_e( 'Compatibility with WPeMatico', 'mpt' );
?>
					</td>
					<td>
						<label class="checkbox <?php 
echo $checkbox_disabled;
?>">
							<input <?php 
echo $disabled;
?> data-switch="true" type="checkbox" name="MPT_plugin_compatibility_settings[enable_wpematico]" id="enable_logs" value="true" <?php 
echo ( !empty( $options['enable_wpematico'] ) && $options['enable_wpematico'] == 'true' ? 'checked' : '' );
?> />
						</label>
					</td>
						<td>
						<p class="description">
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
									<?php 
esc_html_e( 'Enable compatibility with the plugin "WPeMatico".', 'mpt' );
?>
								</div>
							</div>
						</p>
					</td>
					</tr>
				<tr>
					<td>
						<?php 
esc_html_e( 'Compatibility with FeedWordPress', 'mpt' );
?>
					</td>
					<td>
						<label class="checkbox <?php 
echo $checkbox_disabled;
?>">
							<input <?php 
echo $disabled;
?> data-switch="true" type="checkbox" name="MPT_plugin_compatibility_settings[enable_feedwordpress]" id="enable_logs" value="true" <?php 
echo ( !empty( $options['enable_feedwordpress'] ) && $options['enable_feedwordpress'] == 'true' ? 'checked' : '' );
?> />
						</label>
					</td>
						<td>
						<p class="description">
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
									<?php 
esc_html_e( 'Enable compatibility with the plugin "FeedWordPress".', 'mpt' );
?>
								</div>
							</div>
						</p>
					</td>
				</tr>
				<tr>
					<td>
						<?php 
esc_html_e( 'Compatibility with WP Automatic Plugin', 'mpt' );
?>
					</td>
					<td>
						<label class="checkbox <?php 
echo $checkbox_disabled;
?>">
							<input <?php 
echo $disabled;
?> data-switch="true" type="checkbox" name="MPT_plugin_compatibility_settings[enable_wpautomatic]" id="enable_logs" value="true" <?php 
echo ( !empty( $options['enable_wpautomatic'] ) && $options['enable_wpautomatic'] == 'true' ? 'checked' : '' );
?> />
						</label>
					</td>
						<td>
						<p class="description">
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
									<?php 
esc_html_e( 'Enable compatibility with the plugin "WP Automatic Plugin".', 'mpt' );
?>
								</div>
							</div>
						</p>
					</td>
				</tr>
				<tr>
					<td colspan="3">
						<hr/>
					</td>
				</tr>
				</tr>
					<tr>
					<td>
						<?php 
esc_html_e( 'Compatibility with FIFU', 'mpt' );
?>
					</td>
					<td>
						<label class="checkbox <?php 
echo $checkbox_disabled;
?>">
							<input <?php 
echo $disabled;
?> data-switch="true" type="checkbox" name="MPT_plugin_compatibility_settings[enable_FIFU]" id="enable_logs" value="true" <?php 
echo ( !empty( $options['enable_FIFU'] ) && $options['enable_FIFU'] == 'true' ? 'checked' : '' );
?> />
						</label>
					</td>
						<td>
							<p class="description">
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
										<?php 
_e( 'Enable compatibility with the plugin "Featured Image from URL".<br><br> Warning: This option will not upload your images into your media anymore.<br><br> Warning 2: Does not work with DALL-E & Stable Diffusion.', 'mpt' );
?>
									</div>
								</div>
							</p>
					</td>
				</tr>
				<tr>
					<td>
						<?php 
esc_html_e( 'Compatibility with CMB2', 'mpt' );
?>
					</td>
					<td>
						<label class="checkbox <?php 
echo $checkbox_disabled;
?>">
							<input <?php 
echo $disabled;
?> data-switch="true" type="checkbox" name="MPT_plugin_compatibility_settings[enable_cmb2]" id="enable_logs" value="true" <?php 
echo ( !empty( $options['enable_cmb2'] ) && $options['enable_cmb2'] == 'true' ? 'checked' : '' );
?> />
						</label>
					</td>
						<td>
						<p class="description">
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
									<?php 
esc_html_e( 'Enable compatibility with CMB2 plugin to set image to CMB2 field', 'mpt' );
?>
								</div>
							</div>
						</p>
					</td>
				</tr>
				<tr>
					<td>
						<?php 
esc_html_e( 'Compatibility with ACF', 'mpt' );
?>
					</td>
					<td>
						<label class="checkbox <?php 
echo $checkbox_disabled;
?>">
							<input <?php 
echo $disabled;
?> data-switch="true" type="checkbox" name="MPT_plugin_compatibility_settings[enable_acf]" id="enable_logs" value="true" <?php 
echo ( !empty( $options['enable_acf'] ) && $options['enable_acf'] == 'true' ? 'checked' : '' );
?> />
						</label>
					</td>
						<td>
						<p class="description">
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
									<?php 
esc_html_e( 'Enable compatibility with ACF plugin (Advanced Custom Fields) to set image to ACF field', 'mpt' );
?>
								</div>
							</div>
						</p>
					</td>
				</tr>
				<tr>
					<td>
						<?php 
esc_html_e( 'Compatibility with Meta Box', 'mpt' );
?>
					</td>
					<td>
						<label class="checkbox <?php 
echo $checkbox_disabled;
?>">
							<input <?php 
echo $disabled;
?> data-switch="true" type="checkbox" name="MPT_plugin_compatibility_settings[enable_metaboxio]" id="enable_logs" value="true" <?php 
echo ( !empty( $options['enable_metaboxio'] ) && $options['enable_metaboxio'] == 'true' ? 'checked' : '' );
?> />
						</label>
					</td>
						<td>
						<p class="description">
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
									<?php 
esc_html_e( 'Enable compatibility with Meta Box plugin (metabox.io) to set image to Meta Box field', 'mpt' );
?>
								</div>
							</div>
						</p>
					</td>
				</tr>
				</tbody>
			</table>
		<?php 
submit_button();
?>

    </form>
</div>
