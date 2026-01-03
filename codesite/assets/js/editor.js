/**
 * CodeSite Editor JavaScript
 *
 * Handles the CodeMirror editors and live preview.
 */

(function($) {
    'use strict';

    var editors = {};
    var previewDebounce = null;

    // Initialize when DOM is ready
    $(document).ready(function() {
        initEditors();
        initPreview();
        initPaneToggles();
        initPreviewSizes();
        initFieldInserter();
        initSaveHandler();
    });

    /**
     * Initialize CodeMirror editors
     */
    function initEditors() {
        var editorConfig = {
            lineNumbers: true,
            autoCloseBrackets: true,
            autoCloseTags: true,
            matchBrackets: true,
            indentUnit: 2,
            tabSize: 2,
            indentWithTabs: false,
            lineWrapping: true,
            theme: 'dracula'
        };

        // HTML Editor
        var htmlTextarea = document.getElementById('codesite-html');
        if (htmlTextarea) {
            editors.html = CodeMirror.fromTextArea(htmlTextarea, $.extend({}, editorConfig, {
                mode: 'htmlmixed'
            }));
            editors.html.on('change', debouncePreview);
        }

        // CSS Editor
        var cssTextarea = document.getElementById('codesite-css');
        if (cssTextarea) {
            editors.css = CodeMirror.fromTextArea(cssTextarea, $.extend({}, editorConfig, {
                mode: 'css'
            }));
            editors.css.on('change', debouncePreview);
        }

        // JS Editor
        var jsTextarea = document.getElementById('codesite-js');
        if (jsTextarea) {
            editors.js = CodeMirror.fromTextArea(jsTextarea, $.extend({}, editorConfig, {
                mode: 'javascript'
            }));
            editors.js.on('change', debouncePreview);
        }

        // Refresh editors on window resize
        $(window).on('resize', function() {
            Object.values(editors).forEach(function(editor) {
                editor.refresh();
            });
        });
    }

    /**
     * Debounce preview updates
     */
    function debouncePreview() {
        clearTimeout(previewDebounce);
        previewDebounce = setTimeout(updatePreview, 300);
    }

    /**
     * Initialize preview
     */
    function initPreview() {
        // Initial preview
        setTimeout(updatePreview, 500);
    }

    /**
     * Update the live preview
     */
    function updatePreview() {
        var $frame = $('#codesite-preview-frame');
        if (!$frame.length) return;

        var html = editors.html ? editors.html.getValue() : '';
        var css = editors.css ? editors.css.getValue() : '';
        var js = editors.js ? editors.js.getValue() : '';

        var previewHtml = '<!DOCTYPE html>' +
            '<html>' +
            '<head>' +
            '<meta charset="UTF-8">' +
            '<meta name="viewport" content="width=device-width, initial-scale=1">' +
            '<style>' +
            '* { box-sizing: border-box; }' +
            'body { margin: 0; padding: 20px; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; font-size: 16px; line-height: 1.6; }' +
            'img { max-width: 100%; height: auto; }' +
            css +
            '</style>' +
            '</head>' +
            '<body>' +
            html +
            '<script>' + js + '<\/script>' +
            '</body>' +
            '</html>';

        var frame = $frame[0];
        var doc = frame.contentDocument || frame.contentWindow.document;
        doc.open();
        doc.write(previewHtml);
        doc.close();
    }

    /**
     * Initialize pane toggles
     */
    function initPaneToggles() {
        $(document).on('click', '.codesite-pane-toggle', function() {
            var $pane = $(this).closest('.codesite-pane');
            var $content = $pane.find('.codesite-pane-content');
            var isCollapsed = $pane.hasClass('collapsed');

            if (isCollapsed) {
                $pane.removeClass('collapsed');
                $content.show();
                $(this).text('−');
            } else {
                $pane.addClass('collapsed');
                $content.hide();
                $(this).text('+');
            }

            // Refresh editors
            setTimeout(function() {
                Object.values(editors).forEach(function(editor) {
                    editor.refresh();
                });
            }, 100);
        });
    }

    /**
     * Initialize preview size controls
     */
    function initPreviewSizes() {
        $('.codesite-preview-size').on('click', function() {
            var width = $(this).data('width');
            var $frame = $('#codesite-preview-frame');

            $('.codesite-preview-size').removeClass('active');
            $(this).addClass('active');

            $frame.css('width', width);
        });
    }

    /**
     * Initialize dynamic field inserter
     */
    function initFieldInserter() {
        $('#codesite-insert-field').on('click', function() {
            var field = $('#codesite-dynamic-field').val();
            if (!field || !editors.html) return;

            var doc = editors.html.getDoc();
            var cursor = doc.getCursor();
            doc.replaceRange(field, cursor);
            editors.html.focus();
        });
    }

    /**
     * Initialize save handler
     */
    function initSaveHandler() {
        $('#codesite-save').on('click', function() {
            var $btn = $(this);
            var type = $btn.data('type');
            var id = $btn.data('id');

            $btn.prop('disabled', true).text(codesiteAdmin.strings.saving);

            var data = collectData(type);
            var method = id ? 'PUT' : 'POST';
            var endpoint = codesiteAdmin.apiUrl + '/' + type + 's' + (id ? '/' + id : '');

            $.ajax({
                url: endpoint,
                method: method,
                headers: {
                    'X-WP-Nonce': codesiteAdmin.nonce
                },
                contentType: 'application/json',
                data: JSON.stringify(data),
                success: function(response) {
                    $btn.prop('disabled', false).text(codesiteAdmin.strings.saved);

                    // Update URL if new item
                    if (!id && response.id) {
                        var newUrl = codesiteAdmin.adminUrl + 'admin.php?page=codesite-' + type + '-editor&id=' + response.id;
                        window.history.replaceState({}, '', newUrl);
                        $btn.data('id', response.id);
                    }

                    setTimeout(function() {
                        $btn.text('Save');
                    }, 2000);
                },
                error: function(xhr) {
                    $btn.prop('disabled', false).text(codesiteAdmin.strings.error);
                    console.error('Save error:', xhr.responseJSON);
                    setTimeout(function() {
                        $btn.text('Save');
                    }, 2000);
                }
            });
        });

        // Keyboard shortcut: Ctrl/Cmd + S
        $(document).on('keydown', function(e) {
            if ((e.ctrlKey || e.metaKey) && e.key === 's') {
                e.preventDefault();
                $('#codesite-save').click();
            }
        });
    }

    /**
     * Collect form data based on type
     */
    function collectData(type) {
        var data = {};

        switch (type) {
            case 'block':
                data = {
                    name: $('#codesite-block-name').val(),
                    slug: $('#codesite-block-slug').val(),
                    html: editors.html ? editors.html.getValue() : '',
                    css: editors.css ? editors.css.getValue() : '',
                    js: editors.js ? editors.js.getValue() : '',
                    category: $('#codesite-block-category').val() || 'general',
                    css_scope: $('#codesite-block-css-scope').val(),
                    status: $('#codesite-block-status').val()
                };
                break;

            case 'layout':
                var useBlocks = $('input[name="codesite-layout-mode"]:checked').val() === 'blocks';
                data = {
                    name: $('#codesite-layout-name').val(),
                    slug: $('#codesite-layout-slug').val(),
                    type: $('#codesite-layout-type').val(),
                    use_blocks: useBlocks ? 1 : 0,
                    block_order: useBlocks ? window.getBlockOrder() : [],
                    custom_html: !useBlocks && editors.html ? editors.html.getValue() : '',
                    custom_css: !useBlocks && editors.css ? editors.css.getValue() : '',
                    custom_js: !useBlocks && editors.js ? editors.js.getValue() : '',
                    status: $('#codesite-layout-status').val()
                };
                break;

            case 'template':
                data = {
                    name: $('#codesite-template-name').val(),
                    slug: $('#codesite-template-slug').val(),
                    template_type: $('#codesite-template-type').val(),
                    header_layout_id: $('#codesite-template-header').val() || null,
                    footer_layout_id: $('#codesite-template-footer').val() || null,
                    content_blocks: window.getBlockOrder(),
                    custom_html: editors.html ? editors.html.getValue() : '',
                    custom_css: editors.css ? editors.css.getValue() : '',
                    custom_js: editors.js ? editors.js.getValue() : '',
                    priority: parseInt($('#codesite-template-priority').val(), 10) || 10,
                    status: $('#codesite-template-status').val()
                };
                break;
        }

        return data;
    }

    // Expose editors for external access
    window.codesiteEditors = editors;

})(jQuery);
