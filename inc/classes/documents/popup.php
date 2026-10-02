<?php
/**
 * Popup template document.
 *
 * @package ARCHT_Widgets
 * @since 1.0.0
 */

namespace ARCHT_Library\Custom_Documents;

use Elementor\Modules\PageTemplates\Module as Page_Templates_Module;
use Elementor\Core\DocumentTypes\Post;
use Elementor\Modules\Library\Documents\Library_Document;
use ARCHT_Library\Inc\Traits\ARCHT_Display_Conditions_Trait;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Popup template document.
 *
 * @since 1.0.0
 */
class Popup extends Library_Document {
	use ARCHT_Display_Conditions_Trait;

	/**
	 * Get document properties.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return array
	 */
	public static function get_properties() {
		$properties                              = parent::get_properties();
		$properties['support_wp_page_templates'] = true;
		$properties['support_kit']               = true;
		$properties['show_in_finder']            = true;
		return $properties;
	}

	/**
	 * Get the document type.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string
	 */
	public static function get_type() {
		return 'popup';
	}

	/**
	 * Get document name.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string
	 */
	public function get_name() {
		return 'popup';
	}

	/**
	 * Get document title.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string
	 */
	public static function get_title() {
		return esc_html__( 'Popup', 'architect-complete-theme-builder-for-elementor' );
	}

	/**
	 * Get the plural title.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string
	 */
	public static function get_plural_title() {
		return esc_html__( 'Popups', 'architect-complete-theme-builder-for-elementor' );
	}

	/**
	 * Get the "Add New" title.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string
	 */
	public static function get_add_new_title() {
		return esc_html__( 'Add New Popup Template', 'architect-complete-theme-builder-for-elementor' );
	}

	/**
	 * Get the CSS wrapper selector.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string
	 */
	public function get_css_wrapper_selector() {
		return '.elementor-popup-' . $this->get_main_id();
	}

	/**
	 * Save the document on the canvas page template.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param array $data Document data.
	 * @return bool
	 */
	public function save( $data ) {
		update_post_meta( $this->get_main_id(), '_wp_page_template', 'elementor_canvas' );

		return parent::save( $data );
	}

	/**
	 * Whether the popup renders in the editor or its preview iframe.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @return bool
	 */
	private function is_editing_context() {
		return \Elementor\Plugin::$instance->editor->is_edit_mode()
			|| \Elementor\Plugin::$instance->preview->is_preview_mode( $this->get_main_id() );
	}

	/**
	 * Get the popup wrapper attributes.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return array
	 */
	public function get_container_attributes() {
		$attributes = parent::get_container_attributes();
		$settings   = parent::get_settings_for_display();

		$document_id = 'archt-popup-' . $this->get_main_id();

		$custom_classes = [ 'elementor-popup-' . $this->get_main_id() ];

		if ( ! $this->is_editing_context() ) {
			$custom_classes[] = 'archt-hide';
		}

		// "animated" carries the duration; Elementor's keyframes only set the name.
		if ( ! $this->is_editing_context() ) {
			if ( ! empty( $settings['entrance_animation'] ) || ! empty( $settings['exit_animation'] ) ) {
				$custom_classes[] = 'animated';
			}

			if ( ! empty( $settings['entrance_animation'] ) ) {
				$custom_classes[] = $settings['entrance_animation'];
			}
		}

		if ( isset( $settings['animation_speed'] ) && $settings['animation_speed'] > 0 ) {
			$duration            = floatval( $settings['animation_speed'] );
			$attributes['style'] = ( isset( $attributes['style'] ) ? $attributes['style'] . '; ' : '' ) . "animation-duration: {$duration}s;";
		}

		$attributes['class']              .= ' ' . implode( ' ', $custom_classes );
		$attributes['id']                  = $document_id;
		$attributes['data-popup-id']       = esc_attr( $this->get_main_id() );
		$attributes['data-prevent-scroll'] = ( ! empty( $settings['prevent_scroll'] ) && 'yes' === $settings['prevent_scroll'] ) ? '1' : '0';
		$attributes['data-close-on-bg']    = ! empty( $settings['close_on_bg'] ) ? '1' : '0';
		$attributes['data-close-button']   = ( isset( $settings['close_button'] ) && 'none' === $settings['close_button'] ) ? '0' : '1';
		$attributes['data-exclusive']      = ( ! empty( $settings['exclusive'] ) && 'yes' === $settings['exclusive'] ) ? '1' : '0';

		if ( ! empty( $settings['height_type'] ) ) {
			$attributes['data-height'] = esc_attr( $settings['height_type'] );
		}

		$active_triggers = [];

		if ( ! empty( $settings['trigger_onload'] ) && 'yes' === $settings['trigger_onload'] ) {
			$active_triggers[] = 'onload';
		}

		if ( ! empty( $settings['trigger_delay'] ) && 'yes' === $settings['trigger_delay'] ) {
			$active_triggers[] = 'delay';
		}

		if ( ! empty( $settings['trigger_scroll'] ) && 'yes' === $settings['trigger_scroll'] ) {
			$active_triggers[] = 'scroll';
		}

		$attributes['data-triggers'] = esc_attr( implode( ',', $active_triggers ) );

		if ( ! empty( $settings['open_trigger_class'] ) ) {
			$attributes['data-open-class'] = esc_attr( $settings['open_trigger_class'] );
		}

		if ( ! empty( $settings['trigger_delay'] ) && 'yes' === $settings['trigger_delay'] && isset( $settings['trigger_delay_seconds'] ) ) {
			$attributes['data-delay'] = (float) $settings['trigger_delay_seconds'];
		}

		if ( ! empty( $settings['trigger_scroll'] ) && 'yes' === $settings['trigger_scroll'] && isset( $settings['trigger_scroll_percent'] ) ) {
			$attributes['data-scroll'] = (int) $settings['trigger_scroll_percent'];
		}

		$show_times                    = isset( $settings['show_times'] ) ? (int) $settings['show_times'] : 0;
		$attributes['data-show-times'] = $show_times;

		if ( isset( $settings['section_overlay'] ) && $settings['section_overlay'] ) {
			$attributes['data-overlay-color'] = esc_attr( $settings['section_overlay'] );
		}

		if ( ! empty( $settings['entrance_animation'] ) ) {
			$attributes['data-entrance-animation'] = esc_attr( $settings['entrance_animation'] );
		}

		if ( ! empty( $settings['exit_animation'] ) ) {
			$attributes['data-exit-animation'] = esc_attr( $settings['exit_animation'] );
		}

		return $attributes;
	}

	/**
	 * Register the controls.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function register_controls() {
		parent::register_controls();
		$this->remove_control( 'elementor_library_type_filter_type' );
		$this->remove_control( 'elementor_library_type' );
		$this->start_controls_section(
			'popop_animation',
			[
				'label' => esc_html__( 'Animation', 'architect-complete-theme-builder-for-elementor' ),
				'tab'   => \Elementor\Controls_Manager::TAB_SETTINGS,
			]
		);

		// No prefix_class: both controls would write the same "animated" class; see get_container_attributes().
		$this->add_control(
			'entrance_animation',
			[
				'label'   => esc_html__( 'Entrance Animation', 'architect-complete-theme-builder-for-elementor' ),
				'type'    => \Elementor\Controls_Manager::ANIMATION,
				'default' => 'fadeIn',
			]
		);

		$this->add_control(
			'exit_animation',
			[
				'label'       => esc_html__( 'Exit Animation', 'architect-complete-theme-builder-for-elementor' ),
				'type'        => \Elementor\Controls_Manager::EXIT_ANIMATION,
				'default'     => 'fadeIn',
				'description' => esc_html__( 'Elementor lists exit animations by their entrance name - "Fade Out" is the reversed Fade In. The popup plays it in reverse on close.', 'architect-complete-theme-builder-for-elementor' ),
			]
		);

		$this->add_control(
			'animation_speed',
			[
				'label'              => esc_html__( 'Animation Duration (s)', 'architect-complete-theme-builder-for-elementor' ),
				'type'               => \Elementor\Controls_Manager::NUMBER,
				'min'                => 0,
				'max'                => 60,
				'step'               => 0.1,
				'default'            => 0.4,
				'frontend_available' => true,
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'popup_layout',
			[
				'label' => esc_html__( 'Layout', 'architect-complete-theme-builder-for-elementor' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'width',
			[
				'label'      => esc_html__( 'Width', 'architect-complete-theme-builder-for-elementor' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%', 'em', 'rem', 'vw', 'custom' ],
				'range'      => [
					'px' => [
						'min' => 100,
						'max' => 1000,
					],
					'%'  => [
						'min' => 1,
						'max' => 100,
					],
					'vh' => [
						'min' => 10,
						'max' => 100,
					],
				],
				'default'    => [
					'size' => 640,
				],
				'selectors'  => [
					'.elementor-' . $this->get_main_id() => 'width: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'max-width',
			[
				'label'      => esc_html__( 'Max Width', 'architect-complete-theme-builder-for-elementor' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%', 'em', 'rem', 'vw', 'custom' ],
				'range'      => [
					'px' => [
						'min' => 100,
						'max' => 1000,
					],
					'%'  => [
						'min' => 1,
						'max' => 100,
					],
					'vh' => [
						'min' => 10,
						'max' => 100,
					],
				],
				'default'    => [
					'unit' => '%',
					'size' => 100,
				],
				'selectors'  => [
					'.elementor-' . $this->get_main_id() => 'max-width: {{SIZE}}{{UNIT}};',
				],
			]
		);

		// dvh follows the visible viewport on mobile, so a full-height popup leaves no gap under the browser bars.
		$this->add_control(
			'height_type',
			[
				'label'                => esc_html__( 'Height', 'architect-complete-theme-builder-for-elementor' ),
				'type'                 => \Elementor\Controls_Manager::SELECT,
				'default'              => '',
				'options'              => [
					''              => esc_html__( 'Fit To Content', 'architect-complete-theme-builder-for-elementor' ),
					'fit_to_screen' => esc_html__( 'Fit To Screen', 'architect-complete-theme-builder-for-elementor' ),
					'custom'        => esc_html__( 'Custom', 'architect-complete-theme-builder-for-elementor' ),
				],
				'selectors'            => [
					'.elementor-' . $this->get_main_id() => '{{VALUE}}',
				],
				'selectors_dictionary' => [
					'fit_to_screen' => 'height: 100vh; height: 100dvh; max-height: none;',
					'custom'        => 'max-height: 100vh; max-height: 100dvh;',
				],
			]
		);

		$this->add_responsive_control(
			'height',
			[
				'label'      => esc_html__( 'Custom Height', 'architect-complete-theme-builder-for-elementor' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'vh', 'custom' ],
				'range'      => [
					'px' => [
						'min' => 100,
						'max' => 1000,
					],
					'vh' => [
						'min' => 10,
						'max' => 100,
					],
				],
				'default'    => [
					'unit' => 'px',
					'size' => 400,
				],
				'selectors'  => [
					'.elementor-' . $this->get_main_id() => 'height: {{SIZE}}{{UNIT}};',
				],
				'condition'  => [ 'height_type' => 'custom' ],
			]
		);

		$this->add_responsive_control(
			'popup_padding',
			[
				'label'      => esc_html__( 'Padding', 'architect-complete-theme-builder-for-elementor' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em', 'rem' ],
				'selectors'  => [
					'.elementor-' . $this->get_main_id() =>
						'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		// Applied as inset, since the auto margins already position the popup. The editor box is not fixed, so the
		// preview body gets the same space as padding.
		$this->add_responsive_control(
			'popup_margin',
			[
				'label'       => esc_html__( 'Margin', 'architect-complete-theme-builder-for-elementor' ),
				'type'        => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units'  => [ 'px', '%', 'em', 'rem', 'vw', 'vh' ],
				'description' => esc_html__( 'Space between the popup and the edges of the screen.', 'architect-complete-theme-builder-for-elementor' ),
				'selectors'   => [
					'body:not(.elementor-editor-active) .elementor-' . $this->get_main_id() =>
						'inset: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					'body.elementor-editor-active' =>
						'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Background::get_type(),
			[
				'name'           => 'popup_background',
				'types'          => [ 'classic', 'gradient' ],
				'selector'       => '.elementor-' . $this->get_main_id(),
				'fields_options' => [
					'background' => [ 'default' => 'classic' ],
					'color'      => [ 'default' => '#ffffff' ],
				],
			]
		);

		$this->add_control(
			'position_heading',
			[
				'label'     => esc_html__( 'Position', 'architect-complete-theme-builder-for-elementor' ),
				'type'      => \Elementor\Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_responsive_control(
			'horizontal_position',
			[
				'label'                => esc_html__( 'Horizontal', 'architect-complete-theme-builder-for-elementor' ),
				'type'                 => \Elementor\Controls_Manager::CHOOSE,
				'toggle'               => false,
				'default'              => 'center',
				'options'              => [
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
				'selectors'            => [
					'.elementor-' . $this->get_main_id() => '{{VALUE}}',
				],
				'selectors_dictionary' => [
					'left'   => 'margin-left: 0; margin-right: auto;',
					'center' => 'margin-left: auto; margin-right: auto;',
					'right'  => 'margin-left: auto; margin-right: 0;',
				],
			]
		);

		$this->add_responsive_control(
			'vertical_position',
			[
				'label'                => esc_html__( 'Vertical', 'architect-complete-theme-builder-for-elementor' ),
				'type'                 => \Elementor\Controls_Manager::CHOOSE,
				'toggle'               => false,
				'default'              => 'center',
				'options'              => [
					'top'    => [
						'title' => esc_html__( 'Top', 'architect-complete-theme-builder-for-elementor' ),
						'icon'  => 'eicon-v-align-top',
					],
					'center' => [
						'title' => esc_html__( 'Center', 'architect-complete-theme-builder-for-elementor' ),
						'icon'  => 'eicon-v-align-middle',
					],
					'bottom' => [
						'title' => esc_html__( 'Bottom', 'architect-complete-theme-builder-for-elementor' ),
						'icon'  => 'eicon-v-align-bottom',
					],
				],
				'selectors'            => [
					'.elementor-' . $this->get_main_id() => '{{VALUE}}',
				],
				'selectors_dictionary' => [
					'top'    => 'margin-top: 0; margin-bottom: auto;',
					'center' => 'margin-top: auto; margin-bottom: auto;',
					'bottom' => 'margin-top: auto; margin-bottom: 0;',
				],
			]
		);

		$this->add_control(
			'section_overlay',
			[
				'label'     => esc_html__( 'Overlay Color', 'architect-complete-theme-builder-for-elementor' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => 'rgba(0,0,0,0.5)',
				'selectors' => [
					'body.elementor-editor-active, .popup-bg-' . $this->get_main_id() => 'background: {{VALUE}};',
				],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'popup_close_button',
			[
				'label' => esc_html__( 'Close Button', 'architect-complete-theme-builder-for-elementor' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		// A SELECT, not a SWITCHER: the empty "off" value emits no CSS, so toggling wouldn't update the canvas.
		$this->add_control(
			'close_button',
			[
				'label'     => esc_html__( 'Close Button', 'architect-complete-theme-builder-for-elementor' ),
				'type'      => \Elementor\Controls_Manager::SELECT,
				'default'   => 'flex',
				'options'   => [
					'flex' => esc_html__( 'Show', 'architect-complete-theme-builder-for-elementor' ),
					'none' => esc_html__( 'Hide', 'architect-complete-theme-builder-for-elementor' ),
				],
				'selectors' => [
					'.elementor-popup-' . $this->get_main_id() . ' .archt-popup-close-btn' => 'display: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'close_button_size',
			[
				'label'      => esc_html__( 'Icon Size', 'architect-complete-theme-builder-for-elementor' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em' ],
				'range'      => [
					'px' => [
						'min' => 8,
						'max' => 80,
					],
				],
				'selectors'  => [
					'.elementor-popup-' . $this->get_main_id() . ' .archt-popup-close-btn' => 'font-size: {{SIZE}}{{UNIT}};',
					'.elementor-popup-' . $this->get_main_id() . ' .archt-popup-close-btn svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
				],
				'condition'  => [ 'close_button' => 'flex' ],
			]
		);

		$this->add_responsive_control(
			'close_button_offset_y',
			[
				'label'      => esc_html__( 'Offset Top', 'architect-complete-theme-builder-for-elementor' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%' ],
				'range'      => [
					'px' => [
						'min' => -100,
						'max' => 200,
					],
				],
				'selectors'  => [
					'.elementor-popup-' . $this->get_main_id() . ' .archt-popup-close-btn' => 'top: {{SIZE}}{{UNIT}};',
				],
				'condition'  => [ 'close_button' => 'flex' ],
			]
		);

		$this->add_responsive_control(
			'close_button_offset_x',
			[
				'label'      => esc_html__( 'Offset Side', 'architect-complete-theme-builder-for-elementor' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%' ],
				'range'      => [
					'px' => [
						'min' => -100,
						'max' => 200,
					],
				],
				'selectors'  => [
					'body:not(.rtl) .elementor-popup-' . $this->get_main_id() . ' .archt-popup-close-btn' => 'right: {{SIZE}}{{UNIT}};',
					'body.rtl .elementor-popup-' . $this->get_main_id() . ' .archt-popup-close-btn'       => 'left: {{SIZE}}{{UNIT}};',
				],
				'condition'  => [ 'close_button' => 'flex' ],
			]
		);

		$this->start_controls_tabs( 'close_button_tabs', [ 'condition' => [ 'close_button' => 'flex' ] ] );

		$this->start_controls_tab( 'close_button_tab_normal', [ 'label' => esc_html__( 'Normal', 'architect-complete-theme-builder-for-elementor' ) ] );

		$this->add_control(
			'close_button_color',
			[
				'label'     => esc_html__( 'Icon Color', 'architect-complete-theme-builder-for-elementor' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'.elementor-popup-' . $this->get_main_id() . ' .archt-popup-close-btn'     => 'color: {{VALUE}};',
					'.elementor-popup-' . $this->get_main_id() . ' .archt-popup-close-btn svg' => 'fill: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'close_button_background',
			[
				'label'     => esc_html__( 'Background', 'architect-complete-theme-builder-for-elementor' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'.elementor-popup-' . $this->get_main_id() . ' .archt-popup-close-btn' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab( 'close_button_tab_hover', [ 'label' => esc_html__( 'Hover', 'architect-complete-theme-builder-for-elementor' ) ] );

		$this->add_control(
			'close_button_color_hover',
			[
				'label'     => esc_html__( 'Icon Color', 'architect-complete-theme-builder-for-elementor' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'.elementor-popup-' . $this->get_main_id() . ' .archt-popup-close-btn:hover'     => 'color: {{VALUE}};',
					'.elementor-popup-' . $this->get_main_id() . ' .archt-popup-close-btn:hover svg' => 'fill: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'close_button_background_hover',
			[
				'label'     => esc_html__( 'Background', 'architect-complete-theme-builder-for-elementor' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'.elementor-popup-' . $this->get_main_id() . ' .archt-popup-close-btn:hover' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			[
				'name'      => 'close_button_border',
				'selector'  => '.elementor-popup-' . $this->get_main_id() . ' .archt-popup-close-btn',
				'condition' => [ 'close_button' => 'flex' ],
			]
		);

		$this->add_control(
			'close_button_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'architect-complete-theme-builder-for-elementor' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'.elementor-popup-' . $this->get_main_id() . ' .archt-popup-close-btn' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
				'condition'  => [ 'close_button' => 'flex' ],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'popup_advanced',
			[
				'label' => esc_html__( 'Popup Settings', 'architect-complete-theme-builder-for-elementor' ),
				'tab'   => \Elementor\Controls_Manager::TAB_ADVANCED,
			]
		);

		$this->add_control(
			'prevent_scroll',
			[
				'label'        => esc_html__( 'Prevent Page Scrolling', 'architect-complete-theme-builder-for-elementor' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'architect-complete-theme-builder-for-elementor' ),
				'label_off'    => esc_html__( 'No', 'architect-complete-theme-builder-for-elementor' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			]
		);

		$this->add_control(
			'close_on_bg',
			[
				'label'        => esc_html__( 'Close on Overlay Click', 'architect-complete-theme-builder-for-elementor' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'architect-complete-theme-builder-for-elementor' ),
				'label_off'    => esc_html__( 'No', 'architect-complete-theme-builder-for-elementor' ),
				'return_value' => '1',
				'default'      => '1',
			]
		);

		$this->add_control(
			'exclusive',
			[
				'label'        => esc_html__( 'Avoid Multiple Popups', 'architect-complete-theme-builder-for-elementor' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'architect-complete-theme-builder-for-elementor' ),
				'label_off'    => esc_html__( 'No', 'architect-complete-theme-builder-for-elementor' ),
				'return_value' => 'yes',
				'default'      => '',
				'description'  => esc_html__( 'When enabled, opening this popup will close all other open popups first.', 'architect-complete-theme-builder-for-elementor' ),
			]
		);

		$this->add_control(
			'close_button_info',
			[
				'type'        => \Elementor\Controls_Manager::NOTICE,
				'notice_type' => 'info',
				'dismissible' => false,
				'content'     => esc_html__( 'Add the CSS class "archt-popup-close" to any widget to make it close this popup.', 'architect-complete-theme-builder-for-elementor' ),
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'popup_triggers',
			[
				'label' => esc_html__( 'Triggers', 'architect-complete-theme-builder-for-elementor' ),
				'tab'   => \Elementor\Controls_Manager::TAB_ADVANCED,
			]
		);

		$this->add_control(
			'trigger_onload',
			[
				'label'        => esc_html__( 'On Page Load', 'architect-complete-theme-builder-for-elementor' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'architect-complete-theme-builder-for-elementor' ),
				'label_off'    => esc_html__( 'No', 'architect-complete-theme-builder-for-elementor' ),
				'return_value' => 'yes',
				'default'      => '',
			]
		);

		$this->add_control(
			'trigger_delay',
			[
				'label'        => esc_html__( 'After Delay', 'architect-complete-theme-builder-for-elementor' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'architect-complete-theme-builder-for-elementor' ),
				'label_off'    => esc_html__( 'No', 'architect-complete-theme-builder-for-elementor' ),
				'return_value' => 'yes',
				'default'      => '',
			]
		);

		$this->add_control(
			'trigger_delay_seconds',
			[
				'label'     => esc_html__( 'Delay (seconds)', 'architect-complete-theme-builder-for-elementor' ),
				'type'      => \Elementor\Controls_Manager::NUMBER,
				'default'   => 2,
				'min'       => 0,
				'step'      => 0.5,
				'condition' => [
					'trigger_delay' => 'yes',
				],
			]
		);

		$this->add_control(
			'trigger_scroll',
			[
				'label'        => esc_html__( 'On Scroll', 'architect-complete-theme-builder-for-elementor' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'architect-complete-theme-builder-for-elementor' ),
				'label_off'    => esc_html__( 'No', 'architect-complete-theme-builder-for-elementor' ),
				'return_value' => 'yes',
				'default'      => '',
			]
		);

		$this->add_control(
			'trigger_scroll_percent',
			[
				'label'     => esc_html__( 'Scroll Percentage (%)', 'architect-complete-theme-builder-for-elementor' ),
				'type'      => \Elementor\Controls_Manager::NUMBER,
				'default'   => 50,
				'min'       => 1,
				'max'       => 100,
				'condition' => [
					'trigger_scroll' => 'yes',
				],
			]
		);

		$this->add_control(
			'open_trigger_class',
			[
				'label'       => esc_html__( 'Open By Selector', 'architect-complete-theme-builder-for-elementor' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'placeholder' => esc_html__( '#id, .class', 'architect-complete-theme-builder-for-elementor' ),
				'description' => esc_html__( 'Enter a CSS selector. Clicking the matching element will open this popup.', 'architect-complete-theme-builder-for-elementor' ),
			]
		);

		$this->add_control(
			'show_times',
			[
				'label'       => esc_html__( 'Show X Times Per User', 'architect-complete-theme-builder-for-elementor' ),
				'type'        => \Elementor\Controls_Manager::NUMBER,
				'default'     => 0,
				'min'         => 0,
				'description' => esc_html__( 'How many times to show this popup to each visitor. Set to 0 for unlimited. Uses localStorage to track views.', 'architect-complete-theme-builder-for-elementor' ),
			]
		);

		$this->end_controls_section();

		// Registered last so the tab appears after Advanced.
		$this->register_display_conditions_controls( 'all' );
	}
}
