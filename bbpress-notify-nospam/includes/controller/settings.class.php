<?php defined( 'ABSPATH' ) or die( "No direct access allowed" );
/**
 * Controls plugin settings.
 * See http://codex.wordpress.org/Settings_API
 * 
 * @author vinnyalves
 */
class bbPress_Notify_noSpam_Controller_Settings extends bbPress_Notify_noSpam {

	protected $settings_dao;
	
	private $plugin_page;
	
	private $bridge_warnings = array();
	
	
	public function __construct()
	{
		// Make the settings filter always available
		$this->settings_dao = $this->load_lib( 'dal/settings_dao' );
		add_filter( get_parent_class( $this ) . '_settings', array( $this->settings_dao, 'load' ), 0, 10 );
		
		// From here on, admin only
		if ( ! parent::is_admin() )
			return;
		
		add_action( 'admin_menu', array( $this, 'add_menu_pages' ) );
		add_action( 'admin_init', array( $this, 'register_options' ) );
		
		add_filter( 'bbpnns_settings_pagehook', array( $this, 'get_page_hook' ) );
		add_filter( 'bbpnns_settings_available_topics_tags', array( $this, 'available_topics_tags' ), 1, 2 );
		add_filter( 'bbpnns_settings_available_reply_tags', array( $this, 'available_reply_tags' ), 1, 2 );
		
		add_filter( 'pre_update_option_' . $this->domain, array( $this, 'before_update_option' ), 10, 2 );
		
		add_action( 'plugins_loaded', array( $this, 'load_bridge_warnings' ), PHP_INT_MAX );
		
		add_filter( 'bbpnns-warnings', array( $this, 'get_bridge_warnings'), 10, 1 );
	}
	
	
	public function get_bridge_warnings( $warnings=array() )
	{
		$warnings = array_merge( $warnings, $this->bridge_warnings );
		
		return $warnings;
	}
	
	
	/**
	 * Checks if there are any plugins that need a bridge
	 */
	public function load_bridge_warnings()
	{
	    $active_plugins = array_flip( get_option( 'active_plugins', [] ) );
	    
	    $bridges = [
	        [
	            'bridge_name'  => 'bbPress Notify (No-Spam)/Private Groups Bridge',
	            'bridge_class' => 'bbpnns_private_groups_bridge',
	            'bridge_url'   => 'https://usestrict.net/product/bbpress-notify-no-spam-private-groups-bridge/?utm_source=bbpnnswarnings&utm_medium=web&utm_campaign=bridges',
	            'plays_with'   => 'Private Groups',
	            'has_player'   => isset( $active_plugins['bbp-private-groups/bbp-private-groups.php'] ),
	        ],
	        [
	            'bridge_name'  => 'bbPress Notify (No-Spam)/BuddyPress Bridge',
	            'bridge_class' => 'BbpnnsBuddypressBridge',
	            'bridge_url'   => 'https://usestrict.net/product/bbpress-notify-no-spam-buddypress-bridge/?utm_source=bbpnnswarnings&utm_medium=web&utm_campaign=bridges',
	            'plays_with'   => 'BuddyPress',
	            'has_player'   => isset( $active_plugins['buddypress/bp-loader.php'] ),
	        ],
	        [
	            'bridge_name'  => 'bbPress Notify (No-Spam)/MemberPress Bridge',
	            'bridge_class' => 'bbpnns_memberpress_bridge',
	            'bridge_url'   => 'https://usestrict.net/product/bbpress-notify-no-spam-memberpress-bridge/?utm_source=bbpnnswarnings&utm_medium=web&utm_campaign=bridges',
	            'plays_with'   => 'MemberPress',
	            'has_player'   => isset( $active_plugins['memberpress/memberpress.php'] ),
	        ],
	        [
	            'bridge_name'  => 'bbPress Notify (No-Spam)/LearnDash Bridge',
	            'bridge_class' => 'BbpnnsLearndashBridge',
	            'bridge_url'   => 'https://usestrict.net/product/bbpress-notify-no-spam-learndash-bridge/?utm_source=bbpnnswarnings&utm_medium=web&utm_campaign=bridges',
	            'plays_with'   => 'LearnDash with bbPress support',
	            'has_player'   => isset( $active_plugins['learndash-bbpress/learndash-bbpress.php'] ),
	        ],
	        [
	            'bridge_name'  => 'bbPress Notify (No-Spam)/BP Moderation Tools Bridge',
	            'bridge_class' => 'bbpnns_bpmts_bridge',
	            'bridge_url'   => 'https://usestrict.net/product/bbpress-notify-no-spam-bp-moderation-tools-bridge/?utm_source=bbpnnswarnings&utm_medium=web&utm_campaign=bridges',
	            'plays_with'   => 'BP Moderation Tools, by BuddyDev',
	            'has_player'   => isset( $active_plugins['buddypress-moderation-tools/bp-moderation-tools.php'] ),
	        ],
	        [
	            'bridge_name'  => 'bbPress Notify (No-Spam)/WPFusion Bridge',
	            'bridge_class' => 'Bbpnns_Wpfusion_Bridge',
	            'bridge_url'   => 'https://usestrict.net/product/bbpress-notify-no-spam-wpfusion-bridge/?utm_source=bbpnnswarnings&utm_medium=web&utm_campaign=bridges',
	            'plays_with'   => 'WPFusion',
	            'has_player'   => isset( $active_plugins['wp-fusion/wp-fusion.php'] ),
	        ],
	        [
	            'bridge_name'  => 'bbPress Notify (No-Spam) – PMPro Bridge',
	            'bridge_class' => 'Bbpnns_Pmpro_Bridge',
	            'bridge_url'   => 'https://usestrict.net/product/bbpress-notify-no-spam-pmpro-bridge/?utm_source=bbpnnswarnings&utm_medium=web&utm_campaign=bridges',
	            'plays_with'   => 'Paid Memberships Pro',
	            'has_player'   => isset( $active_plugins['paid-memberships-pro/paid-memberships-pro.php'] ),
	        ],
	        [
	            'bridge_name'  => 'bbPress Notify (No-Spam)/AccessAlly Bridge',
	            'bridge_class' => 'Bbpnns_AccessAlly_Bridge',
	            'bridge_url'   => 'https://usestrict.net/product/bbpress-notify-no-spam-accessally-bridge/?utm_source=bbpnnswarnings&utm_medium=web&utm_campaign=bridges',
	            'plays_with'   => 'AccessAlly',
	            'has_player'   => isset( $active_plugins['accessally/accessally.php'] ),
	        ],
	        [
	            'bridge_name'  => 'bbPress Notify (No-Spam)/Ultimate Member Bridge',
	            'bridge_class' => 'bbpnnsUMBridge',
	            'bridge_url'   => 'https://usestrict.net/product/bbpress-notify-no-spam-ultimate-member-bridge/?utm_source=bbpnnswarnings&utm_medium=web&utm_campaign=bridges',
	            'plays_with'   => 'Ultimate Member',
	            'has_player'   => isset( $active_plugins['ultimate-member/ultimate-member.php'] ),
	        ],
	        [
	            'bridge_name'  => 'bbPNNS/bbPress Moderation Plugin Bridge',
	            'bridge_class' => 'Bbpnns_Moderation',
	            'bridge_url'   => 'https://usestrict.net/product/bbpnns-bbpress-moderation-plugin-bridge/?utm_source=bbpnnswarnings&utm_medium=web&utm_campaign=bridges',
	            'plays_with'   => 'bbPress Moderation',
	            'has_player'   => isset( $active_plugins['bbpressmoderation/bbpressmoderation.php'] ),
	        ],
	    ];
	
	    /* translators: 1: Name of Bridged plugin 2: Name of Bridge 3: Bridge URL 4: Name of Bridged plugin */
	    $message = __( '<strong>WARNING</strong>, you are using <strong>%1$s</strong> but do not have <strong>%2$s</strong> installed/active. ' .
	        'Setting role-based notifications will send messages to *all* '.
	        'members of the selected roles. <a href="%3$s" target="_blank">Click here</a> to get the add-on so that bbpnns and %4$s can play nicely.', 'bbpress-notify-nospam' ) ;

		foreach( $bridges as $bridge )
		{
			if ( true === $bridge['has_player'] && ! class_exists( $bridge['bridge_class'] ) )
			{
				$this->bridge_warnings[] = sprintf( $message, $bridge['plays_with'], $bridge['bridge_name'], $bridge['bridge_url'], $bridge['plays_with'] );
			}
		}
	}
	
	
	/**
	 * Returns the pagehook we set at add_options_page
	 */
	public function get_page_hook()
	{
		return $this->plugin_page;
	}
	
	
	/**
	 * 
	 * @param array $new_value
	 * @param array $old_value
	 * @return array
	 */
	public function before_update_option( $new_value, $old_value )
	{
		$newer_value = wp_parse_args( $new_value, $old_value );
		
		if ( isset( $_POST['bbpnns_nullable_fields'] ) )
		{
			$fields = explode(',', $_POST['bbpnns_nullable_fields']);
			foreach ( $fields as $field )
			{
				if ( ! isset( $new_value[$field] ) )
				{
					unset( $newer_value[$field] );
					
					if ( 'background_notifications' === $field )
					{
						unset($newer_value['newtopic_background']);
						unset($newer_value['newreply_background']);
					}
				}
			}
		}
		
		return $newer_value;
	}
	
	
	/**
	 * Returns the available tags for topics
	 * @param array $tags
	 * @return array
	 */
	public function available_topics_tags( $tags, $for )
	{
	    $tags = apply_filters('bbpnns_available_topic_tags', [], $for );

	    return $this->_build_linked_tags($tags);
	}
	
	
	/**
	 * Returns the available tags for replies
	 * @since 1.10
	 */
	public function available_reply_tags( $tags, $for )
	{
	    $tags = apply_filters('bbpnns_available_reply_tags', [], $for );
		
		return $this->_build_linked_tags($tags);
	}
	
	
	/**
	 * Builds insertable TinyMCE links 
	 */
	private function _build_linked_tags( $tags )
	{
	    $linked_tags = array();
	    
	    if ( ! is_array( $tags ) )
	    {
	        $tags = preg_replace( '/\s*,\s*/', ',', $tags ); // Remove any spaces
	        $tags = explode( ',', $tags ); // Make sure it's an array
	    }
	    
	    foreach ( $tags as $t )
	    {
	        $link_tags[] = sprintf('<a href="#" class="bbpnns_tinymce_tag" data-insert-tag="%s">%s</a>', esc_attr($t), esc_html($t) );
	    }
	    
	    return $link_tags;
	}
	
	
	/**
	 * Set up the admin menus
	 */
	public function add_menu_pages()
	{
		$this->add_main_menu();
		$this->add_settings_submenu();
		$this->add_addons_submenu();
		
		// Add our custom menu styles (like orange 'Add-ons' Menu item
		wp_enqueue_style( 'bbppnns-menu-css', $this->get_env()->css_url . 'admin_menu.css' );
	}
	
	
	/**
	 * Adds the main Admin menu
	 */
	public function add_main_menu()
	{
		$env = $this->get_env();
		
		$plugin_data = ( object ) get_plugin_data( $env->plugin_file );
		$view = $this->load_lib( 'view/settings' );
		
		$id = add_menu_page(
				__( 'bbPress Notify (No-Spam)', 'bbpress-notify-nospam' ) ,  // page title
				__( 'bbPress Notify (No-Spam)', 'bbpress-notify-nospam' ) ,  // menu title
				'manage_options',          				          // capability
				$this->domain,           			              // menu slug
				array( $view, 'show_admin'),		              // the callback
				'dashicons-email-alt'
		);
		
		add_action( 'admin_head-' . $id, array( $view,'add_admin_css' ) );
		add_action( 'admin_head-' . $id, array( $view,'add_admin_js' ) );
	}
	
	
	/**
	 * Adds the settings submenu item, pointing to the same place as the main menu
	 */	
	public function add_settings_submenu()
	{
		$view = $this->load_lib( 'view/settings' );
		
		$title    = __( 'Settings' );
		$caps     = 'manage_options';
		$callback = array( $view, 'show_admin' );
			
		$id = add_submenu_page(
				$this->domain,      // Top level slug
				$title,      		// Page Title
				$title,      		// Menu Title
				$caps, 				// Capability
				$this->domain,		// Menu Slug
				$callback			// Callback
		);
		
		$this->plugin_page = $id;
		
		add_action( 'admin_head-' . $id, array( $view,'add_admin_css' ) );
		add_action( 'admin_head-' . $id, array( $view,'add_admin_js' ) );
	}
	
	
	/**
	 * Creates the addons submenu item
	 */
	public function add_addons_submenu()
	{
		$view = $this->load_lib( 'view/settings' );
		
		$title = __( 'Add-ons', 'bbpress-notify-nospam' ) ;
		$caps  = 'manage_options';
		$callback = array( $view, 'show_addons_page' );
		
		$id = add_submenu_page(
				$this->domain,      // Top level slug
				$title,      		// Page Title
				$title,      		// Menu Title
				$caps, 				// Capability
				$this->domain . '-addons',		// Menu Slug
				$callback			// Callback
		);
		
		add_action( 'admin_head-' . $id, array( $view,'add_admin_css' ) );
		add_action( 'admin_head-' . $id, array( $view,'add_admin_js' ) );
	}
	
	
	/**
	 * Whitelists our settings
	 */
	public function register_options()
	{
		/**
		 * We've moved message, for legacy position
		 */
		add_settings_section( 'legacy_bbpress_notify_options', __( 'E-mail Notifications', 'bbpress-notify-nospam' ) , array( $this, 'weve_moved' ), 'bbpress' );
		
		/**
		 * Register our settings
		 */
		register_setting( $this->settings_name, $this->settings_name, array( $this->settings_dao, 'validate_settings' ) );
	}
	
	
	/**
	 * New location notification in legacy settings screen.
	 * @param array $args
	 */
	public function weve_moved( $args )
	{
		printf( '<span id="%s">%s</span>', $args['id'], sprintf( __( 'Looking for bbPress Notify (No-Spam) settings? We\'ve moved. <a href="%s">Check out our new settings page.</a>', 'bbpress-notify-nospam' ) , 
					admin_url( 'admin.php?page=' . $this->domain) ) );
	}
}

/* End of file settings.class.php */
/* Location: bbpress-notify-nospam/includes/controller/settings.class.php */
