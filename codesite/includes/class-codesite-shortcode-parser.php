<?php
/**
 * Shortcode parser for CodeSite.
 *
 * Parses <SCD>shortcode</SCD> syntax.
 */
class CodeSite_Shortcode_Parser {

    /**
     * Parse content and execute shortcodes.
     *
     * @param string $content Content to parse.
     *
     * @return string
     */
    public static function parse( $content ) {
        // Match <SCD>...</SCD> tags.
        $pattern = '/<SCD>(.*?)<\/SCD>/s';

        return preg_replace_callback(
            $pattern,
            function ( $matches ) {
                $shortcode = $matches[1];
                return do_shortcode( $shortcode );
            },
            $content
        );
    }

    /**
     * Get all registered shortcodes.
     *
     * @return array
     */
    public static function get_available_shortcodes() {
        global $shortcode_tags;

        $shortcodes = array();

        foreach ( $shortcode_tags as $tag => $callback ) {
            $shortcodes[] = array(
                'tag'         => $tag,
                'description' => self::get_shortcode_description( $tag ),
            );
        }

        return $shortcodes;
    }

    /**
     * Get shortcode description.
     *
     * @param string $tag Shortcode tag.
     *
     * @return string
     */
    private static function get_shortcode_description( $tag ) {
        // Common shortcode descriptions.
        $descriptions = array(
            'gallery'              => __( 'WordPress Gallery', 'codesite' ),
            'caption'              => __( 'Image Caption', 'codesite' ),
            'audio'                => __( 'Audio Player', 'codesite' ),
            'video'                => __( 'Video Player', 'codesite' ),
            'playlist'             => __( 'Media Playlist', 'codesite' ),
            'embed'                => __( 'Embed Content', 'codesite' ),
            'contact-form-7'       => __( 'Contact Form 7', 'codesite' ),
            'woocommerce_cart'     => __( 'WooCommerce Cart', 'codesite' ),
            'woocommerce_checkout' => __( 'WooCommerce Checkout', 'codesite' ),
            'woocommerce_my_account' => __( 'WooCommerce My Account', 'codesite' ),
            'products'             => __( 'WooCommerce Products', 'codesite' ),
        );

        return isset( $descriptions[ $tag ] ) ? $descriptions[ $tag ] : '';
    }

    /**
     * Wrap shortcode with SCD tags.
     *
     * @param string $shortcode Shortcode string.
     *
     * @return string
     */
    public static function wrap( $shortcode ) {
        return '<SCD>' . $shortcode . '</SCD>';
    }
}
