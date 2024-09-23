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

            // Get the log file
            $current_file = $this->MPT_log_file( true );

            if( false !== $current_file ) {
                $log_file     = ABSPATH . 'wp-content/uploads/magic-post-thumbnail/logs/' . $current_file;
                $file_content = file_get_contents( $log_file );
            } else {
                $file_content =esc_html__( 'No log yet', 'mpt' );;
            }

        ?>

        <table id="general-options" class="form-table logs">
                <tbody>
                    <tr valign="top">
                            <th scope="row">
                                    <?php esc_html_e( 'Enable Logs', 'mpt' ); ?>
                            </th>
                            <td>
                                    <input data-switch="true" type="checkbox" name="MPT_plugin_logs_settings[logs]" id="enable_logs" value="true" <?php echo( !empty( $options['logs']) && $options['logs'] == 'true' )? 'checked': ''; ?> />
                            </td>
                    </tr>

                    <tr valign="top" class="show_logs" <?php echo(($options['logs'] != 'true') ? 'style="display:none;"': ''); ?>>
                        <th scope="row">
                                <?php esc_html_e( 'Logs history', 'mpt' ); ?>
                        </th>

                        <td>
                            <pre id="logs-block" class="text-white bg-dark"><?php echo $file_content; ?></pre>
                            <?php
                                if( false !== $current_file ) {
                                    $download_file_URL = esc_url( wp_nonce_url( add_query_arg( array( 'action' => 'downloadlog' ), admin_url( 'admin.php?page=magic-post-thumbnail-admin-display&module=logs' ) ), 'download_log' ) );
                                    $delete_file       = esc_url( wp_nonce_url( add_query_arg( array( 'action' => 'deletelog' ), admin_url( 'admin.php?page=magic-post-thumbnail-admin-display&module=logs' ) ), 'delete_log' ) );
                            ?>

                            <a href="<?php echo $download_file_URL; ?>" class="button button-primary"><?php esc_html_e( 'Download Log', 'mpt' ); ?></a>

                            <a href="<?php echo $delete_file; ?>" class="delete-logs"><?php esc_html_e( 'Delete all logs', 'mpt' ); ?></a>

                        </td>

                    </tr>

                    <tr valign="top">
                            <th scope="row">
                                <a href="#" class="show-settings"><?php esc_html_e( 'Show settings config', 'mpt' ); ?></a>
                                <a href="#" class="hide-settings"><?php esc_html_e( 'Hide settings config', 'mpt' ); ?></a><br/>
                                <a href="#" class="copy-settings"><?php esc_html_e( 'Copy settings config', 'mpt' ); ?></a><br/>
                                <span class="copied"><?php esc_html_e( 'Copied !', 'mpt' ); ?></span>
                            </th>
                            <td>

                            <?php } ?>

                        <div>

                            <?php
                                $main_settings 		    = array( get_option( 'MPT_plugin_main_settings' ) );
                                $banks_settings 		= array( get_option( 'MPT_plugin_banks_settings' ) );
                                $compatibility_settings	= array( get_option( 'MPT_plugin_compatibility_settings' ) );
                                $cron_settings			= array( get_option( 'MPT_plugin_cron_settings' ) );
                                $proxy_settings			= array( get_option( 'MPT_plugin_proxy_settings' ) );

                                $settings = json_encode(
                                    array_merge(
                                        $main_settings,
                                        $banks_settings,
                                        $compatibility_settings,
                                        $cron_settings,
                                        $proxy_settings
                                    ) );
                            ?>
                            <pre id="logs-block" class="settings-logs"><?php echo esc_html($settings); ?></pre>
                        </div>
                            </td>
                    </tr>

                </tbody>
        </table>

        <?php submit_button(); ?>

    </form>

</div>
