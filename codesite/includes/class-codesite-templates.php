<?php
/**
 * Templates management for CodeSite.
 */
class CodeSite_Templates {

    /**
     * Get all templates.
     *
     * @param array $args Query arguments.
     *
     * @return array
     */
    public static function get_all( $args = array() ) {
        global $wpdb;

        $defaults = array(
            'status'        => 'active',
            'template_type' => null,
            'orderby'       => 'priority',
            'order'         => 'ASC',
            'limit'         => -1,
            'offset'        => 0,
        );

        $args  = wp_parse_args( $args, $defaults );
        $table = CodeSite_Database::templates_table();

        $where  = array( '1=1' );
        $values = array();

        if ( $args['status'] ) {
            $where[]  = 'status = %s';
            $values[] = $args['status'];
        }

        if ( $args['template_type'] ) {
            $where[]  = 'template_type = %s';
            $values[] = $args['template_type'];
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
     * Get a single template by ID.
     *
     * @param int $id Template ID.
     *
     * @return object|null
     */
    public static function get( $id ) {
        global $wpdb;
        $table = CodeSite_Database::templates_table();

        return $wpdb->get_row(
            $wpdb->prepare( "SELECT * FROM $table WHERE id = %d", $id )
        );
    }

    /**
     * Get a template by slug.
     *
     * @param string $slug Template slug.
     *
     * @return object|null
     */
    public static function get_by_slug( $slug ) {
        global $wpdb;
        $table = CodeSite_Database::templates_table();

        return $wpdb->get_row(
            $wpdb->prepare( "SELECT * FROM $table WHERE slug = %s", $slug )
        );
    }

    /**
     * Get template for current request.
     *
     * @return object|null
     */
    public static function get_for_current_request() {
        $template_type = self::get_current_template_type();
        if ( ! $template_type ) {
            return null;
        }

        $templates = self::get_all(
            array(
                'template_type' => $template_type,
                'status'        => 'active',
                'orderby'       => 'priority',
                'order'         => 'ASC',
            )
        );

        foreach ( $templates as $template ) {
            if ( self::check_conditions( $template ) ) {
                return $template;
            }
        }

        // Fallback: check for a template without specific conditions.
        foreach ( $templates as $template ) {
            $conditions = json_decode( $template->conditions, true );
            if ( empty( $conditions ) || empty( $conditions['rules'] ) ) {
                return $template;
            }
        }

        return null;
    }

    /**
     * Get the current template type based on WordPress query.
     *
     * @return string|null
     */
    public static function get_current_template_type() {
        if ( is_front_page() ) {
            return 'front-page';
        }

        if ( is_home() ) {
            return 'home';
        }

        if ( is_singular( 'post' ) ) {
            return 'single-post';
        }

        if ( is_page() ) {
            return 'page';
        }

        if ( is_singular() ) {
            $post_type = get_post_type();
            return 'single-' . $post_type;
        }

        if ( is_category() ) {
            return 'archive-category';
        }

        if ( is_tag() ) {
            return 'archive-tag';
        }

        if ( is_post_type_archive() ) {
            $post_type = get_query_var( 'post_type' );
            return 'archive-' . $post_type;
        }

        if ( is_author() ) {
            return 'archive-author';
        }

        if ( is_date() ) {
            return 'archive-date';
        }

        if ( is_archive() ) {
            return 'archive';
        }

        if ( is_search() ) {
            return 'search';
        }

        if ( is_404() ) {
            return '404';
        }

        return null;
    }

    /**
     * Check if template conditions are met.
     *
     * @param object $template Template object.
     *
     * @return bool
     */
    public static function check_conditions( $template ) {
        $conditions = json_decode( $template->conditions, true );

        if ( empty( $conditions ) || empty( $conditions['rules'] ) ) {
            return true;
        }

        $match_type = isset( $conditions['match'] ) ? $conditions['match'] : 'all';
        $results    = array();

        foreach ( $conditions['rules'] as $rule ) {
            $results[] = self::evaluate_rule( $rule );
        }

        if ( $match_type === 'all' ) {
            return ! in_array( false, $results, true );
        }

        return in_array( true, $results, true );
    }

    /**
     * Evaluate a single condition rule.
     *
     * @param array $rule Rule configuration.
     *
     * @return bool
     */
    private static function evaluate_rule( $rule ) {
        $field    = $rule['field'];
        $operator = $rule['operator'];
        $value    = $rule['value'];

        switch ( $field ) {
            case 'category':
                $categories = get_the_category();
                $cat_slugs  = wp_list_pluck( $categories, 'slug' );
                return self::compare_value( $cat_slugs, $operator, $value );

            case 'tag':
                $tags      = get_the_tags();
                $tag_slugs = $tags ? wp_list_pluck( $tags, 'slug' ) : array();
                return self::compare_value( $tag_slugs, $operator, $value );

            case 'post_format':
                $format = get_post_format();
                return self::compare_value( $format ? $format : 'standard', $operator, $value );

            case 'author':
                $author = get_the_author_meta( 'user_login' );
                return self::compare_value( $author, $operator, $value );

            case 'post_type':
                $post_type = get_post_type();
                return self::compare_value( $post_type, $operator, $value );

            case 'logged_in':
                $logged_in = is_user_logged_in();
                return self::compare_value( $logged_in, $operator, $value === 'true' || $value === true );

            case 'user_role':
                $user  = wp_get_current_user();
                $roles = $user->roles;
                return self::compare_value( $roles, $operator, $value );

            case 'meta':
                if ( strpos( $value, ':' ) !== false ) {
                    list( $meta_key, $meta_value ) = explode( ':', $value, 2 );
                    $actual_value = get_post_meta( get_the_ID(), $meta_key, true );
                    return self::compare_value( $actual_value, $operator, $meta_value );
                }
                return false;

            default:
                return true;
        }
    }

    /**
     * Compare values with operator.
     *
     * @param mixed  $actual   Actual value.
     * @param string $operator Comparison operator.
     * @param mixed  $expected Expected value.
     *
     * @return bool
     */
    private static function compare_value( $actual, $operator, $expected ) {
        switch ( $operator ) {
            case 'is':
                if ( is_array( $actual ) ) {
                    return in_array( $expected, $actual, true );
                }
                return $actual === $expected;

            case 'is_not':
                if ( is_array( $actual ) ) {
                    return ! in_array( $expected, $actual, true );
                }
                return $actual !== $expected;

            case 'contains':
                if ( is_array( $actual ) ) {
                    foreach ( $actual as $item ) {
                        if ( strpos( $item, $expected ) !== false ) {
                            return true;
                        }
                    }
                    return false;
                }
                return strpos( $actual, $expected ) !== false;

            case 'not_contains':
                if ( is_array( $actual ) ) {
                    foreach ( $actual as $item ) {
                        if ( strpos( $item, $expected ) !== false ) {
                            return false;
                        }
                    }
                    return true;
                }
                return strpos( $actual, $expected ) === false;

            default:
                return true;
        }
    }

    /**
     * Create a new template.
     *
     * @param array $data Template data.
     *
     * @return int|false Template ID or false on failure.
     */
    public static function create( $data ) {
        global $wpdb;
        $table = CodeSite_Database::templates_table();

        $defaults = array(
            'name'             => '',
            'slug'             => '',
            'template_type'    => 'page',
            'header_layout_id' => null,
            'footer_layout_id' => null,
            'content_blocks'   => '[]',
            'custom_html'      => '',
            'custom_css'       => '',
            'custom_js'        => '',
            'conditions'       => '{}',
            'priority'         => 10,
            'status'           => 'active',
        );

        $data = wp_parse_args( $data, $defaults );

        // Generate slug if empty.
        if ( empty( $data['slug'] ) ) {
            $data['slug'] = sanitize_title( $data['name'] );
        }

        // Ensure unique slug.
        $data['slug'] = self::unique_slug( $data['slug'] );

        // Ensure JSON fields.
        if ( is_array( $data['content_blocks'] ) ) {
            $data['content_blocks'] = wp_json_encode( $data['content_blocks'] );
        }
        if ( is_array( $data['conditions'] ) ) {
            $data['conditions'] = wp_json_encode( $data['conditions'] );
        }

        $result = $wpdb->insert(
            $table,
            array(
                'name'             => sanitize_text_field( $data['name'] ),
                'slug'             => sanitize_title( $data['slug'] ),
                'template_type'    => sanitize_text_field( $data['template_type'] ),
                'header_layout_id' => $data['header_layout_id'] ? absint( $data['header_layout_id'] ) : null,
                'footer_layout_id' => $data['footer_layout_id'] ? absint( $data['footer_layout_id'] ) : null,
                'content_blocks'   => $data['content_blocks'],
                'custom_html'      => $data['custom_html'],
                'custom_css'       => $data['custom_css'],
                'custom_js'        => $data['custom_js'],
                'conditions'       => $data['conditions'],
                'priority'         => absint( $data['priority'] ),
                'status'           => in_array( $data['status'], array( 'active', 'draft', 'trash' ), true ) ? $data['status'] : 'active',
            ),
            array( '%s', '%s', '%s', '%d', '%d', '%s', '%s', '%s', '%s', '%s', '%d', '%s' )
        );

        if ( $result ) {
            return $wpdb->insert_id;
        }

        return false;
    }

    /**
     * Update a template.
     *
     * @param int   $id   Template ID.
     * @param array $data Template data.
     *
     * @return bool
     */
    public static function update( $id, $data ) {
        global $wpdb;
        $table = CodeSite_Database::templates_table();

        $update_data   = array();
        $update_format = array();

        $string_fields = array( 'name', 'slug', 'template_type', 'custom_html', 'custom_css', 'custom_js' );
        foreach ( $string_fields as $field ) {
            if ( isset( $data[ $field ] ) ) {
                $update_data[ $field ] = $field === 'slug' ? sanitize_title( $data[ $field ] ) : $data[ $field ];
                $update_format[]       = '%s';
            }
        }

        $int_fields = array( 'header_layout_id', 'footer_layout_id', 'priority' );
        foreach ( $int_fields as $field ) {
            if ( isset( $data[ $field ] ) ) {
                $update_data[ $field ] = $data[ $field ] ? absint( $data[ $field ] ) : null;
                $update_format[]       = '%d';
            }
        }

        $json_fields = array( 'content_blocks', 'conditions' );
        foreach ( $json_fields as $field ) {
            if ( isset( $data[ $field ] ) ) {
                $update_data[ $field ] = is_array( $data[ $field ] ) ? wp_json_encode( $data[ $field ] ) : $data[ $field ];
                $update_format[]       = '%s';
            }
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
     * Delete a template.
     *
     * @param int $id Template ID.
     *
     * @return bool
     */
    public static function delete( $id ) {
        global $wpdb;
        $table = CodeSite_Database::templates_table();

        return $wpdb->delete( $table, array( 'id' => $id ), array( '%d' ) ) !== false;
    }

    /**
     * Get unique slug.
     *
     * @param string $slug Base slug.
     * @param int    $id   Exclude template ID.
     *
     * @return string
     */
    private static function unique_slug( $slug, $id = 0 ) {
        global $wpdb;
        $table = CodeSite_Database::templates_table();

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
     * Get available template types.
     *
     * @return array
     */
    public static function get_template_types() {
        $types = array(
            'front-page'       => __( 'Front Page', 'codesite' ),
            'home'             => __( 'Blog Index', 'codesite' ),
            'single-post'      => __( 'Single Post', 'codesite' ),
            'page'             => __( 'Page', 'codesite' ),
            'archive'          => __( 'Archive', 'codesite' ),
            'archive-category' => __( 'Category Archive', 'codesite' ),
            'archive-tag'      => __( 'Tag Archive', 'codesite' ),
            'archive-author'   => __( 'Author Archive', 'codesite' ),
            'archive-date'     => __( 'Date Archive', 'codesite' ),
            'search'           => __( 'Search Results', 'codesite' ),
            '404'              => __( '404 Page', 'codesite' ),
        );

        // Add CPT types.
        $post_types = get_post_types( array( 'public' => true, '_builtin' => false ), 'objects' );
        foreach ( $post_types as $post_type ) {
            $types[ 'single-' . $post_type->name ]  = sprintf( __( 'Single %s', 'codesite' ), $post_type->labels->singular_name );
            $types[ 'archive-' . $post_type->name ] = sprintf( __( '%s Archive', 'codesite' ), $post_type->labels->singular_name );
        }

        return $types;
    }

    /**
     * Count templates.
     *
     * @param string $status Optional status filter.
     *
     * @return int
     */
    public static function count( $status = null ) {
        global $wpdb;
        $table = CodeSite_Database::templates_table();

        if ( $status ) {
            return (int) $wpdb->get_var(
                $wpdb->prepare( "SELECT COUNT(*) FROM $table WHERE status = %s", $status )
            );
        }

        return (int) $wpdb->get_var( "SELECT COUNT(*) FROM $table" );
    }
}
