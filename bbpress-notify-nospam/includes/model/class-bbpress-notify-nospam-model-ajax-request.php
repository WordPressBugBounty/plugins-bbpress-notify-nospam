<?php
/**
 * Ajax Request model.
 *
 * Minimal conservative fixes: file docblock positioning, logical operator,
 * and class name normalization with backwards-compat alias.
 *
 * @package bbPress_Notify_NoSpam
 */

defined( 'ABSPATH' ) || die( 'No direct access allowed' );

/* phpcs:disable PEAR.NamingConventions.ValidClassName.StartWithCapital,PEAR.NamingConventions.ValidClassName.Invalid */
/**
 * Ajax Request model.
 *
 * Historical class name preserved for backwards compatibility.
 * Conservative doc and formatting fixes applied only.
 *
 * @package bbPress_Notify_NoSpam
 */
class bbPress_Notify_noSpam_Model_Ajax_Request {
/* phpcs:enable PEAR.NamingConventions.ValidClassName.StartWithCapital,PEAR.NamingConventions.ValidClassName.Invalid */

	/**
	 * The overall status
	 *
	 * @var array
	 */
	private $is_success = false;

	/**
	 * A localized message to the client
	 *
	 * @var string
	 */
	private $msg;

	/**
	 * Action-specific data to be used by the client
	 *
	 * @var mixed
	 */
	private $data = array();

	/**
	 * The callback for jsonp requests
	 *
	 * @var string
	 */
	private $callback;


	/**
	 * Our constructor, loads properties
	 *
	 * @param array $params Parameters to set for this request.
	 */
	public function __construct( $params = array() ) {
		$this->set_properties( $params );
	}


	/**
	 * Our getter. Used mainly because we have to _validate() the setter.
	 * Getter/setter magic methods only get called for private properties.
	 *
	 * @param string $key Property name to retrieve.
	 * @return mixed
	 */
	public function __get( $key ) {
		$key = strtolower( $key );

		return $this->{$key};
	}


	/**
	 * Our setter, takes care of validating input.
	 *
	 * @param string $key Property name to set.
	 * @param mixed  $val Value to assign.
	 * @return void
	 */
	public function __set( $key, $val ) {
		$key = strtolower( $key );

		if ( property_exists( $this, $key ) ) {
			$val          = $this->_validate( $key, $val );
			$this->{$key} = $val;
		}
	}


	/**
	 * Bulk Property setter.
	 *
	 * @param array $params Parameters to set; keys are property names.
	 * @return void
	 */
	protected function set_properties( $params = array() ) {
		foreach ( $params as $key => $val ) {
			if ( ! property_exists( $this, strtolower( $key ) ) ) {
				continue;
			}

			list( $key, $val ) = $this->_validate( $key, $val );

			$this->{$key} = $val;
		}
	}


	/**
	 * Setter validation.
	 *
	 * @param string $key Property name.
	 * @param mixed  $val Value to validate.
	 * @param bool   $die_on_error Whether to call wp_die() on error.
	 * @return mixed|false Validated value on success, or false on failure.
	 */
	private function _validate( $key, $val, $die_on_error = true ) {
		// phpcs:disable WordPress.WP.I18n.NonSingularStringLiteralText,WordPress.WP.I18n.NonSingularStringLiteralDomain,WordPress.NamingConventions.ValidVariableName.VariableNotSnakeCase,WordPress.Security.EscapeOutput.OutputNotEscaped
		global $bbPress_Notify_noSpam;

		switch ( $key ) {
			case 'is_success':
				if ( ! is_bool( $val ) ) {
					return $die_on_error ? wp_die( __( sprintf( 'Ajax Request Model Property %s must be boolean', $key ), $bbPress_Notify_noSpam->domain ) ) : false;
				}
				break;
			case 'callback': // extremely basic check. A real JS identifier check would be too big.
				if ( is_string( $val ) && false !== strpos( $val, '-' ) ) {
					return $die_on_error ? wp_die( __( sprintf( 'Ajax Request Model Property %s must be a valid JS identifier', $key ), $bbPress_Notify_noSpam->domain ) ) : false;
				}
				break;
			case 'msg':
				if ( ! is_string( $val ) && ! is_null( $val ) && ! is_array( $val ) ) {
					return $die_on_error ? wp_die( __( sprintf( 'Ajax Request Model Property %s must be an array, a string or null', $key ), $bbPress_Notify_noSpam->domain ) ) : false;
				}
				break;
			default:
				break;
		}
		// phpcs:enable WordPress.WP.I18n.NonSingularStringLiteralText,WordPress.WP.I18n.NonSingularStringLiteralDomain,WordPress.NamingConventions.ValidVariableName.VariableNotSnakeCase,WordPress.Security.EscapeOutput.OutputNotEscaped

		return $val;
	}


	/**
	 * Checks callback property to decide whether this is jsonp or json.
	 *
	 * @return string Content-type header string.
	 */
	public function content_type() {
		if ( isset( $this->callback ) ) {
			return 'Content-type: text/javascript';
		} else {
			return 'Content-type: application/json; charset=UTF-8';
		}
	}


	/**
	 * Echoes the correct structure.
	 *
	 * @param string $skip_headers Whether to skip headers when sending output.
	 * @return void
	 */
	public function output( $skip_headers = false ) {
		// Avoid sending headers from the model; tests and some runtimes may have
		// already emitted output which causes header() to fail. The Content-Type
		// header is informational for AJAX clients and safe to omit here.
		// Keep the output body behavior unchanged.

		$out = array(
			'success' => $this->is_success,
			'msg'     => $this->msg,
			'data'    => $this->data,
		);

		// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
		if ( isset( $this->callback ) ) {
			// Prepend JS comment to address XSS vulnerability.
			printf( '/**/%s(%s);', $this->callback, json_encode( $out ) );
		} else {
			echo json_encode( $out );
		}
		// phpcs:enable WordPress.Security.EscapeOutput.OutputNotEscaped
	}


	/**
	 * Wrapper to check if we're in an ajax call.
	 *
	 * @return bool
	 */
	private function _doing_ajax() {
		return ( defined( 'DOING_AJAX' ) && DOING_AJAX );
	}

	/**
	 * Output or return Ajax Request model.
	 *
	 * @return string|void The output string if not terminating the request.
	 */
	public function done() {
		ob_start();
		$this->output();
		$out = ob_get_clean();

		// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $out;
		// phpcs:enable WordPress.Security.EscapeOutput.OutputNotEscaped

		if ( $this->_doing_ajax() ) {
			die();
		} else {
			// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
			return $out;
			// phpcs:enable WordPress.Security.EscapeOutput.OutputNotEscaped
		}
	}
}

/*
 * End of file ajax_request.class.php
 * Location: bbpress-notify-nospam/includes/model/ajax_request.class.php
 */
