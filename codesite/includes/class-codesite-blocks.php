<?php
/**
 * Blocks management for CodeSite.
 */
class CodeSite_Blocks {

    /**
     * Get all blocks.
     *
     * @param array $args Query arguments.
     *
     * @return array
     */
    public static function get_all( $args = array() ) {
        global $wpdb;

        $defaults = array(
            'status'   => 'active',
            'category' => null,
            'orderby'  => 'name',
            'order'    => 'ASC',
            'limit'    => -1,
            'offset'   => 0,
        );

        $args  = wp_parse_args( $args, $defaults );
        $table = CodeSite_Database::blocks_table();

        $where = array( '1=1' );
        $values = array();

        if ( $args['status'] ) {
            $where[]  = 'status = %s';
            $values[] = $args['status'];
        }

        if ( $args['category'] ) {
            $where[]  = 'category = %s';
            $values[] = $args['category'];
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
     * Get a single block by ID.
     *
     * @param int $id Block ID.
     *
     * @return object|null
     */
    public static function get( $id ) {
        global $wpdb;
        $table = CodeSite_Database::blocks_table();

        return $wpdb->get_row(
            $wpdb->prepare( "SELECT * FROM $table WHERE id = %d", $id )
        );
    }

    /**
     * Get a block by slug.
     *
     * @param string $slug Block slug.
     *
     * @return object|null
     */
    public static function get_by_slug( $slug ) {
        global $wpdb;
        $table = CodeSite_Database::blocks_table();

        return $wpdb->get_row(
            $wpdb->prepare( "SELECT * FROM $table WHERE slug = %s", $slug )
        );
    }

    /**
     * Create a new block.
     *
     * @param array $data Block data.
     *
     * @return int|false Block ID or false on failure.
     */
    public static function create( $data ) {
        global $wpdb;
        $table = CodeSite_Database::blocks_table();

        $defaults = array(
            'name'      => '',
            'slug'      => '',
            'html'      => '',
            'css'       => '',
            'js'        => '',
            'category'  => 'general',
            'css_scope' => 'scoped',
            'status'    => 'active',
        );

        $data = wp_parse_args( $data, $defaults );

        // Generate slug if empty.
        if ( empty( $data['slug'] ) ) {
            $data['slug'] = sanitize_title( $data['name'] );
        }

        // Ensure unique slug.
        $data['slug'] = self::unique_slug( $data['slug'] );

        $result = $wpdb->insert(
            $table,
            array(
                'name'      => sanitize_text_field( $data['name'] ),
                'slug'      => sanitize_title( $data['slug'] ),
                'html'      => $data['html'],
                'css'       => $data['css'],
                'js'        => $data['js'],
                'category'  => sanitize_text_field( $data['category'] ),
                'css_scope' => in_array( $data['css_scope'], array( 'global', 'scoped' ), true ) ? $data['css_scope'] : 'scoped',
                'status'    => in_array( $data['status'], array( 'active', 'draft', 'trash' ), true ) ? $data['status'] : 'active',
            ),
            array( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s' )
        );

        if ( $result ) {
            return $wpdb->insert_id;
        }

        return false;
    }

    /**
     * Update a block.
     *
     * @param int   $id   Block ID.
     * @param array $data Block data.
     *
     * @return bool
     */
    public static function update( $id, $data ) {
        global $wpdb;
        $table = CodeSite_Database::blocks_table();

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

        if ( isset( $data['html'] ) ) {
            $update_data['html'] = $data['html'];
            $update_format[]     = '%s';
        }

        if ( isset( $data['css'] ) ) {
            $update_data['css'] = $data['css'];
            $update_format[]    = '%s';
        }

        if ( isset( $data['js'] ) ) {
            $update_data['js'] = $data['js'];
            $update_format[]   = '%s';
        }

        if ( isset( $data['category'] ) ) {
            $update_data['category'] = sanitize_text_field( $data['category'] );
            $update_format[]         = '%s';
        }

        if ( isset( $data['css_scope'] ) ) {
            $update_data['css_scope'] = in_array( $data['css_scope'], array( 'global', 'scoped' ), true ) ? $data['css_scope'] : 'scoped';
            $update_format[]          = '%s';
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
     * Delete a block.
     *
     * @param int $id Block ID.
     *
     * @return bool
     */
    public static function delete( $id ) {
        global $wpdb;
        $table = CodeSite_Database::blocks_table();

        return $wpdb->delete( $table, array( 'id' => $id ), array( '%d' ) ) !== false;
    }

    /**
     * Duplicate a block.
     *
     * @param int $id Block ID.
     *
     * @return int|false New block ID or false on failure.
     */
    public static function duplicate( $id ) {
        $block = self::get( $id );
        if ( ! $block ) {
            return false;
        }

        return self::create(
            array(
                'name'      => $block->name . ' (Copy)',
                'slug'      => $block->slug . '-copy',
                'html'      => $block->html,
                'css'       => $block->css,
                'js'        => $block->js,
                'category'  => $block->category,
                'css_scope' => $block->css_scope,
                'status'    => 'draft',
            )
        );
    }

    /**
     * Get unique slug.
     *
     * @param string $slug  Base slug.
     * @param int    $id    Exclude block ID.
     *
     * @return string
     */
    private static function unique_slug( $slug, $id = 0 ) {
        global $wpdb;
        $table = CodeSite_Database::blocks_table();

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
     * Get all categories.
     *
     * @return array
     */
    public static function get_categories() {
        global $wpdb;
        $table = CodeSite_Database::blocks_table();

        $categories = $wpdb->get_col(
            "SELECT DISTINCT category FROM $table WHERE status = 'active' ORDER BY category ASC"
        );

        return $categories ? $categories : array();
    }

    /**
     * Count blocks.
     *
     * @param string $status Optional status filter.
     *
     * @return int
     */
    public static function count( $status = null ) {
        global $wpdb;
        $table = CodeSite_Database::blocks_table();

        if ( $status ) {
            return (int) $wpdb->get_var(
                $wpdb->prepare( "SELECT COUNT(*) FROM $table WHERE status = %s", $status )
            );
        }

        return (int) $wpdb->get_var( "SELECT COUNT(*) FROM $table" );
    }
}
