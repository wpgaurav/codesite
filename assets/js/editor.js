/**
 * CodeSite Editor JavaScript
 *
 * Uses WordPress built-in code editor (CodeMirror).
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
     * Initialize code editors using WordPress built-in CodeMirror
     */
    function initEditors() {
        var settings = codesiteAdmin.editorSettings;

        // HTML Editor
        var htmlTextarea = document.getElementById('codesite-html');
        if (htmlTextarea && settings.html) {
            editors.html = wp.codeEditor.initialize(htmlTextarea, settings.html);
            if (editors.html && editors.html.codemirror) {
                editors.html.codemirror.on('change', debouncePreview);
            }
        }

        // CSS Editor
        var cssTextarea = document.getElementById('codesite-css');
        if (cssTextarea && settings.css) {
            editors.css = wp.codeEditor.initialize(cssTextarea, settings.css);
            if (editors.css && editors.css.codemirror) {
                editors.css.codemirror.on('change', debouncePreview);
            }
        }

        // JS Editor
        var jsTextarea = document.getElementById('codesite-js');
        if (jsTextarea && settings.js) {
            editors.js = wp.codeEditor.initialize(jsTextarea, settings.js);
            if (editors.js && editors.js.codemirror) {
                editors.js.codemirror.on('change', debouncePreview);
            }
        }

        // Fallback: If editors didn't initialize, make textareas work
        if (!editors.html && htmlTextarea) {
            $(htmlTextarea).on('input', debouncePreview);
        }
        if (!editors.css && cssTextarea) {
            $(cssTextarea).on('input', debouncePreview);
        }
        if (!editors.js && jsTextarea) {
            $(jsTextarea).on('input', debouncePreview);
        }

        // Refresh editors after layout settles
        setTimeout(refreshEditors, 200);
        $(window).on('resize', refreshEditors);
    }

    /**
     * Refresh all CodeMirror editors
     */
    function refreshEditors() {
        Object.keys(editors).forEach(function(key) {
            if (editors[key] && editors[key].codemirror) {
                editors[key].codemirror.refresh();
            }
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
        setTimeout(updatePreview, 500);
    }

    /**
     * Get editor value
     */
    function getEditorValue(type) {
        if (editors[type] && editors[type].codemirror) {
            return editors[type].codemirror.getValue();
        }
        var $textarea = $('#codesite-' + type);
        return $textarea.length ? $textarea.val() : '';
    }

    /**
     * Update the live preview
     */
    function updatePreview() {
        var $frame = $('#codesite-preview-frame');
        if (!$frame.length) return;

        var html = getEditorValue('html');
        var css = getEditorValue('css');
        var js = getEditorValue('js');

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

            setTimeout(refreshEditors, 100);
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
            if (!field) return;

            if (editors.html && editors.html.codemirror) {
                var cm = editors.html.codemirror;
                var doc = cm.getDoc();
                var cursor = doc.getCursor();
                doc.replaceRange(field, cursor);
                cm.focus();
            } else {
                var $textarea = $('#codesite-html');
                if ($textarea.length) {
                    var pos = $textarea[0].selectionStart || 0;
                    var val = $textarea.val();
                    $textarea.val(val.substring(0, pos) + field + val.substring(pos));
                    $textarea[0].selectionStart = $textarea[0].selectionEnd = pos + field.length;
                    $textarea.focus();
                }
            }
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

        // Ctrl/Cmd + S to save
        $(document).on('keydown', function(e) {
            if ((e.ctrlKey || e.metaKey) && e.key === 's') {
                e.preventDefault();
                $('#codesite-save').trigger('click');
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
                    html: getEditorValue('html'),
                    css: getEditorValue('css'),
                    js: getEditorValue('js'),
                    category: $('#codesite-block-category').val() || 'general',
                    css_scope: $('#codesite-block-css-scope').val(),
                    status: $('#codesite-block-status').val()
                };
                break;

            case 'layout':
                data = {
                    name: $('#codesite-layout-name').val(),
                    slug: $('#codesite-layout-slug').val(),
                    type: $('#codesite-layout-type').val(),
                    use_blocks: 0,
                    block_order: window.getBlockOrder ? window.getBlockOrder() : [],
                    custom_html: getEditorValue('html'),
                    custom_css: getEditorValue('css'),
                    custom_js: getEditorValue('js'),
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
                    content_blocks: window.getBlockOrder ? window.getBlockOrder() : [],
                    custom_html: getEditorValue('html'),
                    custom_css: getEditorValue('css'),
                    custom_js: getEditorValue('js'),
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
