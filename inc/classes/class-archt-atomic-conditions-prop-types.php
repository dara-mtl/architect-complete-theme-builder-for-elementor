<?php
/**
 * Prop types holding the display conditions of Elementor v4 atomic elements.
 *
 * @package ARCHT_Widgets
 * @since 1.1.0
 */

namespace ARCHT\Inc\Classes;

use Elementor\Modules\AtomicWidgets\PropTypes\Base\Object_Prop_Type;
use Elementor\Modules\AtomicWidgets\PropTypes\Primitives\Boolean_Prop_Type;
use Elementor\Modules\AtomicWidgets\PropTypes\Primitives\String_Prop_Type;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

require_once __DIR__ . '/class-archt-condition-list-prop-type.php';

/**
 * Display conditions of an element: match mode, condition list and fallback content.
 */
class ARCHT_Display_Conditions_Prop_Type extends Object_Prop_Type {
	/**
	 * Prop type key.
	 *
	 * @since 1.1.0
	 * @return string
	 */
	public static function get_key(): string {
		return 'archt-display-conditions';
	}

	/**
	 * Shape.
	 *
	 * @since 1.1.0
	 * @return array
	 */
	protected function define_shape(): array {
		return [
			'display'         => String_Prop_Type::make()->enum( [ 'all', 'any' ] )->default( 'all' ),
			'conditions'      => ARCHT_Condition_List_Prop_Type::make(),
			'fallback_enable' => Boolean_Prop_Type::make()->default( false ),
			'fallback'        => String_Prop_Type::make(),
		];
	}
}
