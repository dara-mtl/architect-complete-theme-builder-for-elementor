<?php
/**
 * Archive template document.
 *
 * @package ARCHT_Widgets
 * @since 1.0.0
 */

namespace ARCHT_Library\Custom_Documents;

use Elementor\Core\DocumentTypes\Post;
use Elementor\Modules\Library\Documents\Library_Document;
use ARCHT_Library\Inc\Traits\ARCHT_Display_Conditions_Trait;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Archive template document.
 *
 * @since 1.0.0
 */
class Archive extends Library_Document {
	use ARCHT_Display_Conditions_Trait;

	/**
	 * Get document properties.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return array Document properties.
	 */
	public static function get_properties() {
		$properties = parent::get_properties();

		$properties['support_kit']               = true;
		$properties['show_in_finder']            = true;
		$properties['support_wp_page_templates'] = true;

		return $properties;
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
		return 'archive';
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
		return 'archive';
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
		return esc_html__( 'Archive', 'architect-complete-theme-builder-for-elementor' );
	}

	/**
	 * Get the plural title.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string
	 */
	public static function get_plural_title() {
		return esc_html__( 'Archives', 'architect-complete-theme-builder-for-elementor' );
	}

	/**
	 * Get the "Add New" title.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string
	 */
	public static function get_add_new_title() {
		return esc_html__( 'Add New Archive Template', 'architect-complete-theme-builder-for-elementor' );
	}

	/**
	 * Get the CSS wrapper selector.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string
	 */
	public function get_css_wrapper_selector() {
		return 'body.elementor-archive-' . $this->get_main_id();
	}

	/**
	 * Register the document controls.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function register_controls() {
		parent::register_controls();
		$this->remove_control( 'elementor_library_type_filter_type' );
		$this->remove_control( 'elementor_library_type' );

		Post::register_hide_title_control( $this );
		Post::register_style_controls( $this );

		$this->start_controls_section(
			'template_settings',
			[
				'label' => esc_html__( 'Layout', 'architect-complete-theme-builder-for-elementor' ),
				'tab'   => \Elementor\Controls_Manager::TAB_SETTINGS,
			]
		);

		$this->add_control(
			'template',
			[
				'type'    => \Elementor\Controls_Manager::SELECT,
				'label'   => esc_html__( 'Page Layout', 'architect-complete-theme-builder-for-elementor' ),
				'default' => 'default',
				'options' => [
					'default'                 => esc_html__( 'Default', 'architect-complete-theme-builder-for-elementor' ),
					'elementor_header_footer' => esc_html__( 'Elementor Full Width', 'architect-complete-theme-builder-for-elementor' ),
					'elementor_canvas'        => esc_html__( 'Elementor Canvas', 'architect-complete-theme-builder-for-elementor' ),
				],
			]
		);

		$this->end_controls_section();

		$this->register_display_conditions_controls( 'archive' );
	}

	/**
	 * Get the remote library config.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @return array
	 */
	protected function get_remote_library_config() {
		$config = parent::get_remote_library_config();

		$config['type']          = 'page';
		$config['default_route'] = 'templates/pages';

		return $config;
	}
}
