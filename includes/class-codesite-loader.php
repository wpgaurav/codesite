<?php
/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 */
class CodeSite_Loader {

    /**
     * The array of actions registered with WordPress.
     *
     * @var array
     */
    protected $actions = array();

    /**
     * The array of filters registered with WordPress.
     *
     * @var array
     */
    protected $filters = array();

    /**
     * Initialize the class and set its properties.
     */
    public function __construct() {
        $this->load_dependencies();
        $this->set_locale();
        $this->define_admin_hooks();
        $this->define_public_hooks();
    }

    /**
     * Load the required dependencies for this plugin.
     */
    private function load_dependencies() {
        // Database operations.
        require_once CODESITE_PATH . 'includes/class-codesite-database.php';

        // Core model classes.
        require_once CODESITE_PATH . 'includes/class-codesite-blocks.php';
        require_once CODESITE_PATH . 'includes/class-codesite-layouts.php';
        require_once CODESITE_PATH . 'includes/class-codesite-templates.php';
        require_once CODESITE_PATH . 'includes/class-codesite-overrides.php';

        // Rendering.
        require_once CODESITE_PATH . 'includes/class-codesite-renderer.php';
        require_once CODESITE_PATH . 'includes/class-codesite-dynamic-content.php';
        require_once CODESITE_PATH . 'includes/class-codesite-shortcode-parser.php';
        require_once CODESITE_PATH . 'includes/class-codesite-css-compiler.php';

        // Theme override.
        require_once CODESITE_PATH . 'includes/class-codesite-theme-override.php';

        // Integrations.
        require_once CODESITE_PATH . 'includes/class-codesite-tangible-integration.php';

        // REST API.
        require_once CODESITE_PATH . 'includes/class-codesite-rest-api.php';

        // Admin.
        require_once CODESITE_PATH . 'admin/class-codesite-admin.php';

        // Public.
        require_once CODESITE_PATH . 'public/class-codesite-public.php';
    }

    /**
     * Define the locale for this plugin for internationalization.
     */
    private function set_locale() {
        $this->add_action( 'plugins_loaded', $this, 'load_plugin_textdomain' );
    }

    /**
     * Load the plugin text domain for translation.
     */
    public function load_plugin_textdomain() {
        load_plugin_textdomain(
            'codesite',
            false,
            dirname( CODESITE_BASENAME ) . '/languages/'
        );
    }

    /**
     * Register all of the hooks related to the admin area.
     */
    private function define_admin_hooks() {
        $admin = new CodeSite_Admin();

        $this->add_action( 'admin_menu', $admin, 'add_admin_menu' );
        $this->add_action( 'admin_enqueue_scripts', $admin, 'enqueue_styles' );
        $this->add_action( 'admin_enqueue_scripts', $admin, 'enqueue_scripts' );
        $this->add_action( 'add_meta_boxes', $admin, 'add_override_metabox' );
        $this->add_action( 'save_post', $admin, 'save_override_metabox' );

        // REST API.
        $rest_api = new CodeSite_REST_API();
        $this->add_action( 'rest_api_init', $rest_api, 'register_routes' );

        // Tangible integration.
        $tangible = new CodeSite_Tangible_Integration();
        $this->add_action( 'admin_notices', $tangible, 'maybe_show_tangible_notice' );
    }

    /**
     * Register all of the hooks related to the public-facing functionality.
     */
    private function define_public_hooks() {
        $public = new CodeSite_Public();

        $this->add_action( 'wp_enqueue_scripts', $public, 'enqueue_styles' );
        $this->add_action( 'wp_enqueue_scripts', $public, 'enqueue_scripts' );

        // Theme override.
        $theme_override = new CodeSite_Theme_Override();
        $this->add_filter( 'template_include', $theme_override, 'maybe_override_template', 999 );
        $this->add_action( 'wp_enqueue_scripts', $theme_override, 'maybe_dequeue_theme_assets', 999 );

        // Renderer.
        $renderer = new CodeSite_Renderer();
        $this->add_action( 'wp_head', $renderer, 'output_css', 99 );
        $this->add_action( 'wp_footer', $renderer, 'output_js', 99 );
    }

    /**
     * Add a new action to the collection.
     *
     * @param string $hook          The name of the WordPress action.
     * @param object $component     A reference to the instance of the object.
     * @param string $callback      The name of the function.
     * @param int    $priority      Optional. The priority. Default is 10.
     * @param int    $accepted_args Optional. The number of arguments. Default is 1.
     */
    public function add_action( $hook, $component, $callback, $priority = 10, $accepted_args = 1 ) {
        $this->actions = $this->add( $this->actions, $hook, $component, $callback, $priority, $accepted_args );
    }

    /**
     * Add a new filter to the collection.
     *
     * @param string $hook          The name of the WordPress filter.
     * @param object $component     A reference to the instance of the object.
     * @param string $callback      The name of the function.
     * @param int    $priority      Optional. The priority. Default is 10.
     * @param int    $accepted_args Optional. The number of arguments. Default is 1.
     */
    public function add_filter( $hook, $component, $callback, $priority = 10, $accepted_args = 1 ) {
        $this->filters = $this->add( $this->filters, $hook, $component, $callback, $priority, $accepted_args );
    }

    /**
     * A utility function that is used to register the actions and hooks into a single collection.
     *
     * @param array  $hooks         The collection of hooks.
     * @param string $hook          The name of the WordPress hook.
     * @param object $component     A reference to the instance of the object.
     * @param string $callback      The name of the function.
     * @param int    $priority      The priority.
     * @param int    $accepted_args The number of arguments.
     *
     * @return array The collection of actions and filters registered with WordPress.
     */
    private function add( $hooks, $hook, $component, $callback, $priority, $accepted_args ) {
        $hooks[] = array(
            'hook'          => $hook,
            'component'     => $component,
            'callback'      => $callback,
            'priority'      => $priority,
            'accepted_args' => $accepted_args,
        );

        return $hooks;
    }

    /**
     * Register the filters and actions with WordPress.
     */
    public function run() {
        foreach ( $this->filters as $hook ) {
            add_filter(
                $hook['hook'],
                array( $hook['component'], $hook['callback'] ),
                $hook['priority'],
                $hook['accepted_args']
            );
        }

        foreach ( $this->actions as $hook ) {
            add_action(
                $hook['hook'],
                array( $hook['component'], $hook['callback'] ),
                $hook['priority'],
                $hook['accepted_args']
            );
        }
    }
}
