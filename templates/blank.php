<?php
/**
 * Blank template for CodeSite.
 *
 * This template provides a clean slate for CodeSite rendering.
 */

// Pre-render content to collect CSS before wp_head.
$codesite_content = CodeSite_Renderer::render_current_page();
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
</head>
<body <?php body_class( 'codesite-body' ); ?>>
<?php wp_body_open(); ?>

<?php echo $codesite_content; ?>

<?php wp_footer(); ?>
</body>
</html>
