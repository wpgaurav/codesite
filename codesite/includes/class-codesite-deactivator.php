<?php
/**
 * Fired during plugin deactivation.
 */
class CodeSite_Deactivator {

    /**
     * Deactivate the plugin.
     *
     * Does not remove database tables or options to preserve data.
     */
    public static function deactivate() {
        // Flush rewrite rules.
        flush_rewrite_rules();

        // Clear any transients.
        self::clear_transients();
    }

    /**
     * Clear all CodeSite transients.
     */
    private static function clear_transients() {
        global $wpdb;

        $wpdb->query(
            "DELETE FROM $wpdb->options WHERE option_name LIKE '_transient_codesite_%'"
        );
        $wpdb->query(
            "DELETE FROM $wpdb->options WHERE option_name LIKE '_transient_timeout_codesite_%'"
        );
    }
}
