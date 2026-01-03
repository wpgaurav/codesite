<?php
/**
 * Plugin Name: CodeSite
 * Plugin URI: https://gauravtiwari.org/wordpress-plugins/codesite/
 * Description: Build WordPress sites with pure HTML, CSS, and JS. No page builder bloat.
 * Version: 1.2.0
 * Author: Gaurav Tiwari
 * Author URI: https://gauravtiwari.org
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: codesite
 * Domain Path: /languages
 * Requires at least: 6.0
 * Requires PHP: 7.4
 */

// Abort if this file is called directly.
if ( ! defined( 'WPINC' ) ) {
    die;
}

// Plugin version.
define( 'CODESITE_VERSION', '1.2.0' );

// Plugin path.
define( 'CODESITE_PATH', plugin_dir_path( __FILE__ ) );

// Plugin URL.
define( 'CODESITE_URL', plugin_dir_url( __FILE__ ) );

// Plugin basename.
define( 'CODESITE_BASENAME', plugin_basename( __FILE__ ) );

/**
 * The code that runs during plugin activation.
 */
function codesite_activate() {
    require_once CODESITE_PATH . 'includes/class-codesite-activator.php';
    CodeSite_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 */
function codesite_deactivate() {
    require_once CODESITE_PATH . 'includes/class-codesite-deactivator.php';
    CodeSite_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'codesite_activate' );
register_deactivation_hook( __FILE__, 'codesite_deactivate' );

/**
 * The core plugin class.
 */
require_once CODESITE_PATH . 'includes/class-codesite-loader.php';

/**
 * Begins execution of the plugin.
 */
function codesite_run() {
    $plugin = new CodeSite_Loader();
    $plugin->run();
}

codesite_run();
