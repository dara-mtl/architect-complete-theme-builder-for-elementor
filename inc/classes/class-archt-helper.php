<?php
/**
 * Helper functions.
 *
 * @package ARCHT_Widgets
 * @since 1.0.0
 */

namespace ARCHT\Inc\Classes;

use Elementor\Icons_Manager;
use Elementor\Core\Responsive\Responsive;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Helper functions.
 *
 * @since 1.0.0
 */
class ARCHT_Helper {
	/**
	 * Compare two values with an operator.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param mixed  $value1   Left value.
	 * @param string $operator Operator.
	 * @param mixed  $value2   Right value.
	 * @return bool
	 */
	public static function check_operator( $value1, $operator, $value2 ) {
		switch ( $operator ) {
			case '<':
				return $value1 < $value2;
			case '<=':
				return $value1 <= $value2;
			case '>':
				return $value1 > $value2;
			case '>=':
				return $value1 >= $value2;
			case '==':
				return $value1 == $value2; // phpcs:ignore Universal.Operators.StrictComparisons.LooseEqual
			case '===':
				return $value1 === $value2;
			case '!==':
				return $value1 !== $value2;
			case '!=':
			case '<>':
				return $value1 != $value2; // phpcs:ignore Universal.Operators.StrictComparisons.LooseNotEqual
			case '||':
			case 'or':
				return $value1 || $value2;
			case '&&':
			case 'and':
				return $value1 && $value2;
			case 'xor':
				return $value1 xor $value2;
			case 'empty':
				return empty( $value1 );
			case 'not_empty':
				return ! empty( $value1 );
			case 'contain':
				// strpos rather than str_contains: the plugin supports PHP 7.4.
				return '' !== (string) $value2 && false !== strpos( (string) $value1, (string) $value2 );
			case 'not_contain':
				return '' === (string) $value2 || false === strpos( (string) $value1, (string) $value2 );
			default:
				return false;
		} // end switch.
	}

	/**
	 * Get the current archive URL.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string
	 */
	public static function archt_get_the_archive_url() {
		$url = '';
		if ( is_category() || is_tag() || is_tax() ) {
			$url = get_term_link( get_queried_object() );
		} elseif ( is_author() ) {
			$url = get_author_posts_url( get_queried_object_id() );
		} elseif ( is_year() ) {
			$url = get_year_link( get_query_var( 'year' ) );
		} elseif ( is_month() ) {
			$url = get_month_link( get_query_var( 'year' ), get_query_var( 'monthnum' ) );
		} elseif ( is_day() ) {
			$url = get_day_link( get_query_var( 'year' ), get_query_var( 'monthnum' ), get_query_var( 'day' ) );
		} elseif ( is_post_type_archive() ) {
			$url = get_post_type_archive_link( get_post_type() );
		}

		return $url;
	}

	/**
	 * Get the current page title.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param bool $include_context Whether to prefix archive titles with their type.
	 * @return string
	 */
	public static function archt_get_page_title( $include_context = true ) {
		$title = '';

		if ( is_singular() ) {
			$title = get_the_title();

			if ( $include_context ) {
				$title = get_post_type_object( get_post_type() )->labels->singular_name . ': ' . $title;
			}
		} elseif ( is_search() ) {
			/* translators: %s: Search term. */
			$title = sprintf( __( 'Search Results for: %s', 'architect-complete-theme-builder-for-elementor' ), get_search_query() );

			if ( get_query_var( 'paged' ) ) {
				/* translators: %s: Page number. */
				$title .= sprintf( __( '&nbsp;&ndash; Page %s', 'architect-complete-theme-builder-for-elementor' ), get_query_var( 'paged' ) );
			}
		} elseif ( is_home() ) {
			// The page set as "Posts page" in Settings > Reading, when there is one.
			$title = get_option( 'page_for_posts' ) ? single_post_title( '', false ) : __( 'Blog', 'architect-complete-theme-builder-for-elementor' );
		} elseif ( is_404() ) {
			$title = __( 'Page Not Found', 'architect-complete-theme-builder-for-elementor' );
		} elseif ( is_archive() ) {
			// WordPress builds every archive title; the prefix ("Category:", "Tag:"...) is its context.
			if ( ! $include_context ) {
				add_filter( 'get_the_archive_title_prefix', '__return_empty_string' );
			}

			$title = get_the_archive_title();

			remove_filter( 'get_the_archive_title_prefix', '__return_empty_string' );
		}

		return apply_filters( 'archt/core_elements/get_the_archive_title', $title );
	}

	/**
	 * Whether a meta key is an ACF field.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $meta_key Meta key.
	 * @return bool
	 */
	public static function is_acf_field( $meta_key ) {
		return function_exists( 'get_field_object' ) && get_field_object( $meta_key ) !== false;
	}

	/**
	 * Summarize a template's include or exclude display conditions.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param array  $settings Template page settings.
	 * @param string $suffix   'inc' or 'exc'.
	 * @return string
	 */
	public static function get_display_conditions_summary( $settings, $suffix ) {
		static $labels = null;

		if ( null === $labels ) {
			$labels = self::get_display_conditions_options( 'all' );
		}

		$items  = [];
		$picked = [
			'specific'       => 'selected_posts_',
			'specific_terms' => 'selected_terms_',
		];

		foreach ( (array) ( $settings[ 'display_conditions_' . $suffix ] ?? [] ) as $key ) {
			$label = $labels[ $key ] ?? $key;

			if ( isset( $picked[ $key ] ) ) {
				$ids    = $settings[ $picked[ $key ] . $suffix ] ?? [];
				$ids    = is_string( $ids ) ? array_filter( explode( ',', $ids ) ) : (array) $ids;
				$label .= ' (' . count( $ids ) . ')';
			}

			$items[] = $label;
		}

		return implode( ', ', $items );
	}

	/**
	 * Get the display condition options.
	 *
	 * The header_* keys are group headings, styled by archt-backend.css through that prefix; don't rename them.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $type Options: 'all', 'singular', 'archive'.
	 * @return array
	 */
	public static function get_display_conditions_options( $type ) {
		$post_types = get_post_types( [ 'public' => true ], 'objects' );
		$taxonomies = get_taxonomies( [ 'public' => true ], 'objects' );
		$is_woo     = class_exists( 'WooCommerce' );
		$options    = [];

		if ( 'all' === $type ) {
			$options['all'] = esc_html__( 'Entire Site', 'architect-complete-theme-builder-for-elementor' );
		}

		if ( in_array( $type, [ 'all', 'singular' ], true ) ) {
			$options['header_singular'] = esc_html__( 'Single', 'architect-complete-theme-builder-for-elementor' );
			$options['home']            = esc_html__( 'Front Page', 'architect-complete-theme-builder-for-elementor' );
			$options['404']             = esc_html__( '404 Page', 'architect-complete-theme-builder-for-elementor' );
			$options['singular']        = esc_html__( 'All Singular', 'architect-complete-theme-builder-for-elementor' );
			$options['specific']        = esc_html__( 'Specific Page/Post', 'architect-complete-theme-builder-for-elementor' );

			if ( $is_woo ) {
				$options['cart']     = esc_html__( 'Cart Page', 'architect-complete-theme-builder-for-elementor' );
				$options['checkout'] = esc_html__( 'Checkout Page', 'architect-complete-theme-builder-for-elementor' );
				$options['account']  = esc_html__( 'Account Page', 'architect-complete-theme-builder-for-elementor' );
			}

			foreach ( $post_types as $pt ) {
				/* translators: %s: Post type singular label. */
				$options[ 'single-' . $pt->name ] = sprintf( esc_html__( 'Single %s', 'architect-complete-theme-builder-for-elementor' ), $pt->labels->singular_name );
			}
		}

		if ( in_array( $type, [ 'all', 'archive' ], true ) ) {
			$options['header_archive'] = esc_html__( 'Archive', 'architect-complete-theme-builder-for-elementor' );
			$options['archive']        = esc_html__( 'All Archives', 'architect-complete-theme-builder-for-elementor' );
			$options['specific_terms'] = esc_html__( 'Specific Term', 'architect-complete-theme-builder-for-elementor' );
			$options['blog']           = esc_html__( 'Blog Archive', 'architect-complete-theme-builder-for-elementor' );
			$options['search']         = esc_html__( 'Search Results', 'architect-complete-theme-builder-for-elementor' );
			$options['author']         = esc_html__( 'Author Archive', 'architect-complete-theme-builder-for-elementor' );
			$options['date']           = esc_html__( 'Date Archive', 'architect-complete-theme-builder-for-elementor' );

			if ( $is_woo ) {
				$options['shop'] = esc_html__( 'Shop Page', 'architect-complete-theme-builder-for-elementor' );
			}

			foreach ( $post_types as $pt ) {
				if ( $pt->has_archive ) {
					/* translators: %s: Post type plural label. */
					$options[ 'cpt-' . $pt->name ] = sprintf( esc_html__( '%s Archive', 'architect-complete-theme-builder-for-elementor' ), $pt->labels->name );
				}
			}

			$options['header_tax'] = esc_html__( 'Taxonomy', 'architect-complete-theme-builder-for-elementor' );
			foreach ( $taxonomies as $tax ) {
				/* translators: %s: Taxonomy label. */
				$options[ 'tax-' . $tax->name ] = sprintf( esc_html__( '%s Archive', 'architect-complete-theme-builder-for-elementor' ), $tax->label );
			}
		}

		return $options;
	}

	/**
	 * Render an Elementor icon.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $icon Icon settings.
	 * @return string|false
	 */
	public static function archt_get_icons( $icon = '' ) {
		if ( ! empty( $icon ) ) {
			ob_start();
			\Elementor\Icons_Manager::render_icon( $icon, [ 'aria-hidden' => 'true' ] );
			return ob_get_clean();
		} else {
			return false;
		}
	}

	/**
	 * Sanitize input, allowing SVG.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $input Input.
	 * @return string
	 */
	public static function sanitize_and_escape_svg_input( $input ) {
		static $allowed_html = null;

		if ( null === $allowed_html ) {
			$allowed_html = array_merge(
				wp_kses_allowed_html( 'post' ),
				[
					'i'        => [ 'class' => [] ],
					'b'        => [],
					'strong'   => [],
					'em'       => [],
					'u'        => [],
					'br'       => [],
					'svg'      => [
						'xmlns'               => [],
						'width'               => [],
						'height'              => [],
						'viewbox'             => [],
						'preserveaspectratio' => [],
						'fill'                => [],
						'stroke'              => [],
						'stroke-width'        => [],
						'd'                   => [],
						'x'                   => [],
						'y'                   => [],
						'cx'                  => [],
						'cy'                  => [],
						'r'                   => [],
						'rx'                  => [],
						'ry'                  => [],
						'points'              => [],
						'transform'           => [],
						'dy'                  => [],
						'dx'                  => [],
					],
					'path'     => [
						'd'            => [],
						'fill'         => [],
						'stroke'       => [],
						'stroke-width' => [],
						'transform'    => [],
					],
					'circle'   => [
						'cx'           => [],
						'cy'           => [],
						'r'            => [],
						'fill'         => [],
						'stroke'       => [],
						'stroke-width' => [],
					],
					'rect'     => [
						'x'            => [],
						'y'            => [],
						'width'        => [],
						'height'       => [],
						'rx'           => [],
						'ry'           => [],
						'fill'         => [],
						'stroke'       => [],
						'stroke-width' => [],
					],
					'line'     => [
						'x1'           => [],
						'y1'           => [],
						'x2'           => [],
						'y2'           => [],
						'stroke'       => [],
						'stroke-width' => [],
					],
					'polygon'  => [
						'points'       => [],
						'fill'         => [],
						'stroke'       => [],
						'stroke-width' => [],
					],
					'polyline' => [
						'points'       => [],
						'fill'         => [],
						'stroke'       => [],
						'stroke-width' => [],
					],
					'text'     => [
						'x'           => [],
						'y'           => [],
						'fill'        => [],
						'font-size'   => [],
						'font-family' => [],
						'text-anchor' => [],
					],
					'tspan'    => [
						'x'           => [],
						'y'           => [],
						'fill'        => [],
						'font-size'   => [],
						'font-family' => [],
						'dy'          => [],
						'dx'          => [],
					],
				]
			);
		}

		return wp_kses( $input, $allowed_html );
	}
}
