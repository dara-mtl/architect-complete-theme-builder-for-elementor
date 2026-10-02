<?php
/**
 * Shortcode Dynamic Tag.
 *
 * @package ARCHT_Widgets
 * @since 1.0.0
 */

namespace ARCHT_Dynamic_Tag\Tags;

use Elementor\Controls_Manager;
use Elementor\Core\DynamicTags\Tag;
use Elementor\Modules\DynamicTags\Module as TagsModule;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Dynamic tag for retrieving a shortcode content.
 *
 * @since 1.0.0
 */
class Shortcode extends \Elementor\Core\DynamicTags\Tag {

	/**
	 * Get the tag name.
	 *
	 * This method returns a unique identifier for the dynamic tag.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string The tag name.
	 */
	public function get_name() {
		return 'shortcode-tag';
	}

	/**
	 * Get the title of the dynamic tag.
	 *
	 * This method returns the title of the tag as shown in the Elementor interface.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string The title of the dynamic tag.
	 */
	public function get_title() {
		return __( 'Shortcode', 'architect-complete-theme-builder-for-elementor' );
	}

	/**
	 * Get the group of the dynamic tag.
	 *
	 * This method determines the group in which the dynamic tag will appear in the Elementor interface.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string The group name.
	 */
	public function get_group() {
		return 'bpfwe-dynamic-tags';
	}

	/**
	 * Get the categories of the dynamic tag.
	 *
	 * This method returns an array of categories the tag belongs to, allowing it to be grouped
	 * with other similar tags.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return array The categories of the dynamic tag.
	 */
	public function get_categories() {
		return [
			TagsModule::TEXT_CATEGORY,
			TagsModule::URL_CATEGORY,
			TagsModule::POST_META_CATEGORY,
			TagsModule::GALLERY_CATEGORY,
			TagsModule::IMAGE_CATEGORY,
			TagsModule::MEDIA_CATEGORY,
			TagsModule::NUMBER_CATEGORY,
		];
	}

	/**
	 * Register controls for the dynamic tag.
	 *
	 * This method registers the control to allow users to input a shortcode within the Elementor editor.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @return void
	 */
	protected function register_controls() {
		$this->add_control(
			'shortcode',
			[
				'label'       => esc_html__( 'Shortcode', 'architect-complete-theme-builder-for-elementor' ),
				'type'        => Controls_Manager::TEXTAREA,
				'placeholder' => esc_html__( '[your-shortcode]', 'architect-complete-theme-builder-for-elementor' ),
			]
		);
	}

	/**
	 * Render the dynamic tag.
	 *
	 * This method processes and renders the shortcode. It executes the shortcode using `do_shortcode()`
	 * and safely outputs the result with `wp_kses_post()`.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return void
	 */
	public function render() {
		$settings = $this->get_settings();

		if ( empty( $settings['shortcode'] ) ) {
			return;
		}

		$shortcode_string = $settings['shortcode'];
		$value            = do_shortcode( $shortcode_string );

		echo wp_kses_post( $value );
	}
}
