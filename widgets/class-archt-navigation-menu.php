<?php
/**
 * Navigation Menu widget.
 *
 * Adapted from Elementor Header & Footer Builder by Brainstorm Force, licensed GPLv2 or later,
 * itself based on the Elementor Pro Nav Menu widget.
 *
 * @package ARCHT_Widgets
 * @since 1.0.0
 */

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Core\Kits\Documents\Tabs\Global_Typography;
use Elementor\Core\Kits\Documents\Tabs\Global_Colors;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Widget_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class Nav Menu.
 */
class ARCHT_Navigation_Menu_Widget extends Widget_Base {
	/**
	 * Menu index.
	 *
	 * @var $nav_menu_index
	 */
	protected $nav_menu_index = 1;

	/**
	 * Get the widget name.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string Widget name.
	 */
	public function get_name() {
		return 'navigation-menu';
	}

	/**
	 * Get the widget title.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string Widget title.
	 */
	public function get_title() {
		return esc_html__( 'Navigation Menu', 'architect-complete-theme-builder-for-elementor' );
	}

	/**
	 * Get the widget icon.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string Widget icon.
	 */
	public function get_icon() {
		return 'eicon-nav-menu';
	}

	/**
	 * Used to determine where to display the widget in the editor.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return array Widget categories.
	 */
	public function get_categories() {
		return [ 'archt' ];
	}

	/**
	 * Get style dependencies.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return array Widget style dependencies.
	 */
	public function get_style_depends() {
		return [ 'archt-widget-style' ];
	}

	/**
	 * Used to set scripts dependencies required to run the widget.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return array Widget scripts dependencies.
	 */
	public function get_script_depends() {
		return [ 'archt-nav-menu-script' ];
	}

	/**
	 * Used to get index of nav menu.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @return string nav index.
	 */
	protected function get_nav_menu_index() {
		return $this->nav_menu_index++;
	}

	/**
	 * Used to get the list of available menus.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @return array get WordPress menus list.
	 */
	private function get_available_menus() {

		$menus = wp_get_nav_menus();

		$options = [];

		foreach ( $menus as $menu ) {
			$options[ $menu->slug ] = $menu->name;
		}

		return $options;
	}

	/**
	 * Register Nav Menu controls.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function register_controls() {

		$this->register_general_content_controls();
		$this->register_style_content_controls();
		$this->register_dropdown_content_controls();
	}

	/**
	 * Register Nav Menu General Controls.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function register_general_content_controls() {

		$this->start_controls_section(
			'section_menu',
			[
				'label' => esc_html__( 'Menu', 'architect-complete-theme-builder-for-elementor' ),
			]
		);

		$menus = $this->get_available_menus();

		if ( ! empty( $menus ) ) {
			$this->add_control(
				'menu',
				[
					'label'        => esc_html__( 'Menu', 'architect-complete-theme-builder-for-elementor' ),
					'type'         => Controls_Manager::SELECT,
					'options'      => $menus,
					'default'      => array_keys( $menus )[0],
					'save_default' => true,
					'description'  => wp_kses_post(
						sprintf(
							/* translators: %s: URL to the Menus screen. */
							__( 'Go to the <a href="%s" target="_blank">Menus screen</a> to manage your menus.', 'architect-complete-theme-builder-for-elementor' ),
							esc_url( admin_url( 'nav-menus.php' ) )
						)
					),
				]
			);
		} else {
			$this->add_control(
				'menu',
				[
					'type'            => Controls_Manager::RAW_HTML,
					'raw'             => wp_kses_post(
						sprintf(
							/* translators: %s: URL to create a new menu. */
							__( '<strong>There are no menus in your site.</strong><br>Go to the <a href="%s" target="_blank">Menus screen</a> to create one.', 'architect-complete-theme-builder-for-elementor' ),
							esc_url( admin_url( 'nav-menus.php?action=edit&menu=0' ) )
						)
					),
					'content_classes' => 'elementor-panel-alert elementor-panel-alert-info',
				]
			);
		}

		$this->add_control(
			'schema_support',
			[
				'label'        => esc_html__( 'Enable Schema Support', 'architect-complete-theme-builder-for-elementor' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'architect-complete-theme-builder-for-elementor' ),
				'label_off'    => esc_html__( 'No', 'architect-complete-theme-builder-for-elementor' ),
				'return_value' => 'yes',
				'default'      => '',
				'render_type'  => 'template',
				'separator'    => 'before',
			]
		);

		$this->end_controls_section();

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
					'default' => 'horizontal',
					'options' => [
						'horizontal' => esc_html__( 'Horizontal', 'architect-complete-theme-builder-for-elementor' ),
						'vertical'   => esc_html__( 'Vertical', 'architect-complete-theme-builder-for-elementor' ),
						'expanded'   => esc_html__( 'Expanded', 'architect-complete-theme-builder-for-elementor' ),
					],
				]
			);

			$this->add_control(
				'expanded_submenus',
				[
					'label'       => esc_html__( 'Submenus', 'architect-complete-theme-builder-for-elementor' ),
					'type'        => Controls_Manager::SELECT,
					'default'     => 'collapsed',
					'options'     => [
						'collapsed' => esc_html__( 'Click to Expand', 'architect-complete-theme-builder-for-elementor' ),
						'open'      => esc_html__( 'Always Open', 'architect-complete-theme-builder-for-elementor' ),
					],
					'condition'   => [
						'layout' => 'expanded',
					],
				]
			);

			$this->add_control(
				'expanded_accordion',
				[
					'label'        => esc_html__( 'Close Other Submenus', 'architect-complete-theme-builder-for-elementor' ),
					'type'         => Controls_Manager::SWITCHER,
					'label_on'     => esc_html__( 'Yes', 'architect-complete-theme-builder-for-elementor' ),
					'label_off'    => esc_html__( 'No', 'architect-complete-theme-builder-for-elementor' ),
					'return_value' => 'yes',
					'default'      => '',
					'condition'    => [
						'layout'            => 'expanded',
						'expanded_submenus' => 'collapsed',
					],
				]
			);

			$this->add_control(
				'vertical_children_expanded',
				[
					'label'        => esc_html__( 'Start With Submenu Expanded', 'architect-complete-theme-builder-for-elementor' ),
					'type'         => Controls_Manager::SWITCHER,
					'label_on'     => esc_html__( 'Yes', 'architect-complete-theme-builder-for-elementor' ),
					'label_off'    => esc_html__( 'No', 'architect-complete-theme-builder-for-elementor' ),
					'return_value' => 'yes',
					'default'      => '',
					'condition'    => [
						'layout' => 'vertical',
					],
				]
			);

			$this->add_control(
				'navmenu_align',
				[
					'label'        => esc_html__( 'Alignment', 'architect-complete-theme-builder-for-elementor' ),
					'type'         => Controls_Manager::CHOOSE,
					'options'      => [
						'left'    => [
							'title' => esc_html__( 'Left', 'architect-complete-theme-builder-for-elementor' ),
							'icon'  => 'eicon-h-align-left',
						],
						'center'  => [
							'title' => esc_html__( 'Center', 'architect-complete-theme-builder-for-elementor' ),
							'icon'  => 'eicon-h-align-center',
						],
						'right'   => [
							'title' => esc_html__( 'Right', 'architect-complete-theme-builder-for-elementor' ),
							'icon'  => 'eicon-h-align-right',
						],
						'justify' => [
							'title' => esc_html__( 'Justify', 'architect-complete-theme-builder-for-elementor' ),
							'icon'  => 'eicon-h-align-stretch',
						],
					],
					'default'      => 'left',
					'condition'    => [
						'layout' => [ 'horizontal', 'vertical', 'expanded' ],
					],
					'prefix_class' => 'archt-nav-menu__align-',
					'required'     => true,
				]
			);

		$this->end_controls_section();
	}

	/**
	 * Register the menu style controls.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function register_style_content_controls() {

			$this->start_controls_section(
				'section_style_main-menu',
				[
					'label' => esc_html__( 'Main Menu', 'architect-complete-theme-builder-for-elementor' ),
					'tab'   => Controls_Manager::TAB_STYLE,
				]
			);

			$this->add_group_control(
				Group_Control_Typography::get_type(),
				[
					'name'     => 'menu_typography',
					'global'   => [
						'default' => Global_Typography::TYPOGRAPHY_PRIMARY,
					],
					'selector' => '{{WRAPPER}} .archt-menu__link',
				]
			);

			$this->add_responsive_control(
				'padding_horizontal_menu_item',
				[
					'label'      => esc_html__( 'Horizontal Padding', 'architect-complete-theme-builder-for-elementor' ),
					'type'       => Controls_Manager::SLIDER,
					'size_units' => [ 'px' ],
					'range'      => [
						'px' => [
							'min' => 0,
							'max' => 50,
						],
					],
					'default'    => [
						'size' => 15,
						'unit' => 'px',
					],
					'selectors'  => [
						'{{WRAPPER}}' => '--archt-item-px: {{SIZE}}{{UNIT}};',
					],
				]
			);

			$this->add_responsive_control(
				'padding_vertical_menu_item',
				[
					'label'      => esc_html__( 'Vertical Padding', 'architect-complete-theme-builder-for-elementor' ),
					'type'       => Controls_Manager::SLIDER,
					'size_units' => [ 'px' ],
					'range'      => [
						'px' => [
							'max' => 50,
						],
					],
					'default'    => [
						'size' => 15,
						'unit' => 'px',
					],
					'selectors'  => [
						'{{WRAPPER}}' => '--archt-item-py: {{SIZE}}{{UNIT}};',
					],

				]
			);

			$this->add_responsive_control(
				'menu_space_between',
				[
					'label'      => esc_html__( 'Space Between', 'architect-complete-theme-builder-for-elementor' ),
					'type'       => Controls_Manager::SLIDER,
					'size_units' => [ 'px' ],
					'range'      => [
						'px' => [
							'max' => 100,
						],
					],
					'selectors'  => [
						'{{WRAPPER}}' => '--archt-gap: {{SIZE}}{{UNIT}};',
					],

				]
			);

			$this->add_responsive_control(
				'menu_row_space',
				[
					'label'      => esc_html__( 'Row Spacing', 'architect-complete-theme-builder-for-elementor' ),
					'type'       => Controls_Manager::SLIDER,
					'size_units' => [ 'px' ],
					'range'      => [
						'px' => [
							'max' => 100,
						],
					],
					'selectors'  => [
						'{{WRAPPER}}' => '--archt-row-gap: {{SIZE}}{{UNIT}};',
					],
					'condition'  => [
						'layout' => 'horizontal',
					],

				]
			);

			$this->add_group_control(
				\Elementor\Group_Control_Border::get_type(),
				[
					'name'      => 'menu_border_width',
					'label'     => esc_html__( 'Border', 'architect-complete-theme-builder-for-elementor' ),
					'separator' => 'before',
					'selector' => '{{WRAPPER}} .archt-menu__list > li',
				]
			);

			$this->add_control(
				'pagination_border_radius',
				[
					'label'      => esc_html__( 'Border Radius', 'architect-complete-theme-builder-for-elementor' ),
					'type'       => Controls_Manager::SLIDER,
					'size_units' => [ 'px', '%' ],
					'selectors'  => [
						'{{WRAPPER}} .archt-menu__list > li' => 'border-radius: {{SIZE}}{{UNIT}};',
					],
				]
			);

			$this->add_control(
				'pointer',
				[
					'label'     => esc_html__( 'Link Hover Effect', 'architect-complete-theme-builder-for-elementor' ),
					'type'      => Controls_Manager::SELECT,
					'default'   => 'none',
					'separator' => 'before',
					'options'   => [
						'none'        => esc_html__( 'None', 'architect-complete-theme-builder-for-elementor' ),
						'underline'   => esc_html__( 'Underline', 'architect-complete-theme-builder-for-elementor' ),
						'overline'    => esc_html__( 'Overline', 'architect-complete-theme-builder-for-elementor' ),
						'double-line' => esc_html__( 'Double Line', 'architect-complete-theme-builder-for-elementor' ),
						'framed'      => esc_html__( 'Framed', 'architect-complete-theme-builder-for-elementor' ),
					],
					'condition' => [
						'layout' => [ 'horizontal' ],
					],
				]
			);

		$this->add_control(
			'animation_line',
			[
				'label'     => esc_html__( 'Animation', 'architect-complete-theme-builder-for-elementor' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'fade',
				'options'   => [
					'fade'     => esc_html__( 'Fade', 'architect-complete-theme-builder-for-elementor' ),
					'slide'    => esc_html__( 'Slide', 'architect-complete-theme-builder-for-elementor' ),
					'grow'     => esc_html__( 'Grow', 'architect-complete-theme-builder-for-elementor' ),
					'drop-in'  => esc_html__( 'Drop In', 'architect-complete-theme-builder-for-elementor' ),
					'drop-out' => esc_html__( 'Drop Out', 'architect-complete-theme-builder-for-elementor' ),
					'none'     => esc_html__( 'None', 'architect-complete-theme-builder-for-elementor' ),
				],
				'condition' => [
					'layout'  => [ 'horizontal' ],
					'pointer' => [ 'underline', 'overline', 'double-line' ],
				],
			]
		);

		$this->add_control(
			'animation_framed',
			[
				'label'     => esc_html__( 'Frame Animation', 'architect-complete-theme-builder-for-elementor' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'fade',
				'options'   => [
					'fade'    => esc_html__( 'Fade', 'architect-complete-theme-builder-for-elementor' ),
					'grow'    => esc_html__( 'Grow', 'architect-complete-theme-builder-for-elementor' ),
					'shrink'  => esc_html__( 'Shrink', 'architect-complete-theme-builder-for-elementor' ),
					'draw'    => esc_html__( 'Draw', 'architect-complete-theme-builder-for-elementor' ),
					'corners' => esc_html__( 'Corners', 'architect-complete-theme-builder-for-elementor' ),
					'none'    => esc_html__( 'None', 'architect-complete-theme-builder-for-elementor' ),
				],
				'condition' => [
					'layout'  => [ 'horizontal' ],
					'pointer' => 'framed',
				],
			]
		);

		$this->start_controls_tabs( 'tabs_menu_item_style' );

				$this->start_controls_tab(
					'tab_menu_item_normal',
					[
						'label' => esc_html__( 'Normal', 'architect-complete-theme-builder-for-elementor' ),
					]
				);

				$this->add_control(
					'color_menu_item',
					[
						'label'     => esc_html__( 'Text Color', 'architect-complete-theme-builder-for-elementor' ),
						'type'      => Controls_Manager::COLOR,
						'global'    => [
							'default' => Global_Colors::COLOR_TEXT,
						],
						'default'   => '',
						'selectors' => [
							'{{WRAPPER}} .archt-menu__list > li > :is(.archt-menu__link, .archt-menu__toggle)' => 'color: {{VALUE}};',
						],
					]
				);

				$this->add_control(
					'bg_color_menu_item',
					[
						'label'     => esc_html__( 'Background Color', 'architect-complete-theme-builder-for-elementor' ),
						'type'      => Controls_Manager::COLOR,
						'default'   => '',
						'selectors' => [
							'{{WRAPPER}} .archt-menu:not([data-layout="expanded"]) .archt-menu__list > li, {{WRAPPER}} .archt-menu[data-layout="expanded"] .archt-menu__list > li > :is(.archt-menu__link, .archt-menu__toggle)' => 'background-color: {{VALUE}};',
						],
					]
				);

			$this->end_controls_tab();

			$this->start_controls_tab(
				'tab_menu_item_hover',
				[
					'label' => esc_html__( 'Hover', 'architect-complete-theme-builder-for-elementor' ),
				]
			);

				$this->add_control(
					'color_menu_item_hover',
					[
						'label'     => esc_html__( 'Text Color', 'architect-complete-theme-builder-for-elementor' ),
						'type'      => Controls_Manager::COLOR,
						'global'    => [
							'default' => Global_Colors::COLOR_ACCENT,
						],
						'selectors' => [
							'{{WRAPPER}} .archt-menu:not([data-layout="expanded"]) .archt-menu__list > li:is(:hover, :focus-within) > :is(.archt-menu__link, .archt-menu__toggle), {{WRAPPER}} .archt-menu[data-layout="expanded"] .archt-menu__list > li:has(> :is(.archt-menu__link, .archt-menu__toggle):is(:hover, :focus-visible)) > :is(.archt-menu__link, .archt-menu__toggle)' => 'color: {{VALUE}};',
						],
					]
				);

				$this->add_control(
					'bg_color_menu_item_hover',
					[
						'label'     => esc_html__( 'Background Color', 'architect-complete-theme-builder-for-elementor' ),
						'type'      => Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .archt-menu:not([data-layout="expanded"]) .archt-menu__list > li:is(:hover, :focus-within), {{WRAPPER}} .archt-menu[data-layout="expanded"] .archt-menu__list > li:has(> :is(.archt-menu__link, .archt-menu__toggle):is(:hover, :focus-visible)) > :is(.archt-menu__link, .archt-menu__toggle)' => 'background-color: {{VALUE}};',
						],
					]
				);

				$this->add_control(
					'animation_line_color_hover',
					[
						'label'     => esc_html__( 'Hover Effect Color', 'architect-complete-theme-builder-for-elementor' ),
						'type'      => Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .archt-menu__list > li:is(:hover, :focus-within)' => '--archt-pointer-color: {{VALUE}};',
						],
						'condition' => [
							'layout'  => [ 'horizontal' ],
							'pointer' => [ 'underline', 'overline', 'double-line' ],
						],
					]
				);

				$this->add_control(
					'animation_line_color_framed_hover',
					[
						'label'     => esc_html__( 'Hover Effect Color', 'architect-complete-theme-builder-for-elementor' ),
						'type'      => Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .archt-menu__list > li:is(:hover, :focus-within)' => '--archt-pointer-color: {{VALUE}};',
						],
						'condition' => [
							'layout'  => [ 'horizontal' ],
							'pointer' => [ 'framed' ],
						],
					]
				);

			$this->end_controls_tab();

				$this->start_controls_tab(
					'tab_menu_item_active',
					[
						'label' => esc_html__( 'Active', 'architect-complete-theme-builder-for-elementor' ),
					]
				);

				$this->add_control(
					'color_menu_item_active',
					[
						'label'     => esc_html__( 'Text Color', 'architect-complete-theme-builder-for-elementor' ),
						'type'      => Controls_Manager::COLOR,
						'default'   => '',
						'selectors' => [
							'{{WRAPPER}} .archt-menu__list > li:where(.current-menu-item, .current-menu-ancestor) > :is(.archt-menu__link, .archt-menu__toggle)' => 'color: {{VALUE}};',
						],
					]
				);

				$this->add_control(
					'bg_color_menu_item_active',
					[
						'label'     => esc_html__( 'Background Color', 'architect-complete-theme-builder-for-elementor' ),
						'type'      => Controls_Manager::COLOR,
						'default'   => '',
						'selectors' => [
							'{{WRAPPER}} .archt-menu:not([data-layout="expanded"]) .archt-menu__list > li:where(.current-menu-item, .current-menu-ancestor), {{WRAPPER}} .archt-menu[data-layout="expanded"] .archt-menu__list > li:where(.current-menu-item, .current-menu-ancestor) > :is(.archt-menu__link, .archt-menu__toggle)' => 'background-color: {{VALUE}};',
						],
					]
				);

				$this->add_control(
					'animation_line_color_active',
					[
						'label'     => esc_html__( 'Hover Effect Color', 'architect-complete-theme-builder-for-elementor' ),
						'type'      => Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .archt-menu__list > li:where(.current-menu-item, .current-menu-ancestor)' => '--archt-pointer-color: {{VALUE}};',
						],
						'condition' => [
							'layout'  => [ 'horizontal' ],
							'pointer' => [ 'underline', 'overline', 'double-line' ],
						],
					]
				);

				$this->add_control(
					'animation_line_color_framed_active',
					[
						'label'     => esc_html__( 'Hover Effect Color', 'architect-complete-theme-builder-for-elementor' ),
						'type'      => Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .archt-menu__list > li:where(.current-menu-item, .current-menu-ancestor)' => '--archt-pointer-color: {{VALUE}};',
						],
						'condition' => [
							'layout'  => [ 'horizontal' ],
							'pointer' => [ 'framed' ],
						],
					]
				);

			$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();
	}

	/**
	 * Register the dropdown style controls.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function register_dropdown_content_controls() {

			$this->start_controls_section(
				'section_style_dropdown',
				[
					'label' => esc_html__( 'Submenu', 'architect-complete-theme-builder-for-elementor' ),
					'tab'   => Controls_Manager::TAB_STYLE,
				]
			);

			$this->add_group_control(
				Group_Control_Typography::get_type(),
				[
					'name'      => 'dropdown_typography',
					'global'    => [
						'default' => Global_Typography::TYPOGRAPHY_ACCENT,
					],
					'separator' => 'before',
					'selector' => '{{WRAPPER}} .archt-menu__sub .archt-menu__link',
				]
			);

			$this->add_responsive_control(
				'padding_horizontal_dropdown_item',
				[
					'label'      => esc_html__( 'Horizontal Padding', 'architect-complete-theme-builder-for-elementor' ),
					'type'       => Controls_Manager::SLIDER,
					'size_units' => [ 'px' ],
					'default'    => [
						'size' => 15,
						'unit' => 'px',
					],
					'range'      => [
						'px' => [
							'max' => 50,
						],
					],
					'selectors'  => [
						'{{WRAPPER}}' => '--archt-sub-px: {{SIZE}}{{UNIT}};',
					],

				]
			);

			$this->add_responsive_control(
				'padding_vertical_dropdown_item',
				[
					'label'      => esc_html__( 'Vertical Padding', 'architect-complete-theme-builder-for-elementor' ),
					'type'       => Controls_Manager::SLIDER,
					'size_units' => [ 'px' ],
					'default'    => [
						'size' => 15,
						'unit' => 'px',
					],
					'range'      => [
						'px' => [
							'max' => 50,
						],
					],
					'selectors'  => [
						'{{WRAPPER}}' => '--archt-sub-py: {{SIZE}}{{UNIT}};',
					],

				]
			);

			$this->start_controls_tabs( 'tabs_sub_menu_item_style' );

			$this->start_controls_tab(
				'tab_sub_menu_item_normal',
				[
					'label' => esc_html__( 'Normal', 'architect-complete-theme-builder-for-elementor' ),
				]
			);

			$this->add_control(
				'color_sub_menu_item',
				[
					'label'     => esc_html__( 'Text Color', 'architect-complete-theme-builder-for-elementor' ),
					'type'      => Controls_Manager::COLOR,
					'global'    => [
						'default' => Global_Colors::COLOR_TEXT,
					],
					'default'   => '',
					'selectors' => [
						'{{WRAPPER}} .archt-menu__sub :is(.archt-menu__link, .archt-menu__toggle)' => 'color: {{VALUE}};',
					],
				]
			);

			$this->add_control(
				'bg_color_sub_menu_item',
				[
					'label'     => esc_html__( 'Background Color', 'architect-complete-theme-builder-for-elementor' ),
					'type'      => Controls_Manager::COLOR,
					'default'   => '',
					'selectors' => [
						'{{WRAPPER}} .archt-menu:not([data-layout="expanded"]) .archt-menu__sub li, {{WRAPPER}} .archt-menu[data-layout="expanded"] .archt-menu__sub li > :is(.archt-menu__link, .archt-menu__toggle)' => 'background-color: {{VALUE}};',
					],
				]
			);

			$this->end_controls_tab();

			$this->start_controls_tab(
				'tab_sub_menu_item_hover',
				[
					'label' => esc_html__( 'Hover', 'architect-complete-theme-builder-for-elementor' ),
				]
			);

			$this->add_control(
				'color_sub_menu_item_hover',
				[
					'label'     => esc_html__( 'Text Color', 'architect-complete-theme-builder-for-elementor' ),
					'type'      => Controls_Manager::COLOR,
					'global'    => [
						'default' => Global_Colors::COLOR_ACCENT,
					],
					'selectors' => [
						'{{WRAPPER}} .archt-menu:not([data-layout="expanded"]) .archt-menu__sub li:is(:hover, :focus-within) > :is(.archt-menu__link, .archt-menu__toggle), {{WRAPPER}} .archt-menu[data-layout="expanded"] .archt-menu__sub li:has(> :is(.archt-menu__link, .archt-menu__toggle):is(:hover, :focus-visible)) > :is(.archt-menu__link, .archt-menu__toggle)' => 'color: {{VALUE}};',
					],
				]
			);

			$this->add_control(
				'bg_color_sub_menu_item_hover',
				[
					'label'     => esc_html__( 'Background Color', 'architect-complete-theme-builder-for-elementor' ),
					'type'      => Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .archt-menu:not([data-layout="expanded"]) .archt-menu__sub li:is(:hover, :focus-within), {{WRAPPER}} .archt-menu[data-layout="expanded"] .archt-menu__sub li:has(> :is(.archt-menu__link, .archt-menu__toggle):is(:hover, :focus-visible)) > :is(.archt-menu__link, .archt-menu__toggle)' => 'background-color: {{VALUE}};',
					],
				]
			);

			$this->end_controls_tab();

			$this->start_controls_tab(
				'tab_sub_menu_item_active',
				[
					'label' => esc_html__( 'Active', 'architect-complete-theme-builder-for-elementor' ),
				]
			);

			$this->add_control(
				'color_sub_menu_item_active',
				[
					'label'     => esc_html__( 'Text Color', 'architect-complete-theme-builder-for-elementor' ),
					'type'      => Controls_Manager::COLOR,
					'default'   => '',
					'selectors' => [
						'{{WRAPPER}} .archt-menu__sub li:where(.current-menu-item, .current-menu-ancestor) > :is(.archt-menu__link, .archt-menu__toggle)' => 'color: {{VALUE}};',
					],
				]
			);

			$this->add_control(
				'bg_color_sub_menu_item_active',
				[
					'label'     => esc_html__( 'Background Color', 'architect-complete-theme-builder-for-elementor' ),
					'type'      => Controls_Manager::COLOR,
					'default'   => '',
					'selectors' => [
						'{{WRAPPER}} .archt-menu:not([data-layout="expanded"]) .archt-menu__sub li:where(.current-menu-item, .current-menu-ancestor), {{WRAPPER}} .archt-menu[data-layout="expanded"] .archt-menu__sub li:where(.current-menu-item, .current-menu-ancestor) > :is(.archt-menu__link, .archt-menu__toggle)' => 'background-color: {{VALUE}};',
					],
				]
			);

			$this->end_controls_tab();

			$this->end_controls_tabs();

			$this->add_control(
				'heading_submenu_icon',
				[
					'label'     => esc_html__( 'Submenu Icon', 'architect-complete-theme-builder-for-elementor' ),
					'type'      => Controls_Manager::HEADING,
					'separator' => 'before',
				]
			);

			$this->add_control(
				'submenu_icon',
				[
					'label'        => esc_html__( 'Submenu Icon', 'architect-complete-theme-builder-for-elementor' ),
					'type'         => Controls_Manager::SELECT,
					'default'      => 'arrow',
					'options'      => [
						'arrow'   => esc_html__( 'Arrows', 'architect-complete-theme-builder-for-elementor' ),
						'plus'    => esc_html__( 'Plus Sign', 'architect-complete-theme-builder-for-elementor' ),
						'classic' => esc_html__( 'Caret', 'architect-complete-theme-builder-for-elementor' ),
						'none'    => esc_html__( 'None', 'architect-complete-theme-builder-for-elementor' ),
					],
					'prefix_class' => 'archt-submenu-icon-',
					'render_type'  => 'template',
				]
			);

			$this->add_control(
				'submenu_icon_spacing_left',
				[
					'label'      => esc_html__( 'Icon Spacing', 'architect-complete-theme-builder-for-elementor' ),
					'type'       => Controls_Manager::SLIDER,
					'size_units' => [ 'px' ],
					'range'      => [
						'px' => [
							'min' => 0,
							'max' => 20,
						],
					],
					'default'    => [
						'unit' => 'px',
						'size' => 5,
					],
					'selectors'  => [
						'{{WRAPPER}}' => '--archt-icon-gap: {{SIZE}}{{UNIT}};',
					],
					'condition'  => [
						'icon_position' => 'left',
					],
				]
			);

			$this->add_control(
				'submenu_icon_spacing_right',
				[
					'label'      => esc_html__( 'Icon Spacing', 'architect-complete-theme-builder-for-elementor' ),
					'type'       => Controls_Manager::SLIDER,
					'size_units' => [ 'px' ],
					'range'      => [
						'px' => [
							'min' => 0,
							'max' => 20,
						],
					],
					'default'    => [
						'unit' => 'px',
						'size' => 5,
					],
					'selectors'  => [
						'{{WRAPPER}}' => '--archt-icon-gap: {{SIZE}}{{UNIT}};',
					],
					'condition'  => [
						'icon_position' => 'right',
					],
				]
			);

			$this->add_control(
				'submenu_animation',
				[
					'label'        => esc_html__( 'Submenu Animation', 'architect-complete-theme-builder-for-elementor' ),
					'type'         => Controls_Manager::SELECT,
					'default'      => 'none',
					'options'      => [
						'none'       => esc_html__( 'Default', 'architect-complete-theme-builder-for-elementor' ),
						'slide_up'   => esc_html__( 'Slide Up', 'architect-complete-theme-builder-for-elementor' ),
						'slide_down' => esc_html__( 'Slide Down', 'architect-complete-theme-builder-for-elementor' ),
					],
					'prefix_class' => 'archt-submenu-animation-',
					'condition'    => [
						'layout' => 'horizontal',
					],
				]
			);

			$this->add_control(
				'icon_position',
				[
					'label'        => esc_html__( 'Icon Position', 'architect-complete-theme-builder-for-elementor' ),
					'type'         => Controls_Manager::CHOOSE,
					'options'      => [
						'left'  => [
							'title' => esc_html__( 'Left', 'architect-complete-theme-builder-for-elementor' ),
							'icon'  => 'eicon-h-align-left',
						],
						'right' => [
							'title' => esc_html__( 'Right', 'architect-complete-theme-builder-for-elementor' ),
							'icon'  => 'eicon-h-align-right',
						],
					],
					'default'      => 'right',
					'condition'    => [
						'layout' => [ 'horizontal', 'vertical', 'expanded' ],
					],
					'prefix_class' => 'archt-submenu-icon-',
					'required'     => true,
					'render_type'  => 'template',
				]
			);

			$this->add_control(
				'heading_dropdown_divider',
				[
					'label'     => esc_html__( 'Divider', 'architect-complete-theme-builder-for-elementor' ),
					'type'      => Controls_Manager::HEADING,
					'separator' => 'before',
				]
			);

			$this->add_control(
				'dropdown_divider_border',
				[
					'label'       => esc_html__( 'Border Style', 'architect-complete-theme-builder-for-elementor' ),
					'type'        => Controls_Manager::SELECT,
					'default'     => 'none',
					'label_block' => false,
					'options'     => [
						'none'   => esc_html__( 'None', 'architect-complete-theme-builder-for-elementor' ),
						'solid'  => esc_html__( 'Solid', 'architect-complete-theme-builder-for-elementor' ),
						'double' => esc_html__( 'Double', 'architect-complete-theme-builder-for-elementor' ),
						'dotted' => esc_html__( 'Dotted', 'architect-complete-theme-builder-for-elementor' ),
						'dashed' => esc_html__( 'Dashed', 'architect-complete-theme-builder-for-elementor' ),
					],
					'selectors'   => [
						'{{WRAPPER}} .archt-menu__sub > li:not(:last-child)' => 'border-bottom-style: {{VALUE}};',
					],
				]
			);

			$this->add_control(
				'divider_border_color',
				[
					'label'     => esc_html__( 'Border Color', 'architect-complete-theme-builder-for-elementor' ),
					'type'      => Controls_Manager::COLOR,
					'default'   => '#c4c4c4',
					'selectors' => [
						'{{WRAPPER}} .archt-menu__sub > li:not(:last-child)' => 'border-bottom-color: {{VALUE}};',
					],
					'condition' => [
						'dropdown_divider_border!' => 'none',
					],
				]
			);

			$this->add_control(
				'dropdown_divider_width',
				[
					'label'     => esc_html__( 'Border Width', 'architect-complete-theme-builder-for-elementor' ),
					'type'      => Controls_Manager::SLIDER,
					'range'     => [
						'px' => [
							'max' => 50,
						],
					],
					'default'   => [
						'size' => '1',
						'unit' => 'px',
					],
					'selectors' => [
						'{{WRAPPER}} .archt-menu__sub > li:not(:last-child)' => 'border-bottom-width: {{SIZE}}{{UNIT}};',
					],
					'condition' => [
						'dropdown_divider_border!' => 'none',
					],
				]
			);

			$this->add_control(
				'heading_dropdown_box',
				[
					'label'     => esc_html__( 'Dropdown', 'architect-complete-theme-builder-for-elementor' ),
					'type'      => Controls_Manager::HEADING,
					'separator' => 'before',
				]
			);

			$this->add_group_control(
				Group_Control_Border::get_type(),
				[
					'name'     => 'dropdown_border',
					'selector' => '{{WRAPPER}} .archt-menu__sub',
				]
			);

			$this->add_responsive_control(
				'dropdown_border_radius',
				[
					'label'      => esc_html__( 'Border Radius', 'architect-complete-theme-builder-for-elementor' ),
					'type'       => Controls_Manager::DIMENSIONS,
					'size_units' => [ 'px', '%' ],
					'selectors'  => [
						'{{WRAPPER}} .archt-menu__sub' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
						'{{WRAPPER}} .archt-menu__sub > li:first-child' => 'border-start-start-radius: {{TOP}}{{UNIT}}; border-start-end-radius: {{RIGHT}}{{UNIT}};',
						'{{WRAPPER}} .archt-menu__sub > li:last-child' => 'border-end-end-radius: {{BOTTOM}}{{UNIT}}; border-end-start-radius: {{LEFT}}{{UNIT}};',
					],

				]
			);

			$this->add_responsive_control(
				'width_dropdown_item',
				[
					'label'     => esc_html__( 'Dropdown Width (px)', 'architect-complete-theme-builder-for-elementor' ),
					'type'      => Controls_Manager::SLIDER,
					'range'     => [
						'px' => [
							'min' => 0,
							'max' => 500,
						],
					],
					'default'   => [
						'size' => '220',
						'unit' => 'px',
					],
					'selectors' => [
						'{{WRAPPER}}' => '--archt-sub-width: {{SIZE}}{{UNIT}};',
					],
					'condition' => [
						'layout' => 'horizontal',
					],

				]
			);

			$this->add_responsive_control(
				'distance_from_menu',
				[
					'label'     => esc_html__( 'Top Distance', 'architect-complete-theme-builder-for-elementor' ),
					'type'      => Controls_Manager::SLIDER,
					'range'     => [
						'px' => [
							'min' => -100,
							'max' => 100,
						],
					],
					'selectors' => [
						'{{WRAPPER}}' => '--archt-sub-offset: {{SIZE}}px;',
					],
					'condition' => [
						'layout' => [ 'horizontal', 'vertical' ],
					],

				]
			);

			$this->add_control(
				'dropdown_color',
				[
					'label'     => esc_html__( 'Background', 'architect-complete-theme-builder-for-elementor' ),
					'type'      => Controls_Manager::COLOR,
					'default'   => '#ffffff',
					'selectors' => [
						'{{WRAPPER}} .archt-menu__sub' => 'background-color: {{VALUE}};',
					],
				]
			);

			$this->add_group_control(
				Group_Control_Box_Shadow::get_type(),
				[
					'name'      => 'dropdown_box_shadow',
					'exclude'   => [
						'box_shadow_position',
					],
					'selector' => '{{WRAPPER}} .archt-menu__sub',
					'separator' => 'after',
				]
			);

		$this->end_controls_section();
	}

	/**
	 * Render Nav Menu output on the frontend.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function render() {

		$menus = $this->get_available_menus();

		if ( empty( $menus ) ) {
			return false;
		}

		$settings = $this->get_settings_for_display();

		$layout = $settings['layout'];
		$menu   = wp_get_nav_menu_object( $settings['menu'] );

		$this->add_render_attribute(
			'archt-menu',
			[
				'class'       => 'archt-menu',
				'data-layout' => $layout,
				'aria-label'  => $menu ? $menu->name : __( 'Menu', 'architect-complete-theme-builder-for-elementor' ),
			]
		);

		if ( 'yes' === $settings['schema_support'] ) {
			$this->add_render_attribute(
				'archt-menu',
				[
					'itemscope' => 'itemscope',
					'itemtype'  => 'https://schema.org/SiteNavigationElement',
				]
			);
		}

		if ( 'horizontal' === $layout && ! empty( $settings['pointer'] ) && 'none' !== $settings['pointer'] ) {
			$this->add_render_attribute(
				'archt-menu',
				[
					'data-pointer'   => $settings['pointer'],
					'data-animation' => 'framed' === $settings['pointer'] ? $settings['animation_framed'] : $settings['animation_line'],
				]
			);
		}

		if ( 'expanded' === $layout && 'open' === $settings['expanded_submenus'] ) {
			$this->add_render_attribute( 'archt-menu', 'data-submenus', 'open' );
		} elseif ( 'expanded' === $layout && 'yes' === $settings['expanded_accordion'] ) {
			$this->add_render_attribute( 'archt-menu', 'data-accordion', '1' );
		}

		$args = [
			'echo'        => false,
			'menu'        => $settings['menu'],
			'menu_class'  => 'archt-menu__list',
			'menu_id'     => 'menu-' . $this->get_nav_menu_index() . '-' . $this->get_id(),
			'fallback_cb' => '__return_empty_string',
			'container'   => '',
			'walker'      => new ARCHT_Menu_Walker( $settings ),
		];

		?>
		<nav <?php $this->print_render_attribute_string( 'archt-menu' ); ?>>
			<?php echo wp_nav_menu( $args ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped by the menu walker. ?>
		</nav>
		<?php
	}
}
