<?php
if ( ! function_exists( 'add_filter' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
		exit();
}

?>
<div class="wrap">

		<?php settings_errors(); ?>

    <form method="post" action="options.php" id="tabs">

        <?php
            settings_fields( 'MPT-plugin-logs-settings' );
            $options = wp_parse_args( get_option( 'MPT_plugin_logs_settings' ), $this->MPT_default_options_logs_settings( TRUE ) );

            $current_file = $this->MPT_log_file( true );
            $log_path     = $this->MPT_log_file_path( true );

            if ( false !== $log_path && is_readable( $log_path ) ) {
                $file_content = $this->MPT_log_readable_contents( $log_path );
            } else {
                $file_content = esc_html__( 'No log yet', 'magic-post-thumbnail' );
            }

        ?>

        <table id="general-options" class="form-table logs">
                <tbody>
                    <tr valign="top">
                            <th scope="row">
                                    <?php esc_html_e( 'Enable Logs', 'magic-post-thumbnail' ); ?>
                            </th>
                            <td>
                                    <input data-switch="true" type="checkbox" name="MPT_plugin_logs_settings[logs]" id="enable_logs" value="true" <?php echo( !empty( $options['logs']) && $options['logs'] == 'true' )? 'checked': ''; ?> />
                            </td>
                    </tr>

                    <tr valign="top" class="show_logs" <?php echo(($options['logs'] != 'true') ? 'style="display:none;"': ''); ?>>
                        <th scope="row">
                                <?php esc_html_e( 'Logs history', 'magic-post-thumbnail' ); ?>
                        </th>

                        <td>
                            <pre id="logs-block" class="text-white bg-dark"><?php echo esc_html( $file_content ); ?></pre>
                            <?php
                                if( false !== $current_file ) {
                                    $download_file_URL = esc_url( wp_nonce_url( add_query_arg( array( 'action' => 'downloadlog' ), admin_url( 'admin.php?page=magic-post-thumbnail-admin-display&module=logs' ) ), 'download_log' ) );
                                    $delete_file       = esc_url( wp_nonce_url( add_query_arg( array( 'action' => 'deletelog' ), admin_url( 'admin.php?page=magic-post-thumbnail-admin-display&module=logs' ) ), 'delete_log' ) );
                            ?>

                            <a href="<?php echo $download_file_URL; ?>" class="button button-primary"><?php esc_html_e( 'Download Log', 'magic-post-thumbnail' ); ?></a>

                            <a href="<?php echo $delete_file; ?>" class="delete-logs"><?php esc_html_e( 'Delete all logs', 'magic-post-thumbnail' ); ?></a>

                        </td>

                    </tr>

                    <tr valign="top">
                            <th scope="row">
                                <a href="#" class="show-settings"><?php esc_html_e( 'Show settings config', 'magic-post-thumbnail' ); ?></a>
                                <a href="#" class="hide-settings"><?php esc_html_e( 'Hide settings config', 'magic-post-thumbnail' ); ?></a><br/>
                                <a href="#" class="copy-settings"><?php esc_html_e( 'Copy settings config', 'magic-post-thumbnail' ); ?></a><br/>
                                <span class="copied"><?php esc_html_e( 'Copied !', 'magic-post-thumbnail' ); ?></span>
                            </th>
                            <td>

                            <?php } ?>

                        <div>

                            <?php
                                $settings_dump = array(
                                    $this->MPT_redact_settings( get_option( 'MPT_plugin_main_settings' ) ),
                                    $this->MPT_redact_settings( get_option( 'MPT_plugin_banks_settings' ) ),
                                    $this->MPT_redact_settings( get_option( 'MPT_plugin_compatibility_settings' ) ),
                                    $this->MPT_redact_settings( get_option( 'MPT_plugin_cron_settings' ) ),
                                    $this->MPT_redact_settings( get_option( 'MPT_plugin_rights_settings' ) ),
                                    $this->MPT_redact_settings( get_option( 'MPT_plugin_proxy_settings' ) ),
                                );
                                $settings = wp_json_encode( $settings_dump );
                            ?>
                            <pre id="logs-block" class="settings-logs"><?php echo esc_html( $settings ); ?></pre>
                        </div>
                            </td>
                    </tr>

                </tbody>
        </table>

        <?php submit_button(); ?>

    </form>

</div>
