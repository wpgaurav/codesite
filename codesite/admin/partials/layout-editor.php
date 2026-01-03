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
$type        = $layout ? $layout->type : 'section';
$block_order = $layout ? json_decode( $layout->block_order, true ) : array();
$custom_html = $layout ? $layout->custom_html : '';
$custom_css  = $layout ? $layout->custom_css : '';
$custom_js   = $layout ? $layout->custom_js : '';
$use_blocks  = $layout ? (bool) $layout->use_blocks : true;
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

    <div class="codesite-editor-body codesite-layout-editor-body">
        <div class="codesite-editor-sidebar" style="width: 300px;">
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

                <p>
                    <label><?php esc_html_e( 'Content Mode', 'codesite' ); ?></label>
                    <label class="codesite-radio-label">
                        <input type="radio" name="codesite-layout-mode" value="blocks" <?php checked( $use_blocks ); ?>>
                        <?php esc_html_e( 'Use Blocks', 'codesite' ); ?>
                    </label>
                    <label class="codesite-radio-label">
                        <input type="radio" name="codesite-layout-mode" value="custom" <?php checked( ! $use_blocks ); ?>>
                        <?php esc_html_e( 'Custom HTML', 'codesite' ); ?>
                    </label>
                </p>
            </div>

            <div class="codesite-sidebar-section codesite-blocks-mode" <?php echo ! $use_blocks ? 'style="display:none;"' : ''; ?>>
                <h3><?php esc_html_e( 'Blocks in Layout', 'codesite' ); ?></h3>
                <ul id="codesite-layout-blocks" class="codesite-sortable-list">
                    <?php foreach ( $layout_blocks as $block ) : ?>
                        <li data-id="<?php echo esc_attr( $block->id ); ?>">
                            <span class="dashicons dashicons-menu"></span>
                            <?php echo esc_html( $block->name ); ?>
                            <button type="button" class="codesite-remove-block">&times;</button>
                        </li>
                    <?php endforeach; ?>
                </ul>

                <h4><?php esc_html_e( 'Available Blocks', 'codesite' ); ?></h4>
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

        <div class="codesite-editor-main codesite-custom-mode" <?php echo $use_blocks ? 'style="display:none;"' : ''; ?>>
            <div class="codesite-editor-panes">
                <div class="codesite-pane" data-pane="html">
                    <div class="codesite-pane-header">
                        <span class="pane-title">HTML</span>
                    </div>
                    <div class="codesite-pane-content">
                        <textarea id="codesite-html"><?php echo esc_textarea( $custom_html ); ?></textarea>
                    </div>
                </div>

                <div class="codesite-pane" data-pane="css">
                    <div class="codesite-pane-header">
                        <span class="pane-title">CSS</span>
                    </div>
                    <div class="codesite-pane-content">
                        <textarea id="codesite-css"><?php echo esc_textarea( $custom_css ); ?></textarea>
                    </div>
                </div>

                <div class="codesite-pane" data-pane="js">
                    <div class="codesite-pane-header">
                        <span class="pane-title">JS</span>
                    </div>
                    <div class="codesite-pane-content">
                        <textarea id="codesite-js"><?php echo esc_textarea( $custom_js ); ?></textarea>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    $('input[name="codesite-layout-mode"]').on('change', function() {
        var useBlocks = $(this).val() === 'blocks';
        $('.codesite-blocks-mode').toggle(useBlocks);
        $('.codesite-custom-mode').toggle(!useBlocks);
    });
});
</script>
