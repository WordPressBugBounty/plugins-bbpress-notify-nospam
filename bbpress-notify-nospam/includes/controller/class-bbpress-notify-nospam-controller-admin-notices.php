<?php
/**
 * Admin notices controller.
 *
 * Handles display, dismissal and persistence of admin notices for the plugin.
 *
 * @package bbPress_Notify_Nospam
 */

defined( 'ABSPATH' ) || exit;

if ( class_exists( 'bbPress_Notify_noSpam_Controller_Admin_Notices' ) ) {
	return;
}

/* phpcs:disable PEAR.NamingConventions.ValidClassName.StartWithCapital,PEAR.NamingConventions.ValidClassName.Invalid */
/**
 * Controls Admin Notices.
 *
 * @author vinnyalves
 */
class bbPress_Notify_noSpam_Controller_Admin_Notices extends bbPress_Notify_noSpam {

	/**
	 * Holds our notices
	 *
	 * @var array
	 */
	protected $notices = array();


	/**
	 * All available messages
	 *
	 * @var array
	 */
	private static $msg_pool;


	/**
	 * The query string element that holds the messages
	 *
	 * @var unknown
	 */
	private $query_element;



	/**
	 * Constructor.
	 *
	 * @param array $params Optional constructor parameters.
	 */
	public function __construct( $params = array() ) {
		if ( ! parent::is_admin() ) {
			return;
		}

		$this->query_element = get_parent_class( $this );

		add_action( 'admin_notices', array( $this, 'show_notices' ) );

		// Captures redirects after posts like when saving metaboxes.
		// add_filter( 'redirect_post_location', array( $this, 'capture_redirect' ) ).
		add_filter( 'wp_redirect', array( $this, 'capture_redirect' ) );

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- benign: query param is used only to carry notice keys after redirect and is sanitized later.
		if ( isset( $_GET[ $this->query_element ] ) ) {
			add_filter( 'post_updated_messages', array( $this, 'show_notices' ) );
		}

		// Dismiss notice.
		add_action( 'wp_ajax_bbpnns-notice-handler', array( $this, 'handle_notice_dismissal' ) );
	}


	/**
	 * Wrapper for _set_msg
	 *
	 * @param string $code Message code.
	 * @param bool   $die_on_error Whether to die on error.
	 */
	public function set_notice( $code, $die_on_error = false ) {
		$msg = $this->get_message( $code );

		if ( true === $die_on_error ) {
			wp_die( wp_kses_post( $msg->msg ) );
		} else {
			$this->_set_msg( $code );
		}
	}


	/**
	 * Internal notice setter.
	 *
	 * Adds $code to the settings API message div or queues it for output.
	 *
	 * @param string $code Message code.
	 */
	/**
	 * Internal notice setter.
	 *
	 * Adds a message code to the settings API message queue or to the internal
	 * notices array for later output.
	 *
	 * @param string $code Message code.
	 * @return void
	 */
	private function _set_msg( $code ) { // phpcs:ignore PSR2.Methods.MethodDeclaration.Underscore -- legacy internal method retained for backwards compatibility.
		global $pagenow;

		// Maybe use Settings API.
		// If on the settings pages, use the Settings API so messages appear
		// in the standard WP location for settings notices.
		if ( 'options.php' === $pagenow || 'options-general.php' === $pagenow ) {
			$msg = $this->get_message( $code );

			// Maybe defer setting the error.
			if ( ! function_exists( 'add_settings_error' ) ) {
				add_action(
					'admin_notices',
					function () use ( $code, $msg ) {
						add_settings_error( $this->settings_name, $code, $msg->msg, $msg->type );
					},
					-100
				);
			} else {
				add_settings_error( $this->settings_name, $code, $msg->msg, $msg->type );
			}
		}
		// Make sure we get unique notices.
		$this->notices[ $code ] = true;
	}


	/**
	 * Displays any cached notices
	 */
	/**
	 * Displays any cached notices.
	 *
	 * @param array $messages Messages passed from filters (optional).
	 * @return array Array of messages when used as a filter, otherwise original messages.
	 */
	public function show_notices( $messages = array() ) {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- benign: value is sanitized and only used to display admin notices.
		$get = wp_unslash( $_GET );
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- benign: value is sanitized and only used to display admin notices.
		if ( isset( $get[ $this->query_element ] ) ) {
			$raw  = $get[ $this->query_element ];
			$keys = explode( ',', trim( sanitize_text_field( $raw ) ) );
			if ( ! empty( $keys ) ) {
				$this->notices = array_combine( $keys, $keys );
			}
		}

		foreach ( array_keys( $this->notices ) as $code ) {
			$msg         = $this->get_message( $code );
			$dismissable = isset( $msg->is_dismissible ) && $msg->is_dismissible ? ' is-dismissible' : '';
			$nonce       = wp_create_nonce( $code );

			$div = sprintf(
				'<div id="%s" class="%s" data-nonce="%s"><p>%s</p></div>',
				esc_attr( $code ),
				esc_attr( $msg->type . $dismissable ),
				esc_attr( $nonce ),
				wp_kses_post( $msg->msg )
			);

			if ( doing_filter( 'post_updated_messages' ) ) {
				$messages[] = $div;
			} else {
				echo wp_kses_post( $div );
				unset( $this->notices[ $code ] );
			}
		}

		return $messages;
	}



	/**
	 * Clears notices
	 */
	public function clear_notices() {
		$this->notices = array();
	}


	/**
	 * Keeps state between redirects
	 *
	 * @param string $location
	 * @return string
	 */
	/**
	 * Keeps state between redirects by appending notice keys to the redirect URL.
	 *
	 * @param string $location Redirect location.
	 * @return string
	 */
	public function capture_redirect( $location ) {
		if ( ! $this->has_notices() ) {
			return esc_url_raw( remove_query_arg( $this->query_element, $location ) );
		}

		$keys = join( ',', array_keys( $this->notices ) );
		return esc_url_raw( add_query_arg( $this->query_element, $keys, $location ) );
	}


	/**
	 * Access to the message pool
	 *
	 * @param string $code
	 * @return multitype:StdClass
	 */
	/**
	 * Access a single message from the pool.
	 *
	 * @param string $code Message code.
	 * @return object
	 */
	public function get_message( $code ) {
		if ( ! isset( self::$msg_pool ) ) {
			$this->build_notice_pool();
		}

		if ( ! isset( self::$msg_pool[ $code ] ) ) {
			/* translators: 1: invalid message code */
			wp_die( esc_html( sprintf( __( 'Invalid message code %s', 'bbpress-notify-nospam' ), $code ) ) );
		}

		return self::$msg_pool[ $code ];
	}


	/**
	 * Allows checking if there are notices
	 *
	 * @return boolean
	 */
	public function has_notices() {
		return ! empty( $this->notices );
	}



	/**
	 * Dismiss a notice
	 */
	/**
	 * AJAX handler to dismiss a notice.
	 */
	public function handle_notice_dismissal() {
		$notice_id = '';
		if ( isset( $_POST['notice_id'] ) ) {
			$notice_id = sanitize_text_field( wp_unslash( $_POST['notice_id'] ) );
		}

		if ( ! $notice_id || ! isset( $this->notices[ $notice_id ] ) ) {
			wp_die( esc_html__( "I don't recognize that notice!", 'bbpress-notify-nospam' ) );
		}

		if ( check_ajax_referer( 'bbpnns-notice-nonce_' . $notice_id, 'nonce' ) ) {
			$dismissed               = get_option( 'bbpnns_dismissed_admin_notices', array() );
			$dismissed[ $notice_id ] = true;

			update_option( 'bbpnns_dismissed_admin_notices', $dismissed );
		}

		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			exit( 0 );
		}
	}


	/**
	 * Returns array of common notice objects.
	 *
	 * @param array $notices Optional notices to merge into the pool.
	 * @return array
	 */
	public function get_notice_pool( $notices = array() ) {
		// Not all classes get reloaded after wp_redirect, so add those messages here.
		$pool = array_merge(
			$notices,
			array(
				'invalid-postid'              => (object) array(
					'type' => 'error',
					'msg'  => __( 'Invalid post_id.', 'bbpress-notify-nospam' ),
				),
				'bad-params'                  => (object) array(
					'is_dismissible' => true,
					'type'           => 'notice notice-warning',
					'msg'            => __( 'Invalid parameter type.', 'bbpress-notify-nospam' ),
				),
				'old-notify-deactivated'      => (object) array(
					'type' => 'error',
					'msg'  => __( 'The old bbpnns has been deactivated in favor of bbpnns.', 'bbpress-notify-nospam' ),
				),
				'bbpnns_v2_conversion_needed' => (object) array(
					'type'           => 'error',
					'msg'            => __(
						"<div><strong>We need to convert your bbpnns v1.x data into the v2.x format.</strong>\n<a href=\"#\" id=\"bbpnns-convert-v1-to-v2\" class=\"button button-primary\">Run Update</a><div class=\"bbpnns_spinner\"></div></div>",
						'bbpress-notify-nospam'
					),
					'is_dismissible' => true,
				),

			)
		);

		// Allow other plugins to modify/augment the resulting pool.
		$tag  = $this->query_element . '_notice_pool';
		$pool = apply_filters( $tag, $pool );

		return $pool;
	}


	/**
	 * Build and store the notice pool into the static property.
	 *
	 * Other code should use `get_msg_pool()` to read the resolved pool.
	 *
	 * @return array
	 */
	public function build_notice_pool() {
		$pool = $this->get_notice_pool();
		self::set_msg_pool( (array) $pool );
		return self::get_msg_pool();
	}

	/**
	 * Public setter for the resolved message pool.
	 *
	 * @param array $pool Resolved pool to set.
	 */
	public static function set_msg_pool( $pool ) {
		self::$msg_pool = (array) $pool;
	}

	/**
	 * Public getter for the resolved message pool.
	 *
	 * @return array
	 */
	public static function get_msg_pool() {
		return isset( self::$msg_pool ) ? self::$msg_pool : array();
	}
}

/* phpcs:enable PEAR.NamingConventions.ValidClassName.StartWithCapital,PEAR.NamingConventions.ValidClassName.Invalid */

/* End of file class-admin-notices.php */
