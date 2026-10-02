<?php
/**
 * Repeater Archive URL Tag.
 *
 * @package ARCHT_Widgets
 * @since 1.0.0
 */

namespace ARCHT_Dynamic_Tag\Tags;

use Elementor\Core\DynamicTags\Data_Tag;
use Elementor\Modules\DynamicTags\Module as TagsModule;
use ARCHT_Dynamic_Tag\Inc\Classes\ARCHT_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Archive URL dynamic tag.
 */
class Archive_URL extends Data_Tag {


	/**
	 * Get the name.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string
	 */
	public function get_name() {
		return 'archive-url';
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
		return [ TagsModule::URL_CATEGORY ];
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
		return esc_html__( 'Archive URL', 'architect-complete-theme-builder-for-elementor' );
	}

	/**
	 * Get the panel template.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string
	 */
	public function get_panel_template() {
		return ' ({{ url }})';
	}

	/**
	 * Get the tag value.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param array $options Tag options.
	 * @return mixed
	 */
	public function get_value( array $options = [] ) {
		return ARCHT_Helper::archt_get_the_archive_url();
	}
}
