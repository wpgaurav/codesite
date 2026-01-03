<?php
/**
 * Global CSS/JS editor page.
 */

// Security check.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$settings   = CodeSite_Database::get_all_settings();
$global_css = isset( $settings['global_css'] ) ? $settings['global_css'] : '';
$global_js  = isset( $settings['global_js'] ) ? $settings['global_js'] : '';
?>

<div class="wrap codesite-wrap codesite-editor-wrap">
    <div class="codesite-editor-header">
        <div class="codesite-editor-title">
            <h1><?php esc_html_e( 'Global CSS/JS', 'codesite' ); ?></h1>
        </div>
        <div class="codesite-editor-actions">
            <button type="button" id="codesite-save-global" class="button button-primary">
                <?php esc_html_e( 'Save', 'codesite' ); ?>
            </button>
        </div>
    </div>

    <div class="codesite-editor-body">
        <div class="codesite-editor-main">
            <p class="description" style="padding: 10px 20px; margin: 0;">
                <?php esc_html_e( 'Add CSS and JavaScript that will be loaded on every page rendered by CodeSite.', 'codesite' ); ?>
            </p>

            <div class="codesite-editor-panes" style="height: calc(100vh - 200px);">
                <div class="codesite-pane" data-pane="css" style="flex: 1;">
                    <div class="codesite-pane-header">
                        <span class="pane-title"><?php esc_html_e( 'Global CSS', 'codesite' ); ?></span>
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
                        </div>
                    </div>
                    <div class="codesite-pane-content">
                        <textarea id="codesite-css"><?php echo esc_textarea( $global_css ); ?></textarea>
                    </div>
                </div>

                <div class="codesite-pane" data-pane="js" style="flex: 1;">
                    <div class="codesite-pane-header">
                        <span class="pane-title"><?php esc_html_e( 'Global JavaScript', 'codesite' ); ?></span>
                    </div>
                    <div class="codesite-pane-content">
                        <textarea id="codesite-js"><?php echo esc_textarea( $global_js ); ?></textarea>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Save handler
    $('#codesite-save-global').on('click', function() {
        var $btn = $(this);
        $btn.prop('disabled', true).text(codesiteAdmin.strings.saving);

        // Get editor values
        var cssVal = '';
        var jsVal = '';

        if (window.codesiteEditors && window.codesiteEditors.css && window.codesiteEditors.css.codemirror) {
            cssVal = window.codesiteEditors.css.codemirror.getValue();
        } else {
            cssVal = $('#codesite-css').val();
        }

        if (window.codesiteEditors && window.codesiteEditors.js && window.codesiteEditors.js.codemirror) {
            jsVal = window.codesiteEditors.js.codemirror.getValue();
        } else {
            jsVal = $('#codesite-js').val();
        }

        $.ajax({
            url: codesiteAdmin.apiUrl + '/settings',
            method: 'POST',
            headers: {
                'X-WP-Nonce': codesiteAdmin.nonce
            },
            contentType: 'application/json',
            data: JSON.stringify({
                global_css: cssVal,
                global_js: jsVal
            }),
            success: function() {
                $btn.prop('disabled', false).text(codesiteAdmin.strings.saved);
                setTimeout(function() {
                    $btn.text('<?php esc_html_e( 'Save', 'codesite' ); ?>');
                }, 2000);
            },
            error: function() {
                $btn.prop('disabled', false).text(codesiteAdmin.strings.error);
            }
        });
    });
});
</script>
