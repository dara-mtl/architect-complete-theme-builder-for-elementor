<?php
/**
 * Widget: Posts Loop (Archive Templates).
 *
 * @package ARCHT_Widgets
 * @since 1.0.0
 */

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Background;
use Elementor\Core\Kits\Documents\Tabs\Global_Typography;
use Elementor\Core\Kits\Documents\Tabs\Global_Colors;
use Elementor\Widget_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Loop grid/list for archive pages. Reads from the main WP_Query on the
 *
 * @since 1.0.0
 */
class ARCHT_Posts_Loop_Widget extends Widget_Base {

	/**
	 * Get widget name.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string
	 */
	public function get_name() {
		return 'archt-posts-loop';
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
		return esc_html__( 'Posts Loop', 'architect-complete-theme-builder-for-elementor' );
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
		return 'eicon-loop-builder';
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
		return [ 'archive', 'posts', 'loop', 'grid', 'list', 'blog' ];
	}

	/**
	 * Register widget controls.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function register_controls() {

		$this->start_controls_section(
			'section_layout',
			[
				'label' => esc_html__( 'Layout', 'architect-complete-theme-builder-for-elementor' ),
			]
		);

		$this->add_control(
			'layout',
			[
				'label'   => esc_html__( 'Layout', 'architect-complete-theme-builder-for-elementor' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'grid',
				'options' => [
					'grid' => esc_html__( 'Grid', 'architect-complete-theme-builder-for-elementor' ),
					'list' => esc_html__( 'List', 'architect-complete-theme-builder-for-elementor' ),
				],
			]
		);

		$this->add_responsive_control(
			'columns',
			[
				'label'          => esc_html__( 'Columns', 'architect-complete-theme-builder-for-elementor' ),
				'type'           => Controls_Manager::NUMBER,
				'default'        => 3,
				'tablet_default' => 2,
				'mobile_default' => 1,
				'min'            => 1,
				'max'            => 6,
				'step'           => 1,
				'condition'      => [ 'layout' => 'grid' ],
				'selectors'      => [
					'{{WRAPPER}} .archt-posts-loop.archt-layout-grid' => 'grid-template-columns: repeat({{VALUE}}, 1fr);',
				],
			]
		);

		$this->add_responsive_control(
			'column_gap',
			[
				'label'      => esc_html__( 'Column Gap', 'architect-complete-theme-builder-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em', '%' ],
				'default'    => [
					'size' => 20,
					'unit' => 'px',
				],
				'selectors'  => [
					'{{WRAPPER}} .archt-posts-loop' => 'column-gap: {{SIZE}}{{UNIT}};',
				],
				'condition'  => [ 'layout' => 'grid' ],
			]
		);

		$this->add_responsive_control(
			'row_gap',
			[
				'label'      => esc_html__( 'Row Gap', 'architect-complete-theme-builder-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em', '%' ],
				'default'    => [
					'size' => 20,
					'unit' => 'px',
				],
				'selectors'  => [
					'{{WRAPPER}} .archt-posts-loop' => 'row-gap: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_card',
			[
				'label' => esc_html__( 'Post Card', 'architect-complete-theme-builder-for-elementor' ),
			]
		);

		$this->add_control(
			'show_thumbnail',
			[
				'label'     => esc_html__( 'Show Thumbnail', 'architect-complete-theme-builder-for-elementor' ),
				'type'      => Controls_Manager::SWITCHER,
				'default'   => 'yes',
				'label_on'  => esc_html__( 'Yes', 'architect-complete-theme-builder-for-elementor' ),
				'label_off' => esc_html__( 'No', 'architect-complete-theme-builder-for-elementor' ),
			]
		);

		$this->add_control(
			'thumbnail_size',
			[
				'label'     => esc_html__( 'Thumbnail Size', 'architect-complete-theme-builder-for-elementor' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'large',
				'options'   => $this->get_image_size_options(),
				'condition' => [ 'show_thumbnail' => 'yes' ],
			]
		);

		$this->add_control(
			'thumbnail_link',
			[
				'label'     => esc_html__( 'Link Thumbnail to Post', 'architect-complete-theme-builder-for-elementor' ),
				'type'      => Controls_Manager::SWITCHER,
				'default'   => 'yes',
				'condition' => [ 'show_thumbnail' => 'yes' ],
			]
		);

		$this->add_control(
			'show_title',
			[
				'label'     => esc_html__( 'Show Title', 'architect-complete-theme-builder-for-elementor' ),
				'type'      => Controls_Manager::SWITCHER,
				'default'   => 'yes',
				'separator' => 'before',
			]
		);

		$this->add_control(
			'title_tag',
			[
				'label'     => esc_html__( 'Title Tag', 'architect-complete-theme-builder-for-elementor' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'h2',
				'options'   => [
					'h2'   => 'H2',
					'h3'   => 'H3',
					'h4'   => 'H4',
					'h5'   => 'H5',
					'h6'   => 'H6',
					'p'    => 'p',
					'div'  => 'div',
					'span' => 'span',
				],
				'condition' => [ 'show_title' => 'yes' ],
			]
		);

		$this->add_control(
			'show_excerpt',
			[
				'label'     => esc_html__( 'Show Excerpt', 'architect-complete-theme-builder-for-elementor' ),
				'type'      => Controls_Manager::SWITCHER,
				'default'   => 'yes',
				'separator' => 'before',
			]
		);

		$this->add_control(
			'excerpt_length',
			[
				'label'     => esc_html__( 'Excerpt Length (words)', 'architect-complete-theme-builder-for-elementor' ),
				'type'      => Controls_Manager::NUMBER,
				'default'   => 20,
				'min'       => 1,
				'step'      => 1,
				'condition' => [ 'show_excerpt' => 'yes' ],
			]
		);

		$this->add_control(
			'show_meta',
			[
				'label'     => esc_html__( 'Show Meta (Date & Author)', 'architect-complete-theme-builder-for-elementor' ),
				'type'      => Controls_Manager::SWITCHER,
				'default'   => 'yes',
				'separator' => 'before',
			]
		);

		$this->add_control(
			'show_terms',
			[
				'label'     => esc_html__( 'Show Terms', 'architect-complete-theme-builder-for-elementor' ),
				'type'      => Controls_Manager::SWITCHER,
				'default'   => 'yes',
				'separator' => 'before',
			]
		);

		$this->add_control(
			'terms_taxonomy',
			[
				'label'     => esc_html__( 'Taxonomy', 'architect-complete-theme-builder-for-elementor' ),
				'type'      => Controls_Manager::SELECT,
				'options'   => $this->get_taxonomy_options(),
				'default'   => 'category',
				'condition' => [ 'show_terms' => 'yes' ],
			]
		);

		$this->add_control(
			'show_read_more',
			[
				'label'     => esc_html__( 'Show Read More Button', 'architect-complete-theme-builder-for-elementor' ),
				'type'      => Controls_Manager::SWITCHER,
				'default'   => 'yes',
				'separator' => 'before',
			]
		);

		$this->add_control(
			'read_more_text',
			[
				'label'     => esc_html__( 'Button Text', 'architect-complete-theme-builder-for-elementor' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => esc_html__( 'Read More', 'architect-complete-theme-builder-for-elementor' ),
				'condition' => [ 'show_read_more' => 'yes' ],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_pagination',
			[
				'label' => esc_html__( 'Pagination', 'architect-complete-theme-builder-for-elementor' ),
			]
		);

		$this->add_control(
			'show_pagination',
			[
				'label'     => esc_html__( 'Show Pagination', 'architect-complete-theme-builder-for-elementor' ),
				'type'      => Controls_Manager::SWITCHER,
				'default'   => 'yes',
				'label_on'  => esc_html__( 'Yes', 'architect-complete-theme-builder-for-elementor' ),
				'label_off' => esc_html__( 'No', 'architect-complete-theme-builder-for-elementor' ),
			]
		);

		$this->add_control(
			'prev_text',
			[
				'label'     => esc_html__( 'Previous Text', 'architect-complete-theme-builder-for-elementor' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => esc_html__( '« Previous', 'architect-complete-theme-builder-for-elementor' ),
				'condition' => [ 'show_pagination' => 'yes' ],
			]
		);

		$this->add_control(
			'next_text',
			[
				'label'     => esc_html__( 'Next Text', 'architect-complete-theme-builder-for-elementor' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => esc_html__( 'Next »', 'architect-complete-theme-builder-for-elementor' ),
				'condition' => [ 'show_pagination' => 'yes' ],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'style_card',
			[
				'label' => esc_html__( 'Card', 'architect-complete-theme-builder-for-elementor' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'card_background',
				'selector' => '{{WRAPPER}} .archt-loop-item',
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'card_border',
				'selector' => '{{WRAPPER}} .archt-loop-item',
			]
		);

		$this->add_responsive_control(
			'card_border_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'architect-complete-theme-builder-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors'  => [
					'{{WRAPPER}} .archt-loop-item' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'card_box_shadow',
				'selector' => '{{WRAPPER}} .archt-loop-item',
			]
		);

		$this->add_responsive_control(
			'card_padding',
			[
				'label'      => esc_html__( 'Content Padding', 'architect-complete-theme-builder-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .archt-loop-item-content' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'style_thumbnail',
			[
				'label'     => esc_html__( 'Thumbnail', 'architect-complete-theme-builder-for-elementor' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [ 'show_thumbnail' => 'yes' ],
			]
		);

		$this->add_responsive_control(
			'thumb_height',
			[
				'label'      => esc_html__( 'Height', 'architect-complete-theme-builder-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'vh' ],
				'range'      => [
					'px' => [
						'min' => 50,
						'max' => 800,
					],
					'vh' => [
						'min' => 5,
						'max' => 100,
					],
				],
				'default'    => [
					'size' => 200,
					'unit' => 'px',
				],
				'selectors'  => [
					'{{WRAPPER}} .archt-loop-thumbnail' => 'height: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'thumb_object_fit',
			[
				'label'     => esc_html__( 'Object Fit', 'architect-complete-theme-builder-for-elementor' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'cover',
				'options'   => [
					'cover'   => esc_html__( 'Cover', 'architect-complete-theme-builder-for-elementor' ),
					'contain' => esc_html__( 'Contain', 'architect-complete-theme-builder-for-elementor' ),
					'fill'    => esc_html__( 'Fill', 'architect-complete-theme-builder-for-elementor' ),
				],
				'selectors' => [
					'{{WRAPPER}} .archt-loop-thumbnail img' => 'object-fit: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'thumb_border_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'architect-complete-theme-builder-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .archt-loop-thumbnail img' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'style_title',
			[
				'label'     => esc_html__( 'Title', 'architect-complete-theme-builder-for-elementor' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [ 'show_title' => 'yes' ],
			]
		);

		$this->add_control(
			'title_color',
			[
				'label'     => esc_html__( 'Color', 'architect-complete-theme-builder-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'global'    => [ 'default' => Global_Colors::COLOR_PRIMARY ],
				'selectors' => [
					'{{WRAPPER}} .archt-loop-title a' => 'color: {{VALUE}};',
					'{{WRAPPER}} .archt-loop-title'   => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'title_hover_color',
			[
				'label'     => esc_html__( 'Hover Color', 'architect-complete-theme-builder-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .archt-loop-title a:hover' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'title_typography',
				'global'   => [ 'default' => Global_Typography::TYPOGRAPHY_PRIMARY ],
				'selector' => '{{WRAPPER}} .archt-loop-title',
			]
		);

		$this->add_responsive_control(
			'title_spacing',
			[
				'label'      => esc_html__( 'Bottom Spacing', 'architect-complete-theme-builder-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em' ],
				'selectors'  => [
					'{{WRAPPER}} .archt-loop-title' => 'margin-bottom: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'style_excerpt',
			[
				'label'     => esc_html__( 'Excerpt', 'architect-complete-theme-builder-for-elementor' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [ 'show_excerpt' => 'yes' ],
			]
		);

		$this->add_control(
			'excerpt_color',
			[
				'label'     => esc_html__( 'Color', 'architect-complete-theme-builder-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .archt-loop-excerpt' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'excerpt_typography',
				'selector' => '{{WRAPPER}} .archt-loop-excerpt',
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'style_read_more',
			[
				'label'     => esc_html__( 'Read More Button', 'architect-complete-theme-builder-for-elementor' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [ 'show_read_more' => 'yes' ],
			]
		);

		$this->start_controls_tabs( 'read_more_tabs' );

		$this->start_controls_tab(
			'read_more_normal',
			[ 'label' => esc_html__( 'Normal', 'architect-complete-theme-builder-for-elementor' ) ]
		);

		$this->add_control(
			'read_more_color',
			[
				'label'     => esc_html__( 'Text Color', 'architect-complete-theme-builder-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .archt-loop-read-more' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'read_more_bg',
			[
				'label'     => esc_html__( 'Background Color', 'architect-complete-theme-builder-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .archt-loop-read-more' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'read_more_hover',
			[ 'label' => esc_html__( 'Hover', 'architect-complete-theme-builder-for-elementor' ) ]
		);

		$this->add_control(
			'read_more_color_hover',
			[
				'label'     => esc_html__( 'Text Color', 'architect-complete-theme-builder-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .archt-loop-read-more:hover' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'read_more_bg_hover',
			[
				'label'     => esc_html__( 'Background Color', 'architect-complete-theme-builder-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .archt-loop-read-more:hover' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();
		$this->end_controls_tabs();

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'      => 'read_more_typography',
				'selector'  => '{{WRAPPER}} .archt-loop-read-more',
				'separator' => 'before',
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'read_more_border',
				'selector' => '{{WRAPPER}} .archt-loop-read-more',
			]
		);

		$this->add_responsive_control(
			'read_more_border_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'architect-complete-theme-builder-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors'  => [
					'{{WRAPPER}} .archt-loop-read-more' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'read_more_padding',
			[
				'label'      => esc_html__( 'Padding', 'architect-complete-theme-builder-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .archt-loop-read-more' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'style_pagination',
			[
				'label'     => esc_html__( 'Pagination', 'architect-complete-theme-builder-for-elementor' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [ 'show_pagination' => 'yes' ],
			]
		);

		$this->add_control(
			'pagination_color',
			[
				'label'     => esc_html__( 'Link Color', 'architect-complete-theme-builder-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .archt-loop-pagination a' => 'color: {{VALUE}};',
					'{{WRAPPER}} .archt-loop-pagination span' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'pagination_typography',
				'selector' => '{{WRAPPER}} .archt-loop-pagination',
			]
		);

		$this->add_responsive_control(
			'pagination_align',
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
					'{{WRAPPER}} .archt-loop-pagination' => 'text-align: {{VALUE}};',
				],
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Build a label => label map of public taxonomies.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @return array
	 */
	private function get_taxonomy_options() {
		$taxonomies = get_taxonomies( [ 'public' => true ], 'objects' );
		$options    = [];

		foreach ( $taxonomies as $slug => $tax ) {
			$options[ $slug ] = esc_html( $tax->label );
		}

		return $options;
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
	 * Render a single post card.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @param array $settings Widget settings.
	 */
	private function render_post_card( array $settings ) {
		$show_thumb   = 'yes' === $settings['show_thumbnail'];
		$thumb_size   = sanitize_key( $settings['thumbnail_size'] );
		$thumb_link   = 'yes' === $settings['thumbnail_link'];
		$show_title   = 'yes' === $settings['show_title'];
		$title_tag    = \Elementor\Utils::validate_html_tag( $settings['title_tag'] );
		$show_excerpt = 'yes' === $settings['show_excerpt'];
		$excerpt_len  = absint( $settings['excerpt_length'] );
		$show_meta    = 'yes' === $settings['show_meta'];
		$show_terms   = 'yes' === $settings['show_terms'];
		$taxonomy     = sanitize_key( $settings['terms_taxonomy'] );
		$show_rm      = 'yes' === $settings['show_read_more'];
		$rm_text      = esc_html( sanitize_text_field( $settings['read_more_text'] ) );

		echo '<article class="archt-loop-item">';

		if ( $show_thumb && has_post_thumbnail() ) {
			echo '<div class="archt-loop-thumbnail">';
			if ( $thumb_link ) {
				echo '<a href="' . esc_url( get_permalink() ) . '">';
			}
			echo wp_get_attachment_image(
				get_post_thumbnail_id(),
				$thumb_size,
				false,
				[
					'class' => 'archt-loop-thumb-img',
					'alt'   => esc_attr( get_the_title() ),
				]
			);
			if ( $thumb_link ) {
				echo '</a>';
			}
			echo '</div>';
		}

		echo '<div class="archt-loop-item-content">';

		if ( $show_terms ) {
			$terms = get_the_terms( get_the_ID(), $taxonomy );
			if ( $terms && ! is_wp_error( $terms ) ) {
				echo '<div class="archt-loop-terms">';
				$term_parts = [];
				foreach ( $terms as $term ) {
					$term_link    = get_term_link( $term );
					$term_parts[] = sprintf( '<a href="%s">%s</a>', esc_url( $term_link ), esc_html( $term->name ) );
				}
				echo wp_kses_post( implode( ' ', $term_parts ) );
				echo '</div>';
			}
		}

		if ( $show_title ) {
			printf(
				'<%1$s class="archt-loop-title"><a href="%2$s">%3$s</a></%1$s>',
				esc_attr( $title_tag ),
				esc_url( get_permalink() ),
				esc_html( get_the_title() )
			);
		}

		if ( $show_meta ) {
			echo '<div class="archt-loop-meta">';
			printf( '<span class="archt-loop-date">%s</span>', esc_html( get_the_date() ) );
			printf(
				'<span class="archt-loop-author">%s</span>',
				sprintf(
					/* translators: %s: Author display name. */
					esc_html__( 'by %s', 'architect-complete-theme-builder-for-elementor' ),
					esc_html( get_the_author() )
				)
			);
			echo '</div>';
		}

		if ( $show_excerpt ) {
			$post = get_post();
			// Raw post_content skips the_content filters and recursive rendering.
			$excerpt = $post ? ( $post->post_excerpt ? $post->post_excerpt : wp_strip_all_tags( get_post_field( 'post_content', $post->ID ) ) ) : '';
			if ( $excerpt_len ) {
				$excerpt = wp_trim_words( $excerpt, $excerpt_len, '...' );
			}
			if ( $excerpt ) {
				echo '<div class="archt-loop-excerpt">' . wp_kses_post( $excerpt ) . '</div>';
			}
		}

		if ( $show_rm ) {
			printf( '<a class="archt-loop-read-more" href="%s">%s</a>', esc_url( get_permalink() ), esc_html( $rm_text ) );
		}

		echo '</div>';
		echo '</article>';
	}

	/**
	 * Render widget output on the frontend.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function render() {
		global $wp_query;

		$settings = $this->get_settings_for_display();
		$layout   = $settings['layout'];

		$is_editor = \Elementor\Plugin::$instance->editor->is_edit_mode();

		// Viewing the template itself: use a mock query so it does not render inside itself.
		$is_direct_template_view = ( ! $is_editor && 'elementor_library' === get_post_type() );

		if ( $is_editor || $is_direct_template_view ) {
			$query = new WP_Query(
				[
					'post_type'           => 'post',
					'posts_per_page'      => 6,
					'post_status'         => 'publish',
					'ignore_sticky_posts' => true,
				]
			);
		} else {
			$query = $wp_query;
		}

		if ( ! $query->have_posts() ) {
			echo '<p class="archt-no-posts">' . esc_html__( 'No posts found.', 'architect-complete-theme-builder-for-elementor' ) . '</p>';
			return;
		}

		$loop_class = 'archt-posts-loop archt-layout-' . esc_attr( $layout );

		echo '<div class="' . esc_attr( $loop_class ) . '">';

		while ( $query->have_posts() ) {
			$query->the_post();
			$this->render_post_card( $settings );
		}

		echo '</div>';

		$query->rewind_posts();
		wp_reset_postdata();

		if ( 'yes' === $settings['show_pagination'] && ! $is_editor && $query->max_num_pages > 1 ) {
			$links = paginate_links(
				[
					'total'     => $query->max_num_pages,
					'current'   => max( 1, (int) get_query_var( 'paged' ) ),
					'prev_text' => wp_kses_post( $settings['prev_text'] ),
					'next_text' => wp_kses_post( $settings['next_text'] ),
				]
			);

			if ( $links ) {
				echo '<div class="archt-loop-pagination">' . wp_kses_post( $links ) . '</div>';
			}
		}
	}
}
