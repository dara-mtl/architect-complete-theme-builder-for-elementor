<?php
/**
 * Widget: Post Meta (Single Templates).
 *
 * @package ARCHT_Widgets
 * @since 1.0.0
 */

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Repeater;
use Elementor\Widget_Base;
use ARCHT\Inc\Classes\ARCHT_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Renders a flexible post meta bar. Intended for Single templates.
 *
 * @since 1.0.0
 */
class ARCHT_Post_Meta_Widget extends Widget_Base {

	/**
	 * Get widget name.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string
	 */
	public function get_name() {
		return 'archt-post-meta';
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
		return esc_html__( 'Post Meta', 'architect-complete-theme-builder-for-elementor' );
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
		return 'eicon-post-info';
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
		return [ 'post', 'meta', 'date', 'author', 'comments', 'terms', 'taxonomy', 'single' ];
	}

	/**
	 * Register widget controls.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function register_controls() {

		$this->start_controls_section(
			'section_meta_items',
			[
				'label' => esc_html__( 'Meta Items', 'architect-complete-theme-builder-for-elementor' ),
			]
		);

		$repeater = new Repeater();

		$repeater->add_control(
			'meta_type',
			[
				'label'   => esc_html__( 'Type', 'architect-complete-theme-builder-for-elementor' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'date',
				'options' => [
					'date'          => esc_html__( 'Published Date', 'architect-complete-theme-builder-for-elementor' ),
					'modified_date' => esc_html__( 'Modified Date', 'architect-complete-theme-builder-for-elementor' ),
					'author'        => esc_html__( 'Author', 'architect-complete-theme-builder-for-elementor' ),
					'comments'      => esc_html__( 'Comments', 'architect-complete-theme-builder-for-elementor' ),
					'terms'         => esc_html__( 'Terms / Taxonomy', 'architect-complete-theme-builder-for-elementor' ),
				],
			]
		);

		$repeater->add_control(
			'selected_icon',
			[
				'label'            => esc_html__( 'Icon', 'architect-complete-theme-builder-for-elementor' ),
				'type'             => Controls_Manager::ICONS,
				'fa4compatibility' => 'icon',
			]
		);

		$repeater->add_control(
			'show_icon',
			[
				'label'     => esc_html__( 'Show Icon', 'architect-complete-theme-builder-for-elementor' ),
				'type'      => Controls_Manager::SWITCHER,
				'default'   => 'yes',
				'label_on'  => esc_html__( 'Yes', 'architect-complete-theme-builder-for-elementor' ),
				'label_off' => esc_html__( 'No', 'architect-complete-theme-builder-for-elementor' ),
			]
		);

		$repeater->add_control(
			'show_label',
			[
				'label'     => esc_html__( 'Show Label', 'architect-complete-theme-builder-for-elementor' ),
				'type'      => Controls_Manager::SWITCHER,
				'default'   => '',
				'label_on'  => esc_html__( 'Yes', 'architect-complete-theme-builder-for-elementor' ),
				'label_off' => esc_html__( 'No', 'architect-complete-theme-builder-for-elementor' ),
			]
		);

		$repeater->add_control(
			'custom_label',
			[
				'label'     => esc_html__( 'Label', 'architect-complete-theme-builder-for-elementor' ),
				'type'      => Controls_Manager::TEXT,
				'condition' => [
					'show_label' => 'yes',
				],
			]
		);

		$repeater->add_control(
			'date_format',
			[
				'label'     => esc_html__( 'Date Format', 'architect-complete-theme-builder-for-elementor' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'j M. Y',
				'options'   => [
					'j M. Y'    => esc_html__( 'Default (j M. Y)', 'architect-complete-theme-builder-for-elementor' ),
					'd/m/Y'     => esc_html__( 'DD/MM/YYYY', 'architect-complete-theme-builder-for-elementor' ),
					'm/d/Y'     => esc_html__( 'MM/DD/YYYY', 'architect-complete-theme-builder-for-elementor' ),
					'Y-m-d'     => esc_html__( 'YYYY-MM-DD', 'architect-complete-theme-builder-for-elementor' ),
					'F j, Y'    => esc_html__( 'Month DD, YYYY', 'architect-complete-theme-builder-for-elementor' ),
					'from_time' => esc_html__( 'Time Ago (e.g. 3 days ago)', 'architect-complete-theme-builder-for-elementor' ),
				],
				'condition' => [
					'meta_type' => [ 'date', 'modified_date' ],
				],
			]
		);

		$repeater->add_control(
			'author_link',
			[
				'label'     => esc_html__( 'Link to Author Archive', 'architect-complete-theme-builder-for-elementor' ),
				'type'      => Controls_Manager::SWITCHER,
				'default'   => 'yes',
				'condition' => [
					'meta_type' => 'author',
				],
			]
		);

		$repeater->add_control(
			'taxonomy',
			[
				'label'     => esc_html__( 'Taxonomy', 'architect-complete-theme-builder-for-elementor' ),
				'type'      => Controls_Manager::SELECT,
				'options'   => $this->get_taxonomy_options(),
				'default'   => 'category',
				'condition' => [
					'meta_type' => 'terms',
				],
			]
		);

		$repeater->add_control(
			'terms_link',
			[
				'label'     => esc_html__( 'Link Terms', 'architect-complete-theme-builder-for-elementor' ),
				'type'      => Controls_Manager::SWITCHER,
				'default'   => 'yes',
				'condition' => [
					'meta_type' => 'terms',
				],
			]
		);

		$repeater->add_control(
			'terms_separator',
			[
				'label'     => esc_html__( 'Separator', 'architect-complete-theme-builder-for-elementor' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => ', ',
				'condition' => [
					'meta_type' => 'terms',
				],
			]
		);

		$this->add_control(
			'meta_items',
			[
				'label'       => esc_html__( 'Items', 'architect-complete-theme-builder-for-elementor' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'default'     => [
					[
						'meta_type' => 'date',
						'show_icon' => 'yes',
					],
					[
						'meta_type' => 'author',
						'show_icon' => 'yes',
					],
					[
						'meta_type' => 'comments',
						'show_icon' => 'yes',
					],
				],
				'title_field' => '{{{ meta_type }}}',
			]
		);

		$this->add_control(
			'item_separator',
			[
				'label'     => esc_html__( 'Separator Between Items', 'architect-complete-theme-builder-for-elementor' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => ' | ',
				'separator' => 'before',
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_style',
			[
				'label' => esc_html__( 'Meta', 'architect-complete-theme-builder-for-elementor' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'meta_color',
			[
				'label'     => esc_html__( 'Text Color', 'architect-complete-theme-builder-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .archt-post-meta' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'meta_link_color',
			[
				'label'     => esc_html__( 'Link Color', 'architect-complete-theme-builder-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .archt-post-meta a' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'meta_link_color_hover',
			[
				'label'     => esc_html__( 'Link Hover Color', 'architect-complete-theme-builder-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .archt-post-meta a:hover' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'meta_typography',
				'selector' => '{{WRAPPER}} .archt-post-meta',
			]
		);

		$this->add_responsive_control(
			'meta_align',
			[
				'label'     => esc_html__( 'Alignment', 'architect-complete-theme-builder-for-elementor' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => [
					'left'   => [
						'title' => esc_html__( 'Left', 'architect-complete-theme-builder-for-elementor' ),
						'icon'  => 'eicon-text-align-left',
					],
					'center' => [
						'title' => esc_html__( 'Center', 'architect-complete-theme-builder-for-elementor' ),
						'icon'  => 'eicon-text-align-center',
					],
					'right'  => [
						'title' => esc_html__( 'Right', 'architect-complete-theme-builder-for-elementor' ),
						'icon'  => 'eicon-text-align-right',
					],
				],
				'selectors' => [
					'{{WRAPPER}} .archt-post-meta' => 'text-align: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'icon_heading',
			[
				'label'     => esc_html__( 'Icon', 'architect-complete-theme-builder-for-elementor' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'icon_color',
			[
				'label'     => esc_html__( 'Icon Color', 'architect-complete-theme-builder-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .archt-meta-icon'     => 'color: {{VALUE}};',
					'{{WRAPPER}} .archt-meta-icon svg' => 'fill: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'icon_size',
			[
				'label'      => esc_html__( 'Icon Size', 'architect-complete-theme-builder-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em' ],
				'range'      => [
					'px' => [
						'min' => 8,
						'max' => 48,
					],
				],
				'selectors'  => [
					'{{WRAPPER}} .archt-meta-icon'     => 'font-size: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .archt-meta-icon svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'icon_gap',
			[
				'label'      => esc_html__( 'Icon Gap', 'architect-complete-theme-builder-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em' ],
				'range'      => [
					'px' => [
						'min' => 0,
						'max' => 20,
					],
				],
				'default'    => [
					'size' => 4,
					'unit' => 'px',
				],
				'selectors'  => [
					'{{WRAPPER}} .archt-meta-item' => 'gap: {{SIZE}}{{UNIT}};',
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
	 * Render a single meta item and return the HTML string.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @param array $item Repeater item data.
	 * @return string Raw (un-kses'd) HTML for this item.
	 */
	private function render_meta_item( array $item ) {
		$type       = $item['meta_type'];
		$show_icon  = 'yes' === $item['show_icon'];
		$show_label = 'yes' === $item['show_label'];
		$label      = isset( $item['custom_label'] ) ? esc_html( sanitize_text_field( $item['custom_label'] ) ) : '';

		$value_html = '';

		switch ( $type ) {
			case 'date':
			case 'modified_date':
				$date_format = ! empty( $item['date_format'] ) ? $item['date_format'] : 'j M. Y';

				if ( 'from_time' === $date_format ) {
					$timestamp  = 'date' === $type ? get_post_time( 'U', true ) : get_post_modified_time( 'U', true );
					/* translators: %s: Time difference, e.g. "3 days". */
					$value_html = esc_html( sprintf( __( '%s ago', 'architect-complete-theme-builder-for-elementor' ), human_time_diff( $timestamp ) ) );
				} else {
					$date_format = sanitize_text_field( $date_format );
					$value_html  = 'date' === $type
						? esc_html( get_the_date( $date_format ) )
						: esc_html( get_the_modified_date( $date_format ) );
				}
				break;

			case 'author':
				// Read from the post itself, as the $authordata global isn't always set for pages.
				$author_id   = (int) get_post_field( 'post_author' );
				$author_name = esc_html( get_the_author_meta( 'display_name', $author_id ) );
				if ( 'yes' === $item['author_link'] ) {
					$author_url = esc_url( get_author_posts_url( $author_id ) );
					$value_html = sprintf( '<a href="%s">%s</a>', $author_url, $author_name );
				} else {
					$value_html = $author_name;
				}
				break;

			case 'comments':
				$count      = (int) get_comments_number();
				$value_html = sprintf(
					/* translators: %d: Number of comments. */
					esc_html( _n( '%d Comment', '%d Comments', $count, 'architect-complete-theme-builder-for-elementor' ) ),
					$count
				);
				break;

			case 'terms':
				$taxonomy  = sanitize_key( $item['taxonomy'] );
				$terms     = get_the_terms( get_the_ID(), $taxonomy );
				$separator = isset( $item['terms_separator'] ) ? sanitize_text_field( $item['terms_separator'] ) : ', ';

				if ( is_wp_error( $terms ) || empty( $terms ) ) {
					return '';
				}

				$term_parts = [];
				foreach ( $terms as $term ) {
					$term_name = esc_html( $term->name );
					if ( 'yes' === $item['terms_link'] ) {
						$term_link = get_term_link( $term );
						if ( ! is_wp_error( $term_link ) ) {
							$term_name = sprintf( '<a href="%s">%s</a>', esc_url( $term_link ), $term_name );
						}
					}
					$term_parts[] = $term_name;
				}

				$value_html = implode( esc_html( $separator ), $term_parts );
				break;

			default:
				return '';
		}

		if ( empty( $value_html ) ) {
			return '';
		}

		$html = '<span class="archt-meta-item">';

		if ( $show_icon && ! empty( $item['selected_icon']['value'] ) ) {
			$icon_html = ARCHT_Helper::archt_get_icons( $item['selected_icon'] );
			if ( $icon_html ) {
				$html .= '<span class="archt-meta-icon">' . $icon_html . '</span>';
			}
		}

		if ( $show_label && $label ) {
			$html .= sprintf( '<span class="archt-meta-label">%s</span>', $label );
		}

		$html .= sprintf( '<span class="archt-meta-value">%s</span>', wp_kses_post( $value_html ) );
		$html .= '</span>';

		return $html;
	}

	/**
	 * Render widget output on the frontend.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function render() {
		$settings  = $this->get_settings_for_display();
		$items     = $settings['meta_items'];
		$separator = sanitize_text_field( $settings['item_separator'] );

		if ( empty( $items ) ) {
			return;
		}

		$parts = [];

		foreach ( $items as $item ) {
			$rendered = $this->render_meta_item( $item );
			if ( ! empty( $rendered ) ) {
				$parts[] = $rendered;
			}
		}

		if ( empty( $parts ) ) {
			return;
		}

		// Keeps <i> and <svg> icons, which wp_kses_post() strips.
		echo '<div class="archt-post-meta">';
		echo ARCHT_Helper::sanitize_and_escape_svg_input( implode( esc_html( $separator ), $parts ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo '</div>';
	}
}
