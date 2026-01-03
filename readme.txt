=== CodeSite ===
Contributors: gauravtiwari
Author: Gaurav Tiwari
Author URI: https://gauravtiwari.org
Plugin URI: https://gauravtiwari.org/wordpress-plugins/codesite/
Tags: page builder, code editor, html, css, javascript, templates
Requires at least: 6.0
Tested up to: 6.7
Requires PHP: 7.4
Stable tag: 1.1.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Build WordPress sites with pure HTML, CSS, and JS. No page builder bloat.

== Description ==

CodeSite gives developers a CodePen-like interface inside WordPress. Write HTML, CSS, and JS in split panes. See live preview. Build reusable layouts. Create templates for different content types. Output clean, minimal code.

**Features:**

* **Code Editor Interface** - Three-pane editor with syntax highlighting (HTML, CSS, JS)
* **Live Preview** - See changes instantly with responsive preview modes
* **Blocks** - Reusable HTML/CSS/JS components
* **Layouts** - Combine blocks into headers, footers, and sections
* **Templates** - Define how different page types render
* **Dynamic Content** - Use {{field}} syntax for post data, site info, and more
* **Theme Override** - Bypass your theme for complete control
* **Tangible Loops Integration** - Optional support for advanced content loops

**For Developers Who:**

* Know HTML, CSS, and JavaScript
* Want clean, minimal output
* Don't need drag-and-drop widgets
* Value performance over convenience

== Installation ==

1. Upload the `codesite` folder to `/wp-content/plugins/`
2. Activate the plugin through the 'Plugins' menu
3. Go to CodeSite > Dashboard to get started
4. Create your first Block, then build Layouts and Templates

The plugin uses WordPress's built-in code editor for syntax highlighting.

== Frequently Asked Questions ==

= Do I need to know how to code? =

Yes. CodeSite is designed for developers who are comfortable writing HTML, CSS, and JavaScript. If you prefer visual drag-and-drop builders, consider Elementor or similar tools.

= Will this conflict with my theme? =

CodeSite includes a "Theme Override" option that completely bypasses your theme. When enabled, only CodeSite templates are used for rendering. You can also keep your theme active and only override specific pages.

= What is Tangible Loops & Logic? =

Tangible Loops & Logic is an optional plugin that adds powerful content loop functionality. CodeSite can render Tangible syntax for advanced querying without writing PHP.

= How do I add dynamic content? =

Use the {{field}} syntax in your HTML. For example:
- {{post_title}} - The post title
- {{post_content}} - The post content
- {{site_name}} - Your site name
- {{menu:primary}} - Your primary navigation menu

== Changelog ==

= 1.1.0 =
* Switched to WordPress built-in code editor
* Complete light theme for admin interface
* Improved Tangible Loops & Logic integration with one-click install
* Fixed editor focus and typing issues
* Simplified release workflow

= 1.0.0 =
* Initial release
* Block editor with live preview
* Layouts system (headers, footers, sections)
* Templates for all WordPress content types
* Dynamic content parsing
* Theme override functionality
* REST API for all operations
* Tangible Loops & Logic integration

== Upgrade Notice ==

= 1.0.0 =
Initial release of CodeSite.
