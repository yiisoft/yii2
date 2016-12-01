# Yii Documentation Style Guide

Guidelines to go by when writing or editing any Yii documentation.

*This needs to be expanded.*

## General Style

* Try to use an active voice.
* Use short, declarative sentences.
* Demonstrate ideas using code as much as possible.
* Never use "we". It's the Yii development team or the Yii core team. Better yet to put things in terms of the framework or the guide.
* Use the Oxford comma (e.g., "this, that, and the other" not "this, that and the other").

## Formatting

* Use *italics* for emphasis, never capitalization, bold, or underlines.

## Lists

* Numeric lists should be complete sentences that end with periods.
* Bullet lists should be fragments that end with semicolon except the last item, which should end with a period.

## Blocks

Blocks use the Markdown `> Type: `. There are four block types:

* `Warning`, for bad security things and other problems
* `Note`, to emphasize key concepts, things to avoid
* `Info`, general information (an aside); not as strong as a "Note"
* `Tip`, pro tips, extras, can be useful but may not be needed by everyone all the time

The sentence after the colon should begin with a capital letter.

When translating documentation, these Block indicators should not be translated.
Keeps them intact as they are and only translate the block content.
For translating the `Type` word, each guide translation should have a `blocktypes.json` file
containing the translations. The following shows an example for German:

```json
{
    "Warning:": "Achtung:",
    "Note:": "Hinweis:",
    "Info:": "Info:",
    "Tip:": "Tipp:"
}
```

## References

* Yii 2.0 or Yii 2 (not Yii2 or Yii2.0)
* Each "page" of the guide is referred to as a "section".
* References to Code objects:
  - Refer to classes using the full namespace: `yii\base\Model`
  - Refer to class properties using the static syntax even if they are not static: `yii\base\Model::$validators`
  - Refer to class methods using the static syntax even if they are not static and include parenthesis to make it clear, that it is a method: `yii\base\Model::validate()`
  - references to code objects should be writting in `[[]]` to generate links to the API documentation. E.g. `[[yii\base\Model]]`, `[[yii\base\Model::$validators]]`, or `[[yii\base\Model::validate()]]`.

## Capitalizations

* Web, not web
* the guide or this guide, not the Guide

## Validating the docs

The following are some scripts that help find broken links and other issues in the guide:

Find broken links (some false-positives may occur):

    grep -rniP "\[\[[^\],']+?\][^\]]"  docs/guide*
    grep -rniP "[^\[]\[[^\]\[,']+?\]\]"  docs/guide*
    
## Attribution of Translators

The names of the translators will be listed among the guide authors in the
rendered versions of the guide.
Therefor in each guide directory for a different language than english a `translators.json` file
should be created that contains an array of names of the people who have participated in the translation.

```json
[
  "Jane Doe",
  "John Doe"
]
```

If you have contributed a significant part to the translation, feel free to send a pull request adding your name.
