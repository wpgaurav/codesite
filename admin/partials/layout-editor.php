<?php
/**
 * Layout editor page.
 */

// Security check.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$layout_id = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0;
$layout    = $layout_id ? CodeSite_Layouts::get( $layout_id ) : null;

$name        = $layout ? $layout->name : '';
$slug        = $layout ? $layout->slug : '';
$type        = $layout ? $layout->type : 'header';
$block_order = $layout ? json_decode( $layout->block_order, true ) : array();
$custom_html = $layout ? $layout->custom_html : '';
$custom_css  = $layout ? $layout->custom_css : '';
$custom_js   = $layout ? $layout->custom_js : '';
$use_blocks  = $layout ? (bool) $layout->use_blocks : false;
$status      = $layout ? $layout->status : 'active';

$all_blocks = CodeSite_Blocks::get_all();

// Get blocks in order.
$layout_blocks = array();
if ( ! empty( $block_order ) ) {
    foreach ( $block_order as $block_id ) {
        $block = CodeSite_Blocks::get( $block_id );
        if ( $block ) {
            $layout_blocks[] = $block;
        }
    }
}
?>

<div class="wrap codesite-wrap codesite-editor-wrap">
    <div class="codesite-editor-header">
        <div class="codesite-editor-title">
            <a href="<?php echo esc_url( admin_url( 'admin.php?page=codesite-layouts' ) ); ?>" class="codesite-back">
                &larr; <?php esc_html_e( 'Layouts', 'codesite' ); ?>
            </a>
            <input type="text" id="codesite-layout-name" placeholder="<?php esc_attr_e( 'Layout Name', 'codesite' ); ?>" value="<?php echo esc_attr( $name ); ?>" class="codesite-title-input">
        </div>
        <div class="codesite-editor-actions">
            <select id="codesite-layout-status">
                <option value="active" <?php selected( $status, 'active' ); ?>><?php esc_html_e( 'Active', 'codesite' ); ?></option>
                <option value="draft" <?php selected( $status, 'draft' ); ?>><?php esc_html_e( 'Draft', 'codesite' ); ?></option>
            </select>
            <button type="button" id="codesite-save" class="button button-primary" data-type="layout" data-id="<?php echo esc_attr( $layout_id ); ?>">
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
                        <button type="button" class="codesite-pane-toggle" title="<?php esc_attr_e( 'Toggle pane', 'codesite' ); ?>">−</button>
                    </div>
                    <div class="codesite-pane-content">
                        <textarea id="codesite-html"><?php echo esc_textarea( $custom_html ); ?></textarea>
                    </div>
                </div>

                <div class="codesite-pane" data-pane="css">
                    <div class="codesite-pane-header">
                        <span class="pane-title">CSS</span>
                        <div class="codesite-css-tools">
                            <select id="codesite-css-snippets" class="codesite-snippet-select" title="<?php esc_attr_e( 'Insert CSS snippet', 'codesite' ); ?>">
                                <option value=""><?php esc_html_e( 'Snippets...', 'codesite' ); ?></option>
                                <optgroup label="<?php esc_attr_e( 'Flexbox', 'codesite' ); ?>">
                                    <option value="flex-row"><?php esc_html_e( 'Flex Row', 'codesite' ); ?></option>
                                    <option value="flex-col"><?php esc_html_e( 'Flex Column', 'codesite' ); ?></option>
                                    <option value="flex-center"><?php esc_html_e( 'Flex Center', 'codesite' ); ?></option>
                                    <option value="flex-between"><?php esc_html_e( 'Flex Space Between', 'codesite' ); ?></option>
                                    <option value="flex-wrap"><?php esc_html_e( 'Flex Wrap', 'codesite' ); ?></option>
                                </optgroup>
                                <optgroup label="<?php esc_attr_e( 'Grid', 'codesite' ); ?>">
                                    <option value="grid-2col"><?php esc_html_e( 'Grid 2 Columns', 'codesite' ); ?></option>
                                    <option value="grid-3col"><?php esc_html_e( 'Grid 3 Columns', 'codesite' ); ?></option>
                                    <option value="grid-4col"><?php esc_html_e( 'Grid 4 Columns', 'codesite' ); ?></option>
                                    <option value="grid-auto"><?php esc_html_e( 'Grid Auto-fit', 'codesite' ); ?></option>
                                    <option value="grid-12"><?php esc_html_e( 'Grid 12 Column System', 'codesite' ); ?></option>
                                </optgroup>
                                <optgroup label="<?php esc_attr_e( 'Layout', 'codesite' ); ?>">
                                    <option value="container"><?php esc_html_e( 'Container', 'codesite' ); ?></option>
                                    <option value="full-height"><?php esc_html_e( 'Full Height', 'codesite' ); ?></option>
                                    <option value="sticky-header"><?php esc_html_e( 'Sticky Header', 'codesite' ); ?></option>
                                    <option value="sticky-footer"><?php esc_html_e( 'Sticky Footer', 'codesite' ); ?></option>
                                </optgroup>
                                <optgroup label="<?php esc_attr_e( 'Typography', 'codesite' ); ?>">
                                    <option value="text-truncate"><?php esc_html_e( 'Text Truncate', 'codesite' ); ?></option>
                                    <option value="line-clamp"><?php esc_html_e( 'Line Clamp (3 lines)', 'codesite' ); ?></option>
                                    <option value="responsive-text"><?php esc_html_e( 'Responsive Text', 'codesite' ); ?></option>
                                </optgroup>
                                <optgroup label="<?php esc_attr_e( 'Effects', 'codesite' ); ?>">
                                    <option value="shadow"><?php esc_html_e( 'Box Shadow', 'codesite' ); ?></option>
                                    <option value="transition"><?php esc_html_e( 'Transition', 'codesite' ); ?></option>
                                    <option value="hover-scale"><?php esc_html_e( 'Hover Scale', 'codesite' ); ?></option>
                                    <option value="gradient-bg"><?php esc_html_e( 'Gradient Background', 'codesite' ); ?></option>
                                </optgroup>
                                <optgroup label="<?php esc_attr_e( 'Responsive', 'codesite' ); ?>">
                                    <option value="media-tablet"><?php esc_html_e( 'Media Query (Tablet)', 'codesite' ); ?></option>
                                    <option value="media-mobile"><?php esc_html_e( 'Media Query (Mobile)', 'codesite' ); ?></option>
                                    <option value="hide-mobile"><?php esc_html_e( 'Hide on Mobile', 'codesite' ); ?></option>
                                    <option value="show-mobile"><?php esc_html_e( 'Show only on Mobile', 'codesite' ); ?></option>
                                </optgroup>
                            </select>
                            <select id="codesite-html-classes" class="codesite-class-select" title="<?php esc_attr_e( 'Classes from HTML', 'codesite' ); ?>">
                                <option value=""><?php esc_html_e( 'Classes...', 'codesite' ); ?></option>
                            </select>
                        </div>
                        <button type="button" class="codesite-pane-toggle" title="<?php esc_attr_e( 'Toggle pane', 'codesite' ); ?>">−</button>
                    </div>
                    <div class="codesite-pane-content">
                        <textarea id="codesite-css"><?php echo esc_textarea( $custom_css ); ?></textarea>
                    </div>
                </div>

                <div class="codesite-pane" data-pane="js">
                    <div class="codesite-pane-header">
                        <span class="pane-title">JS</span>
                        <button type="button" class="codesite-pane-toggle" title="<?php esc_attr_e( 'Toggle pane', 'codesite' ); ?>">−</button>
                    </div>
                    <div class="codesite-pane-content">
                        <textarea id="codesite-js"><?php echo esc_textarea( $custom_js ); ?></textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="codesite-editor-sidebar">
            <div class="codesite-sidebar-section">
                <h3><?php esc_html_e( 'Settings', 'codesite' ); ?></h3>

                <p>
                    <label for="codesite-layout-slug"><?php esc_html_e( 'Slug', 'codesite' ); ?></label>
                    <input type="text" id="codesite-layout-slug" value="<?php echo esc_attr( $slug ); ?>" class="widefat">
                </p>

                <p>
                    <label for="codesite-layout-type"><?php esc_html_e( 'Type', 'codesite' ); ?></label>
                    <select id="codesite-layout-type" class="widefat">
                        <option value="header" <?php selected( $type, 'header' ); ?>><?php esc_html_e( 'Header', 'codesite' ); ?></option>
                        <option value="footer" <?php selected( $type, 'footer' ); ?>><?php esc_html_e( 'Footer', 'codesite' ); ?></option>
                        <option value="section" <?php selected( $type, 'section' ); ?>><?php esc_html_e( 'Section', 'codesite' ); ?></option>
                    </select>
                </p>
            </div>

            <div class="codesite-sidebar-section">
                <h3><?php esc_html_e( 'Include Blocks', 'codesite' ); ?></h3>
                <p class="description"><?php esc_html_e( 'Optionally include existing blocks in this layout.', 'codesite' ); ?></p>

                <ul id="codesite-layout-blocks" class="codesite-sortable-list">
                    <?php foreach ( $layout_blocks as $block ) : ?>
                        <li data-id="<?php echo esc_attr( $block->id ); ?>">
                            <span class="dashicons dashicons-menu"></span>
                            <?php echo esc_html( $block->name ); ?>
                            <button type="button" class="codesite-remove-block">&times;</button>
                        </li>
                    <?php endforeach; ?>
                </ul>

                <select id="codesite-available-blocks" class="widefat">
                    <option value=""><?php esc_html_e( 'Select a block...', 'codesite' ); ?></option>
                    <?php foreach ( $all_blocks as $block ) : ?>
                        <option value="<?php echo esc_attr( $block->id ); ?>"><?php echo esc_html( $block->name ); ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="button" id="codesite-add-block" class="button" style="margin-top: 5px;">
                    <?php esc_html_e( '+ Add Block', 'codesite' ); ?>
                </button>
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
                        <optgroup label="<?php esc_attr_e( 'Navigation', 'codesite' ); ?>">
                            <option value="{{menu:primary}}">menu:primary</option>
                            <option value="{{menu:footer}}">menu:footer</option>
                        </optgroup>
                        <optgroup label="<?php esc_attr_e( 'Other', 'codesite' ); ?>">
                            <option value="{{current_year}}">current_year</option>
                        </optgroup>
                    </select>
                    <button type="button" id="codesite-insert-field" class="button"><?php esc_html_e( 'Insert', 'codesite' ); ?></button>
                </div>
            </div>
        </div>
    </div>
</div>
