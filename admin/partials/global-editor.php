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
                    </div>
                    <div class="codesite-pane-content">
                        <textarea id="codesite-global-css"><?php echo esc_textarea( $global_css ); ?></textarea>
                    </div>
                </div>

                <div class="codesite-pane" data-pane="js" style="flex: 1;">
                    <div class="codesite-pane-header">
                        <span class="pane-title"><?php esc_html_e( 'Global JavaScript', 'codesite' ); ?></span>
                    </div>
                    <div class="codesite-pane-content">
                        <textarea id="codesite-global-js"><?php echo esc_textarea( $global_js ); ?></textarea>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Initialize editors for global CSS/JS
    var cssEditor = CodeMirror.fromTextArea(document.getElementById('codesite-global-css'), {
        mode: 'css',
        theme: 'dracula',
        lineNumbers: true,
        autoCloseBrackets: true
    });

    var jsEditor = CodeMirror.fromTextArea(document.getElementById('codesite-global-js'), {
        mode: 'javascript',
        theme: 'dracula',
        lineNumbers: true,
        autoCloseBrackets: true
    });

    // Save handler
    $('#codesite-save-global').on('click', function() {
        var $btn = $(this);
        $btn.prop('disabled', true).text(codesiteAdmin.strings.saving);

        $.ajax({
            url: codesiteAdmin.apiUrl + '/settings',
            method: 'POST',
            headers: {
                'X-WP-Nonce': codesiteAdmin.nonce
            },
            contentType: 'application/json',
            data: JSON.stringify({
                global_css: cssEditor.getValue(),
                global_js: jsEditor.getValue()
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
