<?php
/**
 * MCP abilities for theme templates, display locations and classic elements.
 *
 * @package ARCHT_Widgets
 * @since 1.1.0
 */

use ARCHT\Inc\Classes\ARCHT_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Registers Architect's abilities with the WordPress Abilities API and adds them to the Elementor MCP server.
 */
class ARCHT_MCP {

	const PREFIX = 'architect/';

	const TEMPLATE_TYPES = [ 'header', 'footer', 'single', 'archive', 'popup' ];

	const MAX_OPERATIONS = 50;

	const SKIPPED_CONTROL_TYPES = [ 'section', 'tab', 'tabs', 'heading', 'divider', 'raw_html', 'notice', 'alert', 'button', 'deprecated_notice', 'hidden' ];

	const VALUE_FORMATS = [
		'slider'           => '{"unit":"px","size":24}',
		'dimensions'       => '{"unit":"px","top":"10","right":"10","bottom":"10","left":"10","isLinked":true}',
		'gaps'             => '{"column":"20","row":"20","unit":"px","isLinked":true}',
		'color'            => '"#1a1a1a" or "rgba(0,0,0,0.5)"; a global color goes in __globals__ as "globals/colors?id=primary"',
		'media'            => '{"id":123,"url":"https://example.com/image.jpg"}',
		'gallery'          => '[{"id":123,"url":"https://example.com/image.jpg"}]',
		'url'              => '{"url":"https://example.com","is_external":"on","nofollow":""}',
		'icons'            => '{"value":"fas fa-star","library":"fa-solid"}',
		'switcher'         => 'the return value (usually "yes") to turn on, "" to turn off',
		'choose'           => 'one of the option keys',
		'select'           => 'one of the option keys',
		'select2'          => 'an option key, or an array of keys when multiple',
		'popover_toggle'   => '"custom" to turn on the group (typography, text shadow...), "" to reset it',
		'font'             => 'font family name, such as "Inter"',
		'box_shadow'       => '{"horizontal":0,"vertical":4,"blur":12,"spread":0,"color":"rgba(0,0,0,0.15)"}',
		'text_shadow'      => '{"horizontal":0,"vertical":1,"blur":2,"color":"rgba(0,0,0,0.3)"}',
		'image_dimensions' => '{"width":"300","height":"200"}',
		'repeater'         => 'array of objects keyed by the listed fields; missing "_id" values are generated',
		'number'           => 'number',
		'date_time'        => '"YYYY-MM-DD HH:MM"',
		'wysiwyg'          => 'HTML string',
	];

	/**
	 * Constructor.
	 *
	 * @since 1.1.0
	 * @access public
	 */
	public function __construct() {
		if ( ! function_exists( 'wp_register_ability' ) ) {
			return;
		}

		add_action( 'wp_abilities_api_categories_init', [ $this, 'register_category' ] );
		add_action( 'wp_abilities_api_init', [ $this, 'register_abilities' ] );
		add_filter( 'elementor/mcp/server/tools', [ $this, 'add_server_tools' ] );
	}

	/**
	 * Register the Architect ability category.
	 *
	 * @since 1.1.0
	 * @access public
	 */
	public function register_category() {
		if ( ! function_exists( 'wp_register_ability_category' ) ) {
			return;
		}

		wp_register_ability_category(
			'architect',
			[
				'label'       => esc_html__( 'Architect', 'architect-complete-theme-builder-for-elementor' ),
				'description' => esc_html__( 'Architect theme templates, display locations and Elementor elements.', 'architect-complete-theme-builder-for-elementor' ),
			]
		);
	}

	/**
	 * Register the abilities.
	 *
	 * @since 1.1.0
	 * @access public
	 */
	public function register_abilities() {
		foreach ( $this->get_abilities() as $name => $ability ) {
			wp_register_ability( $name, $ability );
		}
	}

	/**
	 * Add the abilities to the Elementor MCP server.
	 *
	 * @since 1.1.0
	 * @access public
	 *
	 * @param array $tools Ability names exposed by the Elementor MCP server.
	 * @return array
	 */
	public function add_server_tools( $tools ) {
		return array_values( array_unique( array_merge( (array) $tools, array_keys( $this->get_abilities() ) ) ) );
	}

	/**
	 * Get the ability definitions, keyed by name.
	 *
	 * @since 1.1.0
	 * @access private
	 *
	 * @return array
	 */
	private function get_abilities() {
		$abilities = [];
		$read      = [
			'readonly'    => true,
			'destructive' => false,
			'idempotent'  => true,
		];
		$write     = [
			'readonly'    => false,
			'destructive' => false,
			'idempotent'  => false,
		];

		// Elementor Pro registers its own theme builder; Architect's template types only exist without it.
		if ( ! \ARCHT_Elementor::is_pro_active() ) {
			$abilities['list-templates'] = $this->ability(
				__( 'List Architect templates', 'architect-complete-theme-builder-for-elementor' ),
				'Lists Architect theme templates (header, footer, single, archive, popup) with their status and display location. Use it to find a template_id before editing a template or its elements.',
				[
					'type'       => 'object',
					'default'    => [],
					'properties' => [
						'type'     => [
							'type' => 'string',
							'enum' => self::TEMPLATE_TYPES,
						],
						'search'   => [ 'type' => 'string' ],
						'page'     => [
							'type'    => 'integer',
							'minimum' => 1,
							'default' => 1,
						],
						'per_page' => [
							'type'    => 'integer',
							'minimum' => 1,
							'maximum' => 50,
							'default' => 20,
						],
					],
				],
				'list_templates',
				'can_edit_posts',
				$read
			);

			$abilities['get-display-options'] = $this->ability(
				__( 'Get display location options', 'architect-complete-theme-builder-for-elementor' ),
				'Returns the display location keys a template type accepts, for architect/set-display-location and architect/create-template. Single templates take singular keys, archive templates take archive keys, and header, footer and popup take both.',
				[
					'type'       => 'object',
					'required'   => [ 'type' ],
					'properties' => [
						'type' => [
							'type' => 'string',
							'enum' => self::TEMPLATE_TYPES,
						],
					],
				],
				'get_display_options',
				'can_edit_posts',
				$read
			);

			$abilities['create-template'] = $this->ability(
				__( 'Create an Architect template', 'architect-complete-theme-builder-for-elementor' ),
				'Creates an Architect theme template (header, footer, single, archive or popup), optionally with its display location, document settings and an initial element tree in Elementor JSON. Created as a draft unless status is "publish"; only published templates display on the site. Returns the template_id to use with architect/manage-elements. A popup\'s size, position, background, radius and overlay are document settings; read them with architect/get-widget-controls and template_id.',
				[
					'type'       => 'object',
					'required'   => [ 'type' ],
					'properties' => [
						'type'     => [
							'type' => 'string',
							'enum' => self::TEMPLATE_TYPES,
						],
						'title'    => [ 'type' => 'string' ],
						'status'   => [
							'type'    => 'string',
							'enum'    => [ 'draft', 'publish' ],
							'default' => 'draft',
						],
						'display'  => $this->display_schema(),
						'settings' => $this->settings_schema( 'Document settings, such as popup triggers or the single and archive page layout.' ),
						'elements' => [
							'type'        => 'array',
							'description' => 'Root elements: containers (or sections), each with nested "elements". Same shape as the "element" of an architect/manage-elements add operation.',
							'items'       => $this->element_schema(),
						],
					],
				],
				'create_template',
				'can_create_templates',
				$write
			);

			$abilities['update-template'] = $this->ability(
				__( 'Update an Architect template', 'architect-complete-theme-builder-for-elementor' ),
				'Changes an Architect template\'s title, status or document settings. Settings are merged into the saved ones; set a key to null to remove it. Read the setting keys with architect/get-widget-controls and template_id. Use architect/set-display-location for the display location.',
				[
					'type'       => 'object',
					'required'   => [ 'template_id' ],
					'properties' => [
						'template_id' => [ 'type' => 'integer' ],
						'title'       => [ 'type' => 'string' ],
						'status'      => [
							'type' => 'string',
							'enum' => [ 'draft', 'pending', 'private', 'publish' ],
						],
						'settings'    => $this->settings_schema( 'Partial document settings to merge.' ),
					],
				],
				'update_template',
				'can_edit_template',
				[
					'readonly'    => false,
					'destructive' => false,
					'idempotent'  => true,
				]
			);

			$abilities['set-display-location'] = $this->ability(
				__( 'Set a template\'s display location', 'architect-complete-theme-builder-for-elementor' ),
				'Sets where an Architect template displays. Only the fields you pass are replaced. "include" and "exclude" take keys from architect/get-display-options; the "specific" key needs include_posts (or exclude_posts) and "specific_terms" needs include_terms (or exclude_terms). Pass an empty include array to stop the template from displaying anywhere.',
				[
					'type'       => 'object',
					'required'   => [ 'template_id' ],
					'properties' => array_merge( [ 'template_id' => [ 'type' => 'integer' ] ], $this->display_schema()['properties'] ),
				],
				'set_display_location',
				'can_edit_template',
				[
					'readonly'    => false,
					'destructive' => false,
					'idempotent'  => true,
				]
			);

			$abilities['delete-template'] = $this->ability(
				__( 'Delete an Architect template', 'architect-complete-theme-builder-for-elementor' ),
				'Moves an Architect template to the trash. It can be restored from Templates > Saved Templates > Trash.',
				[
					'type'       => 'object',
					'required'   => [ 'template_id' ],
					'properties' => [
						'template_id' => [ 'type' => 'integer' ],
					],
				],
				'delete_template',
				'can_delete_template',
				[
					'readonly'    => false,
					'destructive' => true,
					'idempotent'  => true,
				]
			);
		}

		$abilities['list-widgets'] = $this->ability(
			__( 'List Elementor widgets', 'architect-complete-theme-builder-for-elementor' ),
			'Lists the registered Elementor widgets, classic (V3) and atomic (V4), from Elementor, Architect and other plugins. Use a name as widgetType in architect/manage-elements, then read its controls with architect/get-widget-controls.',
			[
				'type'       => 'object',
				'default'    => [],
				'properties' => [
					'search'   => [ 'type' => 'string' ],
					'category' => [
						'type'        => 'string',
						'description' => 'Widget panel category, such as "basic", "general" or "archt" (Architect).',
					],
				],
			],
			'list_widgets',
			'can_edit_posts',
			$read
		);

		$abilities['get-widget-controls'] = $this->ability(
			__( 'Get widget controls', 'architect-complete-theme-builder-for-elementor' ),
			'Returns the setting keys of a classic widget or element (container, section, column), or with template_id the document settings of a template or page, with type, options, default and conditions, plus the value format of each control type. These keys are what architect/manage-elements (or architect/update-template for documents) writes, for content and styling alike. Read layout_guidance before styling: grid and loop widgets size and style their items themselves, and popups size and style their box through document settings. Pass tab and search to keep the answer small.',
			[
				'type'       => 'object',
				'default'    => [],
				'properties' => [
					'widget_type' => [
						'type'        => 'string',
						'description' => 'Widget name from architect/list-widgets, or "container", "section" or "column".',
					],
					'template_id' => [
						'type'        => 'integer',
						'description' => 'Read the document settings of this template or page instead of a widget.',
					],
					'tab'         => [
						'type'    => 'string',
						'enum'    => [ 'all', 'content', 'style', 'advanced' ],
						'default' => 'all',
					],
					'search'      => [
						'type'        => 'string',
						'description' => 'Matches control names, labels and section names, such as "typography" or "color".',
					],
				],
			],
			'get_widget_controls',
			'can_edit_posts',
			$read
		);

		$abilities['get-elements'] = $this->ability(
			__( 'Get document elements', 'architect-complete-theme-builder-for-elementor' ),
			'Returns the element tree of any Elementor document (Architect templates, pages, posts), classic and atomic elements included. Without include_settings each node carries id, elType, widgetType and a short label; with it, the saved settings that architect/manage-elements updates. Pass element_id to read one subtree.',
			[
				'type'       => 'object',
				'required'   => [ 'post_id' ],
				'properties' => [
					'post_id'          => [ 'type' => 'integer' ],
					'element_id'       => [ 'type' => 'string' ],
					'include_settings' => [
						'type'    => 'boolean',
						'default' => false,
					],
				],
			],
			'get_elements',
			'can_edit_document',
			$read
		);

		$abilities['manage-elements'] = $this->ability(
			__( 'Manage document elements', 'architect-complete-theme-builder-for-elementor' ),
			'Adds, updates, deletes, moves and duplicates classic (V3) elements in any Elementor document, Architect templates included, in up to 50 operations saved together. Styling is done through settings: read the keys with architect/get-widget-controls; "custom_css" takes CSS where "selector" is the element. Style the element that owns the layout: a grid or loop widget sets item width, gaps and card styling for its items, and a popup sets its own size, background and radius in document settings; containers inside an item template or popup hold content only. Changes go live on published documents; reload an open editor to see them. Atomic (V4) elements can be added, moved and deleted here, but update them with Elementor\'s own manage-elements tool.',
			[
				'type'       => 'object',
				'required'   => [ 'post_id', 'operations' ],
				'properties' => [
					'post_id'    => [ 'type' => 'integer' ],
					'operations' => [
						'type'     => 'array',
						'minItems' => 1,
						'maxItems' => self::MAX_OPERATIONS,
						'items'    => [
							'type'       => 'object',
							'required'   => [ 'action' ],
							'properties' => [
								'action'     => [
									'type' => 'string',
									'enum' => [ 'add', 'update', 'delete', 'move', 'duplicate' ],
								],
								'element_id' => [
									'type'        => 'string',
									'description' => 'update, delete, move, duplicate: the target element.',
								],
								'parent_id'  => [
									'type'        => 'string',
									'description' => 'add, move: the parent element id, or "document" for the root. Defaults to "document".',
								],
								'index'      => [
									'type'        => 'integer',
									'minimum'     => 0,
									'description' => 'add, move: position among the parent\'s children. Appends when omitted.',
								],
								'element'    => array_merge( $this->element_schema(), [ 'description' => 'add: the element to insert, with optional nested "elements". IDs are generated.' ] ),
								'settings'   => $this->settings_schema( 'update: partial settings to merge; set a key to null to remove it. Setting a key also clears its global or dynamic value.' ),
							],
						],
					],
				],
			],
			'manage_elements',
			'can_edit_document',
			$write
		);

		$prefixed = [];
		foreach ( $abilities as $name => $ability ) {
			$prefixed[ self::PREFIX . $name ] = $ability;
		}

		return $prefixed;
	}

	/**
	 * Build an ability definition.
	 *
	 * @since 1.1.0
	 * @access private
	 *
	 * @param string $label       Label.
	 * @param string $description Description, written for the AI client.
	 * @param array  $schema      Input schema.
	 * @param string $callback    Execute method.
	 * @param string $permission  Permission method.
	 * @param array  $annotations MCP annotations.
	 * @return array
	 */
	private function ability( $label, $description, $schema, $callback, $permission, $annotations ) {
		return [
			'label'               => $label,
			'description'         => $description,
			'category'            => 'architect',
			'input_schema'        => $schema,
			'execute_callback'    => [ $this, $callback ],
			'permission_callback' => [ $this, $permission ],
			'meta'                => [
				'show_in_rest' => true,
				'annotations'  => $annotations,
				'mcp'          => [
					'public' => true,
					'type'   => 'tool',
				],
			],
		];
	}

	/**
	 * Display location input schema.
	 *
	 * @since 1.1.0
	 * @access private
	 *
	 * @return array
	 */
	private function display_schema() {
		$keys = [
			'type'  => 'array',
			'items' => [ 'type' => 'string' ],
		];
		$ids  = [
			'type'  => 'array',
			'items' => [ 'type' => 'integer' ],
		];

		return [
			'type'        => 'object',
			'description' => 'Display location. Keys come from architect/get-display-options.',
			'properties'  => [
				'include'       => array_merge( $keys, [ 'description' => 'Display on these locations, such as ["all"] or ["single-post"].' ] ),
				'exclude'       => array_merge( $keys, [ 'description' => 'Never display on these locations.' ] ),
				'include_posts' => array_merge( $ids, [ 'description' => 'Post or page IDs for the "specific" include key.' ] ),
				'exclude_posts' => array_merge( $ids, [ 'description' => 'Post or page IDs for the "specific" exclude key.' ] ),
				'include_terms' => array_merge( $ids, [ 'description' => 'Term IDs for the "specific_terms" include key.' ] ),
				'exclude_terms' => array_merge( $ids, [ 'description' => 'Term IDs for the "specific_terms" exclude key.' ] ),
			],
		];
	}

	/**
	 * Free-form settings schema.
	 *
	 * @since 1.1.0
	 * @access private
	 *
	 * @param string $description Description.
	 * @return array
	 */
	private function settings_schema( $description ) {
		return [
			'type'                 => 'object',
			'additionalProperties' => true,
			'description'          => $description,
		];
	}

	/**
	 * Element input schema.
	 *
	 * @since 1.1.0
	 * @access private
	 *
	 * @return array
	 */
	private function element_schema() {
		return [
			'type'                 => 'object',
			'additionalProperties' => true,
			'properties'           => [
				'elType'     => [
					'type'        => 'string',
					'description' => '"container", "section", "column" or "widget". Containers hold containers and widgets; sections hold columns; columns hold widgets and inner sections.',
				],
				'widgetType' => [
					'type'        => 'string',
					'description' => 'Widget name, for elType "widget".',
				],
				'settings'   => $this->settings_schema( 'Setting keys from architect/get-widget-controls.' ),
				'elements'   => [
					'type'  => 'array',
					'items' => [
						'type'                 => 'object',
						'additionalProperties' => true,
					],
				],
			],
		];
	}

	/**
	 * Whether the user can edit posts.
	 *
	 * @since 1.1.0
	 * @access public
	 *
	 * @return bool
	 */
	public function can_edit_posts() {
		return current_user_can( 'edit_posts' );
	}

	/**
	 * Whether the user can create templates.
	 *
	 * @since 1.1.0
	 * @access public
	 *
	 * @return bool
	 */
	public function can_create_templates() {
		$post_type = get_post_type_object( 'elementor_library' );

		return $post_type && current_user_can( $post_type->cap->create_posts );
	}

	/**
	 * Whether the user can edit the template.
	 *
	 * @since 1.1.0
	 * @access public
	 *
	 * @param array $input Ability input.
	 * @return bool
	 */
	public function can_edit_template( $input = [] ) {
		$id = absint( ( (array) $input )['template_id'] ?? 0 );

		return $id && current_user_can( 'edit_post', $id );
	}

	/**
	 * Whether the user can delete the template.
	 *
	 * @since 1.1.0
	 * @access public
	 *
	 * @param array $input Ability input.
	 * @return bool
	 */
	public function can_delete_template( $input = [] ) {
		$id = absint( ( (array) $input )['template_id'] ?? 0 );

		return $id && current_user_can( 'delete_post', $id );
	}

	/**
	 * Whether the user can edit the document.
	 *
	 * @since 1.1.0
	 * @access public
	 *
	 * @param array $input Ability input.
	 * @return bool
	 */
	public function can_edit_document( $input = [] ) {
		$id = absint( ( (array) $input )['post_id'] ?? 0 );

		return $id && current_user_can( 'edit_post', $id );
	}

	/**
	 * List the Architect templates.
	 *
	 * @since 1.1.0
	 * @access public
	 *
	 * @param array $input Ability input.
	 * @return array
	 */
	public function list_templates( $input = [] ) {
		$input    = $this->normalize_input( $input );
		$type     = $input['type'] ?? '';
		$per_page = min( 50, max( 1, (int) ( $input['per_page'] ?? 20 ) ) );
		$page     = max( 1, (int) ( $input['page'] ?? 1 ) );

		$query = new WP_Query(
			[
				'post_type'      => 'elementor_library',
				'post_status'    => [ 'publish', 'draft', 'pending', 'private', 'future' ],
				'posts_per_page' => $per_page,
				'paged'          => $page,
				'orderby'        => 'modified',
				'order'          => 'DESC',
				's'              => sanitize_text_field( $input['search'] ?? '' ),
				'lang'           => '',
				'meta_query'     => [ // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
					[
						'key'     => '_elementor_template_type',
						'value'   => $type ? [ $type ] : self::TEMPLATE_TYPES,
						'compare' => 'IN',
					],
				],
			]
		);

		return [
			'templates'   => array_map( [ $this, 'describe_template' ], $query->posts ),
			'total'       => (int) $query->found_posts,
			'page'        => $page,
			'total_pages' => (int) $query->max_num_pages,
		];
	}

	/**
	 * Get the display location options of a template type.
	 *
	 * @since 1.1.0
	 * @access public
	 *
	 * @param array $input Ability input.
	 * @return array
	 */
	public function get_display_options( $input = [] ) {
		$type    = ( (array) $input )['type'] ?? '';
		$options = [];

		foreach ( $this->get_display_option_labels( $type ) as $key => $label ) {
			$options[] = [
				'key'   => (string) $key,
				'label' => $label,
			];
		}

		return [
			'type'    => $type,
			'options' => $options,
			'notes'   => '"specific" needs include_posts or exclude_posts. "specific_terms" needs include_terms or exclude_terms. When several templates of one type match a page, the most recently modified one wins.',
		];
	}

	/**
	 * Create an Architect template.
	 *
	 * @since 1.1.0
	 * @access public
	 *
	 * @param array $input Ability input.
	 * @return array|WP_Error
	 */
	public function create_template( $input = [] ) {
		$input  = $this->normalize_input( $input );
		$type   = $input['type'] ?? '';
		$status = 'publish' === ( $input['status'] ?? 'draft' ) ? 'publish' : 'draft';

		if ( ! in_array( $type, self::TEMPLATE_TYPES, true ) ) {
			return new WP_Error( 'archt_invalid_type', 'Unknown template type.' );
		}

		if ( 'publish' === $status && ! current_user_can( get_post_type_object( 'elementor_library' )->cap->publish_posts ) ) {
			return new WP_Error( 'archt_forbidden', 'You are not allowed to publish templates. Create it as a draft.' );
		}

		$settings = $this->merge_settings( [], (array) ( $input['settings'] ?? [] ) );
		$warnings = [];

		unset( $settings['post_title'], $settings['post_status'] );

		if ( isset( $input['display'] ) ) {
			$settings = $this->apply_display( $type, $settings, (array) $input['display'], $warnings );

			if ( is_wp_error( $settings ) ) {
				return $settings;
			}
		}

		$ids      = [];
		$elements = [];

		foreach ( (array) ( $input['elements'] ?? [] ) as $data ) {
			$element = $this->prepare_element( $data, $ids );

			if ( is_wp_error( $element ) ) {
				return $element;
			}

			$error = $this->check_nesting( null, $element );

			if ( is_wp_error( $error ) ) {
				return $error;
			}

			$elements[] = $element;
		}

		$document = \Elementor\Plugin::$instance->documents->create(
			$type,
			[
				'post_title'  => sanitize_text_field( $input['title'] ?? '' ),
				'post_status' => $status,
			]
		);

		if ( is_wp_error( $document ) ) {
			return $document;
		}

		$data = [ 'elements' => $elements ];

		if ( $settings ) {
			$data['settings'] = $settings;
		}

		$saved = $this->save_document( $document, $data );

		if ( is_wp_error( $saved ) ) {
			return $saved;
		}

		return array_merge(
			$this->describe_template( get_post( $document->get_main_id() ) ),
			[ 'warnings' => $warnings ]
		);
	}

	/**
	 * Update an Architect template.
	 *
	 * @since 1.1.0
	 * @access public
	 *
	 * @param array $input Ability input.
	 * @return array|WP_Error
	 */
	public function update_template( $input = [] ) {
		$input    = $this->normalize_input( $input );
		$document = $this->get_template( $input['template_id'] ?? 0 );

		if ( is_wp_error( $document ) ) {
			return $document;
		}

		$id    = $document->get_main_id();
		$patch = (array) ( $input['settings'] ?? [] );

		unset( $patch['post_title'], $patch['post_status'] );

		$changes  = ! empty( $patch );
		$settings = $this->merge_settings( $this->get_page_settings( $id ), $patch );

		if ( isset( $input['title'] ) && '' !== trim( $input['title'] ) ) {
			$settings['post_title'] = sanitize_text_field( $input['title'] );
			$changes                = true;
		}

		if ( isset( $input['status'] ) ) {
			if ( 'publish' === $input['status'] && ! current_user_can( 'publish_post', $id ) ) {
				return new WP_Error( 'archt_forbidden', 'You are not allowed to publish this template.' );
			}

			$settings['post_status'] = $input['status'];
			$changes                 = true;
		}

		if ( ! $changes ) {
			return new WP_Error( 'archt_nothing_to_update', 'Pass a title, status or settings to update.' );
		}

		$saved = $this->save_document( $document, [ 'settings' => $settings ] );

		if ( is_wp_error( $saved ) ) {
			return $saved;
		}

		return $this->describe_template( get_post( $id ) );
	}

	/**
	 * Set a template's display location.
	 *
	 * @since 1.1.0
	 * @access public
	 *
	 * @param array $input Ability input.
	 * @return array|WP_Error
	 */
	public function set_display_location( $input = [] ) {
		$input    = $this->normalize_input( $input );
		$document = $this->get_template( $input['template_id'] ?? 0 );

		if ( is_wp_error( $document ) ) {
			return $document;
		}

		$id       = $document->get_main_id();
		$warnings = [];
		$settings = $this->apply_display( $document->get_name(), $this->get_page_settings( $id ), $input, $warnings );

		if ( is_wp_error( $settings ) ) {
			return $settings;
		}

		$saved = $this->save_document( $document, [ 'settings' => $settings ] );

		if ( is_wp_error( $saved ) ) {
			return $saved;
		}

		return array_merge( $this->describe_template( get_post( $id ) ), [ 'warnings' => $warnings ] );
	}

	/**
	 * Move an Architect template to the trash.
	 *
	 * @since 1.1.0
	 * @access public
	 *
	 * @param array $input Ability input.
	 * @return array|WP_Error
	 */
	public function delete_template( $input = [] ) {
		$document = $this->get_template( ( (array) $input )['template_id'] ?? 0 );

		if ( is_wp_error( $document ) ) {
			return $document;
		}

		$id = $document->get_main_id();

		if ( ! wp_trash_post( $id ) ) {
			return new WP_Error( 'archt_trash_failed', 'The template could not be moved to the trash.' );
		}

		return [
			'template_id' => $id,
			'status'      => 'trash',
		];
	}

	/**
	 * List the registered widgets.
	 *
	 * @since 1.1.0
	 * @access public
	 *
	 * @param array $input Ability input.
	 * @return array
	 */
	public function list_widgets( $input = [] ) {
		$input    = $this->normalize_input( $input );
		$search   = strtolower( (string) ( $input['search'] ?? '' ) );
		$category = (string) ( $input['category'] ?? '' );
		$widgets  = [];

		foreach ( \Elementor\Plugin::$instance->widgets_manager->get_widget_types() as $name => $widget ) {
			if ( 0 === strpos( $name, 'common' ) || ! $widget->show_in_panel() ) {
				continue;
			}

			$title      = wp_strip_all_tags( (string) $widget->get_title() );
			$categories = (array) $widget->get_categories();

			if ( '' !== $search && false === strpos( strtolower( $name . ' ' . $title ), $search ) ) {
				continue;
			}

			if ( '' !== $category && ! in_array( $category, $categories, true ) ) {
				continue;
			}

			$widgets[] = [
				'name'       => $name,
				'title'      => $title,
				'categories' => $categories,
				'atomic'     => $this->is_atomic_type( $name ),
			];
		}

		$elements = [];

		foreach ( [ 'container', 'section', 'column' ] as $el_type ) {
			if ( \Elementor\Plugin::$instance->elements_manager->get_element_types( $el_type ) ) {
				$elements[] = $el_type;
			}
		}

		return [
			'widgets'  => $widgets,
			'elements' => $elements,
		];
	}

	/**
	 * Get the controls of a widget or element.
	 *
	 * @since 1.1.0
	 * @access public
	 *
	 * @param array $input Ability input.
	 * @return array|WP_Error
	 */
	public function get_widget_controls( $input = [] ) {
		$input    = $this->normalize_input( $input );
		$type     = (string) ( $input['widget_type'] ?? '' );
		$tab      = $input['tab'] ?? 'all';
		$search   = strtolower( (string) ( $input['search'] ?? '' ) );
		$document = null;

		if ( ! empty( $input['template_id'] ) ) {
			$document = $this->get_document( $input['template_id'] );

			if ( is_wp_error( $document ) ) {
				return $document;
			}

			$instance = $document;
			$type     = $document->get_name();
		} else {
			$instance = $this->get_type_instance( in_array( $type, [ 'container', 'section', 'column' ], true ) ? $type : 'widget', $type );

			if ( ! $instance ) {
				return new WP_Error( 'archt_unknown_widget', sprintf( 'Unknown widget type "%s". Use architect/list-widgets.', $type ) );
			}

			if ( $this->is_atomic_type( $type ) ) {
				return new WP_Error( 'archt_atomic_widget', 'This is an atomic (V4) widget. Use Elementor\'s own get-widget-schema tool for it.' );
			}
		}

		$tabs     = [
			'content'  => [ 'content', 'layout', 'settings' ],
			'style'    => [ 'style' ],
			'advanced' => [ 'advanced', 'responsive' ],
		];
		$devices  = array_keys( \Elementor\Plugin::$instance->breakpoints->get_active_breakpoints() );
		$controls = $instance->get_controls();
		$sections = [];
		$list     = [];
		$types    = [];

		foreach ( $controls as $name => $control ) {
			if ( 'section' === ( $control['type'] ?? '' ) ) {
				$sections[ $name ] = wp_strip_all_tags( (string) ( $control['label'] ?? $name ) );
			}
		}

		foreach ( $controls as $name => $control ) {
			$control_type = $control['type'] ?? '';

			if ( in_array( $control_type, self::SKIPPED_CONTROL_TYPES, true ) || $this->is_device_copy( $name, $devices, $controls ) ) {
				continue;
			}

			// Set through architect/set-display-location.
			if ( $document && 'archt_display' === ( $control['tab'] ?? '' ) ) {
				continue;
			}

			if ( 'all' !== $tab && ! in_array( $control['tab'] ?? '', $tabs[ $tab ] ?? [], true ) ) {
				continue;
			}

			$label   = wp_strip_all_tags( (string) ( $control['label'] ?? '' ) );
			$section = $sections[ $control['section'] ?? '' ] ?? '';

			if ( '' !== $search && false === strpos( strtolower( $name . ' ' . $label . ' ' . $section ), $search ) ) {
				continue;
			}

			$list[]                 = $this->describe_control( $name, $control, $section, $devices, $controls );
			$types[ $control_type ] = true;
		}

		/**
		 * Filters the layout guidance given to AI clients for a widget, element or document.
		 *
		 * @since 1.1.0
		 *
		 * @param string[]                         $guidance Guidance sentences.
		 * @param string                           $type     Widget name, elType or document type.
		 * @param \Elementor\Controls_Stack $instance Widget, element or document.
		 */
		$guidance = apply_filters( 'archt/mcp/layout_guidance', $document ? $this->get_document_guidance( $type ) : $this->get_widget_guidance( $controls ), $type, $instance );

		return [
			( $document ? 'document_type' : 'widget_type' ) => $type,
			'layout_guidance' => array_values( (array) $guidance ),
			'controls'        => $list,
			'value_formats'   => array_intersect_key( self::VALUE_FORMATS, $types ),
			'devices'         => array_values( array_diff( $devices, [ 'desktop' ] ) ),
			'notes'           => 'Responsive controls take one key per device: the name for desktop, then name_tablet, name_mobile and so on. Group controls need their toggle set first, such as "<prefix>_typography":"custom" or "background_background":"classic". Global colors and fonts go in "__globals__", such as {"title_color":"globals/colors?id=primary"}. "custom_css" takes CSS where "selector" is the element.',
		];
	}

	/**
	 * Detect what a widget lays out and styles itself, so the AI client doesn't do it inside the items.
	 *
	 * @since 1.1.0
	 * @access private
	 *
	 * @param array $controls Widget controls.
	 * @return string[]
	 */
	private function get_widget_guidance( array $controls ) {
		$grid     = [];
		$template = [];
		$card     = [];

		foreach ( $controls as $name => $control ) {
			$control_type = $control['type'] ?? '';

			if ( in_array( $control_type, self::SKIPPED_CONTROL_TYPES, true ) ) {
				continue;
			}

			if ( preg_match( '/(^|_)(columns|column_gap|row_gap|grid_gap|items_gap|slides_to_show)$/', $name ) ) {
				$grid[ $name ] = true;
			} elseif ( false !== strpos( $name, 'template' ) && in_array( $control_type, [ 'select', 'select2', 'query' ], true ) ) {
				$template[ $name ] = true;
			} elseif ( preg_match( '/^((card|item|box|loop)_[a-z_]*?(background|border_radius|border|box_shadow|padding))/', $name, $match ) ) {
				$card[ $match[1] ] = true;
			}
		}

		$guidance = [];

		if ( $grid ) {
			$guidance[] = sprintf( 'This widget lays out its items itself (%s). Set the items per row and spacing here, not with widths on the items.', implode( ', ', array_slice( array_keys( $grid ), 0, 8 ) ) );
		}

		if ( $template ) {
			$guidance[] = sprintf( 'The item template picked in %s holds the content of one item only. Keep its root container at full width, without its own width, card background, border or shadow; the widget sizes each item.', implode( ', ', array_keys( $template ) ) );
		}

		if ( $card ) {
			$guidance[] = sprintf( 'Card styling (%s) lives on this widget and applies to every item wrapper. Put card backgrounds, borders, shadows and padding here, not in the item template.', implode( ', ', array_slice( array_keys( $card ), 0, 8 ) ) );
		}

		return $guidance;
	}

	/**
	 * Explain what a document type styles itself.
	 *
	 * @since 1.1.0
	 * @access private
	 *
	 * @param string $type Document type.
	 * @return string[]
	 */
	private function get_document_guidance( $type ) {
		if ( 'popup' === $type ) {
			return [ 'The popup box is styled by these document settings: width, max width, height, padding, background, border radius, position, overlay and close button. Set them with architect/update-template. Keep the root container inside the popup at full width, without its own width, background, radius or shadow.' ];
		}

		return [ 'These document settings are set with architect/update-template. Content width and spacing are set on the root containers.' ];
	}

	/**
	 * Get a document's element tree.
	 *
	 * @since 1.1.0
	 * @access public
	 *
	 * @param array $input Ability input.
	 * @return array|WP_Error
	 */
	public function get_elements( $input = [] ) {
		$input    = $this->normalize_input( $input );
		$document = $this->get_document( $input['post_id'] ?? 0 );

		if ( is_wp_error( $document ) ) {
			return $document;
		}

		$elements = $document->get_elements_data();

		if ( ! empty( $input['element_id'] ) ) {
			$path = $this->find_path( $elements, $input['element_id'] );

			if ( null === $path ) {
				return new WP_Error( 'archt_element_not_found', 'Element not found.' );
			}

			$elements = [ $this->get_at( $elements, $path ) ];
		}

		$post = $document->get_main_post();

		return [
			'post_id'  => $post->ID,
			'title'    => $post->post_title,
			'type'     => $document->get_name(),
			'status'   => $post->post_status,
			'elements' => $this->outline( $elements, ! empty( $input['include_settings'] ) ),
		];
	}

	/**
	 * Apply element operations to a document and save it once.
	 *
	 * @since 1.1.0
	 * @access public
	 *
	 * @param array $input Ability input.
	 * @return array|WP_Error
	 */
	public function manage_elements( $input = [] ) {
		$input      = $this->normalize_input( $input );
		$document   = $this->get_document( $input['post_id'] ?? 0 );
		$operations = array_values( (array) ( $input['operations'] ?? [] ) );

		if ( is_wp_error( $document ) ) {
			return $document;
		}

		if ( ! $operations || count( $operations ) > self::MAX_OPERATIONS ) {
			return new WP_Error( 'archt_invalid_operations', sprintf( 'Pass between 1 and %d operations.', self::MAX_OPERATIONS ) );
		}

		$tree    = $document->get_elements_data();
		$ids     = $this->collect_ids( $tree );
		$results = [];
		$changed = false;

		foreach ( $operations as $index => $operation ) {
			$operation = (array) $operation;
			$working   = $tree;
			$result    = $this->apply_operation( $working, $operation, $ids );
			$entry     = [
				'index'  => $index,
				'action' => $operation['action'] ?? '',
			];

			if ( is_wp_error( $result ) ) {
				$results[] = $entry + [
					'status'  => 'error',
					'message' => $result->get_error_message(),
				];
				continue;
			}

			$tree      = $working;
			$changed   = true;
			$results[] = $entry + [
				'status'     => 'ok',
				'element_id' => $result,
			];
		}

		if ( $changed ) {
			$saved = $this->save_document( $document, [ 'elements' => $tree ] );

			if ( is_wp_error( $saved ) ) {
				return $saved;
			}
		}

		return [
			'post_id'  => $document->get_main_id(),
			'saved'    => $changed,
			'results'  => $results,
			'edit_url' => $document->get_edit_url(),
		];
	}

	/**
	 * Apply one element operation.
	 *
	 * @since 1.1.0
	 * @access private
	 *
	 * @param array $tree      Element tree, changed in place.
	 * @param array $operation Operation.
	 * @param array $ids       Element IDs in use, changed in place.
	 * @return string|WP_Error The affected element ID.
	 */
	private function apply_operation( array &$tree, array $operation, array &$ids ) {
		$action = $operation['action'] ?? '';

		if ( 'add' === $action ) {
			$element = $this->prepare_element( $operation['element'] ?? null, $ids );

			if ( is_wp_error( $element ) ) {
				return $element;
			}

			return $this->insert( $tree, $element, $operation['parent_id'] ?? 'document', $operation['index'] ?? null );
		}

		$id   = (string) ( $operation['element_id'] ?? '' );
		$path = '' === $id ? null : $this->find_path( $tree, $id );

		if ( null === $path ) {
			return new WP_Error( 'archt_element_not_found', sprintf( 'Element "%s" not found.', $id ) );
		}

		$position = array_pop( $path );
		$siblings = &$this->children_at( $tree, $path );

		switch ( $action ) {
			case 'update':
				if ( $this->is_atomic( $siblings[ $position ] ) ) {
					return new WP_Error( 'archt_atomic_element', 'Atomic (V4) element: update it with Elementor\'s own manage-elements tool.' );
				}

				if ( empty( $operation['settings'] ) || ! is_array( $operation['settings'] ) ) {
					return new WP_Error( 'archt_missing_settings', 'Pass the settings to update.' );
				}

				$element             = &$siblings[ $position ];
				$element['settings'] = $this->merge_settings( (array) ( $element['settings'] ?? [] ), $operation['settings'] );
				$this->fill_repeater_ids( $element );

				return $id;

			case 'delete':
				array_splice( $siblings, $position, 1 );

				return $id;

			case 'move':
				$element = $siblings[ $position ];
				array_splice( $siblings, $position, 1 );
				unset( $siblings );

				return $this->insert( $tree, $element, $operation['parent_id'] ?? 'document', $operation['index'] ?? null );

			case 'duplicate':
				$copy = $this->renew_ids( $siblings[ $position ], $ids );
				array_splice( $siblings, $position + 1, 0, [ $copy ] );

				return $copy['id'];
		}

		return new WP_Error( 'archt_invalid_action', 'Unknown action.' );
	}

	/**
	 * Insert an element under a parent.
	 *
	 * @since 1.1.0
	 * @access private
	 *
	 * @param array    $tree      Element tree, changed in place.
	 * @param array    $element   Element.
	 * @param string   $parent_id Parent element ID, or 'document'.
	 * @param int|null $index     Position, or null to append.
	 * @return string|WP_Error The element ID.
	 */
	private function insert( array &$tree, array $element, $parent_id, $index ) {
		$parent = null;
		$path   = [];

		if ( 'document' !== $parent_id && '' !== (string) $parent_id ) {
			$path = $this->find_path( $tree, $parent_id );

			if ( null === $path ) {
				return new WP_Error( 'archt_parent_not_found', sprintf( 'Parent "%s" not found.', $parent_id ) );
			}

			$parent = $this->get_at( $tree, $path );
		}

		$error = $this->check_nesting( $parent, $element );

		if ( is_wp_error( $error ) ) {
			return $error;
		}

		$this->set_inner( $element, $parent );

		$children = &$this->children_at( $tree, $path );
		$index    = null === $index ? count( $children ) : min( max( 0, (int) $index ), count( $children ) );

		array_splice( $children, $index, 0, [ $element ] );

		return $element['id'];
	}

	/**
	 * Check that an element may be placed under a parent.
	 *
	 * @since 1.1.0
	 * @access private
	 *
	 * @param array|null $holder  Parent element, or null for the document root.
	 * @param array      $element Element.
	 * @return true|WP_Error
	 */
	private function check_nesting( $holder, array $element ) {
		$holder_type = null === $holder ? 'document' : ( $holder['elType'] ?? '' );
		$type        = $element['elType'] ?? '';

		if ( $this->is_atomic( $element ) || ( $holder && $this->is_atomic( $holder ) ) ) {
			return in_array( $type, [ 'section', 'column' ], true ) || 'widget' === $holder_type
				? new WP_Error( 'archt_invalid_nesting', sprintf( 'A %s cannot go inside a %s.', $type, $holder_type ) )
				: true;
		}

		$rules = [
			'document'  => [ 'container', 'section' ],
			'container' => [ 'container', 'widget' ],
			'section'   => [ 'column' ],
			'column'    => [ 'widget', 'section' ],
		];

		if ( ! in_array( $type, $rules[ $holder_type ] ?? [], true ) ) {
			return new WP_Error( 'archt_invalid_nesting', sprintf( 'A %1$s cannot go inside the %2$s. Allowed: %3$s.', $type, $holder_type, implode( ', ', $rules[ $holder_type ] ?? [ 'nothing' ] ) ) );
		}

		if ( 'section' === $type && ! empty( $holder['isInner'] ) ) {
			return new WP_Error( 'archt_invalid_nesting', 'An inner section cannot hold another section.' );
		}

		return true;
	}

	/**
	 * Set the isInner flag of an element and its columns from its parent.
	 *
	 * @since 1.1.0
	 * @access private
	 *
	 * @param array      $element Element, changed in place.
	 * @param array|null $holder  Parent element, or null for the document root.
	 */
	private function set_inner( array &$element, $holder ) {
		$type = $element['elType'] ?? '';

		if ( 'container' === $type || 'section' === $type ) {
			$element['isInner'] = null !== $holder;
		} elseif ( 'column' === $type ) {
			$element['isInner'] = ! empty( $holder['isInner'] );
		} else {
			return;
		}

		if ( empty( $element['elements'] ) || ! is_array( $element['elements'] ) ) {
			return;
		}

		foreach ( $element['elements'] as &$child ) {
			$this->set_inner( $child, $element );
		}
		unset( $child );
	}

	/**
	 * Validate and normalize a new element, with fresh IDs.
	 *
	 * @since 1.1.0
	 * @access private
	 *
	 * @param mixed $data Element data.
	 * @param array $ids  Element IDs in use, changed in place.
	 * @return array|WP_Error
	 */
	private function prepare_element( $data, array &$ids ) {
		if ( ! is_array( $data ) ) {
			return new WP_Error( 'archt_invalid_element', 'Pass the element as an object with elType, widgetType and settings.' );
		}

		$type        = (string) ( $data['elType'] ?? ( isset( $data['widgetType'] ) ? 'widget' : '' ) );
		$widget_type = 'widget' === $type ? (string) ( $data['widgetType'] ?? '' ) : '';

		if ( ! $this->get_type_instance( $type, $widget_type ) ) {
			return new WP_Error(
				'archt_unknown_element',
				'widget' === $type
					? sprintf( 'Unknown widget type "%s". Use architect/list-widgets.', $widget_type )
					: sprintf( 'Unknown elType "%s". Use container, section, column or widget.', $type )
			);
		}

		$element = [
			'id'       => $this->new_id( $ids ),
			'elType'   => $type,
			'settings' => $this->merge_settings( [], (array) ( $data['settings'] ?? [] ) ),
			'elements' => [],
		];

		if ( $widget_type ) {
			$element['widgetType'] = $widget_type;

			if ( ! empty( $data['elements'] ) ) {
				return new WP_Error( 'archt_invalid_nesting', 'Widgets cannot hold elements.' );
			}
		}

		foreach ( (array) ( $data['elements'] ?? [] ) as $child_data ) {
			$child = $this->prepare_element( $child_data, $ids );

			if ( is_wp_error( $child ) ) {
				return $child;
			}

			$error = $this->check_nesting( $element, $child );

			if ( is_wp_error( $error ) ) {
				return $error;
			}

			$element['elements'][] = $child;
		}

		$this->fill_repeater_ids( $element );

		return $element;
	}

	/**
	 * Copy an element with new IDs.
	 *
	 * @since 1.1.0
	 * @access private
	 *
	 * @param array $element Element.
	 * @param array $ids     Element IDs in use, changed in place.
	 * @return array
	 */
	private function renew_ids( array $element, array &$ids ) {
		$element['id'] = $this->new_id( $ids );

		foreach ( (array) ( $element['elements'] ?? [] ) as $index => $child ) {
			$element['elements'][ $index ] = $this->renew_ids( $child, $ids );
		}

		return $element;
	}

	/**
	 * Merge a settings patch into settings; null values remove keys.
	 *
	 * Setting a plain value also drops the global or dynamic value that would override it.
	 *
	 * @since 1.1.0
	 * @access private
	 *
	 * @param array $settings Settings.
	 * @param array $patch    Settings patch.
	 * @return array
	 */
	private function merge_settings( array $settings, array $patch ) {
		foreach ( $patch as $key => $value ) {
			if ( in_array( $key, [ '__globals__', '__dynamic__' ], true ) && is_array( $value ) ) {
				$settings[ $key ] = $this->merge_settings( (array) ( $settings[ $key ] ?? [] ), $value );
				continue;
			}

			if ( null === $value ) {
				unset( $settings[ $key ] );
				continue;
			}

			$settings[ $key ] = $value;

			foreach ( [ '__globals__', '__dynamic__' ] as $override ) {
				if ( ! isset( $patch[ $override ][ $key ] ) ) {
					unset( $settings[ $override ][ $key ] );
				}
			}
		}

		foreach ( [ '__globals__', '__dynamic__' ] as $override ) {
			if ( isset( $settings[ $override ] ) && empty( $settings[ $override ] ) ) {
				unset( $settings[ $override ] );
			}
		}

		return $settings;
	}

	/**
	 * Give repeater items without an _id a new one.
	 *
	 * @since 1.1.0
	 * @access private
	 *
	 * @param array $element Element, changed in place.
	 */
	private function fill_repeater_ids( array &$element ) {
		$instance = $this->get_type_instance( $element['elType'] ?? '', $element['widgetType'] ?? '' );

		if ( ! $instance || $this->is_atomic( $element ) ) {
			return;
		}

		foreach ( $instance->get_controls() as $name => $control ) {
			if ( 'repeater' !== ( $control['type'] ?? '' ) || empty( $element['settings'][ $name ] ) || ! is_array( $element['settings'][ $name ] ) ) {
				continue;
			}

			foreach ( $element['settings'][ $name ] as $index => $item ) {
				if ( is_array( $item ) && empty( $item['_id'] ) ) {
					$element['settings'][ $name ][ $index ]['_id'] = substr( md5( wp_rand() . microtime() ), 0, 7 );
				}
			}
		}
	}

	/**
	 * Get the Elementor instance of an element or widget type.
	 *
	 * @since 1.1.0
	 * @access private
	 *
	 * @param string $type        elType.
	 * @param string $widget_type Widget name, for widgets.
	 * @return \Elementor\Element_Base|null
	 */
	private function get_type_instance( $type, $widget_type = '' ) {
		$plugin = \Elementor\Plugin::$instance;

		if ( 'widget' === $type ) {
			return '' === $widget_type ? null : $plugin->widgets_manager->get_widget_types( $widget_type );
		}

		return '' === $type ? null : $plugin->elements_manager->get_element_types( $type );
	}

	/**
	 * Whether an element is atomic (V4).
	 *
	 * @since 1.1.0
	 * @access private
	 *
	 * @param array $element Element.
	 * @return bool
	 */
	private function is_atomic( array $element ) {
		return $this->is_atomic_type( $element['elType'] ?? '' ) || $this->is_atomic_type( $element['widgetType'] ?? '' );
	}

	/**
	 * Whether an element or widget name is atomic (V4).
	 *
	 * @since 1.1.0
	 * @access private
	 *
	 * @param string $name elType or widget name.
	 * @return bool
	 */
	private function is_atomic_type( $name ) {
		return 0 === strpos( (string) $name, 'e-' );
	}

	/**
	 * Whether a control is a per-device copy of another control.
	 *
	 * @since 1.1.0
	 * @access private
	 *
	 * @param string $name     Control name.
	 * @param array  $devices  Active device names.
	 * @param array  $controls All controls.
	 * @return bool
	 */
	private function is_device_copy( $name, array $devices, array $controls ) {
		foreach ( $devices as $device ) {
			$suffix = '_' . $device;

			if ( 'desktop' !== $device && substr( $name, -strlen( $suffix ) ) === $suffix && isset( $controls[ substr( $name, 0, -strlen( $suffix ) ) ] ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Describe a control for the AI client.
	 *
	 * @since 1.1.0
	 * @access private
	 *
	 * @param string $name     Control name.
	 * @param array  $control  Control.
	 * @param string $section  Section label.
	 * @param array  $devices  Active device names.
	 * @param array  $controls All controls.
	 * @return array
	 */
	private function describe_control( $name, array $control, $section, array $devices, array $controls ) {
		$entry = [
			'name'    => $name,
			'type'    => $control['type'] ?? '',
			'label'   => wp_strip_all_tags( (string) ( $control['label'] ?? '' ) ),
			'tab'     => $control['tab'] ?? '',
			'section' => $section,
		];

		if ( isset( $control['default'] ) && '' !== $control['default'] && [] !== $control['default'] ) {
			$entry['default'] = $control['default'];
		}

		if ( ! empty( $control['options'] ) && is_array( $control['options'] ) ) {
			$entry['options'] = [];

			foreach ( $control['options'] as $key => $option ) {
				$entry['options'][ $key ] = wp_strip_all_tags( is_array( $option ) ? (string) ( $option['title'] ?? $option['label'] ?? $key ) : (string) $option );
			}
		}

		if ( ! empty( $control['size_units'] ) ) {
			$entry['units'] = array_values( (array) $control['size_units'] );
		}

		if ( ! empty( $control['multiple'] ) ) {
			$entry['multiple'] = true;
		}

		if ( ! empty( $control['is_responsive'] ) || $this->has_device_copies( $name, $devices, $controls ) ) {
			$entry['responsive'] = true;
		}

		if ( ! empty( $control['dynamic']['active'] ) ) {
			$entry['dynamic'] = true;
		}

		if ( ! empty( $control['condition'] ) ) {
			$entry['condition'] = $control['condition'];
		}

		if ( 'repeater' === $entry['type'] && ! empty( $control['fields'] ) ) {
			$entry['fields'] = [];

			foreach ( $control['fields'] as $field_name => $field ) {
				if ( in_array( $field['type'] ?? '', self::SKIPPED_CONTROL_TYPES, true ) ) {
					continue;
				}

				$entry['fields'][] = array_filter(
					[
						'name'    => $field['name'] ?? $field_name,
						'type'    => $field['type'] ?? '',
						'label'   => wp_strip_all_tags( (string) ( $field['label'] ?? '' ) ),
						'default' => $field['default'] ?? null,
					],
					function ( $value ) {
						return null !== $value && '' !== $value;
					}
				);
			}
		}

		return $entry;
	}

	/**
	 * Whether a control has per-device copies.
	 *
	 * @since 1.1.0
	 * @access private
	 *
	 * @param string $name     Control name.
	 * @param array  $devices  Active device names.
	 * @param array  $controls All controls.
	 * @return bool
	 */
	private function has_device_copies( $name, array $devices, array $controls ) {
		foreach ( $devices as $device ) {
			if ( 'desktop' !== $device && isset( $controls[ $name . '_' . $device ] ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Build the outline of an element tree.
	 *
	 * @since 1.1.0
	 * @access private
	 *
	 * @param array $elements         Elements.
	 * @param bool  $include_settings Whether to include settings.
	 * @return array
	 */
	private function outline( array $elements, $include_settings ) {
		$outline = [];

		foreach ( $elements as $element ) {
			$settings = (array) ( $element['settings'] ?? [] );
			$node     = [
				'id'     => $element['id'] ?? '',
				'elType' => $element['elType'] ?? '',
			];

			if ( ! empty( $element['widgetType'] ) ) {
				$node['widgetType'] = $element['widgetType'];
			}

			if ( $this->is_atomic( $element ) ) {
				$node['atomic'] = true;
			}

			$label = $this->element_label( $settings );

			if ( '' !== $label ) {
				$node['label'] = $label;
			}

			if ( $include_settings ) {
				$node['settings'] = (object) $settings;
			}

			if ( ! empty( $element['elements'] ) ) {
				$node['elements'] = $this->outline( $element['elements'], $include_settings );
			}

			$outline[] = $node;
		}

		return $outline;
	}

	/**
	 * Get a short label from an element's settings.
	 *
	 * @since 1.1.0
	 * @access private
	 *
	 * @param array $settings Element settings.
	 * @return string
	 */
	private function element_label( array $settings ) {
		foreach ( [ '_title', 'title', 'text', 'editor', 'heading', 'button_text' ] as $key ) {
			if ( ! empty( $settings[ $key ] ) && is_string( $settings[ $key ] ) ) {
				return wp_html_excerpt( wp_strip_all_tags( $settings[ $key ] ), 60, '…' );
			}
		}

		return '';
	}

	/**
	 * Find the index path of an element.
	 *
	 * @since 1.1.0
	 * @access private
	 *
	 * @param array  $elements Elements.
	 * @param string $id       Element ID.
	 * @param array  $path     Path so far.
	 * @return int[]|null
	 */
	private function find_path( array $elements, $id, array $path = [] ) {
		foreach ( $elements as $index => $element ) {
			$current = array_merge( $path, [ $index ] );

			if ( isset( $element['id'] ) && (string) $element['id'] === (string) $id ) {
				return $current;
			}

			if ( ! empty( $element['elements'] ) && is_array( $element['elements'] ) ) {
				$found = $this->find_path( $element['elements'], $id, $current );

				if ( null !== $found ) {
					return $found;
				}
			}
		}

		return null;
	}

	/**
	 * Get the element at an index path.
	 *
	 * @since 1.1.0
	 * @access private
	 *
	 * @param array $tree Element tree.
	 * @param int[] $path Index path.
	 * @return array
	 */
	private function get_at( array $tree, array $path ) {
		$last = array_pop( $path );

		foreach ( $path as $index ) {
			$tree = $tree[ $index ]['elements'];
		}

		return $tree[ $last ];
	}

	/**
	 * Get a reference to the children list of the element at an index path; an empty path is the root.
	 *
	 * @since 1.1.0
	 * @access private
	 *
	 * @param array $tree Element tree.
	 * @param int[] $path Index path.
	 * @return array
	 */
	private function &children_at( array &$tree, array $path ) {
		$list = &$tree;

		foreach ( $path as $index ) {
			if ( ! isset( $list[ $index ]['elements'] ) || ! is_array( $list[ $index ]['elements'] ) ) {
				$list[ $index ]['elements'] = [];
			}

			$list = &$list[ $index ]['elements'];
		}

		return $list;
	}

	/**
	 * Collect the element IDs of a tree.
	 *
	 * @since 1.1.0
	 * @access private
	 *
	 * @param array $elements Elements.
	 * @param array $ids      IDs so far.
	 * @return array IDs as keys.
	 */
	private function collect_ids( array $elements, array $ids = [] ) {
		foreach ( $elements as $element ) {
			if ( isset( $element['id'] ) ) {
				$ids[ (string) $element['id'] ] = true;
			}

			if ( ! empty( $element['elements'] ) && is_array( $element['elements'] ) ) {
				$ids = $this->collect_ids( $element['elements'], $ids );
			}
		}

		return $ids;
	}

	/**
	 * Generate an unused element ID.
	 *
	 * @since 1.1.0
	 * @access private
	 *
	 * @param array $ids Element IDs in use, changed in place.
	 * @return string
	 */
	private function new_id( array &$ids ) {
		do {
			$id = substr( md5( wp_rand() . microtime() ), 0, 7 );
		} while ( isset( $ids[ $id ] ) );

		$ids[ $id ] = true;

		return $id;
	}

	/**
	 * Apply display location input to template settings.
	 *
	 * @since 1.1.0
	 * @access private
	 *
	 * @param string $type     Template type.
	 * @param array  $settings Template settings.
	 * @param array  $display  Display location input.
	 * @param array  $warnings Warnings, changed in place.
	 * @return array|WP_Error
	 */
	private function apply_display( $type, array $settings, array $display, array &$warnings ) {
		$allowed = array_map( 'strval', array_keys( $this->get_display_option_labels( $type ) ) );

		$suffixes = [
			'include' => 'inc',
			'exclude' => 'exc',
		];

		foreach ( $suffixes as $field => $suffix ) {
			if ( ! array_key_exists( $field, $display ) ) {
				continue;
			}

			$keys    = array_values( array_unique( array_map( 'strval', (array) $display[ $field ] ) ) );
			$invalid = array_diff( $keys, $allowed );

			if ( $invalid ) {
				return new WP_Error( 'archt_invalid_location', sprintf( 'Unknown %1$s location for a %2$s template: %3$s. Use architect/get-display-options.', $field, $type, implode( ', ', $invalid ) ) );
			}

			$settings[ 'display_conditions_' . $suffix ] = $keys;
		}

		$lists = [
			'include_posts' => [ 'selected_posts_inc', 'post' ],
			'exclude_posts' => [ 'selected_posts_exc', 'post' ],
			'include_terms' => [ 'selected_terms_inc', 'term' ],
			'exclude_terms' => [ 'selected_terms_exc', 'term' ],
		];

		foreach ( $lists as $field => $target ) {
			if ( ! array_key_exists( $field, $display ) ) {
				continue;
			}

			$ids     = array_values( array_unique( array_filter( array_map( 'absint', (array) $display[ $field ] ) ) ) );
			$missing = array_filter(
				$ids,
				function ( $id ) use ( $target ) {
					$object = 'post' === $target[1] ? get_post( $id ) : get_term( $id );

					return ! $object || is_wp_error( $object );
				}
			);

			if ( $missing ) {
				return new WP_Error( 'archt_invalid_ids', sprintf( '%1$s not found: %2$s.', $field, implode( ', ', $missing ) ) );
			}

			$settings[ $target[0] ] = array_map( 'strval', $ids );
		}

		foreach ( [ 'inc', 'exc' ] as $suffix ) {
			$keys = (array) ( $settings[ 'display_conditions_' . $suffix ] ?? [] );

			if ( in_array( 'specific', $keys, true ) && empty( $settings[ 'selected_posts_' . $suffix ] ) ) {
				$warnings[] = sprintf( '"specific" is set without %s, so it matches nothing.', 'inc' === $suffix ? 'include_posts' : 'exclude_posts' );
			}

			if ( in_array( 'specific_terms', $keys, true ) && empty( $settings[ 'selected_terms_' . $suffix ] ) ) {
				$warnings[] = sprintf( '"specific_terms" is set without %s, so it matches nothing.', 'inc' === $suffix ? 'include_terms' : 'exclude_terms' );
			}
		}

		if ( empty( $settings['display_conditions_inc'] ) ) {
			$warnings[] = 'No include location is set, so the template does not display anywhere.';
		}

		return $settings;
	}

	/**
	 * Get the display location labels a template type accepts, without group headings.
	 *
	 * @since 1.1.0
	 * @access private
	 *
	 * @param string $type Template type.
	 * @return array
	 */
	private function get_display_option_labels( $type ) {
		$scope   = [
			'single'  => 'singular',
			'archive' => 'archive',
		];
		$options = ARCHT_Helper::get_display_conditions_options( $scope[ $type ] ?? 'all' );
		$labels  = [];

		foreach ( $options as $key => $label ) {
			if ( 0 !== strpos( (string) $key, 'header_' ) ) {
				$labels[ $key ] = html_entity_decode( $label, ENT_QUOTES, 'UTF-8' );
			}
		}

		return $labels;
	}

	/**
	 * Describe a template.
	 *
	 * @since 1.1.0
	 * @access public
	 *
	 * @param WP_Post $post Template post.
	 * @return array
	 */
	public function describe_template( $post ) {
		$settings = $this->get_page_settings( $post->ID );
		$display  = [];
		$lists    = [
			'include'       => 'display_conditions_inc',
			'exclude'       => 'display_conditions_exc',
			'include_posts' => 'selected_posts_inc',
			'exclude_posts' => 'selected_posts_exc',
			'include_terms' => 'selected_terms_inc',
			'exclude_terms' => 'selected_terms_exc',
		];

		foreach ( $lists as $field => $key ) {
			$value = $settings[ $key ] ?? [];
			// Legacy comma-separated value.
			$value = is_string( $value ) ? array_filter( explode( ',', $value ) ) : (array) $value;

			if ( $value ) {
				$display[ $field ] = false === strpos( $field, '_' ) ? array_values( array_map( 'strval', $value ) ) : array_values( array_map( 'absint', $value ) );
			}
		}

		return [
			'template_id'     => $post->ID,
			'title'           => $post->post_title,
			'type'            => get_post_meta( $post->ID, '_elementor_template_type', true ),
			'status'          => $post->post_status,
			'display'         => (object) $display,
			'display_summary' => ARCHT_Helper::get_display_conditions_summary( $settings, 'inc' ),
			'exclude_summary' => ARCHT_Helper::get_display_conditions_summary( $settings, 'exc' ),
			'modified'        => get_post_modified_time( 'c', true, $post ),
			'edit_url'        => add_query_arg(
				[
					'post'   => $post->ID,
					'action' => 'elementor',
				],
				admin_url( 'post.php' )
			),
		];
	}

	/**
	 * Get an Architect template document.
	 *
	 * @since 1.1.0
	 * @access private
	 *
	 * @param int $id Template ID.
	 * @return \Elementor\Core\Base\Document|WP_Error
	 */
	private function get_template( $id ) {
		$id = absint( $id );

		if ( ! $id || 'elementor_library' !== get_post_type( $id ) || ! in_array( get_post_meta( $id, '_elementor_template_type', true ), self::TEMPLATE_TYPES, true ) ) {
			return new WP_Error( 'archt_template_not_found', 'Architect template not found. Use architect/list-templates.' );
		}

		return $this->get_document( $id );
	}

	/**
	 * Get an Elementor document.
	 *
	 * @since 1.1.0
	 * @access private
	 *
	 * @param int $id Post ID.
	 * @return \Elementor\Core\Base\Document|WP_Error
	 */
	private function get_document( $id ) {
		$document = \Elementor\Plugin::$instance->documents->get( absint( $id ), false );

		if ( ! $document || ! $document->is_built_with_elementor() ) {
			return new WP_Error( 'archt_document_not_found', 'No Elementor document with this ID.' );
		}

		return $document;
	}

	/**
	 * Turn ability input, nested objects included, into arrays.
	 *
	 * @since 1.1.0
	 * @access private
	 *
	 * @param mixed $input Ability input.
	 * @return array
	 */
	private function normalize_input( $input ) {
		if ( ! is_array( $input ) && ! is_object( $input ) ) {
			return [];
		}

		$input = json_decode( wp_json_encode( $input ), true );

		return is_array( $input ) ? $input : [];
	}

	/**
	 * Get a document's saved page settings.
	 *
	 * @since 1.1.0
	 * @access private
	 *
	 * @param int $id Post ID.
	 * @return array
	 */
	private function get_page_settings( $id ) {
		$settings = get_post_meta( $id, '_elementor_page_settings', true );

		return is_array( $settings ) ? $settings : [];
	}

	/**
	 * Save a document through Elementor.
	 *
	 * @since 1.1.0
	 * @access private
	 *
	 * @param \Elementor\Core\Base\Document $document Document.
	 * @param array                         $data     Settings and elements.
	 * @return true|WP_Error
	 */
	private function save_document( $document, array $data ) {
		try {
			$saved = $document->save( $data );
		} catch ( \Exception $e ) {
			return new WP_Error( 'archt_save_failed', $e->getMessage() );
		}

		return $saved ? true : new WP_Error( 'archt_save_failed', 'You are not allowed to edit this document.' );
	}
}
new ARCHT_MCP();
