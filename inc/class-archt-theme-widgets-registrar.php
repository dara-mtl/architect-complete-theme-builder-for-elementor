<?php
/**
 * Registers the Single and Archive widgets.
 *
 * @package ARCHT_Widgets
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Single and Archive widgets registrar.
 *
 * @since 1.0.0
 */
class ARCHT_Theme_Widgets_Registrar {

	/**
	 * Single widgets, file slug => class name.
	 *
	 * @var array
	 */
	private $single_widgets = [
		'single/class-archt-post-title'          => 'ARCHT_Post_Title_Widget',
		'single/class-archt-post-featured-image' => 'ARCHT_Post_Featured_Image_Widget',
		'single/class-archt-post-content'        => 'ARCHT_Post_Content_Widget',
		'single/class-archt-post-excerpt'        => 'ARCHT_Post_Excerpt_Widget',
		'single/class-archt-post-meta'           => 'ARCHT_Post_Meta_Widget',
		'single/class-archt-post-navigation'     => 'ARCHT_Post_Navigation_Widget',
	];

	/**
	 * Archive widgets, file slug => class name.
	 *
	 * @var array
	 */
	private $archive_widgets = [
		'archive/class-archt-archive-title'       => 'ARCHT_Archive_Title_Widget',
		'archive/class-archt-archive-description' => 'ARCHT_Archive_Description_Widget',
		'archive/class-archt-posts-loop'          => 'ARCHT_Posts_Loop_Widget',
		'archive/class-archt-archive-pagination'  => 'ARCHT_Archive_Pagination_Widget',
	];

	/**
	 * Widgets directory, no trailing slash.
	 *
	 * @var string
	 */
	private $widgets_dir;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function __construct() {
		$this->widgets_dir = ARCHT_PLUGIN_DIR . 'widgets';

		add_action( 'elementor/widgets/register', [ $this, 'register_widgets' ] );
	}

	/**
	 * Register the widgets. Both sets are always registered so saved templates render.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param \Elementor\Widgets_Manager $widgets_manager Widgets manager.
	 */
	public function register_widgets( $widgets_manager ) {
		require_once ARCHT_PLUGIN_DIR . 'inc/class-archt-widget-context.php';

		$document_type = ARCHT_Widget_Context::get_document_type();

		$is_editor = \Elementor\Plugin::$instance->editor->is_edit_mode();

		$load_single  = ! $is_editor || 'single' === $document_type;
		$load_archive = ! $is_editor || 'archive' === $document_type;

		if ( $load_single ) {
			$this->load_and_register( $widgets_manager, $this->single_widgets );
		}

		if ( $load_archive ) {
			$this->load_and_register( $widgets_manager, $this->archive_widgets );
		}
	}

	/**
	 * Load and register widgets.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @param \Elementor\Widgets_Manager $widgets_manager Widgets manager.
	 * @param array                      $widget_map      File slug => class name.
	 */
	private function load_and_register( $widgets_manager, array $widget_map ) {
		foreach ( $widget_map as $file_slug => $class_name ) {
			$file = $this->widgets_dir . '/' . $file_slug . '.php';

			if ( ! file_exists( $file ) ) {
				continue;
			}

			require_once $file;

			if ( ! class_exists( $class_name ) ) {
				continue;
			}

			$widgets_manager->register( new $class_name() );
		}
	}
}

new ARCHT_Theme_Widgets_Registrar();
