<?php
/**
 * Renderer for CodeSite.
 *
 * Handles rendering blocks, layouts, and templates.
 */
class CodeSite_Renderer {

    /**
     * Collected JS for output.
     *
     * @var array
     */
    private static $js_collection = array();

    /**
     * Current rendering context.
     *
     * @var array
     */
    private static $context = array();

    /**
     * Render a block.
     *
     * @param object|int $block   Block object or ID.
     * @param array      $context Optional context data.
     *
     * @return string
     */
    public static function render_block( $block, $context = array() ) {
        if ( is_numeric( $block ) ) {
            $block = CodeSite_Blocks::get( $block );
        }

        if ( ! $block || $block->status !== 'active' ) {
            return '';
        }

        // Add block CSS.
        CodeSite_CSS_Compiler::add_block( $block );

        // Collect block JS.
        if ( ! empty( $block->js ) ) {
            self::$js_collection[] = array(
                'type'    => 'block',
                'id'      => $block->id,
                'name'    => $block->name,
                'content' => $block->js,
            );
        }

        // Get HTML.
        $html = $block->html;

        // Parse dynamic content.
        $html = CodeSite_Dynamic_Content::parse( $html, $context );

        // Parse shortcodes.
        $html = CodeSite_Shortcode_Parser::parse( $html );

        // Parse Tangible syntax.
        $tangible = new CodeSite_Tangible_Integration();
        $html     = $tangible->parse( $html );

        // Wrap if scoped.
        if ( $block->css_scope === 'scoped' ) {
            $html = sprintf(
                '<div class="codesite-block codesite-block-%d">%s</div>',
                $block->id,
                $html
            );
        }

        return $html;
    }

    /**
     * Render a layout.
     *
     * @param object|int $layout  Layout object or ID.
     * @param array      $context Optional context data.
     *
     * @return string
     */
    public static function render_layout( $layout, $context = array() ) {
        if ( is_numeric( $layout ) ) {
            $layout = CodeSite_Layouts::get( $layout );
        }

        if ( ! $layout || $layout->status !== 'active' ) {
            return '';
        }

        $html = '';

        if ( $layout->use_blocks ) {
            // Render blocks.
            $blocks = CodeSite_Layouts::get_blocks( $layout->id );
            foreach ( $blocks as $block ) {
                $html .= self::render_block( $block, $context );
            }
        } else {
            // Use custom HTML.
            $html = $layout->custom_html;

            // Parse dynamic content.
            $html = CodeSite_Dynamic_Content::parse( $html, $context );

            // Parse shortcodes.
            $html = CodeSite_Shortcode_Parser::parse( $html );

            // Parse Tangible.
            $tangible = new CodeSite_Tangible_Integration();
            $html     = $tangible->parse( $html );
        }

        // Add layout CSS.
        if ( ! empty( $layout->custom_css ) ) {
            CodeSite_CSS_Compiler::add( $layout->custom_css, 'layout', $layout->id );
        }

        // Collect layout JS.
        if ( ! empty( $layout->custom_js ) ) {
            self::$js_collection[] = array(
                'type'    => 'layout',
                'id'      => $layout->id,
                'name'    => $layout->name,
                'content' => $layout->custom_js,
            );
        }

        // Return raw HTML without wrapper div.
        return $html;
    }

    /**
     * Render a template.
     *
     * @param object|int $template Template object or ID.
     * @param array      $context  Optional context data.
     *
     * @return string
     */
    public static function render_template( $template, $context = array() ) {
        if ( is_numeric( $template ) ) {
            $template = CodeSite_Templates::get( $template );
        }

        if ( ! $template || $template->status !== 'active' ) {
            return '';
        }

        self::$context = $context;
        $html          = '';

        // Render header.
        if ( $template->header_layout_id ) {
            $html .= self::render_layout( $template->header_layout_id, $context );
        }

        // Render main content.
        $content_html = '';

        // Content blocks.
        $content_blocks = json_decode( $template->content_blocks, true );
        if ( is_array( $content_blocks ) && ! empty( $content_blocks ) ) {
            foreach ( $content_blocks as $item ) {
                if ( is_numeric( $item ) ) {
                    $content_html .= self::render_block( $item, $context );
                } elseif ( is_string( $item ) ) {
                    // Dynamic content placeholder.
                    $content_html .= CodeSite_Dynamic_Content::parse( $item, $context );
                }
            }
        }

        // Custom HTML.
        if ( ! empty( $template->custom_html ) ) {
            $custom = $template->custom_html;
            $custom = CodeSite_Dynamic_Content::parse( $custom, $context );
            $custom = CodeSite_Shortcode_Parser::parse( $custom );

            $tangible = new CodeSite_Tangible_Integration();
            $custom   = $tangible->parse( $custom );

            $content_html .= $custom;
        }

        $html .= sprintf(
            '<main class="codesite-content codesite-template-%d">%s</main>',
            $template->id,
            $content_html
        );

        // Render footer.
        if ( $template->footer_layout_id ) {
            $html .= self::render_layout( $template->footer_layout_id, $context );
        }

        // Add template CSS.
        if ( ! empty( $template->custom_css ) ) {
            CodeSite_CSS_Compiler::add( $template->custom_css, 'template', $template->id );
        }

        // Collect template JS.
        if ( ! empty( $template->custom_js ) ) {
            self::$js_collection[] = array(
                'type'    => 'template',
                'id'      => $template->id,
                'name'    => $template->name,
                'content' => $template->custom_js,
            );
        }

        return $html;
    }

    /**
     * Render current page.
     *
     * @return string
     */
    public static function render_current_page() {
        // Check for override.
        if ( is_singular() ) {
            $override = CodeSite_Overrides::get( get_the_ID() );
            if ( $override ) {
                return self::render_override( $override );
            }
        }

        // Check for template.
        $template = CodeSite_Templates::get_for_current_request();
        if ( $template ) {
            return self::render_template( $template );
        }

        // Render using default layouts (when theme override is enabled but no template).
        return self::render_with_defaults();
    }

    /**
     * Render page using default layouts.
     *
     * @return string
     */
    public static function render_with_defaults() {
        $html    = '';
        $context = array();

        // Set up post context if on a singular page.
        if ( is_singular() ) {
            $context['post_id'] = get_the_ID();
        }

        // Render default header.
        $default_header = CodeSite_Database::get_setting( 'default_header', null );
        if ( $default_header ) {
            $html .= self::render_layout( $default_header, $context );
        }

        // Render main content.
        $default_main = CodeSite_Database::get_setting( 'default_main_layout', null );
        if ( $default_main ) {
            $html .= '<main class="codesite-content codesite-default">';
            $html .= self::render_layout( $default_main, $context );
            $html .= '</main>';
        } else {
            // Render default post content.
            $html .= '<main class="codesite-content codesite-default">';
            $html .= self::render_default_content( $context );
            $html .= '</main>';
        }

        // Render default footer.
        $default_footer = CodeSite_Database::get_setting( 'default_footer', null );
        if ( $default_footer ) {
            $html .= self::render_layout( $default_footer, $context );
        }

        return $html;
    }

    /**
     * Render default post content.
     *
     * @param array $context Context data.
     *
     * @return string
     */
    private static function render_default_content( $context ) {
        $html = '';

        if ( have_posts() ) {
            while ( have_posts() ) {
                the_post();

                $html .= '<article class="codesite-post">';

                // Title.
                $html .= '<h1 class="codesite-post-title">' . get_the_title() . '</h1>';

                // Content.
                ob_start();
                the_content();
                $html .= '<div class="codesite-post-content">' . ob_get_clean() . '</div>';

                $html .= '</article>';
            }
        } elseif ( is_404() ) {
            $html .= '<div class="codesite-404">';
            $html .= '<h1>' . esc_html__( 'Page Not Found', 'codesite' ) . '</h1>';
            $html .= '<p>' . esc_html__( 'The page you are looking for could not be found.', 'codesite' ) . '</p>';
            $html .= '</div>';
        }

        return $html;
    }

    /**
     * Render an override.
     *
     * @param object $override Override object.
     *
     * @return string
     */
    public static function render_override( $override ) {
        $html    = '';
        $context = array( 'post_id' => $override->post_id );

        // Get template for structure if partial override.
        $template = CodeSite_Templates::get_for_current_request();

        switch ( $override->override_type ) {
            case 'full':
                // Full override - use override's layout IDs or none.
                if ( $override->header_layout_id ) {
                    $html .= self::render_layout( $override->header_layout_id, $context );
                }

                $html .= self::render_override_content( $override, $context );

                if ( $override->footer_layout_id ) {
                    $html .= self::render_layout( $override->footer_layout_id, $context );
                }
                break;

            case 'header':
                // Override header only.
                if ( $override->header_layout_id ) {
                    $html .= self::render_layout( $override->header_layout_id, $context );
                }

                // Use template's content and footer.
                if ( $template ) {
                    $html .= self::render_template_content( $template, $context );
                    if ( $template->footer_layout_id ) {
                        $html .= self::render_layout( $template->footer_layout_id, $context );
                    }
                }
                break;

            case 'footer':
                // Use template's header and content.
                if ( $template ) {
                    if ( $template->header_layout_id ) {
                        $html .= self::render_layout( $template->header_layout_id, $context );
                    }
                    $html .= self::render_template_content( $template, $context );
                }

                // Override footer.
                if ( $override->footer_layout_id ) {
                    $html .= self::render_layout( $override->footer_layout_id, $context );
                }
                break;

            case 'content':
                // Use template's header.
                if ( $template && $template->header_layout_id ) {
                    $html .= self::render_layout( $template->header_layout_id, $context );
                }

                // Override content.
                $html .= self::render_override_content( $override, $context );

                // Use template's footer.
                if ( $template && $template->footer_layout_id ) {
                    $html .= self::render_layout( $template->footer_layout_id, $context );
                }
                break;
        }

        // Add override CSS.
        if ( ! empty( $override->custom_css ) ) {
            CodeSite_CSS_Compiler::add( $override->custom_css, 'override', $override->id );
        }

        // Collect override JS.
        if ( ! empty( $override->custom_js ) ) {
            self::$js_collection[] = array(
                'type'    => 'override',
                'id'      => $override->id,
                'name'    => 'Post ' . $override->post_id,
                'content' => $override->custom_js,
            );
        }

        return $html;
    }

    /**
     * Render override content.
     *
     * @param object $override Override object.
     * @param array  $context  Context data.
     *
     * @return string
     */
    private static function render_override_content( $override, $context ) {
        $html = '';

        // Content blocks.
        $content_blocks = json_decode( $override->content_blocks, true );
        if ( is_array( $content_blocks ) && ! empty( $content_blocks ) ) {
            foreach ( $content_blocks as $block_id ) {
                $html .= self::render_block( $block_id, $context );
            }
        }

        // Custom HTML.
        if ( ! empty( $override->custom_html ) ) {
            $custom = $override->custom_html;
            $custom = CodeSite_Dynamic_Content::parse( $custom, $context );
            $custom = CodeSite_Shortcode_Parser::parse( $custom );

            $tangible = new CodeSite_Tangible_Integration();
            $custom   = $tangible->parse( $custom );

            $html .= $custom;
        }

        return sprintf( '<main class="codesite-content codesite-override">%s</main>', $html );
    }

    /**
     * Render template content only.
     *
     * @param object $template Template object.
     * @param array  $context  Context data.
     *
     * @return string
     */
    private static function render_template_content( $template, $context ) {
        $html = '';

        $content_blocks = json_decode( $template->content_blocks, true );
        if ( is_array( $content_blocks ) && ! empty( $content_blocks ) ) {
            foreach ( $content_blocks as $block_id ) {
                $html .= self::render_block( $block_id, $context );
            }
        }

        if ( ! empty( $template->custom_html ) ) {
            $custom = $template->custom_html;
            $custom = CodeSite_Dynamic_Content::parse( $custom, $context );
            $custom = CodeSite_Shortcode_Parser::parse( $custom );

            $tangible = new CodeSite_Tangible_Integration();
            $custom   = $tangible->parse( $custom );

            $html .= $custom;
        }

        return sprintf( '<main class="codesite-content codesite-template-%d">%s</main>', $template->id, $html );
    }

    /**
     * Output CSS in wp_head.
     */
    public function output_css() {
        $theme_override = new CodeSite_Theme_Override();
        if ( ! $theme_override->should_render() ) {
            return;
        }

        $output_mode = CodeSite_Database::get_setting( 'output_mode', 'inline' );

        if ( $output_mode === 'file' ) {
            // Merged CSS for file mode.
            $css = CodeSite_CSS_Compiler::get_merged();
            if ( ! empty( $css ) ) {
                echo "<style id='codesite-css'>\n";
                echo $css;
                echo "\n</style>";
            }
        } else {
            // Separate style tags with unique IDs for inline mode.
            echo CodeSite_CSS_Compiler::output_inline();
        }
    }

    /**
     * Output JS in wp_footer.
     */
    public function output_js() {
        $theme_override = new CodeSite_Theme_Override();
        if ( ! $theme_override->should_render() ) {
            return;
        }

        // Global JS.
        $global_js = CodeSite_Database::get_setting( 'global_js', '' );

        if ( empty( $global_js ) && empty( self::$js_collection ) ) {
            return;
        }

        echo "<script id='codesite-js'>\n";

        if ( ! empty( $global_js ) ) {
            echo $global_js . "\n";
        }

        foreach ( self::$js_collection as $js ) {
            echo $js['content'] . "\n";
        }

        echo "</script>";
    }

    /**
     * Get JS collection.
     *
     * @return array
     */
    public static function get_js_collection() {
        return self::$js_collection;
    }

    /**
     * Reset JS collection.
     */
    public static function reset_js() {
        self::$js_collection = array();
    }
}
