<?php
/**
 * BbPress Notify (No-Spam) - Controller Common Core.
 *
 * Conservative file-level docblock to satisfy PHPCS file comment requirement.
 *
 * @package bbpress-notify-nospam
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'No direct access allowed' );
}

/* phpcs:disable Squiz.Commenting.InlineComment.InvalidEndChar, Squiz.Commenting.BlockComment.NoEmptyLineBefore, Squiz.Commenting.BlockComment.CloserSameLine, WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase, PEAR.NamingConventions.ValidClassName.StartWithCapital, PEAR.NamingConventions.ValidClassName.Invalid, WordPress.NamingConventions.ValidClassName */

if ( class_exists( 'bbPress_Notify_noSpam_Controller_Common_Core' ) ) {
	return;
}
/**
 * Controller for BbPress Notify (No-Spam) common core.
 *
 * @author vinnyalves
 * @phpcsSuppress PEAR.NamingConventions.ValidClassName.StartWithCapital
 * @phpcsSuppress PEAR.NamingConventions.ValidClassName.Invalid
 * @phpcsSuppress WordPress.NamingConventions.ValidClassName
 */
class bbPress_Notify_noSpam_Controller_Common_Core extends bbPress_Notify_noSpam {
	/**
	 * BbPress forum post type slug.
	 *
	 * @var string
	 */
	public $bbpress_forum_post_type;

	/**
	 * BbPress topic post type slug.
	 *
	 * @var string
	 */
	public $bbpress_topic_post_type;

	/**
	 * BbPress reply post type slug.
	 *
	 * @var string
	 */
	public $bbpress_reply_post_type;

	/**
	 * Plugin settings object.
	 *
	 * @var object
	 */
	public $settings;

	/**
	 * Message type: 'plain', 'html', 'multipart'.
	 *
	 * @var string
	 */
	private $message_type;

	/**
	 * Character set for blog.
	 *
	 * @var string
	 */
	private $charset;

	/**
	 * Alternate plain-text body when sending multipart emails.
	 *
	 * @var string
	 */
	public $AltBody = '';

	/**
	 * Whether the plugin is running in cron context.
	 *
	 * @var bool
	 */
	private $doing_cron = false;

	/**
	 * Forum hierarchy cache.
	 *
	 * @var array
	 */
	private $forum_hierarchy = array();

	/**
	 * Action Scheduler enabled flag.
	 *
	 * @var bool
	 */
	private $action_scheduler_enabled = false;

	/**
	 * Cache of users in OK roles.
	 *
	 * @var array
	 */
	private $users_in_roles = array();

	/**
	 * Used to store the recipients who have already gotten or will get our notifications (in case of bg notifications),
	 * so we can remove them from bbpress' $to_email in bbp_subscription_to_email filter.
	 *
	 * @var array
	 */
	public $queued_recipients = array();

	/**
	 * Stored forum subscribers when loading core subscribers in get_recipients() so we can
	 * optionally auto-subscribe them to a newly created topic.
	 *
	 * @var array
	 */
	public $forum_subscribers = array();

	/**
	 * Error returned by wp_mail when failing.
	 *
	 * @var string
	 */
	private $wp_mail_error = 'Unknown Error';

	/**
	 * Constructor.
	 *
	 * Sets up post type slugs, settings and plugin hooks.
	 */
	public function __construct() {
		$this->bbpress_forum_post_type = $this->get_forum_post_type();
		$this->bbpress_topic_post_type = $this->get_topic_post_type();
		$this->bbpress_reply_post_type = $this->get_reply_post_type();

		$this->doing_cron = ( defined( 'DOING_CRON' ) && DOING_CRON );

		$this->settings = $this->load_lib( 'dal/settings_dao' )->load();

		$this->action_scheduler_enabled = class_exists( 'ActionScheduler' ) && $this->settings->use_action_scheduler;

		// This cannot be in is_admin() because it needs to handle future publishing, which doesn't have is_admin() status
		add_action( 'save_post', array( $this, 'notify_on_save' ), 10, 2 );

		add_action( 'bbpnns_dry_run_trace', array( $this, 'trace' ), 10, 1 );

		// Triggers the notifications on new topics
		if ( $this->settings->background_notifications ) {
			//
			// TOPICS #####
			//

			// Store topic vars for wp-cron
			add_action( 'bbp_new_topic', array( $this, 'bg_notify_new_topic' ), PHP_INT_MAX, 4 );

			// Support for bbpress v2.6 moderation functionality.
			add_action( 'bbp_approved_topic', array( $this, 'bg_notify_new_topic' ), 100, 1 );

			// Keep core bbpress from notifying our recipients
			add_action( 'bbp_new_topic', array( $this, 'bg_filter_topic_recipients' ), 1000, 4 );

			// Called by wp-cron or Action Scheduler
			add_action( 'bbpress_notify_bg_topic', array( $this, 'notify_new_topic' ), 10, 4 );

			//
			// REPLIES ####
			//

			// Store reply vars for wp-cron
			add_action( 'bbp_new_reply', array( $this, 'bg_notify_new_reply' ), PHP_INT_MAX, 7 );

			// Support for bbpress v2.6 moderation functionality.
			add_action( 'bbp_approved_reply', array( $this, 'bg_notify_new_reply' ), 100, 1 );

			// Keep core bbpress from notifying our recipients
			add_action( 'bbp_new_reply', array( $this, 'bg_filter_reply_recipients' ), 1000, 7 );

			// Called by wp-cron or Action Scheduler
			add_action( 'bbpress_notify_bg_reply', array( $this, 'notify_new_reply' ), 10, 7 );
		} else {
			//
			// TOPICS #####
			//

			// bbPress is 11, so we're down to 10 so we can get a list of people who have already been notified by us
			// and remove them from bbpress' core notifications
			add_action( 'bbp_new_topic', array( $this, 'notify_new_topic' ), PHP_INT_MAX, 4 );

			// Support for bbpress v2.6 moderation functionality.
			add_action( 'bbp_approved_topic', array( $this, 'notify_new_topic' ), 10, 1 );

			//
			// REPLIES ####
			//

			// bbPress is 11, so we're down to 10 so we can get a list of people who have already been notified by us
			// and remove them from bbpress' core notifications
			add_action( 'bbp_new_reply', array( $this, 'notify_new_reply' ), PHP_INT_MAX, 7 );

			// Support for bbpress 2.6 moderation functionality.
			add_action( 'bbp_approved_reply', array( $this, 'notify_new_reply' ), 10, 1 );
		}

		if ( $this->bbpnns_is_in_effect() ) {
			// Stop core subscriptions in its tracks
			add_filter( 'bbp_forum_subscription_user_ids', '__return_false', PHP_INT_MAX, 3 );
			add_filter( 'bbp_forum_subscription_mail_message', '__return_false' );
			add_action( 'init', array( $this, 'remove_core_forum_notification' ), 10 );

			add_filter( 'bbp_topic_subscription_user_ids', '__return_false', PHP_INT_MAX, 3 );
			add_filter( 'bbp_subscription_mail_message', '__return_false' );
			add_action( 'init', array( $this, 'remove_core_topic_notification' ), 10 );

		}

		// Munge bbpress_notify_newpost_recipients if forum is hidden
		add_filter( 'bbpress_notify_recipients_hidden_forum', array( $this, 'munge_newpost_recipients' ), 10, 3 );

		// Allow other plugins to fetch available topic tags
		add_filter( 'bbpnns_available_tags', array( $this, 'get_available_tags' ), 10, 2 ); // deprecated, but still works
		add_filter( 'bbpnns_available_topic_tags', array( $this, 'get_available_topic_tags' ), 10, 2 );

		// Allow other plugins to fetch available reply tags
		add_filter( 'bbpnns_available_reply_tags', array( $this, 'get_available_reply_tags' ), 10, 2 );

		add_filter( 'bbpnns_is_in_effect', array( $this, 'bbpnns_is_in_effect' ), 10, 2 );

		// Whether to auto-subscribe new users to forums
		if ( $this->settings->forums_auto_subscribe_new_users ) {
			add_action( 'user_register', array( $this, 'forums_auto_subscribe_new_user' ), 10, 1 );
		}
	}


	/**
	 * Auto-subscribe a user to all forums.
	 *
	 * Called by the `user_register` action.
	 *
	 * @param int $user_id User ID.
	 */
	public function forums_auto_subscribe_new_user( $user_id ) {
		$forums = get_posts(
			array(
				'numberposts' => -1,
				'post_type'   => $this->bbpress_forum_post_type,
				'post_status' => 'publish',
			)
		);

		foreach ( $forums as $forum ) {
			// Check subscription status to avoid conflicts with other plugins.
			if ( ! bbp_is_user_subscribed( $user_id, $forum->ID ) ) {
				bbp_add_user_subscription( $user_id, $forum->ID );
			}
		}
	}

	/**
	 * When using background notifications, ensure core bbPress won't notify our recipients.
	 *
	 * So we load all recipients and save them to the queued_recipients property so
	 * core notification filters can exclude them.
	 *
	 * @param int          $reply_id       Reply ID.
	 * @param int          $topic_id       Topic ID.
	 * @param int          $forum_id       Forum ID.
	 * @param string|false $anonymous_data Anonymous reply data, if any.
	 * @param int          $reply_author   Reply author user ID.
	 * @param bool         $bool           Internal boolean flag.
	 * @param mixed        $reply_to       Reply-to identifier or email.
	 */
	public function bg_filter_reply_recipients( $reply_id = 0, $topic_id = 0, $forum_id = 0, $anonymous_data = false, $reply_author = 0, $bool = false, $reply_to = null ) {
		// Load reply recipients
		if ( ! $reply_author ) {
			$reply_author = bbp_get_reply_author_id( $reply_id );
		}
		$recipients = $this->get_recipients( $forum_id, $type = 'reply', $topic_id, $reply_author );
		$recipients = apply_filters( 'bbpress_reply_notify_recipients', $recipients, $reply_id, $topic_id, $forum_id );
		$recipients = apply_filters( 'bbpnns_filter_recipients_before_send', $recipients );

		$this->queued_recipients = $recipients;

		// Add reply filter for core notifications
		add_filter( 'bbp_topic_subscription_user_ids', array( $this, 'filter_queued_recipients' ), 10, 1 );
	}


	/**
	 * When using background notifications, ensure core bbPress won't notify our recipients for topics.
	 *
	 * Load recipients and store them so core notification filters can exclude them.
	 *
	 * @param int          $topic_id       Topic ID.
	 * @param int          $forum_id       Forum ID.
	 * @param string|false $anonymous_data Anonymous topic data, if any.
	 * @param int          $topic_author   Topic author user ID.
	 */
	public function bg_filter_topic_recipients( $topic_id = 0, $forum_id = 0, $anonymous_data = false, $topic_author = 0 ) {
		if ( $this->is_dry_run() ) {
			$this->trace( 'Starting bg_filter_topic_recipients' );
		}

		// Load topic recipients
		if ( ! $topic_author ) {
			$topic_author = bbp_get_topic_author_id( $topic_id );
		}
		$recipients = $this->get_recipients( $forum_id, $type = 'topic', $topic_id, $topic_author );
		$recipients = apply_filters( 'bbpress_topic_notify_recipients', $recipients, $topic_id, $forum_id );
		$recipients = apply_filters( 'bbpnns_filter_recipients_before_send', $recipients );

		$this->queued_recipients = $recipients;

		// Add topic filter for core notifications
		add_filter( 'bbp_forum_subscription_user_ids', array( $this, 'filter_queued_recipients' ), 10, 1 );
	}


	/**
	 * Schedule the mailout for replies for the next cron run.
	 *
	 * @param int          $reply_id       Reply ID.
	 * @param int          $topic_id       Topic ID.
	 * @param int          $forum_id       Forum ID.
	 * @param string|false $anonymous_data Anonymous reply data, if any.
	 * @param int          $reply_author   Reply author user ID.
	 * @param bool         $bool           Internal boolean flag.
	 * @param mixed        $reply_to       Reply-to identifier or email.
	 */
	public function bg_notify_new_reply( $reply_id = 0, $topic_id = 0, $forum_id = 0, $anonymous_data = false, $reply_author = 0, $bool = false, $reply_to = null ) {
		if ( false === bbpnns()->is_dry_run() ) {
			if ( doing_action( 'bbp_approved_reply' ) ) {
				$topic_id     = bbp_get_reply_topic_id( $reply_id );
				$forum_id     = bbp_get_reply_forum_id( $reply_id );
				$reply_author = bbp_get_reply_author_id( $reply_id );
				$reply_to     = bbp_get_reply_to( $reply_id );
			}

			if ( $this->action_scheduler_enabled ) {
				// Action already added in __construct
				as_enqueue_async_action(
					'bbpress_notify_bg_reply',
					array(
						$reply_id,
						$topic_id,
						$forum_id,
						$anonymous_data,
						$reply_author,
						$bool,
						$reply_to,
					),
					$group  = 'bbpnns',
					$unique = true,
				);
			} else {
				wp_schedule_single_event( time() + 10, 'bbpress_notify_bg_reply', array( $reply_id, $topic_id, $forum_id, $anonymous_data, $reply_author, $bool, $reply_to ) );
			}
		} else {
			$using = $this->action_scheduler_enabled ? 'Action Scheduler' : 'wp-cron';
			$this->trace( "Would have used $using to schedule reply notification for reply_id: " . $reply_id );
		}
	}


	/**
	 * Schedule the mailout for topics for the next cron run.
	 *
	 * @param int          $topic_id       Topic ID.
	 * @param int          $forum_id       Forum ID.
	 * @param string|array $anonymous_data Anonymous topic data, if any.
	 * @param int          $topic_author   Topic author user ID.
	 */
	public function bg_notify_new_topic( $topic_id = 0, $forum_id = 0, $anonymous_data = false, $topic_author = 0 ) {
		if ( false === bbpnns()->is_dry_run() ) {
			if ( doing_action( 'bbp_approved_topic' ) ) {
				$forum_id     = bbp_get_topic_forum_id( $topic_id );
				$topic_author = bbp_get_topic_author_id( $topic_id );
			}

			if ( $this->action_scheduler_enabled ) {
				// Action already added in __construct
				as_enqueue_async_action(
					'bbpress_notify_bg_topic',
					array(
						$topic_id,
						$forum_id,
						$anonymous_data,
						$topic_author,
					),
					$group  = 'bbpnns',
					$unique = true,
				);
			} else {
				wp_schedule_single_event( time() + 10, 'bbpress_notify_bg_topic', array( $topic_id, $forum_id, $anonymous_data, $topic_author ) );
			}
		} else {
			$using = $this->action_scheduler_enabled ? 'Action Scheduler' : 'wp-cron';
			$this->trace( "Would have used $using to schedule topic notification for topic_id: " . $topic_id );
		}
	}


	/**
	 * Check if a user is in one of the allowed roles.
	 *
	 * @param int|null $user_id User ID. If null, the current user is used.
	 * @return bool True if user is in an OK role, false otherwise.
	 */
	public function user_in_ok_role( $user_id = null ) {
		if ( ! $user_id ) {
			$user_id = get_current_user_id();
			if ( ! $user_id ) {
				return false;
			}
		}

		if ( isset( $this->users_in_roles[ $user_id ] ) ) {
			return $this->users_in_roles[ $user_id ];
		}

		// Start out false
		$this->users_in_roles[ $user_id ] = false;

		$roles = array_merge( $this->settings->newtopic_recipients, $this->settings->newreply_recipients );

		$user = get_user_by( 'id', $user_id );

		if ( ! $user ) {
			return false;
		}

		foreach ( (array) $roles as $role ) {
			if ( in_array( $role, (array) $user->roles ) ) {
				$this->users_in_roles[ $user_id ] = true;
				break;
			}
		}

		return $this->users_in_roles[ $user_id ];
	}


	/**
	 * If the admin selected multipart messages, set AltBody on PHPMailer.
	 *
	 * @param object $phpmailer PHPMailer instance.
	 */
	public function set_alt_body( $phpmailer ) {
		$phpmailer->AltBody = wp_strip_all_tags( $this->convert_images_and_links( $this->AltBody ) );
	}

	/**
	 * Add a custom X-Built-By header to outgoing messages.
	 *
	 * @param object $phpmailer PHPMailer instance.
	 */
	public function add_signature_header( $phpmailer ) {
		$sig = sprintf( 'bbPress Notify (No-Spam) v.%s (%s)', self::VERSION, 'https://wordpress.org/plugins/bbpress-notify-nospam/' );
		$phpmailer->addCustomHeader( 'X-Built-By', $sig );
	}


	/**
	 * Used by send_notification to set the correct content type.
	 *
	 * @since 1.14
	 * @param string $content_type Content type to set/override.
	 * @return string Content type string.
	 */
	public function set_content_type( $content_type ) {
		if ( ! isset( $this->message_type ) ) {
			$this->message_type = $this->settings->email_type;
		}

		switch ( $this->message_type ) {
			case 'html':
			case 'multipart':
				$content_type = 'text/html';
				break;
			default:
				$content_type = 'text/plain';
		}

		return $content_type;
	}


	/**
	 * Capture WP_Error from wp_mail failures for debugging.
	 *
	 * @since 1.14
	 * @param WP_Error $wp_error WP_Error returned from wp_mail failure.
	 */
	public function capture_wp_mail_failure( WP_Error $wp_error ) {
		$this->wp_mail_error = $wp_error;
	}


	/**
	 * Make sure we keep links instead of stripping them out along with the rest of the HTML.
	 *
	 * @param string $text HTML content to convert.
	 * @return string Converted text.
	 */
	public function convert_images_and_links( $text ) {
		$dom = new DOMDocument();

		$previous_value = libxml_use_internal_errors( true );

		if ( function_exists( 'mb_convert_encoding' ) ) {
			$dom->loadHTML( mb_convert_encoding( $text, 'HTML-ENTITIES', $this->charset ) );
		} else {
			// Use html entities decoding as a safe fallback when mbstring is not available.
			$dom->loadHTML( html_entity_decode( htmlentities( $text, ENT_COMPAT, $this->charset, false ), ENT_COMPAT, $this->charset ) );
		}

		libxml_use_internal_errors( $previous_value );

		$elements = $dom->getElementsByTagName( 'a' );

		foreach ( $elements as $el ) {
			$href = $el->getAttribute( 'href' );

			// Capture links that have only images in them.
			foreach ( $el->getElementsByTagName( 'img' ) as $img ) {
				$alt = $img->getAttribute( 'alt' );
				$src = $img->getAttribute( 'src' );

				$img_text = '*image*';
				if ( $alt ) {
					$img_text = $alt;
				} else {
					$img_text = basename( $src );
				}

				$img->nodeValue = sprintf( '[img]%s[/img]', $img_text );
			}

			$href = preg_replace_callback(
				'@redirect_to=(https?://[^&]+)@i',
				function ( $matches ) {
					return 'redirect_to=' . urlencode( $matches[1] );
				},
				$href
			);

			$el->nodeValue = sprintf( '(%s) [%s]', $el->nodeValue, htmlspecialchars( $href ) );
		}

		// Unlinked images now
		foreach ( $dom->getElementsByTagName( 'img' ) as $img ) {
			$alt = $img->getAttribute( 'alt' );
			$src = $img->getAttribute( 'src' );

			$img_text = '*image*';
			if ( $alt ) {
				$img_text = $alt;
			} else {
				$img_text = basename( $src );
			}

			$img->nodeValue = sprintf( '[img]%s[/img]', $img_text );
		}

		if ( $elements ) {
			$text = $dom->documentElement->lastChild->nodeValue;
		}

		return $text;
	}


	/**
	 * On-the-fly handling of nl2br by Mandrill.
	 *
	 * @param bool  $nl2br   Whether nl2br conversion is requested.
	 * @param array $message Mandrill message array.
	 * @return bool Modified nl2br flag.
	 */
	public function handle_mandrill_nl2br( $nl2br, $message ) {
		$bbpnns_nl2b2_option = apply_filters( 'bbpnns_handle_mandrill_nl2br', true, $nl2br, $message );

		return $bbpnns_nl2b2_option;
	}

	/**
	 * Build the email subject and body for the given post.
	 *
	 * @since 1.4
	 * @param string $type     Notification type ("topic" or "reply").
	 * @param int    $post_id  Post ID (topic or reply ID).
	 * @param int    $forum_id Forum ID.
	 * @return array Array( $subject, $body ).
	 */
	private function _build_email( $type, $post_id, $forum_id ) {
		global $wp_embed;

		add_shortcode( 'bbpnns_date', array( $this, '_process_date_tag' ) );

		$email_subject = apply_filters( "bbpnns_raw_{$type}_subject", $this->settings->{"new{$type}_email_subject"} );
		$email_body    = apply_filters( "bbpnns_raw_{$type}_body", $this->settings->{"new{$type}_email_body"} );

		$email_subject = wp_specialchars_decode( $email_subject, ENT_QUOTES );
		$email_body    = wp_specialchars_decode( $email_body, ENT_QUOTES );

		$blogname              = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );
		$excerpt_size          = apply_filters( 'bpnns_excerpt_size', 100 );
		$topmost_forum_body    = $this->get_topmost_forum_link( $post_id, $is_subject = false );
		$topmost_forum_subject = $this->get_topmost_forum_link( $post_id, $is_subject = true );
		$forum_url             = bbp_get_forum_permalink( $forum_id );

		// Disable auto-embed for buddyboss/buddypress
		add_filter( 'bbp_use_autoembed', '__return_false' );

		// Replace shortcodes
		if ( 'topic' === $type ) {
			remove_filter( 'bbp_get_topic_content', array( $wp_embed, 'autoembed' ), 2 );

			$content = bbp_get_topic_content( $post_id );
			$title   = wp_specialchars_decode( strip_tags( bbp_get_topic_title( $post_id ) ), ENT_QUOTES );
			$excerpt = wp_specialchars_decode( strip_tags( bbp_get_topic_excerpt( $post_id, $excerpt_size ) ), ENT_QUOTES );
			$author  = bbp_get_topic_author_display_name( $post_id );
			$url     = apply_filters( 'bbpnns_topic_url', bbp_get_topic_permalink( $post_id ), $post_id, $title, $forum_id );
			$forum   = wp_specialchars_decode( strip_tags( get_the_title( $forum_id ) ), ENT_QUOTES );
		} elseif ( 'reply' === $type ) {
			remove_filter( 'bbp_get_reply_content', array( $wp_embed, 'autoembed' ), 2 );

			$content = bbp_get_reply_content( $post_id );
			$title   = wp_specialchars_decode( strip_tags( bbp_get_reply_title( $post_id ) ), ENT_QUOTES );
			$excerpt = wp_specialchars_decode( strip_tags( bbp_get_reply_excerpt( $post_id, $excerpt_size ) ), ENT_QUOTES );
			$author  = bbp_get_reply_author_display_name( $post_id );
			$url     = apply_filters( 'bbpnns_reply_url', bbp_get_reply_permalink( $post_id ), $post_id, $title, $forum_id );
			$forum   = wp_specialchars_decode( strip_tags( get_the_title( $forum_id ) ), ENT_QUOTES );

			// Topic-specific stuff in replies
			$topic_id           = bbp_get_reply_topic_id( $post_id );
			$topic_title        = wp_specialchars_decode( strip_tags( bbp_get_topic_title( $topic_id ) ), ENT_QUOTES );
			$topic_author       = bbp_get_topic_author( $topic_id );
			$topic_author_email = bbp_get_topic_author_email( $topic_id );

			$topic_content = '';
			if ( false !== strpos( $email_body, '[topic-content]' ) ) {
				$topic_content = bbp_get_topic_content( $topic_id );
				// This is causing all lines ending in <br> to be wrapped in <p></p>
				/*
				$topic_content = preg_replace( '/<br\\s*\\/?>/is', PHP_EOL, $topic_content );
				$topic_content = preg_replace( '/(?:<\\/p>\\s*<p>)/ism', PHP_EOL . PHP_EOL, $topic_content ); */
				$topic_content = wp_specialchars_decode( $topic_content, ENT_QUOTES );
			}

			$topic_excerpt = '';
			if ( false !== strpos( $email_body, '[topic-excerpt]' ) ) {
				$topic_excerpt = wp_specialchars_decode( strip_tags( bbp_get_topic_excerpt( $topic_id, $excerpt_size ) ), ENT_QUOTES );
			}
		} else {
			wp_die( 'Invalid type!' );
		}

		// This is causing all lines ending in <br> to be wrapped in <p></p>
		/*
		$content = preg_replace( '/<br\\s*\\/?>/is', PHP_EOL, $content );
		$content = preg_replace( '/(?:<\\/p>\\s*<p>)/ism', PHP_EOL . PHP_EOL, $content ); */
		$content = wp_specialchars_decode( $content, ENT_QUOTES );

		$topic_reply = apply_filters( 'bbpnns_topic_reply', bbp_get_reply_url( $post_id ), $post_id, $title );

		$author_id   = 'topic' === $type ? bbp_get_topic_author_id( $post_id ) : bbp_get_reply_author_id( $post_id );
		$author_info = get_user_by( 'id', $author_id );

		foreach ( array( 'first_name', 'last_name', 'display_name', 'user_nicename' ) as $prop ) {
			$email_subject = str_replace( "[author-{$prop}]", $author_info->{$prop}, $email_subject );
			$email_body    = str_replace( "[author-{$prop}]", $author_info->{$prop}, $email_body );
		}

		$author_email = $author_info->user_email;

		$email_subject = str_replace( '[blogname]', $blogname, $email_subject );
		$email_subject = str_replace( "[$type-title]", $title, $email_subject );
		$email_subject = str_replace( "[$type-content]", $content, $email_subject );
		$email_subject = str_replace( "[$type-excerpt]", $excerpt, $email_subject );
		$email_subject = str_replace( "[$type-author]", $author, $email_subject );
		$email_subject = str_replace( "[$type-url]", $url, $email_subject );
		$email_subject = str_replace( "[$type-replyurl]", $topic_reply, $email_subject );
		$email_subject = str_replace( "[$type-forum]", $forum, $email_subject );
		$email_subject = str_replace( "[$type-forum-url]", $forum_url, $email_subject );
		$email_subject = str_replace( "[$type-author-email]", $author_email, $email_subject );
		$email_subject = preg_replace_callback(
			'/\\[date([^\\]]*)\\]/',
			function ( $matches ) {
				return do_shortcode( '[bbpnns_date ' . $matches[1] . ']' );
			},
			$email_subject
		);
		$email_subject = str_replace( '[topmost-forum]', $topmost_forum_subject, $email_subject );

		$email_body = str_replace( '[blogname]', $blogname, $email_body );
		$email_body = str_replace( "[$type-title]", $title, $email_body );
		$email_body = str_replace( "[$type-content]", $content, $email_body );
		$email_body = str_replace( "[$type-excerpt]", $excerpt, $email_body );
		$email_body = str_replace( "[$type-author]", $author, $email_body );
		$email_body = str_replace( "[$type-url]", $url, $email_body );
		$email_body = str_replace( "[$type-replyurl]", $topic_reply, $email_body );
		$email_body = str_replace( "[$type-forum]", $forum, $email_body );
		$email_body = str_replace( "[$type-forum-url]", $forum_url, $email_body );
		$email_body = str_replace( "[$type-author-email]", $author_email, $email_body );
		$email_body = preg_replace_callback(
			'/\\[date([^\\]]*)\\]/',
			function ( $matches ) {
				return do_shortcode( '[bbpnns_date ' . $matches[1] . ']' );
			},
			$email_body
		);
		$email_body = str_replace( '[topmost-forum]', $topmost_forum_body, $email_body );

		/**
		 * Also do some topic tag replacement in replies. See https://wordpress.org/support/topic/tags-for-reply-e-mail-body/
		 *
		 * @since 1.15.3
		 */
		if ( 'reply' === $type ) {
			$email_subject = str_replace( '[topic-title]', $topic_title, $email_subject );
			$email_subject = str_replace( '[topic-author]', $topic_author, $email_subject );
			$email_subject = str_replace( '[topic-author-email]', $topic_author_email, $email_subject );

			$email_body = str_replace( '[topic-title]', $topic_title, $email_body );
			$email_body = str_replace( '[topic-author]', $topic_author, $email_body );
			$email_body = str_replace( '[topic-author-email]', $topic_author_email, $email_body );
			$email_body = str_replace( '[topic-content]', $topic_content, $email_body );
			$email_body = str_replace( '[topic-excerpt]', $topic_excerpt, $email_body );

			if ( strpos( $email_body, '[topic-url]' ) || strpos( $email_subject, '[topic-url]' ) ) {
				$topic_id  = bbp_get_reply_topic_id( $post_id );
				$topic_url = apply_filters( 'bbpnns_topic_url', bbp_get_topic_permalink( $topic_id ), $topic_id, $title, $forum_id );

				$email_subject = str_replace( '[topic-url]', $topic_url, $email_subject );
				$email_body    = str_replace( '[topic-url]', $topic_url, $email_body );
			}
		}

		/**
		 * Allow subject and body modifications
		 *
		 * @since 1.6.6
		 */
		$email_subject = apply_filters( 'bbpnns_filter_email_subject_in_build', $email_subject, $type, $post_id );
		$email_body    = apply_filters( 'bbpnns_filter_email_body_in_build', $email_body, $type, $post_id );

		remove_shortcode( 'bbpnns_date' );

		return array( $email_subject, $email_body );
	}


	/**
	 * Grabs the top-most forum title/link for a given post_id.
	 *
	 * @param int  $post_id    Post ID (topic or reply ID).
	 * @param bool $is_subject If true, return plain title for subjects.
	 * @return string Topmost forum title or HTML link.
	 */
	protected function get_topmost_forum_link( $post_id, $is_subject = false ) {
		if ( 0 === $post_id ) {
			return '';
		}

		$parents   = $this->get_forum_parents( $post_id );
		$parent_id = end( $parents );

		$forum_title = bbp_get_forum_title( $parent_id );
		$link        = bbp_get_forum_permalink( $parent_id );

		return $is_subject ? $forum_title : sprintf( '<a href="%s">%s</a>', esc_attr( $link ), $forum_title );
	}


	/**
	 * Looks up the hierarchy to find the top-most forum.
	 *
	 * @param int $post_id Post ID.
	 * @return int Array of parent forum IDs for the post.
	 */
	protected function get_forum_parents( $post_id ) {
		if ( empty( $this->forum_hierarchy[ $post_id ] ) ) {
			$lookup_id = bbp_is_topic( $post_id ) ? bbp_get_topic_forum_id( $post_id ) : bbp_get_reply_forum_id( $post_id );

			while ( 1 ) {
				$this->forum_hierarchy[ $post_id ][] = $lookup_id;

				$parent_id = bbp_get_forum_parent_id( $lookup_id );

				if ( $parent_id === $lookup_id ) {
					break;
				}

				if ( 0 === $parent_id ) {
					$parent_id = $lookup_id;
					break;
				}

				$lookup_id = $parent_id;
			}
		}

		return $this->forum_hierarchy[ $post_id ];
	}


	/**
	 * Used to process [date]. Note that the shortcode only exists during _build_body().
	 *
	 * Used to process the `[date]` shortcode inside emails.
	 *
	 * @since 2.2
	 * @param array  $atts Shortcode attributes.
	 * @param string $content Optional content (unused).
	 * @return string Formatted date string.
	 */
	public function _process_date_tag( $atts, $content = '' ) {
		$atts = shortcode_atts(
			array(
				'format' => get_option( 'date_format' ) . ' ' . get_option( 'time_format' ),
			),
			$atts,
			'bbpnns_date'
		);

		return date_i18n( $atts['format'] );
	}


	/**
	 * Extracted get_recipients code to its own method.
	 *
	 * @since 1.9
	 * @param int    $forum_id Forum ID.
	 * @param string $type     Notification type ('topic'|'reply').
	 * @param int    $topic_id Topic ID.
	 * @param int    $author_id Author user ID.
	 * @return array Associative array of recipient user info objects.
	 */
	public function get_recipients( $forum_id, $type, $topic_id, $author_id ) {

		$roles = isset( $this->settings->{"new{$type}_recipients"} ) ? $this->settings->{"new{$type}_recipients"} : array();
		// Normalize roles
		$roles = isset( $roles[0] ) ? $roles : array_keys( $roles );
		$roles = apply_filters( 'bbpress_notify_recipients_hidden_forum', $roles, $type, $forum_id );
		$roles = array_filter(
			$roles,
			static function ( $role ) {
				return ! empty( $role );
			}
		);

		$recipients = array();

		$this->trace( 'In get_recipients, requested roles: ' . print_r( join( ', ', (array) $roles ), 1 ) );

		if ( ! empty( $roles ) ) {
			$users = get_users( array( 'role__in' => $roles ) );

			foreach ( (array) $users as $user ) {
				$recipients[ $user->ID ] = bbpnns()->user_info( $user );
			}

			$this->trace(
				'In get_recipients, loaded recipients by role: ' . print_r(
					array(
						'total_users' => count( $recipients ),
						'IDs'         => join(
							', ',
							array_keys( (array) $recipients )
						),
					),
					1
				)
			);
		}

		// Core subscribers logic
		$subscrp_active = bbp_is_subscriptions_active();
		$subscribers    = array();
		if ( $this->settings->override_bbp_forum_subscriptions && $subscrp_active && 'topic' === $type ) {
			$this->trace( 'Loading forum subscribers for topic notification.' );
			$subscribers             = bbp_get_forum_subscribers( $forum_id );
			$this->forum_subscribers = $subscribers; // We'll use this later
		} elseif ( $this->settings->override_bbp_topic_subscriptions && $subscrp_active && 'reply' === $type ) {
			$this->trace( 'Loading topic subscribers for reply notification.' );
			$subscribers = bbp_get_topic_subscribers( $topic_id );

			if ( $this->settings->include_bbp_forum_subscriptions_in_replies ) {
				$this->trace( 'Also loading forum subscribers for reply notification.' );

				$forum_subscribers = bbp_get_forum_subscribers( $forum_id );
				$subscribers       = array_merge( $subscribers, $forum_subscribers );
			}
		}

		$this->trace( 'In get_recipients, loaded initial subscribers: ' . print_r( $subscribers, 1 ) );

		/**
		 * Allow subscribers to be accessed/changed by other plugins. Introduced for the opt-out add-on.
		 *
		 * @since 1.15.4
		 */
		$subscribers = apply_filters( 'bbpnns_core_subscribers', $subscribers );

		$this->trace( 'In get_recipients, after "bbpnns_core_subscribers" filter: ' . print_r( $subscribers, 1 ) );

		foreach ( (array) $subscribers as $sub_id ) {
			if ( isset( $recipients[ $sub_id ] ) ) {
				continue;
			}

			$user = new WP_User( $sub_id );

			/**
			 * Check that the user really exists.
			 * bbPress can keep a deleted user subscribed, and we don't want that.
			 */
			if ( ! $user->exists() ) {
				$this->trace( 'Skipping subscriber as they no longer exist: ' . $sub_id );
				continue;
			}

			$recipients[ $sub_id ] = bbpnns()->user_info( $user );
		}

		/**
		 * Centralized authors control. Also add author if setting is checked and not already in $recipients.
		 */
		$author_msg = '';
		if ( true === (bool) apply_filters( "bbpnns_notify_authors_{$type}", $this->settings->{"notify_authors_$type"} ) ) {
			$author_msg = '+ author ';
			if ( ! isset( $recipients[ $author_id ] ) ) {
				$this->trace( 'Adding author to recipient list: ' . $author_id );

				$recipients[ $author_id ] = bbpnns()->user_info( $author_id );
			}
		} elseif ( isset( $recipients[ $author_id ] ) ) {
			$author_msg = '- author ';
			$this->trace( 'Removing author from recipient list: ' . $author_id );
			unset( $recipients[ $author_id ] );
		}

		$non_blocked = array();

		// Try to remove memory usage even if this makes extra DB calls.
		global $wpdb;
		$blog_prefix = $wpdb->get_blog_prefix();
		foreach ( $recipients as $user_id => $user ) {
			$user_roles = $user->roles;

			if ( in_array( 'bbp_blocked', $user_roles ) ) {
				$this->trace( "In get_recipients, dropping blocked recipient $user_id" );
				continue;
			}

			$non_blocked[ $user_id ] = $user;
		}

		$recipients = $non_blocked;

		$this->trace( "In get_recipients, final recipient count ( roles + subscribers $author_msg): " . print_r( count( $recipients ), 1 ) );

		return $recipients;
	}


	/**
	 * Check if bbpnns is in effect (whether because of selected roles or of bbpress core notification Overrides.
	 *
	 * @param bool $retval Previously filtered return value.
	 * @param int  $user_id Optional user ID to check.
	 * @return boolean
	 */
	public function bbpnns_is_in_effect( $retval = false, $user_id = null ) {
		// Check if any overrides are on or if role-based recipients/authors are enabled.

		return ( $this->settings->override_bbp_forum_subscriptions ||
				$this->settings->override_bbp_topic_subscriptions ||
				// Authors?
				$this->settings->notify_authors_reply ||
				$this->settings->notify_authors_topic ||
				// Check if the user_id passed is part of the OK'd roles.
				$this->user_in_ok_role( $user_id ) );
	}


	/**
	 * A method just for the reply tags.
	 *
	 * @since 1.10
	 * @param string $tags Comma-separated tags string.
	 * @param string $for  Context for tags ('body'|'subject').
	 * @return string Comma-separated tags string.
	 */
	public function get_available_reply_tags( $tags = '', $for = 'body' ) {
		$tags = '[blogname], [recipient-first_name], [recipient-last_name], [recipient-display_name], [recipient-user_nicename], ' .
			'[reply-title], [reply-content], [reply-excerpt], [reply-url], [reply-replyurl], [reply-author], [reply-author-email], ' .
			'[reply-forum], [reply-forum-url], [topic-url], [topic-title], [topic-author], [topic-author-email], ' .
			'[author-first_name], [author-last_name], [author-display_name], [author-user_nicename], ' .
			'[topic-content], [topic-excerpt], [date]';

		$extra_tags = apply_filters( 'bbpnns_extra_reply_tags', null, $for );

		if ( $extra_tags ) {
			$tags .= ', ' . $extra_tags;
		}

			return $tags;
	}


	/**
	 * A method for the topic tags.
	 *
	 * @since 1.9
	 * @param string $tags Comma-separated tags string.
	 * @param string $for  Context for tags ('body'|'subject').
	 * @return string Comma-separated tags string.
	 */
	public function get_available_topic_tags( $tags = '', $for = 'body' ) {
		$tags = '[blogname], [recipient-first_name], [recipient-last_name], [recipient-display_name], ' .
			'[recipient-user_nicename], [topic-title], [topic-content], [topic-excerpt], [topic-url], ' .
			'[author-first_name], [author-last_name], [author-display_name], [author-user_nicename], ' .
			'[topic-replyurl], [topic-author], [topic-author-email], [topic-forum], [topic-forum-url], [date], [topmost-forum]';

		$extra_tags = apply_filters( 'bbpnns_extra_topic_tags', null, $for );

		if ( $extra_tags ) {
			$tags .= ', ' . $extra_tags;
		}

		return $tags;
	}


	/**
	 * Deprecated
	 *
	 * @param string $tags Comma-separated tags string.
	 * @return string Comma-separated tags string (possibly extended).
	 */
	public function get_available_tags( $tags = '' ) {
		return $this->get_available_topic_tags( $tags );
	}


	/**
	 * Forces admin-only recipients if forum is hidden.
	 *
	 * @since 1.5
	 * @param array  $roles   Current roles array.
	 * @param string $type    Notification type.
	 * @param int    $forum_id Forum ID.
	 * @return array Modified roles array.
	 */
	public function munge_newpost_recipients( $roles, $type, $forum_id = 0 ) {
		if ( true === (bool) bbp_is_forum_hidden( $forum_id ) &&
			true === (bool) ( isset( $this->settings->{"hidden_forum_{$type}_override"} ) ? $this->settings->{"hidden_forum_{$type}_override"} : false ) ) {
			$roles = array( 'administrator' );
		}

		return $roles;
	}


	/**
	 * Remove the core forum notifications if Override Subscriptions to Forums is on.
	 */
	public function remove_core_forum_notification() {
		remove_action( 'bbp_new_topic', 'bbp_notify_forum_subscribers', 11 );
		remove_action( 'bbp_new_topic', 'bbp_notify_forum_subscribers', 9999 ); // BuddyBoss started hooking here.
	}


	/**
	 * Remove the core topic notification if Override Subscriptions to Topics is on.
	 */
	public function remove_core_topic_notification() {
		remove_action( 'bbp_new_reply', 'bbp_notify_topic_subscribers', 11 );
		remove_action( 'bbp_new_reply', 'bbp_notify_topic_subscribers', 9999 ); // BuddyBoss started hooking here.
	}


	/**
	 * Sends notifications when user saves/publishes a post. Note that the send notification checkbox must be ticked.
	 *
	 * @param int    $post_id Post ID being saved.
	 * @param object $post    WP_Post object for the post.
	 * @return array|int|bool Result (see function docblock for details).
	 */
	public function notify_on_save( $post_id, $post ) {
		$is_future_publish = doing_action( 'publish_future_post' );

		// Normalize incoming POST data for safe checks and nonce verification.
		$posted = isset( $_POST ) ? wp_unslash( $_POST ) : array();

		if ( empty( $posted ) && ! $is_future_publish ) {
			return;
		}

		if ( $this->bbpress_topic_post_type !== $post->post_type && $this->bbpress_reply_post_type !== $post->post_type ) {
			return;
		}

		if ( ! $is_future_publish && ! current_user_can( 'manage_options' ) && ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		if ( wp_is_post_revision( $post_id ) || 'publish' !== $post->post_status ) {
			return;
		}

		if ( ! $is_future_publish && ( ! isset( $posted['bbpress_notify_send_notification'] ) || ! $posted['bbpress_notify_send_notification'] ) ) {
			return;
		}

		$type = ( $post->post_type === $this->bbpress_topic_post_type ) ? 'topic' : 'reply';

		if ( ! $is_future_publish &&
			( ! isset( $posted[ "bbpress_send_{$type}_notification_nonce" ] ) ||
			! wp_verify_nonce( $posted[ "bbpress_send_{$type}_notification_nonce" ], "bbpress_send_{$type}_notification_nonce" ) ) ) {
			return;
		}

		// Check the default notification options
		if ( empty( $posted ) && $is_future_publish ) {
			$do_notify = isset( $this->settings->{"default_{$type}_notification_checkbox"} ) ? $this->settings->{"default_{$type}_notification_checkbox"} : false;

			if ( ! $do_notify ) {
				return;
			}
		}

		// Allow our add-ons to do stuff at this point
		do_action( 'bbpnns_doing_notify_on_save' );

		// Still here, so we can notify

		if ( $post->post_type === $this->bbpress_topic_post_type ) {
			return $this->notify_new_topic( $post_id );
		} else {
			return $this->notify_new_reply( $post_id );
		}
	}


	/**
	 * Notify subscribers of a new topic.
	 *
	 * @since 1.0
	 * @param int   $topic_id       Topic ID.
	 * @param int   $forum_id       Forum ID.
	 * @param array $anonymous_data Anonymous topic data.
	 * @param int   $topic_author   Topic author user ID.
	 * @return int|array True on success, or negative code on early exit; array for dry-run.
	 */
	public function notify_new_topic( $topic_id = 0, $forum_id = 0, $anonymous_data = array(), $topic_author = 0 ) {
		if ( doing_action( 'bbp_approved_topic' ) ) {
			$forum_id     = bbp_get_topic_forum_id( $topic_id );
			$topic_author = bbp_get_topic_author_id( $topic_id );
		}

		$this->trace(
			'Starting notify_new_topic for: ' . print_r(
				array(
					'forum_id' => $forum_id,
					'topic_id' => $topic_id,
				),
				1
			)
		);

		$status = get_post_status( $topic_id );

		if ( in_array( $status, (array) apply_filters( 'bbpnns_post_status_blacklist', array( 'spam' ), $status, $forum_id, $topic_id, $reply_id = false ) ) ||
			! in_array( $status, (array) apply_filters( 'bbpnns_post_status_whitelist', array( 'publish' ), $status, $forum_id, $topic_id, $reply_id = false ) ) ) {
			$this->trace( 'Cutting process short due to bad post status.' );

			return -1;
		}

		if ( 0 === $forum_id ) {
			$forum_id = bbp_get_topic_forum_id( $topic_id );
		}

		if ( true === apply_filters( 'bbpnns_skip_topic_notification', false, $forum_id, $topic_id ) ) {
			$this->trace( 'Cutting process short due to bbpnns_skip_topic_notification filter.' );
			return -3;
		}

		if ( ! $topic_author ) {
			$topic_author = bbp_get_topic_author_id( $topic_id );
		}

		if ( $this->doing_cron ) {
			wp_set_current_user( $topic_author );
		}

		$recipients = $this->get_recipients( $forum_id, 'topic', $topic_id, $topic_author );

		/**
		 * Allow topic recipients munging
		 *
		 * @since 1.6.5
		 */
		$recipients = apply_filters( 'bbpress_topic_notify_recipients', $recipients, $topic_id, $forum_id );

		$this->trace( 'Recipient IDs after "bbpress_topic_notify_recipients" filter: ' . print_r( join( ', ', array_keys( (array) $recipients ) ), 1 ) );

		if ( empty( $recipients ) ) {
			$this->trace( 'Ending Dry Run due to empty recipients list.' );
			return -2;
		}

		list( $email_subject, $email_body ) = $this->_build_email( 'topic', $topic_id, $forum_id );

		return $this->send_notification( $recipients, $email_subject, $email_body, $type = 'topic', $topic_id, $forum_id );
	}


	/**
	 * Notify subscribers of a new reply.
	 *
	 * @since 1.0
	 * @param int          $reply_id       Reply ID.
	 * @param int          $topic_id       Topic ID.
	 * @param int          $forum_id       Forum ID.
	 * @param string|false $anonymous_data Anonymous reply data.
	 * @param int          $reply_author   Reply author user ID.
	 * @param bool         $bool           Internal boolean flag.
	 * @param mixed        $reply_to       Reply-to identifier or email.
	 * @return int|array True on success, or negative code on early exit; array for dry-run.
	 */
	public function notify_new_reply( $reply_id = 0, $topic_id = 0, $forum_id = 0, $anonymous_data = false, $reply_author = 0, $bool = false, $reply_to = null ) {
		$this->trace(
			'Starting notify_new_reply for: ' . print_r(
				array(
					'forum_id' => $forum_id,
					'topic_id' => $topic_id,
					'reply_id' => $reply_id,
				),
				1
			)
		);

		if ( doing_action( 'bbp_approved_reply' ) ) {
			$topic_id     = bbp_get_reply_topic_id( $reply_id );
			$forum_id     = bbp_get_reply_forum_id( $reply_id );
			$reply_author = bbp_get_reply_author_id( $reply_id );
			$reply_to     = bbp_get_reply_to( $reply_id );
		}

		$status = get_post_status( $reply_id );

		if ( ! $forum_id ) {
			$forum_id = bbp_get_reply_forum_id( $reply_id );
		}

		if ( ! $topic_id ) {
			$topic_id = bbp_get_reply_topic_id( $reply_id );
		}

		if ( in_array( $status, (array) apply_filters( 'bbpnns_post_status_blacklist', array( 'spam' ), $status, $forum_id, $topic_id, $reply_id ) ) ||
			! in_array( $status, (array) apply_filters( 'bbpnns_post_status_whitelist', array( 'publish' ), $status, $forum_id, $topic_id, $reply_id ) ) ) {
			return -1;
		}

		if ( true === apply_filters( 'bbpnns_skip_reply_notification', false, $forum_id, $topic_id, $reply_id ) ) {
			return -3;
		}

		if ( ! $reply_author ) {
			$reply_author = bbp_get_reply_author_id( $reply_id );
		}

		if ( $this->doing_cron ) {
			wp_set_current_user( $reply_author );
		}

		$recipients = $this->get_recipients( $forum_id, 'reply', $topic_id, $reply_author );

		/**
		 * Allow reply recipients munging
		 *
		 * @since 1.6.5
		 */
		$recipients = apply_filters( 'bbpress_reply_notify_recipients', $recipients, $reply_id, $topic_id, $forum_id );

		$this->trace( 'Filtered recipients before send_notification(): ' . print_r( join( ', ', array_keys( (array) $recipients ) ), 1 ) );

		if ( empty( $recipients ) ) {
			return -2;
		}

		list( $email_subject, $email_body ) = $this->_build_email( 'reply', $reply_id, $forum_id );

		return $this->send_notification( $recipients, $email_subject, $email_body, $type = 'reply', $reply_id, $forum_id );
	}


	/**
	 * Make sure core bbpress doesn't send messages to people we've already notified.
	 *
	 * @param array $user_ids Array of user IDs to load.
	 * @return array Associative array of user objects keyed by ID.
	 */
	public function filter_queued_recipients( $user_ids ) {
		$clean = array();
		if ( ! empty( $this->queued_recipients ) ) {
			foreach ( $user_ids as $id ) {
				if ( isset( $this->queued_recipients[ $id ] ) ) {
					continue;
				}

				$clean[] = $id;
			}
		} else {
			$clean = $user_ids;
		}

		return $clean;
	}


	/**
	 * Send notifications to provided recipients.
	 *
	 * @since 1.0
	 * @param array  $recipients Associative array of recipient user info objects (user_id => user_info).
	 * @param string $subject    Email subject.
	 * @param string $body       Email body.
	 * @param string $type       Notification type ('topic'|'reply').
	 * @param int    $post_id    Post ID (topic or reply ID).
	 * @param int    $forum_id   Forum ID.
	 * @return bool|array True on success, array for dry-run, or false on failure.
	 */
	public function send_notification( $recipients, $subject, $body, $type = '', $post_id = '', $forum_id = '' ) {
		$this->message_type = $this->settings->email_type;

		// Set the content type
		add_filter( 'wp_mail_content_type', array( $this, 'set_content_type' ), 1000, 1 );

		// Capture wp_mail failure
		add_action( 'wp_mail_failed', array( $this, 'capture_wp_mail_failure' ), 10, 1 );

		$from_name = $this->settings->from_name ? $this->settings->from_name : get_option( 'blogname' );
		$from_name = apply_filters( 'bbpnns_from_name', $from_name );

		$from_email = $this->settings->from_email ? $this->settings->from_email : get_option( 'admin_email' );
		$from_email = apply_filters( 'bbpnns_from_email_address', $from_email );

		$headers = array(
			sprintf( 'From: %s <%s>', $from_name, $from_email ),
			'X-Auto-Response-Suppress: All',
		);

		$headers = apply_filters( 'bbpnns_extra_headers', $headers, $recipients, $subject, $body );

		add_action( 'phpmailer_init', array( $this, 'add_signature_header' ) );

		// Allow Management of recipients list
		$recipients = apply_filters( 'bbpnns_filter_recipients_before_send', $recipients );

		$this->trace(
			'Recipients after filter "bbpnns_filter_recipients_before_send": ' . print_r(
				array(
					'total_recipients' => count( $recipients ),
					'IDs'              => join( ', ', array_keys( (array) $recipients ) ),
				),
				1
			)
		);

		/**
		 * This is a workaround for cases where UTF-8 characters were blocking the message.
		 * Run these functions outside the loop for better performance.
		 */
		$do_enc      = (bool) $this->settings->encode_subject;
		$preferences = array();

		/**
		 * Load it just once.
		 */
		$this->charset = get_bloginfo( 'charset' );

		if ( true === $do_enc ) {
			$preferences = apply_filters(
				'bbpnns_subject_enc_preferences',
				array(
					'input-charset'  => $this->charset,
					'output-charset' => 'UTF-8',
					'scheme'         => 'Q',
				)
			);
		}

		// Evaluate this only once, check many
		$is_dry_run = apply_filters( 'bbpnns_dry_run', false );

		// Used to avoid duplicate notifications between ourselves and core bbpress in certain scenarios.
		$this->queued_recipients = $recipients;
		add_filter( 'bbp_forum_subscription_user_ids', array( $this, 'filter_queued_recipients' ), 10, 1 );
		add_filter( 'bbp_topic_subscription_user_ids', array( $this, 'filter_queued_recipients' ), 10, 1 );

		// Try to bypass timeout issues
		if ( $this->doing_cron ) {
			$old_time_limit = ini_get( 'max_execution_time' );
			set_time_limit( 0 );
		}

		// Maybe auto-subscribe user, but only when saving a new topic from the front-end.
		if ( $this->settings->forums_auto_subscribe_to_topics &&
			! empty( $this->forum_subscribers ) &&
			'topic' === $type &&
			! doing_action( 'save_post' ) ) {
			if ( true === apply_filters( 'bbpnns_skip_user_subscription', $is_dry_run ) ) {
				$this->trace( sprintf( 'Would have auto-subscribed %d forum subscriber(s) to the topic.', count( $this->forum_subscribers ) ) );
			} else {
				foreach ( $this->forum_subscribers as $user_id ) {
					bbp_add_user_topic_subscription( $user_id, $post_id );
				}
			}
		}

		$this->trace( sprintf( 'Entering mailout loop for %d users.', count( $recipients ) ) );

		foreach ( (array) $recipients as $recipient_id => $user_info ) {
			$this->trace( sprintf( 'Processing user: %d:%s', $recipient_id, $user_info->user_email ) );

			/**
			 * Allow skipping user during notification
			 *
			 * @since 1.6.4
			 */
			$email = ( -1 == $recipient_id ) ? get_bloginfo( 'admin_email' ) : (string) $user_info->user_email;
			$email = apply_filters( 'bbpnns_skip_notification', $email, $user_info ); // Allow user to be skipped for some reason

			if ( ! $email ) {
				$this->trace( sprintf( 'Skipping notification to user %d:%s due to filter "bbpnns_skip_notification".', $recipient_id, $user_info->user_email ) );
			} elseif ( true === $is_dry_run ) {
				$this->trace( 'Skipping mailout due to dry-run in effect.' );
			}

			if ( ! empty( $email ) && false === $is_dry_run ) {
				$this->wp_mail_error = null;

				/**
				 * Allow per user subject and body modifications
				 *
				 * @since 1.6.4
				 */
				$debug_enabled = ( defined('WP_DEBUG') && WP_DEBUG ) || ( defined('BBPNNS_DEBUG') && BBPNNS_DEBUG );

				if ( $debug_enabled ) { $__bbpnns_filter_start = microtime(true); }
				$filtered_body    = apply_filters( 'bbpnns_filter_email_body_for_user', $body, $user_info, $type, $post_id, $forum_id );
				$filtered_subject = apply_filters( 'bbpnns_filter_email_subject_for_user', $subject, $user_info, $type, $post_id, $forum_id );
				if ( $debug_enabled ) { $__bbpnns_filter_elapsed = round( microtime(true) - $__bbpnns_filter_start, 4 ); }

				/**
				 * Replace user name tags
				 *
				 * @since 1.14
				 */
				foreach ( array( 'first_name', 'last_name', 'display_name', 'user_nicename' ) as $prop ) {
					$filtered_body    = str_replace( "[recipient-{$prop}]", $user_info->{$prop}, $filtered_body );
					$filtered_subject = str_replace( "[recipient-{$prop}]", $user_info->{$prop}, $filtered_subject );
				}

				/**
				 * Multipart messages
				 *
				 * @since 1.14
				 */
				switch ( $this->message_type ) {
					case 'multipart':
						$this->AltBody = $filtered_body;
						if ( ! has_action( 'phpmailer_init', array( $this, 'set_alt_body' ) ) ) {
							add_action( 'phpmailer_init', array( $this, 'set_alt_body' ), 1001, 1 );
						}
						// Intentional fall-through to 'html' to add AltBody then format HTML body.
					case 'html':
						$filtered_body = wpautop( $filtered_body, false ); // Handle missing p tags.
						break;
					case 'plain':
						$filtered_body = wp_strip_all_tags( $this->convert_images_and_links( $filtered_body ) );
						break;
					default:
				}

				/**
				 * Make this optional
				 *
				 * @since 1.9.3
				 */
				if ( true === $do_enc ) {
					/**
					 * Enable UTF-8 characters in subject line
					 *
					 * @since 1.9
					 *
					 * @since 2.12 - Prefer mb_encode_mimeheader over iconv_mime_encode
					 * @since 2.14 - No mime encoding if charset is UTF-8
					 */
					if ( 'UTF-8' === $preferences['input-charset'] ) {
						$filtered_subject = html_entity_decode( $filtered_subject );
					} elseif ( function_exists( 'mb_encode_mimeheader' ) ) {
							$filtered_subject = mb_encode_mimeheader( html_entity_decode( $filtered_subject ), $preferences['input-charset'], $preferences['scheme'] );
					} elseif ( function_exists( 'iconv_mime_encode' ) ) {
						$filtered_subject = iconv_mime_encode( 'Subject', html_entity_decode( $filtered_subject ), $preferences );
						$filtered_subject = substr( $filtered_subject, strlen( 'Subject: ' ) );
					}
				}

				/**
				 * User headers, if any
				 */
				$recipient_headers = apply_filters( 'bbpnns_extra_headers_recipient', $headers, $user_info, $filtered_subject, $filtered_body );

				do_action( 'bbpnns_before_wp_mail', $user_info, $filtered_subject, $filtered_body, $recipient_headers );

				// For debugging
				// add_action( 'phpmailer_init', function($pm){  $pm->postSend(); error_log(__LINE__ . ' message: ' . print_r($pm->getSentMIMEMessage(),1) , 3, '/tmp/out.log' ); });

				// Turn on nl2br for wpMandrill
				add_filter( 'mandrill_nl2br', array( $this, 'handle_mandrill_nl2br' ), 10, 2 );

				if ( $debug_enabled ) { $__bbpnns_wp_mail_start = microtime(true); }
				$wp_mail_ok = wp_mail( $email, $filtered_subject, $filtered_body, $recipient_headers );
				if ( $debug_enabled ) { $__bbpnns_wp_mail_elapsed = round( microtime(true) - $__bbpnns_wp_mail_start, 4 ); }

				if ( ! $wp_mail_ok ) {
					do_action( 'bbpnns_email_failed_single_user', $user_info, $filtered_subject, $filtered_body, $recipient_headers, $this->wp_mail_error );
					do_action( 'bbpnns_after_wp_mail', $user_info, $filtered_subject, $filtered_body, $recipient_headers );

					// Turn off nl2br for wpMandrill
					remove_filter( 'mandrill_nl2br', array( $this, 'handle_mandrill_nl2br' ), 10 );
					continue;
				}

				do_action( 'bbpnns_after_wp_mail', $user_info, $filtered_subject, $filtered_body, $recipient_headers );

				if ( $debug_enabled ) {
					error_log( '[bbpnns] send_notification: user_id=' . intval( $recipient_id ) . ' email=' . $email . ' filter_elapsed=' . ( isset($__bbpnns_filter_elapsed)?$__bbpnns_filter_elapsed:'0' ) . 's wp_mail_elapsed=' . ( isset($__bbpnns_wp_mail_elapsed)?$__bbpnns_wp_mail_elapsed:'0' ) . 's wp_mail_result=' . var_export( $wp_mail_ok, true ) . ' wp_mail_error=' . ( is_null($this->wp_mail_error) ? 'null' : (string) $this->wp_mail_error ) );
				}

				// Turn off nl2br for wpMandrill
				remove_filter( 'mandrill_nl2br', array( $this, 'handle_mandrill_nl2br' ), 10 );

				do_action( 'bbpnns_after_email_sent_single_user', $user_info, $filtered_subject, $filtered_body );
			}
		}

		// Put back original time limit
		if ( $this->doing_cron ) {
			set_time_limit( $old_time_limit );
		}

		do_action( 'bbpnns_after_email_sent_all_users', $recipients, $subject, $body );

		if ( true === apply_filters( 'bbpnns_dry_run', false ) ) {
			$this->trace( 'End of Dry Run.' );

			return array( $recipients, $body );
		}

		return true;
	}


	/**
	 * Populates the dry_run trace.
	 * So we can keep the correct order of trace info, call do_action( 'bbpnns_dry_run_trace', 'The trace message' );
	 *
	 * @param string $msg Message to trace/log.
	 */
	public function trace( $msg ) {
		if ( true === apply_filters( 'bbpnns_dry_run', false ) ) {
			$sig = ! doing_action( 'bbpnns_dry_run_trace' ) ? '[bbPress_Notify_noSpam]: ' : '';

			add_filter(
				'bbpnns_dry_run_trace_info',
				function ( $messages ) use ( $msg, $sig ) {

					if ( substr( $msg, -strlen( PHP_EOL ) ) !== PHP_EOL ) {
						$msg .= PHP_EOL;
					}

					$messages[] = sprintf( '[%s] [%d] %s', gmdate( 'Y-m-d H:i:s' ), getmypid(), $sig . $msg );

					return $messages;
				},
				10,
				1
			);
		}
	}
}

/* phpcs:enable Squiz.Commenting.InlineComment.InvalidEndChar, Squiz.Commenting.BlockComment.NoEmptyLineBefore, Squiz.Commenting.BlockComment.CloserSameLine, WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase, PEAR.NamingConventions.ValidClassName.StartWithCapital, PEAR.NamingConventions.ValidClassName.Invalid, WordPress.NamingConventions.ValidClassName */

/* End of file class-common-core.php */
