<?php
/**
 * CSS compiler for CodeSite.
 *
 * Handles CSS scoping and compilation.
 */
class CodeSite_CSS_Compiler {

    /**
     * CSS collection with source info.
     *
     * @var array
     */
    private static $css_collection = array();

    /**
     * Add CSS to the compilation.
     *
     * @param string $css         CSS content.
     * @param string $source_type Source type (global, layout, block, template, override).
     * @param int    $source_id   Source ID.
     * @param string $scope       Optional scope prefix.
     */
    public static function add( $css, $source_type = 'custom', $source_id = 0, $scope = null ) {
        if ( empty( $css ) ) {
            return;
        }

        if ( $scope ) {
            $css = self::scope_css( $css, $scope );
        }

        // Create unique key.
        $key = $source_type . '-' . $source_id;

        // Don't add duplicates.
        if ( isset( self::$css_collection[ $key ] ) ) {
            return;
        }

        self::$css_collection[ $key ] = array(
            'type'    => $source_type,
            'id'      => $source_id,
            'content' => $css,
        );
    }

    /**
     * Add block CSS.
     *
     * @param object $block Block object.
     */
    public static function add_block( $block ) {
        if ( empty( $block->css ) ) {
            return;
        }

        $scope = null;
        if ( $block->css_scope === 'scoped' ) {
            $scope = 'codesite-block-' . $block->id;
        }

        self::add( $block->css, 'block', $block->id, $scope );
    }

    /**
     * Add layout CSS.
     *
     * @param object $layout Layout object.
     */
    public static function add_layout( $layout ) {
        if ( empty( $layout->custom_css ) ) {
            return;
        }

        self::add( $layout->custom_css, 'layout', $layout->id );
    }

    /**
     * Get CSS collection.
     *
     * @return array
     */
    public static function get_collection() {
        return self::$css_collection;
    }

    /**
     * Reset CSS collection.
     */
    public static function reset() {
        self::$css_collection = array();
    }

    /**
     * Output inline CSS with unique IDs (no merging).
     *
     * @return string
     */
    public static function output_inline() {
        $output = '';

        // Global CSS first.
        $global_css = CodeSite_Database::get_setting( 'global_css', '' );
        if ( ! empty( $global_css ) ) {
            $output .= "<style id=\"codesite-global-css\">\n" . $global_css . "\n</style>\n";
        }

        // Each source gets its own style tag.
        foreach ( self::$css_collection as $key => $item ) {
            $style_id = 'codesite-' . sanitize_title( $key ) . '-css';
            $output .= "<style id=\"" . esc_attr( $style_id ) . "\">\n" . $item['content'] . "\n</style>\n";
        }

        return $output;
    }

    /**
     * Get merged CSS for file output.
     *
     * @return string
     */
    public static function get_merged() {
        $css = '';

        // Global CSS.
        $global_css = CodeSite_Database::get_setting( 'global_css', '' );
        if ( $global_css ) {
            $css .= "/* Global CSS */\n" . $global_css . "\n";
        }

        // All collected CSS.
        foreach ( self::$css_collection as $key => $item ) {
            $css .= "\n/* " . ucfirst( $item['type'] ) . " " . $item['id'] . " */\n";
            $css .= $item['content'] . "\n";
        }

        // Minify if enabled.
        if ( CodeSite_Database::get_setting( 'minify_output', false ) ) {
            $css = self::minify( $css );
        }

        return $css;
    }

    /**
     * Get page CSS (for backwards compatibility).
     *
     * @return string
     */
    public static function get_page_css() {
        return self::get_merged();
    }

    /**
     * Get compiled CSS (for backwards compatibility).
     *
     * @return string
     */
    public static function get() {
        $css = '';
        foreach ( self::$css_collection as $item ) {
            $css .= "\n" . $item['content'];
        }
        return $css;
    }

    /**
     * Scope CSS by prefixing selectors.
     *
     * @param string $css    CSS content.
     * @param string $prefix Scope prefix (class name without dot).
     *
     * @return string
     */
    public static function scope_css( $css, $prefix ) {
        // Remove comments.
        $css = preg_replace( '!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $css );

        // Handle @rules.
        $at_rules = array();
        $css      = preg_replace_callback(
            '/@(media|keyframes|supports|font-face)[^{]+\{([^{}]*(\{[^{}]*\}[^{}]*)*)\}/i',
            function ( $matches ) use ( &$at_rules, $prefix ) {
                $at_rule  = $matches[0];
                $at_type  = $matches[1];
                $content  = $matches[2];

                if ( strtolower( $at_type ) === 'media' || strtolower( $at_type ) === 'supports' ) {
                    // Scope selectors inside media queries.
                    $scoped_content = self::scope_selectors( $content, $prefix );
                    $at_rule        = str_replace( $content, $scoped_content, $at_rule );
                }

                $at_rules[] = $at_rule;
                return '/*AT_RULE_PLACEHOLDER*/';
            },
            $css
        );

        // Scope remaining selectors.
        $css = self::scope_selectors( $css, $prefix );

        // Restore @rules.
        foreach ( $at_rules as $at_rule ) {
            $css = preg_replace( '/\/\*AT_RULE_PLACEHOLDER\*\//', $at_rule, $css, 1 );
        }

        return $css;
    }

    /**
     * Scope selectors in CSS.
     *
     * @param string $css    CSS content.
     * @param string $prefix Scope prefix.
     *
     * @return string
     */
    private static function scope_selectors( $css, $prefix ) {
        // Split by closing brace.
        $rules = explode( '}', $css );
        $scoped = array();

        foreach ( $rules as $rule ) {
            $rule = trim( $rule );
            if ( empty( $rule ) ) {
                continue;
            }

            // Split selector and properties.
            $parts = explode( '{', $rule, 2 );
            if ( count( $parts ) !== 2 ) {
                continue;
            }

            $selectors  = $parts[0];
            $properties = $parts[1];

            // Handle multiple selectors.
            $selector_list   = explode( ',', $selectors );
            $scoped_selectors = array();

            foreach ( $selector_list as $selector ) {
                $selector = trim( $selector );
                if ( empty( $selector ) ) {
                    continue;
                }

                // Don't scope certain selectors.
                if ( self::should_skip_scoping( $selector ) ) {
                    $scoped_selectors[] = $selector;
                } else {
                    // Handle :root specially.
                    if ( strpos( $selector, ':root' ) === 0 ) {
                        $selector = str_replace( ':root', ".{$prefix}", $selector );
                    } else {
                        $selector = ".{$prefix} {$selector}";
                    }
                    $scoped_selectors[] = $selector;
                }
            }

            if ( ! empty( $scoped_selectors ) ) {
                $scoped[] = implode( ', ', $scoped_selectors ) . ' { ' . $properties . ' }';
            }
        }

        return implode( "\n", $scoped );
    }

    /**
     * Check if selector should skip scoping.
     *
     * @param string $selector CSS selector.
     *
     * @return bool
     */
    private static function should_skip_scoping( $selector ) {
        // Skip @rules, placeholders, keyframe percentages.
        if ( strpos( $selector, '@' ) === 0 ) {
            return true;
        }
        if ( strpos( $selector, '%' ) !== false ) {
            return true;
        }
        if ( strpos( $selector, 'AT_RULE_PLACEHOLDER' ) !== false ) {
            return true;
        }

        return false;
    }

    /**
     * Minify CSS.
     *
     * @param string $css CSS content.
     *
     * @return string
     */
    public static function minify( $css ) {
        // Remove comments.
        $css = preg_replace( '!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $css );

        // Remove whitespace.
        $css = preg_replace( '/\s+/', ' ', $css );

        // Remove spaces around certain characters.
        $css = preg_replace( '/\s*([{}:;,>+~])\s*/', '$1', $css );

        // Remove trailing semicolons before closing braces.
        $css = str_replace( ';}', '}', $css );

        return trim( $css );
    }
}
