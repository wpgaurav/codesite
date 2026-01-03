<?php
/**
 * Admin functionality for CodeSite.
 */
class CodeSite_Admin {

    /**
     * Add admin menu.
     */
    public function add_admin_menu() {
        // Main menu.
        add_menu_page(
            __( 'CodeSite', 'codesite' ),
            __( 'CodeSite', 'codesite' ),
            'edit_codesite_blocks',
            'codesite',
            array( $this, 'render_dashboard' ),
            'dashicons-editor-code',
            30
        );

        // Dashboard submenu.
        add_submenu_page(
            'codesite',
            __( 'Dashboard', 'codesite' ),
            __( 'Dashboard', 'codesite' ),
            'edit_codesite_blocks',
            'codesite',
            array( $this, 'render_dashboard' )
        );

        // Blocks.
        add_submenu_page(
            'codesite',
            __( 'Blocks', 'codesite' ),
            __( 'Blocks', 'codesite' ),
            'edit_codesite_blocks',
            'codesite-blocks',
            array( $this, 'render_blocks' )
        );

        // Add New Block.
        add_submenu_page(
            'codesite',
            __( 'Add New Block', 'codesite' ),
            __( 'Add New Block', 'codesite' ),
            'edit_codesite_blocks',
            'codesite-block-editor',
            array( $this, 'render_block_editor' )
        );

        // Layouts.
        add_submenu_page(
            'codesite',
            __( 'Layouts', 'codesite' ),
            __( 'Layouts', 'codesite' ),
            'edit_codesite_blocks',
            'codesite-layouts',
            array( $this, 'render_layouts' )
        );

        // Add New Layout.
        add_submenu_page(
            'codesite',
            __( 'Add New Layout', 'codesite' ),
            __( 'Add New Layout', 'codesite' ),
            'edit_codesite_blocks',
            'codesite-layout-editor',
            array( $this, 'render_layout_editor' )
        );

        // Templates.
        add_submenu_page(
            'codesite',
            __( 'Templates', 'codesite' ),
            __( 'Templates', 'codesite' ),
            'edit_codesite_templates',
            'codesite-templates',
            array( $this, 'render_templates' )
        );

        // Add New Template.
        add_submenu_page(
            'codesite',
            __( 'Add New Template', 'codesite' ),
            __( 'Add New Template', 'codesite' ),
            'edit_codesite_templates',
            'codesite-template-editor',
            array( $this, 'render_template_editor' )
        );

        // Global CSS/JS.
        add_submenu_page(
            'codesite',
            __( 'Global CSS/JS', 'codesite' ),
            __( 'Global CSS/JS', 'codesite' ),
            'manage_codesite',
            'codesite-global',
            array( $this, 'render_global_editor' )
        );

        // Settings.
        add_submenu_page(
            'codesite',
            __( 'Settings', 'codesite' ),
            __( 'Settings', 'codesite' ),
            'manage_codesite',
            'codesite-settings',
            array( $this, 'render_settings' )
        );
    }

    /**
     * Enqueue admin styles.
     *
     * @param string $hook Current admin page.
     */
    public function enqueue_styles( $hook ) {
        if ( strpos( $hook, 'codesite' ) === false ) {
            return;
        }

        // Admin styles.
        wp_enqueue_style(
            'codesite-admin',
            CODESITE_URL . 'assets/css/admin.css',
            array(),
            CODESITE_VERSION
        );
    }

    /**
     * Enqueue admin scripts.
     *
     * @param string $hook Current admin page.
     */
    public function enqueue_scripts( $hook ) {
        if ( strpos( $hook, 'codesite' ) === false ) {
            return;
        }

        // Use WordPress built-in code editor (no autocomplete).
        $editor_settings = array(
            'codemirror' => array(
                'lineNumbers'       => true,
                'lineWrapping'      => true,
                'indentUnit'        => 2,
                'tabSize'           => 2,
                'indentWithTabs'    => false,
                'autoCloseBrackets' => false,
                'autoCloseTags'     => false,
                'matchBrackets'     => false,
                'lint'              => false,
                'gutters'           => array(),
            ),
        );

        // Enqueue WordPress code editor for HTML.
        $html_settings = wp_enqueue_code_editor(
            array_merge(
                $editor_settings,
                array( 'type' => 'text/html' )
            )
        );

        // Enqueue WordPress code editor for CSS.
        $css_settings = wp_enqueue_code_editor(
            array_merge(
                $editor_settings,
                array( 'type' => 'text/css' )
            )
        );

        // Enqueue WordPress code editor for JS.
        $js_settings = wp_enqueue_code_editor(
            array_merge(
                $editor_settings,
                array( 'type' => 'text/javascript' )
            )
        );

        // Admin scripts.
        wp_enqueue_script(
            'codesite-admin',
            CODESITE_URL . 'assets/js/admin.js',
            array( 'jquery' ),
            CODESITE_VERSION,
            true
        );

        // Editor scripts.
        wp_enqueue_script(
            'codesite-editor',
            CODESITE_URL . 'assets/js/editor.js',
            array( 'jquery', 'wp-codemirror' ),
            CODESITE_VERSION,
            true
        );

        // Localize script with editor settings.
        wp_localize_script(
            'codesite-admin',
            'codesiteAdmin',
            array(
                'apiUrl'         => rest_url( 'codesite/v1' ),
                'nonce'          => wp_create_nonce( 'wp_rest' ),
                'adminUrl'       => admin_url(),
                'editorSettings' => array(
                    'html' => $html_settings ? $html_settings : false,
                    'css'  => $css_settings ? $css_settings : false,
                    'js'   => $js_settings ? $js_settings : false,
                ),
                'strings'        => array(
                    'saving'        => __( 'Saving...', 'codesite' ),
                    'saved'         => __( 'Saved!', 'codesite' ),
                    'error'         => __( 'Error saving.', 'codesite' ),
                    'confirmDelete' => __( 'Are you sure you want to delete this?', 'codesite' ),
                ),
            )
        );
    }

    /**
     * Render dashboard page.
     */
    public function render_dashboard() {
        include CODESITE_PATH . 'admin/partials/dashboard.php';
    }

    /**
     * Render blocks list page.
     */
    public function render_blocks() {
        include CODESITE_PATH . 'admin/partials/blocks-list.php';
    }

    /**
     * Render block editor page.
     */
    public function render_block_editor() {
        include CODESITE_PATH . 'admin/partials/block-editor.php';
    }

    /**
     * Render layouts list page.
     */
    public function render_layouts() {
        include CODESITE_PATH . 'admin/partials/layouts-list.php';
    }

    /**
     * Render layout editor page.
     */
    public function render_layout_editor() {
        include CODESITE_PATH . 'admin/partials/layout-editor.php';
    }

    /**
     * Render templates list page.
     */
    public function render_templates() {
        include CODESITE_PATH . 'admin/partials/templates-list.php';
    }

    /**
     * Render template editor page.
     */
    public function render_template_editor() {
        include CODESITE_PATH . 'admin/partials/template-editor.php';
    }

    /**
     * Render global CSS/JS editor page.
     */
    public function render_global_editor() {
        include CODESITE_PATH . 'admin/partials/global-editor.php';
    }

    /**
     * Render settings page.
     */
    public function render_settings() {
        include CODESITE_PATH . 'admin/partials/settings.php';
    }

    /**
     * Add override metabox to post editor.
     */
    public function add_override_metabox() {
        $post_types = get_post_types( array( 'public' => true ), 'names' );

        foreach ( $post_types as $post_type ) {
            add_meta_box(
                'codesite-override',
                __( 'CodeSite Override', 'codesite' ),
                array( $this, 'render_override_metabox' ),
                $post_type,
                'side',
                'default'
            );
        }
    }

    /**
     * Render override metabox.
     *
     * @param WP_Post $post Current post.
     */
    public function render_override_metabox( $post ) {
        $override = CodeSite_Overrides::get( $post->ID );
        $enabled  = $override ? true : false;
        $type     = $override ? $override->override_type : 'full';

        wp_nonce_field( 'codesite_override', 'codesite_override_nonce' );
        ?>
        <p>
            <label>
                <input type="checkbox" name="codesite_override_enabled" value="1" <?php checked( $enabled ); ?>>
                <?php esc_html_e( 'Enable custom template for this post', 'codesite' ); ?>
            </label>
        </p>

        <div class="codesite-override-options" style="<?php echo $enabled ? '' : 'display:none;'; ?>">
            <p><strong><?php esc_html_e( 'Override Type:', 'codesite' ); ?></strong></p>
            <p>
                <label>
                    <input type="radio" name="codesite_override_type" value="full" <?php checked( $type, 'full' ); ?>>
                    <?php esc_html_e( 'Full page', 'codesite' ); ?>
                </label>
            </p>
            <p>
                <label>
                    <input type="radio" name="codesite_override_type" value="content" <?php checked( $type, 'content' ); ?>>
                    <?php esc_html_e( 'Content only', 'codesite' ); ?>
                </label>
            </p>
            <p>
                <label>
                    <input type="radio" name="codesite_override_type" value="header" <?php checked( $type, 'header' ); ?>>
                    <?php esc_html_e( 'Header only', 'codesite' ); ?>
                </label>
            </p>
            <p>
                <label>
                    <input type="radio" name="codesite_override_type" value="footer" <?php checked( $type, 'footer' ); ?>>
                    <?php esc_html_e( 'Footer only', 'codesite' ); ?>
                </label>
            </p>

            <p>
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=codesite-block-editor&post_id=' . $post->ID ) ); ?>" class="button">
                    <?php esc_html_e( 'Open CodeSite Editor', 'codesite' ); ?>
                </a>
            </p>
        </div>

        <script>
        jQuery(document).ready(function($) {
            $('input[name="codesite_override_enabled"]').on('change', function() {
                $('.codesite-override-options').toggle(this.checked);
            });
        });
        </script>
        <?php
    }

    /**
     * Save override metabox.
     *
     * @param int $post_id Post ID.
     */
    public function save_override_metabox( $post_id ) {
        if ( ! isset( $_POST['codesite_override_nonce'] ) ) {
            return;
        }

        if ( ! wp_verify_nonce( $_POST['codesite_override_nonce'], 'codesite_override' ) ) {
            return;
        }

        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }

        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }

        $enabled = isset( $_POST['codesite_override_enabled'] ) && $_POST['codesite_override_enabled'] === '1';

        if ( $enabled ) {
            $type = isset( $_POST['codesite_override_type'] ) ? sanitize_text_field( $_POST['codesite_override_type'] ) : 'full';

            CodeSite_Overrides::save(
                $post_id,
                array(
                    'override_type' => $type,
                )
            );
        } else {
            CodeSite_Overrides::delete( $post_id );
        }
    }
}
