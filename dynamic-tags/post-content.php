<?php
/**
 * Post Content Dynamic Tag.
 *
 * @package ARCHT_Widgets
 * @since 1.0.0
 */

namespace ARCHT_Dynamic_Tag\Tags;

use Elementor\Controls_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Elementor custom dynamic tag.
 *
 * @since 1.0.0
 */
class Post_Content extends \Elementor\Core\DynamicTags\Tag {

	/**
	 * Get the name of the dynamic tag.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string The name of the dynamic tag.
	 */
	public function get_name() {
		return 'post-content-tag';
	}

	/**
	 * Get the title of the dynamic tag.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string The title of the dynamic tag.
	 */
	public function get_title() {
		return __( 'Post Content', 'architect-complete-theme-builder-for-elementor' );
	}

	/**
	 * Get the group that the dynamic tag belongs to.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string The group name.
	 */
	public function get_group() {
		return 'post';
	}

	/**
	 * Get the categories for the dynamic tag.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return array The categories that the dynamic tag belongs to.
	 */
	public function get_categories() {
		return [ \Elementor\Modules\DynamicTags\Module::TEXT_CATEGORY ];
	}

	/**
	 * Register the controls for the dynamic tag.
	 *
	 * This method defines the settings and options that can be customized in the Elementor editor.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function register_controls() {
		$this->add_control(
			'max_length',
			[
				'label' => esc_html__( 'Content Length', 'architect-complete-theme-builder-for-elementor' ),
				'type'  => Controls_Manager::NUMBER,
			]
		);
	}

	/**
	 * Render the post content.
	 *
	 * This method displays the content of the current post, potentially trimmed based on the `max_length` setting.
	 * If the page is in preview mode or in the admin area, a placeholder message is displayed instead of the content.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return void
	 */
	public function render() {
		$current_url = '';

		if ( isset( $_SERVER['HTTP_HOST'] ) && isset( $_SERVER['REQUEST_URI'] ) ) {
			$current_url = 'http://' . sanitize_text_field( wp_unslash( $_SERVER['HTTP_HOST'] ) ) . sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) );
		}

		$max_length = absint( $this->get_settings( 'max_length' ) );

		$is_elementor_editor = (
			strpos( $current_url, 'preview_nonce' ) !== false ||
			strpos( $current_url, 'elementor-preview' ) !== false ||
			is_admin()
		);

		if ( $is_elementor_editor ) {
			echo esc_html__( 'This is the post content. The full content will only display live.', 'architect-complete-theme-builder-for-elementor' );
			return;
		}

		$raw_content = get_the_content();

		if ( $max_length > 0 ) {
			$trimmed_content = wp_strip_all_tags( $raw_content );
			$post_content    = wp_trim_words( $trimmed_content, $max_length, '...' );

			echo wp_kses_post( $post_content );
		} else {
			$full_content = apply_filters( 'the_content', $raw_content );
			echo wp_kses_post( $full_content );
		}
	}
}
