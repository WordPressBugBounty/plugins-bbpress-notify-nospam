<?php
/**
 * Login controller for bbPress Notify No-Spam.
 *
 * @package bbPress_Notify_NoSpam
 */

defined( 'ABSPATH' ) || die( 'No direct access allowed' );

if ( class_exists( 'bbPress_Notify_noSpam_Controller_Login' ) ) {
	return;
}

// phpcs:disable PEAR.NamingConventions.ValidClassName.StartWithCapital,PEAR.NamingConventions.ValidClassName.Invalid
/**
 * Controls login functionality for private forums.
 *
 * @author vinnyalves
 */
class bbPress_Notify_noSpam_Controller_Login extends bbPress_Notify_noSpam {

	/**
	 * Cache of forum visibility keyed by forum ID.
	 *
	 * @var array
	 */
	private $forums = array();


	/**
	 * Constructor.
	 *
	 * Registers hooks and filters.
	 *
	 * @return void
	 */
	public function __construct() {
		add_action( 'template_redirect', array( $this, 'maybe_handle_login' ), 100001 );

		add_filter( 'bbpnns_topic_url', array( $this, 'maybe_add_redirect' ), 10, 4 );
		add_filter( 'bbpnns_reply_url', array( $this, 'maybe_add_redirect' ), 10, 4 );
		add_filter( 'bbpnns_topic_reply', array( $this, 'maybe_add_redirect' ), 10, 3 );
	}


	/**
	 * If necessary, sends users to the login URL with a redirect to wherever they wanted to go.
	 *
	 * @return void|string Returns redirect URL during testing.
	 */
	public function maybe_handle_login() {
		if ( isset( $_GET['bbpnns-login'] ) ) {
			$redirect_to = isset( $_GET['redirect_to'] ) ? sanitize_text_field( wp_unslash( $_GET['redirect_to'] ) ) : '';

			// Validate redirect target to a safe URL (fallback to home_url on invalid).
			$redirect_to = wp_validate_redirect( $redirect_to, home_url( '/' ) );

			if ( ! is_user_logged_in() ) {
				$login_url = apply_filters( 'bbpnns-login-url', wp_login_url( $redirect_to ), $_GET );

				if ( true === apply_filters( 'BBPNNS_TESTING', false ) ) { // phpcs:ignore WordPress.NamingConventions.ValidHookName.NotLowercase
					return $login_url;
				}

				wp_safe_redirect( $login_url );

				exit();
			} else {
				if ( true === apply_filters( 'BBPNNS_TESTING', false ) ) { // phpcs:ignore WordPress.NamingConventions.ValidHookName.NotLowercase
					return $redirect_to;
				}

				wp_safe_redirect( $redirect_to );

				exit();
			}
		}
	}


	/**
	 * Check if a login URL is required.
	 *
	 * @param string     $url      The current URL.
	 * @param int        $post_id  The post ID.
	 * @param string     $title    The post title.
	 * @param int|string $forum_id Optional forum ID.
	 * @return string
	 */
	public function maybe_add_redirect( $url, $post_id, $title, $forum_id = '' ) {
		$post_url = $url;

		if ( ! $forum_id ) {
			$forum_id = get_post_meta( $post_id, '_bbp_forum_id', true );
		}

		if ( ! isset( $this->forums[ $forum_id ] ) ) {
			$this->forums[ $forum_id ] = bbp_get_forum_visibility( $forum_id );
		}

		if ( 'publish' !== $this->forums[ $forum_id ] ) {
			$url = esc_url_raw(
				add_query_arg(
					array(
						'bbpnns-login' => 1,
						'redirect_to'  => $url,
					),
					home_url( '/' )
				)
			);
			$url = apply_filters( 'bbpnns_redirect_url', $url, $post_url, $post_id, $title, $forum_id );
		}

		return $url;
	}
}
// phpcs:enable PEAR.NamingConventions.ValidClassName.StartWithCapital,PEAR.NamingConventions.ValidClassName.Invalid

/*
End of file class-login.php
*/
/* Location: bbpress-notify-nospam/includes/controller/class-login.php */
