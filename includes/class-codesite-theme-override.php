<?php
/**
 * Theme override for CodeSite.
 *
 * Handles bypassing the active theme when needed.
 */
class CodeSite_Theme_Override {

    /**
     * Check if theme override is enabled.
     *
     * @return bool
     */
    public function is_enabled() {
        return (bool) CodeSite_Database::get_setting( 'theme_override', false );
    }

    /**
     * Check if CodeSite is enabled.
     *
     * @return bool
     */
    public function is_codesite_enabled() {
        return (bool) CodeSite_Database::get_setting( 'enabled', true );
    }

    /**
     * Check if CodeSite should render the current request.
     *
     * @return bool
     */
    public function should_render() {
        if ( ! $this->is_codesite_enabled() ) {
            return false;
        }

        if ( is_admin() ) {
            return false;
        }

        // If theme override is enabled, always render (using default layouts).
        if ( $this->is_enabled() ) {
            return true;
        }

        // Check for override on current post.
        if ( is_singular() ) {
            $override = CodeSite_Overrides::get( get_the_ID() );
            if ( $override ) {
                return true;
            }
        }

        // Check for template.
        $template = CodeSite_Templates::get_for_current_request();
        if ( $template ) {
            return true;
        }

        return false;
    }

    /**
     * Maybe override the template.
     *
     * @param string $template Template path.
     *
     * @return string
     */
    public function maybe_override_template( $template ) {
        if ( ! $this->should_render() ) {
            return $template;
        }

        return CODESITE_PATH . 'templates/blank.php';
    }

    /**
     * Maybe dequeue theme assets.
     */
    public function maybe_dequeue_theme_assets() {
        if ( ! $this->should_render() || ! $this->is_enabled() ) {
            return;
        }

        global $wp_styles, $wp_scripts;

        $theme_slug        = get_stylesheet();
        $parent_theme_slug = get_template();

        // Dequeue theme styles.
        if ( isset( $wp_styles->registered ) ) {
            foreach ( $wp_styles->registered as $handle => $style ) {
                if ( $this->is_theme_asset( $style->src, $theme_slug, $parent_theme_slug ) ) {
                    wp_dequeue_style( $handle );
                }
            }
        }

        // Dequeue theme scripts.
        if ( isset( $wp_scripts->registered ) ) {
            foreach ( $wp_scripts->registered as $handle => $script ) {
                if ( $this->is_theme_asset( $script->src, $theme_slug, $parent_theme_slug ) ) {
                    wp_dequeue_script( $handle );
                }
            }
        }
    }

    /**
     * Check if asset belongs to theme.
     *
     * @param string $src              Asset source URL.
     * @param string $theme_slug       Active theme slug.
     * @param string $parent_theme_slug Parent theme slug.
     *
     * @return bool
     */
    private function is_theme_asset( $src, $theme_slug, $parent_theme_slug ) {
        if ( empty( $src ) ) {
            return false;
        }

        $src = strtolower( $src );

        if ( strpos( $src, "/themes/{$theme_slug}/" ) !== false ) {
            return true;
        }

        if ( $parent_theme_slug !== $theme_slug && strpos( $src, "/themes/{$parent_theme_slug}/" ) !== false ) {
            return true;
        }

        return false;
    }

    /**
     * Get theme override settings.
     *
     * @return array
     */
    public function get_settings() {
        return array(
            'enabled'          => $this->is_enabled(),
            'keep_admin_bar'   => CodeSite_Database::get_setting( 'keep_admin_bar', true ),
            'keep_core_styles' => CodeSite_Database::get_setting( 'keep_core_styles', true ),
        );
    }
}
