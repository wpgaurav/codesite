<?php
/**
 * Tangible Loops & Logic integration for CodeSite.
 */
class CodeSite_Tangible_Integration {

    /**
     * Plugin slug on WordPress.org
     */
    const PLUGIN_SLUG = 'tangible-loops-and-logic';

    /**
     * Plugin file path for installation check.
     */
    const PLUGIN_FILE = 'tangible-loops-and-logic/plugin.php';

    /**
     * Check if Tangible is active.
     *
     * @return bool
     */
    public function is_active() {
        return defined( 'JETONLINE_TEMPLATE_SYSTEM_VERSION' )
            || defined( 'JETONLINE_TEMPLATE_SYSTEM_PATH' )
            || class_exists( 'Jetonline\\TemplateSystem\\Plugin' )
            || function_exists( 'tangible' )
            || function_exists( 'tangible_template' );
    }

    /**
     * Check if Tangible is installed but not active.
     *
     * @return bool
     */
    public function is_installed() {
        if ( ! function_exists( 'get_plugins' ) ) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }
        $plugins = get_plugins();
        return isset( $plugins[ self::PLUGIN_FILE ] );
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

        $is_installed = $this->is_installed();
        $install_url  = wp_nonce_url(
            admin_url( 'update.php?action=install-plugin&plugin=' . self::PLUGIN_SLUG ),
            'install-plugin_' . self::PLUGIN_SLUG
        );
        $activate_url = wp_nonce_url(
            admin_url( 'plugins.php?action=activate&plugin=' . rawurlencode( self::PLUGIN_FILE ) ),
            'activate-plugin_' . self::PLUGIN_FILE
        );
        $wp_org_url   = 'https://wordpress.org/plugins/tangible-loops-and-logic/';
        ?>
        <div class="notice notice-info is-dismissible codesite-tangible-notice" style="padding: 15px; border-left-color: #667eea;">
            <div style="display: flex; align-items: flex-start; gap: 15px;">
                <div style="flex-shrink: 0;">
                    <span class="dashicons dashicons-superhero-alt" style="font-size: 36px; width: 36px; height: 36px; color: #667eea;"></span>
                </div>
                <div style="flex: 1;">
                    <h3 style="margin: 0 0 8px; font-size: 14px;">
                        <?php esc_html_e( 'Supercharge CodeSite with Tangible Loops & Logic', 'codesite' ); ?>
                    </h3>
                    <p style="margin: 0 0 12px; color: #50575e;">
                        <?php esc_html_e( 'Add powerful content loops, conditional logic, and dynamic queries to your CodeSite templates. Create post grids, filter content, and build dynamic layouts - all with a simple, intuitive syntax.', 'codesite' ); ?>
                    </p>
                    <p style="margin: 0;">
                        <?php if ( $is_installed ) : ?>
                            <a href="<?php echo esc_url( $activate_url ); ?>" class="button button-primary">
                                <?php esc_html_e( 'Activate Tangible', 'codesite' ); ?>
                            </a>
                        <?php else : ?>
                            <a href="<?php echo esc_url( $install_url ); ?>" class="button button-primary">
                                <?php esc_html_e( 'Install Tangible (Free)', 'codesite' ); ?>
                            </a>
                        <?php endif; ?>
                        <a href="<?php echo esc_url( $wp_org_url ); ?>" class="button" target="_blank" rel="noopener">
                            <?php esc_html_e( 'Learn More', 'codesite' ); ?>
                            <span class="dashicons dashicons-external" style="font-size: 14px; line-height: 28px; width: 14px; height: 14px;"></span>
                        </a>
                        <a href="#" class="button codesite-dismiss-tangible" style="margin-left: 5px;">
                            <?php esc_html_e( 'Dismiss', 'codesite' ); ?>
                        </a>
                    </p>
                </div>
            </div>
        </div>
        <script>
        jQuery(document).ready(function($) {
            $('.codesite-dismiss-tangible, .codesite-tangible-notice .notice-dismiss').on('click', function(e) {
                if ($(this).hasClass('codesite-dismiss-tangible')) {
                    e.preventDefault();
                }
                $.post(ajaxurl, {
                    action: 'codesite_dismiss_tangible',
                    nonce: '<?php echo esc_js( wp_create_nonce( 'codesite_dismiss_tangible' ) ); ?>'
                });
                $('.codesite-tangible-notice').fadeOut();
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

    /**
     * Get Tangible status for dashboard.
     *
     * @return array
     */
    public function get_status() {
        if ( $this->is_active() ) {
            return array(
                'status'  => 'active',
                'message' => __( 'Tangible Loops & Logic is active', 'codesite' ),
            );
        }

        if ( $this->is_installed() ) {
            return array(
                'status'  => 'inactive',
                'message' => __( 'Tangible is installed but not active', 'codesite' ),
            );
        }

        return array(
            'status'  => 'not_installed',
            'message' => __( 'Tangible Loops & Logic is not installed', 'codesite' ),
        );
    }
}

// Register AJAX handler.
add_action( 'wp_ajax_codesite_dismiss_tangible', array( 'CodeSite_Tangible_Integration', 'ajax_dismiss_tangible' ) );
