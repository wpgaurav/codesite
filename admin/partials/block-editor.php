<?php
/**
 * Block editor page.
 */

// Security check.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$block_id = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0;
$block    = $block_id ? CodeSite_Blocks::get( $block_id ) : null;

$name      = $block ? $block->name : '';
$slug      = $block ? $block->slug : '';
$html      = $block ? $block->html : '';
$css       = $block ? $block->css : '';
$js        = $block ? $block->js : '';
$category  = $block ? $block->category : 'general';
$css_scope = $block ? $block->css_scope : 'scoped';
$status    = $block ? $block->status : 'active';

$categories = CodeSite_Blocks::get_categories();
if ( ! in_array( 'general', $categories, true ) ) {
    array_unshift( $categories, 'general' );
}
?>

<div class="wrap codesite-wrap codesite-editor-wrap">
    <div class="codesite-editor-header">
        <div class="codesite-editor-title">
            <a href="<?php echo esc_url( admin_url( 'admin.php?page=codesite-blocks' ) ); ?>" class="codesite-back">
                &larr; <?php esc_html_e( 'Blocks', 'codesite' ); ?>
            </a>
            <input type="text" id="codesite-block-name" placeholder="<?php esc_attr_e( 'Block Name', 'codesite' ); ?>" value="<?php echo esc_attr( $name ); ?>" class="codesite-title-input">
        </div>
        <div class="codesite-editor-actions">
            <select id="codesite-block-status">
                <option value="active" <?php selected( $status, 'active' ); ?>><?php esc_html_e( 'Active', 'codesite' ); ?></option>
                <option value="draft" <?php selected( $status, 'draft' ); ?>><?php esc_html_e( 'Draft', 'codesite' ); ?></option>
            </select>
            <button type="button" id="codesite-save" class="button button-primary" data-type="block" data-id="<?php echo esc_attr( $block_id ); ?>">
                <?php esc_html_e( 'Save', 'codesite' ); ?>
            </button>
        </div>
    </div>

    <div class="codesite-editor-body">
        <div class="codesite-editor-main">
            <!-- Preview Area -->
            <div class="codesite-preview-area">
                <div class="codesite-pane-header">
                    <span class="pane-title"><?php esc_html_e( 'Preview', 'codesite' ); ?></span>
                    <div class="codesite-preview-controls">
                        <button type="button" class="codesite-preview-size active" data-width="100%">
                            <span class="dashicons dashicons-desktop"></span>
                        </button>
                        <button type="button" class="codesite-preview-size" data-width="768px">
                            <span class="dashicons dashicons-tablet"></span>
                        </button>
                        <button type="button" class="codesite-preview-size" data-width="375px">
                            <span class="dashicons dashicons-smartphone"></span>
                        </button>
                    </div>
                </div>
                <div class="codesite-preview-content">
                    <iframe id="codesite-preview-frame" src="about:blank"></iframe>
                </div>
            </div>

            <!-- Code Editors -->
            <div class="codesite-code-editors">
                <div class="codesite-pane" data-pane="html">
                    <div class="codesite-pane-header">
                        <span class="pane-title">HTML</span>
                        <div class="codesite-pane-tools">
                            <button type="button" class="codesite-pane-tool codesite-format-html" title="<?php esc_attr_e( 'Format', 'codesite' ); ?>">
                                <span class="dashicons dashicons-editor-alignleft"></span>
                            </button>
                            <button type="button" class="codesite-pane-tool codesite-fullscreen-toggle" title="<?php esc_attr_e( 'Fullscreen', 'codesite' ); ?>">
                                <span class="dashicons dashicons-editor-expand"></span>
                            </button>
                        </div>
                        <button type="button" class="codesite-pane-toggle" title="<?php esc_attr_e( 'Toggle pane', 'codesite' ); ?>">−</button>
                    </div>
                    <div class="codesite-pane-content">
                        <textarea id="codesite-html"><?php echo esc_textarea( $html ); ?></textarea>
                    </div>
                </div>

                <div class="codesite-pane" data-pane="css">
                    <div class="codesite-pane-header">
                        <span class="pane-title">CSS</span>
                        <div class="codesite-css-tools">
                            <?php include CODESITE_PATH . 'admin/partials/snippets-dropdown.php'; ?>
                            <select id="codesite-html-classes" class="codesite-class-select" title="<?php esc_attr_e( 'Classes from HTML', 'codesite' ); ?>">
                                <option value=""><?php esc_html_e( 'Classes...', 'codesite' ); ?></option>
                            </select>
                        </div>
                        <div class="codesite-pane-tools">
                            <button type="button" class="codesite-pane-tool codesite-format-css" title="<?php esc_attr_e( 'Format', 'codesite' ); ?>">
                                <span class="dashicons dashicons-editor-alignleft"></span>
                            </button>
                            <button type="button" class="codesite-pane-tool codesite-fullscreen-toggle" title="<?php esc_attr_e( 'Fullscreen', 'codesite' ); ?>">
                                <span class="dashicons dashicons-editor-expand"></span>
                            </button>
                        </div>
                        <button type="button" class="codesite-pane-toggle" title="<?php esc_attr_e( 'Toggle pane', 'codesite' ); ?>">−</button>
                    </div>
                    <div class="codesite-pane-content">
                        <textarea id="codesite-css"><?php echo esc_textarea( $css ); ?></textarea>
                    </div>
                </div>

                <div class="codesite-pane" data-pane="js">
                    <div class="codesite-pane-header">
                        <span class="pane-title">JS</span>
                        <div class="codesite-pane-tools">
                            <button type="button" class="codesite-pane-tool codesite-format-js" title="<?php esc_attr_e( 'Format', 'codesite' ); ?>">
                                <span class="dashicons dashicons-editor-alignleft"></span>
                            </button>
                            <button type="button" class="codesite-pane-tool codesite-fullscreen-toggle" title="<?php esc_attr_e( 'Fullscreen', 'codesite' ); ?>">
                                <span class="dashicons dashicons-editor-expand"></span>
                            </button>
                        </div>
                        <button type="button" class="codesite-pane-toggle" title="<?php esc_attr_e( 'Toggle pane', 'codesite' ); ?>">−</button>
                    </div>
                    <div class="codesite-pane-content">
                        <textarea id="codesite-js"><?php echo esc_textarea( $js ); ?></textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="codesite-editor-sidebar">
            <button type="button" class="codesite-sidebar-toggle" title="<?php esc_attr_e( 'Toggle Sidebar', 'codesite' ); ?>">
                <span class="dashicons dashicons-arrow-right-alt2"></span>
            </button>
            <div class="codesite-sidebar-section">
                <h3><?php esc_html_e( 'Settings', 'codesite' ); ?></h3>

                <p>
                    <label for="codesite-block-slug"><?php esc_html_e( 'Slug', 'codesite' ); ?></label>
                    <input type="text" id="codesite-block-slug" value="<?php echo esc_attr( $slug ); ?>" class="widefat">
                </p>

                <p>
                    <label for="codesite-block-category"><?php esc_html_e( 'Category', 'codesite' ); ?></label>
                    <input type="text" id="codesite-block-category" value="<?php echo esc_attr( $category ); ?>" list="codesite-categories" class="widefat">
                    <datalist id="codesite-categories">
                        <?php foreach ( $categories as $cat ) : ?>
                            <option value="<?php echo esc_attr( $cat ); ?>">
                        <?php endforeach; ?>
                    </datalist>
                </p>

                <p>
                    <label for="codesite-block-css-scope"><?php esc_html_e( 'CSS Scope', 'codesite' ); ?></label>
                    <select id="codesite-block-css-scope" class="widefat">
                        <option value="scoped" <?php selected( $css_scope, 'scoped' ); ?>><?php esc_html_e( 'Scoped (prefixed)', 'codesite' ); ?></option>
                        <option value="global" <?php selected( $css_scope, 'global' ); ?>><?php esc_html_e( 'Global', 'codesite' ); ?></option>
                    </select>
                </p>
            </div>

            <div class="codesite-sidebar-section">
                <h3><?php esc_html_e( 'Dynamic Fields', 'codesite' ); ?></h3>
                <div class="codesite-field-picker">
                    <select id="codesite-dynamic-field" class="widefat">
                        <option value=""><?php esc_html_e( 'Select a field...', 'codesite' ); ?></option>
                        <optgroup label="<?php esc_attr_e( 'Site', 'codesite' ); ?>">
                            <option value="{{site_name}}">site_name</option>
                            <option value="{{site_description}}">site_description</option>
                            <option value="{{site_url}}">site_url</option>
                            <option value="{{site_logo}}">site_logo</option>
                        </optgroup>
                        <optgroup label="<?php esc_attr_e( 'Post', 'codesite' ); ?>">
                            <option value="{{post_title}}">post_title</option>
                            <option value="{{post_content}}">post_content</option>
                            <option value="{{post_excerpt}}">post_excerpt</option>
                            <option value="{{post_date}}">post_date</option>
                            <option value="{{post_author}}">post_author</option>
                            <option value="{{post_thumbnail}}">post_thumbnail</option>
                            <option value="{{post_url}}">post_url</option>
                        </optgroup>
                        <optgroup label="<?php esc_attr_e( 'Navigation', 'codesite' ); ?>">
                            <option value="{{menu:primary}}">menu:primary</option>
                        </optgroup>
                        <optgroup label="<?php esc_attr_e( 'Other', 'codesite' ); ?>">
                            <option value="{{current_year}}">current_year</option>
                        </optgroup>
                    </select>
                    <button type="button" id="codesite-insert-field" class="button"><?php esc_html_e( 'Insert', 'codesite' ); ?></button>
                </div>
            </div>

            <div class="codesite-sidebar-section">
                <h3><?php esc_html_e( 'Help', 'codesite' ); ?></h3>
                <p class="description">
                    <?php esc_html_e( 'Use {{field_name}} syntax for dynamic content.', 'codesite' ); ?>
                </p>
                <p class="description">
                    <?php esc_html_e( 'Wrap shortcodes with <SCD>[shortcode]</SCD> tags.', 'codesite' ); ?>
                </p>
            </div>
        </div>
    </div>
</div>
