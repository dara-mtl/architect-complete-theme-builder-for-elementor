<?php
/**
 * Bare template for the popup editor preview, free of the theme's markup.
 *
 * @package ARCHT_Widgets
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class( 'archt-popup-preview' ); ?>>
<?php
wp_body_open();
\Elementor\Plugin::$instance->modules_manager->get_modules( 'page-templates' )->print_content();
wp_footer();
?>
</body>
</html>
