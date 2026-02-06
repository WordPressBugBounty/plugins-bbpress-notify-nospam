<?php
/**
 * Abstract class for models.
 *
 * Low-risk documentation and formatting fixes applied.
 *
 * @package bbpress-notify-nospam
 */

defined( 'ABSPATH' ) || die( 'No direct access allowed' );

/* phpcs:disable PEAR.NamingConventions.ValidClassName.StartWithCapital,PEAR.NamingConventions.ValidClassName.Invalid */
/**
 * Abstract class for models.
 *
 * Historical class name is preserved for backwards compatibility.
 *
 * @package bbPress_Notify_NoSpam
 */
abstract class bbPress_Notify_noSpam_Model_Abstract_Model extends bbPress_Notify_noSpam {

	/**
	 * Force children to have a validation method.
	 *
	 * @param string $key Property name.
	 * @param mixed  $val Value to validate.
	 * @param bool   $die_on_error Whether to call wp_die() on error.
	 */
	abstract protected function validate( $key, $val, $die_on_error = true );

	/**
	 * Holds our properties. Gets set by child via register_properties().
	 *
	 * @var array
	 */
	protected $props;


	/**
	 * Our getter. Used mainly because we have to validate the setter.
	 * Getter/setter magic methods only get called for private properties.
	 *
	 * @param string $key Property name to retrieve.
	 * @return mixed Value of the requested property.
	 */
	public function __get( $key ) {
		return $this->props[ $key ];
	}


	/**
	 * Our setter, takes care of validating input.
	 *
	 * @param string $key Property name to set.
	 * @param mixed  $val Value to assign to the property.
	 * @return void
	 */
	public function __set( $key, $val ) {
		$this->is_registered( $key );

		$val = $this->validate( $key, $val );

		$this->props[ $key ] = $val;
	}


	/**
	 * Property setter
	 *
	 * @param array $params Parameters to set; keys are property names.
	 * @return void
	 */
	protected function set_properties( $params = array() ) {
		if ( is_object( $params ) && get_class( $params ) === get_class( $this ) ) {
			$params = $params->as_array();
		}

		foreach ( $params as $key => $val ) {
			$this->is_registered( $key );

			$val = $this->validate( $key, $val ); // Child method.

			$this->props[ $key ] = $val;
		}
	}


	/**
	 * Returns properties array. To be used when saving.
	 *
	 * @return array Properties for persistence.
	 */
	public function as_array() {
		$props = $this->props;
		unset( $props['props'], $props['domain'] );

		return $props;
	}


	/**
	 * Runs validation on a single key/value pair without dying
	 *
	 * @param string $key Property name to validate.
	 * @param mixed  $val Value to validate.
	 * @return bool True if valid, false otherwise.
	 */
	public function is_valid( $key, $val ) {
		if ( $this->is_registered( $key, false ) ) {
			return $this->validate( $key, $val, false );
		}

		return false;
	}


	/**
	 * Sets up the available properties of the child model
	 *
	 * @param array $defaults Default properties for the model.
	 * @return void
	 */
	public function register_properties( $defaults = array() ) {
		$this->props = $defaults;
	}

	/**
	 * Checks that the key being set has been registered.
	 *
	 * @param string $key Property name to check.
	 * @param bool   $die_on_error Whether to call wp_die() on error.
	 * @return bool True if registered, false otherwise.
	 */
	private function is_registered( $key, $die_on_error = true ) {
		// Check that the property is valid.
		if ( ! array_key_exists( $key, $this->props ) ) {
			if ( true === $die_on_error ) {
				/* Translators: %1$s is the property name, %2$s is the model class name. */
				$msg = sprintf( __( 'Invalid property %1$s for %2$s', 'bbpress-notify-nospam' ), esc_html( $key ), esc_html( get_class( $this ) ) );
				wp_die( esc_html( $msg ) );
			}

			return false;
		}

		return true;
	}

/* phpcs:enable PEAR.NamingConventions.ValidClassName.StartWithCapital,PEAR.NamingConventions.ValidClassName.Invalid */
}
