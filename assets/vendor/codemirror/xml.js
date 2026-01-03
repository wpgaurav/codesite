// CodeMirror XML mode - Placeholder
// Download from: https://codemirror.net/5/mode/xml/xml.js
(function(mod) {
    if (typeof exports == "object" && typeof module == "object")
        mod(require("../../lib/codemirror"));
    else if (typeof define == "function" && define.amd)
        define(["../../lib/codemirror"], mod);
    else
        mod(CodeMirror);
})(function(CodeMirror) {
    "use strict";

    CodeMirror.defineMode("xml", function(config, parserConfig) {
        return {
            token: function(stream) {
                stream.next();
                return null;
            }
        };
    });

    CodeMirror.defineMIME("text/xml", "xml");
    CodeMirror.defineMIME("application/xml", "xml");
    CodeMirror.defineMIME("text/html", {name: "xml", htmlMode: true});
});
