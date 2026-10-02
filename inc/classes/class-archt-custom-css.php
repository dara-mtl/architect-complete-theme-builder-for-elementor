<?php
/**
 * Custom CSS for elements and pages, skipped when Elementor Pro is active.
 *
 * Portions adapted from Elementor / Elementor Pro, Copyright (C) Elementor Ltd.,
 * licensed GPLv3. See https://elementor.com
 *
 * @package ARCHT_Widgets
 * @since 1.0.0
 */

use Elementor\Controls_Manager;
use Elementor\Element_Base;
use Elementor\Core\Files\CSS\Post;
use Elementor\Core\DynamicTags\Dynamic_CSS;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( defined( 'ELEMENTOR_PRO_VERSION' ) ) {
	return;
}

/**
 * Adds the Custom CSS control and injects its CSS into Elementor's stylesheets.
 *
 * @since 1.0.0
 */
class ARCHT_Custom_Css {

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function __construct() {
		add_action( 'elementor/element/after_section_end', [ __CLASS__, 'add_controls_section' ], 10, 2 );
		add_action( 'elementor/element/parse_css', [ $this, 'add_post_css' ], 10, 2 );
		add_action( 'elementor/css-file/post/parse', [ $this, 'add_page_settings_css' ] );
		add_action( 'elementor/editor/after_enqueue_scripts', [ $this, 'enqueue_editor_scripts' ] );

		// Elementor free strips custom_css from v4 atomic styles; keep it.
		add_filter( 'elementor/atomic_widgets/editor_data/element_styles', [ $this, 'keep_atomic_custom_css' ], 20, 2 );
	}

	/**
	 * Keep the custom CSS stripped from v4 atomic styles.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param array $filtered Styles without custom_css.
	 * @param array $original Styles as authored.
	 * @return array
	 */
	public function keep_atomic_custom_css( $filtered, $original ) {
		return $original;
	}

	/**
	 * Enqueue the editor CodeMirror script.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function enqueue_editor_scripts() {
		wp_enqueue_script( 'archt-custom-css-script', ARCHT_Elementor::asset_url( 'assets/js/archt-custom-css.js' ), [ 'jquery' ], ARCHT_Elementor::VERSION, true );
	}

	/**
	 * Replace Elementor's Custom CSS upsell section with the real controls.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param Element_Base $element    Element.
	 * @param string       $section_id Section that just ended.
	 */
	public static function add_controls_section( $element, $section_id ) {
		if ( 'section_custom_css_pro' !== $section_id ) {
			return;
		}

		\Elementor\Plugin::$instance->controls_manager->remove_control_from_stack(
			$element->get_unique_name(),
			[ 'section_custom_css_pro', 'custom_css_pro' ]
		);

		$element->start_controls_section(
			'archt_custom_element_css',
			[
				'label' => esc_html__( 'Custom CSS', 'architect-complete-theme-builder-for-elementor' ),
				'tab'   => Controls_Manager::TAB_ADVANCED,
			]
		);

		$element->add_control(
			'custom_css_title',
			[
				'raw'  => esc_html__( 'Add your own custom CSS here', 'architect-complete-theme-builder-for-elementor' ),
				'type' => Controls_Manager::RAW_HTML,
			]
		);

		$element->add_control(
			'custom_css',
			[
				'type'        => Controls_Manager::CODE,
				'label'       => esc_html__( 'Custom CSS', 'architect-complete-theme-builder-for-elementor' ),
				'language'    => 'css',
				'render_type' => 'ui',
				'show_label'  => false,
				'description' => esc_html__(
					"Use 'selector' to target wrapper element. Examples:\nselector {color: red;} // For main element\nselector .child-element {margin: 10px;} // For child element\n.my-class {text-align: center;} // Or use any custom selector",
					'architect-complete-theme-builder-for-elementor'
				),
				'separator'   => 'none',
			]
		);

		$element->end_controls_section();
	}

	/**
	 * Append an element's custom CSS to the post stylesheet.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param Post         $post_css Post CSS file.
	 * @param Element_Base $element  Element.
	 */
	public function add_post_css( $post_css, $element ) {
		if ( $post_css instanceof Dynamic_CSS ) {
			return;
		}

		$element_settings = $element->get_settings();

		if ( empty( $element_settings['custom_css'] ) ) {
			return;
		}

		$css = trim( $element_settings['custom_css'] );

		if ( empty( $css ) ) {
			return;
		}

		$css = str_replace( 'selector', $post_css->get_element_unique_selector( $element ), $css );
		$css = sprintf(
			'/* Start custom CSS for %s, class: %s */',
			$element->get_name(),
			$element->get_unique_selector()
		) . $css . '/* End custom CSS */';

		$post_css->get_stylesheet()->add_raw_css( $css );
	}

	/**
	 * Append the page custom CSS to the post stylesheet.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param Post $post_css Post CSS file.
	 */
	public function add_page_settings_css( $post_css ) {
		$document   = \Elementor\Plugin::$instance->documents->get( $post_css->get_post_id() );
		$custom_css = $document->get_settings( 'custom_css' );

		if ( null !== $custom_css ) {
			$custom_css = trim( $custom_css );
		}

		if ( empty( $custom_css ) ) {
			return;
		}

		$custom_css = str_replace( 'selector', $document->get_css_wrapper_selector(), $custom_css );
		$custom_css = '/* Start custom CSS for page-settings */' . $custom_css . '/* End custom CSS */';

		$post_css->get_stylesheet()->add_raw_css( $custom_css );
	}
}

new ARCHT_Custom_Css();
