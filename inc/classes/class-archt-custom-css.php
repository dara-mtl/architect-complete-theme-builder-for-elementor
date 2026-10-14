<?php
/**
 * Custom CSS for elements and pages, skipped when Elementor Pro is active.
 *
 * The control is registered only for users who hold the capability WordPress requires for
 * its own Additional CSS editor, and every value is sanitized on save and again on render.
 *
 * Portions adapted from Elementor / Elementor Pro, Copyright (C) Elementor Ltd.,
 * licensed GPLv3.
 *
 * @package ARCHT_Widgets
 * @since 1.0.0
 */

use Elementor\Controls_Manager;
use Elementor\Element_Base;
use Elementor\Core\Files\CSS\Post;
use Elementor\Core\DynamicTags\Dynamic_CSS;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( defined( 'ELEMENTOR_PRO_VERSION' ) ) {
	return;
}

/**
 * Adds the Custom CSS control and injects its CSS into Elementor's stylesheets.
 *
 * @since 1.0.0
 */
class ARCHT_Custom_Css {

	/**
	 * Capability required to write custom CSS.
	 *
	 * 'edit_css' is the capability WordPress gives its own Additional CSS editor. Core maps it
	 * to 'unfiltered_html', so it is denied when DISALLOW_UNFILTERED_HTML is set and, on
	 * multisite, to everyone but super admins.
	 *
	 * @since 1.0.0
	 */
	const CAPABILITY = 'edit_css';

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function __construct() {
		add_action( 'elementor/element/after_section_end', [ __CLASS__, 'add_controls_section' ], 10, 2 );
		add_action( 'elementor/element/parse_css', [ $this, 'add_post_css' ], 10, 2 );
		add_action( 'elementor/css-file/post/parse', [ $this, 'add_page_settings_css' ] );
		add_action( 'elementor/editor/after_enqueue_scripts', [ $this, 'enqueue_editor_scripts' ] );

		// Sanitize what is written to the database, not only what is printed.
		add_filter( 'elementor/document/save/data', [ $this, 'sanitize_saved_data' ], 10, 2 );

		// Elementor free strips custom_css from v4 atomic styles; keep it, sanitized.
		add_filter( 'elementor/atomic_widgets/editor_data/element_styles', [ $this, 'keep_atomic_custom_css' ], 20, 2 );
	}

	/**
	 * Whether the current user may write custom CSS.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return bool
	 */
	public static function user_can_edit_css() {
		/**
		 * Filters the capability required to write custom CSS.
		 *
		 * @since 1.0.0
		 *
		 * @param string $capability Capability name.
		 */
		$capability = apply_filters( 'archt/custom_css/capability', self::CAPABILITY );

		return current_user_can( $capability );
	}

	/**
	 * Remove everything that could leave the CSS context, fetch a remote file, or execute.
	 *
	 * Follows WP_Customize_Custom_CSS_Setting, which guards against a '</style' breakout, and
	 * goes further by dropping @import and the legacy script-in-CSS properties.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $css CSS as written in the editor.
	 * @return string Sanitized CSS.
	 */
	public static function sanitize_css( $css ) {
		if ( ! is_string( $css ) || '' === trim( $css ) ) {
			return '';
		}

		// Control characters, keeping tab, newline and carriage return.
		$css = preg_replace( '/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $css );

		/*
		 * '<' has no meaning in CSS, and removing it makes a '</style>' breakout impossible,
		 * including the partial '</sty' form that a concatenated stylesheet could complete.
		 * '>' is kept, as child selectors need it.
		 */
		$css = str_replace( '<', '', $css );

		// Remote fetches, and declarations that carry a script or a binding.
		$css = preg_replace( '/@(?:import|charset)\b[^;{}]*;?/i', '', $css );
		$css = preg_replace( '/(?<![\w-])(?:behavior|-moz-binding)\s*:[^;}]*;?/i', '', $css );
		$css = preg_replace( '/[^;{}]*(?:expression\s*\(|(?:java|vb|live)script\s*:|mocha\s*:)[^;}]*;?/i', '', $css );

		// Close an unterminated comment, so it cannot swallow the rules Elementor appends after ours.
		if ( substr_count( $css, '/*' ) > substr_count( $css, '*/' ) ) {
			$css .= '*/';
		}

		// Balance braces, for the same reason.
		$unclosed = substr_count( $css, '{' ) - substr_count( $css, '}' );
		if ( $unclosed > 0 ) {
			$css .= str_repeat( '}', $unclosed );
		}

		return trim( $css );
	}

	/**
	 * Sanitize the custom CSS in a document as it is saved.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param array $data Document data being saved.
	 * @return array
	 */
	public function sanitize_saved_data( $data ) {
		if ( ! is_array( $data ) ) {
			return $data;
		}

		if ( isset( $data['settings']['custom_css'] ) ) {
			$data['settings']['custom_css'] = self::sanitize_css( $data['settings']['custom_css'] );
		}

		if ( isset( $data['elements'] ) && is_array( $data['elements'] ) ) {
			$data['elements'] = self::sanitize_elements( $data['elements'] );
		}

		return $data;
	}

	/**
	 * Sanitize the custom CSS of every element in a tree.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @param array $elements Elements.
	 * @return array
	 */
	private static function sanitize_elements( $elements ) {
		foreach ( $elements as $index => $element ) {
			if ( ! is_array( $element ) ) {
				continue;
			}

			if ( isset( $element['settings']['custom_css'] ) ) {
				$elements[ $index ]['settings']['custom_css'] = self::sanitize_css( $element['settings']['custom_css'] );
			}

			if ( isset( $element['styles'] ) && is_array( $element['styles'] ) ) {
				$elements[ $index ]['styles'] = self::sanitize_atomic_styles( $element['styles'] );
			}

			if ( isset( $element['elements'] ) && is_array( $element['elements'] ) ) {
				$elements[ $index ]['elements'] = self::sanitize_elements( $element['elements'] );
			}
		}

		return $elements;
	}

	/**
	 * Sanitize the custom CSS in v4 atomic style variants, where it is stored base64 encoded.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @param array $styles Atomic styles.
	 * @return array
	 */
	private static function sanitize_atomic_styles( $styles ) {
		foreach ( $styles as $style_id => $style ) {
			if ( empty( $style['variants'] ) || ! is_array( $style['variants'] ) ) {
				continue;
			}

			foreach ( $style['variants'] as $variant_index => $variant ) {
				if ( empty( $variant['custom_css']['raw'] ) || ! is_string( $variant['custom_css']['raw'] ) ) {
					continue;
				}

				$raw = base64_decode( $variant['custom_css']['raw'], true );
				$raw = false === $raw ? '' : self::sanitize_css( $raw );

				if ( '' === $raw ) {
					unset( $styles[ $style_id ]['variants'][ $variant_index ]['custom_css'] );
					continue;
				}

				$styles[ $style_id ]['variants'][ $variant_index ]['custom_css'] = [ 'raw' => base64_encode( $raw ) ];
			}
		}

		return $styles;
	}

	/**
	 * Keep the custom CSS stripped from v4 atomic styles, sanitized.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param array $filtered Styles without custom_css.
	 * @param array $original Styles as authored.
	 * @return array
	 */
	public function keep_atomic_custom_css( $filtered, $original ) {
		return is_array( $original ) ? self::sanitize_atomic_styles( $original ) : $filtered;
	}

	/**
	 * Enqueue the editor CodeMirror script.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function enqueue_editor_scripts() {
		if ( ! self::user_can_edit_css() ) {
			return;
		}

		wp_enqueue_script( 'archt-custom-css-script', ARCHT_Elementor::asset_url( 'assets/js/archt-custom-css.js' ), [ 'jquery' ], ARCHT_Elementor::VERSION, true );
	}

	/**
	 * Replace Elementor's Custom CSS upsell section with the real controls.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param Element_Base $element    Element.
	 * @param string       $section_id Section that just ended.
	 */
	public static function add_controls_section( $element, $section_id ) {
		if ( 'section_custom_css_pro' !== $section_id ) {
			return;
		}

		// Users without the capability keep Elementor's own section, and never see a field.
		if ( ! self::user_can_edit_css() ) {
			return;
		}

		\Elementor\Plugin::$instance->controls_manager->remove_control_from_stack(
			$element->get_unique_name(),
			[ 'section_custom_css_pro', 'custom_css_pro' ]
		);

		$element->start_controls_section(
			'archt_custom_element_css',
			[
				'label' => esc_html__( 'Custom CSS', 'architect-complete-theme-builder-for-elementor' ),
				'tab'   => Controls_Manager::TAB_ADVANCED,
			]
		);

		$element->add_control(
			'custom_css_title',
			[
				'raw'  => esc_html__( 'Add your own custom CSS here', 'architect-complete-theme-builder-for-elementor' ),
				'type' => Controls_Manager::RAW_HTML,
			]
		);

		$element->add_control(
			'custom_css',
			[
				'type'        => Controls_Manager::CODE,
				'label'       => esc_html__( 'Custom CSS', 'architect-complete-theme-builder-for-elementor' ),
				'language'    => 'css',
				'render_type' => 'ui',
				'show_label'  => false,
				'description' => esc_html__(
					"Use 'selector' to target wrapper element. Examples:\nselector {color: red;} // For main element\nselector .child-element {margin: 10px;} // For child element\n.my-class {text-align: center;} // Or use any custom selector",
					'architect-complete-theme-builder-for-elementor'
				),
				'separator'   => 'none',
			]
		);

		$element->end_controls_section();
	}

	/**
	 * Append an element's custom CSS to the post stylesheet.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param Post         $post_css Post CSS file.
	 * @param Element_Base $element  Element.
	 */
	public function add_post_css( $post_css, $element ) {
		if ( $post_css instanceof Dynamic_CSS ) {
			return;
		}

		$element_settings = $element->get_settings();

		if ( empty( $element_settings['custom_css'] ) ) {
			return;
		}

		$css = self::sanitize_css( $element_settings['custom_css'] );

		if ( empty( $css ) ) {
			return;
		}

		$css = str_replace( 'selector', $post_css->get_element_unique_selector( $element ), $css );
		$css = sprintf(
			'/* Start custom CSS for %s, class: %s */',
			$element->get_name(),
			$element->get_unique_selector()
		) . $css . '/* End custom CSS */';

		$post_css->get_stylesheet()->add_raw_css( $css );
	}

	/**
	 * Append the page custom CSS to the post stylesheet.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param Post $post_css Post CSS file.
	 */
	public function add_page_settings_css( $post_css ) {
		$document   = \Elementor\Plugin::$instance->documents->get( $post_css->get_post_id() );
		$custom_css = self::sanitize_css( $document->get_settings( 'custom_css' ) );

		if ( empty( $custom_css ) ) {
			return;
		}

		$custom_css = str_replace( 'selector', $document->get_css_wrapper_selector(), $custom_css );
		$custom_css = '/* Start custom CSS for page-settings */' . $custom_css . '/* End custom CSS */';

		$post_css->get_stylesheet()->add_raw_css( $custom_css );
	}
}

new ARCHT_Custom_Css();
