// CodeMirror 5.65.16
// This is a placeholder file. Please download the actual CodeMirror library.
// See README.md in this directory for instructions.
//
// Download from: https://codemirror.net/5/
// Or use CDN: https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/codemirror.min.js

if (typeof CodeMirror === 'undefined') {
    console.warn('CodeMirror library not loaded. Please download CodeMirror 5.x from https://codemirror.net/5/');

    // Minimal fallback to prevent JS errors
    window.CodeMirror = {
        fromTextArea: function(textarea, options) {
            console.warn('CodeMirror not available. Using fallback.');
            var wrapper = document.createElement('div');
            wrapper.className = 'CodeMirror';
            wrapper.style.cssText = 'border: 1px solid #ccc; font-family: monospace; padding: 10px;';

            var textareaClone = textarea.cloneNode(true);
            textareaClone.style.cssText = 'width: 100%; height: 300px; font-family: monospace; font-size: 14px; border: none; resize: none;';
            textareaClone.style.display = 'block';

            wrapper.appendChild(textareaClone);
            textarea.parentNode.insertBefore(wrapper, textarea);
            textarea.style.display = 'none';

            return {
                getValue: function() { return textareaClone.value; },
                setValue: function(v) { textareaClone.value = v; },
                on: function(event, callback) {
                    textareaClone.addEventListener('input', callback);
                },
                getDoc: function() {
                    return {
                        getCursor: function() { return { line: 0, ch: textareaClone.selectionStart }; },
                        replaceRange: function(text, pos) {
                            var start = textareaClone.selectionStart;
                            var end = textareaClone.selectionEnd;
                            textareaClone.value = textareaClone.value.substring(0, start) + text + textareaClone.value.substring(end);
                            textareaClone.selectionStart = textareaClone.selectionEnd = start + text.length;
                        }
                    };
                },
                focus: function() { textareaClone.focus(); },
                refresh: function() {}
            };
        }
    };
}
