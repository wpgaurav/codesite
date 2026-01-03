/**
 * CodeSite Editor JavaScript
 *
 * Uses WordPress built-in code editor (CodeMirror).
 */

(function($) {
    'use strict';

    var editors = {};
    var previewDebounce = null;

    // CSS Snippets Library - declarations only (no class wrappers)
    var cssSnippets = {
        // Flexbox
        'flex-row': 'display: flex;\nflex-direction: row;\ngap: 1rem;',
        'flex-col': 'display: flex;\nflex-direction: column;\ngap: 1rem;',
        'flex-center': 'display: flex;\njustify-content: center;\nalign-items: center;',
        'flex-between': 'display: flex;\njustify-content: space-between;\nalign-items: center;',
        'flex-wrap': 'display: flex;\nflex-wrap: wrap;\ngap: 1rem;',

        // Grid
        'grid-2col': 'display: grid;\ngrid-template-columns: repeat(2, 1fr);\ngap: 1.5rem;',
        'grid-3col': 'display: grid;\ngrid-template-columns: repeat(3, 1fr);\ngap: 1.5rem;',
        'grid-4col': 'display: grid;\ngrid-template-columns: repeat(4, 1fr);\ngap: 1.5rem;',
        'grid-auto': 'display: grid;\ngrid-template-columns: repeat(auto-fit, minmax(250px, 1fr));\ngap: 1.5rem;',
        'grid-12': 'display: grid;\ngrid-template-columns: repeat(12, 1fr);\ngap: 1rem;',

        // Layout
        'container': 'width: 100%;\nmax-width: 1200px;\nmargin-left: auto;\nmargin-right: auto;\npadding-left: 1rem;\npadding-right: 1rem;',
        'full-height': 'min-height: 100vh;\ndisplay: flex;\nflex-direction: column;',
        'sticky-header': 'position: sticky;\ntop: 0;\nz-index: 100;\nbackground: #fff;',
        'sticky-footer': 'margin-top: auto;',

        // Typography
        'text-truncate': 'white-space: nowrap;\noverflow: hidden;\ntext-overflow: ellipsis;',
        'line-clamp': 'display: -webkit-box;\n-webkit-line-clamp: 3;\n-webkit-box-orient: vertical;\noverflow: hidden;',
        'responsive-text': 'font-size: clamp(1rem, 2.5vw, 2rem);\nline-height: 1.4;',

        // Effects
        'shadow': 'box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);',
        'shadow-lg': 'box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);',
        'transition': 'transition: all 0.3s ease;',
        'transition-fast': 'transition: all 0.15s ease;',
        'hover-scale': 'transition: transform 0.3s ease;',
        'gradient-bg': 'background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);',

        // Responsive
        'media-tablet': '@media (max-width: 768px) {\n  /* Tablet styles */\n  \n}',
        'media-mobile': '@media (max-width: 480px) {\n  /* Mobile styles */\n  \n}',

        // Buttons
        'btn': 'display: inline-block;\npadding: 0.75rem 1.5rem;\nfont-size: 1rem;\nfont-weight: 500;\ntext-align: center;\ntext-decoration: none;\nborder: none;\nborder-radius: 0.375rem;\ncursor: pointer;\ntransition: all 0.2s ease;',
        'btn-primary': 'background: #2563eb;\ncolor: #fff;',
        'btn-secondary': 'background: #6b7280;\ncolor: #fff;',

        // Cards
        'card': 'background: #fff;\nborder-radius: 0.5rem;\nbox-shadow: 0 1px 3px rgba(0,0,0,0.1);\noverflow: hidden;',
        'card-body': 'padding: 1.5rem;',

        // Forms
        'form-input': 'width: 100%;\npadding: 0.75rem 1rem;\nfont-size: 1rem;\nborder: 1px solid #d1d5db;\nborder-radius: 0.375rem;\ntransition: border-color 0.2s, box-shadow 0.2s;',
        'form-label': 'display: block;\nmargin-bottom: 0.5rem;\nfont-weight: 500;',

        // Navigation
        'nav': 'display: flex;\nalign-items: center;\ngap: 2rem;\npadding: 1rem 2rem;',
        'nav-links': 'display: flex;\ngap: 1.5rem;\nlist-style: none;\nmargin: 0;\npadding: 0;',

        // Hero Section
        'hero': 'padding: 6rem 2rem;\ntext-align: center;\nbackground: linear-gradient(135deg, #667eea 0%, #764ba2 100%);\ncolor: #fff;',

        // Footer
        'footer': 'padding: 3rem 2rem;\nbackground: #1f2937;\ncolor: #9ca3af;',

        // Animations
        'fade-in': 'animation: fadeIn 0.5s ease forwards;',
        'slide-up': 'animation: slideUp 0.5s ease forwards;',
        'pulse': 'animation: pulse 2s ease-in-out infinite;',
        'spin': 'animation: spin 1s linear infinite;',

        // Keyframes (these need full syntax)
        'keyframe-fade': '@keyframes fadeIn {\n  from { opacity: 0; }\n  to { opacity: 1; }\n}',
        'keyframe-slide': '@keyframes slideUp {\n  from { opacity: 0; transform: translateY(20px); }\n  to { opacity: 1; transform: translateY(0); }\n}',

        // Utilities
        'center-text': 'text-align: center;',
        'center-block': 'margin-left: auto;\nmargin-right: auto;',
        'hidden': 'display: none;',
        'sr-only': 'position: absolute;\nwidth: 1px;\nheight: 1px;\npadding: 0;\nmargin: -1px;\noverflow: hidden;\nclip: rect(0, 0, 0, 0);\nborder: 0;',

        // Reset/Normalize
        'reset': '/* CSS Reset */\n*, *::before, *::after {\n  box-sizing: border-box;\n}\n* {\n  margin: 0;\n}\nbody {\n  line-height: 1.5;\n  -webkit-font-smoothing: antialiased;\n}\nimg, picture, video, canvas, svg {\n  display: block;\n  max-width: 100%;\n}\ninput, button, textarea, select {\n  font: inherit;\n}'
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
        initCodeTools();
        initFullscreen();
        initCollapsibleSidebar();
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
     * Set editor value
     */
    function setEditorValue(type, value) {
        if (editors[type] && editors[type].codemirror) {
            editors[type].codemirror.setValue(value);
        } else {
            $('#codesite-' + type).val(value);
        }
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
        // Handle select dropdown
        $('#codesite-css-snippets').on('change', function() {
            var snippetKey = $(this).val();
            if (!snippetKey || !cssSnippets[snippetKey]) return;
            insertSnippet(snippetKey);
            $(this).val('');
        });

        // Handle searchable input
        $('#codesite-snippet-search').on('change', function() {
            var snippetKey = $(this).val().toLowerCase().trim();
            if (!snippetKey || !cssSnippets[snippetKey]) return;
            insertSnippet(snippetKey);
            $(this).val('');
        }).on('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                $(this).trigger('change');
            }
        });

        function insertSnippet(snippetKey) {
            var snippet = cssSnippets[snippetKey];
            if (!snippet) return;

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

            // Trigger preview update
            debouncePreview();
        }
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

    /**
     * Initialize code formatting tools
     */
    function initCodeTools() {
        // Format HTML button
        $(document).on('click', '.codesite-format-html', function() {
            var html = getEditorValue('html');
            var formatted = formatHTML(html);
            setEditorValue('html', formatted);
            debouncePreview();
        });

        // Format CSS button
        $(document).on('click', '.codesite-format-css', function() {
            var css = getEditorValue('css');
            var formatted = formatCSS(css);
            setEditorValue('css', formatted);
            debouncePreview();
        });

        // Format JS button
        $(document).on('click', '.codesite-format-js', function() {
            var js = getEditorValue('js');
            var formatted = formatJS(js);
            setEditorValue('js', formatted);
            debouncePreview();
        });
    }

    /**
     * Format HTML code
     */
    function formatHTML(html) {
        if (!html.trim()) return html;

        var indent = 0;
        var indentStr = '  ';
        var result = '';
        var inTag = false;
        var tagName = '';
        var selfClosing = ['area', 'base', 'br', 'col', 'embed', 'hr', 'img', 'input', 'link', 'meta', 'param', 'source', 'track', 'wbr'];

        // Normalize whitespace
        html = html.replace(/>\s+</g, '><').trim();

        for (var i = 0; i < html.length; i++) {
            var char = html[i];

            if (char === '<') {
                inTag = true;
                tagName = '';

                // Check if closing tag
                if (html[i + 1] === '/') {
                    indent = Math.max(0, indent - 1);
                }

                if (result && !result.endsWith('\n')) {
                    result += '\n';
                }
                result += indentStr.repeat(indent);
            }

            if (inTag && char !== '<' && char !== '>') {
                if (char !== ' ' && char !== '/') {
                    tagName += char.toLowerCase();
                }
            }

            result += char;

            if (char === '>') {
                inTag = false;

                // Increase indent for opening tags (not self-closing or closing)
                if (html[i - 1] !== '/' && !html.substring(Math.max(0, i - 10), i).includes('</')) {
                    if (selfClosing.indexOf(tagName) === -1 && tagName && tagName[0] !== '/') {
                        indent++;
                    }
                }
            }
        }

        return result.trim();
    }

    /**
     * Format CSS code
     */
    function formatCSS(css) {
        if (!css.trim()) return css;

        var result = '';
        var indent = 0;
        var indentStr = '  ';
        var inComment = false;
        var inString = false;
        var stringChar = '';

        // Remove extra whitespace but preserve structure
        css = css.replace(/\s+/g, ' ').trim();

        for (var i = 0; i < css.length; i++) {
            var char = css[i];
            var nextChar = css[i + 1] || '';
            var prevChar = css[i - 1] || '';

            // Handle comments
            if (char === '/' && nextChar === '*' && !inString) {
                inComment = true;
                if (result && !result.endsWith('\n')) {
                    result += '\n' + indentStr.repeat(indent);
                }
            }
            if (char === '/' && prevChar === '*' && inComment) {
                result += char + '\n';
                inComment = false;
                continue;
            }

            // Handle strings
            if ((char === '"' || char === "'") && prevChar !== '\\' && !inComment) {
                if (!inString) {
                    inString = true;
                    stringChar = char;
                } else if (char === stringChar) {
                    inString = false;
                }
            }

            if (!inComment && !inString) {
                if (char === '{') {
                    result += ' {\n';
                    indent++;
                    result += indentStr.repeat(indent);
                    continue;
                }
                if (char === '}') {
                    indent = Math.max(0, indent - 1);
                    if (!result.endsWith('\n')) {
                        result += '\n';
                    }
                    result += indentStr.repeat(indent) + '}\n';
                    if (nextChar && nextChar !== '}') {
                        result += '\n';
                    }
                    continue;
                }
                if (char === ';') {
                    result += ';\n' + indentStr.repeat(indent);
                    continue;
                }
                if (char === ':' && nextChar !== ' ') {
                    result += ': ';
                    continue;
                }
                if (char === ' ' && (result.endsWith('\n') || result.endsWith('  '))) {
                    continue;
                }
            }

            result += char;
        }

        // Clean up extra whitespace
        result = result.replace(/\n\s*\n\s*\n/g, '\n\n');
        result = result.replace(/{\s*\n\s*\n/g, '{\n');
        result = result.replace(/;\s*\n\s*}/g, ';\n}');

        return result.trim();
    }

    /**
     * Format JavaScript code
     */
    function formatJS(js) {
        if (!js.trim()) return js;

        var result = '';
        var indent = 0;
        var indentStr = '  ';
        var inString = false;
        var stringChar = '';
        var inComment = false;
        var inLineComment = false;

        for (var i = 0; i < js.length; i++) {
            var char = js[i];
            var nextChar = js[i + 1] || '';
            var prevChar = js[i - 1] || '';

            // Handle line comments
            if (char === '/' && nextChar === '/' && !inString && !inComment) {
                inLineComment = true;
            }
            if (char === '\n' && inLineComment) {
                inLineComment = false;
                result += char + indentStr.repeat(indent);
                continue;
            }

            // Handle block comments
            if (char === '/' && nextChar === '*' && !inString) {
                inComment = true;
            }
            if (char === '/' && prevChar === '*' && inComment) {
                result += char;
                inComment = false;
                continue;
            }

            // Handle strings
            if ((char === '"' || char === "'" || char === '`') && prevChar !== '\\' && !inComment && !inLineComment) {
                if (!inString) {
                    inString = true;
                    stringChar = char;
                } else if (char === stringChar) {
                    inString = false;
                }
            }

            if (!inComment && !inLineComment && !inString) {
                if (char === '{') {
                    result += ' {\n';
                    indent++;
                    result += indentStr.repeat(indent);
                    continue;
                }
                if (char === '}') {
                    indent = Math.max(0, indent - 1);
                    if (!result.endsWith('\n')) {
                        result += '\n';
                    }
                    result += indentStr.repeat(indent) + '}';
                    if (nextChar && nextChar !== ';' && nextChar !== ',' && nextChar !== ')') {
                        result += '\n' + indentStr.repeat(indent);
                    }
                    continue;
                }
                if (char === ';') {
                    result += ';\n' + indentStr.repeat(indent);
                    continue;
                }
                if (char === ' ' && (result.endsWith('\n') || result.endsWith('  '))) {
                    continue;
                }
            }

            result += char;
        }

        // Clean up
        result = result.replace(/\n\s*\n\s*\n/g, '\n\n');

        return result.trim();
    }

    /**
     * Initialize fullscreen mode
     */
    function initFullscreen() {
        $(document).on('click', '.codesite-fullscreen-toggle', function() {
            var $pane = $(this).closest('.codesite-pane');
            var $wrap = $('.codesite-editor-wrap');
            var $body = $('body');

            if ($pane.hasClass('fullscreen')) {
                // Exit fullscreen
                $pane.removeClass('fullscreen');
                $wrap.removeClass('has-fullscreen');
                $body.removeClass('codesite-fullscreen-active');
                $(this).attr('title', 'Fullscreen').find('.dashicons').removeClass('dashicons-editor-contract').addClass('dashicons-editor-expand');

                // Refresh editors after a longer delay to ensure layout is settled
                setTimeout(function() {
                    refreshEditors();
                    // Extra refresh for CodeMirror to recalculate dimensions
                    setTimeout(refreshEditors, 100);
                }, 50);
            } else {
                // Enter fullscreen
                $('.codesite-pane').removeClass('fullscreen');
                $pane.addClass('fullscreen');
                $wrap.addClass('has-fullscreen');
                $body.addClass('codesite-fullscreen-active');
                $(this).attr('title', 'Exit Fullscreen').find('.dashicons').removeClass('dashicons-editor-expand').addClass('dashicons-editor-contract');

                // Refresh editors for fullscreen layout
                setTimeout(refreshEditors, 50);
            }
        });

        // ESC to exit fullscreen
        $(document).on('keydown', function(e) {
            if (e.key === 'Escape' && $('.codesite-pane.fullscreen').length) {
                $('.codesite-pane.fullscreen').find('.codesite-fullscreen-toggle').trigger('click');
            }
        });
    }

    /**
     * Initialize collapsible sidebar
     */
    function initCollapsibleSidebar() {
        $(document).on('click', '.codesite-sidebar-toggle', function() {
            var $sidebar = $('.codesite-editor-sidebar');
            var $main = $('.codesite-editor-main');

            $sidebar.toggleClass('collapsed');

            if ($sidebar.hasClass('collapsed')) {
                $(this).attr('title', 'Show Sidebar').find('.dashicons').removeClass('dashicons-arrow-right-alt2').addClass('dashicons-arrow-left-alt2');
            } else {
                $(this).attr('title', 'Hide Sidebar').find('.dashicons').removeClass('dashicons-arrow-left-alt2').addClass('dashicons-arrow-right-alt2');
            }

            setTimeout(refreshEditors, 100);
        });
    }

    // Expose editors for external access
    window.codesiteEditors = editors;
    window.codesiteFormatHTML = formatHTML;
    window.codesiteFormatCSS = formatCSS;
    window.codesiteFormatJS = formatJS;

})(jQuery);
