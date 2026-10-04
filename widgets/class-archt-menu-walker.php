<?php
/**
 * Navigation menu walker.
 *
 * @package ARCHT_Widgets
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class Menu_Walker.
 */
class ARCHT_Menu_Walker extends \Walker_Nav_Menu {

	/**
	 * Widget settings.
	 *
	 * @var array
	 */
	protected $settings;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param array $settings Widget settings.
	 */
	public function __construct( $settings ) {
		$this->settings = $settings;
	}

	/**
	 * Whether the first vertical submenu is open.
	 *
	 * @var bool
	 */
	protected $first_vertical_submenu_opened = false;

	/**
	 * Start a submenu.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string         $output Output.
	 * @param int            $depth  Depth.
	 * @param stdClass|array $args   Menu arguments.
	 */
	public function start_lvl( &$output, $depth = 0, $args = [] ) {
		$indent     = str_repeat( "\t", $depth );
		$axis_class = ( 0 === $depth % 2 ) ? 'archt-submenu-vertical' : 'archt-submenu-horizontal';

		$classes = [
			'sub-menu',
			$axis_class,
		];

		$should_open_first = (
		isset( $this->settings['layout'] ) &&
		'vertical' === $this->settings['layout'] &&
		! empty( $this->settings['vertical_children_expanded'] ) &&
		'yes' === $this->settings['vertical_children_expanded'] &&
		0 === $depth &&
		false === $this->first_vertical_submenu_opened
		);

		if ( $should_open_first ) {
			$classes[]                           = 'open';
			$this->first_vertical_submenu_opened = true;
		}

		$class_names = implode( ' ', array_map( 'esc_attr', $classes ) );

		$output .= "\n$indent<ul class=\"" . esc_attr( $class_names ) . "\">\n";
	}


	/**
	 * Start a menu item, following the core Walker_Nav_Menu filters so other plugins can hook in.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string         $output Output.
	 * @param WP_Post        $item   Menu item.
	 * @param int            $depth  Depth.
	 * @param stdClass|array $args   Menu arguments.
	 * @param int            $id     Item ID.
	 */
	public function start_el( &$output, $item, $depth = 0, $args = [], $id = 0 ) {
		$args         = (object) $args;
		$classes      = empty( $item->classes ) ? [] : (array) $item->classes;
		$has_children = in_array( 'menu-item-has-children', $classes, true );
		$schema       = isset( $this->settings['schema_support'] ) && 'yes' === $this->settings['schema_support'];

		if ( 0 === $depth ) {
			$classes[] = 'parent';
		}

		$class_names = implode( ' ', apply_filters( 'nav_menu_css_class', array_filter( $classes ), $item, $args, $depth ) );
		$class_names = trim( $class_names . ( $has_children ? ' archt-has-submenu' : '' ) );
		$li_id       = apply_filters( 'nav_menu_item_id', 'menu-item-' . $item->ID, $item, $args, $depth );

		$output .= str_repeat( "\t", $depth ) . '<li' . ( $li_id ? ' id="' . esc_attr( $li_id ) . '"' : '' ) . ' class="' . esc_attr( $class_names ) . '"' . ( $schema ? ' itemprop="name"' : '' ) . '>';

		$link_class = 'archt-menu-item';

		if ( $depth > 0 ) {
			$link_class .= in_array( 'current-menu-item', $classes, true ) ? ' archt-sub-menu-item archt-sub-menu-item-active' : ' archt-sub-menu-item';
		}

		$atts = [
			'title'        => $item->attr_title,
			'target'       => $item->target,
			'rel'          => ( '_blank' === $item->target && empty( $item->xfn ) ) ? 'noopener' : $item->xfn,
			'href'         => $item->url,
			'aria-current' => $item->current ? 'page' : '',
			'class'        => $link_class,
			'itemprop'     => $schema ? 'url' : '',
		];
		$atts = apply_filters( 'nav_menu_link_attributes', $atts, $item, $args, $depth );

		$attributes = '';

		foreach ( $atts as $attr => $value ) {
			if ( is_scalar( $value ) && '' !== $value && false !== $value ) {
				$attributes .= ' ' . $attr . '="' . ( 'href' === $attr ? esc_url( $value ) : esc_attr( $value ) ) . '"';
			}
		}

		$title = apply_filters( 'the_title', $item->title, $item->ID );
		$title = apply_filters( 'nav_menu_item_title', $title, $item, $args, $depth );
		$icon  = $has_children ? $this->get_submenu_icon() : '';
		$right = isset( $this->settings['icon_position'] ) && 'right' === $this->settings['icon_position'];

		$item_output  = $has_children ? '<div class="archt-has-submenu-container">' : '';
		$item_output .= $args->before . ( $right ? '' : $icon );
		$item_output .= '<a' . $attributes . '>' . $args->link_before . $title . $args->link_after . '</a>';
		$item_output .= ( $right ? $icon : '' ) . $args->after;
		$item_output .= $has_children ? '</div>' : '';

		$output .= apply_filters( 'walker_nav_menu_start_el', $item_output, $item, $depth, $args );
	}

	/**
	 * Get the submenu toggle icon. Decorative: the link text already names the item.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @return string
	 */
	private function get_submenu_icon() {
		$icon = isset( $this->settings['submenu_icon'] ) ? $this->settings['submenu_icon'] : 'arrow';

		switch ( $icon ) {
			case 'none':
				return '';
			case 'plus':
				return ' <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" aria-hidden="true" focusable="false"><path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>';
			case 'classic':
				return ' <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" aria-hidden="true" focusable="false"><path d="M6 9l6 6 6-6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>';
			default:
				return ' <svg xmlns="http://www.w3.org/2000/svg" width="10" height="16" viewBox="0 0 14 24" fill="none" aria-hidden="true" focusable="false"><path d="M7 15L1 9h12L7 15z" fill="currentColor"/></svg>';
		}
	}
}
