# CodeMirror Installation

This directory should contain CodeMirror 5.x files.

## Required Files

Download from https://codemirror.net/5/ and place:

1. `codemirror.js` - Core library
2. `codemirror.css` - Core styles
3. `dracula.css` - Theme (from lib/codemirror/theme/)

### Modes (from mode/ directory):
4. `xml.js` - XML mode
5. `css.js` - CSS mode
6. `javascript.js` - JavaScript mode
7. `htmlmixed.js` - HTML mixed mode

### Addons (from addon/ directory):
8. `closetag.js` - Auto close tags (edit/closetag.js)
9. `closebrackets.js` - Auto close brackets (edit/closebrackets.js)
10. `emmet.js` - Emmet support (optional, from external source)

## Quick Setup

```bash
# Download CodeMirror
curl -L https://codemirror.net/5/codemirror.zip -o codemirror.zip
unzip codemirror.zip

# Copy required files
cp codemirror-5.*/lib/codemirror.js ./
cp codemirror-5.*/lib/codemirror.css ./
cp codemirror-5.*/theme/dracula.css ./
cp codemirror-5.*/mode/xml/xml.js ./
cp codemirror-5.*/mode/css/css.js ./
cp codemirror-5.*/mode/javascript/javascript.js ./
cp codemirror-5.*/mode/htmlmixed/htmlmixed.js ./
cp codemirror-5.*/addon/edit/closetag.js ./
cp codemirror-5.*/addon/edit/closebrackets.js ./
```

## CDN Alternative

If you prefer using a CDN during development, you can modify the enqueue functions in `admin/class-codesite-admin.php` to load from:

```
https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/
```
