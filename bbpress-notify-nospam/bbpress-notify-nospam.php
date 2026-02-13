<?php
/**
 * Plugin loader for bbPress Notify (No-Spam) (v3 refactor).
 *
 * This file intentionally contains only the plugin header and a tiny
 * bootstrap that loads the refactored class files under `includes/`.
 *
 * @package bbPress_Notify_NoSpam
 */

/*
 * Plugin Name: bbPress Notify (No-Spam)
 * Description: Sends email notifications upon topic/reply creation, as long as it's not flagged as spam. If you like this plugin, help share the trust and rate it!
 * Version:     3.0.2
 * Author:      Vinny Alves (UseStrict Consulting)
 * License:     GNU General Public License, v2 ( or newer )
 * License URI: http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * Text Domain: bbpress-notify-nospam
 * Domain Path: /lang
 */

defined( 'ABSPATH' ) || exit;

// Load autoloader and refactored class files.
require_once __DIR__ . '/includes/autoload.php';

// Fallback: ensure the main class is available if autoload failed for any reason.
if ( ! class_exists( 'bbPress_Notify_noSpam' ) && file_exists( __DIR__ . '/includes/controller/class-bbpress-notify-nospam.php' ) ) {
	require_once __DIR__ . '/includes/controller/class-bbpress-notify-nospam.php';
}
// phpcs:ignore WordPress.NamingConventions.ValidVariableName -- historic global used widely in plugin
$bbPress_Notify_noSpam = bbpnns();

/**
 * Helper wrapper to bootstrap the plugin and return the instance.
 *
 * @return bbPress_Notify_noSpam|false
 */
function bbpnns() {
	return bbPress_Notify_noSpam::bootstrap();
}

/*
 * End of file bbpress-notify-nospam.php
 */

/*
 * Location: bbpress-notify-nospam/bbpress-notify-nospam.php
 */
/* Location: bbpress-notify-nospam/bbpress-notify-nospam.php */
