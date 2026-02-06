<?php
/**
 * AJAX controller.
 *
 * Handles AJAX endpoints used by the plugin (dry run, fetch posts, database
 * upgrade helpers).
 *
 * @package bbPress_Notify_Nospam
 */

defined( 'ABSPATH' ) || exit;

/* phpcs:disable PEAR.NamingConventions.ValidClassName.StartWithCapital,PEAR.NamingConventions.ValidClassName.Invalid */
/**
 * Controls AJAX requests.
 *
 * @author vinnyalves
 */
class bbPress_Notify_noSpam_Controller_Ajax extends bbPress_Notify_noSpam {

	/**
	 * Ajax request model instance.
	 *
	 * @var bbPress_Notify_noSpam_Model_Ajax_Request
	 */
	private $ar;

	/**
	 * Constructor.
	 */
	public function __construct() {
		if ( ! parent::is_admin() ) {
			return;
		}

		$this->load_lib( 'model/ajax_request' );

		add_action( 'wp_ajax_bbpnns_update_db', array( $this, 'update_db' ) );
		add_action( 'wp_ajax_bbpnns_dry_run_fetch_posts', array( $this, 'fetch_posts' ) );
		add_action( 'wp_ajax_bbpnns_dry_run_run_test', array( $this, 'run_dry_run' ) );
	}

	/**
	 * Run a dry-run test that triggers topic/reply hooks without sending notifications.
	 *
	 * Expects POST params: `post_type`, `post_id`, `nonce`.
	 *
	 * @return mixed
	 * @throws Exception When the nonce is invalid or other errors occur.
	 */
	public function run_dry_run() {
		$params = array(
			'post_type' => '',
			'post_id'   => '',
			'nonce'     => '',
		);

		// _init creates the model and helps with testing.
		$callback = null;
		$this->_init( $params, 'POST', $callback );

		// Sanitize expected inputs.
		$params['post_type'] = isset( $params['post_type'] ) ? sanitize_text_field( wp_unslash( $params['post_type'] ) ) : '';
		$params['post_id']   = isset( $params['post_id'] ) ? absint( $params['post_id'] ) : 0;
		$params['nonce']     = isset( $params['nonce'] ) ? sanitize_text_field( wp_unslash( $params['nonce'] ) ) : '';

		$settings = $this->load_lib( 'dal/settings_dao' )->load();

		$this->ar->is_success = false;
		try {
			$nonce = $params['nonce'];

			if ( ! wp_verify_nonce( $nonce, 'dry-run-test-nonce' ) ) {
				throw new Exception( __( 'Invalid nonce', 'bbpress-notify-nospam' ) );
			}

			// Stop bbpress from sending anything.
			add_filter( 'bbp_forum_subscription_mail_message', '__return_false' );
			add_filter( 'bbp_subscription_mail_message', '__return_false' );

			remove_action( 'bbp_new_reply', 'bbp_notify_topic_subscribers', 11 );
			remove_action( 'bbp_new_topic', 'bbp_notify_forum_subscribers', 11 );

			remove_action( 'bbp_new_reply', 'bbp_notify_topic_subscribers', 9999 );
			remove_action( 'bbp_new_topic', 'bbp_notify_forum_subscribers', 9999 );

			// Turn on dry_run.
			add_filter( 'bbpnns_dry_run', '__return_true', PHP_INT_MAX );

			$anonymous_data = array();
			// Trigger new post.
			if ( $this->get_topic_post_type() === $params['post_type'] ) {
				$topic_id     = $params['post_id'];
				$forum_id     = bbp_get_topic_forum_id( $topic_id );
				$topic_author = bbp_get_topic_author_id( $topic_id );

				do_action( 'bbp_new_topic', $topic_id, $forum_id, $anonymous_data, $topic_author );

				if ( $settings->background_notifications ) {
					do_action( 'bbpress_notify_bg_topic', $topic_id, $forum_id, $anonymous_data, $topic_author );
				}
			} else {
				$reply_id     = $params['post_id'];
				$topic_id     = bbp_get_reply_topic_id( $reply_id );
				$forum_id     = bbp_get_topic_forum_id( $topic_id );
				$reply_author = bbp_get_reply_author_id( $reply_id );

				do_action( 'bbp_new_reply', $reply_id, $topic_id, $forum_id, $anonymous_data, $reply_author );

				if ( $settings->background_notifications ) {
					do_action( 'bbpress_notify_bg_reply', $reply_id, $topic_id, $forum_id, $anonymous_data, $reply_author );
				}
			}

			// Read the trace.
			$trace = apply_filters( 'bbpnns_dry_run_trace_info', array() );

			$this->ar->is_success = true;
			$this->ar->data       = $trace;

		} catch ( Exception $e ) {
			// Set the model values.
			$this->ar->is_success = false;
			$this->ar->msg        = $e->getMessage();
		}

		return $this->_done();
	}

	/**
	 * Fetch topics for Dry-run.
	 *
	 * Fetch posts for dry-run selection.
	 *
	 * Expects POST params: `s`, `posts_per_page`, `paged`, `nonce`, `post_type`.
	 *
	 * @return mixed
	 * @throws Exception When nonce verification fails or other errors occur.
	 */
	public function fetch_posts() {
		$params = array(
			's'              => '',
			'posts_per_page' => -1,
			'paged'          => 1,
			'nonce'          => '',
			'post_type'      => '',
		);

		// _init creates the model and helps with testing.
		$callback = null;
		$this->_init( $params, 'POST', $callback );

		// Sanitize inputs.
		$params['s'] = isset( $params['s'] ) ? sanitize_text_field( wp_unslash( $params['s'] ) ) : '';
		// Allow negative values like -1 (no limit). Use intval() instead of absint()
		// which would convert -1 to 1.
		$params['posts_per_page'] = isset( $params['posts_per_page'] ) ? intval( $params['posts_per_page'] ) : -1;
		$params['paged']          = isset( $params['paged'] ) ? absint( $params['paged'] ) : 1;
		$params['post_type']      = isset( $params['post_type'] ) ? sanitize_text_field( wp_unslash( $params['post_type'] ) ) : '';

		$this->ar->is_success = true;
		try {
			$nonce = $params['nonce'];

			if ( ! wp_verify_nonce( $nonce, 'dry-run-post-nonce' ) ) {
				throw new Exception( __( 'Invalid nonce', 'bbpress-notify-nospam' ) );
			}

			$dao = $this->load_lib( 'dal/dry_run_dao' );

			$posts = array();

			// Build query args for the DAO.
			$query_args = array(
				's'              => $params['s'],
				'posts_per_page' => $params['posts_per_page'],
				'paged'          => $params['paged'],
			);

			if ( $this->get_topic_post_type() === $params['post_type'] ) {
				$posts = $dao->get_topics( $query_args );
			} else {
				$posts = $dao->get_replies( $query_args );
			}

			$results = array(
				'results'    => array(),
				'pagination' => array( 'more' => false ),
			);

			if ( ! empty( $posts ) ) {
				foreach ( $posts as $id => $title ) {
					$results['results'][] = array(
						'id'   => $id,
						'text' => $title,
					);
				}

				$results['total_count'] = count( $posts );
			}

			$this->ar->is_success = true;
			$this->ar->data       = $results;

		} catch ( Exception $e ) {
			// Set the model values.
			$this->ar->is_success = false;
			$this->ar->msg        = $e->getMessage();
		}

		// And print out the response.
		return $this->_done();
	}

	/**
	 * Update the settings to version 2.
	 *
	 * Run DB update (v2 conversion).
	 *
	 * @param string        $message Message passed by reference for testability.
	 * @param callable|null $callback Optional callback name.
	 * @return mixed
	 * @throws Exception When the nonce is invalid or upgrade fails.
	 */
	public function update_db( $message = '', $callback = null ) {
		$params = array(
			'message' => &$message,
			'nonce'   => '',
		);

		// _init creates the model and helps with testing.
		$callback = $callback ?? null;
		$this->_init( $params, 'POST', $callback );

		// Sanitize nonce.
		$params['nonce'] = isset( $params['nonce'] ) ? sanitize_text_field( wp_unslash( $params['nonce'] ) ) : '';

		try {

			$nonce = $params['nonce'];

			if ( ! wp_verify_nonce( $nonce, 'bbpnns_v2_conversion_needed' ) ) {
				throw new Exception( __( 'Invalid nonce', 'bbpress-notify-nospam' ) );
			}

			$conv = $this->load_lib( 'helper/converter', array( 'add_action' => false ) );
			if ( ! $conv->do_db_upgrade() ) {
				throw new Exception( __( 'There was a problem updating the database.', 'bbpress-notify-nospam' ) );
			}

			// Set the model values.
			$this->ar->is_success = true;
			// Keep the string translatable without embedding HTML; wrap with allowed HTML for output.
			$this->ar->msg = wp_kses_post( sprintf( '<strong>%s</strong>', __( 'Database update completed successfully!', 'bbpress-notify-nospam' ) ) );
		} catch ( Exception $e ) {
			// If there was an error, set it accordingly.
			$this->ar->is_success = false;
			$this->ar->msg        = $e->getMessage();
		}

		// And print out the response.
		return $this->_done();
	}

	/**
	 * Wrapper to check if we're in an ajax call.
	 *
	 * @return bool
	 */
	private function _doing_ajax() { // phpcs:ignore PSR2.Methods.MethodDeclaration.Underscore -- legacy internal method retained for backwards compatibility.
		return ( defined( 'DOING_AJAX' ) && DOING_AJAX );
	}

	/**
	 * Wrapper to fetch query params.
	 *
	 * @param array  $vars     Default vars and their defaults.
	 * @param string $method   HTTP method to inspect (GET|POST).
	 * @param string $callback Optional callback name passed by request.
	 */
	private function _init( &$vars = array(), $method = 'POST', &$callback = null ) { // phpcs:ignore PSR2.Methods.MethodDeclaration.Underscore -- legacy internal method retained for backwards compatibility.
		$this->ar = new bbPress_Notify_noSpam_Model_Ajax_Request();
		$params   = array();

		// phpcs:disable WordPress.Security.NonceVerification.Recommended,WordPress.Security.NonceVerification.Missing -- Nonce verification is performed by the calling method where appropriate.
		if ( 'GET' === $method && isset( $_GET ) ) {
			$params = wp_unslash( $_GET );
		} elseif ( 'POST' === $method && isset( $_POST ) ) {
			$params = wp_unslash( $_POST );
		}
		// phpcs:enable WordPress.Security.NonceVerification.Missing

		if ( isset( $params ) ) {
			$vars = shortcode_atts( $vars, $params );

			if ( isset( $params['callback'] ) ) {
				$callback = trim( sanitize_text_field( $params['callback'] ) );
			}
		}

		$this->ar->callback = $callback;
	}

	/**
	 * Output or return Ajax Request model.
	 *
	 * @return string|void
	 */
	private function _done() { // phpcs:ignore PSR2.Methods.MethodDeclaration.Underscore -- legacy internal method retained for backwards compatibility.
		if ( $this->_doing_ajax() ) {
			$this->ar->output();
			wp_die();
		}

		ob_start();
		$this->ar->output();
		return ob_get_clean();
	}
}

/*
 * End of file class-ajax.php
 */
/* Location: bbpress-notify-nospam/includes/controller/class-ajax.php */
