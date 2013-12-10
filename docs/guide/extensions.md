Extending Yii
=============

Code style
----------

- Extension code style should be similar to [core framework code style](https://github.com/yiisoft/yii2/wiki/Core-framework-code-style).
- In case of using getter and setter for defining a property it's preferred to use method in extension code rather than property.
- All classes, methods and properties should be documented using phpdoc. Note that you can use markdown and like to API
documents using `[[name()]]`.
- If you're displaying errors to developers do not translate these (i.e. do not use `\Yii::t()`). Errors should be
  translated only if they're displayed to end users.

### Namespace and package names

- Extension MUST use the type `yii2-extension` in `composer.json` file.
- Extension MUST NOT use `yii` or `yii2` in the composer package name or in the namespaces used in the package.
- Extension SHOULD use namespaces in this format `vendor-name\package` (all lowercase).
- Extension MAY use a `yii2-` prefix in the composer vendor name (URL).
- Extension MAY use a `yii2-` prefix in the repository name (URL).

Distribution
------------

- There should be a `readme.md` file clearly describing what extension does in English, its requirements, how to install
  and use it. It should be written using markdown. If you want to provide translated readme, name it as `readme_ru.md`
  where `ru` is your language code. If extension provides a widget it is a good idea to include some screenshots.
- TBD: composer.json
- It is recommended to host your extensions at github.com.

Working with database
---------------------

- If extension creates or modifies database schema always use Yii migrations instead of SQL files or custom scripts.

Assets
------

TBD

Events
------

TBD

i18n
----

TBD

Testing your extension
----------------------

TBD