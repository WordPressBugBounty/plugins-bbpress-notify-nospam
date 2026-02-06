<?php
/**
 * BbPress Notify main class (moved for v3 loader refactor).
 *
 * Low-risk docblocks added by automated PHPCS run.
 *
 * @package bbPress_Notify_noSpam
 */

defined( 'ABSPATH' ) || exit;

/**
 * Legacy main plugin class kept for backward compatibility.
 *
 * @phpcsSuppress PEAR.NamingConventions.ValidClassName.Invalid -- Legacy class name kept for backward compatibility.
 * @phpcsSuppress PEAR.NamingConventions.ValidClassName.StartWithCapital -- Legacy class name kept for backward compatibility.
 */
class bbPress_Notify_noSpam /* phpcs:ignore PEAR.NamingConventions.ValidClassName.StartWithCapital,PEAR.NamingConventions.ValidClassName.Invalid */ {
	const VERSION = '3.0.1';

	/**
	 * The singletons
	 *
	 * @var array
	 */
	public static $instances = array();

	/**
	 * The domain to be used for l10n. Defaults to the parent class name
	 *
	 * @var string
	 */
	public $domain = __CLASS__;

	/**
	 * Holds the environment object once set_env() is called
	 *
	 * @var object
	 */
	protected static $env;

	/**
	 * The name of the key in wp_options table that holds our settings
	 *
	 * @var string
	 */
	protected $settings_name = __CLASS__;

	/**
	 * Holds library singletons
	 *
	 * @var object
	 */
	protected $libs;

	/**
	 * Constructor.
	 *
	 * @param array $params Optional params to pass to bootstrap.
	 */
	public function __construct( $params = array() ) {
		$this->set_env();

		if ( self::is_admin() ) {
			$notices = $this->load_lib( 'controller/admin_notices' );
			$this->load_lib( 'controller/ajax' );

			register_activation_hook( $this->get_env()->plugin_file, array( $this, 'do_activation' ) );
			register_deactivation_hook( $this->get_env()->plugin_file, array( $this, 'do_deactivation' ) );
		} elseif ( defined( 'DOING_CRON' ) && DOING_CRON ) {
			set_time_limit( 0 );
		}

		add_action( 'init', array( $this, 'load_textdomain' ), 0 );
		add_action( 'init', array( $this, 'init' ), 1 );
	}

	/**
	 * Load plugin textdomain.
	 */
	public function load_textdomain() {
		load_plugin_textdomain( 'bbpress-notify-nospam', false, dirname( untrailingslashit( plugin_basename( $this->get_env()->plugin_file ) ) ) . '/lang' );
	}

	/**
	 * Initialize plugin (load controllers/libraries as needed).
	 */
	public function init() {
		$this->load_lib( 'controller/settings' );
		$this->load_lib( 'controller/login' );
		$this->load_lib( 'controller/common_core' );

		if ( self::is_admin() ) {
			$did_v2_conversion = get_option( 'bbpnns_v2_conversion_complete', false );

			if ( false === $did_v2_conversion ) {
				$has_v1_data = get_option( 'bbpress_notify_newtopic_email_subject', false );

				if ( false !== $has_v1_data ) {
					$converter = $this->load_lib( 'helper/converter' );

					if ( isset( $_GET['bbpnns_force_convert'] ) && filter_var( wp_unslash( $_GET['bbpnns_force_convert'] ), FILTER_VALIDATE_BOOLEAN ) && current_user_can( 'manage_options' ) ) {
						$status = $converter->do_db_upgrade();

						if ( true === $status ) {
							wp_die(
								wp_kses_post(
									sprintf(
										/* translators: 1: admin URL */
										__( 'bbPress Notify (No-Spam) 1.x -> 2.x conversion was successful. Click <a href="%s">here</a> to go back to your WP Admin.', 'bbpress-notify-nospam' ),
										esc_url( admin_url( '/' ) )
									)
								),
								200
							);
						}
					} else {
						$notices = $this->load_lib( 'controller/admin_notices' );
						$notices->set_notice( 'bbpnns_v2_conversion_needed' );
					}
				}
			}

			$this->load_lib( 'controller/admin_core' );
		}
	}

	/**
	 * Proxy to common_core send_notification().
	 *
	 * @param mixed  $recipients Recipients list.
	 * @param string $subject    Subject text.
	 * @param string $body       Body content.
	 * @param string $type       Notification type.
	 * @param mixed  $post_id    Post ID.
	 * @param mixed  $forum_id   Forum ID.
	 * @return mixed
	 */
	public function send_notification( $recipients, $subject, $body, $type = '', $post_id = '', $forum_id = '' ) {
		return $this->load_lib( 'controller/common_core' )->send_notification( $recipients, $subject, $body, $type, $post_id, $forum_id );
	}

	/**
	 * Get forum post type name.
	 *
	 * @return string
	 */
	public function get_forum_post_type() {
		static $forum_post_type;
		if ( ! $forum_post_type ) {
			$forum_post_type = bbp_get_forum_post_type();
		}

		return $forum_post_type;
	}

	/**
	 * Get topic post type name.
	 *
	 * @return string
	 */
	public function get_topic_post_type() {
		static $topic_post_type;
		if ( ! $topic_post_type ) {
			$topic_post_type = bbp_get_topic_post_type();
		}

		return $topic_post_type;
	}

	/**
	 * Get reply post type name.
	 *
	 * @return string
	 */
	public function get_reply_post_type() {
		static $reply_post_type;
		if ( ! $reply_post_type ) {
			$reply_post_type = bbp_get_reply_post_type();
		}

		return $reply_post_type;
	}

	/**
	 * Activation hook.
	 */
	public function do_activation() {
		if ( ! class_exists( 'bbPress' ) ) {
			deactivate_plugins( plugin_basename( $this->get_env()->plugin_file ) );
			wp_die( esc_html__( 'Sorry, you need to activate bbPress first.', 'bbpress-notify-nospam' ) );
		}
	}

	/**
	 * Deactivation hook.
	 *
	 * No-op kept for BC.
	 */
	public function do_deactivation() {
		return;
	}

	/**
	 * Bootstrap the plugin singletons.
	 *
	 * @param array $params Optional bootstrap params.
	 * @return mixed
	 */
	public static function bootstrap( $params = array() ) {
		if ( ! class_exists( 'bbPress' ) ) {
			if ( 'plugins_loaded' !== current_filter() ) {
				add_action( 'plugins_loaded', array( 'bbPress_Notify_noSpam', 'bootstrap' ), 100000 );
			} else {
				add_action( 'admin_notices', array( 'bbPress_Notify_noSpam', 'missing_bbpress_notice' ) );
			}

			return false;
		}

		$class = function_exists( 'get_called_class' ) ? get_called_class() : self::get_called_class();

		if ( ! isset( self::$instances[ $class ] ) ) {
			self::$instances[ $class ] = new $class( $params );
		}

		return self::$instances[ $class ];
	}

	/**
	 * Admin notice displayed when bbPress is missing.
	 */
	public static function missing_bbpress_notice() {
		?>
		<div class="error">
			<p>
				<?php echo wp_kses_post( __( '<strong>bbPress Notify (No-Spam)</strong> could not find an active bbPress plugin. It will not load until bbPress is installed and active.', 'bbpress-notify-nospam' ) ); ?>
			</p>
		</div>
		<?php
	}

	/**
	 * Compatibility shim for get_called_class() where unavailable.
	 *
	 * @return string
	 */
	private static function get_called_class() {
		$bt = debug_backtrace();

		if ( is_array( $bt[2]['args'] ) && 2 === count( $bt[2]['args'] ) ) {
			return $bt[2]['args'][0][0];
		}

		return $bt[1]['class'];
	}

	/**
	 * Initialize environment object and paths.
	 */
	protected function set_env() {
		// Plugin root is two levels up from this file (includes/controller).
		$root       = trailingslashit( dirname( dirname( __DIR__ ) ) );
		$plugin_url = trailingslashit( plugins_url( 'assets', dirname( dirname( __DIR__ ) ) . '/bbpress-notify-nospam.php' ) );

		self::$env = (object) array(
			'root_dir'    => $root,
			'inc_dir'     => $root . 'includes/',
			'tmpl_dir'    => $root . 'includes/view/templates/',
			'js_url'      => $plugin_url . 'js/',
			'css_url'     => $plugin_url . 'css/',
			'img_url'     => $plugin_url . 'img/',
			'plugin_file' => dirname( dirname( __DIR__ ) ) . '/bbpress-notify-nospam.php',
		);
	}

	/**
	 * Retrieve the environment object, initializing if needed.
	 *
	 * @return object
	 */
	protected function get_env() {
		if ( ! isset( self::$env ) ) {
			$this->set_env();
		}

		return self::$env;
	}

	/**
	 * Load a library/class by logical name and return its instance.
	 *
	 * @param string $name        Logical name like 'controller/ajax' or 'dal/settings_dao'.
	 * @param array  $params      Optional constructor params.
	 * @param bool   $force_reload If true, force a new instance.
	 * @return object
	 */
	public function load_lib( $name, $params = array(), $force_reload = false ) {
		// Normalize the logical name so callers can pass either
		// `controller/ajax`, `controller/ajax`, `dal/settings_dao` or `dal/settings-dao`.
		$norm_key = str_replace( array( '/', '-' ), '_', $name );

		// Return cached instance if present and not forcing reload.
		if ( isset( $this->libs ) && isset( $this->libs->{$norm_key} ) && false === $force_reload ) {
			return $this->libs->{$norm_key};
		}

		if ( ! isset( $this->libs ) ) {
			$this->libs = (object) array();
		}

		// Build the expected class name by converting the logical name to
		// an underscored suffix, and prefixing with this class name.
		$classname = __CLASS__ . '_' . str_replace( array( '/', '-' ), '_', $name );

		// Trigger autoload and verify the class exists.
		if ( ! class_exists( $classname, true ) ) {
			$bt         = debug_backtrace();
			$debug_info = array(
				'file'   => isset( $bt[0]['file'] ) ? $bt[0]['file'] : '',
				'line'   => isset( $bt[0]['line'] ) ? $bt[0]['line'] : '',
				'method' => isset( $bt[0]['function'] ) ? $bt[0]['function'] : '',
			);

			wp_die( sprintf( 'Cannot find Lib class: %s Debug:<pre>%s</pre>', esc_html( $classname ), esc_html( wp_json_encode( $debug_info ) ) ) );
		}

		// Always instantiate the class directly; do not call a `bootstrap()` factory.
		$this->libs->{$norm_key} = new $classname( $params );

		return $this->libs->{$norm_key};
	}

	/**
	 * Load all classes from a directory and return instantiated objects.
	 *
	 * @param string $dir Directory relative to includes/ or absolute.
	 * @return array
	 */
	protected function load_all( $dir ) {
		$inc_dir = $this->get_env()->inc_dir;

		if ( false === ( strstr( $dir, $inc_dir ) ) ) {
			$dir = $inc_dir . '/' . $dir;
		}

		$dir = str_replace( '//', '/', $dir );
		$dir = preg_replace( ',/$,', '', $dir );

		$loaded = array();

		// Support both legacy and new class filename patterns.
		$files = array_merge( glob( $dir . '/*.class.php' ), glob( $dir . '/class-*.php' ) );

		foreach ( $files as $file ) {
			// Derive a logical name relative to includes.
			$rel = str_replace( $inc_dir, '', $file );
			$rel = preg_replace( '#^/#', '', $rel );
			$rel = preg_replace( '#^controller/class-#', 'controller/', $rel );
			$rel = preg_replace( '#^class-#', '', $rel );
			$rel = preg_replace( '/\.class\.php$|\.php$/', '', $rel );

			$loaded[ $rel ] = $this->load_lib( $rel );
		}

		return $loaded;
	}

	/**
	 * Render a template by name with an optional stash of variables.
	 *
	 * @param string $name  Template name (with or without .tmpl.php).
	 * @param array  $stash Variables to expose to the template.
	 * @param bool   $debug If true, echo the template path for debugging.
	 */
	public function render_template( $name, $stash = array(), $debug = false ) {
		$env = $this->get_env();

		if ( '.tmpl.php' !== substr( $name, -9 ) ) {
			$name .= '.tmpl.php';
		}

		if ( ! file_exists( $env->tmpl_dir . $name ) ) {
			wp_die( sprintf( 'Bad template request: %s', esc_html( $env->tmpl_dir . $name ) ) );
		}

		$stash = (object) $stash;

		if ( true === $debug ) {
			echo esc_html( $env->tmpl_dir . '/' . $name );
		}

		include $env->tmpl_dir . $name;
	}

	/**
	 * Wrapper that allows filtering the admin check for testing.
	 *
	 * @return bool
	 */
	public static function is_admin() {
		if ( has_filter( __CLASS__ . '_is_admin' ) ) {
			return apply_filters( __CLASS__ . '_is_admin', false );
		} else {
			return is_admin();
		}
	}

	/**
	 * Append a debug message to the plugin log if WP_DEBUG is enabled.
	 *
	 * @param mixed $msg Message or data to log.
	 */
	public function log_msg( $msg ) {
		if ( ! ( defined( 'WP_DEBUG' ) && WP_DEBUG ) ) {
			return;
		}

		$log_path = $this->get_env()->root_dir . 'log.txt';

		if ( is_scalar( $msg ) ) {
			$body = (string) $msg;
		} else {
			$body = print_r( $msg, true );
		}

		$line = '[' . gmdate( 'd/m/Y H:i:s' ) . '] ' . $body . PHP_EOL;

		// Use file_put_contents with lock to ensure the log file is created
		// and written to reliably in CLI/test environments.
		@file_put_contents( $log_path, $line, FILE_APPEND | LOCK_EX );
	}

	/**
	 * Normalize a WP_User or ID into a simple user info object.
	 *
	 * @param mixed $user_obj_or_id WP_User instance or numeric ID or object with ID.
	 * @return object
	 */
	public function user_info( $user_obj_or_id ) {

		if ( is_int( $user_obj_or_id ) ) {
			$user = new WP_User( $user_obj_or_id );
		} elseif ( $user_obj_or_id instanceof WP_User ) {
			$user = $user_obj_or_id;
		} else {
			$user_id = 0;

			if ( is_object( $user_obj_or_id ) && isset( $user_obj_or_id->ID ) ) {
				$user_id = (int) $user_obj_or_id->ID;
			} elseif ( is_numeric( $user_obj_or_id ) ) {
				$user_id = (int) $user_obj_or_id;
			}

			$user = $user_id ? new WP_User( $user_id ) : new WP_User( 0 );
		}

		$properties = array( 'ID', 'user_email', 'first_name', 'last_name', 'display_name', 'user_nicename' );

		$user_info = (object) array();

		foreach ( $properties as $prop ) {
			$user_info->{$prop} = isset( $user->{$prop} ) ? $user->{$prop} : '';
		}

		$user_info->roles = isset( $user->roles ) ? (array) $user->roles : array();

		return $user_info;
	}

	/**
	 * Is this request a dry-run (no outbound notifications)?
	 *
	 * @return bool
	 */
	public function is_dry_run() {
		return apply_filters( 'bbpnns_dry_run', false );
	}

	/**
	 * Get plugin settings object.
	 *
	 * This centralizes access to settings so callers use a method instead of
	 * calling `apply_filters()` directly to fetch settings. The filter is
	 * applied at the end so users can modify the final settings object.
	 *
	 * @return object|array
	 */
	public function get_settings() {
		$settings = $this->load_lib( 'dal/settings_dao' )->load();

		/**
		 * Allow modification of the resolved settings object.
		 *
		 * @since 3.0.0
		 */
		return apply_filters( 'bbPress_Notify_noSpam_settings', $settings ); // phpcs:ignore WordPress.NamingConventions.ValidHookName.NotLowercase
	}
}

