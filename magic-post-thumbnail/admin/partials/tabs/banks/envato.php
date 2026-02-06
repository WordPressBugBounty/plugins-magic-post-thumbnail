<?php
if ( ! function_exists( 'add_filter' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
		exit();
}

// Create link to generate Envato token
$extension_id           = md5( get_site_url() );
$extension_description  = urlencode( get_bloginfo( 'name' ) );
$envato_token_link      = 'https://api.extensions.envato.com/extensions/begin_activation?extension_id='.$extension_id.'&extension_type=envato-wordpress&extension_description='.$extension_description;
?>

<tr valign="top">
	<td colspan="2" class="source-logo"><img alt="Envato Logo" src="<?php echo plugin_dir_url( __FILE__ ); ?>/img/envato.png"></td>
</tr>

<tr valign="top">
	<td colspan="2">
		<div class="update-nag">
			<?php
				echo sprintf(
					__('You can use your Envato Elements subscription. You can sign up for <a target="_blank" href="%1$s">Envato Elements here</a> and <a target="_blank" href="%2$s">follow this link</a> to generate the token (one token per website).', 'mpt'),
					'https://1.envato.market/9gGeK5',
					$envato_token_link
				);
				?>
		</div>
	</td>
</tr>

<tr valign="top">
	<th scope="row">
		<label for="hseparator"><?php esc_html_e( 'Envato Token', 'mpt' ); ?></label>
	</th>
	<td id="password-envato" class="password">
		<input type="password" name="MPT_plugin_banks_settings[envato][envato_token]" class="form-control" value="<?php echo( isset( $options['envato']['envato_token'] ) && !empty( $options['envato']['envato_token']) )? esc_attr( $options['envato']['envato_token'] ): ''; ?>" >
		<i id="togglePassword"></i>
	</td>
</tr>

<tr valign="top">
	<td colspan="2">
		<button class="btn btn-primary" id="btnEnvato" onclick="return false;">
			<?php esc_html_e( 'API testing', 'mpt' ); ?>
		</button>
		<span id="resultEnvato"><img src="<?php echo plugin_dir_url( __FILE__ ); ?>../../../img/loader-mpt.gif" width="32" class="hidden"/></span>
	</td>
</tr>

<tr valign="top">
	<th scope="row">
		<label for="hseparator"><?php esc_html_e( 'Orientation', 'mpt' ); ?></label>
	</th>
	<td>
		<select name="MPT_plugin_banks_settings[envato][orientation]" class="form-control" >
			<?php
			$selected = $options['envato']['orientation'];

			$orientation = array(
        esc_html__( 'All', 'mpt' )        => '',
				esc_html__( 'Landscape', 'mpt' )  => 'Landscape',
				esc_html__( 'Portrait', 'mpt' )   => 'Portrait',
        esc_html__( 'Square', 'mpt' )     => 'Square'
			);

			foreach( $orientation as $name_orientation => $code_orientation ) {
				$choose=($selected == $code_orientation)?'selected="selected"': '';
				echo '<option '. $choose .' value="'. $code_orientation .'">'. $name_orientation .'</option>';
			}
			?>
		</select>
	</td>
</tr>

<tr valign="top">
	<th scope="row">
		<label for="hseparator"><?php esc_html_e( 'Number of people', 'mpt' ); ?></label>
	</th>
	<td>
		<select name="MPT_plugin_banks_settings[envato][number_of_people]" class="form-control" >
			<?php
			$selected = $options['envato']['number_of_people'];

			$size = array(
        esc_html__( 'All', 'mpt' )          => '',
        esc_html__( 'No people', 'mpt' )    => 'No people',
        esc_html__( '1 person', 'mpt' )     => '1 person',
				esc_html__( '2 people', 'mpt' )     => '2 people',
        esc_html__( '3+ people', 'mpt' )    => '3+ people',
			);

			foreach( $size as $name_size => $code_size ) {
				$choose=($selected == $code_size)?'selected="selected"': '';
				echo '<option '. $choose .' value="'. $code_size .'">'. $name_size .'</option>';
			}
			?>
		</select>
	</td>
</tr>

<tr valign="top">
	<td colspan="2">
		<hr/>
	</td>
</tr>
