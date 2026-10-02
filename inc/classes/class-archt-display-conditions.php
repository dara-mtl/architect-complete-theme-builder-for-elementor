<?php
/**
 * Element display conditions.
 *
 * @package ARCHT_Widgets
 * @since 1.0.0
 */

use Elementor\Repeater;
use Elementor\Controls_Manager;

use ARCHT\Inc\Classes\ARCHT_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Element display conditions.
 */
class ARCHT_Display_Conditions {
	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function __construct() {
		add_action( 'elementor/element/container/section_layout/after_section_end', [ $this, 'add_controls_section' ] );
		add_action( 'elementor/element/common/_section_style/after_section_end', [ $this, 'add_controls_section' ] );
		add_filter( 'elementor/frontend/container/should_render', [ $this, 'render_content' ], 10, 2 );
		add_filter( 'elementor/frontend/widget/should_render', [ $this, 'render_content' ], 10, 2 );
	}

	/**
	 * Hide elements whose display conditions fail.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param bool                    $should_render Whether the element renders.
	 * @param \Elementor\Element_Base $element       Element.
	 * @return bool
	 */
	public function render_content( $should_render, $element ) {
		if ( \Elementor\Plugin::$instance->preview->is_preview_mode() ) {
			return $should_render;
		}

		$settings = $element->get_settings_for_display();

		if ( empty( $settings['condition_enable'] ) || 'yes' !== $settings['condition_enable'] ) {
			return $should_render;
		}

		if ( empty( $settings['condition_list'] ) || ! is_array( $settings['condition_list'] ) ) {
			return $should_render;
		}

		$match_all = ! isset( $settings['condition_display'] ) || 'all' === $settings['condition_display'];

		foreach ( $settings['condition_list'] as $item ) {
			$source = isset( $item['condition_value'] ) ? $item['condition_value'] : 'dynamic_tag';

			if ( 'shortcode' === $source ) {
				$value1 = isset( $item['comparator_shortcode'] ) ? do_shortcode( $item['comparator_shortcode'] ) : '';
			} else {
				$value1 = isset( $item['comparator_dt'] ) ? $item['comparator_dt'] : '';
			}

			$operator = isset( $item['condition_operator'] ) ? $item['condition_operator'] : 'not_empty';
			$value2   = isset( $item['condition_expected_value'] ) ? $item['condition_expected_value'] : '';
			$matched  = ARCHT_Helper::check_operator( $value1, $operator, $value2 );

			if ( $match_all && ! $matched ) {
				return false;
			}

			if ( ! $match_all && $matched ) {
				return $should_render;
			}
		}

		return $match_all ? $should_render : false;
	}

	/**
	 * Add the Display Conditions section.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param \Elementor\Element_Base $element Element.
	 */
	public static function add_controls_section( $element ) {
		$element->start_controls_section(
			'conditions_section',
			[
				'label' => esc_html__( 'Display Conditions', 'architect-complete-theme-builder-for-elementor' ),
				'tab'   => Controls_Manager::TAB_ADVANCED,
			]
		);

		$element->add_control(
			'condition_enable',
			[
				'type'         => Controls_Manager::SWITCHER,
				'label'        => esc_html__( 'Enable Display Conditions', 'architect-complete-theme-builder-for-elementor' ),
				'label_on'     => esc_html__( 'Yes', 'architect-complete-theme-builder-for-elementor' ),
				'label_off'    => esc_html__( 'No', 'architect-complete-theme-builder-for-elementor' ),
				'return_value' => 'yes',
				'default'      => 'no',
				'prefix_class' => 'archt-condition-',
			]
		);

		$element->add_control(
			'condition_display',
			[
				'type'      => \Elementor\Controls_Manager::SELECT,
				'label'     => esc_html__( 'Display When', 'architect-complete-theme-builder-for-elementor' ),
				'default'   => 'all',
				'options'   => [
					'all' => esc_html__( 'All Conditions Are Met', 'architect-complete-theme-builder-for-elementor' ),
					'any' => esc_html__( 'Any Condition is Met', 'architect-complete-theme-builder-for-elementor' ),
				],
				'condition' => [
					'condition_enable' => 'yes',
				],
			]
		);

		$repeater = new Repeater();

		$repeater->start_controls_tabs( 'field_repeater' );

		$repeater->add_control(
			'condition_value',
			[
				'type'        => \Elementor\Controls_Manager::SELECT,
				'label_block' => true,
				'default'     => 'dynamic_tag',
				'options'     => [
					'dynamic_tag' => esc_html__( 'Custom Field', 'architect-complete-theme-builder-for-elementor' ),
					'shortcode'   => esc_html__( 'Shortcode', 'architect-complete-theme-builder-for-elementor' ),
				],
			]
		);

		$repeater->add_control(
			'comparator_dt',
			[
				'type'        => \Elementor\Controls_Manager::TEXT,
				'label_block' => true,
				'placeholder' => esc_html__( 'Choose a Custom Field', 'architect-complete-theme-builder-for-elementor' ),
				'dynamic'     => [
					'active' => true,
				],
				'condition'   => [
					'condition_value' => 'dynamic_tag',
				],
			]
		);

		$repeater->add_control(
			'comparator_shortcode',
			[
				'type'        => \Elementor\Controls_Manager::TEXT,
				'label_block' => true,
				'placeholder' => '[shortcode attribute="value"]',
				'condition'   => [
					'condition_value' => 'shortcode',
				],
			]
		);

		$repeater->add_control(
			'condition_operator',
			[
				'type'        => \Elementor\Controls_Manager::SELECT,
				'label_block' => true,
				'default'     => 'empty',
				'options'     => [
					'=='          => esc_html__( 'Equal', 'architect-complete-theme-builder-for-elementor' ),
					'!='          => esc_html__( 'Not Equal', 'architect-complete-theme-builder-for-elementor' ),
					'<'           => esc_html__( 'Less Than', 'architect-complete-theme-builder-for-elementor' ),
					'>'           => esc_html__( 'Greater Than', 'architect-complete-theme-builder-for-elementor' ),
					'empty'       => esc_html__( 'Empty', 'architect-complete-theme-builder-for-elementor' ),
					'not_empty'   => esc_html__( 'Not Empty', 'architect-complete-theme-builder-for-elementor' ),
					'contain'     => esc_html__( 'Contains', 'architect-complete-theme-builder-for-elementor' ),
					'not_contain' => esc_html__( 'Doesn\'t Contain', 'architect-complete-theme-builder-for-elementor' ),
				],
			]
		);

		$repeater->add_control(
			'condition_expected_value',
			[
				'type'        => \Elementor\Controls_Manager::TEXTAREA,
				'label_block' => true,
				'placeholder' => esc_html__( 'Enter the string that needs to be returned in order for the condition to apply.', 'architect-complete-theme-builder-for-elementor' ),
				'condition'   => [
					'condition_operator!' => [ 'empty','not_empty' ],
				],
			]
		);

		$element->add_control(
			'condition_list',
			[
				'label'       => esc_html__( 'Conditions', 'architect-complete-theme-builder-for-elementor' ),
				'type'        => \Elementor\Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'title_field' => esc_html__( 'Condition', 'architect-complete-theme-builder-for-elementor' ),
				'default'     => [
					[
						'condition_value'    => 'dynamic_tag',
						'condition_operator' => 'not_empty',
					],
				],
				'condition'   => [
					'condition_enable' => 'yes',
				],
			]
		);

		$element->end_controls_section();
	}
}
new ARCHT_Display_Conditions();
