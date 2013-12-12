Extending Yii
=============

Code style
----------

- Extension code style SHOULD be similar to [core framework code style](https://github.com/yiisoft/yii2/wiki/Core-framework-code-style).
- In case of using getter and setter for defining a property it's preferred to use method in extension code rather than property.
- All classes, methods and properties SHOULD be documented using phpdoc. Note that you can use markdown and link to properties and methods
using the following syntax: e.g. `[[name()]]`, `[[name\space\MyClass::name()]]`.
- If you're displaying errors to developers do not translate these (i.e. do not use `\Yii::t()`). Errors should be
  translated only if they're displayed to end users.
- Extension SHOULD NOT use class prefixes.  
- Extension SHOULD provide a valid PSR-0 autoloading configuration in `composer.json`  
- Especially for core components (eg. `WebUser`) additional functionality SHOULD be implemented using behaviors or traits instead subclassing.

### Namespace and package names

- Extension MUST use the type `yii2-extension` in `composer.json` file.
- Extension MUST NOT use `yii` or `yii2` in the composer package name or in the namespaces used in the package.
- Extension SHOULD use namespaces in this format `vendor-name\package` (all lowercase).
- Extension MAY use a `yii2-` prefix in the composer vendor name (URL).
- Extension MAY use a `yii2-` prefix in the repository name (URL).

### Dependencies

- Additional code, eg. libraries, SHOULD be required in your `composer.json` file.
- Requirements SHOULD NOT include `dev` packages without a `stable` release.
- Use appropriate version constraints, eg. `1.*`, `@stable` for requirements.

### Versioning

- Extension SHOULD follow the rules of [semantic versioning](http://semver.org).
- Use a consistent format for your repository tags, as they are treated as version strings by composer, eg. `0.2.4`,`0.2.5`,`0.3.0`,`1.0.0`.

Distribution
------------

- There should be a `readme.md` file clearly describing what extension does in English, its requirements, how to install
  and use it. It should be written using markdown. If you want to provide translated readme, name it as `readme_ru.md`
  where `ru` is your language code. If extension provides a widget it is a good idea to include some screenshots.
- It is recommended to host your extensions at [Github](github.com).
- Extension MUST be registered at [Packagist](https://packagist.org).
- TBD: composer.json

Working with database
---------------------

- If extension creates or modifies database schema always use Yii migrations instead of SQL files or custom scripts.
- Migrations SHOULD be database agnostic.
- You MUST NOT make use of active-record model classes in your migrations.

Assets
------

- Asset files MUST be registered through Bundles.

Events
------

- Extension SHOULD make use of the event system, in favor of providing too complex configuration options.

i18n
----

- Extension SHOULD provide at least one message catalogue with either source or target language in English.
- Extension MAY provide a configuration for creating message catalogues.

Authorization
-------------

- Auth-items for controllers SHOULD be named after the following format `vendor\ext\controller\action`.
- Auth-items names may be shortened using an asterisk, eg. `vendor\ext\*`

Testing your extension
----------------------

- Extension SHOULD be testable with *Codeception*.