<?php
/**
 * Navigation Menu widget.
 *
 * @package ARCHT_Widgets
 * @since 1.0.0
 */

use Elementor\Controls_Manager;
use Elementor\Utils;
use Elementor\Group_Control_Typography;
use Elementor\Core\Kits\Documents\Tabs\Global_Typography;
use Elementor\Core\Kits\Documents\Tabs\Global_Colors;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Background;
use Elementor\Widget_Base;
use Elementor\Plugin;

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
	 * Check if the Elementor is updated.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return boolean if Elementor updated.
	 */
	public static function is_elementor_updated() {
		if ( class_exists( 'Elementor\Icons_Manager' ) ) {
			return true;
		} else {
			return false;
		}
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
					'selector' => '{{WRAPPER}} a.archt-menu-item, {{WRAPPER}} a.archt-sub-menu-item',
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
						'{{WRAPPER}} .parent > a, {{WRAPPER}} .parent > .archt-has-submenu-container'      => 'padding-left: {{SIZE}}{{UNIT}}; padding-right: {{SIZE}}{{UNIT}};',
						'{{WRAPPER}} .archt-nav-menu__layout-vertical .menu-item ul ul > .menu-item'       => 'padding-left: calc( {{SIZE}}{{UNIT}} + 20px ); padding-right: {{SIZE}}{{UNIT}};',
						'{{WRAPPER}} .archt-nav-menu__layout-vertical .menu-item ul ul ul > .menu-item'    => 'padding-left: calc( {{SIZE}}{{UNIT}} + 40px ); padding-right: {{SIZE}}{{UNIT}};',
						'{{WRAPPER}} .archt-nav-menu__layout-vertical .menu-item ul ul ul ul > .menu-item' => 'padding-left: calc( {{SIZE}}{{UNIT}} + 60px ); padding-right: {{SIZE}}{{UNIT}};',
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
						'{{WRAPPER}} .parent > a, {{WRAPPER}} .parent > .archt-has-submenu-container' => 'padding-top: {{SIZE}}{{UNIT}}; padding-bottom: {{SIZE}}{{UNIT}};',
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
						'body:not(.rtl) {{WRAPPER}} .archt-nav-menu__layout-horizontal .archt-nav-menu > li.menu-item:not(:last-child)' => 'margin-right: {{SIZE}}{{UNIT}}',
						'body.rtl {{WRAPPER}} .archt-nav-menu__layout-horizontal .archt-nav-menu > li.menu-item:not(:last-child)' => 'margin-left: {{SIZE}}{{UNIT}}',
						'{{WRAPPER}} nav:not(.archt-nav-menu__layout-horizontal) .archt-nav-menu > li.menu-item:not(:last-child)' => 'margin-bottom: {{SIZE}}{{UNIT}}',
						'(tablet)body:not(.rtl) {{WRAPPER}}.archt-nav-menu__breakpoint-tablet .archt-nav-menu__layout-horizontal .archt-nav-menu > li.menu-item:not(:last-child)' => 'margin-right: 0px',
						'(mobile)body:not(.rtl) {{WRAPPER}}.archt-nav-menu__breakpoint-mobile .archt-nav-menu__layout-horizontal .archt-nav-menu > li.menu-item:not(:last-child)' => 'margin-right: 0px',
						'(tablet)body {{WRAPPER}} nav.archt-nav-menu__layout-vertical .archt-nav-menu > li.menu-item:not(:last-child)' => 'margin-bottom: 0px',
						'(mobile)body {{WRAPPER}} nav.archt-nav-menu__layout-vertical .archt-nav-menu > li.menu-item:not(:last-child)' => 'margin-bottom: 0px',
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
						'body:not(.rtl) {{WRAPPER}} .archt-nav-menu__layout-horizontal .archt-nav-menu > li.menu-item' => 'margin: {{SIZE}}{{UNIT}} 0',
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
					'selector'  =>
						'body:not(.rtl) {{WRAPPER}} .archt-nav-menu > li:not(.cta)',
				]
			);

			$this->add_control(
				'pagination_border_radius',
				[
					'label'      => esc_html__( 'Border Radius', 'architect-complete-theme-builder-for-elementor' ),
					'type'       => Controls_Manager::SLIDER,
					'size_units' => [ 'px', '%' ],
					'selectors'  => [
						'body:not(.rtl) {{WRAPPER}} .archt-nav-menu__layout-horizontal .archt-nav-menu > li:not(.cta)' =>
							'border-radius: {{SIZE}}{{UNIT}}',
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
							'{{WRAPPER}} .parent > a.archt-menu-item, {{WRAPPER}} .parent > .archt-has-submenu-container a, {{WRAPPER}} .parent > .archt-has-submenu-container svg' => 'color: {{VALUE}}',
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
							'{{WRAPPER}} .parent:not(.cta)' => 'background-color: {{VALUE}}',
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
							'{{WRAPPER}} .parent:hover > a,  {{WRAPPER}} .parent > .archt-has-submenu-container:hover a, {{WRAPPER}} .parent > .archt-has-submenu-container:hover svg, {{WRAPPER}} .parent.current_page_item:hover .archt-has-submenu-container > a:not(.archt-sub-menu-item), {{WRAPPER}} .parent.current_page_item:hover > .archt-has-submenu-container > svg, {{WRAPPER}} .parent.current_page_item:hover > a, {{WRAPPER}} .parent.current_page_item:hover > svg, {{WRAPPER}} .parent.current-menu-ancestor:hover .archt-has-submenu-container > a:not(.archt-sub-menu-item), {{WRAPPER}} .parent.current-menu-ancestor:hover > .archt-has-submenu-container > svg' => 'color: {{VALUE}}',
						],
					]
				);

				$this->add_control(
					'bg_color_menu_item_hover',
					[
						'label'     => esc_html__( 'Background Color', 'architect-complete-theme-builder-for-elementor' ),
						'type'      => Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .parent:not(.cta):hover, {{WRAPPER}} .parent:not(.cta).current_page_item:hover, {{WRAPPER}} .parent:not(.cta).current-menu-ancestor:hover > .archt-has-submenu-container' => 'background-color: {{VALUE}}',
						],
					]
				);

				$this->add_control(
					'animation_line_color_hover',
					[
						'label'     => esc_html__( 'Hover Effect Color', 'architect-complete-theme-builder-for-elementor' ),
						'type'      => Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .menu-item.parent a.archt-menu-item:hover:after, {{WRAPPER}} .menu-item.parent a.archt-menu-item:before' => 'background-color: {{VALUE}}',
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
							'{{WRAPPER}} .menu-item.parent a.archt-menu-item:hover:after, {{WRAPPER}} .menu-item.parent a.archt-menu-item:before' => 'border-color: {{VALUE}}',
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
							'{{WRAPPER}} .parent.current-menu-ancestor .archt-has-submenu-container > a:not(.archt-sub-menu-item), {{WRAPPER}} .parent.current-menu-ancestor > .archt-has-submenu-container > svg, {{WRAPPER}} .parent.current_page_item .archt-has-submenu-container > a:not(.archt-sub-menu-item), {{WRAPPER}} .parent.current_page_item > .archt-has-submenu-container > svg, {{WRAPPER}} .parent.current_page_item > a, {{WRAPPER}} .parent.current_page_item > svg' => 'color: {{VALUE}}',
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
							'{{WRAPPER}} .parent.current-menu-ancestor, {{WRAPPER}} .parent.current_page_item' => 'background-color: {{VALUE}}',
						],
					]
				);

				$this->add_control(
					'animation_line_color_active',
					[
						'label'     => esc_html__( 'Hover Effect Color', 'architect-complete-theme-builder-for-elementor' ),
						'type'      => Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .menu-item.parent a.archt-menu-item:after, {{WRAPPER}} .menu-item.parent a.archt-menu-item:before' => 'background-color: {{VALUE}}',
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
							'{{WRAPPER}} .menu-item.parent a.archt-menu-item:after, {{WRAPPER}} .menu-item.parent a.archt-menu-item:before' => 'border-color: {{VALUE}}',
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
					'selector'  => '
							{{WRAPPER}} .sub-menu li a.archt-sub-menu-item,
							{{WRAPPER}} nav.archt-dropdown li a.archt-sub-menu-item,
							{{WRAPPER}} nav.archt-dropdown li a.archt-menu-item',
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
						'{{WRAPPER}} .sub-menu .menu-item' => 'padding-left: {{SIZE}}{{UNIT}}; padding-right: {{SIZE}}{{UNIT}}',
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
						'{{WRAPPER}} .sub-menu .menu-item' => 'padding-top: {{SIZE}}{{UNIT}}; padding-bottom: {{SIZE}}{{UNIT}}',
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
						'{{WRAPPER}} .sub-menu .archt-sub-menu-item, {{WRAPPER}} .sub-menu .archt-has-submenu-container svg' => 'color: {{VALUE}};',
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
						'{{WRAPPER}} .sub-menu li' => 'background-color: {{VALUE}}',
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
						'{{WRAPPER}} .sub-menu li:hover > .archt-sub-menu-item, {{WRAPPER}} .sub-menu li:hover .archt-has-submenu-container svg, {{WRAPPER}} .sub-menu li:hover .archt-has-submenu-container a' => 'color: {{VALUE}};',
					],
				]
			);

			$this->add_control(
				'bg_color_sub_menu_item_hover',
				[
					'label'     => esc_html__( 'Background Color', 'architect-complete-theme-builder-for-elementor' ),
					'type'      => Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .sub-menu li:hover' => 'background-color: {{VALUE}}',
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
						'{{WRAPPER}} .sub-menu .current-menu-item > .archt-sub-menu-item, {{WRAPPER}} .sub-menu .archt-sub-menu-item-active, {{WRAPPER}} .sub-menu .current-menu-item .archt-has-submenu-container svg' => 'color: {{VALUE}};',
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
						'{{WRAPPER}} .sub-menu .current-menu-item, {{WRAPPER}} .sub-menu .current_page_item' => 'background-color: {{VALUE}}',
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
						'{{WRAPPER}} .archt-has-submenu-container svg' => 'margin-right: {{SIZE}}{{UNIT}};',
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
						'{{WRAPPER}} .archt-has-submenu-container svg' => 'margin-left: {{SIZE}}{{UNIT}};',
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
						'{{WRAPPER}} .sub-menu li.menu-item:not(:last-child),
						{{WRAPPER}} nav.archt-dropdown li.menu-item:not(:last-child)' => 'border-bottom-style: {{VALUE}};',
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
						'{{WRAPPER}} .sub-menu li.menu-item:not(:last-child),
						{{WRAPPER}} nav.archt-dropdown li.menu-item:not(:last-child)' => 'border-bottom-color: {{VALUE}};',
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
						'{{WRAPPER}} .sub-menu li.menu-item:not(:last-child),
						{{WRAPPER}} nav.archt-dropdown li.menu-item:not(:last-child)' => 'border-width: {{SIZE}}{{UNIT}};',
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
					'selector' => '{{WRAPPER}} nav.archt-nav-menu__layout-horizontal .sub-menu,
							{{WRAPPER}} nav:not(.archt-nav-menu__layout-horizontal) .sub-menu.sub-menu-open,
							{{WRAPPER}} nav.archt-dropdown .archt-nav-menu',
				]
			);

			$this->add_responsive_control(
				'dropdown_border_radius',
				[
					'label'      => esc_html__( 'Border Radius', 'architect-complete-theme-builder-for-elementor' ),
					'type'       => Controls_Manager::DIMENSIONS,
					'size_units' => [ 'px', '%' ],
					'selectors'  => [
						'{{WRAPPER}} .sub-menu'          => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
						'{{WRAPPER}} .sub-menu li.menu-item:first-child' => 'border-top-left-radius: {{TOP}}{{UNIT}}; border-top-right-radius: {{RIGHT}}{{UNIT}};overflow:hidden;',
						'{{WRAPPER}} .sub-menu li.menu-item:last-child' => 'border-bottom-right-radius: {{BOTTOM}}{{UNIT}}; border-bottom-left-radius: {{LEFT}}{{UNIT}};overflow:hidden',
						'{{WRAPPER}} nav.archt-dropdown' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
						'{{WRAPPER}} nav.archt-dropdown li.menu-item:first-child' => 'border-top-left-radius: {{TOP}}{{UNIT}}; border-top-right-radius: {{RIGHT}}{{UNIT}};overflow:hidden',
						'{{WRAPPER}} nav.archt-dropdown li.menu-item:last-child' => 'border-bottom-right-radius: {{BOTTOM}}{{UNIT}}; border-bottom-left-radius: {{LEFT}}{{UNIT}};overflow:hidden',
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
						'{{WRAPPER}} ul.sub-menu' => 'width: {{SIZE}}{{UNIT}}',
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
						'{{WRAPPER}} nav.archt-nav-menu__layout-horizontal:not(.archt-dropdown) ul.sub-menu, {{WRAPPER}} nav.archt-nav-menu__layout-vertical:not(.archt-dropdown) ul.sub-menu' => 'margin-top: {{SIZE}}px;',
						'{{WRAPPER}} .archt-dropdown.menu-is-active' => 'margin-top: {{SIZE}}px;',
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
						'{{WRAPPER}} .sub-menu' => 'background-color: {{VALUE}}',
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
					'selector'  => '{{WRAPPER}} .archt-nav-menu .sub-menu,
								{{WRAPPER}} nav.archt-dropdown',
					'separator' => 'after',
				]
			);

		$this->end_controls_section();
	}

	/**
	 * Add itemprop for Navigation Schema.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $atts link attributes.
	 */
	public function handle_link_attrs( $atts ) {
		$atts .= ' itemprop="url"';
		return $atts;
	}

	/**
	 * Add itemprop to the li tag of Navigation Schema.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $value link attributes.
	 */
	public function handle_li_values( $value ) {
		$value .= ' itemprop="name"';
		return $value;
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

		$vertical_children_expanded = (
			'vertical' === $settings['layout'] &&
			! empty( $settings['vertical_children_expanded'] ) &&
			'yes' === $settings['vertical_children_expanded']
		);

		$args = [
			'echo'        => false,
			'menu'        => $settings['menu'],
			'menu_class'  => 'archt-nav-menu',
			'menu_id'     => 'menu-' . $this->get_nav_menu_index() . '-' . $this->get_id(),
			'fallback_cb' => '__return_empty_string',
			'container'   => '',
			'walker'      => new ARCHT_Menu_Walker( $settings ),
		];

		if ( 'yes' === $settings['schema_support'] ) {
			$this->add_render_attribute( 'archt-nav-menu', 'itemscope', 'itemscope' );
			$this->add_render_attribute( 'archt-nav-menu', 'itemtype', 'https://schema.org/SiteNavigationElement' );

			add_filter( 'archt_nav_menu_attrs', [ $this, 'handle_link_attrs' ] );
			add_filter( 'nav_menu_li_values', [ $this, 'handle_li_values' ] );
		}

		$this->add_render_attribute(
			'archt-main-menu',
			'class',
			[
				'archt-nav-menu',
				'archt-layout-' . $settings['layout'],
			]
		);

		$this->add_render_attribute( 'archt-main-menu', 'class', $settings['layout'] );

		if ( $vertical_children_expanded ) {
			$this->add_render_attribute( 'archt-main-menu', 'class', 'archt-vertical-children-expanded' );
		}

		$this->add_render_attribute( 'archt-main-menu', 'data-layout', $settings['layout'] );

		if ( 'expanded' === $settings['layout'] ) {
			if ( 'open' === $settings['expanded_submenus'] ) {
				$this->add_render_attribute( 'archt-main-menu', 'class', 'archt-expanded-always-open' );
			} elseif ( 'yes' === $settings['expanded_accordion'] ) {
				$this->add_render_attribute( 'archt-main-menu', 'data-accordion', '1' );
			}
		}

		if ( $settings['pointer'] ) {
			if ( 'horizontal' === $settings['layout'] || 'vertical' === $settings['layout'] ) {
				$this->add_render_attribute( 'archt-main-menu', 'class', 'archt-pointer__' . $settings['pointer'] );

				if ( in_array( $settings['pointer'], [ 'double-line', 'underline', 'overline' ], true ) ) {
					$key = 'animation_line';
					$this->add_render_attribute( 'archt-main-menu', 'class', 'archt-animation__' . $settings[ $key ] );
				} elseif ( 'framed' === $settings['pointer'] ) {
					$key = 'animation_' . $settings['pointer'];
					$this->add_render_attribute( 'archt-main-menu', 'class', 'archt-animation__' . $settings[ $key ] );
				}
			}
		}

		$this->add_render_attribute(
			'archt-nav-menu',
			'class',
			[
				'archt-nav-menu__layout-' . $settings['layout'],
				'archt-nav-menu__submenu-' . $settings['submenu_icon'],
			]
		);

		?>
			<div <?php $this->print_render_attribute_string( 'archt-main-menu' ); ?>>
				<nav <?php $this->print_render_attribute_string( 'archt-nav-menu' ); ?>>
					<?php echo wp_nav_menu( $args ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped by the menu walker. ?> 
				</nav>
			</div>
			<?php
	}
}
