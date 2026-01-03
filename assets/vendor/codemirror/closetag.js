// CodeMirror closetag addon - Placeholder
// Download from: https://codemirror.net/5/addon/edit/closetag.js
(function(mod) {
    if (typeof exports == "object" && typeof module == "object")
        mod(require("../../lib/codemirror"), require("../fold/xml-fold"));
    else if (typeof define == "function" && define.amd)
        define(["../../lib/codemirror", "../fold/xml-fold"], mod);
    else
        mod(CodeMirror);
})(function(CodeMirror) {
    // Placeholder - auto close tags functionality
});
