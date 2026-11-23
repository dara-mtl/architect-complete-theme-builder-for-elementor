<?php
/**
 * Element display conditions.
 *
 * @package ARCHT_Widgets
 * @since 1.0.0
 */

use Elementor\Repeater;
use Elementor\Controls_Manager;

use ARCHT\Inc\Classes\ARCHT_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Element display conditions.
 */
class ARCHT_Display_Conditions {
	/**
	 * Prop holding the display conditions of v4 atomic elements.
	 */
	const ATOMIC_PROP = 'archt-display-conditions';

	/**
	 * Fallback content of the v4 elements being hidden, keyed by element object ID.
	 *
	 * @var array
	 */
	private $hidden_atomic = [];

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function __construct() {
		add_action( 'elementor/element/container/section_layout/after_section_end', [ $this, 'add_controls_section' ] );
		add_action( 'elementor/element/common/_section_style/after_section_end', [ $this, 'add_controls_section' ] );
		add_filter( 'elementor/frontend/container/should_render', [ $this, 'render_content' ], 10, 2 );
		add_filter( 'elementor/frontend/widget/should_render', [ $this, 'render_content' ], 10, 2 );

		add_filter( 'elementor/atomic-widgets/props-schema', [ $this, 'add_atomic_prop' ], 5 );
		add_filter( 'elementor/atomic-widgets/controls', [ $this, 'add_atomic_control' ], 60, 2 );
		add_action( 'elementor/frontend/before_render', [ $this, 'before_atomic_render' ], 1 );
		add_action( 'elementor/frontend/after_render', [ $this, 'after_atomic_render' ], PHP_INT_MAX );
		add_action( 'elementor/editor/after_enqueue_scripts', [ $this, 'enqueue_atomic_editor_script' ] );
		add_filter( 'elementor/element/is_dynamic_content', [ $this, 'skip_element_cache' ], 10, 2 );
	}

	/**
	 * Keep elements with display conditions out of Elementor's element cache, so their conditions run on every request.
	 *
	 * @since 1.1.0
	 * @access public
	 *
	 * @param bool  $is_dynamic Whether the element content is dynamic.
	 * @param array $raw_data   Element raw data.
	 * @return bool
	 */
	public function skip_element_cache( $is_dynamic, $raw_data ) {
		$settings = isset( $raw_data['settings'] ) && is_array( $raw_data['settings'] ) ? $raw_data['settings'] : [];

		if ( ( isset( $settings['condition_enable'] ) && 'yes' === $settings['condition_enable'] ) || ! empty( $settings[ self::ATOMIC_PROP ]['value']['conditions']['value'] ) ) {
			return true;
		}

		return $is_dynamic;
	}

	/**
	 * Whether the current request renders inside the editor.
	 *
	 * @since 1.1.0
	 * @access private
	 *
	 * @return bool
	 */
	private function is_editor_render() {
		$plugin = \Elementor\Plugin::$instance;

		return $plugin->preview->is_preview_mode() || $plugin->editor->is_edit_mode();
	}

	/**
	 * Add the conditions prop to every v4 atomic element.
	 *
	 * @since 1.1.0
	 * @access public
	 *
	 * @param array $schema Props schema.
	 * @return array
	 */
	public function add_atomic_prop( $schema ) {
		if ( ! isset( $schema[ self::ATOMIC_PROP ] ) && class_exists( '\Elementor\Modules\AtomicWidgets\PropTypes\Base\Object_Prop_Type' ) ) {
			require_once ARCHT_PLUGIN_DIR . 'inc/classes/class-archt-atomic-conditions-prop-types.php';

			$prop_type = \ARCHT\Inc\Classes\ARCHT_Display_Conditions_Prop_Type::make();

			if ( class_exists( '\Elementor\Modules\Components\PropTypes\Overridable_Prop_Type' ) ) {
				$prop_type->meta( \Elementor\Modules\Components\PropTypes\Overridable_Prop_Type::ignore() );
			}

			$schema[ self::ATOMIC_PROP ] = $prop_type;
		}

		return $schema;
	}

	/**
	 * Add the conditions control to the Settings section of v4 atomic elements, replacing Elementor's Pro upsell.
	 *
	 * @since 1.1.0
	 * @access public
	 *
	 * @param array  $controls Element controls.
	 * @param object $element  Atomic element.
	 * @return array
	 */
	public function add_atomic_control( $controls, $element ) {
		if ( ! class_exists( '\Elementor\Modules\AtomicWidgets\Controls\Base\Atomic_Control_Base' ) || ! array_key_exists( self::ATOMIC_PROP, $element::get_props_schema() ) ) {
			return $controls;
		}

		require_once ARCHT_PLUGIN_DIR . 'inc/classes/class-archt-atomic-conditions-control.php';

		foreach ( $controls as $section ) {
			if ( ! $section instanceof \Elementor\Modules\AtomicWidgets\Controls\Section || 'settings' !== $section->get_id() ) {
				continue;
			}

			$items = array_filter(
				$section->get_items(),
				function ( $item ) {
					return ! $item instanceof \Elementor\Modules\Promotions\Controls\Atomic_Promotion_Control || 'display-conditions' !== $item->get_bind();
				}
			);

			$items[] = \ARCHT\Inc\Classes\ARCHT_Atomic_Conditions_Control::bind_to( self::ATOMIC_PROP )
				->set_label( __( 'Display Conditions', 'architect-complete-theme-builder-for-elementor' ) )
				->set_meta( [ 'topDivider' => true ] );

			$section->set_items( array_values( $items ) );
			break;
		}

		return $controls;
	}

	/**
	 * Start buffering a v4 atomic element whose display conditions fail, so its output can be dropped after render.
	 *
	 * @since 1.1.0
	 * @access public
	 *
	 * @param \Elementor\Element_Base $element Element.
	 */
	public function before_atomic_render( $element ) {
		if ( ! method_exists( $element, 'get_atomic_controls' ) || $this->is_editor_render() ) {
			return;
		}

		$result = $this->check_atomic_conditions( $element );

		if ( null === $result || $result['met'] ) {
			return;
		}

		$this->hidden_atomic[ spl_object_id( $element ) ] = $result['fallback'];

		ob_start();
	}

	/**
	 * Drop the buffered output of a hidden v4 atomic element, printing the fallback content in its place.
	 *
	 * @since 1.1.0
	 * @access public
	 *
	 * @param \Elementor\Element_Base $element Element.
	 */
	public function after_atomic_render( $element ) {
		$id = spl_object_id( $element );

		if ( ! array_key_exists( $id, $this->hidden_atomic ) ) {
			return;
		}

		$fallback = $this->hidden_atomic[ $id ];

		unset( $this->hidden_atomic[ $id ] );
		ob_end_clean();

		if ( '' !== $fallback ) {
			echo '<div class="archt-condition-fallback">' . do_shortcode( wpautop( wp_kses_post( $fallback ) ) ) . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Sanitized with wp_kses_post before shortcodes run.
		}
	}

	/**
	 * Evaluate the display conditions of a v4 atomic element.
	 *
	 * @since 1.1.0
	 * @access private
	 *
	 * @param \Elementor\Element_Base $element Element.
	 * @return array|null Null without conditions, otherwise whether they are met and the fallback content.
	 */
	private function check_atomic_conditions( $element ) {
		$settings  = $element->get_data( 'settings' );
		$raw       = isset( $settings[ self::ATOMIC_PROP ]['value'] ) && is_array( $settings[ self::ATOMIC_PROP ]['value'] ) ? $settings[ self::ATOMIC_PROP ]['value'] : [];
		$raw_items = isset( $raw['conditions']['value'] ) && is_array( $raw['conditions']['value'] ) ? $raw['conditions']['value'] : [];
		$prop_type = $raw_items ? $this->get_atomic_prop_type() : null;

		if ( ! $prop_type ) {
			return null;
		}

		$resolver  = \Elementor\Modules\AtomicWidgets\PropsResolver\Render_Props_Resolver::for_settings();
		$shape     = $prop_type->get_shape();
		$item_type = $shape['conditions']->get_item_type();
		$checks    = [];

		unset( $shape['conditions'] );

		$data = $resolver->resolve( $shape, $raw );

		foreach ( $raw_items as $raw_item ) {
			$item       = (array) $resolver->resolve_value( $raw_item, $item_type );
			$is_dynamic = isset( $raw_item['value']['key']['$$type'] ) && 'dynamic' === $raw_item['value']['key']['$$type'];
			$key        = isset( $item['key'] ) && is_scalar( $item['key'] ) ? trim( (string) $item['key'] ) : '';

			if ( $is_dynamic ) {
				$value1 = $key;
			} elseif ( '' === $key ) {
				continue;
			} elseif ( isset( $item['source'] ) && 'shortcode' === $item['source'] ) {
				$value1 = do_shortcode( $key );
			} else {
				$value1 = $this->get_field_value( $key );
			}

			$checks[] = [
				$value1,
				isset( $item['operator'] ) ? (string) $item['operator'] : 'not_empty',
				isset( $item['value'] ) && is_scalar( $item['value'] ) ? (string) $item['value'] : '',
			];
		}

		if ( empty( $checks ) ) {
			return null;
		}

		return [
			'met'      => $this->evaluate( $checks, ! isset( $data['display'] ) || 'any' !== $data['display'] ),
			'fallback' => ! empty( $data['fallback_enable'] ) && ! empty( $data['fallback'] ) && is_string( $data['fallback'] ) ? $data['fallback'] : '',
		];
	}

	/**
	 * The conditions prop type, with the dynamic tag support Elementor adds to element schemas.
	 *
	 * @since 1.1.0
	 * @access private
	 *
	 * @return \Elementor\Modules\AtomicWidgets\PropTypes\Base\Object_Prop_Type|null
	 */
	private function get_atomic_prop_type() {
		static $prop_type = null;

		if ( null !== $prop_type || ! class_exists( '\Elementor\Modules\AtomicWidgets\PropsResolver\Render_Props_Resolver' ) ) {
			return $prop_type;
		}

		require_once ARCHT_PLUGIN_DIR . 'inc/classes/class-archt-atomic-conditions-prop-types.php';

		$schema = [ self::ATOMIC_PROP => \ARCHT\Inc\Classes\ARCHT_Display_Conditions_Prop_Type::make() ];

		if ( class_exists( '\Elementor\Modules\AtomicWidgets\DynamicTags\Dynamic_Prop_Types_Mapping' ) ) {
			$schema = \Elementor\Modules\AtomicWidgets\DynamicTags\Dynamic_Prop_Types_Mapping::make()->get_extended_schema( $schema );
		}

		$prop_type = $schema[ self::ATOMIC_PROP ];

		return $prop_type;
	}

	/**
	 * Load the v4 conditions control in the editor when atomic elements are active.
	 *
	 * @since 1.1.0
	 * @access public
	 */
	public function enqueue_atomic_editor_script() {
		if ( ! \Elementor\Plugin::$instance->experiments->is_feature_active( 'e_atomic_elements' ) ) {
			return;
		}

		$deps = array_filter(
			[ 'react', 'wp-i18n', 'elementor-v2-ui', 'elementor-v2-icons', 'elementor-v2-editor-ui', 'elementor-v2-editor-props', 'elementor-v2-editor-controls', 'elementor-v2-editor-editing-panel' ],
			function ( $handle ) {
				return wp_script_is( $handle, 'registered' );
			}
		);

		wp_enqueue_script( 'archt-atomic-conditions', ARCHT_Elementor::asset_url( 'assets/js/backend/archt-atomic-conditions.js' ), array_values( $deps ), ARCHT_Elementor::VERSION, true );
		wp_set_script_translations( 'archt-atomic-conditions', 'architect-complete-theme-builder-for-elementor' );
	}

	/**
	 * Hide elements whose display conditions fail, printing the fallback content in their place.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param bool                    $should_render Whether the element renders.
	 * @param \Elementor\Element_Base $element       Element.
	 * @return bool
	 */
	public function render_content( $should_render, $element ) {
		if ( $this->is_editor_render() ) {
			return $should_render;
		}

		$settings = $element->get_settings_for_display();

		if ( empty( $settings['condition_enable'] ) || 'yes' !== $settings['condition_enable'] ) {
			return $should_render;
		}

		if ( empty( $settings['condition_list'] ) || ! is_array( $settings['condition_list'] ) ) {
			return $should_render;
		}

		if ( $this->conditions_met( $settings, $element->get_settings( 'condition_list' ) ) ) {
			return $should_render;
		}

		if ( isset( $settings['condition_fallback_enable'] ) && 'yes' === $settings['condition_fallback_enable'] && ! empty( $settings['condition_fallback'] ) ) {
			echo '<div class="elementor-element elementor-element-' . esc_attr( $element->get_id() ) . ' archt-condition-fallback">' . do_shortcode( wpautop( wp_kses_post( $settings['condition_fallback'] ) ) ) . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Sanitized with wp_kses_post before shortcodes run.
		}

		return false;
	}

	/**
	 * Whether an element's display conditions are met.
	 *
	 * @since 1.1.0
	 * @access private
	 *
	 * @param array $settings Element settings.
	 * @param array $raw_list Unparsed condition list, to tell dynamic tags from plain field keys.
	 * @return bool
	 */
	private function conditions_met( $settings, $raw_list ) {
		$checks = [];

		foreach ( $settings['condition_list'] as $index => $item ) {
			$source = isset( $item['condition_value'] ) ? $item['condition_value'] : 'dynamic_tag';

			if ( 'shortcode' === $source ) {
				$value1 = isset( $item['comparator_shortcode'] ) ? do_shortcode( $item['comparator_shortcode'] ) : '';
			} else {
				$value1 = isset( $item['comparator_dt'] ) ? $item['comparator_dt'] : '';

				if ( '' !== $value1 && empty( $raw_list[ $index ]['__dynamic__']['comparator_dt'] ) ) {
					$value1 = $this->get_field_value( trim( $value1 ) );
				}
			}

			$checks[] = [
				$value1,
				isset( $item['condition_operator'] ) ? $item['condition_operator'] : 'not_empty',
				isset( $item['condition_expected_value'] ) ? $item['condition_expected_value'] : '',
			];
		}

		return $this->evaluate( $checks, ! isset( $settings['condition_display'] ) || 'all' === $settings['condition_display'] );
	}

	/**
	 * Evaluate [ value, operator, expected ] checks, requiring all or any of them to match.
	 *
	 * @since 1.1.0
	 * @access private
	 *
	 * @param array $checks    Checks.
	 * @param bool  $match_all Whether every check must match.
	 * @return bool
	 */
	private function evaluate( $checks, $match_all ) {
		foreach ( $checks as $check ) {
			$matched = ARCHT_Helper::check_operator( $check[0], $check[1], $check[2] );

			if ( $match_all !== $matched ) {
				return $matched;
			}
		}

		return $match_all;
	}

	/**
	 * Read a custom field by key from the current post, or the queried term or author outside the loop.
	 *
	 * @since 1.1.0
	 * @access private
	 *
	 * @param string $key Meta key.
	 * @return string
	 */
	private function get_field_value( $key ) {
		$object = in_the_loop() ? null : get_queried_object();

		if ( $object instanceof \WP_Term ) {
			$value = get_term_meta( $object->term_id, $key, true );
		} elseif ( $object instanceof \WP_User ) {
			$value = get_user_meta( $object->ID, $key, true );
		} else {
			$post_id = get_the_ID();
			$value   = $post_id ? get_post_meta( $post_id, $key, true ) : '';
		}

		if ( is_array( $value ) ) {
			$value = implode( ', ', array_filter( $value, 'is_scalar' ) );
		}

		return is_scalar( $value ) ? (string) $value : '';
	}

	/**
	 * Add the Display Conditions section.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param \Elementor\Element_Base $element Element.
	 */
	public static function add_controls_section( $element ) {
		$element->start_controls_section(
			'conditions_section',
			[
				'label' => esc_html__( 'Display Conditions', 'architect-complete-theme-builder-for-elementor' ),
				'tab'   => Controls_Manager::TAB_ADVANCED,
			]
		);

		$element->add_control(
			'condition_enable',
			[
				'type'         => Controls_Manager::SWITCHER,
				'label'        => esc_html__( 'Enable Display Conditions', 'architect-complete-theme-builder-for-elementor' ),
				'label_on'     => esc_html__( 'Yes', 'architect-complete-theme-builder-for-elementor' ),
				'label_off'    => esc_html__( 'No', 'architect-complete-theme-builder-for-elementor' ),
				'return_value' => 'yes',
				'default'      => 'no',
				'prefix_class' => 'archt-condition-',
			]
		);

		$element->add_control(
			'condition_display',
			[
				'type'      => \Elementor\Controls_Manager::SELECT,
				'label'     => esc_html__( 'Display When', 'architect-complete-theme-builder-for-elementor' ),
				'default'   => 'all',
				'options'   => [
					'all' => esc_html__( 'All Conditions Are Met', 'architect-complete-theme-builder-for-elementor' ),
					'any' => esc_html__( 'Any Condition is Met', 'architect-complete-theme-builder-for-elementor' ),
				],
				'condition' => [
					'condition_enable' => 'yes',
				],
			]
		);

		$repeater = new Repeater();

		$repeater->start_controls_tabs( 'field_repeater' );

		$repeater->add_control(
			'condition_value',
			[
				'type'        => \Elementor\Controls_Manager::SELECT,
				'label_block' => true,
				'default'     => 'dynamic_tag',
				'options'     => [
					'dynamic_tag' => esc_html__( 'Custom Field', 'architect-complete-theme-builder-for-elementor' ),
					'shortcode'   => esc_html__( 'Shortcode', 'architect-complete-theme-builder-for-elementor' ),
				],
			]
		);

		$repeater->add_control(
			'comparator_dt',
			[
				'type'        => \Elementor\Controls_Manager::TEXT,
				'label_block' => true,
				'placeholder' => esc_html__( 'Field key or dynamic tag', 'architect-complete-theme-builder-for-elementor' ),
				'dynamic'     => [
					'active' => true,
				],
				'condition'   => [
					'condition_value' => 'dynamic_tag',
				],
			]
		);

		$repeater->add_control(
			'comparator_shortcode',
			[
				'type'        => \Elementor\Controls_Manager::TEXT,
				'label_block' => true,
				'placeholder' => '[shortcode attribute="value"]',
				'condition'   => [
					'condition_value' => 'shortcode',
				],
			]
		);

		$repeater->add_control(
			'condition_operator',
			[
				'type'        => \Elementor\Controls_Manager::SELECT,
				'label_block' => true,
				'default'     => 'empty',
				'options'     => [
					'=='          => esc_html__( 'Equal', 'architect-complete-theme-builder-for-elementor' ),
					'!='          => esc_html__( 'Not Equal', 'architect-complete-theme-builder-for-elementor' ),
					'<'           => esc_html__( 'Less Than', 'architect-complete-theme-builder-for-elementor' ),
					'>'           => esc_html__( 'Greater Than', 'architect-complete-theme-builder-for-elementor' ),
					'empty'       => esc_html__( 'Empty', 'architect-complete-theme-builder-for-elementor' ),
					'not_empty'   => esc_html__( 'Not Empty', 'architect-complete-theme-builder-for-elementor' ),
					'contain'     => esc_html__( 'Contains', 'architect-complete-theme-builder-for-elementor' ),
					'not_contain' => esc_html__( 'Doesn\'t Contain', 'architect-complete-theme-builder-for-elementor' ),
				],
			]
		);

		$repeater->add_control(
			'condition_expected_value',
			[
				'type'        => \Elementor\Controls_Manager::TEXTAREA,
				'label_block' => true,
				'placeholder' => esc_html__( 'Enter the string that needs to be returned in order for the condition to apply.', 'architect-complete-theme-builder-for-elementor' ),
				'condition'   => [
					'condition_operator!' => [ 'empty','not_empty' ],
				],
			]
		);

		$element->add_control(
			'condition_list',
			[
				'label'       => esc_html__( 'Conditions', 'architect-complete-theme-builder-for-elementor' ),
				'type'        => \Elementor\Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'title_field' => esc_html__( 'Condition', 'architect-complete-theme-builder-for-elementor' ),
				'default'     => [
					[
						'condition_value'    => 'dynamic_tag',
						'condition_operator' => 'not_empty',
					],
				],
				'condition'   => [
					'condition_enable' => 'yes',
				],
			]
		);

		$element->add_control(
			'condition_fallback_enable',
			[
				'type'         => Controls_Manager::SWITCHER,
				'label'        => esc_html__( 'Fallback Content', 'architect-complete-theme-builder-for-elementor' ),
				'description'  => esc_html__( 'Shown in place of this element when its conditions are not met.', 'architect-complete-theme-builder-for-elementor' ),
				'return_value' => 'yes',
				'separator'    => 'before',
				'condition'    => [
					'condition_enable' => 'yes',
				],
			]
		);

		$element->add_control(
			'condition_fallback',
			[
				'type'       => Controls_Manager::WYSIWYG,
				'show_label' => false,
				'dynamic'    => [
					'active' => true,
				],
				'condition'  => [
					'condition_enable'          => 'yes',
					'condition_fallback_enable' => 'yes',
				],
			]
		);

		$element->end_controls_section();
	}
}
new ARCHT_Display_Conditions();
