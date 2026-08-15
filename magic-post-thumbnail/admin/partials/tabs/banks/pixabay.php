<?php
if ( ! function_exists( 'add_filter' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
		exit();
}
?>

<tr valign="top">
	<td colspan="2" class="source-logo"><img alt="Pixabay Logo" src="<?php echo plugin_dir_url( __FILE__ ); ?>/img/pixabay.png"></td>
</tr>

<tr valign="top">
	<td colspan="2">
		<div class="update-nag">
			<?php _e('From now on, <b>it\'s required</b> to provide your own <b>username</b> and <b>api key</b>. You can register in Pixabay website <a target="_blank" href="https://pixabay.com/en/accounts/register/">here</a> and get api username/key <a target="_blank" href="https://pixabay.com/api/docs/">here</a>', 'magic-post-thumbnail' ); ?>
		</div>
	</td>
</tr>

<tr valign="top">
	<th scope="row">
		<label for="hseparator"><?php esc_html_e( 'Pixabay username', 'magic-post-thumbnail' ); ?></label>
	</th>
	<td>
		<input type="text" name="MPT_plugin_banks_settings[pixabay][username]" class="form-control" value="<?php echo( isset( $options['pixabay']['username'] ) && !empty( $options['pixabay']['username']) )? esc_attr( $options['pixabay']['username'] ): ''; ?>" >
	</td>
</tr>
<tr valign="top">
	<th scope="row">
		<label for="hseparator"><?php esc_html_e( 'Api key', 'magic-post-thumbnail' ); ?></label>
	</th>
	<td id="password-pixabay" class="password">
		<input type="password" name="MPT_plugin_banks_settings[pixabay][apikey]" class="form-control" value="<?php echo( isset( $options['pixabay']['apikey'] ) && !empty( $options['pixabay']['apikey']) )? esc_attr( $options['pixabay']['apikey'] ): ''; ?>" >
		<i id="togglePassword"></i>
	</td>
</tr>
<tr valign="top">
	<td colspan="2">
		<button class="btn btn-primary" id="btnPixabay" onclick="return false;">
			<?php esc_html_e( 'API testing', 'magic-post-thumbnail' ); ?>
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
		<label for="hseparator"><?php esc_html_e( 'Image Type', 'magic-post-thumbnail' ); ?></label>
	</th>
	<td>
		<select name="MPT_plugin_banks_settings[pixabay][imgtype]" class="form-control" >
			<?php
			$selected = $options['pixabay']['imgtype'];

			$imgtype = array(
				__( '-- All --', 'magic-post-thumbnail' )		=> 'all',
				__( 'Photo', 'magic-post-thumbnail' )				=> 'photo',
				__( 'Illustration', 'magic-post-thumbnail' )	=> 'illustration',
				__( 'Vector image', 'magic-post-thumbnail' )				=> 'vector',
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
		<label for="hseparator"><?php esc_html_e( 'Choose the language', 'magic-post-thumbnail' ); ?></label>
	</th>
	<td>
		<select name="MPT_plugin_banks_settings[pixabay][search_country]" class="form-control" >
			<?php

				$selected = $options['pixabay']['search_country'];
				$country_choose = array(
					__( 'Czech', 'magic-post-thumbnail' )				=> 'cs',
					__( 'Danish', 'magic-post-thumbnail' )				=> 'da',
					__( 'German', 'magic-post-thumbnail' )				=> 'de',
					__( 'English', 'magic-post-thumbnail' )			=> 'en',
					__( 'Spanish', 'magic-post-thumbnail' )			=> 'es',
					__( 'French', 'magic-post-thumbnail' )				=> 'fr',
					__( 'Indonesian', 'magic-post-thumbnail' )		=> 'id',
					__( 'Italian', 'magic-post-thumbnail' )			=> 'it',
					__( 'Hungarian', 'magic-post-thumbnail' )		=> 'hu',
					__( 'Dutch', 'magic-post-thumbnail' )				=> 'nl',
					__( 'Norwegian', 'magic-post-thumbnail' )		=> 'no',
					__( 'Polish', 'magic-post-thumbnail' )				=> 'pl',
					__( 'Portuguese', 'magic-post-thumbnail' )		=> 'pt',
					__( 'Romanian', 'magic-post-thumbnail' )			=> 'ro',
					__( 'Slovak', 'magic-post-thumbnail' )				=> 'sk',
					__( 'Finnish', 'magic-post-thumbnail' )			=> 'fi',
					__( 'Swedish', 'magic-post-thumbnail' )			=> 'sv',
					__( 'Turkish', 'magic-post-thumbnail' )			=> 'tr',
					__( 'Vietnamese', 'magic-post-thumbnail' )		=> 'vi',
					__( 'Thai', 'magic-post-thumbnail' )					=> 'th',
					__( 'Bulgarian', 'magic-post-thumbnail' )		=> 'bg',
					__( 'Russian', 'magic-post-thumbnail' )			=> 'ru',
					__( 'Greek', 'magic-post-thumbnail' )				=> 'el',
					__( 'Japanese', 'magic-post-thumbnail' )			=> 'ja',
					__( 'Korean', 'magic-post-thumbnail' )				=> 'ko',
					__( 'Chinese', 'magic-post-thumbnail' )			=> 'zh',
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
		<label for="hseparator"><?php esc_html_e( 'Orientation', 'magic-post-thumbnail' ); ?></label>
	</th>
	<td>
		<select name="MPT_plugin_banks_settings[pixabay][orientation]" class="form-control" >
			<?php
			$selected = $options['pixabay']['orientation'];

			$orientation = array(
				__( '-- All --', 'magic-post-thumbnail' )	=> 'all',
				__( 'Horizontal', 'magic-post-thumbnail' )	=> 'horizontal',
				__( 'Vertical', 'magic-post-thumbnail' )		=> 'vertical',
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
		<label for="hseparator"><?php esc_html_e( 'Minimum width', 'magic-post-thumbnail' ); ?></label>
	</th>
	<td>
		<input type="number" name="MPT_plugin_banks_settings[pixabay][min_width]" min="0" class="form-control" value="<?php echo( isset( $options['pixabay']['min_width'] ) && !empty( $options['pixabay']['min_width']) )? (int)$options['pixabay']['min_width']: '0'; ?>" >
		<i><?php esc_html_e( 'px minimum for width', 'magic-post-thumbnail' ); ?></i>
	</td>
</tr>



<tr valign="top">
	<th scope="row">
		<label for="hseparator"><?php esc_html_e( 'Minimum height', 'magic-post-thumbnail' ); ?></label>
	</th>
	<td>
		<input type="number" name="MPT_plugin_banks_settings[pixabay][min_height]" min="0" class="form-control" value="<?php echo( isset( $options['pixabay']['min_height'] ) && !empty( $options['pixabay']['min_height']) )? (int)$options['pixabay']['min_height']: '0'; ?>" >
		<i><?php esc_html_e( 'px minimum for height', 'magic-post-thumbnail' ); ?></i>
	</td>
</tr>



<tr valign="top">
	<th scope="row">
		<label for="hseparator"><?php esc_html_e( 'Safesearch', 'magic-post-thumbnail' ); ?></label>
	</th>
	<td>
		<select name="MPT_plugin_banks_settings[pixabay][safesearch]" class="form-control" >
			<?php
			$selected = $options['pixabay']['safesearch'];

			$safesearch = array(
				__( 'Off', 'magic-post-thumbnail' )		=> 'false',
				__( 'Active', 'magic-post-thumbnail' )	=> 'true',
			);

			foreach( $safesearch as $name_safesearch => $code_safesearch ) {
				$choose=($selected == $code_safesearch)?'selected="selected"': '';
				echo '<option '. $choose .' value="'. $code_safesearch .'">'. $name_safesearch .'</option>';
			}
			?>
		</select>
	</td>
</tr>
