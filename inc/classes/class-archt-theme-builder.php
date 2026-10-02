<?php
/**
 * Theme Builder for Elementor integration.
 *
 * @package ARCHT_Widgets
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Resolves and renders the active templates.
 */
class ARCHT_Theme_Builder {
	/**
	 * Single instance.
	 *
	 * @var ARCHT_Theme_Builder|null
	 */
	private static $instance = null;

	/**
	 * Active template IDs for this request.
	 *
	 * @var array
	 */
	private $active_templates = [
		'header'  => null,
		'footer'  => null,
		'content' => null,
		'mode'    => 'full-width',
	];

	/**
	 * Popup template IDs for this request.
	 *
	 * @var int[]
	 */
	private $active_popup_ids = [];

	/**
	 * Option holding the template cache version.
	 */
	const CACHE_VERSION_OPTION = 'archt_template_cache_version';

	/**
	 * Get the single instance.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return ARCHT_Theme_Builder
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function __construct() {
		if ( ! did_action( 'elementor/loaded' ) || ARCHT_Elementor::is_pro_active() ) {
			return;
		}

		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_styles' ], 40 );
		add_action( 'elementor/frontend/after_enqueue_styles', [ $this, 'register_atomic_template_styles' ], 5 );

		add_action( 'template_redirect', [ $this, 'determine_active_templates' ], 1 );
		add_action( 'template_redirect', [ $this, 'setup_template_filters' ] );

		add_filter( 'pre_handle_404', [ $this, 'allow_pagination_on_templates' ], 10, 2 );

		add_action( 'save_post_elementor_library', [ __CLASS__, 'flush_template_cache' ] );
		add_action( 'before_delete_post', [ __CLASS__, 'flush_template_cache' ] );
		add_action( 'elementor/document/after_save', [ __CLASS__, 'flush_template_cache' ] );

		add_action( 'wp_head', [ $this, 'output_critical_css' ], 1 );
		add_action( 'wp_footer', [ $this, 'insert_popup' ] );
	}

	/**
	 * Resolve the templates that apply to this request.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function determine_active_templates() {
		if ( $this->is_elementor_edit_or_preview_mode() ) {
			return;
		}

		$this->active_templates['header'] = $this->find_matching_template( $this->get_header_templates() );
		$this->active_templates['footer'] = $this->find_matching_template( $this->get_footer_templates() );

		// Skip content templates on WooCommerce cart and checkout.
		if ( ! ( function_exists( 'is_woocommerce' ) && ( is_cart() || is_checkout() ) ) ) {
			$content_id = $this->find_matching_template( $this->get_elementor_templates() );

			if ( $content_id ) {
				$this->active_templates['content'] = $content_id;
				$this->active_templates['mode']    = $this->get_document_layout( $content_id );
			}
		}

		foreach ( $this->get_popup_templates() as $template_id ) {
			if ( $this->validate_template( $template_id ) ) {
				$this->active_popup_ids[] = $this->get_translated_template_id( $template_id );
			}
		}
	}

	/**
	 * Get the first template whose conditions match this request.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @param int[] $template_ids Template IDs.
	 * @return int|null
	 */
	private function find_matching_template( array $template_ids ) {
		foreach ( $template_ids as $template_id ) {
			if ( $this->validate_template( $template_id ) ) {
				return $this->get_translated_template_id( $template_id );
			}
		}
		return null;
	}

	/**
	 * Get a content template's page layout.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @param int $template_id Template ID.
	 * @return string 'canvas' or 'full-width'.
	 */
	private function get_document_layout( $template_id ) {
		$layout = get_post_meta( $template_id, '_wp_page_template', true );

		return 'elementor_canvas' === $layout ? 'canvas' : 'full-width';
	}

	/**
	 * Hook the template loader, or the theme's header and footer.
	 *
	 * The loader takes over the page for content templates, and for block themes that have no get_header() to hook.
	 * Otherwise only the header and footer are swapped, so the theme keeps its own page template.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function setup_template_filters() {
		$header = $this->active_templates['header'];
		$footer = $this->active_templates['footer'];

		if ( $this->active_templates['content'] || ( ( $header || $footer ) && wp_is_block_theme() ) ) {
			add_filter( 'template_include', [ $this, 'load_custom_template_file' ], 99 );
			return;
		}

		if ( $header ) {
			add_action( 'get_header', [ $this, 'render_theme_header' ] );
		}

		if ( $footer ) {
			add_action( 'get_footer', [ $this, 'render_theme_footer' ] );
		}
	}

	/**
	 * Print the theme's header file with its site header swapped for the header template.
	 *
	 * The theme's wrappers stay in place, as most themes open them in header.php and close them in footer.php.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string|null $name Header template name.
	 */
	public function render_theme_header( $name ) {
		$html     = $this->capture_theme_part( 'header', $name );
		$template = $this->capture_template( $this->active_templates['header'], 'header' );

		if ( '' === trim( $html ) ) {
			?>
			<!DOCTYPE html>
			<html <?php language_attributes(); ?>>
			<head>
				<meta charset="<?php bloginfo( 'charset' ); ?>">
				<meta name="viewport" content="width=device-width, initial-scale=1">
				<?php if ( ! current_theme_supports( 'title-tag' ) ) : ?>
					<title><?php echo esc_html( wp_get_document_title() ); ?></title>
				<?php endif; ?>
				<?php wp_head(); ?>
			</head>
			<body <?php body_class(); ?>>
			<?php
			wp_body_open();
			echo $template; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			return;
		}

		$swapped = $this->replace_element( $html, 'header', 'site-header|masthead', $template );

		// No site header element found: add the template right after the body tag.
		if ( null === $swapped ) {
			$swapped = preg_match( '#<body\b[^>]*>#i', $html, $body, PREG_OFFSET_CAPTURE )
				? substr_replace( $html, $template, $body[0][1] + strlen( $body[0][0] ), 0 )
				: $html . $template;
		}

		echo $swapped; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Print the theme's footer file with its site footer swapped for the footer template.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string|null $name Footer template name.
	 */
	public function render_theme_footer( $name ) {
		$html     = $this->capture_theme_part( 'footer', $name );
		$template = $this->capture_template( $this->active_templates['footer'], 'footer' );

		if ( '' === trim( $html ) ) {
			echo $template; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			wp_footer();
			echo '</body></html>';
			return;
		}

		$swapped = $this->replace_element( $html, 'footer', 'site-footer|colophon', $template );

		// No site footer element found: add the template before the closing body tag.
		if ( null === $swapped ) {
			$position = stripos( $html, '</body>' );
			$swapped  = false === $position ? $template . $html : substr_replace( $html, $template, $position, 0 );
		}

		echo $swapped; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Load a theme header or footer file and return its output.
	 *
	 * Loading it once here keeps WordPress from requiring it again after the get_header/get_footer action.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @param string      $slug 'header' or 'footer'.
	 * @param string|null $name Template name.
	 * @return string
	 */
	private function capture_theme_part( $slug, $name ) {
		$templates   = '' === (string) $name ? [] : [ "{$slug}-{$name}.php" ];
		$templates[] = "{$slug}.php";

		ob_start();
		locate_template( $templates, true );
		return (string) ob_get_clean();
	}

	/**
	 * Render a template and return its output.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @param int    $template_id Template ID.
	 * @param string $type        Template type.
	 * @return string
	 */
	private function capture_template( $template_id, $type ) {
		ob_start();
		$this->render_elementor_content( $template_id, $type );
		return (string) ob_get_clean();
	}

	/**
	 * Replace an element, nested tags of the same name included, with other markup.
	 *
	 * The element carrying one of the hint classes or IDs is preferred, then the first element with that tag.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @param string $html        HTML to search.
	 * @param string $tag         Tag name.
	 * @param string $hints       Regex alternation of class or ID names, such as 'site-header|masthead'.
	 * @param string $replacement Replacement markup.
	 * @return string|null The new HTML, or null when no element was found.
	 */
	private function replace_element( $html, $tag, $hints, $replacement ) {
		if ( ! preg_match( '#<' . $tag . '\b[^>]*\b(?:' . $hints . ')\b[^>]*>#i', $html, $match, PREG_OFFSET_CAPTURE )
			&& ! preg_match( '#<' . $tag . '\b[^>]*>#i', $html, $match, PREG_OFFSET_CAPTURE ) ) {
			return null;
		}

		$start  = $match[0][1];
		$offset = $start;
		$depth  = 0;

		while ( preg_match( '#<(/?)' . $tag . '\b[^>]*>#i', $html, $found, PREG_OFFSET_CAPTURE, $offset ) ) {
			$depth += '/' === $found[1][0] ? -1 : 1;
			$offset = $found[0][1] + strlen( $found[0][0] );

			if ( 0 === $depth ) {
				return substr( $html, 0, $start ) . $replacement . substr( $html, $offset );
			}
		}

		return null;
	}

	/**
	 * Swap in the custom loader file.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $template Template path.
	 * @return string
	 */
	public function load_custom_template_file( $template ) {
		$loader_path = plugin_dir_path( __FILE__ ) . 'templates/archt-elementor-loader.php';
		if ( file_exists( $loader_path ) ) {
			return $loader_path;
		}
		return $template;
	}

	/**
	 * Get the active header template ID.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return int|null
	 */
	public function get_active_header_id() {
		return $this->active_templates['header'];
	}

	/**
	 * Get the active footer template ID.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return int|null
	 */
	public function get_active_footer_id() {
		return $this->active_templates['footer'];
	}

	/**
	 * Get the active content template ID.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return int|null
	 */
	public function get_active_content_id() {
		return $this->active_templates['content'];
	}

	/**
	 * Get the active content template's page layout.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string
	 */
	public function get_template_mode() {
		return $this->active_templates['mode'];
	}

	/**
	 * Get the active content template's document type.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string 'single', 'archive' or ''.
	 */
	public function get_active_content_type() {
		if ( ! $this->active_templates['content'] ) {
			return '';
		}

		$document = \Elementor\Plugin::$instance->documents->get( $this->active_templates['content'] );

		return $document ? $document->get_name() : '';
	}

	/**
	 * Whether a template applies to this request.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @param int $template_id Template ID.
	 * @return bool
	 */
	private function validate_template( $template_id ) {
		$document = \Elementor\Plugin::$instance->documents->get( $this->get_translated_template_id( $template_id ) );
		if ( ! $document ) {
			return false;
		}
		$settings = $document->get_settings_for_display();
		return ( $this->check_inclusion_conditions( $settings ) && ! $this->check_exclusion_conditions( $settings ) );
	}

	/**
	 * Print the popup CSS needed before archt-popup.css loads.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function output_critical_css() {
		if ( empty( $this->active_popup_ids ) ) {
			return;
		}
		echo '<style id="archt-critical-css">[data-elementor-type="popup"],[class^="popup-bg-"]{display:none;}</style>' . "\n";
	}

	/**
	 * Render the active popups.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function insert_popup() {
		if ( $this->is_elementor_edit_or_preview_mode() ) {
			return;
		}

		foreach ( $this->active_popup_ids as $popup_id ) {
			$this->render_elementor_content( $popup_id, 'popup' );
		}
	}

	/**
	 * Render a template, wrapped for its type.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param int    $template_id Template ID.
	 * @param string $type        Template type.
	 */
	public function render_elementor_content( $template_id, $type ) {
		$content = \Elementor\Plugin::instance()->frontend->get_builder_content_for_display( $template_id );
		if ( empty( $content ) ) {
			return;
		}

		switch ( $type ) {
			case 'header':
				do_action( 'archt/header/before' );
				echo '<header itemtype="https://schema.org/WPHeader" itemscope="itemscope" role="banner">' . $content . '</header>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				do_action( 'archt/header/after' );
				break;
			case 'footer':
				do_action( 'archt/footer/before' );
				echo '<footer itemtype="https://schema.org/WPFooter" itemscope="itemscope" role="contentinfo">' . $content . '</footer>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				do_action( 'archt/footer/after' );
				break;
			case 'popup':
				echo $content . '<div class="popup-bg-' . esc_attr( $template_id ) . '"></div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				break;
			case 'content':
				echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				break;
		}
	}

	/**
	 * Enqueue the active templates' styles.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function enqueue_styles() {
		if ( ! did_action( 'elementor/loaded' ) ) {
			return;
		}

		$kits_manager = \Elementor\Plugin::$instance->kits_manager;
		$kit_id       = $kits_manager->get_active_id();
		if ( $kit_id ) {
			$css_file = \Elementor\Core\Files\CSS\Post::create( $kit_id );
			if ( ! wp_style_is( 'elementor-kit-' . $kit_id ) ) {
				$css_file->enqueue();
			}
		}

		$template_ids = array_filter(
			[
				$this->active_templates['header'],
				$this->active_templates['footer'],
				$this->active_templates['content'],
			]
		);
		$template_ids = array_merge( $template_ids, $this->active_popup_ids );

		foreach ( $template_ids as $id ) {
			\Elementor\Core\Files\CSS\Post::create( $id )->enqueue();
		}

		if ( $template_ids ) {
			// Enqueue widget styles now, before wp_head() closes.
			\Elementor\Plugin::$instance->frontend->enqueue_styles();
		}

		if ( $this->active_popup_ids ) {
			wp_enqueue_style( 'archt-popup-style', ARCHT_Elementor::asset_url( 'assets/css/archt-popup.css' ), [], ARCHT_Elementor::VERSION );
			ARCHT_Elementor::enqueue_popup_script();

			foreach ( $this->active_popup_ids as $popup_id ) {
				$this->enqueue_animation_styles( $popup_id );
			}
		}
	}

	/**
	 * Announce the injected templates to Elementor's v4 atomic styles.
	 *
	 * Atomic_Styles_Manager only renders CSS for post IDs passed to elementor/post/render.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function register_atomic_template_styles() {
		if ( $this->is_elementor_edit_or_preview_mode() ) {
			return;
		}

		$template_ids = array_filter(
			array_merge(
				[ $this->active_templates['header'], $this->active_templates['footer'], $this->active_templates['content'] ],
				$this->active_popup_ids
			)
		);

		$queried_id = is_singular() ? (int) get_the_ID() : 0;

		foreach ( array_unique( $template_ids ) as $id ) {
			if ( (int) $id === $queried_id ) {
				continue;
			}
			do_action( 'elementor/post/render', $id );
		}
	}

	/**
	 * Enqueue a popup's entrance and exit animation keyframes.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @param int $popup_id Popup template ID.
	 */
	private function enqueue_animation_styles( $popup_id ) {
		$document = \Elementor\Plugin::$instance->documents->get( $popup_id );

		if ( ! $document ) {
			return;
		}

		$settings = $document->get_settings_for_display();

		foreach ( [ 'entrance_animation', 'exit_animation' ] as $setting ) {
			$animation = ! empty( $settings[ $setting ] ) ? $settings[ $setting ] : '';

			if ( ! $animation || 'none' === $animation ) {
				continue;
			}

			$handle = 'e-animation-' . $animation;

			if ( wp_style_is( $handle, 'registered' ) ) {
				wp_enqueue_style( $handle );
			}
		}
	}

	/**
	 * Allow paginated requests when a template supplies the loop.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param bool      $preempt Whether to short-circuit the 404 handler.
	 * @param \WP_Query $query   The query that triggered the check.
	 * @return bool
	 */
	public function allow_pagination_on_templates( $preempt, $query ) {
		if ( $preempt || ! $query->is_main_query() || $query->get( 'paged' ) < 2 ) {
			return $preempt;
		}

		if ( ! $query->is_singular() && ! $query->is_archive() && ! $query->is_home() && ! $query->is_search() ) {
			return $preempt;
		}

		return (bool) $this->find_matching_template( $this->get_elementor_templates() );
	}

	/**
	 * Whether Elementor is in edit or preview mode.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @return bool
	 */
	private function is_elementor_edit_or_preview_mode() {
		return \Elementor\Plugin::$instance->editor->is_edit_mode() || \Elementor\Plugin::$instance->preview->is_preview_mode();
	}

	/**
	 * Whether an exclusion condition matches this request.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @param array $settings Template settings.
	 * @return bool
	 */
	private function check_exclusion_conditions( $settings ) {
		if ( ! empty( $settings['display_conditions_exc'] ) ) {
			foreach ( $settings['display_conditions_exc'] as $item ) {
				if ( $this->check_conditions( $item, $settings ) ) {
					return true;
				}
			}
		}
		return false;
	}

	/**
	 * Whether an inclusion condition matches this request.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @param array $settings Template settings.
	 * @return bool
	 */
	private function check_inclusion_conditions( $settings ) {
		if ( $this->is_elementor_edit_or_preview_mode() ) {
			return true;
		}
		if ( empty( $settings['display_conditions_inc'] ) ) {
			return false;
		}
		foreach ( $settings['display_conditions_inc'] as $item ) {
			if ( $this->check_conditions( $item, $settings ) ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Whether a condition matches this request.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @param string $item     Condition key.
	 * @param array  $settings Template settings.
	 * @return bool
	 */
	private function check_conditions( $item, $settings ) {
		if (
			// WordPress doesn't count the blog page or search results as archives; Elementor Pro's All Archives does.
			( 'archive' === $item && ( is_archive() || is_home() || is_search() ) ) ||
			( '404' === $item && is_404() ) ||
			( 'search' === $item && is_search() ) ||
			( 'author' === $item && is_author() ) ||
			( 'date' === $item && is_date() ) ||
			( 'blog' === $item && is_home() ) ||
			( 'singular' === $item && is_singular() ) ||
			( 'home' === $item && is_front_page() ) ||
			( 'tax-category' === $item && is_category() ) ||
			( 'tax-tag' === $item && is_tag() ) ||
			( class_exists( 'WooCommerce' ) && (
				( 'shop' === $item && is_shop() ) ||
				( 'cart' === $item && is_cart() ) ||
				( 'account' === $item && is_account_page() ) ||
				( 'checkout' === $item && is_checkout() )
			) )
		) {
			return true;
		}
		if ( 0 === strpos( $item, 'cpt-' ) ) {
			return is_post_type_archive( substr( $item, 4 ) );
		}
		if ( 0 === strpos( $item, 'tax-' ) && 'tax-category' !== $item && 'tax-tag' !== $item ) {
			return is_tax( substr( $item, 4 ) );
		}
		if ( 0 === strpos( $item, 'single-' ) ) {
			return is_singular( substr( $item, 7 ) );
		}
		if ( 'all' === $item ) {
			return true;
		}
		if ( 'specific' === $item ) {

			if ( ! empty( $settings['selected_posts_exc'] ) ) {
				$selected_posts_exc = $settings['selected_posts_exc'];

				// Legacy comma-separated value.
				if ( is_string( $selected_posts_exc ) ) {
					$selected_posts_exc = explode( ',', $selected_posts_exc );
				}

				foreach ( $selected_posts_exc as $post_id ) {
					if ( get_the_ID() === $this->get_translated_id( $post_id, 'any' ) ) {
						return true;
					}
				}

				return false;
			}

			if ( ! empty( $settings['selected_posts_inc'] ) ) {
				$selected_posts_inc = $settings['selected_posts_inc'];

				// Legacy comma-separated value.
				if ( is_string( $selected_posts_inc ) ) {
					$selected_posts_inc = explode( ',', $selected_posts_inc );
				}

				foreach ( $selected_posts_inc as $post_id ) {
					if ( get_the_ID() === $this->get_translated_id( $post_id, 'any' ) ) {
						return true;
					}
				}

				return false;
			}
		}

		if ( 'specific_terms' === $item ) {
			$term = ( is_tax() || is_category() || is_tag() ) ? get_queried_object() : null;

			if ( ! empty( $settings['selected_terms_exc'] ) && $term ) {
				if ( in_array( $term->term_id, $this->get_translated_ids( $settings['selected_terms_exc'], $term->taxonomy ), true ) ) {
					return false;
				}
			}

			if ( ! empty( $settings['selected_terms_inc'] ) ) {
				return $term && in_array( $term->term_id, $this->get_translated_ids( $settings['selected_terms_inc'], $term->taxonomy ), true );
			}
		}
		return false;
	}

	/**
	 * Get an object's translation in the current language, or the object itself.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @param int    $id   Post or term ID.
	 * @param string $type Post type, 'any', or taxonomy.
	 * @return int
	 */
	private function get_translated_id( $id, $type ) {
		$id = (int) $id;

		if ( function_exists( 'pll_get_post' ) ) {
			$translated = taxonomy_exists( $type ) ? pll_get_term( $id ) : pll_get_post( $id );
			return $translated ? (int) $translated : $id;
		}

		return (int) apply_filters( 'wpml_object_id', $id, $type, true );
	}

	/**
	 * Get the translations of several objects in the current language.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @param array  $ids  Post or term IDs.
	 * @param string $type Post type, 'any', or taxonomy.
	 * @return int[]
	 */
	private function get_translated_ids( $ids, $type ) {
		return array_map(
			function ( $id ) use ( $type ) {
				return $this->get_translated_id( $id, $type );
			},
			(array) $ids
		);
	}

	/**
	 * Get the current language of the active translation plugin.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @return string
	 */
	private static function get_current_language() {
		if ( function_exists( 'pll_current_language' ) ) {
			return (string) pll_current_language();
		}

		return (string) apply_filters( 'wpml_current_language', '' );
	}

	/**
	 * Get the template query arguments.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @param string[] $terms elementor_library_type slugs.
	 * @return array
	 */
	private function get_args( $terms ) {
		$args = [
			'post_type'      => 'elementor_library',
			'post_status'    => 'publish',
			'tabs_group'     => 'library',
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'no_found_rows'  => true,
			'tax_query'      => [ // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
				[
					'taxonomy' => 'elementor_library_type',
					'field'    => 'slug',
					'terms'    => $terms,
				],
			],
		];
		if ( function_exists( 'pll_current_language' ) ) {
			$args['lang'] = pll_current_language();
		} elseif ( has_filter( 'wpml_current_language' ) ) {
			$args['suppress_filters'] = false;
		}
		return $args;
	}

	/**
	 * Get the header template IDs.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @return int[]
	 */
	private function get_header_templates() {
		return $this->fetch_templates( [ 'header' ] );
	}

	/**
	 * Get the single and archive template IDs.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @return int[]
	 */
	private function get_elementor_templates() {
		return $this->fetch_templates( [ 'archive', 'single' ] );
	}

	/**
	 * Get the footer template IDs.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @return int[]
	 */
	private function get_footer_templates() {
		return $this->fetch_templates( [ 'footer' ] );
	}

	/**
	 * Get the popup template IDs.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @return int[]
	 */
	private function get_popup_templates() {
		return $this->fetch_templates( [ 'popup' ] );
	}

	/**
	 * Get the template IDs of the given library types, cached.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @param string[] $terms elementor_library_type slugs.
	 * @return int[]
	 */
	private function fetch_templates( $terms ) {
		static $runtime_cache = [];

		$language      = self::get_current_language();
		$transient_key = 'archt_templates_' . self::get_cache_version() . '_' . implode( '_', $terms ) . ( $language ? '_' . $language : '' );

		if ( isset( $runtime_cache[ $transient_key ] ) ) {
			return $runtime_cache[ $transient_key ];
		}

		$template_ids = get_transient( $transient_key );

		if ( ! is_array( $template_ids ) ) {
			$template_ids = array_map( 'absint', (array) get_posts( $this->get_args( $terms ) ) );
			set_transient( $transient_key, $template_ids, WEEK_IN_SECONDS );
		}

		$runtime_cache[ $transient_key ] = $template_ids;

		return $template_ids;
	}

	/**
	 * Get the template cache version.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @return int
	 */
	private static function get_cache_version() {
		return (int) get_option( self::CACHE_VERSION_OPTION, 1 );
	}

	/**
	 * Invalidate every cached template list.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param int|\Elementor\Core\Base\Document|null $context Post ID or document, depending on the hook.
	 */
	public static function flush_template_cache( $context = null ) {
		$post_id = null;

		if ( is_numeric( $context ) ) {
			$post_id = (int) $context;
		} elseif ( $context instanceof \Elementor\Core\Base\Document ) {
			$post_id = $context->get_main_id();
		}

		if ( $post_id && 'elementor_library' !== get_post_type( $post_id ) ) {
			return;
		}

		update_option( self::CACHE_VERSION_OPTION, self::get_cache_version() + 1, false );
	}

	/**
	 * Get a template's translation in the current language.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @param int $template_id Template ID.
	 * @return int
	 */
	private function get_translated_template_id( $template_id ) {
		if ( is_admin() && ! wp_doing_ajax() ) {
			return $template_id;
		}
		return $this->get_translated_id( $template_id, 'elementor_library' );
	}
}
ARCHT_Theme_Builder::instance();
