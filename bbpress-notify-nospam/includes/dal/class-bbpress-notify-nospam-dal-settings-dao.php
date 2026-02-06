<?php
/**
 * Datalayer for plugin settings.
 *
 * Provides accessors to load, validate, and save plugin settings.
 *
 * @package bbPress_Notify_Nospam
 * @author  vinnyalves
 */

// phpcs:disable PEAR.NamingConventions.ValidClassName.StartWithCapital,PEAR.NamingConventions.ValidClassName.Invalid

defined( 'ABSPATH' ) || die( 'No direct access allowed' );

/**
 * Settings DAO.
 *
 * Kept with historical class name for BC; suppress naming sniff.
 *
 * @since 1.0.0
 * @phpcsSuppress PEAR.NamingConventions.ValidClassName.StartWithCapital
 * @phpcsSuppress PEAR.NamingConventions.ValidClassName.Invalid
 */
class bbPress_Notify_noSpam_DAL_Settings_Dao extends bbPress_Notify_noSpam {

	/**
	 * Associative array where we store our cached Settings Model Object
	 *
	 * @var object
	 */
	private $cache = array();


	/**
	 * Constructor.
	 *
	 * Intentionally empty to avoid parent initialization during unit tests.
	 *
	 * @return void
	 */
	public function __construct() {
		// NOOP. We don't want PHP to call the parent automatically.
	}

	/**
	 * Load the settings model from the database (cached).
	 *
	 * @return bbPress_Notify_noSpam_Model_Settings
	 */
	public function load() {
		if ( ! isset( $this->cache[ $this->settings_name ] ) ) {
			$this->load_lib( 'model/settings' );

			$db_params = get_option( $this->settings_name, array() );

			// Check that WP didn't return false for settings not found.
			if ( false === $db_params ) {
				$db_params = array();
			}

			$this->cache[ $this->settings_name ] = new bbPress_Notify_noSpam_Model_Settings( $db_params );
		}

		return $this->cache[ $this->settings_name ];
	}

	/**
	 * Validate posted settings and return the sanitized array.
	 *
	 * @param array $_post Raw posted settings to validate.
	 *
	 * @return array Sanitized settings array.
	 */
	public function validate_settings( $_post ) {
		$settings_model = $this->load_lib( 'model/settings' );

		foreach ( $_post as $key => $value ) {
			$validated = $settings_model->is_valid( $key, $value );
			if ( false === $validated ) {
				unset( $_post[ $key ] );
			} else {
				$_post[ $key ] = $validated;
			}
		}

		return $_post;
	}


	/**
	 * Persist settings to the options table.
	 *
	 * This is primarily used in tests; production code uses the API.
	 *
	 * @param bbPress_Notify_noSpam_Model_Settings $settings_model Settings model instance.
	 *
	 * @return void
	 */
	public function save( bbPress_Notify_noSpam_Model_Settings $settings_model ) {
		update_option( $this->settings_name, $settings_model->as_array() );

		$this->cache[ $this->settings_name ] = $settings_model;
	}
}

// phpcs:enable PEAR.NamingConventions.ValidClassName.StartWithCapital,PEAR.NamingConventions.ValidClassName.Invalid

/*
 * End of file settings_dao.class.php
 */
/* Location: bbpress-notify-nospam/includes/dal/settings_dao.class.php */
