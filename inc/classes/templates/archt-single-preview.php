<?php
/**
 * Standalone Single template preview, opened by "Apply & Preview".
 *
 * Query args: archt_preview, archt_template_id, archt_preview_id, archt_nonce.
 *
 * @package ARCHT_Widgets
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$archt_template_id = isset( $_GET['archt_template_id'] ) ? absint( $_GET['archt_template_id'] ) : 0;
$archt_preview_id  = isset( $_GET['archt_preview_id'] ) ? absint( $_GET['archt_preview_id'] ) : 0;
$archt_nonce       = isset( $_GET['archt_nonce'] ) ? sanitize_text_field( wp_unslash( $_GET['archt_nonce'] ) ) : '';

if ( ! $archt_template_id || ! $archt_preview_id || ! $archt_nonce ) {
	wp_die( esc_html__( 'Invalid preview request.', 'architect-complete-theme-builder-for-elementor' ), 400 );
}

if ( ! wp_verify_nonce( $archt_nonce, 'archt_single_preview' ) ) {
	wp_die( esc_html__( 'Security check failed.', 'architect-complete-theme-builder-for-elementor' ), 403 );
}

if ( ! current_user_can( 'edit_post', $archt_template_id ) ) {
	wp_die( esc_html__( 'You do not have permission to preview this template.', 'architect-complete-theme-builder-for-elementor' ), 403 );
}

$archt_template = get_post( $archt_template_id );
if ( ! $archt_template || 'elementor_library' !== $archt_template->post_type ) {
	wp_die( esc_html__( 'Template not found.', 'architect-complete-theme-builder-for-elementor' ), 404 );
}

$archt_preview_post = get_post( $archt_preview_id );
if ( ! $archt_preview_post || ! in_array( $archt_preview_post->post_status, [ 'publish', 'private' ], true ) ) {
	wp_die( esc_html__( 'Preview post not found.', 'architect-complete-theme-builder-for-elementor' ), 404 );
}

// Make the preview post the main query, so wp_reset_postdata() calls during render return to it, not the home page.
$archt_query_args = [
	'post_type'   => $archt_preview_post->post_type,
	'post_status' => [ 'publish', 'private' ],
	'lang'        => '',
];

$archt_query_args[ 'page' === $archt_preview_post->post_type ? 'page_id' : 'p' ] = $archt_preview_id;

$archt_query = new WP_Query( $archt_query_args );

// A translation plugin can filter out a post in another language; it is already validated above.
if ( ! $archt_query->have_posts() ) {
	$archt_query->posts      = [ $archt_preview_post ];
	$archt_query->post       = $archt_preview_post;
	$archt_query->post_count = 1;
}

// phpcs:disable WordPress.WP.GlobalVariablesOverride.Prohibited
$GLOBALS['wp_query']     = $archt_query;
$GLOBALS['wp_the_query'] = $GLOBALS['wp_query'];
$GLOBALS['post']         = $archt_preview_post;
// phpcs:enable
setup_postdata( $archt_preview_post );
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>
		<?php
		printf(
			/* translators: 1: post title, 2: template title */
			esc_html__( 'Preview: %1$s - %2$s', 'architect-complete-theme-builder-for-elementor' ),
			esc_html( get_the_title( $archt_preview_id ) ),
			esc_html( get_the_title( $archt_template_id ) )
		);
		?>
	</title>
	<?php wp_head(); ?>
</head>
<body <?php body_class( 'archt-single-preview' ); ?>>
<?php wp_body_open(); ?>

<?php
echo \Elementor\Plugin::$instance->frontend->get_builder_content_for_display( $archt_template_id, true ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
?>

<?php wp_footer(); ?>
</body>
</html>
<?php
wp_reset_postdata();
