<?php
/**
 * Plugin settings model.
 *
 * Conservative documentation, escaping and PHPCS fixes applied.
 *
 * @package bbPress_Notify_NoSpam
 */

defined( 'ABSPATH' ) || die( 'No direct access allowed' );

/* phpcs:disable PEAR.NamingConventions.ValidClassName.StartWithCapital,PEAR.NamingConventions.ValidClassName.Invalid */
/**
 * Settings model.
 *
 * Historical class name preserved for backwards compatibility.
 *
 * @package bbPress_Notify_NoSpam
 */
class bbPress_Notify_noSpam_Model_Settings {
/* phpcs:enable PEAR.NamingConventions.ValidClassName.StartWithCapital,PEAR.NamingConventions.ValidClassName.Invalid */

	/**
	 * Whether to UTF-8 encode the subject line.
	 *
	 * @var bool
	 */
	private $encode_subject = false;

	/**
	 * The type of message to be sent out: 'html', 'plain', 'multipart'.
	 *
	 * @var string
	 */
	private $email_type = 'html';

	/**
	 * Whether to notify in background for new topics.
	 *
	 * @var bool
	 */
	private $newtopic_background = false;

	/**
	 * Whether to notify in background for new replies.
	 *
	 * @var bool
	 */
	private $newreply_background = false;

	/**
	 * Whether background notifications are enabled.
	 *
	 * @var bool
	 */
	private $background_notifications = false;

	/**
	 * Whether to use the Action Scheduler for background notifications.
	 *
	 * @var bool
	 */
	private $use_action_scheduler = false;

	/**
	 * Default value for topic notification checkbox in New Post UI.
	 *
	 * @var bool
	 */
	private $default_topic_notification_checkbox = false;

	/**
	 * Default value for reply notification checkbox in New Post UI.
	 *
	 * @var bool
	 */
	private $default_reply_notification_checkbox = false;

	/**
	 * Whether to override bbPress forum subscriptions.
	 *
	 * @var bool
	 */
	private $override_bbp_forum_subscriptions = false;

	/**
	 * Whether to override bbPress topic subscriptions.
	 *
	 * @var bool
	 */
	private $override_bbp_topic_subscriptions = false;

	/**
	 * Whether to include forum subscriptions in replies notifications.
	 *
	 * @var bool
	 */
	private $include_bbp_forum_subscriptions_in_replies = false;

	/**
	 * Whether to auto-subscribe forum subscribers to new topics.
	 *
	 * @var bool
	 */
	private $forums_auto_subscribe_to_topics = false;

	/**
	 * Whether to auto-subscribe new users to all forums.
	 *
	 * @var bool
	 */
	private $forums_auto_subscribe_new_users = false;

	/**
	 * Recipient roles for new topic notifications.
	 *
	 * @var array
	 */
	private $newtopic_recipients = array();

	/**
	 * Recipient roles for new reply notifications.
	 *
	 * @var array
	 */
	private $newreply_recipients = array();

	/**
	 * Whether to notify authors of their topics.
	 *
	 * @var bool
	 */
	private $notify_authors_topic = false;

	/**
	 * Whether to notify authors of their replies.
	 *
	 * @var bool
	 */
	private $notify_authors_reply = false;

	/**
	 * Force admin-only emails if forum is hidden (topics).
	 *
	 * @var bool
	 */
	private $hidden_forum_topic_override = false;

	/**
	 * Force admin-only emails if forum is hidden (replies).
	 *
	 * @var bool
	 */
	private $hidden_forum_reply_override = false;

	/**
	 * The Email subject line for new topics.
	 *
	 * @var string
	 */
	private $newtopic_email_subject = '';

	/**
	 * The Email subject line for new replies.
	 *
	 * @var string
	 */
	private $newreply_email_subject = '';

	/**
	 * The Email body template for new topics.
	 *
	 * @var string
	 */
	private $newtopic_email_body = '';

	/**
	 * The Email body template for new replies.
	 *
	 * @var string
	 */
	private $newreply_email_body = '';

	/**
	 * Custom From name for outgoing emails.
	 *
	 * @var string
	 */
	private $from_name = '';

	/**
	 * Custom From email address for outgoing emails.
	 *
	 * @var string
	 */
	private $from_email = '';

	/**
	 * Internal vars.
	 *
	 * @var string
	 */
	/**
	 * Plugin text domain.
	 *
	 * @var string
	 */
	protected $domain;

	/**
	 * Human-readable option keys mapping.
	 *
	 * @var array
	 */
	protected $option_keys;



	/**
	 * The constructor - it overrides any default settings with whatever is in $params.
	 *
	 * @param array $params Parameters to set for this model.
	 */
	public function __construct( $params = array() ) {
				// Translate keys.
		$this->translate_options();

		$this->set_properties( $params );

		/**
		 * Some defaults
		 */
		$this->maybe_set_default( 'all' );
	}

	/**
	 * Sets default values for some properties.
	 *
	 * @param string $key Property to initialize; 'all' to initialize all.
	 * @return void
	 */
	public function maybe_set_default( $key = '' ) {
		if ( 'all' === $key || 'newtopic_email_subject' === $key ) {
			if ( empty( $this->newtopic_email_subject ) ) {
				$this->newtopic_email_subject = __( '[[blogname]] New topic: [topic-title]', 'bbpress-notify-nospam' );
			}
		}

		if ( 'all' === $key || 'newreply_email_subject' === $key ) {
			if ( empty( $this->newreply_email_subject ) ) {
				$this->newreply_email_subject = __( '[[blogname]] New reply for [topic-title]', 'bbpress-notify-nospam' );
			}
		}

		if ( 'all' === $key || 'newtopic_email_body' === $key ) {
			if ( empty( $this->newtopic_email_body ) ) {
				$this->newtopic_email_body = __( "Hello!\nA new topic has been posted by [topic-author].\nTopic title: [topic-title]\nTopic url: [topic-url]\n\nExcerpt:\n[topic-excerpt]", 'bbpress-notify-nospam' );
			}
		}

		if ( 'all' === $key || 'newreply_email_body' === $key ) {
			if ( empty( $this->newreply_email_body ) ) {
				$this->newreply_email_body = __( "Hello!\nA new reply has been posted by [reply-author].\nTopic title: [reply-title]\nTopic url: [reply-url]\n\nExcerpt:\n[reply-excerpt]", 'bbpress-notify-nospam' );
			}
		}
	}


	/**
	 * Make keys readable if displaying any errors.
	 *
	 * @return void
	 */
	public function translate_options() {
		$this->option_keys = array(
			'newtopic_email_subject'                     => __( 'New Topic Email Subject', 'bbpress-notify-nospam' ),
			'newreply_email_subject'                     => __( 'New Reply Email Subject', 'bbpress-notify-nospam' ),
			'newtopic_email_body'                        => __( 'New Topic Email Body', 'bbpress-notify-nospam' ),
			'newreply_email_body'                        => __( 'New Reply Email Body', 'bbpress-notify-nospam' ),
			'newtopic_recipients'                        => __( 'Recipients for New Topics', 'bbpress-notify-nospam' ),
			'newreply_recipients'                        => __( 'Recipients for New Replies', 'bbpress-notify-nospam' ),
			'encode_subject'                             => __( 'Encode Subject', 'bbpress-notify-nospam' ),
			'newtopic_background'                        => __( 'Notify New Topics in Background', 'bbpress-notify-nospam' ),
			'newreply_background'                        => __( 'Notify New Replies in Background', 'bbpress-notify-nospam' ),
			'background_notifications'                   => __( 'Background Notifications', 'bbpress-notify-nospam' ),
			'use_action_scheduler'                       => __( 'Use Action Scheduler', 'bbpress-notify-nospam' ),
			'default_topic_notification_checkbox'        => __( 'Default Topic Notification Checkbox', 'bbpress-notify-nospam' ),
			'default_reply_notification_checkbox'        => __( 'Default Reply Notification Checkbox', 'bbpress-notify-nospam' ),
			'override_bbp_forum_subscriptions'           => __( 'Override bbPress Forum Subscriptions', 'bbpress-notify-nospam' ),
			'override_bbp_topic_subscriptions'           => __( 'Override bbPress Topic Subscriptions', 'bbpress-notify-nospam' ),
			'include_bbp_forum_subscriptions_in_replies' => __( 'Also notify <em>forum</em> subscribers of new replies', 'bbpress-notify-nospam' ),
			'forums_auto_subscribe_to_topics'            => __( 'Automatically subscribe forum subscribers to new topics.', 'bbpress-notify-nospam' ),
			'forums_auto_subscribe_new_users'            => __( 'Automatically subscribe new users to all forums.', 'bbpress-notify-nospam' ),
			'notify_authors_topic'                       => __( 'Notify Authors of their Topics', 'bbpress-notify-nospam' ),
			'notify_authors_reply'                       => __( 'Notify Authors of their Replies', 'bbpress-notify-nospam' ),
			'hidden_forum_topic_override'                => __( 'Only Notify Admins if Forum is Hidden', 'bbpress-notify-nospam' ),
			'hidden_forum_reply_override'                => __( 'Only Notify Admins if Topic is Hidden', 'bbpress-notify-nospam' ),
			'email_type'                                 => __( 'Message Type', 'bbpress-notify-nospam' ),
			'from_name'                                  => __( 'From Name', 'bbpress-notify-nospam' ),
			'from_email'                                 => __( 'From Email', 'bbpress-notify-nospam' ),
		);
	}


	/**
	 * Our getter. Used mainly because we have to _validate the setter.
	 * Getter/setter magic methods only get called for private properties.
	 *
	 * @param string $key Property name to retrieve.
	 * @return mixed
	 */
	public function __get( $key ) {
		$value = $this->{$key};

		// Fix badly converted recipients array on the fly.
		if ( 'newtopic_recipients' === $key || 'newreply_recipients' === $key ) {
			if ( ! empty( $value ) && ! isset( $value[0] ) ) {
				$value = array_keys( $value );
			}
		} elseif ( ( 'newtopic_email_subject' === $key || 'newreply_email_subject' === $key ) && $this->encode_subject ) {
			// De-entitize HTML if UTF-8 subjects have been set.
			$value = html_entity_decode( $value );
		}

		return $value;
	}

	/**
	 * Magic isset handler so isset() works for private properties accessed via __get().
	 *
	 * @param string $key Property name.
	 * @return bool
	 */
	public function __isset( $key ) {
		if ( ! property_exists( $this, $key ) ) {
			return false;
		}

		$val = $this->{$key};

		return isset( $val );
	}

	/**
	 * Magic unset handler so unset() works for private properties accessed via __get/\__set.
	 *
	 * @param string $key Property name.
	 * @return void
	 */
	public function __unset( $key ) {
		if ( property_exists( $this, $key ) ) {
			$this->{$key} = null;
		}
	}

	/**
	 * Provide useful debug information for var_dump()/debugging.
	 *
	 * @return array
	 */
	public function __debugInfo() {
		$out = array();
		if ( isset( $this->option_keys ) && is_array( $this->option_keys ) ) {
			foreach ( array_keys( $this->option_keys ) as $k ) {
				$out[ $k ] = $this->{$k};
			}
		}
		return $out;
	}

	/**
	 * Serialize the model state.
	 *
	 * @return array
	 */
	public function __serialize(): array {
		return $this->as_array();
	}

	/**
	 * Unserialize the model state.
	 *
	 * @param array $data Serialized data.
	 * @return void
	 */
	public function __unserialize( array $data ): void {
		// Map provided array back into properties using existing setter/validation.
		$this->set_properties( $data );
	}

	/**
	 * Handle cloning: clear any runtime-only caches if present.
	 */
	public function __clone() {
		// Clear any ephemeral caches on clone.
		if ( isset( $this->option_keys ) ) {
			// noop for now; placeholder in case runtime caches are added later.
		}
	}


	/**
	 * Our setter, takes care of validating input.
	 *
	 * @param string $key Property name to set.
	 * @param mixed  $val Value to assign.
	 * @return void
	 */
	public function __set( $key, $val ) {
		if ( 'message_type' === $key ) {
			$key = 'email_type';
		}

		$val = $this->_validate( $key, $val );

		$this->{$key} = $val;
	}


	/**
	 * Property setter
	 *
	 * @param array $params
	 */
	/**
	 * Bulk property setter.
	 *
	 * @param array $params Parameters to set; keys are property names.
	 * @return void
	 */
	private function set_properties( $params = array() ) {
		foreach ( $params as $key => $val ) {
			$val = $this->_validate( $key, $val );

			$this->{$key} = $val;
		}
	}


	/**
	 * Used by the WP Settings API. See settings_dao.class.php
	 *
	 * @param string $key Property name to validate.
	 * @param mixed  $val Value to validate.
	 * @return bool True if valid, false otherwise.
	 */
	public function is_valid( $key, $val ) {
		// Return the validated value (or false) so callers can use
		// the sanitized value instead of a boolean sentinel.
		return $this->_validate( $key, $val, false );
	}


	/**
	 * Setter validation
	 *
	 * @param string  $key
	 * @param mixed   $val
	 * @param boolean $die_on_error - whether to throw wp_die() or return false on errors
	 * @return Ambigous <mixed, string, boolean>
	 */
	/**
	 * Validate a single property value.
	 *
	 * @param string $key Property name.
	 * @param mixed  $val Value to validate.
	 * @param bool   $die_on_error Whether to call wp_die() on error.
	 * @return mixed Validated value or false on failure.
	 */
	private function _validate( $key, $val, $die_on_error = true ) {
		if ( ! property_exists( $this, $key ) ) {
			if ( true === $die_on_error ) {
				/* Translators: %1$s is the property name. */
				wp_die( esc_html( sprintf( __( 'Invalid property %1$s for Settings Model', 'bbpress-notify-nospam' ), $key ) ) );
			}

			return false;
		}

				// Validate each key/value pair.
		switch ( $key ) {
			case 'encode_subject':
			case 'newtopic_background':
			case 'newreply_background':
			case 'background_notifications':
			case 'use_action_scheduler':
			case 'default_topic_notification_checkbox':
			case 'default_reply_notification_checkbox':
			case 'override_bbp_forum_subscriptions':
			case 'override_bbp_topic_subscriptions':
			case 'include_bbp_forum_subscriptions_in_replies':
			case 'forums_auto_subscribe_to_topics':
			case 'forums_auto_subscribe_new_users':
			case 'notify_authors_topic':
			case 'notify_authors_reply':
			case 'hidden_forum_topic_override':
			case 'hidden_forum_reply_override':
				$val = (bool) $val;
				break;
			case 'email_type':
				if ( ! in_array( $val, array( 'html', 'plain', 'multipart' ) ) ) {
					if ( true === $die_on_error ) {
						wp_die( esc_html( __( 'Invalid value for Message Type', 'bbpress-notify-nospam' ) ) );
					}

					return false;

					unset( $val );
				}
				break;
			case 'newtopic_recipients':
			case 'newreply_recipients':
				if ( ! is_array( $val ) ) {
					if ( true === $die_on_error ) {
						/* Translators: %1$s is the option label. */
						wp_die( esc_html( sprintf( __( 'Invalid data type for %1$s', 'bbpress-notify-nospam' ), $this->option_keys[ $key ] ) ) );
					}

					return false;

					$this->set_default( $key );
					$val = $this->{$key};
				}
				break;
			case 'newtopic_email_subject':
			case 'newreply_email_subject':
			case 'newtopic_email_body':
			case 'newreply_email_body':
				$val = trim( $val );
				if ( empty( $val ) ) {
					if ( true === $die_on_error ) {
						/* Translators: %1$s is the option label. */
						wp_die( esc_html( sprintf( __( '%1$s cannot be empty!', 'bbpress-notify-nospam' ), $this->option_keys[ $key ] ) ) );
					}

					return false;

					$this->set_default( $key );
					$val = $this->{$key};
				}
				break;
			case 'from_name':
			case 'from_email':
				$val = trim( $val );
				break;
			default:
				break;
		}

		return $val;
	}


	/**
	 * Return model properties as an associative array suitable for persistence.
	 *
	 * @return array Associative array of option keys to values.
	 */
	public function as_array() {
		$vars = array();

		foreach ( $this->option_keys as $key => $val ) {
			$vars[ $key ] = $this->{$key};
		}

		return $vars;
	}
}

/*
 * End of file settings_model.class.php
 * Location: bbpress-notify-nospam/includes/model/settings.class.php
 */
