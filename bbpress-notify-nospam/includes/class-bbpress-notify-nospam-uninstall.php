<?php
/**
 * Uninstall helper for bbPress Notify (No-Spam)
 *
 * @package bbPress_Notify_Nospam
 */

defined( 'ABSPATH' ) || exit;

if ( class_exists( 'bbPress_Notify_noSpam_Uninstall' ) ) {
	return;
}

/* phpcs:disable PEAR.NamingConventions.ValidClassName.StartWithCapital,PEAR.NamingConventions.ValidClassName.Invalid */
/**
 * Uninstall helper class for bbPress Notify (No-Spam).
 *
 * Handles removal of plugin options during uninstall.
 */
class bbPress_Notify_noSpam_Uninstall extends bbPress_Notify_noSpam {

	/**
	 * Topic post type used when building option names.
	 *
	 * @var string
	 */
	protected $bbpress_topic_post_type = '';

	/**
	 * Reply post type used when building option names.
	 *
	 * @var string
	 */
	protected $bbpress_reply_post_type = '';

	/**
	 * Constructor.
	 *
	 * If bbPress functions are available, run uninstall actions immediately,
	 * otherwise defer until `plugins_loaded` so the helpers are available.
	 */
	public function __construct() {
		if ( function_exists( 'bbp_get_topic_post_type' ) ) {
			$this->do_stuff();
		} else {
			add_action( 'plugins_loaded', array( $this, 'do_stuff' ) );
		}
	}

	/**
	 * Perform uninstall actions (determine post types and delete options).
	 */
	public function do_stuff() {
		$this->bbpress_topic_post_type = function_exists( 'bbp_get_topic_post_type' ) ? bbp_get_topic_post_type() : 'topic';
		$this->bbpress_reply_post_type = function_exists( 'bbp_get_reply_post_type' ) ? bbp_get_reply_post_type() : 'reply';

		$this->delete_options();
	}

	/**
	 * Delete plugin-related options from the database.
	 */
	public function delete_options() {
		$topic = $this->bbpress_topic_post_type;
		$reply = $this->bbpress_reply_post_type;

		$options = array(
			'bbpnns-dismissed-1_7_1',
			'bbpnns-opt-out-msg',
			'bbpress-notify-pro-dismissed',
			'bbpress_notify_newtopic_background',
			'bbpress_notify_newreply_background',
			'bbpress_notify_newtopic_recipients',
			'bbpress_notify_newreply_recipients',
			'bbpress_notify_newtopic_email_subject',
			'bbpress_notify_newtopic_email_body',
			'bbpress_notify_newreply_email_subject',
			'bbpress_notify_newreply_email_body',
			"bbpress_notify_default_{$topic}_notification",
			"bbpress_notify_default_{$reply}_notification",
			'bbpress_notify_encode_subject',
			'bbpnns_notify_authors_topic',
			'bbpnns_notify_authors_reply',
			'bbpnns_hijack_bbp_subscriptions_forum',
			'bbpnns_hijack_bbp_subscriptions_topic',
			'bbpress_notify_message_type',
			'bbpnns_dismissed_admin_notices',
			'bbPress_Notify_noSpam',
			'bbpnns_v2_conversion_complete',
		);

		foreach ( $options as $option ) {
			delete_option( $option );
		}
	}
}
/* phpcs:enable PEAR.NamingConventions.ValidClassName.StartWithCapital,PEAR.NamingConventions.ValidClassName.Invalid */
