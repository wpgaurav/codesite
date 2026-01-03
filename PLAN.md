# CodeSite: WordPress Code-First Site Builder

## Project Overview

**Plugin Name:** CodeSite (working title)
**Tagline:** Build WordPress sites with pure HTML, CSS, and JS. No page builder bloat.

### The Problem

Page builders like Elementor and Divi are great for non-coders. But they produce bloated markup, add 500KB+ of CSS/JS, and lock you into their ecosystem. Developers who know HTML/CSS/JS don't need drag-and-drop widgets. They need a clean canvas and real-time preview.

The WordPress block editor is improving, but it's still fighting against its own abstraction layer. Sometimes you just want to write `<div class="hero">` and see it render.

### The Solution

CodeSite gives developers a CodePen-like interface inside WordPress. Write HTML, CSS, and JS in split panes. See live preview. Build reusable layouts. Create templates for different content types. Output clean, minimal code.

Think of it as: WordPress as a headless CMS, but with a visual builder for the frontend. You control every line of code. It has support for custom CSS frameworks, custom JS libraries. It also has a theme bypass option. Also, the code editing enables autocompletion for html and CSS classes and live preview. 

---

## Core Concepts

### 1. Blocks (Atomic Units)

A **Block** is the smallest unit. It contains:
- HTML (with dynamic placeholders)
- CSS (scoped or global)
- JavaScript (optional)
- Metadata (name, description, category)

Blocks are reusable across layouts and templates.

### 2. Layouts (Structural Components)

A **Layout** is a collection of Blocks arranged in order. Layouts serve specific purposes:
- `header` - Site header, loads on every page
- `footer` - Site footer, loads on every page
- `section` - Reusable section (testimonials, CTAs, features)

Layouts can be assigned globally or per-template.

### 3. Templates (Page Types)

A **Template** defines how a specific content type renders:
- `front-page` - Homepage
- `single-post` - Individual blog posts
- `single-{cpt}` - Custom post type singles
- `archive` - Blog archive
- `archive-{cpt}` - CPT archives
- `page` - Default page template
- `404` - Not found page
- `search` - Search results

Templates combine:
- A header layout (or custom HTML)
- Main content area (blocks + dynamic content)
- A footer layout (or custom HTML)

### 4. Overrides (Per-Post Customization)

Any post, page, or CPT can override its template. This allows:
- Custom header/footer for specific pages
- Completely custom HTML for landing pages
- Selective block replacement
- CSS framework support with autocompletion.

---

## Technical Architecture

### Database Schema

```sql
-- Blocks table
CREATE TABLE {prefix}_codesite_blocks (
    id BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    html LONGTEXT,
    css LONGTEXT,
    js LONGTEXT,
    category VARCHAR(100) DEFAULT 'general',
    css_scope ENUM('global', 'scoped') DEFAULT 'scoped',
    status ENUM('active', 'draft', 'trash') DEFAULT 'active',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_slug (slug),
    INDEX idx_category (category),
    INDEX idx_status (status)
);

-- Layouts table
CREATE TABLE {prefix}_codesite_layouts (
    id BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    type ENUM('header', 'footer', 'section') NOT NULL,
    block_order LONGTEXT, -- JSON array of block IDs
    custom_html LONGTEXT, -- Alternative to blocks
    custom_css LONGTEXT,
    custom_js LONGTEXT,
    use_blocks TINYINT(1) DEFAULT 1, -- 1 = use blocks, 0 = use custom HTML
    status ENUM('active', 'draft', 'trash') DEFAULT 'active',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_slug (slug),
    INDEX idx_type (type)
);

-- Templates table
CREATE TABLE {prefix}_codesite_templates (
    id BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    template_type VARCHAR(100) NOT NULL, -- front-page, single-post, archive, etc.
    header_layout_id BIGINT(20) UNSIGNED NULL,
    footer_layout_id BIGINT(20) UNSIGNED NULL,
    content_blocks LONGTEXT, -- JSON array of block IDs for main content
    custom_html LONGTEXT,
    custom_css LONGTEXT,
    custom_js LONGTEXT,
    conditions LONGTEXT, -- JSON for conditional display rules
    priority INT DEFAULT 10,
    status ENUM('active', 'draft', 'trash') DEFAULT 'active',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (header_layout_id) REFERENCES {prefix}_codesite_layouts(id) ON DELETE SET NULL,
    FOREIGN KEY (footer_layout_id) REFERENCES {prefix}_codesite_layouts(id) ON DELETE SET NULL,
    INDEX idx_template_type (template_type),
    INDEX idx_priority (priority)
);

-- Post overrides table (meta-like but structured)
CREATE TABLE {prefix}_codesite_overrides (
    id BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    post_id BIGINT(20) UNSIGNED NOT NULL,
    override_type ENUM('full', 'header', 'footer', 'content') DEFAULT 'full',
    header_layout_id BIGINT(20) UNSIGNED NULL,
    footer_layout_id BIGINT(20) UNSIGNED NULL,
    content_blocks LONGTEXT,
    custom_html LONGTEXT,
    custom_css LONGTEXT,
    custom_js LONGTEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_post (post_id),
    FOREIGN KEY (post_id) REFERENCES {prefix}_posts(ID) ON DELETE CASCADE
);

-- Global settings table
CREATE TABLE {prefix}_codesite_settings (
    id BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(255) NOT NULL UNIQUE,
    setting_value LONGTEXT,
    autoload TINYINT(1) DEFAULT 1,
    INDEX idx_key (setting_key)
);
```

### File Structure

```
codesite/
├── codesite.php                    # Main plugin file
├── readme.txt
├── assets/
│   ├── css/
│   │   ├── admin.css              # Admin UI styles
│   │   ├── editor.css             # Code editor styles
│   │   └── frontend.css           # Minimal frontend reset (optional)
│   ├── js/
│   │   ├── admin.js               # Admin functionality
│   │   ├── editor.js              # CodeMirror/Monaco setup
│   │   ├── preview.js             # Live preview handling
│   │   ├── layers-panel.js        # Drag-drop layers
│   │   └── dynamic-fields.js      # Dynamic content parser
│   └── vendor/
│       ├── codemirror/            # Or Monaco Editor
│       └── sortablejs/            # For drag-drop
├── includes/
│   ├── class-codesite-loader.php
│   ├── class-codesite-activator.php
│   ├── class-codesite-deactivator.php
│   ├── class-codesite-database.php
│   ├── class-codesite-blocks.php
│   ├── class-codesite-layouts.php
│   ├── class-codesite-templates.php
│   ├── class-codesite-overrides.php
│   ├── class-codesite-renderer.php
│   ├── class-codesite-dynamic-content.php
│   ├── class-codesite-shortcode-parser.php
│   ├── class-codesite-tangible-integration.php
│   ├── class-codesite-theme-override.php
│   └── class-codesite-rest-api.php
├── admin/
│   ├── class-codesite-admin.php
│   ├── partials/
│   │   ├── dashboard.php
│   │   ├── block-editor.php
│   │   ├── layout-editor.php
│   │   ├── template-editor.php
│   │   ├── settings.php
│   │   └── layers-panel.php
│   └── js/
│       └── admin-app.js           # React/Vue app (optional)
├── public/
│   ├── class-codesite-public.php
│   └── partials/
│       └── template-wrapper.php
├── templates/
│   └── blank.php                  # Blank canvas template
└── languages/
    └── codesite.pot
```

---

## Feature Specifications

### Feature 1: Code Editor Interface

**Requirements:**
- Three-pane editor: HTML, CSS, JS (CodePen style)
- Resizable panes (drag dividers)
- Collapsible panes (click to hide/show)
- Syntax highlighting with CodeMirror 6 or Monaco
- Emmet support for HTML
- CSS autocomplete
- JS linting (optional, toggleable)
- Dark/light theme toggle
- Vim/Emacs keybindings (optional)

**Editor Settings (per user):**
```json
{
  "theme": "dark",
  "fontSize": 14,
  "tabSize": 2,
  "wordWrap": true,
  "emmet": true,
  "linting": false,
  "keymap": "default",
  "paneLayout": "horizontal", // or "vertical"
  "visiblePanes": ["html", "css", "js"]
}
```

**UI Layout:**
```
┌─────────────────────────────────────────────────────────────┐
│ [Block Name] [Save] [Preview] [Settings]        [Dark/Light]│
├─────────────────────────────────────────────────────────────┤
│ ┌─────────────┬─────────────┬─────────────┬────────────────┐│
│ │   HTML      │    CSS      │     JS      │    Preview     ││
│ │             │             │             │                ││
│ │  <div>      │  .hero {    │  document.  │   ┌──────────┐ ││
│ │    ...      │    ...      │    query... │   │  Live    │ ││
│ │  </div>     │  }          │             │   │  Render  │ ││
│ │             │             │             │   └──────────┘ ││
│ └─────────────┴─────────────┴─────────────┴────────────────┘│
├─────────────────────────────────────────────────────────────┤
│ [Layers Panel]  [Dynamic Fields]  [Shortcodes]  [Help]      │
└─────────────────────────────────────────────────────────────┘
```

### Feature 2: Live Preview

**How it works:**
1. User types in any pane
2. Debounced update (300ms after keystroke stops)
3. Preview iframe receives updated content via postMessage
4. No page reload, instant visual feedback

**Preview modes:**
- Desktop (100% width)
- Tablet (768px)
- Mobile (375px)
- Custom width input

**Preview data:**
- For blocks: Renders block in isolation
- For layouts: Renders all blocks in order
- For templates: Renders full page with sample post data

**Sample data for preview:**
```php
$preview_data = [
    'post' => [
        'title' => 'Sample Post Title',
        'content' => 'Lorem ipsum dolor sit amet...',
        'excerpt' => 'This is a sample excerpt.',
        'date' => '2025-01-03',
        'author' => 'John Doe',
        'featured_image' => '/path/to/placeholder.jpg',
        'categories' => ['Technology', 'WordPress'],
        'tags' => ['plugin', 'development']
    ],
    'site' => [
        'name' => get_bloginfo('name'),
        'description' => get_bloginfo('description'),
        'url' => home_url(),
        'logo' => '/path/to/logo.png' // or actual logo
    ]
];
```

### Feature 3: Theme Override System

**The problem:** Active theme loads its own CSS/JS which conflicts with custom code.

**Solution:** Complete theme bypass option. And allow loading a custom CSS framework.

**Implementation:**
```php
class CodeSite_Theme_Override {
    
    private $settings;
    
    public function __construct() {
        $this->settings = get_option('codesite_settings', []);
        
        if ($this->is_theme_override_enabled()) {
            // Remove theme template hierarchy
            add_filter('template_include', [$this, 'use_blank_template'], 999);
            
            // Remove theme styles
            add_action('wp_enqueue_scripts', [$this, 'dequeue_theme_assets'], 999);
            
            // Remove theme supports that add markup
            add_action('after_setup_theme', [$this, 'remove_theme_supports'], 999);
        }
    }
    
    public function is_theme_override_enabled() {
        return isset($this->settings['theme_override']) 
            && $this->settings['theme_override'] === true;
    }
    
    public function use_blank_template($template) {
        // Check if CodeSite should handle this request
        if ($this->should_codesite_render()) {
            return CODESITE_PATH . 'templates/blank.php';
        }
        return $template;
    }
    
    public function dequeue_theme_assets() {
        global $wp_styles, $wp_scripts;
        
        $theme_slug = get_stylesheet();
        $parent_theme_slug = get_template();
        
        // Dequeue theme styles
        foreach ($wp_styles->registered as $handle => $style) {
            if ($this->is_theme_asset($style->src, $theme_slug, $parent_theme_slug)) {
                wp_dequeue_style($handle);
            }
        }
        
        // Dequeue theme scripts
        foreach ($wp_scripts->registered as $handle => $script) {
            if ($this->is_theme_asset($script->src, $theme_slug, $parent_theme_slug)) {
                wp_dequeue_script($handle);
            }
        }
    }
    
    private function is_theme_asset($src, $theme_slug, $parent_theme_slug) {
        return strpos($src, "/themes/{$theme_slug}/") !== false
            || strpos($src, "/themes/{$parent_theme_slug}/") !== false;
    }
}
```

**Settings UI:**
```
Theme Override Settings
─────────────────────────────────────────
☑ Disable active theme completely
  └─ Removes all theme CSS, JS, and templates
  
☐ Keep theme header/footer
  └─ Only override main content area
  
☑ Load WordPress core styles
  └─ wp-block-library, etc.
  
☐ Load plugin styles (specify)
  └─ [WooCommerce] [Gravity Forms] [Custom...]
```

### Feature 4: Layouts System

**Layout Types:**

| Type      | Description      | Load Location                      |
| --------- | ---------------- | ---------------------------------- |
| `header`  | Site header      | Before main content, every page    |
| `footer`  | Site footer      | After main content, every page     |
| `section` | Reusable section | Inserted via template or shortcode |

**Layout Editor UI:**
```
┌─────────────────────────────────────────────────────────────┐
│ Layout: Main Header                    Type: [Header ▼]     │
├─────────────────────────────────────────────────────────────┤
│ ┌─────────────────────────────────┬────────────────────────┐│
│ │  Blocks in Layout               │  Available Blocks      ││
│ │                                 │                        ││
│ │  ┌─────────────────────────┐   │  ○ Logo Block          ││
│ │  │ 1. Logo Block       [↕] │   │  ○ Navigation Block    ││
│ │  └─────────────────────────┘   │  ○ Search Block        ││
│ │  ┌─────────────────────────┐   │  ○ CTA Button          ││
│ │  │ 2. Navigation Block [↕] │   │  ○ Social Icons        ││
│ │  └─────────────────────────┘   │                        ││
│ │  ┌─────────────────────────┐   │  [+ Create New Block]  ││
│ │  │ 3. CTA Button       [↕] │   │                        ││
│ │  └─────────────────────────┘   │                        ││
│ │                                 │                        ││
│ │  [+ Add Block]                  │                        ││
│ └─────────────────────────────────┴────────────────────────┘│
├─────────────────────────────────────────────────────────────┤
│ OR use custom HTML:  ○ Use Blocks  ● Use Custom HTML       │
├─────────────────────────────────────────────────────────────┤
│ [HTML Editor]  [CSS Editor]  [JS Editor]  [Preview]         │
└─────────────────────────────────────────────────────────────┘
```

**Layout Data Structure:**
```json
{
  "id": 1,
  "name": "Main Header",
  "slug": "main-header",
  "type": "header",
  "use_blocks": true,
  "block_order": [3, 7, 12],
  "custom_html": null,
  "custom_css": null,
  "custom_js": null,
  "status": "active"
}
```

### Feature 5: Templates System

**Template Hierarchy:**

CodeSite follows WordPress template hierarchy but with its own templates:

```
Request Type        → CodeSite Template Slug
────────────────────────────────────────────
Front Page          → front-page
Blog Index          → home (or archive)
Single Post         → single-post
Single Page         → page
Single CPT          → single-{cpt_slug}
Category Archive    → archive-category
Tag Archive         → archive-tag
CPT Archive         → archive-{cpt_slug}
Author Archive      → archive-author
Date Archive        → archive-date
Search Results      → search
404 Page            → 404
```

**Template Editor UI:**
```
┌─────────────────────────────────────────────────────────────┐
│ Template: Single Post                                        │
├─────────────────────────────────────────────────────────────┤
│ Template Type: [Single Post ▼]    Priority: [10]            │
├─────────────────────────────────────────────────────────────┤
│ CONDITIONS                                                   │
│ ─────────────────────────────────────────────────────────── │
│ Apply to:  ○ All posts  ● Specific conditions               │
│                                                              │
│ ┌─────────────────────────────────────────────────────────┐ │
│ │ Category [is] [Technology ▼]           [×]              │ │
│ │ AND                                                      │ │
│ │ Post Format [is] [Standard ▼]          [×]              │ │
│ │ [+ Add Condition]                                        │ │
│ └─────────────────────────────────────────────────────────┘ │
├─────────────────────────────────────────────────────────────┤
│ STRUCTURE                                                    │
│ ─────────────────────────────────────────────────────────── │
│ Header Layout: [Main Header ▼]  ☐ None  ☐ Custom            │
│ Footer Layout: [Main Footer ▼]  ☐ None  ☐ Custom            │
├─────────────────────────────────────────────────────────────┤
│ MAIN CONTENT                                                 │
│ ─────────────────────────────────────────────────────────── │
│ ┌─────────────────────────────────────────────────────────┐ │
│ │  1. Breadcrumb Block                               [↕]  │ │
│ │  2. Post Header Block                              [↕]  │ │
│ │  3. {{the_content}}                                [↕]  │ │
│ │  4. Author Bio Block                               [↕]  │ │
│ │  5. Related Posts Block                            [↕]  │ │
│ │  [+ Add Block] [+ Add Dynamic Content]                  │ │
│ └─────────────────────────────────────────────────────────┘ │
├─────────────────────────────────────────────────────────────┤
│ [HTML] [CSS] [JS] [Preview with Sample Post]                │
└─────────────────────────────────────────────────────────────┘
```

**Template Conditions:**

Conditions allow multiple templates for the same type:

```php
$conditions = [
    'match' => 'all', // 'all' or 'any'
    'rules' => [
        [
            'field' => 'category',
            'operator' => 'is',
            'value' => 'technology'
        ],
        [
            'field' => 'post_format',
            'operator' => 'is',
            'value' => 'standard'
        ]
    ]
];
```

**Available Condition Fields:**
- `category` - Post category
- `tag` - Post tag
- `post_format` - Post format
- `author` - Post author
- `post_type` - Post type (for archives)
- `taxonomy` - Any taxonomy
- `meta` - Post meta field
- `user_role` - Current user role
- `logged_in` - User logged in status

### Feature 6: Post/Page Overrides

**Override Panel in Post Editor:**

Appears as a metabox or sidebar panel in the classic/block editor.

```
┌─────────────────────────────────────────┐
│ CodeSite Override                       │
├─────────────────────────────────────────┤
│ ☑ Enable custom template for this post  │
│                                         │
│ Override Type:                          │
│ ○ Full page (header + content + footer) │
│ ● Content only (keep header/footer)     │
│ ○ Header only                           │
│ ○ Footer only                           │
│                                         │
│ [Open CodeSite Editor]                  │
│                                         │
│ Current: Using "Single Post" template   │
└─────────────────────────────────────────┘
```

**Override Storage:**
```php
// Stored in codesite_overrides table
$override = [
    'post_id' => 123,
    'override_type' => 'content', // full, header, footer, content
    'header_layout_id' => null,   // null = use template default
    'footer_layout_id' => null,
    'content_blocks' => [5, 8, 12],
    'custom_html' => '<article>...</article>',
    'custom_css' => '.custom { ... }',
    'custom_js' => 'console.log("custom");'
];
```

### Feature 7: Dynamic Content System

**Syntax:** `{{field_name}}` or `{{field_name|filter}}`

**Available Fields:**

| Field                         | Description             | Example Output                 |
| ----------------------------- | ----------------------- | ------------------------------ |
| `{{site_name}}`               | Site title              | "My WordPress Site"            |
| `{{site_description}}`        | Site tagline            | "Just another..."              |
| `{{site_url}}`                | Home URL                | "https://example.com"          |
| `{{site_logo}}`               | Logo URL                | "/wp-content/uploads/logo.png" |
| `{{site_logo_img}}`           | Full `<img>` tag        | `<img src="..." alt="...">`    |
| `{{current_year}}`            | Current year            | "2025"                         |
| `{{post_title}}`              | Post title              | "Hello World"                  |
| `{{post_content}}`            | Post content (filtered) | Full HTML content              |
| `{{post_excerpt}}`            | Post excerpt            | "This is the excerpt..."       |
| `{{post_date}}`               | Publish date            | "January 3, 2025"              |
| `{{post_date\|format:Y-m-d}}` | Formatted date          | "2025-01-03"                   |
| `{{post_author}}`             | Author name             | "John Doe"                     |
| `{{post_author_avatar}}`      | Author avatar URL       | "/path/to/avatar.jpg"          |
| `{{post_thumbnail}}`          | Featured image URL      | "/path/to/image.jpg"           |
| `{{post_thumbnail_img}}`      | Full `<img>` tag        | `<img src="..." alt="...">`    |
| `{{post_categories}}`         | Category list           | `<a>Tech</a>, <a>News</a>`     |
| `{{post_tags}}`               | Tag list                | `<a>plugin</a>, <a>dev</a>`    |
| `{{post_id}}`                 | Post ID                 | "123"                          |
| `{{post_url}}`                | Permalink               | "https://example.com/post/"    |
| `{{post_meta:key}}`           | Custom field            | Value of meta key              |
| `{{acf:field_name}}`          | ACF field               | ACF field value                |
| `{{menu:location}}`           | Nav menu                | Full menu HTML                 |
| `{{widget:sidebar_id}}`       | Widget area             | Widget area HTML               |
| `{{user_name}}`               | Current user name       | "Jane" (or empty)              |
| `{{user_avatar}}`             | Current user avatar     | Avatar URL                     |

**Filters (Modifiers):**

| Filter           | Description      | Example                                        |
| ---------------- | ---------------- | ---------------------------------------------- |
| `\|upper`        | Uppercase        | `{{post_title\|upper}}`                        |
| `\|lower`        | Lowercase        | `{{post_title\|lower}}`                        |
| `\|truncate:100` | Limit characters | `{{post_excerpt\|truncate:100}}`               |
| `\|words:20`     | Limit words      | `{{post_excerpt\|words:20}}`                   |
| `\|date:F j, Y`  | Date format      | `{{post_date\|date:F j, Y}}`                   |
| `\|default:text` | Fallback value   | `{{post_thumbnail\|default:/placeholder.jpg}}` |
| `\|escape`       | HTML escape      | `{{user_input\|escape}}`                       |
| `\|raw`          | No escaping      | `{{post_content\|raw}}`                        |
| `\|strip_tags`   | Remove HTML      | `{{post_content\|strip_tags}}`                 |

**Parser Implementation:**
```php
class CodeSite_Dynamic_Content {
    
    private $context;
    
    public function parse($content, $context = []) {
        $this->context = $context;
        
        // Match {{field}} or {{field|filter}} or {{field|filter:arg}}
        $pattern = '/\{\{([a-z_:]+)(\|[a-z_:,]+)*\}\}/i';
        
        return preg_replace_callback($pattern, [$this, 'replace_field'], $content);
    }
    
    private function replace_field($matches) {
        $full_match = $matches[0];
        $field = $matches[1];
        $filters = isset($matches[2]) ? explode('|', trim($matches[2], '|')) : [];
        
        // Get raw value
        $value = $this->get_field_value($field);
        
        // Apply filters
        foreach ($filters as $filter) {
            $value = $this->apply_filter($value, $filter);
        }
        
        return $value;
    }
    
    private function get_field_value($field) {
        // Site fields
        if (strpos($field, 'site_') === 0) {
            return $this->get_site_field($field);
        }
        
        // Post fields
        if (strpos($field, 'post_') === 0) {
            return $this->get_post_field($field);
        }
        
        // ACF fields
        if (strpos($field, 'acf:') === 0) {
            $acf_field = str_replace('acf:', '', $field);
            return function_exists('get_field') ? get_field($acf_field) : '';
        }
        
        // Meta fields
        if (strpos($field, 'post_meta:') === 0) {
            $meta_key = str_replace('post_meta:', '', $field);
            return get_post_meta(get_the_ID(), $meta_key, true);
        }
        
        // Menu
        if (strpos($field, 'menu:') === 0) {
            $location = str_replace('menu:', '', $field);
            return wp_nav_menu(['theme_location' => $location, 'echo' => false]);
        }
        
        return '';
    }
}
```

### Feature 8: Shortcode Rendering

**Syntax:** `<SCD>[shortcode attr="value"]</SCD>`

Why the wrapper? Two reasons:
1. Prevents shortcodes from being parsed prematurely
2. Allows visual identification in the editor

**Implementation:**
```php
class CodeSite_Shortcode_Parser {
    
    public function parse($content) {
        // Match <SCD>...</SCD> tags
        $pattern = '/<SCD>(.*?)<\/SCD>/s';
        
        return preg_replace_callback($pattern, function($matches) {
            $shortcode = $matches[1];
            return do_shortcode($shortcode);
        }, $content);
    }
}
```

**Editor Integration:**

The editor should:
1. Highlight `<SCD>` tags distinctly
2. Show a preview of shortcode output (if possible)
3. Provide a shortcode picker/inserter

```
┌─────────────────────────────────────────┐
│ Insert Shortcode                    [×] │
├─────────────────────────────────────────┤
│ Search: [____________]                  │
│                                         │
│ Available Shortcodes:                   │
│ ○ [contact-form-7] - Contact Form       │
│ ○ [gallery] - WordPress Gallery         │
│ ○ [woocommerce_cart] - WC Cart          │
│ ○ [tangible_loop] - Tangible Loop       │
│                                         │
│ [Insert Selected]                       │
└─────────────────────────────────────────┘
```

### Feature 9: Tangible Loops & Logic Integration

**Why Tangible?**

Tangible Loops & Logic is the best template engine for WordPress that isn't PHP. It provides:
- Loop through any post type
- Conditional logic
- Clean syntax
- No PHP knowledge required

**Integration Points:**

1. **Detection:** Check if Tangible is active
2. **Prompt:** If not active, show install prompt
3. **Syntax Support:** Highlight Tangible tags in editor
4. **Documentation:** Link to Tangible docs

**Detection & Prompt:**
```php
class CodeSite_Tangible_Integration {
    
    public function __construct() {
        add_action('admin_notices', [$this, 'maybe_show_tangible_notice']);
    }
    
    public function is_tangible_active() {
        return defined('JETONLINE_TEMPLATE_SYSTEM_VERSION') 
            || class_exists('Jetonline\\TemplateSystem\\Plugin');
    }
    
    public function maybe_show_tangible_notice() {
        if ($this->is_tangible_active()) return;
        if (!$this->is_codesite_page()) return;
        
        ?>
        <div class="notice notice-info is-dismissible">
            <p>
                <strong>Enhance CodeSite with Tangible Loops & Logic</strong><br>
                For powerful content loops and conditional logic, install the 
                <a href="https://loopsandlogic.com/" target="_blank">Tangible Loops & Logic</a> plugin.
                It's free and works seamlessly with CodeSite.
            </p>
            <p>
                <a href="<?php echo admin_url('plugin-install.php?s=tangible+loops&tab=search'); ?>" 
                   class="button button-primary">Install Now</a>
                <a href="#" class="button codesite-dismiss-tangible">Dismiss</a>
            </p>
        </div>
        <?php
    }
}
```

**Usage Example in CodeSite:**
```html
<div class="recent-posts">
    <Loop type="post" count="3" orderby="date" order="desc">
        <article class="post-card">
            <If field="thumbnail">
                <img src="{Field thumbnail_url size=medium}" alt="{Field title}">
            </If>
            <h3><a href="{Field url}">{Field title}</a></h3>
            <p>{Field excerpt}</p>
        </article>
    </Loop>
</div>
```

### Feature 10: Layers Panel

**Purpose:** Bird's-eye view of page structure with drag-drop reordering.

**UI Design:**
```
┌─────────────────────────────────────────┐
│ Layers                             [−]  │
├─────────────────────────────────────────┤
│ 📁 Header: Main Header                  │
│    ├─ 📦 Logo Block                     │
│    ├─ 📦 Navigation Block               │
│    └─ 📦 CTA Button                     │
│                                         │
│ 📁 Content                              │
│    ├─ 📦 Hero Section                   │
│    ├─ 📦 Features Grid                  │
│    ├─ 📄 {{post_content}}               │
│    └─ 📦 CTA Section                    │
│                                         │
│ 📁 Footer: Main Footer                  │
│    ├─ 📦 Footer Links                   │
│    ├─ 📦 Newsletter Signup              │
│    └─ 📦 Copyright Block                │
└─────────────────────────────────────────┘
```

**Functionality:**
- Drag blocks to reorder within sections
- Drag blocks between sections (where allowed)
- Click to select and scroll to block in editor
- Double-click to open block for editing
- Right-click context menu: Edit, Duplicate, Delete
- Visual indicators for:
  - 📁 Layout/Section
  - 📦 Block
  - 📄 Dynamic content
  - ⚡ Shortcode

**Implementation:**
```javascript
// Using SortableJS for drag-drop
import Sortable from 'sortablejs';

class LayersPanel {
    constructor(container) {
        this.container = container;
        this.init();
    }
    
    init() {
        // Make each section sortable
        const sections = this.container.querySelectorAll('.layer-section');
        
        sections.forEach(section => {
            new Sortable(section, {
                group: 'layers',
                animation: 150,
                handle: '.drag-handle',
                onEnd: (evt) => this.onReorder(evt)
            });
        });
    }
    
    onReorder(evt) {
        const blockId = evt.item.dataset.blockId;
        const newSection = evt.to.dataset.section;
        const newIndex = evt.newIndex;
        
        // Emit event for main editor to handle
        this.emit('reorder', { blockId, newSection, newIndex });
    }
}
```

---

## CSS Output Strategy

### How CSS is Compiled

All CSS from blocks, layouts, and templates is merged and output in `wp_head`.

**Order of CSS output:**
1. CodeSite base reset (optional, toggleable)
2. Global CSS (from settings)
3. Layout CSS (header, then footer)
4. Template CSS
5. Block CSS (in render order)
6. Override CSS (if applicable)

**Scoped vs Global CSS:**

Each block can choose:
- **Global:** CSS applies site-wide
- **Scoped:** CSS is prefixed with unique class

**Scoping Implementation:**
```php
class CodeSite_CSS_Compiler {
    
    public function compile_block_css($block) {
        if ($block->css_scope === 'scoped') {
            return $this->scope_css($block->css, "codesite-block-{$block->id}");
        }
        return $block->css;
    }
    
    private function scope_css($css, $prefix) {
        // Simple scoping: prepend prefix to each selector
        // This is a simplified version - production would use a proper CSS parser
        
        $lines = explode('}', $css);
        $scoped = [];
        
        foreach ($lines as $line) {
            if (empty(trim($line))) continue;
            
            $parts = explode('{', $line, 2);
            if (count($parts) !== 2) continue;
            
            $selectors = $parts[0];
            $rules = $parts[1];
            
            // Handle multiple selectors
            $selector_list = explode(',', $selectors);
            $scoped_selectors = array_map(function($s) use ($prefix) {
                $s = trim($s);
                // Don't scope @rules
                if (strpos($s, '@') === 0) return $s;
                return ".{$prefix} {$s}";
            }, $selector_list);
            
            $scoped[] = implode(', ', $scoped_selectors) . '{' . $rules . '}';
        }
        
        return implode("\n", $scoped);
    }
}
```

**Output in wp_head:**
```php
add_action('wp_head', function() {
    $css = CodeSite_CSS_Compiler::get_page_css();
    
    if (!empty($css)) {
        echo "<style id='codesite-css'>\n";
        echo $css;
        echo "\n</style>";
    }
}, 99);
```

---

## JavaScript Output Strategy

### How JS is Loaded

JavaScript from blocks, layouts, and templates is:
1. Collected during render
2. Wrapped in IIFE for scope isolation
3. Output before `</body>` via `wp_footer`

**Output Structure:**
```html
<script id="codesite-js">
(function() {
    // Global JS from settings
    
    // Layout: Main Header
    (function() {
        // Block: Navigation
        // ...navigation JS...
    })();
    
    // Template JS
    (function() {
        // ...template JS...
    })();
    
    // Block JS (in order)
    (function() {
        // Block: Hero Section
        // ...
    })();
    
})();
</script>
```

**Optional: External File**

For performance, offer option to output JS as external file:
```php
// In settings
☐ Inline JavaScript (default, simpler)
☑ External JavaScript file (better caching)
```

---

## Admin UI Structure

### Menu Structure

```
CodeSite
├── Dashboard
├── Blocks
│   ├── All Blocks
│   └── Add New Block
├── Layouts
│   ├── All Layouts
│   └── Add New Layout
├── Templates
│   ├── All Templates
│   └── Add New Template
├── Global CSS/JS
└── Settings
```

### Dashboard View

```
┌─────────────────────────────────────────────────────────────┐
│ CodeSite Dashboard                                          │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│ ┌──────────────┐ ┌──────────────┐ ┌──────────────┐         │
│ │   12         │ │    4         │ │    3         │         │
│ │   Blocks     │ │   Layouts    │ │   Templates  │         │
│ │   [View All] │ │   [View All] │ │   [View All] │         │
│ └──────────────┘ └──────────────┘ └──────────────┘         │
│                                                             │
│ Quick Actions                                               │
│ ─────────────────────────────────────────────────────────── │
│ [+ New Block] [+ New Layout] [+ New Template]               │
│                                                             │
│ Recent Activity                                             │
│ ─────────────────────────────────────────────────────────── │
│ • "Hero Section" block updated - 2 hours ago                │
│ • "Main Header" layout created - 1 day ago                  │
│ • "Single Post" template modified - 2 days ago              │
│                                                             │
│ System Status                                               │
│ ─────────────────────────────────────────────────────────── │
│ ✓ Theme Override: Active                                    │
│ ✓ Tangible Loops: Installed                                 │
│ ⚠ No 404 template defined                                   │
│                                                             │
└─────────────────────────────────────────────────────────────┘
```

### Settings Page

```
┌─────────────────────────────────────────────────────────────┐
│ CodeSite Settings                                           │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│ General                                                     │
│ ─────────────────────────────────────────────────────────── │
│ ☑ Enable CodeSite frontend rendering                        │
│ ☑ Disable active theme completely                           │
│ ☐ Keep WordPress admin bar on frontend                      │
│                                                             │
│ Performance                                                  │
│ ─────────────────────────────────────────────────────────── │
│ ○ Inline CSS/JS (simpler, no extra requests)                │
│ ● External CSS/JS files (better caching)                    │
│ ☑ Minify output                                             │
│                                                             │
│ Defaults                                                     │
│ ─────────────────────────────────────────────────────────── │
│ Default Header Layout: [Main Header ▼]                      │
│ Default Footer Layout: [Main Footer ▼]                      │
│                                                             │
│ Global Code                                                  │
│ ─────────────────────────────────────────────────────────── │
│ [Global CSS Editor]                                         │
│ [Global JS Editor]                                          │
│                                                             │
│ Integrations                                                 │
│ ─────────────────────────────────────────────────────────── │
│ ☑ Enable Tangible Loops & Logic support                     │
│ ☑ Enable ACF dynamic field support                          │
│ ☐ Enable WooCommerce template overrides                     │
│                                                             │
│ [Save Settings]                                             │
│                                                             │
└─────────────────────────────────────────────────────────────┘
```

---

## REST API Endpoints

All endpoints prefixed with `/wp-json/codesite/v1/`

### Blocks

| Method | Endpoint                 | Description      |
| ------ | ------------------------ | ---------------- |
| GET    | `/blocks`                | List all blocks  |
| GET    | `/blocks/{id}`           | Get single block |
| POST   | `/blocks`                | Create block     |
| PUT    | `/blocks/{id}`           | Update block     |
| DELETE | `/blocks/{id}`           | Delete block     |
| POST   | `/blocks/{id}/duplicate` | Duplicate block  |

### Layouts

| Method | Endpoint               | Description        |
| ------ | ---------------------- | ------------------ |
| GET    | `/layouts`             | List all layouts   |
| GET    | `/layouts/{id}`        | Get single layout  |
| POST   | `/layouts`             | Create layout      |
| PUT    | `/layouts/{id}`        | Update layout      |
| DELETE | `/layouts/{id}`        | Delete layout      |
| PUT    | `/layouts/{id}/blocks` | Update block order |

### Templates

| Method | Endpoint          | Description         |
| ------ | ----------------- | ------------------- |
| GET    | `/templates`      | List all templates  |
| GET    | `/templates/{id}` | Get single template |
| POST   | `/templates`      | Create template     |
| PUT    | `/templates/{id}` | Update template     |
| DELETE | `/templates/{id}` | Delete template     |

### Rendering

| Method | Endpoint           | Description             |
| ------ | ------------------ | ----------------------- |
| POST   | `/render/block`    | Render block preview    |
| POST   | `/render/layout`   | Render layout preview   |
| POST   | `/render/template` | Render template preview |
| POST   | `/render/dynamic`  | Parse dynamic fields    |

### Utilities

| Method | Endpoint          | Description                    |
| ------ | ----------------- | ------------------------------ |
| GET    | `/shortcodes`     | List available shortcodes      |
| GET    | `/dynamic-fields` | List available dynamic fields  |
| GET    | `/post-types`     | List post types for templates  |
| GET    | `/taxonomies`     | List taxonomies for conditions |

---

## Technical Considerations

### Performance

**Admin:**
- Lazy load editors (only load CodeMirror when editor opens)
- Debounce preview updates (300ms)
- Cache compiled templates in transients

**Frontend:**
- Single CSS output (not per-block)
- Optional external file output
- Minification option
- Consider critical CSS extraction (future)

### Security

**Input:**
- Sanitize all HTML with `wp_kses_post()` or custom allowed tags
- Escape output with `esc_html()`, `esc_attr()`, etc.
- Validate nonces on all AJAX/REST requests

**Output:**
- Dynamic fields escaped by default
- `|raw` filter requires explicit opt-in
- User capability checks on all operations

**Capabilities:**
- `manage_codesite` - Full access
- `edit_codesite_blocks` - Create/edit blocks
- `edit_codesite_templates` - Create/edit templates
- Map to existing roles: Administrators get all, Editors get blocks

### Compatibility

**WordPress:**
- Minimum: 6.0
- Tested up to: 6.7

**PHP:**
- Minimum: 7.4
- Recommended: 8.0+

**Browsers:**
- Chrome/Edge (last 2 versions)
- Firefox (last 2 versions)
- Safari (last 2 versions)
- No IE support

### Conflict Prevention

**Potential conflicts:**
- Other page builders (Elementor, Divi)
- Theme builders (Oxygen, Bricks)
- Other template overrides

**Solutions:**
- Check for active builders, warn user
- Don't load on builder pages
- Clear documentation on what to disable

---

## Naming Conventions

### Database

- Tables: `{prefix}_codesite_*`
- Options: `codesite_*`
- Transients: `codesite_cache_*`
- User meta: `codesite_*`

### Code

- Classes: `CodeSite_*` or `CodeSite\Namespace\*`
- Functions: `codesite_*`
- Hooks: `codesite/*` (action/filter)
- REST namespace: `codesite/v1`

### CSS

- Admin: `.codesite-*`
- Frontend: `.codesite-block-*`, `.codesite-layout-*`

### JavaScript

- Global namespace: `window.CodeSite`
- Events: `codesite:*`

---

## Future Enhancements (Post-Launch)

### Version 1.1
- Import/export blocks and templates
- Block categories and search
- Template library (pre-built templates)

### Version 1.2
- Multi-site support
- Role-based editing permissions
- Revision history for blocks

### Version 1.3
- Visual block builder (optional)
- AI-assisted code generation
- Accessibility checker

### Version 1.4
- Headless mode (JSON API output)
- React/Vue component export
- Static site generation

---

## Competitor Analysis

| Feature              | CodeSite  | Oxygen   | Bricks  | Breakdance |
| -------------------- | --------- | -------- | ------- | ---------- |
| Code-first approach  | ✓ Primary | ✓ Option | Partial | No         |
| Clean output         | ✓✓✓       | ✓✓       | ✓✓      | ✓          |
| Live preview         | ✓         | ✓        | ✓       | ✓          |
| Learning curve       | Medium    | High     | Medium  | Low        |
| Price                | Free      | $149     | $79     | $149       |
| Theme override       | ✓         | ✓        | ✓       | ✓          |
| Dynamic content      | ✓         | ✓        | ✓       | ✓          |
| Tangible integration | ✓         | No       | No      | No         |

**Differentiation:**
CodeSite is for developers who think in HTML/CSS/JS and want full control. It's not a visual builder with code output. It's a code editor with visual preview.

---

## Resources

### Libraries to Use
- **CodeMirror 6** - Code editor (MIT license)
- **SortableJS** - Drag and drop (MIT license)
- **PostCSS** - CSS processing (MIT license) - optional

### Documentation to Reference
- [WordPress Plugin Handbook](https://developer.wordpress.org/plugins/)
- [WordPress REST API Handbook](https://developer.wordpress.org/rest-api/)
- [CodeMirror 6 Documentation](https://codemirror.net/6/)
- [Tangible Loops & Logic](https://loopsandlogic.com/docs/)

### Similar Projects to Study
- Oxygen Builder (architecture)
- Bricks Builder (UX patterns)
- CodePen (editor experience)
- Webflow (template system concepts)

---

*Document version: 1.0*
*Last updated: January 2025*
*Author: Planning Document*