# TransclusionFunctions

[This extension](https://github.com/Gittenburg/TransclusionFunctions) should work for MediaWiki 1.31.1 and higher and provides three [parser functions](https://www.mediawiki.org/wiki/Manual:Parser_functions).

## maptemplate

Usage: `{{#maptemplate:text|template|insep|outsep}}`

Splits the provided text on *insep*, wrapping each part in the given *template*, optionally followed by an *outsep*. For example: `{{#maptemplate:one;two;three|foo|;|,}}` &rarr; `{{foo|one}},{{foo|two}},{{foo|three}}`.
*insep* and *outsep* understand `\n` as a newline and support &lt;nowiki> tags, which are necessary for leading or trailing whitespace. Templates are also passed the index with the `#` parameter. Optionally a fifth parameter can be given, which is then passed to every template as `#userparam`.

The function is inspired by [`arraymaptemplate`  of Extension:Page Forms](https://www.mediawiki.org/wiki/Extension:Page_Forms/Page_Forms_and_templates#arraymaptemplate). Semantic MediaWiki's `#set` function [also provides a template parameter](https://www.semantic-mediawiki.org/wiki/Help:Setting_values/Working_with_the_template_parameter), which can act as a maptemplate but however inevitably sets an SMW property for every value, which can be undesirable. While the [#explode parser function](https://www.mediawiki.org/wiki/Extension:StringFunctions##explode:) can be used to approximate the functionality for a limited number of values, it limits the string length to `$wgPFStringLenghtLimit` and does not support extension tags.

## frametitle

Usage: `{{#frametitle:level}}`

MediaWiki parses templates with frames, this parser function makes the titles of these frames accessible. Level 0 yields the title of the innermost frame, level 1 the title of the parent frame, and so on ... When the level is omitted it defaults to 0. For example:

|       |Template:A             |Template:B| Page C   |
|-------|-----------------------|----------|----------|
|Source |`{{#frametitle:level}}`|`{{A}}`   | `{{B}}`
|level=0|Template:A             |Template:A|Template:A
|level=1|                       |Template:B|Template:B
|level=2|                       |          |Page C

The [MediaWiki page name magic words](https://www.mediawiki.org/wiki/Help:Magic_words#Page_names) only work on the title of the topmost page. This parser function allows templates to access their own title while embedded, or determine where they are embedded.

## ifembeds

Usage: `{{#ifembeds:template|yes|no}}`

After the current page has been parsed, check if the given template was transcluded. If so the second parameter is parsed, otherwise the third parameter. The metadata of the ParserOutput is then merged into the current page. This function can be used to categorize pages depending on if a template was used.

## Known incompatibilities

* [Extension:Labeled Section Transclusion](https://www.mediawiki.org/wiki/Extension:Labeled_Section_Transclusion) directly inserts transcluded wikitext in the current frame (`#frametitle` will not work as expected).
