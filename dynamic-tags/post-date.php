<?php
/**
 * Post Date Dynamic Tag.
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
class Post_Date extends \Elementor\Core\DynamicTags\Tag {

	/**
	 * Get the name of the dynamic tag.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string The name of the dynamic tag.
	 */
	public function get_name() {
		return 'post-date-tag';
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
		return __( 'Post Date', 'architect-complete-theme-builder-for-elementor' );
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
	 * Render the post date.
	 *
	 * This method retrieves and displays the publication date of the current post.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return void
	 */
	public function render() {
		echo wp_kses_post( get_the_date() );
	}
}
