<?php
/**
 * Custom Elementor Widget Category Registration.
 *
 * @package ARCHT_Widgets
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Adds the Architect widget category for Elementor.
 *
 * @param \Elementor\Elements_Manager $elements_manager The elements manager instance.
 */
function archt_categories( $elements_manager ) {

	$elements_manager->add_category(
		'archt',
		[
			'title' => esc_html__( 'Architect', 'architect-complete-theme-builder-for-elementor' ),
			'icon'  => 'eicon-theme-builder',
		]
	);
}

add_action( 'elementor/elements/categories_registered', 'archt_categories' );
