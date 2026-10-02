<?php
/**
 * Site Logo Tag.
 *
 * @package ARCHT_Widgets
 * @since 1.0.0
 */

namespace ARCHT_Dynamic_Tag\Tags;

use Elementor\Core\DynamicTags\Data_Tag;
use Elementor\Utils;
use Elementor\Modules\DynamicTags\Module as TagsModule;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Site logo dynamic tag.
 */
class Site_Logo extends Data_Tag {

	/**
	 * Get the name.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string
	 */
	public function get_name() {
		return 'site-logo';
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
		return esc_html__( 'Site Logo', 'architect-complete-theme-builder-for-elementor' );
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
		return [ TagsModule::IMAGE_CATEGORY ];
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
		$custom_logo_id = get_theme_mod( 'custom_logo' );

		if ( $custom_logo_id ) {
			$url = wp_get_attachment_image_src( $custom_logo_id, 'full' )[0];
		} else {
			$url = Utils::get_placeholder_image_src();
		}

		return [
			'id'  => $custom_logo_id,
			'url' => $url,
		];
	}
}
