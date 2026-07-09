# Web Platform Tests: Sanitizer API

This directory contains test fixtures from the Web Platform Tests `sanitizer-api`
suite, used for testing `WP_HTML_Sanitizer` against the same conformance data
browsers use for the HTML Sanitizer API.

The fixtures were taken from
[`web-platform-tests/wpt/sanitizer-api`](https://github.com/web-platform-tests/wpt/tree/master/sanitizer-api)
at commit SHA
[`69da2f70b8e617659e6ddc0c3c99755417482512`](https://github.com/web-platform-tests/wpt/commit/69da2f70b8e617659e6ddc0c3c99755417482512).

They are licensed under the 3-Clause BSD License; see `../LICENSE.md`.

## File provenance

Copied verbatim from the WPT repository:

- `sethtml-safety.sub.dat`
- `sethtml-unsafety.sub.dat`
- `sethtml-tree-construction.sub.dat`

Extracted from the `<script type="html5lib-testcases">` blocks embedded in the
corresponding WPT `.html` test files (the test *data* is unmodified; only the
surrounding HTML/JS harness was stripped):

- `sanitizer-basic-filtering.dat` (from `sanitizer-basic-filtering.html`)
- `sanitizer-javascript-url.dat` (from `sanitizer-javascript-url.html`)
- `html5lib-basics.dat` (from `html5lib-basics.html`)

## Format

Each test case consists of `#data` (input HTML), an optional `#config` (a JSON
`SanitizerConfig`), and `#document` (the expected tree in html5lib tree-dump
format), optionally with `#document-fragment` (the parse context element) or
`#error` (an expected `TypeError` for invalid configurations).

Two files carry harness-level conventions from their source `.html` files,
honored by the PHPUnit test class rather than encoded in the data:

- `sanitizer-javascript-url.dat`: cases run with an empty sanitizer
  configuration (`{sanitizer: {}}`) under the safe operation.
- `html5lib-basics.dat` and `sethtml-unsafety.sub.dat`: cases run under the
  unsafe operation (`setHTMLUnsafe`).

The `{{host}}` placeholders in `.sub.dat` files are WPT server substitutions;
they are compared literally here since they appear identically in input and
expected output.

## Updating

1. Check out the latest version of the WPT repository mentioned above.
2. Replace the verbatim `.sub.dat` files with the current ones from `sanitizer-api/`.
3. Re-extract the `.dat` files from the `html5lib-testcases` script blocks of
   the corresponding `.html` files.
4. Update the SHA mentioned in this README file.
