<?php
/**
 * Prop type holding the list of display conditions of an Elementor v4 atomic element.
 *
 * @package ARCHT_Widgets
 * @since 1.1.0
 */

namespace ARCHT\Inc\Classes;

use Elementor\Modules\AtomicWidgets\PropTypes\Base\Array_Prop_Type;
use Elementor\Modules\AtomicWidgets\PropTypes\Contracts\Prop_Type;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

require_once __DIR__ . '/class-archt-condition-prop-type.php';

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
