<?php
/**
 * Layouts management for CodeSite.
 */
class CodeSite_Layouts {

    /**
     * Get all layouts.
     *
     * @param array $args Query arguments.
     *
     * @return array
     */
    public static function get_all( $args = array() ) {
        global $wpdb;

        $defaults = array(
            'status'  => 'active',
            'type'    => null,
            'orderby' => 'name',
            'order'   => 'ASC',
            'limit'   => -1,
            'offset'  => 0,
        );

        $args  = wp_parse_args( $args, $defaults );
        $table = CodeSite_Database::layouts_table();

        $where  = array( '1=1' );
        $values = array();

        if ( $args['status'] ) {
            $where[]  = 'status = %s';
            $values[] = $args['status'];
        }

        if ( $args['type'] ) {
            $where[]  = 'type = %s';
            $values[] = $args['type'];
        }

        $where_sql = implode( ' AND ', $where );
        $order_sql = sprintf( 'ORDER BY %s %s', esc_sql( $args['orderby'] ), esc_sql( $args['order'] ) );
        $limit_sql = $args['limit'] > 0 ? sprintf( 'LIMIT %d OFFSET %d', $args['limit'], $args['offset'] ) : '';

        $sql = "SELECT * FROM $table WHERE $where_sql $order_sql $limit_sql";

        if ( ! empty( $values ) ) {
            $sql = $wpdb->prepare( $sql, $values );
        }

        return $wpdb->get_results( $sql );
    }

    /**
     * Get a single layout by ID.
     *
     * @param int $id Layout ID.
     *
     * @return object|null
     */
    public static function get( $id ) {
        global $wpdb;
        $table = CodeSite_Database::layouts_table();

        return $wpdb->get_row(
            $wpdb->prepare( "SELECT * FROM $table WHERE id = %d", $id )
        );
    }

    /**
     * Get a layout by slug.
     *
     * @param string $slug Layout slug.
     *
     * @return object|null
     */
    public static function get_by_slug( $slug ) {
        global $wpdb;
        $table = CodeSite_Database::layouts_table();

        return $wpdb->get_row(
            $wpdb->prepare( "SELECT * FROM $table WHERE slug = %s", $slug )
        );
    }

    /**
     * Create a new layout.
     *
     * @param array $data Layout data.
     *
     * @return int|false Layout ID or false on failure.
     */
    public static function create( $data ) {
        global $wpdb;
        $table = CodeSite_Database::layouts_table();

        $defaults = array(
            'name'        => '',
            'slug'        => '',
            'type'        => 'section',
            'block_order' => '[]',
            'custom_html' => '',
            'custom_css'  => '',
            'custom_js'   => '',
            'use_blocks'  => 1,
            'status'      => 'active',
        );

        $data = wp_parse_args( $data, $defaults );

        // Generate slug if empty.
        if ( empty( $data['slug'] ) ) {
            $data['slug'] = sanitize_title( $data['name'] );
        }

        // Ensure unique slug.
        $data['slug'] = self::unique_slug( $data['slug'] );

        // Validate type.
        $valid_types = array( 'header', 'footer', 'section' );
        if ( ! in_array( $data['type'], $valid_types, true ) ) {
            $data['type'] = 'section';
        }

        // Ensure block_order is JSON.
        if ( is_array( $data['block_order'] ) ) {
            $data['block_order'] = wp_json_encode( $data['block_order'] );
        }

        $result = $wpdb->insert(
            $table,
            array(
                'name'        => sanitize_text_field( $data['name'] ),
                'slug'        => sanitize_title( $data['slug'] ),
                'type'        => $data['type'],
                'block_order' => $data['block_order'],
                'custom_html' => $data['custom_html'],
                'custom_css'  => $data['custom_css'],
                'custom_js'   => $data['custom_js'],
                'use_blocks'  => (int) $data['use_blocks'],
                'status'      => in_array( $data['status'], array( 'active', 'draft', 'trash' ), true ) ? $data['status'] : 'active',
            ),
            array( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%s' )
        );

        if ( $result ) {
            return $wpdb->insert_id;
        }

        return false;
    }

    /**
     * Update a layout.
     *
     * @param int   $id   Layout ID.
     * @param array $data Layout data.
     *
     * @return bool
     */
    public static function update( $id, $data ) {
        global $wpdb;
        $table = CodeSite_Database::layouts_table();

        $update_data   = array();
        $update_format = array();

        if ( isset( $data['name'] ) ) {
            $update_data['name'] = sanitize_text_field( $data['name'] );
            $update_format[]     = '%s';
        }

        if ( isset( $data['slug'] ) ) {
            $update_data['slug'] = sanitize_title( $data['slug'] );
            $update_format[]     = '%s';
        }

        if ( isset( $data['type'] ) ) {
            $valid_types = array( 'header', 'footer', 'section' );
            $update_data['type'] = in_array( $data['type'], $valid_types, true ) ? $data['type'] : 'section';
            $update_format[]     = '%s';
        }

        if ( isset( $data['block_order'] ) ) {
            $update_data['block_order'] = is_array( $data['block_order'] ) ? wp_json_encode( $data['block_order'] ) : $data['block_order'];
            $update_format[]            = '%s';
        }

        if ( isset( $data['custom_html'] ) ) {
            $update_data['custom_html'] = $data['custom_html'];
            $update_format[]            = '%s';
        }

        if ( isset( $data['custom_css'] ) ) {
            $update_data['custom_css'] = $data['custom_css'];
            $update_format[]           = '%s';
        }

        if ( isset( $data['custom_js'] ) ) {
            $update_data['custom_js'] = $data['custom_js'];
            $update_format[]          = '%s';
        }

        if ( isset( $data['use_blocks'] ) ) {
            $update_data['use_blocks'] = (int) $data['use_blocks'];
            $update_format[]           = '%d';
        }

        if ( isset( $data['status'] ) ) {
            $update_data['status'] = in_array( $data['status'], array( 'active', 'draft', 'trash' ), true ) ? $data['status'] : 'active';
            $update_format[]       = '%s';
        }

        if ( empty( $update_data ) ) {
            return false;
        }

        $result = $wpdb->update(
            $table,
            $update_data,
            array( 'id' => $id ),
            $update_format,
            array( '%d' )
        );

        return $result !== false;
    }

    /**
     * Delete a layout.
     *
     * @param int $id Layout ID.
     *
     * @return bool
     */
    public static function delete( $id ) {
        global $wpdb;
        $table = CodeSite_Database::layouts_table();

        return $wpdb->delete( $table, array( 'id' => $id ), array( '%d' ) ) !== false;
    }

    /**
     * Get unique slug.
     *
     * @param string $slug Base slug.
     * @param int    $id   Exclude layout ID.
     *
     * @return string
     */
    private static function unique_slug( $slug, $id = 0 ) {
        global $wpdb;
        $table = CodeSite_Database::layouts_table();

        $original_slug = $slug;
        $counter       = 1;

        while ( true ) {
            $existing = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT id FROM $table WHERE slug = %s AND id != %d",
                    $slug,
                    $id
                )
            );

            if ( ! $existing ) {
                break;
            }

            $slug = $original_slug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Get blocks for a layout.
     *
     * @param int $id Layout ID.
     *
     * @return array
     */
    public static function get_blocks( $id ) {
        $layout = self::get( $id );
        if ( ! $layout || ! $layout->use_blocks ) {
            return array();
        }

        $block_ids = json_decode( $layout->block_order, true );
        if ( ! is_array( $block_ids ) || empty( $block_ids ) ) {
            return array();
        }

        $blocks = array();
        foreach ( $block_ids as $block_id ) {
            $block = CodeSite_Blocks::get( $block_id );
            if ( $block && $block->status === 'active' ) {
                $blocks[] = $block;
            }
        }

        return $blocks;
    }

    /**
     * Count layouts.
     *
     * @param string $status Optional status filter.
     *
     * @return int
     */
    public static function count( $status = null ) {
        global $wpdb;
        $table = CodeSite_Database::layouts_table();

        if ( $status ) {
            return (int) $wpdb->get_var(
                $wpdb->prepare( "SELECT COUNT(*) FROM $table WHERE status = %s", $status )
            );
        }

        return (int) $wpdb->get_var( "SELECT COUNT(*) FROM $table" );
    }
}
