<?php
/**
 * Custom Attributes for elements, skipped when Elementor Pro is active.
 *
 * Portions adapted from Elementor Pro, Copyright (C) Elementor Ltd.,
 * licensed GPLv3.
 *
 * @package ARCHT_Widgets
 * @since 1.0.0
 */

use Elementor\Controls_Manager;
use Elementor\Element_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( defined( 'ELEMENTOR_PRO_VERSION' ) ) {
	return;
}

/**
 * Adds the Custom Attributes control and renders the attributes on the wrapper.
 *
 * @since 1.0.0
 */
class ARCHT_Custom_Attributes {

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function __construct() {
		add_action( 'elementor/element/after_section_end', [ $this, 'archt_attributes_controls_section' ], 10, 2 );
		add_action( 'elementor/frontend/before_render', [ $this, 'archt_render_attributes' ] );
	}

	/**
	 * Replace Elementor's Custom Attributes upsell section with the real controls.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param Element_Base $element    Element.
	 * @param string       $section_id Section that just ended.
	 */
	public function archt_attributes_controls_section( $element, $section_id ) {
		if ( ! $element instanceof Element_Base ) {
			return;
		}

		if ( 'section_custom_attributes_pro' !== $section_id ) {
			return;
		}

		\Elementor\Plugin::$instance->controls_manager->remove_control_from_stack(
			$element->get_unique_name(),
			[ 'section_custom_attributes_pro', 'custom_attributes_pro' ]
		);

		$element->start_controls_section(
			'archt_custom_element_attributes',
			[
				'label' => esc_html__( 'Attributes', 'architect-complete-theme-builder-for-elementor' ),
				'tab'   => Controls_Manager::TAB_ADVANCED,
			]
		);

		$element->add_control(
			'archt_attributes',
			[
				'label'       => esc_html__( 'Custom Attributes', 'architect-complete-theme-builder-for-elementor' ),
				'type'        => Controls_Manager::TEXTAREA,
				'placeholder' => esc_html__( 'Key|Value', 'architect-complete-theme-builder-for-elementor' ),
				'description' => esc_html__(
					'Set custom attributes for the wrapper element. Each attribute in a separate line. Separate attribute key from the value using | character.',
					'architect-complete-theme-builder-for-elementor'
				),
				'dynamic'     => [
					'active' => true,
				],
				'render_type' => 'none',
				'classes'     => 'elementor-control-direction-ltr',
			]
		);

		$element->end_controls_section();
	}

	/**
	 * Add the custom attributes to the element wrapper.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param Element_Base $element Element.
	 */
	public function archt_render_attributes( $element ) {
		$settings = $element->get_settings_for_display();

		if ( empty( $settings['archt_attributes'] ) ) {
			return;
		}

		$attributes = $this->parse_custom_attributes( $settings['archt_attributes'], "\n" );
		$black_list = $this->get_black_list_attributes();

		foreach ( $attributes as $attribute => $value ) {
			if ( ! in_array( $attribute, $black_list, true ) ) {
				$element->add_render_attribute( '_wrapper', $attribute, $value );
			}
		}
	}

	/**
	 * Parse "key|value" lines into safe attributes.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @param string $attributes_string Raw attributes.
	 * @param string $delimiter         Separator between attributes.
	 * @return array<string, string>
	 */
	private function parse_custom_attributes( $attributes_string, $delimiter = ',' ) {
		$result = [];

		foreach ( explode( $delimiter, (string) $attributes_string ) as $line ) {
			$line = trim( $line );

			if ( '' === $line ) {
				continue;
			}

			$pair  = explode( '|', $line, 2 );
			$key   = strtolower( trim( $pair[0] ) );
			$value = isset( $pair[1] ) ? trim( $pair[1] ) : '';

			// Drop invalid keys rather than trimming them into a different attribute.
			if ( ! preg_match( '/^[a-z][a-z0-9_-]*$/', $key ) ) {
				continue;
			}

			if ( $this->is_unsafe_attribute( $key ) ) {
				continue;
			}

			$result[ $key ] = $value;
		}

		return $result;
	}

	/**
	 * Whether an attribute name can execute script or hijack navigation.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @param string $key Lowercased attribute name.
	 * @return bool
	 */
	private function is_unsafe_attribute( $key ) {
		if ( 0 === strpos( $key, 'on' ) ) {
			return true;
		}

		return in_array( $key, [ 'href', 'src', 'srcdoc', 'formaction', 'action', 'xlink' ], true );
	}

	/**
	 * Get the reserved attribute names users can't overwrite.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @return string[]
	 */
	private function get_black_list_attributes() {
		static $reserved = null;

		if ( null === $reserved ) {
			$reserved = [
				'id',
				'class',
				'data-id',
				'data-element_type',
				'data-widget_type',
				'data-model-cid',
				'data-settings',
			];

			/**
			 * Filters the attributes that are never rendered on the element wrapper.
			 *
			 * @since 1.0.0
			 *
			 * @param string[] $reserved Reserved attribute names.
			 */
			$reserved = apply_filters( 'archt/element/attributes/black_list', $reserved );
		}

		return $reserved;
	}
}

new ARCHT_Custom_Attributes();
