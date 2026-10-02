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
	 * Start a menu item.
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
		$indent = ( $depth ) ? str_repeat( "\t", $depth ) : '';
		$args   = (object) $args;

		$class_names = '';
		$value       = '';
		$rel_xfn     = '';
		$rel_blank   = '';

		$classes = empty( $item->classes ) ? [] : (array) $item->classes;
		$submenu = in_array( 'menu-item-has-children', $classes, true ) ? ' archt-has-submenu' : '';

		if ( 0 === $depth ) {
			array_push( $classes, 'parent' );
		}

		$class_names = join( ' ', apply_filters( 'nav_menu_css_class', array_filter( $classes ), $item, $args, $depth ) );
		$class_names = ' class="' . esc_attr( $class_names ) . $submenu . '"';
		$value       = apply_filters( 'nav_menu_li_values', $value );

		$output .= $indent . '<li id="menu-item-' . $item->ID . '"' . $value . $class_names . '>';

		if ( isset( $item->target ) && '_blank' === $item->target && isset( $item->xfn ) && false === strpos( $item->xfn, 'noopener' ) ) {
			$rel_xfn = ' noopener';
		}
		if ( isset( $item->target ) && '_blank' === $item->target && isset( $item->xfn ) && empty( $item->xfn ) ) {
			$rel_blank = 'rel="noopener"';
		}

		$attributes  = ! empty( $item->attr_title ) ? ' title="' . esc_attr( $item->attr_title ) . '"' : '';
		$attributes .= ! empty( $item->target ) ? ' target="' . esc_attr( $item->target ) . '"' : '';
		$attributes .= ! empty( $item->xfn ) ? ' rel="' . esc_attr( $item->xfn ) . $rel_xfn . '"' : '' . $rel_blank;
		$attributes .= ! empty( $item->url ) ? ' href="' . esc_url( $item->url ) . '"' : '';

		$a_classes = 'archt-menu-item';
		if ( in_array( 'current-menu-item', $item->classes, true ) && $depth > 0 ) {
			$a_classes .= ' archt-sub-menu-item archt-sub-menu-item-active';
		} elseif ( $depth > 0 ) {
			$a_classes .= ' archt-sub-menu-item';
		}

		$icon_position = $this->settings['icon_position'];

		$atts           = apply_filters( 'archt_nav_menu_attrs', $attributes );
		$a_classes_attr = ' class="' . esc_attr( $a_classes ) . '"';

		$item_output  = in_array( 'menu-item-has-children', $classes, true ) ? '<div class="archt-has-submenu-container">' : '';
		$item_output .= $args->before;
		if ( 'left' === $icon_position ) {
			if ( in_array( 'menu-item-has-children', $classes, true ) ) {
				switch ( $this->settings['submenu_icon'] ) {
					case 'plus':
						$item_output .= ' <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" aria-labelledby="plusSignTitle" role="img"><title id="plusSignTitle">Plus Sign</title><path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>';
						break;
					case 'classic':
						$item_output .= ' <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" aria-labelledby="caretDownTitle" role="img"><title id="caretDownTitle">Caret Down</title><path d="M6 9l6 6 6-6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>';
						break;
					case 'arrow':
					default:
						$item_output .= ' <svg xmlns="http://www.w3.org/2000/svg" width="10" height="16" viewBox="0 0 14 24" fill="none" aria-labelledby="downArrowTitle" role="img"><title id="downArrowTitle">Narrow Downward Arrow</title><path d="M7 15L1 9h12L7 15z" fill="currentColor"/></svg>';
						break;
				}
			}
		}
		$item_output .= '<a' . $atts . $a_classes_attr . '>';
		$item_output .= $args->link_before . apply_filters( 'the_title', $item->title, $item->ID ) . $args->link_after;
		$item_output .= '</a>';
		if ( 'right' === $icon_position ) {
			if ( in_array( 'menu-item-has-children', $classes, true ) ) {
				switch ( $this->settings['submenu_icon'] ) {
					case 'plus':
						$item_output .= ' <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" aria-labelledby="plusSignTitle" role="img"><title id="plusSignTitle">Plus Sign</title><path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>';
						break;
					case 'classic':
						$item_output .= ' <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" aria-labelledby="caretDownTitle" role="img"><title id="caretDownTitle">Caret Down</title><path d="M6 9l6 6 6-6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>';
						break;
					case 'arrow':
					default:
						$item_output .= ' <svg xmlns="http://www.w3.org/2000/svg" width="10" height="16" viewBox="0 0 14 24" fill="none" aria-labelledby="downArrowTitle" role="img"><title id="downArrowTitle">Narrow Downward Arrow</title><path d="M7 15L1 9h12L7 15z" fill="currentColor"/></svg>';
						break;
				}
			}
		}
		$item_output .= $args->after;
		$item_output .= in_array( 'menu-item-has-children', $classes, true ) ? '</div>' : '';

		$output .= apply_filters( 'walker_nav_menu_start_el', $item_output, $item, $depth, $args );
	}
}
