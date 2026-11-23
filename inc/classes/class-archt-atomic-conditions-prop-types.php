<?php
/**
 * Prop types holding the display conditions of Elementor v4 atomic elements.
 *
 * @package ARCHT_Widgets
 * @since 1.1.0
 */

namespace ARCHT\Inc\Classes;

use Elementor\Modules\AtomicWidgets\PropTypes\Base\Array_Prop_Type;
use Elementor\Modules\AtomicWidgets\PropTypes\Base\Object_Prop_Type;
use Elementor\Modules\AtomicWidgets\PropTypes\Contracts\Prop_Type;
use Elementor\Modules\AtomicWidgets\PropTypes\Primitives\Boolean_Prop_Type;
use Elementor\Modules\AtomicWidgets\PropTypes\Primitives\String_Prop_Type;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

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

/**
 * List of display conditions.
 */
class ARCHT_Condition_List_Prop_Type extends Array_Prop_Type {
	/**
	 * Prop type key.
	 *
	 * @since 1.1.0
	 * @return string
	 */
	public static function get_key(): string {
		return 'archt-condition-list';
	}

	/**
	 * Item type.
	 *
	 * @since 1.1.0
	 * @return Prop_Type
	 */
	protected function define_item_type(): Prop_Type {
		return ARCHT_Condition_Prop_Type::make();
	}
}

/**
 * One display condition. The key is a field key, a shortcode, or a dynamic tag.
 */
class ARCHT_Condition_Prop_Type extends Object_Prop_Type {
	/**
	 * Prop type key.
	 *
	 * @since 1.1.0
	 * @return string
	 */
	public static function get_key(): string {
		return 'archt-condition';
	}

	/**
	 * Shape.
	 *
	 * @since 1.1.0
	 * @return array
	 */
	protected function define_shape(): array {
		return [
			'source'   => String_Prop_Type::make()->enum( [ 'field', 'shortcode' ] )->default( 'field' ),
			'key'      => String_Prop_Type::make(),
			'operator' => String_Prop_Type::make()->enum( [ '==', '!=', '<', '>', 'empty', 'not_empty', 'contain', 'not_contain' ] )->default( 'not_empty' ),
			'value'    => String_Prop_Type::make(),
		];
	}
}
