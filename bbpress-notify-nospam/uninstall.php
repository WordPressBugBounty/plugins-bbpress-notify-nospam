<?php
/**
 * BBPress Notify (No-Spam) Uninstall
 *
 * Bootstrap to run uninstall helper located in includes/.
 *
 * @package bbPress_Notify_Nospam
 */

// Protect against direct access and ensure this is an uninstall run.
if ( ! defined( 'bbPress_Notify_noSpam_TEST_UNINSTALL' ) && ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit();
}

// Include the main plugin file only if the public helper isn't already available.
$main_plugin = __DIR__ . '/bbpress-notify-nospam.php';
if ( ! function_exists( 'bbpnns' ) && file_exists( $main_plugin ) ) {
	require_once $main_plugin;
}

// Load the uninstall helper class from includes/controller/ (WPCS-style filename).
$uninstall_file = __DIR__ . '/includes/controller/class-bbpress-notify-nospam-uninstall.php';
if ( file_exists( $uninstall_file ) ) {
	require_once $uninstall_file;
}

// Instantiate the uninstall helper if available.
if ( class_exists( 'bbPress_Notify_noSpam_Uninstall' ) ) {
	new bbPress_Notify_noSpam_Uninstall();
}

/* End of uninstall.php */
