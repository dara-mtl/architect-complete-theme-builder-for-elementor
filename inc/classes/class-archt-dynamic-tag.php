<?php
/**
 * Registers the dynamic tags.
 *
 * @package ARCHT_Widgets
 * @since 1.0.0
 */

namespace ARCHT_Dynamic_Tag\Classes;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Dynamic tags registrar.
 *
 * @since 1.0.0
 */
class ARCHT_Dynamic_Tag {
	const TAG_DIR       = __DIR__ . '/../../dynamic-tags/';
	const TAG_NAMESPACE = 'ARCHT_Dynamic_Tag\\Tags\\';

	/**
	 * Architect-only tags, always registered.
	 *
	 * @var array
	 */
	private $archt_tags = [
		'archive-description' => 'Archive_Description',
		'archive-title'       => 'Archive_Title',
		'archive-url'         => 'Archive_URL',
		'site-logo'           => 'Site_Logo',
		'site-tagline'        => 'Site_Tagline',
		'site-title'          => 'Site_Title',
		'site-url'            => 'Site_URL',
	];

	/**
	 * Tags shared with BPFWE, registered only when BPFWE is inactive.
	 *
	 * @var array
	 */
	private $shared_tags = [
		'custom-field'        => 'Custom_Field',
		'image-custom-field'  => 'Image_Custom_Field',
		'repeater'            => 'Repeater',
		'post-content'        => 'Post_Content',
		'shortcode'           => 'Shortcode',
		'post-title'          => 'Post_Title',
		'post-date'           => 'Post_Date',
		'post-url'            => 'Post_URL',
		'pages-url'           => 'Pages_URL',
		'post-featured-image' => 'Post_Featured_Image',
		'post-excerpt'        => 'Post_Excerpt',
		'post-terms'          => 'Post_Terms',
		'author-meta'         => 'Author_Info_Meta',
		'user-meta'           => 'User_Meta',
		'tax-meta'            => 'Taxonomy_Meta',
	];

	/**
	 * Shared tags that don't overlap Elementor Pro's, registered alongside it.
	 *
	 * @var string[]
	 */
	private $pro_safe_tags = [ 'tax-meta', 'user-meta', 'image-custom-field', 'custom-field', 'repeater', 'post-content' ];

	/**
	 * Constructor to initialize the dynamic tag registration.
	 *
	 * Hooks into Elementor's dynamic tag registration action.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function __construct() {
		if ( version_compare( ELEMENTOR_VERSION, '3.5.0', '<' ) ) {
			add_action( 'elementor/dynamic_tags/register_tags', [ $this, 'register_tags' ] );
		} else {
			add_action( 'elementor/dynamic_tags/register', [ $this, 'register_tags' ] );
		}
	}

	/**
	 * Registers custom dynamic tags with Elementor.
	 *
	 * Dynamically includes and registers tag classes based on the tags list.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param \Elementor\DynamicTags_Manager $dynamic_tags Elementor's dynamic tags manager instance.
	 */
	public function register_tags( $dynamic_tags ) {
		$bpfwe_active = class_exists( 'BPFWE_Dynamic_Tag\\Classes\\BPFWE_Dynamic_Tag' );
		$pro_active   = defined( 'ELEMENTOR_PRO_VERSION' );

		if ( ! $pro_active ) {
			\Elementor\Plugin::$instance->dynamic_tags->register_group( 'site', [ 'title' => esc_html__( 'Site', 'architect-complete-theme-builder-for-elementor' ) ] );
			\Elementor\Plugin::$instance->dynamic_tags->register_group( 'archive', [ 'title' => esc_html__( 'Archive', 'architect-complete-theme-builder-for-elementor' ) ] );

			$this->register_tag_list( $dynamic_tags, $this->archt_tags );
		}

		if ( ! $bpfwe_active ) {
			\Elementor\Plugin::$instance->dynamic_tags->register_group( 'bpfwe-dynamic-tags', [ 'title' => esc_html__( 'Custom Dynamic Tags', 'architect-complete-theme-builder-for-elementor' ) ] );
			\Elementor\Plugin::$instance->dynamic_tags->register_group( 'post', [ 'title' => esc_html__( 'Post', 'architect-complete-theme-builder-for-elementor' ) ] );
			\Elementor\Plugin::$instance->dynamic_tags->register_group( 'author', [ 'title' => esc_html__( 'Author', 'architect-complete-theme-builder-for-elementor' ) ] );
			\Elementor\Plugin::$instance->dynamic_tags->register_group( 'user', [ 'title' => esc_html__( 'User', 'architect-complete-theme-builder-for-elementor' ) ] );
			\Elementor\Plugin::$instance->dynamic_tags->register_group( 'taxonomy', [ 'title' => esc_html__( 'Taxonomy', 'architect-complete-theme-builder-for-elementor' ) ] );

			$tags = $pro_active ? array_intersect_key( $this->shared_tags, array_flip( $this->pro_safe_tags ) ) : $this->shared_tags;
			$this->register_tag_list( $dynamic_tags, $tags );
		}
	}

	/**
	 * Include and register a file slug => class name map of tags.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @param \Elementor\Core\DynamicTags\Manager $dynamic_tags Dynamic tags manager.
	 * @param array                               $tags         File slug => class name.
	 */
	private function register_tag_list( $dynamic_tags, array $tags ) {
		foreach ( $tags as $file => $class_name ) {
			$full_class_name = self::TAG_NAMESPACE . $class_name;
			$full_file       = self::TAG_DIR . $file . '.php';

			if ( ! file_exists( $full_file ) ) {
				continue;
			}

			require_once $full_file;

			if ( ! class_exists( $full_class_name ) ) {
				continue;
			}

			if ( version_compare( ELEMENTOR_VERSION, '3.5.0', '<' ) ) {
				$dynamic_tags->register_tag( new $full_class_name() );
			} else {
				$dynamic_tags->register( new $full_class_name() );
			}
		}
	}
}

new ARCHT_Dynamic_Tag();
