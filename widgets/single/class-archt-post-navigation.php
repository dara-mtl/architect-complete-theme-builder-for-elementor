<?php
/**
 * Widget: Post Navigation (Single Templates).
 *
 * @package ARCHT_Widgets
 * @since 1.0.0
 */

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Border;
use Elementor\Widget_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Renders prev/next post links. Intended for Single templates.
 *
 * @since 1.0.0
 */
class ARCHT_Post_Navigation_Widget extends Widget_Base {

	/**
	 * Get widget name.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string
	 */
	public function get_name() {
		return 'archt-post-navigation';
	}

	/**
	 * Get widget title.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string
	 */
	public function get_title() {
		return esc_html__( 'Post Navigation', 'architect-complete-theme-builder-for-elementor' );
	}

	/**
	 * Get widget icon.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-post-navigation';
	}

	/**
	 * Get the style dependencies.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return array
	 */
	public function get_style_depends() {
		return [ 'archt-theme-widgets-style' ];
	}

	/**
	 * Get widget categories.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return array
	 */
	public function get_categories() {
		return [ 'archt' ];
	}

	/**
	 * Get widget keywords.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return array
	 */
	public function get_keywords() {
		return [ 'post', 'navigation', 'prev', 'next', 'single' ];
	}

	/**
	 * Register widget controls.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function register_controls() {

		$this->start_controls_section(
			'section_content',
			[
				'label' => esc_html__( 'Navigation', 'architect-complete-theme-builder-for-elementor' ),
			]
		);

		$this->add_control(
			'show_label',
			[
				'label'     => esc_html__( 'Show Label', 'architect-complete-theme-builder-for-elementor' ),
				'type'      => Controls_Manager::SWITCHER,
				'default'   => 'yes',
				'label_on'  => esc_html__( 'Yes', 'architect-complete-theme-builder-for-elementor' ),
				'label_off' => esc_html__( 'No', 'architect-complete-theme-builder-for-elementor' ),
			]
		);

		$this->add_control(
			'prev_label',
			[
				'label'     => esc_html__( 'Previous Label', 'architect-complete-theme-builder-for-elementor' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => esc_html__( 'Previous', 'architect-complete-theme-builder-for-elementor' ),
				'condition' => [ 'show_label' => 'yes' ],
			]
		);

		$this->add_control(
			'next_label',
			[
				'label'     => esc_html__( 'Next Label', 'architect-complete-theme-builder-for-elementor' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => esc_html__( 'Next', 'architect-complete-theme-builder-for-elementor' ),
				'condition' => [ 'show_label' => 'yes' ],
			]
		);

		$this->add_control(
			'show_title',
			[
				'label'     => esc_html__( 'Show Post Title', 'architect-complete-theme-builder-for-elementor' ),
				'type'      => Controls_Manager::SWITCHER,
				'default'   => 'yes',
				'label_on'  => esc_html__( 'Yes', 'architect-complete-theme-builder-for-elementor' ),
				'label_off' => esc_html__( 'No', 'architect-complete-theme-builder-for-elementor' ),
			]
		);

		$this->add_control(
			'show_arrows',
			[
				'label'     => esc_html__( 'Show Arrows', 'architect-complete-theme-builder-for-elementor' ),
				'type'      => Controls_Manager::SWITCHER,
				'default'   => 'yes',
				'label_on'  => esc_html__( 'Yes', 'architect-complete-theme-builder-for-elementor' ),
				'label_off' => esc_html__( 'No', 'architect-complete-theme-builder-for-elementor' ),
			]
		);

		$this->add_control(
			'in_same_term',
			[
				'label'     => esc_html__( 'Same Category Only', 'architect-complete-theme-builder-for-elementor' ),
				'type'      => Controls_Manager::SWITCHER,
				'default'   => '',
				'label_on'  => esc_html__( 'Yes', 'architect-complete-theme-builder-for-elementor' ),
				'label_off' => esc_html__( 'No', 'architect-complete-theme-builder-for-elementor' ),
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_style',
			[
				'label' => esc_html__( 'Navigation', 'architect-complete-theme-builder-for-elementor' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'nav_color',
			[
				'label'     => esc_html__( 'Color', 'architect-complete-theme-builder-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .archt-post-nav a' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'nav_color_hover',
			[
				'label'     => esc_html__( 'Hover Color', 'architect-complete-theme-builder-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .archt-post-nav a:hover' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'nav_typography',
				'selector' => '{{WRAPPER}} .archt-post-nav a',
			]
		);

		$this->add_control(
			'label_color',
			[
				'label'     => esc_html__( 'Label Color', 'architect-complete-theme-builder-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'separator' => 'before',
				'selectors' => [
					'{{WRAPPER}} .archt-nav-label' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'label_typography',
				'selector' => '{{WRAPPER}} .archt-nav-label',
			]
		);

		$this->add_responsive_control(
			'nav_spacing',
			[
				'label'      => esc_html__( 'Spacing Between Prev/Next', 'architect-complete-theme-builder-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em' ],
				'separator'  => 'before',
				'selectors'  => [
					'{{WRAPPER}} .archt-post-nav' => 'gap: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'      => 'nav_border',
				'selector'  => '{{WRAPPER}} .archt-nav-item',
				'separator' => 'before',
			]
		);

		$this->add_responsive_control(
			'nav_padding',
			[
				'label'      => esc_html__( 'Padding', 'architect-complete-theme-builder-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .archt-nav-item' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Render widget output on the frontend.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function render() {
		$settings    = $this->get_settings_for_display();
		$show_label  = 'yes' === $settings['show_label'];
		$show_title  = 'yes' === $settings['show_title'];
		$show_arrows = 'yes' === $settings['show_arrows'];
		$in_same     = 'yes' === $settings['in_same_term'];
		$prev_label  = esc_html( sanitize_text_field( $settings['prev_label'] ) );
		$next_label  = esc_html( sanitize_text_field( $settings['next_label'] ) );

		$prev_post = get_previous_post( $in_same, '', 'category' );
		$next_post = get_next_post( $in_same, '', 'category' );

		if ( ! $prev_post && ! $next_post ) {
			return;
		}

		echo '<nav class="archt-post-nav" aria-label="' . esc_attr__( 'Post navigation', 'architect-complete-theme-builder-for-elementor' ) . '">';

		echo '<div class="archt-nav-item archt-nav-prev">';
		if ( $prev_post ) {
			$prev_url = esc_url( get_permalink( $prev_post ) );
			echo '<a href="' . esc_url( $prev_url ) . '">';

			if ( $show_arrows ) {
				echo '<i class="eicon-chevron-left archt-nav-arrow" aria-hidden="true"></i>';
			}

			if ( $show_label ) {
				echo '<span class="archt-nav-label">' . esc_html( $prev_label ) . '</span>';
			}

			if ( $show_title ) {
				echo '<span class="archt-nav-title">' . esc_html( get_the_title( $prev_post ) ) . '</span>';
			}

			echo '</a>';
		}
		echo '</div>';

		echo '<div class="archt-nav-item archt-nav-next">';
		if ( $next_post ) {
			$next_url = esc_url( get_permalink( $next_post ) );
			echo '<a href="' . esc_url( $next_url ) . '">';

			if ( $show_label ) {
				echo '<span class="archt-nav-label">' . esc_html( $next_label ) . '</span>';
			}

			if ( $show_title ) {
				echo '<span class="archt-nav-title">' . esc_html( get_the_title( $next_post ) ) . '</span>';
			}

			if ( $show_arrows ) {
				echo '<i class="eicon-chevron-right archt-nav-arrow" aria-hidden="true"></i>';
			}

			echo '</a>';
		}
		echo '</div>';

		echo '</nav>';
	}
}
