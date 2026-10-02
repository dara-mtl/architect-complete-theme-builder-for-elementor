<?php
/**
 * Template document types.
 *
 * @package ARCHT_Widgets
 * @since 1.0.0
 */

namespace ARCHT_Library;

use Elementor\Core\Base\Module as BaseModule;
use Elementor\Modules\Library\Documents;
use Elementor\Plugin;
use Elementor\Core\Documents_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( \ARCHT_Elementor::is_pro_active() ) {
	return;
}

require_once 'documents/header.php';
require_once 'documents/footer.php';
require_once 'documents/archive.php';
require_once 'documents/single.php';
require_once 'documents/popup.php';

/**
 * Registers the Architect template document types.
 *
 * @since 1.0.0
 */
class ARCHT_Custom_Library {

	/**
	 * Get module name.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string
	 */
	public function get_name() {
		return 'archt_library';
	}

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function __construct() {
		add_action(
			'elementor/documents/register',
			function ( Documents_Manager $documents_manager ) {
				$documents_manager
				->register_document_type( 'header', \ARCHT_Library\Custom_Documents\Header::get_class_full_name() )
				->register_document_type( 'footer', \ARCHT_Library\Custom_Documents\Footer::get_class_full_name() )
				->register_document_type( 'archive', \ARCHT_Library\Custom_Documents\Archive::get_class_full_name() )
				->register_document_type( 'single', \ARCHT_Library\Custom_Documents\Single::get_class_full_name() )
				->register_document_type( 'popup', \ARCHT_Library\Custom_Documents\Popup::get_class_full_name() );
			}
		);

		add_filter( 'manage_elementor_library_posts_columns', [ $this, 'add_location_column' ] );
		add_action( 'manage_elementor_library_posts_custom_column', [ $this, 'render_location_column' ], 10, 2 );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_list_styles' ] );
	}

	/**
	 * Add the Display Location column to the saved templates list.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param array $columns List table columns.
	 * @return array
	 */
	public function add_location_column( $columns ) {
		$column   = [ 'archt_location' => esc_html__( 'Display Location', 'architect-complete-theme-builder-for-elementor' ) ];
		$position = array_search( 'elementor_library_type', array_keys( $columns ), true );
		$position = false === $position ? count( $columns ) - 1 : $position + 1;

		return array_slice( $columns, 0, $position, true ) + $column + array_slice( $columns, $position, null, true );
	}

	/**
	 * Render the Display Location column for Architect templates.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $column  Column key.
	 * @param int    $post_id Template ID.
	 */
	public function render_location_column( $column, $post_id ) {
		if ( 'archt_location' !== $column || ! in_array( get_post_meta( $post_id, '_elementor_template_type', true ), [ 'header', 'footer', 'single', 'archive', 'popup' ], true ) ) {
			return;
		}

		$settings = (array) get_post_meta( $post_id, '_elementor_page_settings', true );
		$include  = \ARCHT\Inc\Classes\ARCHT_Helper::get_display_conditions_summary( $settings, 'inc' );
		$exclude  = \ARCHT\Inc\Classes\ARCHT_Helper::get_display_conditions_summary( $settings, 'exc' );

		if ( ! $include ) {
			echo '<span class="archt-muted">' . esc_html__( 'Not set', 'architect-complete-theme-builder-for-elementor' ) . '</span>';
			return;
		}

		echo esc_html( $include );

		if ( $exclude ) {
			/* translators: %s: Excluded display locations. */
			echo '<br><span class="archt-muted">' . esc_html( sprintf( __( 'Except: %s', 'architect-complete-theme-builder-for-elementor' ), $exclude ) ) . '</span>';
		}
	}

	/**
	 * Enqueue the saved templates list styles.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $hook Current admin page hook.
	 */
	public function enqueue_list_styles( $hook ) {
		if ( 'edit.php' === $hook && 'elementor_library' === get_current_screen()->post_type ) {
			wp_enqueue_style( 'archt-backend-style', \ARCHT_Elementor::asset_url( 'assets/css/backend/archt-backend.css' ), [], \ARCHT_Elementor::VERSION );
		}
	}
}
new ARCHT_Custom_Library();
