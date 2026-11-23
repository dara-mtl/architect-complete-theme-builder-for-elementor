<?php
/**
 * Display conditions control for Elementor v4 atomic elements.
 *
 * @package ARCHT_Widgets
 * @since 1.1.0
 */

namespace ARCHT\Inc\Classes;

use Elementor\Modules\AtomicWidgets\Controls\Base\Atomic_Control_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Display conditions control for Elementor v4 atomic elements, rendered by assets/js/backend/archt-atomic-conditions.js.
 */
class ARCHT_Atomic_Conditions_Control extends Atomic_Control_Base {
	/**
	 * Control type.
	 *
	 * @since 1.1.0
	 * @return string
	 */
	public function get_type(): string {
		return 'archt-display-conditions';
	}

	/**
	 * Control props.
	 *
	 * @since 1.1.0
	 * @return array
	 */
	public function get_props(): array {
		return [];
	}
}
