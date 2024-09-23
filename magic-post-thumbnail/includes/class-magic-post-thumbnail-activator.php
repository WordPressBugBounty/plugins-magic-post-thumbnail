<?php

/**
 * Fired during plugin activation
 *
 * @link       https://magic-post-thumbnail.com/
 * @since      1.0.0
 *
 * @package    Magic_Post_Thumbnail
 * @subpackage Magic_Post_Thumbnail/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Magic_Post_Thumbnail
 * @subpackage Magic_Post_Thumbnail/includes
 * @author     Magic Post Thumbnail <contact@magic-post-thumbnail.com>
 */
class Magic_Post_Thumbnail_Activator {

	/**
	 * Add activation date during activation
	 *
	 * @since    1.0.0
	 */
	public static function activate() {

		if (!get_option('MPT_plugin_activation_date')) {
			update_option('MPT_plugin_activation_date', time());
		}

	}

}
