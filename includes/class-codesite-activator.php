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
        self::create_default_layouts();

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

    /**
     * Create default layouts.
     */
    private static function create_default_layouts() {
        global $wpdb;
        $table = $wpdb->prefix . 'codesite_layouts';

        // Check if default layouts already exist.
        $existing = $wpdb->get_var( "SELECT COUNT(*) FROM $table WHERE slug IN ('default-header', 'default-footer')" );
        if ( $existing > 0 ) {
            return;
        }

        // Default Header HTML.
        $header_html = '<header class="site-header">
    <div class="site-branding">
        <a href="{{site_url}}" class="site-title">{{site_name}}</a>
        <p class="site-tagline">{{site_tagline}}</p>
    </div>
    <button class="menu-toggle" aria-label="Menu">
        <span></span>
        <span></span>
        <span></span>
    </button>
    <nav class="site-nav">
        {{menu:primary}}
    </nav>
</header>';

        // Default Header CSS.
        $header_css = '/* Header Layout */
.site-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1rem 2rem;
    background: #fff;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    position: relative;
}

.site-branding {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.site-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: #1a1a1a;
    text-decoration: none;
}

.site-title:hover {
    color: #0073aa;
}

.site-tagline {
    margin: 0;
    font-size: 0.875rem;
    color: #666;
}

.site-nav ul {
    display: flex;
    gap: 1.5rem;
    list-style: none;
    margin: 0;
    padding: 0;
}

.site-nav a {
    color: #333;
    text-decoration: none;
    font-weight: 500;
    padding: 0.5rem 0;
    transition: color 0.2s;
}

.site-nav a:hover {
    color: #0073aa;
}

.menu-toggle {
    display: none;
    flex-direction: column;
    gap: 5px;
    background: none;
    border: none;
    cursor: pointer;
    padding: 0.5rem;
}

.menu-toggle span {
    display: block;
    width: 24px;
    height: 2px;
    background: #333;
    transition: transform 0.3s, opacity 0.3s;
}

/* Responsive */
@media (max-width: 768px) {
    .menu-toggle {
        display: flex;
    }

    .site-nav {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: #fff;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        display: none;
        padding: 1rem 2rem;
    }

    .site-nav.active {
        display: block;
    }

    .site-nav ul {
        flex-direction: column;
        gap: 0;
    }

    .site-nav li {
        border-bottom: 1px solid #eee;
    }

    .site-nav li:last-child {
        border-bottom: none;
    }

    .site-nav a {
        display: block;
        padding: 0.75rem 0;
    }
}';

        // Default Header JS.
        $header_js = '// Mobile menu toggle
document.addEventListener("DOMContentLoaded", function() {
    var toggle = document.querySelector(".menu-toggle");
    var nav = document.querySelector(".site-nav");

    if (toggle && nav) {
        toggle.addEventListener("click", function() {
            nav.classList.toggle("active");
        });
    }
});';

        // Default Footer HTML.
        $footer_html = '<footer class="site-footer">
    <div class="footer-content">
        <p>&copy; {{current_year}} {{site_name}}. All rights reserved.</p>
    </div>
</footer>';

        // Default Footer CSS.
        $footer_css = '/* Footer Layout */
.site-footer {
    background: #1a1a1a;
    color: #fff;
    padding: 2rem;
    margin-top: auto;
}

.footer-content {
    max-width: 1200px;
    margin: 0 auto;
    text-align: center;
}

.footer-content p {
    margin: 0;
    font-size: 0.875rem;
    color: #999;
}

.footer-content a {
    color: #fff;
    text-decoration: none;
}

.footer-content a:hover {
    text-decoration: underline;
}';

        // Create default header.
        $header_id = null;
        $result = $wpdb->insert(
            $table,
            array(
                'name'        => 'Default Header',
                'slug'        => 'default-header',
                'type'        => 'header',
                'block_order' => '[]',
                'custom_html' => $header_html,
                'custom_css'  => $header_css,
                'custom_js'   => $header_js,
                'use_blocks'  => 0,
                'status'      => 'active',
            ),
            array( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%s' )
        );
        if ( $result ) {
            $header_id = $wpdb->insert_id;
        }

        // Create default footer.
        $footer_id = null;
        $result = $wpdb->insert(
            $table,
            array(
                'name'        => 'Default Footer',
                'slug'        => 'default-footer',
                'type'        => 'footer',
                'block_order' => '[]',
                'custom_html' => $footer_html,
                'custom_css'  => $footer_css,
                'custom_js'   => '',
                'use_blocks'  => 0,
                'status'      => 'active',
            ),
            array( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%s' )
        );
        if ( $result ) {
            $footer_id = $wpdb->insert_id;
        }

        // Set as defaults.
        if ( $header_id ) {
            CodeSite_Database::update_setting( 'default_header', $header_id );
        }
        if ( $footer_id ) {
            CodeSite_Database::update_setting( 'default_footer', $footer_id );
        }
    }
}
