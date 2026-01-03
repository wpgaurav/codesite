<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
</head>
<body <?php body_class( 'codesite-body' ); ?>>
<?php wp_body_open(); ?>

<?php
// Render the page content.
echo CodeSite_Renderer::render_current_page();
?>

<?php wp_footer(); ?>
</body>
</html>
