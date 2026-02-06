<?php
/**
 * Admin core controller.
 *
 * Controller for admin core behaviour (meta boxes, links and upgrades).
 *
 * @package bbPress_Notify_Nospam
 */

defined( 'ABSPATH' ) || exit;

if ( class_exists( 'bbPress_Notify_noSpam_Controller_Admin_Core' ) ) {
	return;
}

/* phpcs:disable PEAR.NamingConventions.ValidClassName.StartWithCapital,PEAR.NamingConventions.ValidClassName.Invalid */
/**
 * Admin core controller class.
 *
 * Handles admin-related features such as adding plugin action links,
 * registering notification meta boxes, and deactivating legacy plugins.
 *
 * @since 1.0
 */
class bbPress_Notify_noSpam_Controller_Admin_Core extends bbPress_Notify_noSpam {

	/**
	 * Constructor.
	 *
	 * @since 1.0
	 */
	public function __construct() {
		// Add Settings link to the plugin page.
		add_filter( 'plugin_action_links', array( $this, 'plugin_action_links' ), 10, 2 );

		// Deactivate original bbPress Notify if found.
		add_action( 'admin_init', array( $this, 'deactivate_old' ) );

		// Notification meta boxes if needed.
		add_action( 'add_meta_boxes', array( $this, 'add_notification_meta_box' ), 10 );

		// Required by BuddyPress bridge Add-on and others.
		do_action( 'bbpnns_register_settings' );
	}

	/**
	 * Register notification meta boxes.
	 *
	 * @since 1.4
	 * @return void
	 */
	public function add_notification_meta_box() {
		$view = $this->load_lib( 'view/metaboxes' );

		add_meta_box(
			'send_notification',
			__( 'Notifications', 'bbpress-notify-nospam' ),
			array( $view, 'notification_meta_box_content' ),
			$this->get_topic_post_type(),
			'side',
			'high'
		);

		add_meta_box(
			'send_notification',
			__( 'Notifications', 'bbpress-notify-nospam' ),
			array( $view, 'notification_meta_box_content' ),
			$this->get_reply_post_type(),
			'side',
			'high'
		);
	}

	/**
	 * Add plugin action links on the plugins page.
	 *
	 * @since 1.4
	 * @param array  $links Existing action links.
	 * @param string $file  Plugin file.
	 * @return array Modified action links.
	 */
	public function plugin_action_links( $links, $file ) {
		if ( plugin_basename( __DIR__ . '/bbpress-notify-nospam.php' ) === $file ) {
			$links[] = '<a href="' . admin_url( 'admin.php?page=bbpress#' . $this->settings_section ) . '">' . __( 'Settings', 'bbpress-notify-nospam' ) . '</a>';
		}

		return $links;
	}

	/**
	 * Deactivate the non-nospam version of bbPress Notify.
	 *
	 * @since 1.0
	 * @return void
	 */
	public function deactivate_old() {
		$old_plugin = 'bbpress-notify/bbpress-notify.php';
		if ( is_plugin_active( $old_plugin ) ) {
			deactivate_plugins( $old_plugin );
		}
	}
}
/* phpcs:enable PEAR.NamingConventions.ValidClassName.StartWithCapital,PEAR.NamingConventions.ValidClassName.Invalid */

/* End of file class-admin-core.php */
