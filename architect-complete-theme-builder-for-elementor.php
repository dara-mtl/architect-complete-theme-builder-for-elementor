<?php
/**
 * Plugin Name: Architect - Complete Theme Builder for Elementor
 * Requires Plugins: elementor
 * Description: Complete theme building solution for Elementor with headers, footers, popups, templates and display conditions.
 * Plugin URI: https://wpsmartwidgets.com/doc/architect-theme-builder-for-elementor/
 * Author: WP Smart Widgets
 * Author URI: https://wpsmartwidgets.com/
 * Documentation URI: https://wpsmartwidgets.com/doc/architect-theme-builder-for-elementor/
 * Version: 1.0.0-beta
 * Requires PHP: 7.4
 * Requires at least: 6.2
 * Tested up to: 7.1
 * Elementor tested up to: 4.3.2
 * Text Domain: architect-complete-theme-builder-for-elementor
 * Domain Path: /lang
 * License: GPL-3.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package ARCHT_Widgets
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

define( 'ARCHT_PLUGIN_FILE', __FILE__ );
define( 'ARCHT_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'ARCHT_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'ARCHT_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

// Add widget categories.
require_once ARCHT_PLUGIN_DIR . 'widget-categories.php';

/**
 * Main plugin class.
 *
 * @since 1.0.0
 */
final class ARCHT_Elementor {
	const VERSION                   = '1.0.0-beta';
	const MINIMUM_ELEMENTOR_VERSION = '3.0.0';
	const MINIMUM_PHP_VERSION       = '7.4';

	/**
	 * Single instance.
	 *
	 * @var ARCHT_Elementor|null
	 */
	private static $instance = null;

	/**
	 * Get the single instance.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return ARCHT_Elementor
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
		add_action( 'plugins_loaded', [ $this, 'on_plugins_loaded' ] );
	}

	/**
	 * Boot once all plugins are loaded.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function on_plugins_loaded() {
		if ( $this->is_compatible() ) {
			add_action( 'elementor/init', [ $this, 'init' ] );
		}
	}

	/**
	 * Whether Elementor Pro is active. The theme builder stands down when it is.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return bool
	 */
	public static function is_pro_active() {
		return defined( 'ELEMENTOR_PRO_VERSION' );
	}

	/**
	 * Check Elementor and PHP requirements.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return bool
	 */
	public function is_compatible() {
		if ( ! did_action( 'elementor/loaded' ) ) {
			add_action( 'admin_notices', [ $this, 'admin_notice_missing_main_plugin' ] );
			return false;
		}

		if ( ! version_compare( ELEMENTOR_VERSION, self::MINIMUM_ELEMENTOR_VERSION, '>=' ) ) {
			add_action( 'admin_notices', [ $this, 'admin_notice_minimum_elementor_version' ] );
			return false;
		}

		if ( version_compare( PHP_VERSION, self::MINIMUM_PHP_VERSION, '<' ) ) {
			add_action( 'admin_notices', [ $this, 'admin_notice_minimum_php_version' ] );
			return false;
		}

		return true;
	}

	/**
	 * Initialize the plugin.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function init() {
		add_action( 'elementor/widgets/register', [ $this, 'init_widgets' ] );
		add_action( 'elementor/frontend/after_enqueue_scripts', [ $this, 'widget_scripts' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'widget_styles' ], 5 );
		add_action( 'elementor/frontend/after_enqueue_styles', [ $this, 'widget_styles' ] );
		add_action( 'elementor/editor/before_enqueue_scripts', [ $this, 'backend_widget_scripts' ] );
		add_action( 'elementor/editor/v2/scripts/enqueue', [ $this, 'app_bar_scripts' ] );
		add_action( 'elementor/editor/before_enqueue_styles', [ $this, 'backend_widget_styles' ] );
		add_action( 'elementor/preview/enqueue_styles', [ $this, 'archt_doc_css' ] );
		add_filter( 'template_include', [ $this, 'handle_preview_endpoint' ], 999 );

		if ( self::is_pro_active() ) {
			add_action( 'admin_notices', [ $this, 'admin_notice_pro_active' ] );
		}

		require_once ARCHT_PLUGIN_DIR . 'inc/classes/class-archt-helper.php';
		require_once ARCHT_PLUGIN_DIR . 'inc/traits/trait-display-conditions.php';
		require_once ARCHT_PLUGIN_DIR . 'inc/classes/class-archt-theme-builder.php';
		require_once ARCHT_PLUGIN_DIR . 'inc/classes/class-archt-custom-library.php';
		require_once ARCHT_PLUGIN_DIR . 'inc/classes/class-archt-display-conditions.php';
		require_once ARCHT_PLUGIN_DIR . 'inc/classes/class-archt-dynamic-tag.php';
		require_once ARCHT_PLUGIN_DIR . 'inc/classes/class-archt-custom-css.php';
		require_once ARCHT_PLUGIN_DIR . 'inc/classes/class-archt-custom-attributes.php';
		require_once ARCHT_PLUGIN_DIR . 'inc/classes/class-archt-ajax.php';
		// Architect Theme Builder page, on hold: require_once ARCHT_PLUGIN_DIR . 'inc/classes/class-archt-site-parts.php';.
		require_once ARCHT_PLUGIN_DIR . 'inc/class-archt-theme-widgets-registrar.php';
	}

	/**
	 * Get an asset URL, pointing to its minified copy unless SCRIPT_DEBUG is on.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $path Asset path relative to the plugin root, such as 'assets/js/archt-menu.js'.
	 * @return string
	 */
	public static function asset_url( $path ) {
		$min = preg_replace( '/\.(js|css)$/', '.min.$1', $path );

		if ( ( ! defined( 'SCRIPT_DEBUG' ) || ! SCRIPT_DEBUG ) && file_exists( ARCHT_PLUGIN_DIR . $min ) ) {
			$path = $min;
		}

		return plugins_url( $path, ARCHT_PLUGIN_FILE );
	}

	/**
	 * Enqueue the frontend widget styles.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function widget_styles() {
		if ( ! wp_style_is( 'archt-widget-style', 'registered' ) && ! wp_style_is( 'archt-widget-style', 'enqueued' ) ) {
			wp_enqueue_style( 'archt-widget-style', self::asset_url( 'assets/css/archt-widget.css' ), [ 'elementor-frontend' ], self::VERSION );
		}

		if ( ! wp_style_is( 'archt-theme-widgets-style', 'registered' ) && ! wp_style_is( 'archt-theme-widgets-style', 'enqueued' ) ) {
			wp_enqueue_style( 'archt-theme-widgets-style', self::asset_url( 'assets/css/archt-theme-widgets.css' ), [ 'elementor-frontend' ], self::VERSION );
		}
	}

	/**
	 * Enqueue frontend scripts.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function widget_scripts() {
		wp_register_script( 'archt-nav-menu-script', self::asset_url( 'assets/js/archt-menu.js' ), [ 'jquery' ], self::VERSION, true );
	}

	/**
	 * Load the single preview template for ?archt_preview=1 requests, and the bare popup template in the editor.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $template The template path WordPress resolved.
	 * @return string The preview template path for preview requests, the original otherwise.
	 */
	public function handle_preview_endpoint( $template ) {
		if ( ! empty( $_GET['archt_preview'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return ARCHT_PLUGIN_DIR . 'inc/classes/templates/archt-single-preview.php';
		}

		$preview = \Elementor\Plugin::$instance->preview;

		if ( $preview->is_preview_mode() ) {
			$document = \Elementor\Plugin::$instance->documents->get( $preview->get_post_id() );

			if ( $document && 'popup' === $document->get_name() ) {
				return ARCHT_PLUGIN_DIR . 'inc/classes/templates/archt-popup-preview.php';
			}
		}

		return $template;
	}

	/**
	 * Enqueue backend styles.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function backend_widget_styles() {
		wp_enqueue_style( 'archt-backend-style', self::asset_url( 'assets/css/backend/archt-backend.css' ), [], self::VERSION );
	}

	/**
	 * Enqueue backend scripts.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function backend_widget_scripts() {
		$post_id       = isset( $_GET['post'] ) ? absint( $_GET['post'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$document_type = '';
		if ( $post_id && did_action( 'elementor/loaded' ) ) {
			$document = \Elementor\Plugin::$instance->documents->get( $post_id );
			if ( $document ) {
				$document_type = $document->get_name();
			}
		}

		wp_enqueue_script( 'archt-editor-script', self::asset_url( 'assets/js/backend/archt-editor.js' ), [ 'jquery' ], self::VERSION, true );
		wp_localize_script(
			'archt-editor-script',
			'archtEditor',
			[
				'preview'      => [
					'url'   => home_url( '/' ),
					'nonce' => wp_create_nonce( 'archt_single_preview' ),
				],
				'select2'      => [
					'url'   => admin_url( 'admin-ajax.php' ),
					'nonce' => wp_create_nonce( 'archt_search_related_items' ),
				],
				'documentType' => $document_type,
				'i18n'         => [
					'dialogTitle'   => esc_html__( 'No Display Location Set', 'architect-complete-theme-builder-for-elementor' ),
					'dialogMessage' => esc_html__( 'This template won\'t appear anywhere on your site until you set a Display Location. You can set it in the Display tab.', 'architect-complete-theme-builder-for-elementor' ),
					'btnSet'        => esc_html__( 'Set Display Location', 'architect-complete-theme-builder-for-elementor' ),
					'btnSave'       => esc_html__( 'Save Anyway', 'architect-complete-theme-builder-for-elementor' ),
				],
			]
		);
	}

	/**
	 * Enqueue the Publish dropdown item, before the v2 app bar starts.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function app_bar_scripts() {
		wp_enqueue_script( 'archt-app-bar-script', self::asset_url( 'assets/js/backend/archt-app-bar.js' ), [ 'elementor-v2-editor-app-bar', 'elementor-v2-editor-documents', 'elementor-v2-icons' ], self::VERSION, true );
		wp_localize_script( 'archt-app-bar-script', 'archtAppBar', [ 'title' => esc_html__( 'Display Location', 'architect-complete-theme-builder-for-elementor' ) ] );
	}

	/**
	 * Register widgets.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function init_widgets() {
		require_once ARCHT_PLUGIN_DIR . 'widgets/class-archt-navigation-menu.php';
		require_once ARCHT_PLUGIN_DIR . 'widgets/class-archt-menu-walker.php';

		$widgets_manager = \Elementor\Plugin::instance()->widgets_manager;
		$widgets_manager->register( new \ARCHT_Navigation_Menu_Widget() );
	}

	/**
	 * Enqueue the document styles in the editor preview.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function archt_doc_css() {
		$post_id  = \Elementor\Plugin::$instance->preview->get_post_id();
		$document = \Elementor\Plugin::$instance->documents->get( $post_id );

		if ( ! $document ) {
			return;
		}

		$type = $document->get_name();

		if ( ! in_array( $type, [ 'header', 'footer', 'popup' ], true ) ) {
			return;
		}

		wp_enqueue_style( 'archt-preview-style', self::asset_url( 'assets/css/backend/archt-preview.css' ), [], self::VERSION );

		if ( 'popup' === $type ) {
			self::enqueue_popup_script();
		}
	}

	/**
	 * Enqueue the popup script, shared by the frontend and the editor preview.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public static function enqueue_popup_script() {
		wp_enqueue_script( 'archt-popup-script', self::asset_url( 'assets/js/archt-popup.js' ), [ 'jquery', 'elementor-frontend' ], self::VERSION, true );

		wp_localize_script(
			'archt-popup-script',
			'archtPopup',
			[
				'closeLabel' => esc_html__( 'Close', 'architect-complete-theme-builder-for-elementor' ),
			]
		);
	}

	/**
	 * Notice: the theme builder defers to Elementor Pro.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function admin_notice_pro_active() {
		$screen = get_current_screen();

		if ( ! $screen || ! in_array( $screen->id, [ 'plugins', 'edit-elementor_library' ], true ) ) {
			return;
		}

		$message = sprintf(
			/* translators: 1: Plugin name 2: Elementor Pro */
			esc_html__( '"%1$s" detected "%2$s". The theme builder, display conditions and popups are handled by %2$s to avoid conflicts. Architect\'s widgets and dynamic tags remain available.', 'architect-complete-theme-builder-for-elementor' ),
			'<strong>' . esc_html__( 'Architect - Complete Theme Builder for Elementor', 'architect-complete-theme-builder-for-elementor' ) . '</strong>',
			'<strong>' . esc_html__( 'Elementor Pro', 'architect-complete-theme-builder-for-elementor' ) . '</strong>'
		);

		printf( '<div class="notice notice-info is-dismissible"><p>%1$s</p></div>', wp_kses_post( $message ) );
	}

	/**
	 * Notice: Elementor is missing.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function admin_notice_missing_main_plugin() {
		$message = sprintf(
			/* translators: 1: Plugin name 2: Elementor */
			esc_html__( '"%1$s" requires "%2$s" to be installed and activated.', 'architect-complete-theme-builder-for-elementor' ),
			'<strong>' . esc_html__( 'Architect - Complete Theme Builder for Elementor', 'architect-complete-theme-builder-for-elementor' ) . '</strong>',
			'<strong>' . esc_html__( 'Elementor', 'architect-complete-theme-builder-for-elementor' ) . '</strong>'
		);

		printf( '<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', wp_kses_post( $message ) );
	}

	/**
	 * Notice: Elementor version too old.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function admin_notice_minimum_elementor_version() {
		$message = sprintf(
			/* translators: 1: Plugin name 2: Elementor 3: Required Elementor version */
			esc_html__( '"%1$s" requires "%2$s" version %3$s or greater.', 'architect-complete-theme-builder-for-elementor' ),
			'<strong>' . esc_html__( 'Architect - Complete Theme Builder for Elementor', 'architect-complete-theme-builder-for-elementor' ) . '</strong>',
			'<strong>' . esc_html__( 'Elementor', 'architect-complete-theme-builder-for-elementor' ) . '</strong>',
			self::MINIMUM_ELEMENTOR_VERSION
		);

		printf( '<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', wp_kses_post( $message ) );
	}

	/**
	 * Notice: PHP version too old.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function admin_notice_minimum_php_version() {
		$message = sprintf(
			/* translators: 1: Plugin name 2: PHP 3: Required PHP version */
			esc_html__( '"%1$s" requires "%2$s" version %3$s or greater.', 'architect-complete-theme-builder-for-elementor' ),
			'<strong>' . esc_html__( 'Architect - Complete Theme Builder for Elementor', 'architect-complete-theme-builder-for-elementor' ) . '</strong>',
			'<strong>' . esc_html__( 'PHP', 'architect-complete-theme-builder-for-elementor' ) . '</strong>',
			self::MINIMUM_PHP_VERSION
		);

		printf( '<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', wp_kses_post( $message ) );
	}
}

ARCHT_Elementor::instance();
