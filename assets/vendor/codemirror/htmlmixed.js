// CodeMirror HTML mixed mode - Placeholder
// Download from: https://codemirror.net/5/mode/htmlmixed/htmlmixed.js
(function(mod) {
    if (typeof exports == "object" && typeof module == "object")
        mod(require("../../lib/codemirror"), require("../xml/xml"), require("../javascript/javascript"), require("../css/css"));
    else if (typeof define == "function" && define.amd)
        define(["../../lib/codemirror", "../xml/xml", "../javascript/javascript", "../css/css"], mod);
    else
        mod(CodeMirror);
})(function(CodeMirror) {
    "use strict";

    CodeMirror.defineMode("htmlmixed", function(config, parserConfig) {
        return CodeMirror.getMode(config, "xml");
    }, "xml", "javascript", "css");

    CodeMirror.defineMIME("text/html", "htmlmixed");
});
