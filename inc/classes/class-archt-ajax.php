<?php
/**
 * AJAX handlers.
 *
 * @package ARCHT_Widgets
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * AJAX handlers.
 */
class ARCHT_Ajax {

	/**
	 * Search posts and terms for the Select2 display condition fields.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function archt_search_related_items() {
		$nonce = isset( $_GET['nonce'] ) ? sanitize_text_field( wp_unslash( $_GET['nonce'] ) ) : '';
		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error( [ 'message' => 'Access Denied' ], 403 );
		}

		if ( ! $nonce || ! wp_verify_nonce( $nonce, 'archt_search_related_items' ) ) {
			wp_send_json_error( [ 'message' => 'Access Denied' ], 403 );
		}

		$search   = isset( $_GET['q'] ) ? sanitize_text_field( wp_unslash( $_GET['q'] ) ) : '';
		$mode     = isset( $_GET['mode'] ) ? sanitize_text_field( wp_unslash( $_GET['mode'] ) ) : 'post';
		$page     = isset( $_GET['page'] ) ? absint( $_GET['page'] ) : 1;
		$per_page = 6;
		$results  = [];

		$post_type = ! empty( $_GET['post_type'] ) ? sanitize_text_field( wp_unslash( $_GET['post_type'] ) ) : 'any';
		if ( 'post' === $mode ) {
			$post_query = new WP_Query(
				[
					's'              => $search,
					'post_type'      => $post_type,
					'post_status'    => 'publish',
					'posts_per_page' => $per_page,
					'paged'          => $page,
					'no_found_rows'  => true,
				]
			);

			foreach ( $post_query->posts as $post ) {
				$results[] = [
					'id'   => $post->ID,
					'text' => $post->post_title . ' (' . $post->post_type . ')',
				];
			}
		} else {
			$taxonomies = get_taxonomies(
				[
					'public' => true,
				],
				'names'
			);

			$args = [
				'taxonomy'   => $taxonomies,
				'hide_empty' => false,
				'number'     => $per_page,
				'search'     => $search,
			];

			$terms = get_terms( $args );

			if ( ! is_wp_error( $terms ) ) {
				foreach ( $terms as $term ) {
					$results[] = [
						'id'   => $term->term_id,
						'text' => $term->name . ' (' . $term->taxonomy . ')',
					];
				}
			}
		}
		wp_send_json_success( $results );
	}

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function __construct() {
		add_action( 'wp_ajax_archt_search_related_items', [ $this, 'archt_search_related_items' ] );
	}
}
new ARCHT_Ajax();
