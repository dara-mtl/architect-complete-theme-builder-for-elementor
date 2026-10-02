<?php
/**
 * Bare template render for the Architect Theme Builder card previews.
 *
 * @package ARCHT_Widgets
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$archt_template_id = isset( $_GET['archt_thumb'] ) ? absint( $_GET['archt_thumb'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$archt_nonce       = isset( $_GET['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ) : '';

if ( ! $archt_template_id || ! wp_verify_nonce( $archt_nonce, 'archt_thumb' ) || ! current_user_can( 'edit_post', $archt_template_id ) || 'elementor_library' !== get_post_type( $archt_template_id ) ) {
	wp_die( '', '', 403 );
}

// Keep cache and optimization plugins from adding scripts after our output buffer.
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals
defined( 'DONOTCACHEPAGE' ) || define( 'DONOTCACHEPAGE', true );
defined( 'DONOTMINIFY' ) || define( 'DONOTMINIFY', true );
defined( 'DONOTROCKETOPTIMIZE' ) || define( 'DONOTROCKETOPTIMIZE', true );
do_action( 'litespeed_disable_all', 'Architect template preview' );
// phpcs:enable
add_filter( 'autoptimize_filter_noptimize', '__return_true' );

show_admin_bar( false );
wp_enqueue_style( 'archt-thumbnail', ARCHT_Elementor::asset_url( 'assets/css/backend/archt-thumbnail.css' ), [], ARCHT_Elementor::VERSION );

remove_action( 'wp_head', 'wp_print_head_scripts', 9 );
remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
remove_action( 'wp_footer', 'wp_print_footer_scripts', 20 );

// Previews are static: no script needs to run in them.
ob_start(
	function ( $html ) {
		$html = preg_replace( '#<script\b[^>]*>.*?</script>#is', '', $html );
		$html = preg_replace( '#(<link\b[^>]*?)media\s*=\s*(["\'])print\2([^>]*?onload\s*=\s*["\'][^"\']*this\.media)#i', '$1media=$2all$2$3', $html );
		return preg_replace( '#(<[a-z][^>]*?)\s+on[a-z]+\s*=\s*("[^"]*"|\'[^\']*\')#i', '$1', $html );
	}
);

$archt_type    = sanitize_html_class( get_post_meta( $archt_template_id, '_elementor_template_type', true ) );
$archt_content = \Elementor\Plugin::$instance->frontend->get_builder_content_for_display( $archt_template_id, true );
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="robots" content="noindex">
	<?php wp_head(); ?>
</head>
<body <?php body_class( 'archt-thumb archt-thumb-' . $archt_type ); ?>>
<?php
echo $archt_content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
wp_footer();
?>
</body>
</html>
<?php
ob_end_flush();
