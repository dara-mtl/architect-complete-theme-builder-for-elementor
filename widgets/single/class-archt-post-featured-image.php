<?php
/**
 * Widget: Post Featured Image (Single Templates).
 *
 * @package ARCHT_Widgets
 * @since 1.0.0
 */

use Elementor\Controls_Manager;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Css_Filter;
use Elementor\Widget_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Displays the current post featured image. Intended for Single templates.
 *
 * @since 1.0.0
 */
class ARCHT_Post_Featured_Image_Widget extends Widget_Base {

	/**
	 * Get widget name.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string
	 */
	public function get_name() {
		return 'archt-post-featured-image';
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
		return esc_html__( 'Featured Image', 'architect-complete-theme-builder-for-elementor' );
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
		return 'eicon-featured-image';
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
		return [ 'post', 'image', 'featured', 'thumbnail', 'single' ];
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
				'label' => esc_html__( 'Featured Image', 'architect-complete-theme-builder-for-elementor' ),
			]
		);

		$this->add_control(
			'image_size',
			[
				'label'   => esc_html__( 'Image Size', 'architect-complete-theme-builder-for-elementor' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'full',
				'options' => $this->get_image_size_options(),
			]
		);

		$this->add_control(
			'link_to_post',
			[
				'label'     => esc_html__( 'Link to Post', 'architect-complete-theme-builder-for-elementor' ),
				'type'      => Controls_Manager::SWITCHER,
				'label_on'  => esc_html__( 'Yes', 'architect-complete-theme-builder-for-elementor' ),
				'label_off' => esc_html__( 'No', 'architect-complete-theme-builder-for-elementor' ),
				'default'   => '',
			]
		);

		$this->add_control(
			'open_lightbox',
			[
				'label'     => esc_html__( 'Open Lightbox', 'architect-complete-theme-builder-for-elementor' ),
				'type'      => Controls_Manager::SWITCHER,
				'label_on'  => esc_html__( 'Yes', 'architect-complete-theme-builder-for-elementor' ),
				'label_off' => esc_html__( 'No', 'architect-complete-theme-builder-for-elementor' ),
				'default'   => '',
				'condition' => [
					'link_to_post' => '',
				],
			]
		);

		$this->add_control(
			'fallback',
			[
				'label'   => esc_html__( 'Fallback Image', 'architect-complete-theme-builder-for-elementor' ),
				'type'    => Controls_Manager::MEDIA,
				'dynamic' => [ 'active' => true ],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_style',
			[
				'label' => esc_html__( 'Image', 'architect-complete-theme-builder-for-elementor' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'image_align',
			[
				'label'     => esc_html__( 'Alignment', 'architect-complete-theme-builder-for-elementor' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => [
					'left'   => [
						'title' => esc_html__( 'Left', 'architect-complete-theme-builder-for-elementor' ),
						'icon'  => 'eicon-h-align-left',
					],
					'center' => [
						'title' => esc_html__( 'Center', 'architect-complete-theme-builder-for-elementor' ),
						'icon'  => 'eicon-h-align-center',
					],
					'right'  => [
						'title' => esc_html__( 'Right', 'architect-complete-theme-builder-for-elementor' ),
						'icon'  => 'eicon-h-align-right',
					],
				],
				'selectors' => [
					'{{WRAPPER}} .archt-featured-image-wrap' => 'text-align: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'image_width',
			[
				'label'      => esc_html__( 'Width', 'architect-complete-theme-builder-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ '%', 'px', 'vw' ],
				'range'      => [
					'%'  => [
						'min' => 1,
						'max' => 100,
					],
					'px' => [
						'min' => 1,
						'max' => 1600,
					],
					'vw' => [
						'min' => 1,
						'max' => 100,
					],
				],
				'selectors'  => [
					'{{WRAPPER}} .archt-featured-image' => 'width: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'image_max_width',
			[
				'label'      => esc_html__( 'Max Width', 'architect-complete-theme-builder-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ '%', 'px', 'vw' ],
				'range'      => [
					'%'  => [
						'min' => 1,
						'max' => 100,
					],
					'px' => [
						'min' => 1,
						'max' => 1600,
					],
					'vw' => [
						'min' => 1,
						'max' => 100,
					],
				],
				'selectors'  => [
					'{{WRAPPER}} .archt-featured-image' => 'max-width: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'image_height',
			[
				'label'      => esc_html__( 'Height', 'architect-complete-theme-builder-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'vh' ],
				'range'      => [
					'px' => [
						'min' => 1,
						'max' => 1200,
					],
					'vh' => [
						'min' => 1,
						'max' => 100,
					],
				],
				'selectors'  => [
					'{{WRAPPER}} .archt-featured-image' => 'height: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'image_object_fit',
			[
				'label'     => esc_html__( 'Object Fit', 'architect-complete-theme-builder-for-elementor' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => '',
				'options'   => [
					''        => esc_html__( 'Default', 'architect-complete-theme-builder-for-elementor' ),
					'fill'    => esc_html__( 'Fill', 'architect-complete-theme-builder-for-elementor' ),
					'cover'   => esc_html__( 'Cover', 'architect-complete-theme-builder-for-elementor' ),
					'contain' => esc_html__( 'Contain', 'architect-complete-theme-builder-for-elementor' ),
				],
				'selectors' => [
					'{{WRAPPER}} .archt-featured-image' => 'object-fit: {{VALUE}};',
				],
				'condition' => [
					'image_height[size]!' => '',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'      => 'image_border',
				'selector'  => '{{WRAPPER}} .archt-featured-image',
				'separator' => 'before',
			]
		);

		$this->add_responsive_control(
			'image_border_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'architect-complete-theme-builder-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors'  => [
					'{{WRAPPER}} .archt-featured-image' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'image_box_shadow',
				'selector' => '{{WRAPPER}} .archt-featured-image',
			]
		);

		$this->start_controls_tabs( 'image_effects' );

		$this->start_controls_tab(
			'image_effects_normal',
			[ 'label' => esc_html__( 'Normal', 'architect-complete-theme-builder-for-elementor' ) ]
		);

		$this->add_group_control(
			Group_Control_Css_Filter::get_type(),
			[
				'name'     => 'image_css_filter',
				'selector' => '{{WRAPPER}} .archt-featured-image',
			]
		);

		$this->add_control(
			'image_opacity',
			[
				'label'     => esc_html__( 'Opacity', 'architect-complete-theme-builder-for-elementor' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => [
					'px' => [
						'min'  => 0,
						'max'  => 1,
						'step' => 0.01,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .archt-featured-image' => 'opacity: {{SIZE}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'image_effects_hover',
			[ 'label' => esc_html__( 'Hover', 'architect-complete-theme-builder-for-elementor' ) ]
		);

		$this->add_group_control(
			Group_Control_Css_Filter::get_type(),
			[
				'name'     => 'image_css_filter_hover',
				'selector' => '{{WRAPPER}} .archt-featured-image:hover',
			]
		);

		$this->add_control(
			'image_opacity_hover',
			[
				'label'     => esc_html__( 'Opacity', 'architect-complete-theme-builder-for-elementor' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => [
					'px' => [
						'min'  => 0,
						'max'  => 1,
						'step' => 0.01,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .archt-featured-image:hover' => 'opacity: {{SIZE}};',
				],
			]
		);

		$this->add_control(
			'image_transition',
			[
				'label'     => esc_html__( 'Transition Duration (ms)', 'architect-complete-theme-builder-for-elementor' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => [
					'px' => [
						'min'  => 0,
						'max'  => 3000,
						'step' => 100,
					],
				],
				'default'   => [ 'size' => 300 ],
				'selectors' => [
					'{{WRAPPER}} .archt-featured-image' => 'transition: all {{SIZE}}ms;',
				],
			]
		);

		$this->end_controls_tab();
		$this->end_controls_tabs();

		$this->end_controls_section();
	}

	/**
	 * Build a list of registered image size options.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @return array
	 */
	private function get_image_size_options() {
		$sizes   = get_intermediate_image_sizes();
		$options = [];

		foreach ( $sizes as $size ) {
			$options[ $size ] = ucwords( str_replace( [ '-', '_' ], ' ', $size ) );
		}

		$options['full'] = esc_html__( 'Full', 'architect-complete-theme-builder-for-elementor' );

		return $options;
	}

	/**
	 * Render widget output on the frontend.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function render() {
		$settings     = $this->get_settings_for_display();
		$size         = sanitize_key( $settings['image_size'] );
		$thumbnail_id = get_post_thumbnail_id();

		if ( ! $thumbnail_id ) {
			if ( ! empty( $settings['fallback']['id'] ) ) {
				$thumbnail_id = absint( $settings['fallback']['id'] );
			} elseif ( ! empty( $settings['fallback']['url'] ) ) {
				echo '<div class="archt-featured-image-wrap">';
				printf(
					'<img class="archt-featured-image" src="%s" alt="%s" />',
					esc_url( $settings['fallback']['url'] ),
					esc_attr( get_the_title() )
				);
				echo '</div>';
				return;
			} else {
				return;
			}
		}

		$image_html = wp_get_attachment_image(
			$thumbnail_id,
			$size,
			false,
			[
				'class' => 'archt-featured-image',
				'alt'   => esc_attr( get_the_title() ),
			]
		);

		if ( empty( $image_html ) ) {
			return;
		}

		echo '<div class="archt-featured-image-wrap">';

		if ( 'yes' === $settings['link_to_post'] ) {
			printf(
				'<a href="%s">%s</a>',
				esc_url( get_permalink() ),
				$image_html // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_get_attachment_image is safe.
			);
		} elseif ( 'yes' === $settings['open_lightbox'] ) {
			$full_url = wp_get_attachment_image_url( $thumbnail_id, 'full' );
			printf(
				'<a href="%s" data-elementor-open-lightbox="yes" data-elementor-lightbox-slideshow="%s">%s</a>',
				esc_url( $full_url ),
				esc_attr( $this->get_id() ),
				$image_html // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_get_attachment_image is safe.
			);
		} else {
			echo $image_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_get_attachment_image is safe.
		}

		echo '</div>';
	}
}
