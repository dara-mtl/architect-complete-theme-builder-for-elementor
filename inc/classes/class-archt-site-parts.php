<?php
/**
 * Architect Theme Builder admin page.
 *
 * @package ARCHT_Widgets
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ARCHT_Elementor::is_pro_active() ) {
	return;
}

/**
 * Lists the Architect templates by type, with their display locations.
 *
 * Registered as a top-level menu: Elementor 4.3 re-registers Templates
 * submenus under its own menu, which breaks the page URL.
 */
class ARCHT_Site_Parts {

	const SLUG = 'archt-theme-builder';

	/**
	 * Admin page hook suffix.
	 *
	 * @var string
	 */
	private $hook = '';

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function __construct() {
		add_action( 'admin_menu', [ $this, 'register_page' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_styles' ] );
		add_filter( 'admin_body_class', [ $this, 'body_class' ] );
		add_action( 'template_redirect', [ $this, 'render_thumbnail' ], 0 );
	}

	/**
	 * Render a bare template for a card preview on ?archt_thumb=ID requests.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function render_thumbnail() {
		if ( empty( $_GET['archt_thumb'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return;
		}

		require_once ARCHT_PLUGIN_DIR . 'inc/classes/templates/archt-thumbnail.php';
		exit;
	}

	/**
	 * Get the card preview URL of a template.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @param int $post_id Template ID.
	 * @return string
	 */
	private function get_thumbnail_url( $post_id ) {
		return add_query_arg(
			[
				'archt_thumb' => $post_id,
				'_wpnonce'    => wp_create_nonce( 'archt_thumb' ),
			],
			home_url( '/' )
		);
	}

	/**
	 * Template types, keyed by elementor_library_type slug.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @return array
	 */
	private function get_types() {
		return [
			'header'  => [
				'label' => esc_html__( 'Headers', 'architect-complete-theme-builder-for-elementor' ),
				'icon'  => 'eicon-header',
			],
			'footer'  => [
				'label' => esc_html__( 'Footers', 'architect-complete-theme-builder-for-elementor' ),
				'icon'  => 'eicon-footer',
			],
			'single'  => [
				'label' => esc_html__( 'Singles', 'architect-complete-theme-builder-for-elementor' ),
				'icon'  => 'eicon-single-post',
			],
			'archive' => [
				'label' => esc_html__( 'Archives', 'architect-complete-theme-builder-for-elementor' ),
				'icon'  => 'eicon-archive',
			],
			'popup'   => [
				'label' => esc_html__( 'Popups', 'architect-complete-theme-builder-for-elementor' ),
				'icon'  => 'eicon-lightbox-expand',
			],
		];
	}

	/**
	 * Register the menu page right below Elementor's (position 2).
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function register_page() {
		$this->hook = (string) add_menu_page(
			esc_html__( 'Architect Theme Builder', 'architect-complete-theme-builder-for-elementor' ),
			esc_html__( 'Architect', 'architect-complete-theme-builder-for-elementor' ),
			'edit_posts',
			self::SLUG,
			[ $this, 'render_page' ],
			'dashicons-layout',
			3
		);
	}

	/**
	 * Add the page body class.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $classes Admin body classes.
	 * @return string
	 */
	public function body_class( $classes ) {
		$screen = get_current_screen();
		return $screen && $screen->id === $this->hook ? $classes . ' archt-theme-builder-page' : $classes;
	}

	/**
	 * Enqueue the page styles.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $hook Current admin page hook.
	 */
	public function enqueue_styles( $hook ) {
		if ( $hook !== $this->hook ) {
			return;
		}

		if ( ! wp_style_is( 'elementor-icons', 'registered' ) ) {
			wp_register_style( 'elementor-icons', ELEMENTOR_ASSETS_URL . 'lib/eicons/css/elementor-icons.min.css', [], ELEMENTOR_VERSION );
		}

		wp_enqueue_style( 'archt-site-parts', ARCHT_Elementor::asset_url( 'assets/css/backend/archt-site-parts.css' ), [ 'elementor-icons' ], ARCHT_Elementor::VERSION );
	}

	/**
	 * Render the page.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function render_page() {
		$types   = $this->get_types();
		$current = isset( $_GET['type'] ) ? sanitize_key( wp_unslash( $_GET['type'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$current = isset( $types[ $current ] ) ? $current : '';
		$shown   = $current ? [ $current => $types[ $current ] ] : $types;
		$counts  = [];

		foreach ( $types as $type => $data ) {
			$counts[ $type ] = $this->get_templates( $type );
		}
		?>
		<hr class="wp-header-end">
		<div class="archt-sp">
			<nav class="archt-sp-nav">
				<p class="archt-sp-nav-title"><?php esc_html_e( 'Site Parts', 'architect-complete-theme-builder-for-elementor' ); ?></p>
				<a href="<?php echo esc_url( $this->get_page_url() ); ?>" class="<?php echo '' === $current ? 'is-active' : ''; ?>"><i class="eicon-site-search" aria-hidden="true"></i><?php esc_html_e( 'All Parts', 'architect-complete-theme-builder-for-elementor' ); ?><span class="archt-sp-count"><?php echo esc_html( array_sum( array_map( 'count', $counts ) ) ); ?></span></a>
				<?php foreach ( $types as $type => $data ) : ?>
					<a href="<?php echo esc_url( $this->get_page_url( $type ) ); ?>" class="<?php echo $type === $current ? 'is-active' : ''; ?>"><i class="<?php echo esc_attr( $data['icon'] ); ?>" aria-hidden="true"></i><?php echo esc_html( $data['label'] ); ?><span class="archt-sp-count"><?php echo esc_html( count( $counts[ $type ] ) ); ?></span></a>
				<?php endforeach; ?>
			</nav>

			<div class="archt-sp-main">
				<h1><?php esc_html_e( 'Architect Theme Builder', 'architect-complete-theme-builder-for-elementor' ); ?></h1>
				<p class="archt-sp-intro"><?php esc_html_e( 'Headers, footers, single and archive layouts and popups built with Architect. Each part only shows up where its Display Location says.', 'architect-complete-theme-builder-for-elementor' ); ?></p>
				<?php foreach ( $shown as $type => $data ) : ?>
					<section class="archt-sp-section">
						<h2><?php echo esc_html( $data['label'] ); ?></h2>
						<div class="archt-sp-grid">
							<a class="archt-sp-card archt-sp-add" href="<?php echo esc_url( \Elementor\Plugin::$instance->documents->get_create_new_post_url( 'elementor_library', $type ) ); ?>">
								<i class="eicon-plus-circle" aria-hidden="true"></i>
								<?php esc_html_e( 'Add New', 'architect-complete-theme-builder-for-elementor' ); ?>
							</a>
							<?php foreach ( $counts[ $type ] as $post ) : ?>
								<?php $this->render_card( $post, $data ); ?>
							<?php endforeach; ?>
						</div>
					</section>
				<?php endforeach; ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Render a template card.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @param WP_Post $post Template post.
	 * @param array   $data Type label and icon.
	 */
	private function render_card( $post, $data ) {
		$document = \Elementor\Plugin::$instance->documents->get( $post->ID );
		$settings = (array) get_post_meta( $post->ID, '_elementor_page_settings', true );
		$include  = \ARCHT\Inc\Classes\ARCHT_Helper::get_display_conditions_summary( $settings, 'inc' );
		$exclude  = \ARCHT\Inc\Classes\ARCHT_Helper::get_display_conditions_summary( $settings, 'exc' );
		$status   = get_post_status_object( $post->post_status );
		$language = $this->get_language( $post->ID );
		?>
		<div class="archt-sp-card">
			<div class="archt-sp-thumb">
				<?php if ( has_post_thumbnail( $post ) ) : ?>
					<?php echo get_the_post_thumbnail( $post, 'medium' ); ?>
				<?php else : ?>
					<iframe class="archt-sp-frame" src="<?php echo esc_url( $this->get_thumbnail_url( $post->ID ) ); ?>" loading="lazy" tabindex="-1" aria-hidden="true" onload="this.classList.add('is-loaded')"></iframe>
					<i class="<?php echo esc_attr( $data['icon'] ); ?>" aria-hidden="true"></i>
				<?php endif; ?>
				<?php if ( 'publish' !== $post->post_status && $status ) : ?>
					<span class="archt-sp-status"><?php echo esc_html( $status->label ); ?></span>
				<?php endif; ?>
				<?php if ( $language ) : ?>
					<span class="archt-sp-lang"><?php echo esc_html( strtoupper( $language ) ); ?></span>
				<?php endif; ?>
			</div>
			<div class="archt-sp-body">
				<h3><?php echo esc_html( get_the_title( $post ) ? get_the_title( $post ) : __( '(no title)', 'architect-complete-theme-builder-for-elementor' ) ); ?></h3>
				<?php if ( $include ) : ?>
					<p class="archt-sp-where"><strong><?php esc_html_e( 'Display Location:', 'architect-complete-theme-builder-for-elementor' ); ?></strong> <?php echo esc_html( $include ); ?></p>
					<?php if ( $exclude ) : ?>
						<p class="archt-sp-where"><strong><?php esc_html_e( 'Excluded Locations:', 'architect-complete-theme-builder-for-elementor' ); ?></strong> <?php echo esc_html( $exclude ); ?></p>
					<?php endif; ?>
				<?php else : ?>
					<p class="archt-sp-where archt-sp-warning"><i class="eicon-warning-full" aria-hidden="true"></i><?php esc_html_e( 'No Display Location, not shown anywhere.', 'architect-complete-theme-builder-for-elementor' ); ?></p>
				<?php endif; ?>
			</div>
			<div class="archt-sp-actions">
				<?php if ( $document && $document->is_editable_by_current_user() ) : ?>
					<a class="button button-primary" href="<?php echo esc_url( $document->get_edit_url() ); ?>"><?php esc_html_e( 'Edit', 'architect-complete-theme-builder-for-elementor' ); ?></a>
				<?php endif; ?>
				<?php if ( current_user_can( 'delete_post', $post->ID ) ) : ?>
					<a class="archt-sp-trash" href="<?php echo esc_url( get_delete_post_link( $post->ID ) ); ?>"><?php esc_html_e( 'Trash', 'architect-complete-theme-builder-for-elementor' ); ?></a>
				<?php endif; ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Get a template's language code from Polylang or WPML.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @param int $post_id Template ID.
	 * @return string
	 */
	private function get_language( $post_id ) {
		if ( function_exists( 'pll_get_post_language' ) ) {
			return (string) pll_get_post_language( $post_id );
		}

		$details = apply_filters( 'wpml_post_language_details', null, $post_id );

		return is_array( $details ) && ! empty( $details['language_code'] ) ? $details['language_code'] : '';
	}

	/**
	 * Get the templates of a type.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @param string $type elementor_library_type slug.
	 * @return WP_Post[]
	 */
	private function get_templates( $type ) {
		return get_posts(
			[
				'post_type'      => 'elementor_library',
				'post_status'    => [ 'publish', 'draft', 'pending', 'private', 'future' ],
				'posts_per_page' => -1,
				'orderby'        => 'modified',
				'no_found_rows'  => true,
				'tax_query'      => [ // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
					[
						'taxonomy' => 'elementor_library_type',
						'field'    => 'slug',
						'terms'    => $type,
					],
				],
			]
		);
	}

	/**
	 * Get the page URL, optionally filtered by type.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @param string $type Template type.
	 * @return string
	 */
	private function get_page_url( $type = '' ) {
		$url = admin_url( 'admin.php?page=' . self::SLUG );
		return $type ? add_query_arg( 'type', $type, $url ) : $url;
	}
}
new ARCHT_Site_Parts();
