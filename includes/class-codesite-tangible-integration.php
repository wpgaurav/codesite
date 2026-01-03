<?php
/**
 * Tangible Loops & Logic integration for CodeSite.
 */
class CodeSite_Tangible_Integration {

    /**
     * Check if Tangible is active.
     *
     * @return bool
     */
    public function is_active() {
        return defined( 'JETONLINE_TEMPLATE_SYSTEM_VERSION' )
            || class_exists( 'Jetonline\\TemplateSystem\\Plugin' )
            || function_exists( 'tangible' );
    }

    /**
     * Check if we're on a CodeSite admin page.
     *
     * @return bool
     */
    private function is_codesite_page() {
        if ( ! is_admin() ) {
            return false;
        }

        $screen = get_current_screen();
        if ( ! $screen ) {
            return false;
        }

        return strpos( $screen->id, 'codesite' ) !== false;
    }

    /**
     * Maybe show Tangible installation notice.
     */
    public function maybe_show_tangible_notice() {
        if ( $this->is_active() ) {
            return;
        }

        if ( ! $this->is_codesite_page() ) {
            return;
        }

        // Check if dismissed.
        if ( get_option( 'codesite_tangible_notice_dismissed' ) ) {
            return;
        }

        ?>
        <div class="notice notice-info is-dismissible codesite-tangible-notice">
            <p>
                <strong><?php esc_html_e( 'Enhance CodeSite with Tangible Loops & Logic', 'codesite' ); ?></strong><br>
                <?php esc_html_e( 'For powerful content loops and conditional logic, install the Tangible Loops & Logic plugin. It\'s free and works seamlessly with CodeSite.', 'codesite' ); ?>
            </p>
            <p>
                <a href="<?php echo esc_url( admin_url( 'plugin-install.php?s=tangible+loops&tab=search' ) ); ?>"
                   class="button button-primary">
                    <?php esc_html_e( 'Install Now', 'codesite' ); ?>
                </a>
                <a href="#" class="button codesite-dismiss-tangible">
                    <?php esc_html_e( 'Dismiss', 'codesite' ); ?>
                </a>
            </p>
        </div>
        <script>
        jQuery(document).ready(function($) {
            $('.codesite-dismiss-tangible').on('click', function(e) {
                e.preventDefault();
                $.post(ajaxurl, {
                    action: 'codesite_dismiss_tangible',
                    nonce: '<?php echo esc_js( wp_create_nonce( 'codesite_dismiss_tangible' ) ); ?>'
                });
                $(this).closest('.notice').fadeOut();
            });
        });
        </script>
        <?php
    }

    /**
     * Dismiss tangible notice via AJAX.
     */
    public static function ajax_dismiss_tangible() {
        check_ajax_referer( 'codesite_dismiss_tangible', 'nonce' );
        update_option( 'codesite_tangible_notice_dismissed', true );
        wp_die();
    }

    /**
     * Parse Tangible template syntax in content.
     *
     * @param string $content Content to parse.
     *
     * @return string
     */
    public function parse( $content ) {
        if ( ! $this->is_active() ) {
            return $content;
        }

        // Use Tangible's template engine if available.
        if ( function_exists( 'tangible_template' ) ) {
            return tangible_template( $content );
        }

        return $content;
    }
}

// Register AJAX handler.
add_action( 'wp_ajax_codesite_dismiss_tangible', array( 'CodeSite_Tangible_Integration', 'ajax_dismiss_tangible' ) );
