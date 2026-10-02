<?php
/**
 * Footer template document.
 *
 * @package ARCHT_Widgets
 * @since 1.0.0
 */

namespace ARCHT_Library\Custom_Documents;

use Elementor\Modules\Library\Documents\Library_Document;
use ARCHT_Library\Inc\Traits\ARCHT_Display_Conditions_Trait;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Footer template document.
 *
 * @since 1.0.0
 */
class Footer extends Library_Document {
	use ARCHT_Display_Conditions_Trait;

	/**
	 * Get the document properties.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return array
	 */
	public static function get_properties() {
		$properties = parent::get_properties();

		$properties['support_kit'] = true;

		return $properties;
	}

	/**
	 * Get document name.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string Document name.
	 */
	public function get_name() {
		return 'footer';
	}

	/**
	 * Get document title.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string Document title.
	 */
	public static function get_title() {
		return esc_html__( 'Footer', 'architect-complete-theme-builder-for-elementor' );
	}

	/**
	 * Get the document type.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string
	 */
	public static function get_type() {
		return 'footer';
	}

	/**
	 * Register the controls.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function register_controls() {
		parent::register_controls();
		$this->remove_control( 'elementor_library_type_filter_type' );
		$this->remove_control( 'elementor_library_type' );
		$this->register_display_conditions_controls( 'all' );
	}
}
