/**
 * CodeSite Editor JavaScript
 *
 * Uses WordPress built-in code editor (CodeMirror).
 */

(function($) {
    'use strict';

    var editors = {};
    var previewDebounce = null;

    // CSS Snippets Library
    var cssSnippets = {
        // Flexbox
        'flex-row': '.flex-row {\n  display: flex;\n  flex-direction: row;\n  gap: 1rem;\n}',
        'flex-col': '.flex-col {\n  display: flex;\n  flex-direction: column;\n  gap: 1rem;\n}',
        'flex-center': '.flex-center {\n  display: flex;\n  justify-content: center;\n  align-items: center;\n}',
        'flex-between': '.flex-between {\n  display: flex;\n  justify-content: space-between;\n  align-items: center;\n}',
        'flex-wrap': '.flex-wrap {\n  display: flex;\n  flex-wrap: wrap;\n  gap: 1rem;\n}',

        // Grid
        'grid-2col': '.grid-2col {\n  display: grid;\n  grid-template-columns: repeat(2, 1fr);\n  gap: 1.5rem;\n}',
        'grid-3col': '.grid-3col {\n  display: grid;\n  grid-template-columns: repeat(3, 1fr);\n  gap: 1.5rem;\n}',
        'grid-4col': '.grid-4col {\n  display: grid;\n  grid-template-columns: repeat(4, 1fr);\n  gap: 1.5rem;\n}',
        'grid-auto': '.grid-auto {\n  display: grid;\n  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));\n  gap: 1.5rem;\n}',
        'grid-12': '/* 12 Column Grid System */\n.grid-12 {\n  display: grid;\n  grid-template-columns: repeat(12, 1fr);\n  gap: 1rem;\n}\n.col-1 { grid-column: span 1; }\n.col-2 { grid-column: span 2; }\n.col-3 { grid-column: span 3; }\n.col-4 { grid-column: span 4; }\n.col-5 { grid-column: span 5; }\n.col-6 { grid-column: span 6; }\n.col-7 { grid-column: span 7; }\n.col-8 { grid-column: span 8; }\n.col-9 { grid-column: span 9; }\n.col-10 { grid-column: span 10; }\n.col-11 { grid-column: span 11; }\n.col-12 { grid-column: span 12; }',

        // Layout
        'container': '.container {\n  width: 100%;\n  max-width: 1200px;\n  margin-left: auto;\n  margin-right: auto;\n  padding-left: 1rem;\n  padding-right: 1rem;\n}',
        'full-height': '.full-height {\n  min-height: 100vh;\n  display: flex;\n  flex-direction: column;\n}',
        'sticky-header': '.sticky-header {\n  position: sticky;\n  top: 0;\n  z-index: 100;\n  background: #fff;\n}',
        'sticky-footer': '/* Sticky Footer Layout */\n.page-wrapper {\n  min-height: 100vh;\n  display: flex;\n  flex-direction: column;\n}\n.main-content {\n  flex: 1;\n}\n.sticky-footer {\n  margin-top: auto;\n}',

        // Typography
        'text-truncate': '.text-truncate {\n  white-space: nowrap;\n  overflow: hidden;\n  text-overflow: ellipsis;\n}',
        'line-clamp': '.line-clamp {\n  display: -webkit-box;\n  -webkit-line-clamp: 3;\n  -webkit-box-orient: vertical;\n  overflow: hidden;\n}',
        'responsive-text': '.responsive-text {\n  font-size: clamp(1rem, 2.5vw, 2rem);\n  line-height: 1.4;\n}',

        // Effects
        'shadow': '.shadow {\n  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);\n}\n.shadow-lg {\n  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);\n}',
        'transition': '.transition {\n  transition: all 0.3s ease;\n}\n.transition-fast {\n  transition: all 0.15s ease;\n}\n.transition-slow {\n  transition: all 0.5s ease;\n}',
        'hover-scale': '.hover-scale {\n  transition: transform 0.3s ease;\n}\n.hover-scale:hover {\n  transform: scale(1.05);\n}',
        'gradient-bg': '.gradient-bg {\n  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);\n}\n.gradient-text {\n  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);\n  -webkit-background-clip: text;\n  -webkit-text-fill-color: transparent;\n  background-clip: text;\n}',

        // Responsive
        'media-tablet': '@media (max-width: 768px) {\n  /* Tablet styles */\n  \n}',
        'media-mobile': '@media (max-width: 480px) {\n  /* Mobile styles */\n  \n}',
        'hide-mobile': '.hide-mobile {\n  display: block;\n}\n@media (max-width: 768px) {\n  .hide-mobile {\n    display: none;\n  }\n}',
        'show-mobile': '.show-mobile {\n  display: none;\n}\n@media (max-width: 768px) {\n  .show-mobile {\n    display: block;\n  }\n}'
    };

    // Initialize when DOM is ready
    $(document).ready(function() {
        initEditors();
        initPreview();
        initPaneToggles();
        initPreviewSizes();
        initFieldInserter();
        initSaveHandler();
        initCssSnippets();
        initClassSuggestions();
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

    /**
     * Initialize CSS snippet insertion
     */
    function initCssSnippets() {
        $('#codesite-css-snippets').on('change', function() {
            var snippetKey = $(this).val();
            if (!snippetKey || !cssSnippets[snippetKey]) return;

            var snippet = cssSnippets[snippetKey];

            // Insert into CSS editor
            if (editors.css && editors.css.codemirror) {
                var cm = editors.css.codemirror;
                var doc = cm.getDoc();
                var cursor = doc.getCursor();
                var currentValue = cm.getValue();

                // Add newline if content exists and doesn't end with newlines
                if (currentValue && !currentValue.endsWith('\n\n')) {
                    snippet = (currentValue.endsWith('\n') ? '\n' : '\n\n') + snippet;
                }

                doc.replaceRange(snippet + '\n', cursor);
                cm.focus();
            } else {
                var $textarea = $('#codesite-css');
                if ($textarea.length) {
                    var currentVal = $textarea.val();
                    var separator = currentVal && !currentVal.endsWith('\n\n') ? '\n\n' : '';
                    $textarea.val(currentVal + separator + snippet + '\n');
                }
            }

            // Reset dropdown
            $(this).val('');

            // Trigger preview update
            debouncePreview();
        });
    }

    /**
     * Initialize class suggestions from HTML
     */
    function initClassSuggestions() {
        var $classSelect = $('#codesite-html-classes');
        if (!$classSelect.length) return;

        // Update class suggestions when HTML changes
        function updateClassSuggestions() {
            var html = getEditorValue('html');
            var classes = extractClasses(html);

            // Clear existing options except the first
            $classSelect.find('option:not(:first)').remove();

            // Add new options
            if (classes.length > 0) {
                classes.forEach(function(className) {
                    $classSelect.append(
                        $('<option>', { value: className, text: '.' + className })
                    );
                });
            }
        }

        // Extract classes from HTML
        function extractClasses(html) {
            var classes = [];
            var regex = /class\s*=\s*["']([^"']+)["']/gi;
            var match;

            while ((match = regex.exec(html)) !== null) {
                var classNames = match[1].split(/\s+/);
                classNames.forEach(function(name) {
                    name = name.trim();
                    if (name && classes.indexOf(name) === -1) {
                        classes.push(name);
                    }
                });
            }

            return classes.sort();
        }

        // Insert selected class into CSS editor
        $classSelect.on('change', function() {
            var className = $(this).val();
            if (!className) return;

            var selector = '.' + className + ' {\n  \n}';

            // Insert into CSS editor
            if (editors.css && editors.css.codemirror) {
                var cm = editors.css.codemirror;
                var doc = cm.getDoc();
                var cursor = doc.getCursor();
                var currentValue = cm.getValue();

                // Add newline if content exists
                if (currentValue && !currentValue.endsWith('\n\n')) {
                    selector = (currentValue.endsWith('\n') ? '\n' : '\n\n') + selector;
                }

                doc.replaceRange(selector, cursor);

                // Position cursor inside the braces
                var newCursor = doc.getCursor();
                doc.setCursor({ line: newCursor.line - 1, ch: 2 });
                cm.focus();
            } else {
                var $textarea = $('#codesite-css');
                if ($textarea.length) {
                    var currentVal = $textarea.val();
                    var separator = currentVal && !currentVal.endsWith('\n\n') ? '\n\n' : '';
                    $textarea.val(currentVal + separator + selector);
                }
            }

            // Reset dropdown
            $(this).val('');

            // Trigger preview update
            debouncePreview();
        });

        // Initial update
        setTimeout(updateClassSuggestions, 600);

        // Update when HTML editor changes
        if (editors.html && editors.html.codemirror) {
            editors.html.codemirror.on('change', function() {
                clearTimeout(window.classUpdateTimeout);
                window.classUpdateTimeout = setTimeout(updateClassSuggestions, 500);
            });
        } else {
            $('#codesite-html').on('input', function() {
                clearTimeout(window.classUpdateTimeout);
                window.classUpdateTimeout = setTimeout(updateClassSuggestions, 500);
            });
        }
    }

    // Expose editors for external access
    window.codesiteEditors = editors;

})(jQuery);
