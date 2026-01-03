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
        'show-mobile': '.show-mobile {\n  display: none;\n}\n@media (max-width: 768px) {\n  .show-mobile {\n    display: block;\n  }\n}',

        // Buttons
        'btn': '.btn {\n  display: inline-block;\n  padding: 0.75rem 1.5rem;\n  font-size: 1rem;\n  font-weight: 500;\n  text-align: center;\n  text-decoration: none;\n  border: none;\n  border-radius: 0.375rem;\n  cursor: pointer;\n  transition: all 0.2s ease;\n}\n.btn-primary {\n  background: #2563eb;\n  color: #fff;\n}\n.btn-primary:hover {\n  background: #1d4ed8;\n}\n.btn-secondary {\n  background: #6b7280;\n  color: #fff;\n}\n.btn-secondary:hover {\n  background: #4b5563;\n}\n.btn-outline {\n  background: transparent;\n  border: 2px solid currentColor;\n}\n.btn-outline:hover {\n  background: rgba(0,0,0,0.05);\n}',

        // Cards
        'card': '.card {\n  background: #fff;\n  border-radius: 0.5rem;\n  box-shadow: 0 1px 3px rgba(0,0,0,0.1);\n  overflow: hidden;\n}\n.card-img {\n  width: 100%;\n  height: 200px;\n  object-fit: cover;\n}\n.card-body {\n  padding: 1.5rem;\n}\n.card-title {\n  margin: 0 0 0.5rem;\n  font-size: 1.25rem;\n  font-weight: 600;\n}\n.card-text {\n  margin: 0;\n  color: #6b7280;\n}',

        // Forms
        'form': '.form-group {\n  margin-bottom: 1rem;\n}\n.form-label {\n  display: block;\n  margin-bottom: 0.5rem;\n  font-weight: 500;\n}\n.form-input {\n  width: 100%;\n  padding: 0.75rem 1rem;\n  font-size: 1rem;\n  border: 1px solid #d1d5db;\n  border-radius: 0.375rem;\n  transition: border-color 0.2s, box-shadow 0.2s;\n}\n.form-input:focus {\n  outline: none;\n  border-color: #2563eb;\n  box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);\n}\n.form-textarea {\n  min-height: 120px;\n  resize: vertical;\n}',

        // Navigation
        'nav': '.nav {\n  display: flex;\n  align-items: center;\n  gap: 2rem;\n  padding: 1rem 2rem;\n}\n.nav-brand {\n  font-size: 1.5rem;\n  font-weight: 700;\n  text-decoration: none;\n  color: inherit;\n}\n.nav-links {\n  display: flex;\n  gap: 1.5rem;\n  list-style: none;\n  margin: 0;\n  padding: 0;\n}\n.nav-link {\n  text-decoration: none;\n  color: #4b5563;\n  transition: color 0.2s;\n}\n.nav-link:hover {\n  color: #2563eb;\n}',

        // Hero Section
        'hero': '.hero {\n  padding: 6rem 2rem;\n  text-align: center;\n  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);\n  color: #fff;\n}\n.hero-title {\n  font-size: 3rem;\n  font-weight: 700;\n  margin: 0 0 1rem;\n}\n.hero-subtitle {\n  font-size: 1.25rem;\n  opacity: 0.9;\n  margin: 0 0 2rem;\n}\n@media (max-width: 768px) {\n  .hero-title {\n    font-size: 2rem;\n  }\n}',

        // Footer
        'footer': '.footer {\n  padding: 3rem 2rem;\n  background: #1f2937;\n  color: #9ca3af;\n}\n.footer-grid {\n  display: grid;\n  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));\n  gap: 2rem;\n  max-width: 1200px;\n  margin: 0 auto;\n}\n.footer-title {\n  color: #fff;\n  font-size: 1.125rem;\n  margin: 0 0 1rem;\n}\n.footer-links {\n  list-style: none;\n  padding: 0;\n  margin: 0;\n}\n.footer-links a {\n  color: #9ca3af;\n  text-decoration: none;\n}\n.footer-links a:hover {\n  color: #fff;\n}',

        // Animations
        'fade-in': '@keyframes fadeIn {\n  from { opacity: 0; }\n  to { opacity: 1; }\n}\n.fade-in {\n  animation: fadeIn 0.5s ease forwards;\n}',
        'slide-up': '@keyframes slideUp {\n  from {\n    opacity: 0;\n    transform: translateY(20px);\n  }\n  to {\n    opacity: 1;\n    transform: translateY(0);\n  }\n}\n.slide-up {\n  animation: slideUp 0.5s ease forwards;\n}',
        'pulse': '@keyframes pulse {\n  0%, 100% { opacity: 1; }\n  50% { opacity: 0.5; }\n}\n.pulse {\n  animation: pulse 2s ease-in-out infinite;\n}',
        'spin': '@keyframes spin {\n  from { transform: rotate(0deg); }\n  to { transform: rotate(360deg); }\n}\n.spin {\n  animation: spin 1s linear infinite;\n}',

        // Utilities
        'utilities': '/* Spacing */\n.m-0 { margin: 0; }\n.m-1 { margin: 0.25rem; }\n.m-2 { margin: 0.5rem; }\n.m-3 { margin: 1rem; }\n.m-4 { margin: 1.5rem; }\n.m-5 { margin: 2rem; }\n.p-0 { padding: 0; }\n.p-1 { padding: 0.25rem; }\n.p-2 { padding: 0.5rem; }\n.p-3 { padding: 1rem; }\n.p-4 { padding: 1.5rem; }\n.p-5 { padding: 2rem; }\n\n/* Text */\n.text-center { text-align: center; }\n.text-left { text-align: left; }\n.text-right { text-align: right; }\n.font-bold { font-weight: 700; }\n.font-medium { font-weight: 500; }\n\n/* Display */\n.hidden { display: none; }\n.block { display: block; }\n.inline-block { display: inline-block; }\n.sr-only {\n  position: absolute;\n  width: 1px;\n  height: 1px;\n  padding: 0;\n  margin: -1px;\n  overflow: hidden;\n  clip: rect(0, 0, 0, 0);\n  border: 0;\n}',

        // Reset/Normalize
        'reset': '/* CSS Reset */\n*, *::before, *::after {\n  box-sizing: border-box;\n}\n* {\n  margin: 0;\n}\nhtml {\n  -webkit-text-size-adjust: 100%;\n}\nbody {\n  line-height: 1.5;\n  -webkit-font-smoothing: antialiased;\n}\nimg, picture, video, canvas, svg {\n  display: block;\n  max-width: 100%;\n}\ninput, button, textarea, select {\n  font: inherit;\n}\np, h1, h2, h3, h4, h5, h6 {\n  overflow-wrap: break-word;\n}\na {\n  color: inherit;\n  text-decoration: inherit;\n}\nbutton {\n  background: none;\n  border: none;\n  cursor: pointer;\n}'
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

            if ($pane.hasClass('fullscreen')) {
                $pane.removeClass('fullscreen');
                $wrap.removeClass('has-fullscreen');
                $(this).attr('title', 'Fullscreen').find('.dashicons').removeClass('dashicons-editor-contract').addClass('dashicons-editor-expand');
            } else {
                $('.codesite-pane').removeClass('fullscreen');
                $pane.addClass('fullscreen');
                $wrap.addClass('has-fullscreen');
                $(this).attr('title', 'Exit Fullscreen').find('.dashicons').removeClass('dashicons-editor-expand').addClass('dashicons-editor-contract');
            }

            setTimeout(refreshEditors, 100);
        });

        // ESC to exit fullscreen
        $(document).on('keydown', function(e) {
            if (e.key === 'Escape' && $('.codesite-pane.fullscreen').length) {
                $('.codesite-fullscreen-toggle').trigger('click');
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
