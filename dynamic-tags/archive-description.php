<?php
/**
 * Repeater Archive Description Tag.
 *
 * @package ARCHT_Widgets
 * @since 1.0.0
 */

namespace ARCHT_Dynamic_Tag\Tags;

use Elementor\Core\DynamicTags\Tag;
use Elementor\Modules\DynamicTags\Module as TagsModule;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Archive description dynamic tag.
 */
class Archive_Description extends Tag {


	/**
	 * Get the name.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string
	 */
	public function get_name() {
		return 'archive-description';
	}

	/**
	 * Get the title.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string
	 */
	public function get_title() {
		return esc_html__( 'Archive Description', 'architect-complete-theme-builder-for-elementor' );
	}

	/**
	 * Get the group.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string
	 */
	public function get_group() {
		return 'archive';
	}

	/**
	 * Get the categories.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return array
	 */
	public function get_categories() {
		return [ TagsModule::TEXT_CATEGORY ];
	}

	/**
	 * Render the tag output.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function render() {
		echo wp_kses_post( get_the_archive_description() );
	}
}
