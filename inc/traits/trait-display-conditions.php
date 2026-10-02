<?php
/**
 * Template display conditions controls.
 *
 * @package ARCHT_Widgets
 * @since 1.0.0
 */

namespace ARCHT_Library\Inc\Traits;

use ARCHT\Inc\Classes\ARCHT_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// Registered directly: this file is loaded during elementor/init, so a nested elementor/init callback would never fire.
\Elementor\Controls_Manager::add_tab( 'archt_display', esc_html__( 'Conditions', 'architect-complete-theme-builder-for-elementor' ) );

trait ARCHT_Display_Conditions_Trait {

	/**
	 * Register the Display Location controls.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param string $type Options: 'all', 'singular', 'archive'.
	 */
	protected function register_display_conditions_controls( $type = 'all' ) {
		$this->start_controls_section(
			'display_conditions_settings',
			[
				'label' => esc_html__( 'Display Location', 'architect-complete-theme-builder-for-elementor' ),
				'tab'   => 'archt_display',
			]
		);

		$this->add_control(
			'display_conditions_inc',
			[
				'type'        => \Elementor\Controls_Manager::SELECT2,
				'label_block' => true,
				'multiple'    => true,
				'label'       => esc_html__( 'Display On', 'architect-complete-theme-builder-for-elementor' ),
				'options'     => ARCHT_Helper::get_display_conditions_options( $type ),
			]
		);

		$settings = $this->get_settings();

		$options_inc = [];
		if ( ! empty( $settings['selected_posts_inc'] ) ) {
			foreach ( (array) $settings['selected_posts_inc'] as $post_id ) {
				$options_inc[ $post_id ] = get_the_title( $post_id );
			}
		}

		$this->add_control(
			'selected_posts_inc',
			[
				'label'       => esc_html__( 'Specific Posts/Pages', 'architect-complete-theme-builder-for-elementor' ),
				'type'        => \Elementor\Controls_Manager::SELECT2,
				'label_block' => true,
				'multiple'    => true,
				'options'     => $options_inc,
				'condition'   => [ 'display_conditions_inc' => 'specific' ],
				'description' => esc_html__( 'Start typing to search posts and pages.', 'architect-complete-theme-builder-for-elementor' ),
			]
		);

		$options_terms_inc = [];
		if ( ! empty( $settings['selected_terms_inc'] ) ) {
			foreach ( (array) $settings['selected_terms_inc'] as $term_id ) {
				$term = get_term( $term_id );
				if ( $term ) {
					$options_terms_inc[ $term_id ] = esc_html( $term->name ) . ' (' . esc_html( $term->taxonomy ) . ')';
				}
			}
		}

		$this->add_control(
			'selected_terms_inc',
			[
				'label'       => esc_html__( 'Include Specific Terms', 'architect-complete-theme-builder-for-elementor' ),
				'type'        => \Elementor\Controls_Manager::SELECT2,
				'label_block' => true,
				'multiple'    => true,
				'options'     => $options_terms_inc,
				'condition'   => [ 'display_conditions_inc' => 'specific_terms' ],
				'description' => esc_html__( 'Start typing to search taxonomy terms.', 'architect-complete-theme-builder-for-elementor' ),
			]
		);

		$this->add_control(
			'display_conditions_exc',
			[
				'type'        => \Elementor\Controls_Manager::SELECT2,
				'label_block' => true,
				'multiple'    => true,
				'label'       => esc_html__( 'Do Not Display On', 'architect-complete-theme-builder-for-elementor' ),
				'separator'   => 'before',
				'options'     => ARCHT_Helper::get_display_conditions_options( $type ),
			]
		);

		$options_exc = [];
		if ( ! empty( $settings['selected_posts_exc'] ) ) {
			foreach ( (array) $settings['selected_posts_exc'] as $post_id ) {
				$options_exc[ $post_id ] = get_the_title( $post_id );
			}
		}

		$this->add_control(
			'selected_posts_exc',
			[
				'label'       => esc_html__( 'Exclude Specific Posts', 'architect-complete-theme-builder-for-elementor' ),
				'type'        => \Elementor\Controls_Manager::SELECT2,
				'label_block' => true,
				'multiple'    => true,
				'options'     => $options_exc,
				'condition'   => [ 'display_conditions_exc' => 'specific' ],
				'description' => esc_html__( 'Start typing to search posts and pages.', 'architect-complete-theme-builder-for-elementor' ),
			]
		);

		$options_terms_exc = [];
		if ( ! empty( $settings['selected_terms_exc'] ) ) {
			foreach ( (array) $settings['selected_terms_exc'] as $term_id ) {
				$term = get_term( $term_id );
				if ( $term ) {
					$options_terms_exc[ $term_id ] = $term->name . ' (' . $term->taxonomy . ')';
				}
			}
		}

		$this->add_control(
			'selected_terms_exc',
			[
				'label'       => esc_html__( 'Exclude Specific Terms', 'architect-complete-theme-builder-for-elementor' ),
				'type'        => \Elementor\Controls_Manager::SELECT2,
				'label_block' => true,
				'multiple'    => true,
				'options'     => $options_terms_exc,
				'condition'   => [ 'display_conditions_exc' => 'specific_terms' ],
				'description' => esc_html__( 'Start typing to search taxonomy terms.', 'architect-complete-theme-builder-for-elementor' ),
			]
		);

		$this->end_controls_section();
	}
}
