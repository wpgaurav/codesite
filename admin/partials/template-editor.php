<?php
/**
 * Template editor page.
 */

// Security check.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$template_id = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0;
$template    = $template_id ? CodeSite_Templates::get( $template_id ) : null;

$name             = $template ? $template->name : '';
$slug             = $template ? $template->slug : '';
$template_type    = $template ? $template->template_type : 'page';
$header_layout_id = $template ? $template->header_layout_id : null;
$footer_layout_id = $template ? $template->footer_layout_id : null;
$content_blocks   = $template ? json_decode( $template->content_blocks, true ) : array();
$custom_html      = $template ? $template->custom_html : '';
$custom_css       = $template ? $template->custom_css : '';
$custom_js        = $template ? $template->custom_js : '';
$conditions       = $template ? json_decode( $template->conditions, true ) : array();
$priority         = $template ? $template->priority : 10;
$status           = $template ? $template->status : 'active';

$template_types = CodeSite_Templates::get_template_types();
$all_layouts    = CodeSite_Layouts::get_all();
$all_blocks     = CodeSite_Blocks::get_all();

$headers = array_filter( $all_layouts, function( $l ) { return $l->type === 'header'; } );
$footers = array_filter( $all_layouts, function( $l ) { return $l->type === 'footer'; } );
?>

<div class="wrap codesite-wrap codesite-editor-wrap">
    <div class="codesite-editor-header">
        <div class="codesite-editor-title">
            <a href="<?php echo esc_url( admin_url( 'admin.php?page=codesite-templates' ) ); ?>" class="codesite-back">
                &larr; <?php esc_html_e( 'Templates', 'codesite' ); ?>
            </a>
            <input type="text" id="codesite-template-name" placeholder="<?php esc_attr_e( 'Template Name', 'codesite' ); ?>" value="<?php echo esc_attr( $name ); ?>" class="codesite-title-input">
        </div>
        <div class="codesite-editor-actions">
            <select id="codesite-template-status">
                <option value="active" <?php selected( $status, 'active' ); ?>><?php esc_html_e( 'Active', 'codesite' ); ?></option>
                <option value="draft" <?php selected( $status, 'draft' ); ?>><?php esc_html_e( 'Draft', 'codesite' ); ?></option>
            </select>
            <button type="button" id="codesite-save" class="button button-primary" data-type="template" data-id="<?php echo esc_attr( $template_id ); ?>">
                <?php esc_html_e( 'Save', 'codesite' ); ?>
            </button>
        </div>
    </div>

    <div class="codesite-editor-body">
        <div class="codesite-editor-sidebar" style="width: 350px;">
            <div class="codesite-sidebar-section">
                <h3><?php esc_html_e( 'Template Settings', 'codesite' ); ?></h3>

                <p>
                    <label for="codesite-template-slug"><?php esc_html_e( 'Slug', 'codesite' ); ?></label>
                    <input type="text" id="codesite-template-slug" value="<?php echo esc_attr( $slug ); ?>" class="widefat">
                </p>

                <p>
                    <label for="codesite-template-type"><?php esc_html_e( 'Template Type', 'codesite' ); ?></label>
                    <select id="codesite-template-type" class="widefat">
                        <?php foreach ( $template_types as $type_key => $type_label ) : ?>
                            <option value="<?php echo esc_attr( $type_key ); ?>" <?php selected( $template_type, $type_key ); ?>>
                                <?php echo esc_html( $type_label ); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </p>

                <p>
                    <label for="codesite-template-priority"><?php esc_html_e( 'Priority', 'codesite' ); ?></label>
                    <input type="number" id="codesite-template-priority" value="<?php echo esc_attr( $priority ); ?>" class="widefat" min="1">
                    <span class="description"><?php esc_html_e( 'Lower number = higher priority', 'codesite' ); ?></span>
                </p>
            </div>

            <div class="codesite-sidebar-section">
                <h3><?php esc_html_e( 'Structure', 'codesite' ); ?></h3>

                <p>
                    <label for="codesite-template-header"><?php esc_html_e( 'Header Layout', 'codesite' ); ?></label>
                    <select id="codesite-template-header" class="widefat">
                        <option value=""><?php esc_html_e( 'None', 'codesite' ); ?></option>
                        <?php foreach ( $headers as $header ) : ?>
                            <option value="<?php echo esc_attr( $header->id ); ?>" <?php selected( $header_layout_id, $header->id ); ?>>
                                <?php echo esc_html( $header->name ); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </p>

                <p>
                    <label for="codesite-template-footer"><?php esc_html_e( 'Footer Layout', 'codesite' ); ?></label>
                    <select id="codesite-template-footer" class="widefat">
                        <option value=""><?php esc_html_e( 'None', 'codesite' ); ?></option>
                        <?php foreach ( $footers as $footer ) : ?>
                            <option value="<?php echo esc_attr( $footer->id ); ?>" <?php selected( $footer_layout_id, $footer->id ); ?>>
                                <?php echo esc_html( $footer->name ); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </p>
            </div>

            <div class="codesite-sidebar-section">
                <h3><?php esc_html_e( 'Content Blocks', 'codesite' ); ?></h3>
                <ul id="codesite-template-blocks" class="codesite-sortable-list">
                    <?php
                    if ( ! empty( $content_blocks ) ) :
                        foreach ( $content_blocks as $block_id ) :
                            $block = CodeSite_Blocks::get( $block_id );
                            if ( $block ) :
                    ?>
                        <li data-id="<?php echo esc_attr( $block->id ); ?>">
                            <span class="dashicons dashicons-menu"></span>
                            <?php echo esc_html( $block->name ); ?>
                            <button type="button" class="codesite-remove-block">&times;</button>
                        </li>
                    <?php
                            endif;
                        endforeach;
                    endif;
                    ?>
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
        </div>

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
                        <span class="pane-title"><?php esc_html_e( 'Custom HTML', 'codesite' ); ?></span>
                        <button type="button" class="codesite-pane-toggle" title="<?php esc_attr_e( 'Toggle pane', 'codesite' ); ?>">−</button>
                    </div>
                    <div class="codesite-pane-content">
                        <textarea id="codesite-html"><?php echo esc_textarea( $custom_html ); ?></textarea>
                    </div>
                </div>

                <div class="codesite-pane" data-pane="css">
                    <div class="codesite-pane-header">
                        <span class="pane-title">CSS</span>
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
    </div>
</div>
