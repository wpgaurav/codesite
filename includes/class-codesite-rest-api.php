<?php
/**
 * REST API for CodeSite.
 */
class CodeSite_REST_API {

    /**
     * Namespace for the API.
     *
     * @var string
     */
    private $namespace = 'codesite/v1';

    /**
     * Register REST routes.
     */
    public function register_routes() {
        // Blocks.
        register_rest_route(
            $this->namespace,
            '/blocks',
            array(
                array(
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => array( $this, 'get_blocks' ),
                    'permission_callback' => array( $this, 'can_edit' ),
                ),
                array(
                    'methods'             => WP_REST_Server::CREATABLE,
                    'callback'            => array( $this, 'create_block' ),
                    'permission_callback' => array( $this, 'can_edit' ),
                ),
            )
        );

        register_rest_route(
            $this->namespace,
            '/blocks/(?P<id>\d+)',
            array(
                array(
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => array( $this, 'get_block' ),
                    'permission_callback' => array( $this, 'can_edit' ),
                ),
                array(
                    'methods'             => WP_REST_Server::EDITABLE,
                    'callback'            => array( $this, 'update_block' ),
                    'permission_callback' => array( $this, 'can_edit' ),
                ),
                array(
                    'methods'             => WP_REST_Server::DELETABLE,
                    'callback'            => array( $this, 'delete_block' ),
                    'permission_callback' => array( $this, 'can_manage' ),
                ),
            )
        );

        register_rest_route(
            $this->namespace,
            '/blocks/(?P<id>\d+)/duplicate',
            array(
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => array( $this, 'duplicate_block' ),
                'permission_callback' => array( $this, 'can_edit' ),
            )
        );

        // Layouts.
        register_rest_route(
            $this->namespace,
            '/layouts',
            array(
                array(
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => array( $this, 'get_layouts' ),
                    'permission_callback' => array( $this, 'can_edit' ),
                ),
                array(
                    'methods'             => WP_REST_Server::CREATABLE,
                    'callback'            => array( $this, 'create_layout' ),
                    'permission_callback' => array( $this, 'can_edit' ),
                ),
            )
        );

        register_rest_route(
            $this->namespace,
            '/layouts/(?P<id>\d+)',
            array(
                array(
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => array( $this, 'get_layout' ),
                    'permission_callback' => array( $this, 'can_edit' ),
                ),
                array(
                    'methods'             => WP_REST_Server::EDITABLE,
                    'callback'            => array( $this, 'update_layout' ),
                    'permission_callback' => array( $this, 'can_edit' ),
                ),
                array(
                    'methods'             => WP_REST_Server::DELETABLE,
                    'callback'            => array( $this, 'delete_layout' ),
                    'permission_callback' => array( $this, 'can_manage' ),
                ),
            )
        );

        // Templates.
        register_rest_route(
            $this->namespace,
            '/templates',
            array(
                array(
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => array( $this, 'get_templates' ),
                    'permission_callback' => array( $this, 'can_edit' ),
                ),
                array(
                    'methods'             => WP_REST_Server::CREATABLE,
                    'callback'            => array( $this, 'create_template' ),
                    'permission_callback' => array( $this, 'can_manage' ),
                ),
            )
        );

        register_rest_route(
            $this->namespace,
            '/templates/(?P<id>\d+)',
            array(
                array(
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => array( $this, 'get_template' ),
                    'permission_callback' => array( $this, 'can_edit' ),
                ),
                array(
                    'methods'             => WP_REST_Server::EDITABLE,
                    'callback'            => array( $this, 'update_template' ),
                    'permission_callback' => array( $this, 'can_manage' ),
                ),
                array(
                    'methods'             => WP_REST_Server::DELETABLE,
                    'callback'            => array( $this, 'delete_template' ),
                    'permission_callback' => array( $this, 'can_manage' ),
                ),
            )
        );

        // Rendering.
        register_rest_route(
            $this->namespace,
            '/render/block',
            array(
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => array( $this, 'render_block' ),
                'permission_callback' => array( $this, 'can_edit' ),
            )
        );

        register_rest_route(
            $this->namespace,
            '/render/layout',
            array(
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => array( $this, 'render_layout' ),
                'permission_callback' => array( $this, 'can_edit' ),
            )
        );

        register_rest_route(
            $this->namespace,
            '/render/preview',
            array(
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => array( $this, 'render_preview' ),
                'permission_callback' => array( $this, 'can_edit' ),
            )
        );

        // Utilities.
        register_rest_route(
            $this->namespace,
            '/shortcodes',
            array(
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => array( $this, 'get_shortcodes' ),
                'permission_callback' => array( $this, 'can_edit' ),
            )
        );

        register_rest_route(
            $this->namespace,
            '/dynamic-fields',
            array(
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => array( $this, 'get_dynamic_fields' ),
                'permission_callback' => array( $this, 'can_edit' ),
            )
        );

        register_rest_route(
            $this->namespace,
            '/template-types',
            array(
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => array( $this, 'get_template_types' ),
                'permission_callback' => array( $this, 'can_edit' ),
            )
        );

        register_rest_route(
            $this->namespace,
            '/settings',
            array(
                array(
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => array( $this, 'get_settings' ),
                    'permission_callback' => array( $this, 'can_manage' ),
                ),
                array(
                    'methods'             => WP_REST_Server::EDITABLE,
                    'callback'            => array( $this, 'update_settings' ),
                    'permission_callback' => array( $this, 'can_manage' ),
                ),
            )
        );
    }

    /**
     * Check if user can edit blocks.
     *
     * @return bool
     */
    public function can_edit() {
        return current_user_can( 'edit_codesite_blocks' );
    }

    /**
     * Check if user can manage CodeSite.
     *
     * @return bool
     */
    public function can_manage() {
        return current_user_can( 'manage_codesite' );
    }

    // Blocks.

    /**
     * Get all blocks.
     *
     * @param WP_REST_Request $request Request object.
     *
     * @return WP_REST_Response
     */
    public function get_blocks( $request ) {
        $args = array(
            'status'   => $request->get_param( 'status' ),
            'category' => $request->get_param( 'category' ),
        );

        $blocks = CodeSite_Blocks::get_all( array_filter( $args ) );

        return rest_ensure_response( $blocks );
    }

    /**
     * Get a single block.
     *
     * @param WP_REST_Request $request Request object.
     *
     * @return WP_REST_Response|WP_Error
     */
    public function get_block( $request ) {
        $block = CodeSite_Blocks::get( $request['id'] );

        if ( ! $block ) {
            return new WP_Error( 'not_found', __( 'Block not found.', 'codesite' ), array( 'status' => 404 ) );
        }

        return rest_ensure_response( $block );
    }

    /**
     * Create a block.
     *
     * @param WP_REST_Request $request Request object.
     *
     * @return WP_REST_Response|WP_Error
     */
    public function create_block( $request ) {
        $data = array(
            'name'      => $request->get_param( 'name' ),
            'slug'      => $request->get_param( 'slug' ),
            'html'      => $request->get_param( 'html' ),
            'css'       => $request->get_param( 'css' ),
            'js'        => $request->get_param( 'js' ),
            'category'  => $request->get_param( 'category' ),
            'css_scope' => $request->get_param( 'css_scope' ),
            'status'    => $request->get_param( 'status' ),
        );

        $id = CodeSite_Blocks::create( array_filter( $data, function( $v ) { return $v !== null; } ) );

        if ( ! $id ) {
            return new WP_Error( 'create_failed', __( 'Failed to create block.', 'codesite' ), array( 'status' => 500 ) );
        }

        $block = CodeSite_Blocks::get( $id );

        return rest_ensure_response( $block );
    }

    /**
     * Update a block.
     *
     * @param WP_REST_Request $request Request object.
     *
     * @return WP_REST_Response|WP_Error
     */
    public function update_block( $request ) {
        $block = CodeSite_Blocks::get( $request['id'] );

        if ( ! $block ) {
            return new WP_Error( 'not_found', __( 'Block not found.', 'codesite' ), array( 'status' => 404 ) );
        }

        $data = array();
        $fields = array( 'name', 'slug', 'html', 'css', 'js', 'category', 'css_scope', 'status' );

        foreach ( $fields as $field ) {
            $value = $request->get_param( $field );
            if ( $value !== null ) {
                $data[ $field ] = $value;
            }
        }

        $result = CodeSite_Blocks::update( $request['id'], $data );

        if ( ! $result ) {
            return new WP_Error( 'update_failed', __( 'Failed to update block.', 'codesite' ), array( 'status' => 500 ) );
        }

        $block = CodeSite_Blocks::get( $request['id'] );

        return rest_ensure_response( $block );
    }

    /**
     * Delete a block.
     *
     * @param WP_REST_Request $request Request object.
     *
     * @return WP_REST_Response|WP_Error
     */
    public function delete_block( $request ) {
        $block = CodeSite_Blocks::get( $request['id'] );

        if ( ! $block ) {
            return new WP_Error( 'not_found', __( 'Block not found.', 'codesite' ), array( 'status' => 404 ) );
        }

        $result = CodeSite_Blocks::delete( $request['id'] );

        if ( ! $result ) {
            return new WP_Error( 'delete_failed', __( 'Failed to delete block.', 'codesite' ), array( 'status' => 500 ) );
        }

        return rest_ensure_response( array( 'deleted' => true ) );
    }

    /**
     * Duplicate a block.
     *
     * @param WP_REST_Request $request Request object.
     *
     * @return WP_REST_Response|WP_Error
     */
    public function duplicate_block( $request ) {
        $id = CodeSite_Blocks::duplicate( $request['id'] );

        if ( ! $id ) {
            return new WP_Error( 'duplicate_failed', __( 'Failed to duplicate block.', 'codesite' ), array( 'status' => 500 ) );
        }

        $block = CodeSite_Blocks::get( $id );

        return rest_ensure_response( $block );
    }

    // Layouts.

    /**
     * Get all layouts.
     *
     * @param WP_REST_Request $request Request object.
     *
     * @return WP_REST_Response
     */
    public function get_layouts( $request ) {
        $args = array(
            'status' => $request->get_param( 'status' ),
            'type'   => $request->get_param( 'type' ),
        );

        $layouts = CodeSite_Layouts::get_all( array_filter( $args ) );

        return rest_ensure_response( $layouts );
    }

    /**
     * Get a single layout.
     *
     * @param WP_REST_Request $request Request object.
     *
     * @return WP_REST_Response|WP_Error
     */
    public function get_layout( $request ) {
        $layout = CodeSite_Layouts::get( $request['id'] );

        if ( ! $layout ) {
            return new WP_Error( 'not_found', __( 'Layout not found.', 'codesite' ), array( 'status' => 404 ) );
        }

        return rest_ensure_response( $layout );
    }

    /**
     * Create a layout.
     *
     * @param WP_REST_Request $request Request object.
     *
     * @return WP_REST_Response|WP_Error
     */
    public function create_layout( $request ) {
        $data = array(
            'name'        => $request->get_param( 'name' ),
            'slug'        => $request->get_param( 'slug' ),
            'type'        => $request->get_param( 'type' ),
            'block_order' => $request->get_param( 'block_order' ),
            'custom_html' => $request->get_param( 'custom_html' ),
            'custom_css'  => $request->get_param( 'custom_css' ),
            'custom_js'   => $request->get_param( 'custom_js' ),
            'use_blocks'  => $request->get_param( 'use_blocks' ),
            'status'      => $request->get_param( 'status' ),
        );

        $id = CodeSite_Layouts::create( array_filter( $data, function( $v ) { return $v !== null; } ) );

        if ( ! $id ) {
            return new WP_Error( 'create_failed', __( 'Failed to create layout.', 'codesite' ), array( 'status' => 500 ) );
        }

        $layout = CodeSite_Layouts::get( $id );

        return rest_ensure_response( $layout );
    }

    /**
     * Update a layout.
     *
     * @param WP_REST_Request $request Request object.
     *
     * @return WP_REST_Response|WP_Error
     */
    public function update_layout( $request ) {
        $layout = CodeSite_Layouts::get( $request['id'] );

        if ( ! $layout ) {
            return new WP_Error( 'not_found', __( 'Layout not found.', 'codesite' ), array( 'status' => 404 ) );
        }

        $data = array();
        $fields = array( 'name', 'slug', 'type', 'block_order', 'custom_html', 'custom_css', 'custom_js', 'use_blocks', 'status' );

        foreach ( $fields as $field ) {
            $value = $request->get_param( $field );
            if ( $value !== null ) {
                $data[ $field ] = $value;
            }
        }

        $result = CodeSite_Layouts::update( $request['id'], $data );

        if ( ! $result ) {
            return new WP_Error( 'update_failed', __( 'Failed to update layout.', 'codesite' ), array( 'status' => 500 ) );
        }

        $layout = CodeSite_Layouts::get( $request['id'] );

        return rest_ensure_response( $layout );
    }

    /**
     * Delete a layout.
     *
     * @param WP_REST_Request $request Request object.
     *
     * @return WP_REST_Response|WP_Error
     */
    public function delete_layout( $request ) {
        $layout = CodeSite_Layouts::get( $request['id'] );

        if ( ! $layout ) {
            return new WP_Error( 'not_found', __( 'Layout not found.', 'codesite' ), array( 'status' => 404 ) );
        }

        $result = CodeSite_Layouts::delete( $request['id'] );

        if ( ! $result ) {
            return new WP_Error( 'delete_failed', __( 'Failed to delete layout.', 'codesite' ), array( 'status' => 500 ) );
        }

        return rest_ensure_response( array( 'deleted' => true ) );
    }

    // Templates.

    /**
     * Get all templates.
     *
     * @param WP_REST_Request $request Request object.
     *
     * @return WP_REST_Response
     */
    public function get_templates( $request ) {
        $args = array(
            'status'        => $request->get_param( 'status' ),
            'template_type' => $request->get_param( 'template_type' ),
        );

        $templates = CodeSite_Templates::get_all( array_filter( $args ) );

        return rest_ensure_response( $templates );
    }

    /**
     * Get a single template.
     *
     * @param WP_REST_Request $request Request object.
     *
     * @return WP_REST_Response|WP_Error
     */
    public function get_template( $request ) {
        $template = CodeSite_Templates::get( $request['id'] );

        if ( ! $template ) {
            return new WP_Error( 'not_found', __( 'Template not found.', 'codesite' ), array( 'status' => 404 ) );
        }

        return rest_ensure_response( $template );
    }

    /**
     * Create a template.
     *
     * @param WP_REST_Request $request Request object.
     *
     * @return WP_REST_Response|WP_Error
     */
    public function create_template( $request ) {
        $data = array(
            'name'             => $request->get_param( 'name' ),
            'slug'             => $request->get_param( 'slug' ),
            'template_type'    => $request->get_param( 'template_type' ),
            'header_layout_id' => $request->get_param( 'header_layout_id' ),
            'footer_layout_id' => $request->get_param( 'footer_layout_id' ),
            'content_blocks'   => $request->get_param( 'content_blocks' ),
            'custom_html'      => $request->get_param( 'custom_html' ),
            'custom_css'       => $request->get_param( 'custom_css' ),
            'custom_js'        => $request->get_param( 'custom_js' ),
            'conditions'       => $request->get_param( 'conditions' ),
            'priority'         => $request->get_param( 'priority' ),
            'status'           => $request->get_param( 'status' ),
        );

        $id = CodeSite_Templates::create( array_filter( $data, function( $v ) { return $v !== null; } ) );

        if ( ! $id ) {
            return new WP_Error( 'create_failed', __( 'Failed to create template.', 'codesite' ), array( 'status' => 500 ) );
        }

        $template = CodeSite_Templates::get( $id );

        return rest_ensure_response( $template );
    }

    /**
     * Update a template.
     *
     * @param WP_REST_Request $request Request object.
     *
     * @return WP_REST_Response|WP_Error
     */
    public function update_template( $request ) {
        $template = CodeSite_Templates::get( $request['id'] );

        if ( ! $template ) {
            return new WP_Error( 'not_found', __( 'Template not found.', 'codesite' ), array( 'status' => 404 ) );
        }

        $data = array();
        $fields = array( 'name', 'slug', 'template_type', 'header_layout_id', 'footer_layout_id', 'content_blocks', 'custom_html', 'custom_css', 'custom_js', 'conditions', 'priority', 'status' );

        foreach ( $fields as $field ) {
            $value = $request->get_param( $field );
            if ( $value !== null ) {
                $data[ $field ] = $value;
            }
        }

        $result = CodeSite_Templates::update( $request['id'], $data );

        if ( ! $result ) {
            return new WP_Error( 'update_failed', __( 'Failed to update template.', 'codesite' ), array( 'status' => 500 ) );
        }

        $template = CodeSite_Templates::get( $request['id'] );

        return rest_ensure_response( $template );
    }

    /**
     * Delete a template.
     *
     * @param WP_REST_Request $request Request object.
     *
     * @return WP_REST_Response|WP_Error
     */
    public function delete_template( $request ) {
        $template = CodeSite_Templates::get( $request['id'] );

        if ( ! $template ) {
            return new WP_Error( 'not_found', __( 'Template not found.', 'codesite' ), array( 'status' => 404 ) );
        }

        $result = CodeSite_Templates::delete( $request['id'] );

        if ( ! $result ) {
            return new WP_Error( 'delete_failed', __( 'Failed to delete template.', 'codesite' ), array( 'status' => 500 ) );
        }

        return rest_ensure_response( array( 'deleted' => true ) );
    }

    // Rendering.

    /**
     * Render a block preview.
     *
     * @param WP_REST_Request $request Request object.
     *
     * @return WP_REST_Response
     */
    public function render_block( $request ) {
        $block_id = $request->get_param( 'id' );

        if ( $block_id ) {
            $html = CodeSite_Renderer::render_block( $block_id );
        } else {
            // Render from provided data.
            $block = (object) array(
                'id'        => 0,
                'html'      => $request->get_param( 'html' ) ?: '',
                'css'       => $request->get_param( 'css' ) ?: '',
                'js'        => $request->get_param( 'js' ) ?: '',
                'css_scope' => $request->get_param( 'css_scope' ) ?: 'scoped',
                'status'    => 'active',
            );

            $html = CodeSite_Dynamic_Content::parse( $block->html );
            $html = CodeSite_Shortcode_Parser::parse( $html );
        }

        $css = CodeSite_CSS_Compiler::get();

        return rest_ensure_response(
            array(
                'html' => $html,
                'css'  => $css,
            )
        );
    }

    /**
     * Render a layout preview.
     *
     * @param WP_REST_Request $request Request object.
     *
     * @return WP_REST_Response
     */
    public function render_layout( $request ) {
        $layout_id = $request->get_param( 'id' );
        $html      = CodeSite_Renderer::render_layout( $layout_id );
        $css       = CodeSite_CSS_Compiler::get();

        return rest_ensure_response(
            array(
                'html' => $html,
                'css'  => $css,
            )
        );
    }

    /**
     * Render a live preview.
     *
     * @param WP_REST_Request $request Request object.
     *
     * @return WP_REST_Response
     */
    public function render_preview( $request ) {
        $html = $request->get_param( 'html' ) ?: '';
        $css  = $request->get_param( 'css' ) ?: '';
        $js   = $request->get_param( 'js' ) ?: '';

        // Parse dynamic content.
        $html = CodeSite_Dynamic_Content::parse( $html );

        // Parse shortcodes.
        $html = CodeSite_Shortcode_Parser::parse( $html );

        // Parse Tangible.
        $tangible = new CodeSite_Tangible_Integration();
        $html     = $tangible->parse( $html );

        return rest_ensure_response(
            array(
                'html' => $html,
                'css'  => $css,
                'js'   => $js,
            )
        );
    }

    // Utilities.

    /**
     * Get available shortcodes.
     *
     * @return WP_REST_Response
     */
    public function get_shortcodes() {
        $shortcodes = CodeSite_Shortcode_Parser::get_available_shortcodes();

        return rest_ensure_response( $shortcodes );
    }

    /**
     * Get available dynamic fields.
     *
     * @return WP_REST_Response
     */
    public function get_dynamic_fields() {
        $fields  = CodeSite_Dynamic_Content::get_available_fields();
        $filters = CodeSite_Dynamic_Content::get_available_filters();

        return rest_ensure_response(
            array(
                'fields'  => $fields,
                'filters' => $filters,
            )
        );
    }

    /**
     * Get template types.
     *
     * @return WP_REST_Response
     */
    public function get_template_types() {
        $types = CodeSite_Templates::get_template_types();

        return rest_ensure_response( $types );
    }

    /**
     * Get settings.
     *
     * @return WP_REST_Response
     */
    public function get_settings() {
        $settings = CodeSite_Database::get_all_settings();

        return rest_ensure_response( $settings );
    }

    /**
     * Update settings.
     *
     * @param WP_REST_Request $request Request object.
     *
     * @return WP_REST_Response
     */
    public function update_settings( $request ) {
        $settings = $request->get_json_params();
        $result   = CodeSite_Database::update_all_settings( $settings );

        return rest_ensure_response(
            array(
                'success'  => $result,
                'settings' => CodeSite_Database::get_all_settings(),
            )
        );
    }
}
