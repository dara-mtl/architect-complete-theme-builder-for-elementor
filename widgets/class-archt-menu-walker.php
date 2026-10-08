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
	 * Whether the first vertical submenu is already open.
	 *
	 * @var bool
	 */
	protected $first_opened = false;

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
		$output .= "\n" . str_repeat( "\t", $depth ) . '<ul class="sub-menu archt-menu__sub">' . "\n";
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
		$open         = $has_children && $this->is_open( $classes, $depth );

		$class_names = implode( ' ', apply_filters( 'nav_menu_css_class', array_filter( $classes ), $item, $args, $depth ) );
		$class_names = trim( $class_names . ' archt-menu__item' . ( $has_children ? ' archt-has-submenu' : '' ) . ( $open ? ' is-open' : '' ) );
		$li_id       = apply_filters( 'nav_menu_item_id', 'menu-item-' . $item->ID, $item, $args, $depth );

		$output .= str_repeat( "\t", $depth ) . '<li' . ( $li_id ? ' id="' . esc_attr( $li_id ) . '"' : '' ) . ' class="' . esc_attr( $class_names ) . '"' . ( $schema ? ' itemprop="name"' : '' ) . '>';

		$atts = [
			'title'        => $item->attr_title,
			'target'       => $item->target,
			'rel'          => ( '_blank' === $item->target && empty( $item->xfn ) ) ? 'noopener' : $item->xfn,
			'href'         => $item->url,
			'aria-current' => $item->current ? 'page' : '',
			'class'        => 'archt-menu__link',
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

		$item_output  = $args->before . '<a' . $attributes . '>' . $args->link_before . $title . $args->link_after . '</a>' . $args->after;
		$item_output .= $has_children ? $this->get_toggle( $title, $open ) : '';

		$output .= apply_filters( 'walker_nav_menu_start_el', $item_output, $item, $depth, $args );
	}

	/**
	 * Whether a submenu renders open: the first one of a vertical menu set to start expanded,
	 * and the current branch of an expanded menu, so it shows without waiting for the script.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @param array $classes Menu item classes.
	 * @param int   $depth   Depth.
	 *
	 * @return bool
	 */
	private function is_open( $classes, $depth ) {
		$layout = isset( $this->settings['layout'] ) ? $this->settings['layout'] : 'horizontal';

		if ( 'vertical' === $layout && 0 === $depth && ! $this->first_opened && ! empty( $this->settings['vertical_children_expanded'] ) && 'yes' === $this->settings['vertical_children_expanded'] ) {
			$this->first_opened = true;
			return true;
		}

		return 'expanded' === $layout && (bool) array_intersect( [ 'current-menu-ancestor', 'current-menu-parent' ], $classes );
	}

	/**
	 * Get the submenu toggle button. None when the icon is set to none: the parent link opens the submenu instead.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @param string $title Menu item title.
	 * @param bool   $open  Whether the submenu renders open.
	 *
	 * @return string
	 */
	private function get_toggle( $title, $open ) {
		$icon = isset( $this->settings['submenu_icon'] ) ? $this->settings['submenu_icon'] : 'arrow';

		switch ( $icon ) {
			case 'none':
				return '';
			case 'plus':
				$svg = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" aria-hidden="true" focusable="false"><path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>';
				break;
			case 'classic':
				$svg = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" aria-hidden="true" focusable="false"><path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>';
				break;
			default:
				$svg = '<svg xmlns="http://www.w3.org/2000/svg" width="10" height="16" viewBox="0 0 14 24" fill="none" aria-hidden="true" focusable="false"><path d="M7 15L1 9h12L7 15z" fill="currentColor"/></svg>';
		}

		/* translators: %s: Menu item title. */
		$label = sprintf( __( 'Toggle submenu for %s', 'architect-complete-theme-builder-for-elementor' ), wp_strip_all_tags( $title ) );

		return '<button type="button" class="archt-menu__toggle" aria-expanded="' . ( $open ? 'true' : 'false' ) . '" aria-label="' . esc_attr( $label ) . '">' . $svg . '</button>';
	}
}
