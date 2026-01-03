<?php
/**
 * CSS compiler for CodeSite.
 *
 * Handles CSS scoping and compilation.
 */
class CodeSite_CSS_Compiler {

    /**
     * Compiled CSS storage.
     *
     * @var string
     */
    private static $compiled_css = '';

    /**
     * Add CSS to the compilation.
     *
     * @param string $css    CSS content.
     * @param string $scope  Optional scope prefix.
     */
    public static function add( $css, $scope = null ) {
        if ( empty( $css ) ) {
            return;
        }

        if ( $scope ) {
            $css = self::scope_css( $css, $scope );
        }

        self::$compiled_css .= "\n" . $css;
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

        if ( $block->css_scope === 'scoped' ) {
            self::add( $block->css, 'codesite-block-' . $block->id );
        } else {
            self::add( $block->css );
        }
    }

    /**
     * Get compiled CSS.
     *
     * @return string
     */
    public static function get() {
        return self::$compiled_css;
    }

    /**
     * Reset compiled CSS.
     */
    public static function reset() {
        self::$compiled_css = '';
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

    /**
     * Get page CSS including global styles.
     *
     * @return string
     */
    public static function get_page_css() {
        $css = '';

        // Global CSS.
        $global_css = CodeSite_Database::get_setting( 'global_css', '' );
        if ( $global_css ) {
            $css .= "/* Global CSS */\n" . $global_css . "\n";
        }

        // Compiled CSS.
        $compiled = self::get();
        if ( $compiled ) {
            $css .= $compiled;
        }

        // Minify if enabled.
        if ( CodeSite_Database::get_setting( 'minify_output', false ) ) {
            $css = self::minify( $css );
        }

        return $css;
    }
}
