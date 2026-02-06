<?php
/**
 * Metaboxes view class.
 *
 * Minimal docblock and escaping for outputs.
 *
 * @package bbPress_Notify_NoSpam
 * Location: includes/view/class-bbpress-notify-nospam-view-metaboxes.php
 */

defined( 'ABSPATH' ) || die( 'No direct access allowed' );

// phpcs:disable PEAR.NamingConventions.ValidClassName.StartWithCapital, PEAR.NamingConventions.ValidClassName.Invalid, Squiz.Commenting.ClassComment.Missing
// phpcs:ignore PEAR.NamingConventions.ValidClassName.Invalid -- legacy class name
/**
 * Class bbPress_Notify_noSpam_View_Metaboxes
 */
class bbPress_Notify_noSpam_View_Metaboxes extends bbPress_Notify_noSpam {

	/**
	 * Settings container.
	 *
	 * @var object
	 */
	private $settings;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->settings = $this->load_lib( 'dal/settings_dao' )->load();
	}

	/**
	 * Render the notification metabox content.
	 *
	 * @since 1.4
	 * @param WP_Post $post The post object.
	 */
	public function notification_meta_box_content( $post ) {
		$type = ( $post->post_type === $this->get_topic_post_type() ) ? 'topic' : 'reply';

		$default = $this->settings->{"default_{$type}_notification_checkbox"};
		$checked = checked( $default, true, false );

		wp_create_nonce( "bbpress_send_{$type}_notification_nonce" );

		wp_nonce_field( "bbpress_send_{$type}_notification_nonce", "bbpress_send_{$type}_notification_nonce" );
		printf(
			'<label><input type="checkbox" name="bbpress_notify_send_notification" %s> %s</label>',
			esc_attr( $checked ),
			esc_html__( 'Send notification.', 'bbpress-notify-nospam' )
		);
	}
}

/*
 * End of file metaboxes.class.php
 * Location: bbpress-notify-nospam/includes/view/metaboxes.class.php
 */

// phpcs:enable
