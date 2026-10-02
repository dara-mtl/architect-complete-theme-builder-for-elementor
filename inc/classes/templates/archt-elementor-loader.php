<?php
/**
 * Template loader used when Architect renders a content template, or the header and footer on block themes.
 *
 * @package ARCHT_Widgets
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$archt_theme_builder = ARCHT_Theme_Builder::instance();

$archt_header_id    = $archt_theme_builder->get_active_header_id();
$archt_footer_id    = $archt_theme_builder->get_active_footer_id();
$archt_content_id   = $archt_theme_builder->get_active_content_id();
$archt_content_type = $archt_theme_builder->get_active_content_type();
$archt_mode         = $archt_theme_builder->get_template_mode();
$archt_block_theme  = wp_is_block_theme();
$archt_own_document = 'canvas' === $archt_mode || $archt_header_id || $archt_block_theme;

// The document CSS wrapper selectors need these body classes.
add_filter(
	'body_class',
	function ( $classes ) use ( $archt_content_id, $archt_content_type, $archt_mode ) {
		$classes[] = 'canvas' === $archt_mode ? 'elementor-template-canvas' : 'elementor-template-full-width';

		if ( $archt_content_id ) {
			$classes[] = 'elementor-page-' . $archt_content_id;

			if ( $archt_content_type ) {
				$classes[] = 'elementor-' . $archt_content_type . '-' . $archt_content_id;
			}
		}

		return $classes;
	}
);

if ( $archt_own_document ) {
	if ( 'canvas' !== $archt_mode && $archt_header_id ) {
		do_action( 'get_header', null, [] );
	}
	?>
	<!DOCTYPE html>
	<html <?php language_attributes(); ?>>
	<head>
		<meta charset="<?php bloginfo( 'charset' ); ?>">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<?php if ( ! current_theme_supports( 'title-tag' ) ) : ?>
			<title><?php echo esc_html( wp_get_document_title() ); ?></title>
		<?php endif; ?>
		<?php wp_head(); ?>
	</head>
	<body <?php body_class(); ?>>
	<?php
	wp_body_open();

	if ( 'canvas' === $archt_mode ) {
		do_action( 'elementor/page_templates/canvas/before_content' );
	} else {
		do_action( 'elementor/page_templates/header-footer/before_content' );

		if ( $archt_header_id ) {
			$archt_theme_builder->render_elementor_content( $archt_header_id, 'header' );
		} else {
			block_header_area();
		}
	}
} else {
	get_header();
	do_action( 'elementor/page_templates/header-footer/before_content' );
}

echo '<main role="main" class="site-main elementor-theme-builder-content">';

if ( $archt_content_id ) {
	if ( is_singular() ) {
		setup_postdata( get_queried_object() );
	}

	$archt_theme_builder->render_elementor_content( $archt_content_id, 'content' );
} else {
	while ( have_posts() ) :
		the_post();
		the_content();
	endwhile;
}

echo '</main>';

if ( 'canvas' === $archt_mode ) {
	do_action( 'elementor/page_templates/canvas/after_content' );
	wp_footer();
	?>
	</body>
	</html>
	<?php
} elseif ( $archt_footer_id || $archt_block_theme ) {
	do_action( 'elementor/page_templates/header-footer/after_content' );

	if ( $archt_footer_id ) {
		do_action( 'get_footer', null, [] );
		$archt_theme_builder->render_elementor_content( $archt_footer_id, 'footer' );
	} else {
		block_footer_area();
	}

	wp_footer();
	?>
	</body>
	</html>
	<?php
} else {
	do_action( 'elementor/page_templates/header-footer/after_content' );
	get_footer();
}
