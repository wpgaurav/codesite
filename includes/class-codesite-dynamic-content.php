<?php
/**
 * Dynamic content parser for CodeSite.
 *
 * Parses {{field}} syntax and replaces with actual values.
 */
class CodeSite_Dynamic_Content {

    /**
     * Parse content and replace dynamic fields.
     *
     * @param string $content Content to parse.
     * @param array  $context Additional context data.
     *
     * @return string
     */
    public static function parse( $content, $context = array() ) {
        // Match {{field}} or {{field|filter}} or {{field|filter:arg}}
        $pattern = '/\{\{([a-z_:]+)(\|[a-z_:,0-9]+)*\}\}/i';

        return preg_replace_callback(
            $pattern,
            function ( $matches ) use ( $context ) {
                $full_match = $matches[0];
                $field      = $matches[1];
                $filters    = isset( $matches[2] ) ? array_filter( explode( '|', trim( $matches[2], '|' ) ) ) : array();

                // Get raw value.
                $value = self::get_field_value( $field, $context );

                // Apply filters.
                foreach ( $filters as $filter ) {
                    $value = self::apply_filter( $value, $filter );
                }

                return $value;
            },
            $content
        );
    }

    /**
     * Get field value.
     *
     * @param string $field   Field name.
     * @param array  $context Context data.
     *
     * @return mixed
     */
    private static function get_field_value( $field, $context = array() ) {
        // Site fields.
        if ( strpos( $field, 'site_' ) === 0 ) {
            return self::get_site_field( $field );
        }

        // Post fields.
        if ( strpos( $field, 'post_' ) === 0 ) {
            return self::get_post_field( $field, $context );
        }

        // User fields.
        if ( strpos( $field, 'user_' ) === 0 ) {
            return self::get_user_field( $field );
        }

        // ACF fields.
        if ( strpos( $field, 'acf:' ) === 0 ) {
            $acf_field = str_replace( 'acf:', '', $field );
            if ( function_exists( 'get_field' ) ) {
                return get_field( $acf_field );
            }
            return '';
        }

        // Meta fields.
        if ( strpos( $field, 'post_meta:' ) === 0 ) {
            $meta_key = str_replace( 'post_meta:', '', $field );
            return get_post_meta( get_the_ID(), $meta_key, true );
        }

        // Menu.
        if ( strpos( $field, 'menu:' ) === 0 ) {
            $location = str_replace( 'menu:', '', $field );
            return wp_nav_menu(
                array(
                    'theme_location' => $location,
                    'echo'           => false,
                    'fallback_cb'    => false,
                )
            );
        }

        // Widget.
        if ( strpos( $field, 'widget:' ) === 0 ) {
            $sidebar_id = str_replace( 'widget:', '', $field );
            ob_start();
            dynamic_sidebar( $sidebar_id );
            return ob_get_clean();
        }

        // Current year.
        if ( $field === 'current_year' ) {
            return date( 'Y' );
        }

        // Current date.
        if ( $field === 'current_date' ) {
            return date_i18n( get_option( 'date_format' ) );
        }

        // Context data.
        if ( isset( $context[ $field ] ) ) {
            return $context[ $field ];
        }

        return '';
    }

    /**
     * Get site field value.
     *
     * @param string $field Field name.
     *
     * @return string
     */
    private static function get_site_field( $field ) {
        switch ( $field ) {
            case 'site_name':
                return get_bloginfo( 'name' );

            case 'site_description':
                return get_bloginfo( 'description' );

            case 'site_url':
                return home_url();

            case 'site_logo':
                $logo_id = get_theme_mod( 'custom_logo' );
                if ( $logo_id ) {
                    return wp_get_attachment_image_url( $logo_id, 'full' );
                }
                return '';

            case 'site_logo_img':
                $logo_id = get_theme_mod( 'custom_logo' );
                if ( $logo_id ) {
                    return wp_get_attachment_image( $logo_id, 'full', false, array( 'class' => 'codesite-logo' ) );
                }
                return '';

            case 'site_admin_email':
                return get_option( 'admin_email' );

            case 'site_language':
                return get_bloginfo( 'language' );

            default:
                return '';
        }
    }

    /**
     * Get post field value.
     *
     * @param string $field   Field name.
     * @param array  $context Context data.
     *
     * @return string
     */
    private static function get_post_field( $field, $context = array() ) {
        $post_id = isset( $context['post_id'] ) ? $context['post_id'] : get_the_ID();
        $post    = get_post( $post_id );

        if ( ! $post ) {
            return '';
        }

        switch ( $field ) {
            case 'post_title':
                return get_the_title( $post );

            case 'post_content':
                return apply_filters( 'the_content', $post->post_content );

            case 'post_excerpt':
                return has_excerpt( $post ) ? get_the_excerpt( $post ) : wp_trim_words( $post->post_content, 55 );

            case 'post_date':
                return get_the_date( '', $post );

            case 'post_modified':
                return get_the_modified_date( '', $post );

            case 'post_author':
                return get_the_author_meta( 'display_name', $post->post_author );

            case 'post_author_avatar':
                return get_avatar_url( $post->post_author );

            case 'post_author_bio':
                return get_the_author_meta( 'description', $post->post_author );

            case 'post_thumbnail':
                return get_the_post_thumbnail_url( $post, 'full' );

            case 'post_thumbnail_img':
                return get_the_post_thumbnail( $post, 'full' );

            case 'post_categories':
                $categories = get_the_category( $post_id );
                if ( empty( $categories ) ) {
                    return '';
                }
                $links = array();
                foreach ( $categories as $cat ) {
                    $links[] = sprintf(
                        '<a href="%s">%s</a>',
                        esc_url( get_category_link( $cat->term_id ) ),
                        esc_html( $cat->name )
                    );
                }
                return implode( ', ', $links );

            case 'post_tags':
                $tags = get_the_tags( $post_id );
                if ( empty( $tags ) ) {
                    return '';
                }
                $links = array();
                foreach ( $tags as $tag ) {
                    $links[] = sprintf(
                        '<a href="%s">%s</a>',
                        esc_url( get_tag_link( $tag->term_id ) ),
                        esc_html( $tag->name )
                    );
                }
                return implode( ', ', $links );

            case 'post_id':
                return $post_id;

            case 'post_url':
                return get_permalink( $post );

            case 'post_type':
                return get_post_type( $post );

            case 'post_status':
                return $post->post_status;

            case 'post_comment_count':
                return $post->comment_count;

            default:
                return '';
        }
    }

    /**
     * Get user field value.
     *
     * @param string $field Field name.
     *
     * @return string
     */
    private static function get_user_field( $field ) {
        if ( ! is_user_logged_in() ) {
            return '';
        }

        $user = wp_get_current_user();

        switch ( $field ) {
            case 'user_name':
                return $user->display_name;

            case 'user_login':
                return $user->user_login;

            case 'user_email':
                return $user->user_email;

            case 'user_avatar':
                return get_avatar_url( $user->ID );

            case 'user_avatar_img':
                return get_avatar( $user->ID );

            case 'user_bio':
                return $user->description;

            case 'user_url':
                return $user->user_url;

            default:
                return '';
        }
    }

    /**
     * Apply filter to value.
     *
     * @param mixed  $value  Value to filter.
     * @param string $filter Filter string.
     *
     * @return mixed
     */
    private static function apply_filter( $value, $filter ) {
        // Parse filter and argument.
        $parts = explode( ':', $filter, 2 );
        $name  = $parts[0];
        $arg   = isset( $parts[1] ) ? $parts[1] : null;

        switch ( $name ) {
            case 'upper':
                return strtoupper( $value );

            case 'lower':
                return strtolower( $value );

            case 'ucfirst':
                return ucfirst( $value );

            case 'ucwords':
                return ucwords( $value );

            case 'truncate':
                $length = $arg ? intval( $arg ) : 100;
                if ( strlen( $value ) > $length ) {
                    return substr( $value, 0, $length ) . '...';
                }
                return $value;

            case 'words':
                $count = $arg ? intval( $arg ) : 20;
                return wp_trim_words( $value, $count );

            case 'date':
                $format = $arg ? $arg : get_option( 'date_format' );
                $timestamp = is_numeric( $value ) ? $value : strtotime( $value );
                return $timestamp ? date_i18n( $format, $timestamp ) : $value;

            case 'default':
                return empty( $value ) ? $arg : $value;

            case 'escape':
                return esc_html( $value );

            case 'raw':
                return $value;

            case 'strip_tags':
                return wp_strip_all_tags( $value );

            case 'nl2br':
                return nl2br( $value );

            case 'json':
                return wp_json_encode( $value );

            case 'urlencode':
                return urlencode( $value );

            case 'esc_attr':
                return esc_attr( $value );

            case 'esc_url':
                return esc_url( $value );

            default:
                return $value;
        }
    }

    /**
     * Get all available dynamic fields.
     *
     * @return array
     */
    public static function get_available_fields() {
        return array(
            'Site Fields'    => array(
                '{{site_name}}'        => __( 'Site title', 'codesite' ),
                '{{site_description}}' => __( 'Site tagline', 'codesite' ),
                '{{site_url}}'         => __( 'Home URL', 'codesite' ),
                '{{site_logo}}'        => __( 'Logo URL', 'codesite' ),
                '{{site_logo_img}}'    => __( 'Logo <img> tag', 'codesite' ),
            ),
            'Post Fields'    => array(
                '{{post_title}}'         => __( 'Post title', 'codesite' ),
                '{{post_content}}'       => __( 'Post content', 'codesite' ),
                '{{post_excerpt}}'       => __( 'Post excerpt', 'codesite' ),
                '{{post_date}}'          => __( 'Publish date', 'codesite' ),
                '{{post_author}}'        => __( 'Author name', 'codesite' ),
                '{{post_thumbnail}}'     => __( 'Featured image URL', 'codesite' ),
                '{{post_thumbnail_img}}' => __( 'Featured image <img> tag', 'codesite' ),
                '{{post_categories}}'    => __( 'Category links', 'codesite' ),
                '{{post_tags}}'          => __( 'Tag links', 'codesite' ),
                '{{post_url}}'           => __( 'Permalink', 'codesite' ),
                '{{post_id}}'            => __( 'Post ID', 'codesite' ),
            ),
            'User Fields'    => array(
                '{{user_name}}'   => __( 'Current user name', 'codesite' ),
                '{{user_avatar}}' => __( 'Current user avatar URL', 'codesite' ),
            ),
            'Date/Time'      => array(
                '{{current_year}}' => __( 'Current year', 'codesite' ),
                '{{current_date}}' => __( 'Current date', 'codesite' ),
            ),
            'Custom Fields'  => array(
                '{{post_meta:key}}'    => __( 'Post meta value', 'codesite' ),
                '{{acf:field_name}}'   => __( 'ACF field value', 'codesite' ),
            ),
            'Navigation'     => array(
                '{{menu:location}}'   => __( 'Navigation menu', 'codesite' ),
                '{{widget:sidebar}}' => __( 'Widget area', 'codesite' ),
            ),
        );
    }

    /**
     * Get available filters.
     *
     * @return array
     */
    public static function get_available_filters() {
        return array(
            'upper'        => __( 'Uppercase', 'codesite' ),
            'lower'        => __( 'Lowercase', 'codesite' ),
            'truncate:100' => __( 'Limit characters', 'codesite' ),
            'words:20'     => __( 'Limit words', 'codesite' ),
            'date:F j, Y'  => __( 'Date format', 'codesite' ),
            'default:text' => __( 'Fallback value', 'codesite' ),
            'escape'       => __( 'HTML escape', 'codesite' ),
            'raw'          => __( 'No escaping', 'codesite' ),
            'strip_tags'   => __( 'Remove HTML', 'codesite' ),
        );
    }
}
