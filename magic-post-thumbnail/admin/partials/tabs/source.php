<?php

if ( !function_exists( 'add_filter' ) ) {
    header( 'Status: 403 Forbidden' );
    header( 'HTTP/1.1 403 Forbidden' );
    exit;
}
?>
<div class="wrap">

	<?php 
$this->MPT_promo_black_friday_html();
?>

	<?php 
settings_errors();
?>

    <form method="post" action="options.php">

            <?php 
settings_fields( 'MPT-plugin-banks-settings' );
$options = wp_parse_args( get_option( 'MPT_plugin_banks_settings' ), $this->MPT_default_options_banks_settings( false ) );
$list_api_auto = $this->MPT_banks_name_auto();
$saved_auto = array();
if ( !empty( $options['api_chosen_auto'] ) && is_array( $options['api_chosen_auto'] ) ) {
    foreach ( $options['api_chosen_auto'] as $saved_slug ) {
        $saved_auto[] = $this->MPT_normalize_bank_slug( ( is_string( $saved_slug ) ? $saved_slug : '' ) );
    }
}
$order_auto = array();
if ( !empty( $options['api_chosen_auto'] ) && is_array( $options['api_chosen_auto'] ) ) {
    foreach ( array_keys( $options['api_chosen_auto'] ) as $saved_key ) {
        $order_auto[] = $this->MPT_normalize_bank_slug( $saved_key );
    }
}
$status_labels = array(
    'ready'    => esc_html__( 'Ready', 'magic-post-thumbnail' ),
    'missing'  => esc_html__( 'No key', 'magic-post-thumbnail' ),
    'untested' => esc_html__( 'Untested', 'magic-post-thumbnail' ),
    'valid'    => esc_html__( 'Key OK', 'magic-post-thumbnail' ),
    'invalid'  => esc_html__( 'Key failed', 'magic-post-thumbnail' ),
);
?>

            <div class="alert alert-custom alert-default" role="alert">
                <div class="alert-icon"><span class="svg-icon svg-icon-primary svg-icon-xl"><svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
                    <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                        <rect x="0" y="0" width="24" height="24"></rect>
                        <path d="M7.07744993,12.3040451 C7.72444571,13.0716094 8.54044565,13.6920474 9.46808594,14.1079953 L5,23 L4.5,18 L7.07744993,12.3040451 Z M14.5865511,14.2597864 C15.5319561,13.9019016 16.375416,13.3366121 17.0614026,12.6194459 L19.5,18 L19,23 L14.5865511,14.2597864 Z M12,3.55271368e-14 C12.8284271,3.53749572e-14 13.5,0.671572875 13.5,1.5 L13.5,4 L10.5,4 L10.5,1.5 C10.5,0.671572875 11.1715729,3.56793164e-14 12,3.55271368e-14 Z" fill="#000000" opacity="0.3"></path>
                        <path d="M12,10 C13.1045695,10 14,9.1045695 14,8 C14,6.8954305 13.1045695,6 12,6 C10.8954305,6 10,6.8954305 10,8 C10,9.1045695 10.8954305,10 12,10 Z M12,13 C9.23857625,13 7,10.7614237 7,8 C7,5.23857625 9.23857625,3 12,3 C14.7614237,3 17,5.23857625 17,8 C17,10.7614237 14.7614237,13 12,13 Z" fill="#000000" fill-rule="nonzero"></path>
                    </g>
                </svg></span>
                </div>
                <div class="alert-text">
                    <?php 
esc_html_e( 'This list is the fallback chain used by every automatic generation (editor button, bulk, cron, imports). Drag to set priority. Gutenberg search banks are chosen on the Gutenberg Block page.', 'magic-post-thumbnail' );
?>
                </div>
            </div>

			<?php 
$enabled_banks = array();
foreach ( $list_api_auto as $api => $api_code ) {
    if ( false === $api_code[1] ) {
        continue;
    }
    $enabled_banks[] = array(
        'label' => $api,
        'slug'  => $api_code[0],
    );
}
$panel_ids = array();
foreach ( $enabled_banks as $index => $bank ) {
    $panel_ids[$bank['slug']] = 'tab-' . (int) $index;
}
// Checked and usable banks form the visible chain. Everything else is folded away.
$rows_active = array();
$rows_other = array();
foreach ( $list_api_auto as $api => $api_code ) {
    $slug = $api_code[0];
    $enabled = true === $api_code[1];
    $row = array(
        'api'  => $api,
        'code' => $api_code,
    );
    if ( $enabled && in_array( $slug, $saved_auto, true ) ) {
        $position = array_search( $slug, $order_auto, true );
        $row['sort'] = ( false === $position ? 99 : $position );
        $rows_active[] = $row;
    } else {
        $rows_other[] = $row;
    }
}
usort( $rows_active, function ( $a, $b ) {
    if ( $a['sort'] === $b['sort'] ) {
        return 0;
    }
    return ( $a['sort'] < $b['sort'] ? -1 : 1 );
} );
// The panel shown on load: first bank of the chain, so no flash and no reliance on JS.
$default_slug = '';
if ( !empty( $rows_active ) ) {
    $default_slug = $rows_active[0]['code'][0];
} elseif ( !empty( $enabled_banks ) ) {
    $default_slug = $enabled_banks[0]['slug'];
}
$default_status = ( '' !== $default_slug ? $this->MPT_bank_key_status( $default_slug, $options ) : '' );
$default_label = ( isset( $status_labels[$default_status] ) ? $status_labels[$default_status] : $default_status );
$render_bank_item = function ( $api, $api_code, $position ) use(
    $options,
    $status_labels,
    $panel_ids,
    $default_slug
) {
    $slug = $api_code[0];
    $enabled = true === $api_code[1];
    $is_ai = $this->MPT_bank_is_paid( $slug );
    $panel_id = ( isset( $panel_ids[$slug] ) ? $panel_ids[$slug] : '' );
    $status = ( $enabled ? $this->MPT_bank_key_status( $slug, $options ) : '' );
    $label = ( '' !== $status && isset( $status_labels[$status] ) ? $status_labels[$status] : $status );
    $order = ( $position ? (int) $position : 99 );
    $html = '<li class="mpt-bank-item' . (( $enabled ? '' : ' mpt-bank-item-locked' )) . (( '' !== $panel_id && $slug === $default_slug ? ' is-selected' : '' )) . '"';
    $html .= ' data-bank="' . esc_attr( $slug ) . '"';
    $html .= ' data-label="' . esc_attr( $api ) . '"';
    $html .= ' data-panel="' . esc_attr( $panel_id ) . '"';
    $html .= ' data-status="' . esc_attr( $status ) . '"';
    $html .= ' data-status-label="' . esc_attr( $label ) . '">';
    $html .= '<span class="mpt-bank-pos" aria-hidden="true">' . (( $position ? (int) $position : '' )) . '</span>';
    $html .= '<label class="checkbox' . (( $enabled ? '' : ' checkbox-disabled' )) . '">';
    $html .= '<input class="ordered-checkbox" data-order="' . $order . '" data-ai="' . (( $is_ai ? '1' : '0' )) . '" type="checkbox"';
    $html .= ( $position ? ' checked="checked"' : '' );
    $html .= ( $enabled ? '' : ' disabled' );
    $html .= ' value="' . esc_attr( $slug ) . '" name="MPT_plugin_banks_settings[api_chosen_auto][' . esc_attr( $slug ) . ']">';
    $html .= ' <span></span></label>';
    $html .= '<span class="mpt-bank-name">' . esc_html( $api ) . '</span>';
    $html .= ( $is_ai ? '<em class="mpt-bank-type mpt-bank-ai">' . esc_html__( 'AI', 'magic-post-thumbnail' ) . '</em>' : (( in_array( $slug, $this->MPT_local_bank_slugs(), true ) ? '<em class="mpt-bank-type mpt-bank-local">' . esc_html__( 'Local', 'magic-post-thumbnail' ) . '</em>' : '<em class="mpt-bank-type mpt-bank-photo">' . esc_html__( 'Photos', 'magic-post-thumbnail' ) . '</em>' )) );
    if ( !$enabled ) {
        $html .= '<em class="mpt-bank-type mpt-bank-locked">' . esc_html__( 'Pro', 'magic-post-thumbnail' ) . '</em>';
    } else {
        $html .= '<span class="mpt-key-badge mpt-key-' . esc_attr( $status ) . '">' . esc_html( $label ) . '</span>';
    }
    if ( '' !== $panel_id ) {
        $html .= '<button type="button" class="button-link mpt-bank-configure" aria-label="' . esc_attr( sprintf( __( 'Configure %s', 'magic-post-thumbnail' ), $api ) ) . '">' . esc_html__( 'Configure', 'magic-post-thumbnail' ) . '</button>';
    }
    return $html . '</li>';
};
?>

			<div class="mpt-source-layout">

				<div class="mpt-source-col-chain chosen_api" id="mpt-bank-chain">

					<h3><?php 
esc_html_e( 'Automatic generation chain', 'magic-post-thumbnail' );
?></h3>
					<p class="description"><?php 
esc_html_e( 'Used by the editor button, bulk generation, cron and imports. Drag to reorder: the first bank is tried first.', 'magic-post-thumbnail' );
?></p>

					<ul class="radio-list mpt-bank-list" id="mpt-sortable-auto">
						<?php 
foreach ( $rows_active as $index => $row ) {
    echo $render_bank_item( $row['api'], $row['code'], $index + 1 );
    // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}
?>
					</ul>

					<p id="mpt-chain-empty" class="mpt-chain-empty"<?php 
echo ( empty( $rows_active ) ? '' : ' hidden' );
?>>
						<?php 
esc_html_e( 'No bank selected. Add at least one bank below, otherwise no image can be generated.', 'magic-post-thumbnail' );
?>
					</p>

					<p id="mpt-chain-unsaved" class="mpt-chain-unsaved" hidden>
						<?php 
esc_html_e( 'Chain not saved yet. Generation on posts still uses the last saved chain until you click Save.', 'magic-post-thumbnail' );
?>
					</p>

					<div class="mpt-banks-more">
						<ul class="radio-list mpt-bank-list" id="mpt-banks-inactive">
							<?php 
foreach ( $rows_other as $row ) {
    echo $render_bank_item( $row['api'], $row['code'], null );
    // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}
?>
						</ul>
					</div>

				</div>

				<div class="mpt-source-col-settings">

					<h3><?php 
esc_html_e( 'Banks Settings', 'magic-post-thumbnail' );
?></h3>
					<p class="description"><?php 
esc_html_e( 'Select a bank on the left to edit its API key, size, language and filters.', 'magic-post-thumbnail' );
?></p>

					<div id="mpt-bank-settings" class="mpt-bank-settings">
						<p class="mpt-bank-settings-head">
							<strong id="mpt-bank-settings-title"><?php 
echo esc_html( ( '' !== $default_slug ? $this->MPT_bank_label( $default_slug ) : '' ) );
?></strong>
							<span id="mpt-bank-settings-status" class="mpt-key-badge mpt-key-<?php 
echo esc_attr( $default_status );
?>"><?php 
echo esc_html( $default_label );
?></span>
						</p>
						<div class="mpt-bank-settings-panels">
						<?php 
foreach ( $enabled_banks as $index => $bank ) {
    $executeElseBlock = true;
    $file = $this->MPT_bank_settings_file( $bank['slug'] );
    $panel_style = ( $bank['slug'] === $default_slug ? '' : ' style="display: none;"' );
    echo '<table id="tab-' . (int) $index . '" class="form-table mpt-bank-panel"' . $panel_style . '>';
    // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    echo '<tbody>';
    if ( $executeElseBlock ) {
        if ( !in_array( $bank['slug'], array(
            'youtube',
            'unsplash',
            'pexels',
            'stability',
            'replicate',
            'ideogram',
            'flux2',
            'gemini',
            'grok'
        ), true ) ) {
            include_once 'banks/' . $file . '.php';
        }
    }
    echo '</tbody>';
    echo '</table>';
}
?>
						</div>
					</div>

					<?php 
$preview_ai = '' !== $default_slug && $this->MPT_bank_is_paid( $default_slug );
?>
					<div class="mpt-preview-box" id="mpt-preview">
						<h3><?php 
esc_html_e( 'Preview a search', 'magic-post-thumbnail' );
?></h3>
						<p class="description"><?php 
esc_html_e( 'Test this bank without creating an image on a post. Uses the last saved API key and filters.', 'magic-post-thumbnail' );
?></p>
						<p id="mpt-preview-ai-warning" class="mpt-preview-ai-warning"<?php 
echo ( $preview_ai ? '' : ' hidden' );
?>>
							<?php 
if ( $preview_ai ) {
    printf( 
        /* translators: %s: bank name */
        esc_html__( 'This bank (%s) is a generative AI service. Preview will consume tokens.', 'magic-post-thumbnail' ),
        esc_html( $this->MPT_bank_label( $default_slug ) )
     );
}
?>
						</p>
						<p>
							<input type="text" id="mpt-preview-keyword" class="regular-text" value="sunset" />
							<button type="button" class="button button-secondary" id="mpt-preview-btn"><?php 
esc_html_e( 'Preview', 'magic-post-thumbnail' );
?></button>
						</p>
						<div id="mpt-preview-results"></div>
					</div>

				</div>

			</div>

            <?php 
submit_button();
?>

    </form>
</div>

