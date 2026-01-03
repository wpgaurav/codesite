<?php
/**
 * Fired during plugin activation.
 */
class CodeSite_Activator {

    /**
     * Activate the plugin.
     *
     * Creates database tables and sets default options.
     */
    public static function activate() {
        self::create_tables();
        self::set_default_options();
        self::add_capabilities();

        // Flush rewrite rules.
        flush_rewrite_rules();
    }

    /**
     * Create database tables.
     */
    private static function create_tables() {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();

        // Blocks table.
        $table_blocks = $wpdb->prefix . 'codesite_blocks';
        $sql_blocks   = "CREATE TABLE $table_blocks (
            id BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            slug VARCHAR(255) NOT NULL,
            html LONGTEXT,
            css LONGTEXT,
            js LONGTEXT,
            category VARCHAR(100) DEFAULT 'general',
            css_scope VARCHAR(20) DEFAULT 'scoped',
            status VARCHAR(20) DEFAULT 'active',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY unique_slug (slug),
            KEY idx_category (category),
            KEY idx_status (status)
        ) $charset_collate;";

        // Layouts table.
        $table_layouts = $wpdb->prefix . 'codesite_layouts';
        $sql_layouts   = "CREATE TABLE $table_layouts (
            id BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            slug VARCHAR(255) NOT NULL,
            type VARCHAR(20) NOT NULL,
            block_order LONGTEXT,
            custom_html LONGTEXT,
            custom_css LONGTEXT,
            custom_js LONGTEXT,
            use_blocks TINYINT(1) DEFAULT 1,
            status VARCHAR(20) DEFAULT 'active',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY unique_slug (slug),
            KEY idx_type (type),
            KEY idx_status (status)
        ) $charset_collate;";

        // Templates table.
        $table_templates = $wpdb->prefix . 'codesite_templates';
        $sql_templates   = "CREATE TABLE $table_templates (
            id BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            slug VARCHAR(255) NOT NULL,
            template_type VARCHAR(100) NOT NULL,
            header_layout_id BIGINT(20) UNSIGNED NULL,
            footer_layout_id BIGINT(20) UNSIGNED NULL,
            content_blocks LONGTEXT,
            custom_html LONGTEXT,
            custom_css LONGTEXT,
            custom_js LONGTEXT,
            conditions LONGTEXT,
            priority INT DEFAULT 10,
            status VARCHAR(20) DEFAULT 'active',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY unique_slug (slug),
            KEY idx_template_type (template_type),
            KEY idx_priority (priority),
            KEY idx_status (status)
        ) $charset_collate;";

        // Overrides table.
        $table_overrides = $wpdb->prefix . 'codesite_overrides';
        $sql_overrides   = "CREATE TABLE $table_overrides (
            id BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            post_id BIGINT(20) UNSIGNED NOT NULL,
            override_type VARCHAR(20) DEFAULT 'full',
            header_layout_id BIGINT(20) UNSIGNED NULL,
            footer_layout_id BIGINT(20) UNSIGNED NULL,
            content_blocks LONGTEXT,
            custom_html LONGTEXT,
            custom_css LONGTEXT,
            custom_js LONGTEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY unique_post (post_id)
        ) $charset_collate;";

        // Settings table.
        $table_settings = $wpdb->prefix . 'codesite_settings';
        $sql_settings   = "CREATE TABLE $table_settings (
            id BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            setting_key VARCHAR(255) NOT NULL,
            setting_value LONGTEXT,
            autoload TINYINT(1) DEFAULT 1,
            UNIQUE KEY unique_key (setting_key)
        ) $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        dbDelta( $sql_blocks );
        dbDelta( $sql_layouts );
        dbDelta( $sql_templates );
        dbDelta( $sql_overrides );
        dbDelta( $sql_settings );
    }

    /**
     * Set default options.
     */
    private static function set_default_options() {
        $default_settings = array(
            'enabled'            => true,
            'theme_override'     => false,
            'keep_admin_bar'     => true,
            'output_mode'        => 'inline',
            'minify_output'      => false,
            'default_header'     => null,
            'default_footer'     => null,
            'global_css'         => '',
            'global_js'          => '',
            'tangible_support'   => true,
            'acf_support'        => true,
            'woocommerce_support' => false,
        );

        if ( ! get_option( 'codesite_settings' ) ) {
            add_option( 'codesite_settings', $default_settings );
        }

        // Store version.
        update_option( 'codesite_version', CODESITE_VERSION );
    }

    /**
     * Add custom capabilities.
     */
    private static function add_capabilities() {
        $admin = get_role( 'administrator' );
        if ( $admin ) {
            $admin->add_cap( 'manage_codesite' );
            $admin->add_cap( 'edit_codesite_blocks' );
            $admin->add_cap( 'edit_codesite_templates' );
        }

        $editor = get_role( 'editor' );
        if ( $editor ) {
            $editor->add_cap( 'edit_codesite_blocks' );
        }
    }
}
