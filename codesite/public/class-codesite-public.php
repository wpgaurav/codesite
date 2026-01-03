<?php
/**
 * Public-facing functionality for CodeSite.
 */
class CodeSite_Public {

    /**
     * Enqueue public styles.
     */
    public function enqueue_styles() {
        $theme_override = new CodeSite_Theme_Override();

        if ( ! $theme_override->should_render() ) {
            return;
        }

        // Frontend base styles (optional reset).
        if ( CodeSite_Database::get_setting( 'load_base_styles', true ) ) {
            wp_enqueue_style(
                'codesite-frontend',
                CODESITE_URL . 'assets/css/frontend.css',
                array(),
                CODESITE_VERSION
            );
        }
    }

    /**
     * Enqueue public scripts.
     */
    public function enqueue_scripts() {
        $theme_override = new CodeSite_Theme_Override();

        if ( ! $theme_override->should_render() ) {
            return;
        }

        // Currently no frontend scripts needed.
        // JS is output inline via the renderer.
    }
}
