<?php
/**
 * CSS Snippets dropdown - searchable.
 *
 * Include this in editor templates.
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<div class="codesite-snippets-wrapper">
    <input type="text"
           id="codesite-snippet-search"
           class="codesite-snippet-search"
           placeholder="<?php esc_attr_e( 'Search snippets...', 'codesite' ); ?>"
           autocomplete="off"
           list="codesite-snippets-list">
    <datalist id="codesite-snippets-list">
        <!-- Flexbox -->
        <option value="flex-row"><?php esc_html_e( 'Flex Row', 'codesite' ); ?></option>
        <option value="flex-col"><?php esc_html_e( 'Flex Column', 'codesite' ); ?></option>
        <option value="flex-center"><?php esc_html_e( 'Flex Center', 'codesite' ); ?></option>
        <option value="flex-between"><?php esc_html_e( 'Flex Space Between', 'codesite' ); ?></option>
        <option value="flex-wrap"><?php esc_html_e( 'Flex Wrap', 'codesite' ); ?></option>
        <!-- Grid -->
        <option value="grid-2col"><?php esc_html_e( 'Grid 2 Columns', 'codesite' ); ?></option>
        <option value="grid-3col"><?php esc_html_e( 'Grid 3 Columns', 'codesite' ); ?></option>
        <option value="grid-4col"><?php esc_html_e( 'Grid 4 Columns', 'codesite' ); ?></option>
        <option value="grid-auto"><?php esc_html_e( 'Grid Auto-fit', 'codesite' ); ?></option>
        <option value="grid-12"><?php esc_html_e( 'Grid 12 Column', 'codesite' ); ?></option>
        <!-- Layout -->
        <option value="container"><?php esc_html_e( 'Container', 'codesite' ); ?></option>
        <option value="full-height"><?php esc_html_e( 'Full Height', 'codesite' ); ?></option>
        <option value="sticky-header"><?php esc_html_e( 'Sticky Header', 'codesite' ); ?></option>
        <option value="sticky-footer"><?php esc_html_e( 'Sticky Footer', 'codesite' ); ?></option>
        <!-- Components -->
        <option value="btn"><?php esc_html_e( 'Buttons', 'codesite' ); ?></option>
        <option value="card"><?php esc_html_e( 'Card', 'codesite' ); ?></option>
        <option value="form"><?php esc_html_e( 'Form', 'codesite' ); ?></option>
        <option value="nav"><?php esc_html_e( 'Navigation', 'codesite' ); ?></option>
        <option value="hero"><?php esc_html_e( 'Hero Section', 'codesite' ); ?></option>
        <option value="footer"><?php esc_html_e( 'Footer', 'codesite' ); ?></option>
        <!-- Typography -->
        <option value="text-truncate"><?php esc_html_e( 'Text Truncate', 'codesite' ); ?></option>
        <option value="line-clamp"><?php esc_html_e( 'Line Clamp', 'codesite' ); ?></option>
        <option value="responsive-text"><?php esc_html_e( 'Responsive Text', 'codesite' ); ?></option>
        <!-- Effects -->
        <option value="shadow"><?php esc_html_e( 'Box Shadow', 'codesite' ); ?></option>
        <option value="transition"><?php esc_html_e( 'Transition', 'codesite' ); ?></option>
        <option value="hover-scale"><?php esc_html_e( 'Hover Scale', 'codesite' ); ?></option>
        <option value="gradient-bg"><?php esc_html_e( 'Gradient', 'codesite' ); ?></option>
        <!-- Animations -->
        <option value="fade-in"><?php esc_html_e( 'Fade In', 'codesite' ); ?></option>
        <option value="slide-up"><?php esc_html_e( 'Slide Up', 'codesite' ); ?></option>
        <option value="pulse"><?php esc_html_e( 'Pulse', 'codesite' ); ?></option>
        <option value="spin"><?php esc_html_e( 'Spin', 'codesite' ); ?></option>
        <!-- Responsive -->
        <option value="media-tablet"><?php esc_html_e( 'Media Tablet', 'codesite' ); ?></option>
        <option value="media-mobile"><?php esc_html_e( 'Media Mobile', 'codesite' ); ?></option>
        <option value="hide-mobile"><?php esc_html_e( 'Hide on Mobile', 'codesite' ); ?></option>
        <option value="show-mobile"><?php esc_html_e( 'Show on Mobile', 'codesite' ); ?></option>
        <!-- Utilities -->
        <option value="utilities"><?php esc_html_e( 'Utilities Pack', 'codesite' ); ?></option>
        <option value="reset"><?php esc_html_e( 'CSS Reset', 'codesite' ); ?></option>
    </datalist>
</div>
