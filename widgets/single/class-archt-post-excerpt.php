<?php
/**
 * Widget: Post Excerpt (Single Templates).
 *
 * @package ARCHT_Widgets
 * @since 1.0.0
 */

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Widget_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Displays the current post excerpt. Intended for Single templates.
 *
 * @since 1.0.0
 */
class ARCHT_Post_Excerpt_Widget extends Widget_Base {

	/**
	 * Get widget name.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string
	 */
	public function get_name() {
		return 'archt-post-excerpt';
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
		return esc_html__( 'Post Excerpt', 'architect-complete-theme-builder-for-elementor' );
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
		return 'eicon-post-excerpt';
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
		return [ 'post', 'excerpt', 'summary', 'single' ];
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
				'label' => esc_html__( 'Excerpt', 'architect-complete-theme-builder-for-elementor' ),
			]
		);

		$this->add_control(
			'excerpt_length',
			[
				'label'   => esc_html__( 'Excerpt Length (words)', 'architect-complete-theme-builder-for-elementor' ),
				'type'    => Controls_Manager::NUMBER,
				'default' => '',
				'min'     => 1,
				'step'    => 1,
			]
		);

		$this->add_control(
			'more_text',
			[
				'label'       => esc_html__( 'Read More Text', 'architect-complete-theme-builder-for-elementor' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => '',
				'placeholder' => esc_html__( 'e.g. Read More', 'architect-complete-theme-builder-for-elementor' ),
				'dynamic'     => [ 'active' => true ],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_style',
			[
				'label' => esc_html__( 'Excerpt', 'architect-complete-theme-builder-for-elementor' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'excerpt_color',
			[
				'label'     => esc_html__( 'Color', 'architect-complete-theme-builder-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .archt-post-excerpt' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'excerpt_typography',
				'selector' => '{{WRAPPER}} .archt-post-excerpt',
			]
		);

		$this->add_responsive_control(
			'excerpt_align',
			[
				'label'     => esc_html__( 'Alignment', 'architect-complete-theme-builder-for-elementor' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => [
					'left'    => [
						'title' => esc_html__( 'Left', 'architect-complete-theme-builder-for-elementor' ),
						'icon'  => 'eicon-text-align-left',
					],
					'center'  => [
						'title' => esc_html__( 'Center', 'architect-complete-theme-builder-for-elementor' ),
						'icon'  => 'eicon-text-align-center',
					],
					'right'   => [
						'title' => esc_html__( 'Right', 'architect-complete-theme-builder-for-elementor' ),
						'icon'  => 'eicon-text-align-right',
					],
					'justify' => [
						'title' => esc_html__( 'Justify', 'architect-complete-theme-builder-for-elementor' ),
						'icon'  => 'eicon-text-align-justify',
					],
				],
				'selectors' => [
					'{{WRAPPER}} .archt-post-excerpt' => 'text-align: {{VALUE}};',
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
		$settings = $this->get_settings_for_display();
		$post     = get_post();

		if ( ! $post ) {
			return;
		}

		$excerpt = $post->post_excerpt;

		if ( empty( $excerpt ) ) {
			$excerpt = get_the_content( '', false, $post );
			$excerpt = wp_strip_all_tags( $excerpt );
		}

		$length = absint( $settings['excerpt_length'] );

		if ( $length ) {
			$more_text = sanitize_text_field( $settings['more_text'] );
			$suffix    = $more_text ? '... ' . $more_text : '...';
			$excerpt   = wp_trim_words( $excerpt, $length, $suffix );
		}

		if ( empty( $excerpt ) ) {
			return;
		}

		echo '<div class="archt-post-excerpt">';
		echo wp_kses_post( $excerpt );
		echo '</div>';
	}
}
