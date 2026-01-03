<?php
/**
 * Post/Page overrides management for CodeSite.
 */
class CodeSite_Overrides {

    /**
     * Get override for a post.
     *
     * @param int $post_id Post ID.
     *
     * @return object|null
     */
    public static function get( $post_id ) {
        global $wpdb;
        $table = CodeSite_Database::overrides_table();

        return $wpdb->get_row(
            $wpdb->prepare( "SELECT * FROM $table WHERE post_id = %d", $post_id )
        );
    }

    /**
     * Check if a post has an override.
     *
     * @param int $post_id Post ID.
     *
     * @return bool
     */
    public static function has_override( $post_id ) {
        return self::get( $post_id ) !== null;
    }

    /**
     * Create or update an override.
     *
     * @param int   $post_id Post ID.
     * @param array $data    Override data.
     *
     * @return bool
     */
    public static function save( $post_id, $data ) {
        global $wpdb;
        $table = CodeSite_Database::overrides_table();

        $defaults = array(
            'override_type'    => 'full',
            'header_layout_id' => null,
            'footer_layout_id' => null,
            'content_blocks'   => '[]',
            'custom_html'      => '',
            'custom_css'       => '',
            'custom_js'        => '',
        );

        $data = wp_parse_args( $data, $defaults );

        // Validate override type.
        $valid_types = array( 'full', 'header', 'footer', 'content' );
        if ( ! in_array( $data['override_type'], $valid_types, true ) ) {
            $data['override_type'] = 'full';
        }

        // Ensure content_blocks is JSON.
        if ( is_array( $data['content_blocks'] ) ) {
            $data['content_blocks'] = wp_json_encode( $data['content_blocks'] );
        }

        $existing = self::get( $post_id );

        if ( $existing ) {
            // Update.
            $result = $wpdb->update(
                $table,
                array(
                    'override_type'    => $data['override_type'],
                    'header_layout_id' => $data['header_layout_id'] ? absint( $data['header_layout_id'] ) : null,
                    'footer_layout_id' => $data['footer_layout_id'] ? absint( $data['footer_layout_id'] ) : null,
                    'content_blocks'   => $data['content_blocks'],
                    'custom_html'      => $data['custom_html'],
                    'custom_css'       => $data['custom_css'],
                    'custom_js'        => $data['custom_js'],
                ),
                array( 'post_id' => $post_id ),
                array( '%s', '%d', '%d', '%s', '%s', '%s', '%s' ),
                array( '%d' )
            );
        } else {
            // Insert.
            $result = $wpdb->insert(
                $table,
                array(
                    'post_id'          => $post_id,
                    'override_type'    => $data['override_type'],
                    'header_layout_id' => $data['header_layout_id'] ? absint( $data['header_layout_id'] ) : null,
                    'footer_layout_id' => $data['footer_layout_id'] ? absint( $data['footer_layout_id'] ) : null,
                    'content_blocks'   => $data['content_blocks'],
                    'custom_html'      => $data['custom_html'],
                    'custom_css'       => $data['custom_css'],
                    'custom_js'        => $data['custom_js'],
                ),
                array( '%d', '%s', '%d', '%d', '%s', '%s', '%s', '%s' )
            );
        }

        return $result !== false;
    }

    /**
     * Delete an override.
     *
     * @param int $post_id Post ID.
     *
     * @return bool
     */
    public static function delete( $post_id ) {
        global $wpdb;
        $table = CodeSite_Database::overrides_table();

        return $wpdb->delete( $table, array( 'post_id' => $post_id ), array( '%d' ) ) !== false;
    }

    /**
     * Get all posts with overrides.
     *
     * @param array $args Query arguments.
     *
     * @return array
     */
    public static function get_all( $args = array() ) {
        global $wpdb;

        $defaults = array(
            'override_type' => null,
            'limit'         => -1,
            'offset'        => 0,
        );

        $args  = wp_parse_args( $args, $defaults );
        $table = CodeSite_Database::overrides_table();

        $where  = array( '1=1' );
        $values = array();

        if ( $args['override_type'] ) {
            $where[]  = 'override_type = %s';
            $values[] = $args['override_type'];
        }

        $where_sql = implode( ' AND ', $where );
        $limit_sql = $args['limit'] > 0 ? sprintf( 'LIMIT %d OFFSET %d', $args['limit'], $args['offset'] ) : '';

        $sql = "SELECT * FROM $table WHERE $where_sql ORDER BY post_id DESC $limit_sql";

        if ( ! empty( $values ) ) {
            $sql = $wpdb->prepare( $sql, $values );
        }

        return $wpdb->get_results( $sql );
    }

    /**
     * Count overrides.
     *
     * @return int
     */
    public static function count() {
        global $wpdb;
        $table = CodeSite_Database::overrides_table();

        return (int) $wpdb->get_var( "SELECT COUNT(*) FROM $table" );
    }
}
