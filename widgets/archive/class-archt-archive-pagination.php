<?php
/**
 * Widget: Archive Pagination (Archive Templates).
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
 * Renders archive pagination. Intended for Archive templates.
 *
 * @since 1.0.0
 */
class ARCHT_Archive_Pagination_Widget extends Widget_Base {

	/**
	 * Get widget name.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string
	 */
	public function get_name() {
		return 'archt-archive-pagination';
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
		return esc_html__( 'Archive Pagination', 'architect-complete-theme-builder-for-elementor' );
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
		return 'eicon-post-list';
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
		return [ 'archive', 'pagination', 'pages', 'next', 'prev' ];
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
				'label' => esc_html__( 'Pagination', 'architect-complete-theme-builder-for-elementor' ),
			]
		);

		$this->add_control(
			'prev_text',
			[
				'label'   => esc_html__( 'Previous Text', 'architect-complete-theme-builder-for-elementor' ),
				'type'    => Controls_Manager::TEXT,
				'default' => esc_html__( '« Previous', 'architect-complete-theme-builder-for-elementor' ),
			]
		);

		$this->add_control(
			'next_text',
			[
				'label'   => esc_html__( 'Next Text', 'architect-complete-theme-builder-for-elementor' ),
				'type'    => Controls_Manager::TEXT,
				'default' => esc_html__( 'Next »', 'architect-complete-theme-builder-for-elementor' ),
			]
		);

		$this->add_control(
			'show_numbers',
			[
				'label'     => esc_html__( 'Show Page Numbers', 'architect-complete-theme-builder-for-elementor' ),
				'type'      => Controls_Manager::SWITCHER,
				'default'   => 'yes',
				'label_on'  => esc_html__( 'Yes', 'architect-complete-theme-builder-for-elementor' ),
				'label_off' => esc_html__( 'No', 'architect-complete-theme-builder-for-elementor' ),
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_style',
			[
				'label' => esc_html__( 'Pagination', 'architect-complete-theme-builder-for-elementor' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'align',
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
					'{{WRAPPER}} .archt-archive-pagination' => 'text-align: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'link_color',
			[
				'label'     => esc_html__( 'Link Color', 'architect-complete-theme-builder-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .archt-archive-pagination a' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'link_hover_color',
			[
				'label'     => esc_html__( 'Link Hover Color', 'architect-complete-theme-builder-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .archt-archive-pagination a:hover' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'current_color',
			[
				'label'     => esc_html__( 'Current Page Color', 'architect-complete-theme-builder-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .archt-archive-pagination .current' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'pagination_typography',
				'selector' => '{{WRAPPER}} .archt-archive-pagination',
			]
		);

		$this->add_responsive_control(
			'item_spacing',
			[
				'label'      => esc_html__( 'Item Spacing', 'architect-complete-theme-builder-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em' ],
				'default'    => [
					'size' => 8,
					'unit' => 'px',
				],
				'selectors'  => [
					'{{WRAPPER}} .archt-archive-pagination .page-numbers' => 'margin: 0 {{SIZE}}{{UNIT}};',
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
		global $wp_query;

		if ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
			echo '<div class="archt-archive-pagination">';
			echo '<a class="page-numbers" href="#">1</a>';
			echo '<span class="page-numbers current">2</span>';
			echo '<a class="page-numbers" href="#">3</a>';
			echo '</div>';
			return;
		}

		$settings  = $this->get_settings_for_display();
		$show_nums = 'yes' === $settings['show_numbers'];
		$prev_text = wp_kses_post( $settings['prev_text'] );
		$next_text = wp_kses_post( $settings['next_text'] );

		$pagination = paginate_links(
			[
				'total'     => $wp_query->max_num_pages,
				'current'   => max( 1, get_query_var( 'paged' ) ),
				'prev_text' => $prev_text,
				'next_text' => $next_text,
				'type'      => 'array',
			]
		);

		if ( empty( $pagination ) ) {
			return;
		}

		if ( ! $show_nums ) {
			$pagination = array_filter(
				$pagination,
				function ( $item ) {
					return false !== strpos( $item, 'prev' ) || false !== strpos( $item, 'next' );
				}
			);
		}

		if ( empty( $pagination ) ) {
			return;
		}

		echo '<nav class="archt-archive-pagination" aria-label="' . esc_attr__( 'Archive navigation', 'architect-complete-theme-builder-for-elementor' ) . '">';
		echo wp_kses_post( implode( ' ', $pagination ) );
		echo '</nav>';
	}
}
