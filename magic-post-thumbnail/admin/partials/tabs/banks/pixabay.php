<?php
if ( ! function_exists( 'add_filter' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
		exit();
}
?>

<tr valign="top">
	<td colspan="2">
		<div class="update-nag">
			<?php _e('From now on, <b>it\'s required</b> to provide your own <b>username</b> and <b>api key</b>. You can register in Pixabay website <a target="_blank" href="https://pixabay.com/en/accounts/register/">here</a> and get api username/key <a target="_blank" href="https://pixabay.com/api/docs/">here</a>', 'mpt' ); ?>
		</div>
	</td>
</tr>

<tr valign="top">
	<th scope="row">
		<label for="hseparator"><?php esc_html_e( 'Pixabay username', 'mpt' ); ?></label>
	</th>
	<td>
		<input type="text" name="MPT_plugin_banks_settings[pixabay][username]" class="form-control" value="<?php echo( isset( $options['pixabay']['username'] ) && !empty( $options['pixabay']['username']) )? esc_attr( $options['pixabay']['username'] ): ''; ?>" >
	</td>
</tr>
<tr valign="top">
	<th scope="row">
		<label for="hseparator"><?php esc_html_e( 'Api key', 'mpt' ); ?></label>
	</th>
	<td id="password-pixabay" class="password">
		<input type="password" name="MPT_plugin_banks_settings[pixabay][apikey]" class="form-control" value="<?php echo( isset( $options['pixabay']['apikey'] ) && !empty( $options['pixabay']['apikey']) )? esc_attr( $options['pixabay']['apikey'] ): ''; ?>" >
		<i id="togglePassword"></i>
	</td>
</tr>
<tr valign="top">
	<td colspan="2">
		<button class="btn btn-primary" id="btnPixabay" onclick="return false;">
			<?php esc_html_e( 'API testing', 'mpt' ); ?>
		</button>
		<span id="resultPixabay"><img src="<?php echo plugin_dir_url( __FILE__ ); ?>../../../img/loader-mpt.gif" width="32" class="hidden"/></span>
	</td>
</tr>


<tr valign="top">
	<td colspan="2">
		<hr/>
	</td>
</tr>


<tr valign="top">
	<th scope="row">
		<label for="hseparator"><?php esc_html_e( 'Image Type', 'mpt' ); ?></label>
	</th>
	<td>
		<select name="MPT_plugin_banks_settings[pixabay][imgtype]" class="form-control" >
			<?php
			$selected = $options['pixabay']['imgtype'];

			$imgtype = array(
				__( '-- All --', 'mpt' )		=> 'all',
				__( 'Photo', 'mpt' )				=> 'photo',
				__( 'Illustration', 'mpt' )	=> 'illustration',
				__( 'Vector image', 'mpt' )				=> 'vector',
			);

			foreach( $imgtype as $name_imgtype => $code_imgtype ) {
				$choose=($selected == $code_imgtype)?'selected="selected"': '';
				echo '<option '. $choose .' value="'. $code_imgtype .'">'. $name_imgtype .'</option>';
			}
			?>
		</select>
	</td>
</tr>

<tr valign="top">
	<th scope="row">
		<label for="hseparator"><?php esc_html_e( 'Choose the language', 'mpt' ); ?></label>
	</th>
	<td>
		<select name="MPT_plugin_banks_settings[pixabay][search_country]" class="form-control" >
			<?php

				$selected = $options['pixabay']['search_country'];
				$country_choose = array(
					__( 'Czech', 'mpt' )				=> 'cs',
					__( 'Danish', 'mpt' )				=> 'da',
					__( 'German', 'mpt' )				=> 'de',
					__( 'English', 'mpt' )			=> 'en',
					__( 'Spanish', 'mpt' )			=> 'es',
					__( 'French', 'mpt' )				=> 'fr',
					__( 'Indonesian', 'mpt' )		=> 'id',
					__( 'Italian', 'mpt' )			=> 'it',
					__( 'Hungarian', 'mpt' )		=> 'hu',
					__( 'Dutch', 'mpt' )				=> 'nl',
					__( 'Norwegian', 'mpt' )		=> 'no',
					__( 'Polish', 'mpt' )				=> 'pl',
					__( 'Portuguese', 'mpt' )		=> 'pt',
					__( 'Romanian', 'mpt' )			=> 'ro',
					__( 'Slovak', 'mpt' )				=> 'sk',
					__( 'Finnish', 'mpt' )			=> 'fi',
					__( 'Swedish', 'mpt' )			=> 'sv',
					__( 'Turkish', 'mpt' )			=> 'tr',
					__( 'Vietnamese', 'mpt' )		=> 'vi',
					__( 'Thai', 'mpt' )					=> 'th',
					__( 'Bulgarian', 'mpt' )		=> 'bg',
					__( 'Russian', 'mpt' )			=> 'ru',
					__( 'Greek', 'mpt' )				=> 'el',
					__( 'Japanese', 'mpt' )			=> 'ja',
					__( 'Korean', 'mpt' )				=> 'ko',
					__( 'Chinese', 'mpt' )			=> 'zh',
				);
				ksort( $country_choose );

				foreach( $country_choose as $name_country => $code_country ) {
					$choose=($selected == $code_country)?'selected="selected"': '';
					echo '<option '. $choose .' value="'. $code_country .'">'. $name_country .'</option>';
				}
			?>
		</select>
	</td>
</tr>

<tr valign="top">
	<th scope="row">
		<label for="hseparator"><?php esc_html_e( 'Orientation', 'mpt' ); ?></label>
	</th>
	<td>
		<select name="MPT_plugin_banks_settings[pixabay][orientation]" class="form-control" >
			<?php
			$selected = $options['pixabay']['orientation'];

			$orientation = array(
				__( '-- All --', 'mpt' )	=> 'all',
				__( 'Horizontal', 'mpt' )	=> 'horizontal',
				__( 'Vertical', 'mpt' )		=> 'vertical',
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
		<label for="hseparator"><?php esc_html_e( 'Minimum width', 'mpt' ); ?></label>
	</th>
	<td>
		<input type="number" name="MPT_plugin_banks_settings[pixabay][min_width]" min="0" class="form-control" value="<?php echo( isset( $options['pixabay']['min_width'] ) && !empty( $options['pixabay']['min_width']) )? (int)$options['pixabay']['min_width']: '0'; ?>" >
		<i><?php esc_html_e( 'px minimum for width', 'mpt' ); ?></i>
	</td>
</tr>



<tr valign="top">
	<th scope="row">
		<label for="hseparator"><?php esc_html_e( 'Minimum height', 'mpt' ); ?></label>
	</th>
	<td>
		<input type="number" name="MPT_plugin_banks_settings[pixabay][min_height]" min="0" class="form-control" value="<?php echo( isset( $options['pixabay']['min_height'] ) && !empty( $options['pixabay']['min_height']) )? (int)$options['pixabay']['min_height']: '0'; ?>" >
		<i><?php esc_html_e( 'px minimum for height', 'mpt' ); ?></i>
	</td>
</tr>



<tr valign="top">
	<th scope="row">
		<label for="hseparator"><?php esc_html_e( 'Safesearch', 'mpt' ); ?></label>
	</th>
	<td>
		<select name="MPT_plugin_banks_settings[pixabay][safesearch]" class="form-control" >
			<?php
			$selected = $options['pixabay']['safesearch'];

			$safesearch = array(
				__( 'Off', 'mpt' )		=> 'false',
				__( 'Active', 'mpt' )	=> 'true',
			);

			foreach( $safesearch as $name_safesearch => $code_safesearch ) {
				$choose=($selected == $code_safesearch)?'selected="selected"': '';
				echo '<option '. $choose .' value="'. $code_safesearch .'">'. $name_safesearch .'</option>';
			}
			?>
		</select>
	</td>
</tr>
