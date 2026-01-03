<?php
/**
 * Database operations for CodeSite.
 */
class CodeSite_Database {

    /**
     * Get the blocks table name.
     *
     * @return string
     */
    public static function blocks_table() {
        global $wpdb;
        return $wpdb->prefix . 'codesite_blocks';
    }

    /**
     * Get the layouts table name.
     *
     * @return string
     */
    public static function layouts_table() {
        global $wpdb;
        return $wpdb->prefix . 'codesite_layouts';
    }

    /**
     * Get the templates table name.
     *
     * @return string
     */
    public static function templates_table() {
        global $wpdb;
        return $wpdb->prefix . 'codesite_templates';
    }

    /**
     * Get the overrides table name.
     *
     * @return string
     */
    public static function overrides_table() {
        global $wpdb;
        return $wpdb->prefix . 'codesite_overrides';
    }

    /**
     * Get the settings table name.
     *
     * @return string
     */
    public static function settings_table() {
        global $wpdb;
        return $wpdb->prefix . 'codesite_settings';
    }

    /**
     * Get a setting value.
     *
     * @param string $key     Setting key.
     * @param mixed  $default Default value.
     *
     * @return mixed
     */
    public static function get_setting( $key, $default = null ) {
        $settings = get_option( 'codesite_settings', array() );
        return isset( $settings[ $key ] ) ? $settings[ $key ] : $default;
    }

    /**
     * Update a setting value.
     *
     * @param string $key   Setting key.
     * @param mixed  $value Setting value.
     *
     * @return bool
     */
    public static function update_setting( $key, $value ) {
        $settings         = get_option( 'codesite_settings', array() );
        $settings[ $key ] = $value;
        return update_option( 'codesite_settings', $settings );
    }

    /**
     * Get all settings.
     *
     * @return array
     */
    public static function get_all_settings() {
        return get_option( 'codesite_settings', array() );
    }

    /**
     * Update all settings.
     *
     * @param array $settings Settings array.
     *
     * @return bool
     */
    public static function update_all_settings( $settings ) {
        return update_option( 'codesite_settings', $settings );
    }
}
