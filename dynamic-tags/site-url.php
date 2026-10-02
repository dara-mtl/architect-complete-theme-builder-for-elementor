<?php
/**
 * Site URL Tag.
 *
 * @package ARCHT_Widgets
 * @since 1.0.0
 */

namespace ARCHT_Dynamic_Tag\Tags;

use Elementor\Core\DynamicTags\Data_Tag;
use Elementor\Modules\DynamicTags\Module as TagsModule;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Site URL dynamic tag.
 */
class Site_URL extends Data_Tag {


	/**
	 * Get the name.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string
	 */
	public function get_name() {
		return 'site-url';
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
		return esc_html__( 'Site URL', 'architect-complete-theme-builder-for-elementor' );
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
		return 'site';
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
	 * Get the tag value.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param array $options Tag options.
	 * @return mixed
	 */
	public function get_value( array $options = [] ) {
		return esc_url( home_url() );
	}
}
