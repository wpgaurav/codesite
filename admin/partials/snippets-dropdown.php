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
           placeholder="<?php esc_attr_e( 'Snippets...', 'codesite' ); ?>"
           autocomplete="off"
           list="codesite-snippets-list">
    <datalist id="codesite-snippets-list">
        <!-- Flexbox -->
        <option value="flex-row">Flex Row</option>
        <option value="flex-col">Flex Column</option>
        <option value="flex-center">Flex Center</option>
        <option value="flex-between">Flex Space Between</option>
        <option value="flex-wrap">Flex Wrap</option>
        <!-- Grid -->
        <option value="grid-2col">Grid 2 Columns</option>
        <option value="grid-3col">Grid 3 Columns</option>
        <option value="grid-4col">Grid 4 Columns</option>
        <option value="grid-auto">Grid Auto-fit</option>
        <option value="grid-12">Grid 12 Column</option>
        <!-- Layout -->
        <option value="container">Container</option>
        <option value="full-height">Full Height</option>
        <option value="sticky-header">Sticky Header</option>
        <option value="sticky-footer">Sticky Footer</option>
        <!-- Components -->
        <option value="btn">Button</option>
        <option value="btn-primary">Button Primary</option>
        <option value="btn-secondary">Button Secondary</option>
        <option value="card">Card</option>
        <option value="card-body">Card Body</option>
        <option value="form-input">Form Input</option>
        <option value="form-label">Form Label</option>
        <option value="nav">Navigation</option>
        <option value="nav-links">Nav Links</option>
        <option value="hero">Hero Section</option>
        <option value="footer">Footer</option>
        <!-- Typography -->
        <option value="text-truncate">Text Truncate</option>
        <option value="line-clamp">Line Clamp</option>
        <option value="responsive-text">Responsive Text</option>
        <!-- Effects -->
        <option value="shadow">Box Shadow</option>
        <option value="shadow-lg">Shadow Large</option>
        <option value="transition">Transition</option>
        <option value="transition-fast">Transition Fast</option>
        <option value="hover-scale">Hover Scale</option>
        <option value="gradient-bg">Gradient Background</option>
        <!-- Animations -->
        <option value="fade-in">Fade In</option>
        <option value="slide-up">Slide Up</option>
        <option value="pulse">Pulse</option>
        <option value="spin">Spin</option>
        <option value="keyframe-fade">Keyframe Fade</option>
        <option value="keyframe-slide">Keyframe Slide</option>
        <!-- Responsive -->
        <option value="media-tablet">Media Tablet</option>
        <option value="media-mobile">Media Mobile</option>
        <!-- Utilities -->
        <option value="center-text">Center Text</option>
        <option value="center-block">Center Block</option>
        <option value="hidden">Hidden</option>
        <option value="sr-only">Screen Reader Only</option>
        <option value="reset">CSS Reset</option>
    </datalist>
</div>
