<?php
/**
 * Repeater Archive Title Tag.
 *
 * @package ARCHT_Widgets
 * @since 1.0.0
 */

namespace ARCHT_Dynamic_Tag\Tags;

use Elementor\Controls_Manager;
use Elementor\Core\DynamicTags\Tag;
use Elementor\Modules\DynamicTags\Module as TagsModule;
use ARCHT\Inc\Classes\ARCHT_Helper;


if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Archive title dynamic tag.
 */
class Archive_Title extends Tag {

	/**
	 * Get the name.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string
	 */
	public function get_name() {
		return 'archive-title';
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
		return esc_html__( 'Archive Title', 'architect-complete-theme-builder-for-elementor' );
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
		$include_context = 'yes' === $this->get_settings( 'include_context' );

		$title = ARCHT_Helper::archt_get_page_title( $include_context );

		echo wp_kses_post( $title );
	}

	/**
	 * Register the controls.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function register_controls() {
		$this->add_control(
			'include_context',
			[
				'label'   => esc_html__( 'Include Context', 'architect-complete-theme-builder-for-elementor' ),
				'type'    => Controls_Manager::SWITCHER,
				'default' => 'yes',
			]
		);
	}
}
