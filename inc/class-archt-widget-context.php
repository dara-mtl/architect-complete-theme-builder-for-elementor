<?php
/**
 * Resolves the Elementor document type for widget registration.
 *
 * @package ARCHT_Widgets
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Widget context helper.
 *
 * @since 1.0.0
 */
class ARCHT_Widget_Context {

	/**
	 * Cached document type string.
	 *
	 * @var string|null
	 */
	private static $document_type = null;

	/**
	 * Get the document type being edited or previewed.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string Document type, or '' when there is none.
	 */
	public static function get_document_type() {
		if ( null !== self::$document_type ) {
			return self::$document_type;
		}

		self::$document_type = '';

		if ( ! did_action( 'elementor/loaded' ) ) {
			return self::$document_type;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$post_id = isset( $_GET['post'] ) ? absint( $_GET['post'] ) : 0;

		if ( ! $post_id && isset( $_POST['editor_post_id'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			$post_id = absint( $_POST['editor_post_id'] ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
		}

		if ( ! $post_id && isset( $_GET['elementor-preview'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$post_id = absint( $_GET['elementor-preview'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		}

		// Nonce already verified by archt-single-preview.php.
		if ( ! $post_id && isset( $_GET['archt_preview'], $_GET['archt_template_id'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$post_id = absint( $_GET['archt_template_id'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		}

		if ( $post_id ) {
			$document = \Elementor\Plugin::$instance->documents->get( $post_id );
			if ( $document ) {
				self::$document_type = $document->get_name();
			}
		}

		return self::$document_type;
	}
}
