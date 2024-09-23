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
			<?php _e('<a target="_blank" href="https://oldsearch.creativecommons.org/">CC Search</a> provides Creative Commons images. You can search these images manually from <a target="_blank" href="https://wordpress.org/openverse/">Openverse (WordPress)</a>.', 'mpt' ); ?>
		</div>
	</td>
</tr>

<tr valign="top">
	<th scope="row">
		<label for="hseparator"><?php esc_html_e( 'Source', 'mpt' ); ?></label>
	</th>
	<td>

		<select name="MPT_plugin_banks_settings[cc_search][source]" class="form-control form-control-lg" >
			<?php
				$selected = $options['cc_search']['source'];
				$source_choose = array(
					esc_html__( 'All sources', 'mpt' )          => 1,
					esc_html__( 'All except flickr', 'mpt' )    => 2,
					esc_html__( 'Other', 'mpt' )                => 3
				);
				//ksort( $country_choose );

				foreach( $source_choose as $name_source => $code_source ) {
					$choose = ( $selected == $code_source) ? 'selected="selected"': '';
					echo '<option '. $choose .' value="'. $code_source .'">'. $name_source .'</option>';
				}
			?>
		</select>
	</td>
</tr>

<tr valign="top">
	<th scope="row">
		<label for="hseparator"><?php esc_html_e( 'Rights', 'mpt' ); ?></label>
	</th>
	<td class="checkbox-list">
		<p class="description">
			<?php esc_html_e( 'Choose these options can reduce relevance of results, but permit to use free-to-use images.', 'mpt' ); ?>
		</p>
		<?php
			$rights_array = array(
        		esc_html__( 'Zéro' )                                            => 'CC0',
				esc_html__( 'Attribution-NoDerivs', 'mpt' )                     => 'BY-ND',
				esc_html__( 'Attribution-NonCommercial', 'mpt' )                => 'BY-NC',
				esc_html__( 'Attribution-NonCommercial-ShareAlike', 'mpt' )     => 'BY-NC-SA',
				esc_html__( 'Attribution-NonCommercial-NoDerivs', 'mpt' )       => 'BY-NC-ND',
				esc_html__( 'Attribution-ShareAlike', 'mpt' )                   => 'BY-SA',
				esc_html__( 'Attribution', 'mpt' )                              => 'BY',
				esc_html__( 'Public Domain Mark', 'mpt' )                       => 'PDM',
			);


			foreach ( $rights_array as $right => $right_code ) {
				$checked= ( isset( $options['cc_search']['rights'] ) && !empty( $options['cc_search']['rights'] ) && in_array( $right_code, $options['cc_search']['rights'] ) )? 'checked="checked""' : '';
				echo '<label class="checkbox">
					<input '. $checked .' name="MPT_plugin_banks_settings[cc_search][rights]['. $right_code .']" type="checkbox" value="'. $right_code .'"> <span></span> '. $right .'
				</label>';
			}
		?>

	</td>
</tr>

<tr valign="top">
	<th scope="row">
		<label for="hseparator"><?php esc_html_e( 'Image Type', 'mpt' ); ?></label>
	</th>
	<td>
		<select name="MPT_plugin_banks_settings[cc_search][imgtype]" class="form-control form-control-lg" >
			<?php
			$selected = $options['cc_search']['imgtype'];

			$formats = array(
				esc_html__( '-- Default --', 'mpt' )            => '',
				esc_html__( 'Illustration', 'mpt' )             => 'illustration',
				esc_html__( 'Photography', 'mpt' )             => 'photograph',
				esc_html__( 'Digitized Artwork', 'mpt' )        => 'digitized_artwork'
			);

			// format
			foreach( $formats as $name_format => $code_format ) {
				$choose=($selected == $code_format)?'selected="selected"': '';
				echo '<option '. $choose .' value="'. $code_format .'">'. $name_format .'</option>';
			}
			?>
		</select>
	</td>
</tr>

<tr valign="top">
	<th scope="row">
		<label for="hseparator"><?php esc_html_e( 'Aspect Ratio', 'mpt' ); ?></label>
	</th>
	<td>
		<select name="MPT_plugin_banks_settings[cc_search][aspect_ratio]" class="form-control form-control-lg" >
			<?php
			$selected = $options['cc_search']['aspect_ratio'];

			$aspect_ratio = array(
				esc_html__( 'Tall', 'mpt' )     => 'tall',
				esc_html__( 'Wide', 'mpt' )     => 'wide',
				esc_html__( 'Square', 'mpt' )   => 'square'
			);

			foreach( $aspect_ratio as $name_aspect_ratio => $code_aspect_ratio ) {
				$choose = ( $selected == $code_aspect_ratio ) ? 'selected="selected"' : '';
				echo '<option '. $choose .' value="'. $code_aspect_ratio .'">'. $name_aspect_ratio .'</option>';
			}
			?>
		</select>
	</td>
</tr>
