<?php
/**
 * Single template document.
 *
 * @package ARCHT_Widgets
 * @since 1.0.0
 */

namespace ARCHT_Library\Custom_Documents;

use Elementor\Core\DocumentTypes\Post;
use Elementor\Modules\Library\Documents\Library_Document;
use ARCHT_Library\Inc\Traits\ARCHT_Display_Conditions_Trait;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Single template document.
 *
 * @since 1.0.0
 */
class Single extends Library_Document {
	use ARCHT_Display_Conditions_Trait;

	/**
	 * Get document properties.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return array Document properties.
	 */
	public static function get_properties() {
		$properties = parent::get_properties();

		$properties['support_kit']               = true;
		$properties['show_in_finder']            = true;
		$properties['support_wp_page_templates'] = true;

		return $properties;
	}

	/**
	 * Get the document type.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string
	 */
	public static function get_type() {
		return 'single';
	}

	/**
	 * Get document name.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string Document name.
	 */
	public function get_name() {
		return 'single';
	}

	/**
	 * Get document title.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string Document title.
	 */
	public static function get_title() {
		return esc_html__( 'Single', 'architect-complete-theme-builder-for-elementor' );
	}

	/**
	 * Get the plural title.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string
	 */
	public static function get_plural_title() {
		return esc_html__( 'Singles', 'architect-complete-theme-builder-for-elementor' );
	}

	/**
	 * Get the "Add New" title.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string
	 */
	public static function get_add_new_title() {
		return esc_html__( 'Add New Single Template', 'architect-complete-theme-builder-for-elementor' );
	}

	/**
	 * Get the CSS wrapper selector.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string
	 */
	public function get_css_wrapper_selector() {
		return 'body.elementor-single-' . $this->get_main_id();
	}

	/**
	 * Get the default "Preview As" value: the latest post.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string
	 */
	public static function get_preview_as_default() {
		$latest = get_posts(
			[
				'post_type'        => 'post',
				'numberposts'      => 1,
				'orderby'          => 'date',
				'order'            => 'DESC',
				'suppress_filters' => false,
			]
		);

		return ! empty( $latest ) ? 'post/' . absint( $latest[0]->ID ) : '';
	}

	/**
	 * Get the "Preview As" options.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return array Options keyed by "{post_type}/{post_id}".
	 */
	public static function get_preview_as_options() {
		$post_types = get_post_types(
			[ 'show_in_nav_menus' => true ],
			'objects'
		);

		$options = [];

		foreach ( $post_types as $post_type ) {
			$posts = get_posts(
				[
					'post_type'        => $post_type->name,
					'posts_per_page'   => 10,
					'orderby'          => 'date',
					'order'            => 'DESC',
					'suppress_filters' => false,
				]
			);

			if ( empty( $posts ) ) {
				continue;
			}

			$group = [];

			foreach ( $posts as $post ) {
				$group[ $post_type->name . '/' . absint( $post->ID ) ] = esc_html( $post->post_title );
			}

			$options[] = [
				'label'   => esc_html( $post_type->label ),
				'options' => $group,
			];
		}

		return $options;
	}

	/**
	 * Get the query args for the "Preview As" post.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return array
	 */
	public function get_document_query_args() {
		$settings = $this->get_settings_for_display();
		$source   = isset( $settings['preview_as'] ) ? sanitize_text_field( $settings['preview_as'] ) : '';

		if ( ! empty( $source ) ) {
			$parts     = explode( '/', $source );
			$post_type = isset( $parts[0] ) ? sanitize_key( $parts[0] ) : 'post';
			$post_id   = isset( $parts[1] ) ? absint( $parts[1] ) : 0;

			if ( $post_id ) {
				return [
					'post_type' => $post_type,
					'p'         => $post_id,
				];
			}
		}

		$latest = get_posts(
			[
				'post_type'        => 'post',
				'numberposts'      => 1,
				'orderby'          => 'date',
				'order'            => 'DESC',
				'suppress_filters' => false,
			]
		);

		$args = [ 'post_type' => 'post' ];

		if ( ! empty( $latest ) ) {
			$args['p'] = absint( $latest[0]->ID );
		}

		return $args;
	}

	/**
	 * Switch the global query to the "Preview As" post.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function switch_to_preview_query() {
		$query_args = $this->get_document_query_args();
		\Elementor\Plugin::instance()->db->switch_to_query( $query_args );
	}

	/**
	 * Get elements raw data against the preview post.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param array|null $data              Element data.
	 * @param bool       $with_html_content Whether to include rendered HTML.
	 * @return array
	 */
	public function get_elements_raw_data( $data = null, $with_html_content = false ) {
		$this->switch_to_preview_query();

		$editor_data = parent::get_elements_raw_data( $data, $with_html_content );

		\Elementor\Plugin::instance()->db->restore_current_query();

		return $editor_data;
	}

	/**
	 * Render an element against the preview post.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param array $data Element data.
	 * @return string
	 */
	public function render_element( $data ) {
		$this->switch_to_preview_query();

		$render_html = parent::render_element( $data );

		\Elementor\Plugin::instance()->db->restore_current_query();

		return $render_html;
	}

	/**
	 * Register the document controls.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function register_controls() {
		parent::register_controls();
		$this->remove_control( 'elementor_library_type_filter_type' );
		$this->remove_control( 'elementor_library_type' );

		Post::register_hide_title_control( $this );
		Post::register_style_controls( $this );

		$this->start_controls_section(
			'template_settings',
			[
				'label' => esc_html__( 'Layout', 'architect-complete-theme-builder-for-elementor' ),
				'tab'   => \Elementor\Controls_Manager::TAB_SETTINGS,
			]
		);

		$this->add_control(
			'template',
			[
				'type'    => \Elementor\Controls_Manager::SELECT,
				'label'   => esc_html__( 'Page Layout', 'architect-complete-theme-builder-for-elementor' ),
				'default' => 'default',
				'options' => [
					'default'                 => esc_html__( 'Default', 'architect-complete-theme-builder-for-elementor' ),
					'elementor_header_footer' => esc_html__( 'Elementor Full Width', 'architect-complete-theme-builder-for-elementor' ),
					'elementor_canvas'        => esc_html__( 'Elementor Canvas', 'architect-complete-theme-builder-for-elementor' ),
				],
			]
		);

		$this->end_controls_section();

		$this->register_display_conditions_controls( 'singular' );

		$this->start_controls_section(
			'preview_settings',
			[
				'label' => esc_html__( 'Preview Settings', 'architect-complete-theme-builder-for-elementor' ),
				'tab'   => 'archt_display',
			]
		);

		$this->add_control(
			'preview_as',
			[
				'type'    => \Elementor\Controls_Manager::SELECT,
				'label'   => esc_html__( 'Preview As', 'architect-complete-theme-builder-for-elementor' ),
				'groups'  => static::get_preview_as_options(),
				'default' => static::get_preview_as_default(),
			]
		);

		$this->add_control(
			'apply_preview',
			[
				'type'        => \Elementor\Controls_Manager::BUTTON,
				'label'       => esc_html__( 'Apply & Preview', 'architect-complete-theme-builder-for-elementor' ),
				'label_block' => true,
				'show_label'  => false,
				'text'        => esc_html__( 'Apply & Preview', 'architect-complete-theme-builder-for-elementor' ),
				'event'       => 'elementorThemeBuilder:ApplyPreview',
			]
		);

		$this->end_controls_section();
	}
}
