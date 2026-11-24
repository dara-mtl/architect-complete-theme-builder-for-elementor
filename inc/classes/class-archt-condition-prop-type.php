<?php
/**
 * Prop type holding one display condition of an Elementor v4 atomic element.
 *
 * @package ARCHT_Widgets
 * @since 1.1.0
 */

namespace ARCHT\Inc\Classes;

use Elementor\Modules\AtomicWidgets\PropTypes\Base\Object_Prop_Type;
use Elementor\Modules\AtomicWidgets\PropTypes\Primitives\String_Prop_Type;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
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
