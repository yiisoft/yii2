Extending Yii
=============

Code style
----------

- Extension code style SHOULD be similar to [core framework code style](https://github.com/yiisoft/yii2/wiki/Core-framework-code-style).
- All classes, methods and properties SHOULD be documented using phpdoc. Note that you can use markdown and link to properties and methods
using the following syntax: e.g. `[[name()]]`, `[[name\space\MyClass::name()]]`.
- If you're displaying errors to developers do not translate these (i.e. do not use `\Yii::t()`). Errors should be
  translated only if they're displayed to end users.
- Extension SHOULD NOT use class prefixes (i.e. `TbNavBar`, `EMyWidget`, etc.)


### Namespace

- Extension MUST NOT use `yiisoft` in the namespaces used in the package.
- Extension MUST NOT have a root-namespace named `\yii`, `\yii2` or `\yiisoft`.
- Extension SHOULD use namespaces in this format `vendor-name\type` (all lowercase).

Distribution
------------

- There should be a `readme.md` file clearly describing what extension does in English, its requirements, how to install
  and use it. It should be written using markdown. If you want to provide translated readme, name it as `readme_ru.md`
  where `ru` is your language code. If extension provides a widget it is a good idea to include some screenshots.
- It is recommended to host your extensions at [Github](github.com).
- Extension MUST be registered at [Packagist](https://packagist.org). Choose package name wisely since changing it leads
  to losing stats and inability to install package by the old name.
- Extension MUST provide a valid autoloading configuration in `composer.json`.


### Composer package name

If your extension was made specifically for Yii2 (i.e. cannot be used as a standalone PHP library) it is recommended to
name it like the following:

```
yii2-my-extension-name-type
```

In the above:

- `yii2-` prefix.
- Extension name lowecase, words separated by `-`.
- `-type` postfix where type may be `widget`, `behavior`, `module` etc.

### Dependencies

- Additional code, eg. libraries, SHOULD be required in your `composer.json` file.
- When extension is released in a stable version, its requirements SHOULD NOT include `dev` packages that do not have a `stable` release.
- Use appropriate version constraints, eg. `1.*`, `@stable` for requirements.

### Versioning

- Extension SHOULD follow the rules of [semantic versioning](http://semver.org).
- Use a consistent format for your repository tags, as they are treated as version strings by composer, eg. `0.2.4`,`0.2.5`,`0.3.0`,`1.0.0`.

### composer.json

- Extension MUST use the type `yii2-extension` in `composer.json` file.
- Extension MUST NOT use `yii` or `yii2` in their composer vendor name.
- Extension MUST NOT use `yiisoft` in the composer package name or the composer vendor name.

If your extension classes reside directly in repository root use PSR-4 the following way in your `composer.json`:

```
{
        "name": "samdark/yii2-iconized-menu-widget",
        "description": "IconizedMenu automatically adds favicons in front of menu links",
        "keywords": ["yii", "extension", "favicon", "menu", "icon"],
        "homepage": "https://github.com/samdark/yii2-iconized-menu-widget",
        "type": "yii2-extension",
        "license": "BSD-3-Clause",
        "authors": [
                {
                        "name": "Alexander Makarov",
                        "email": "sam@rmcreative.ru"
                }
        ],
        "require": {
                "yiisoft/yii2": "*"
        },
        "autoload": {
                "psr-4": {
                        "samdark\\widgets\\": ""
                }
        }
}
```

In the above `samdark/yii2-iconized-menu-widget` is the package name that will be registered
at [Packagist](https://packagist.org). It is common for it to match your github repository.

We're using `psr-4` autoloader and mapping `samdark\widgets` namespace to the root directory where our classes reside.

More details can be found in the [composer documentation](http://getcomposer.org/doc/04-schema.md#autoload).

Working with database
---------------------

- If extension creates or modifies database schema always use Yii migrations instead of SQL files or custom scripts.
- Migrations SHOULD be DBMS agnostic.
- You MUST NOT make use of active-record model classes in your migrations.

Assets
------

- Asset files MUST be registered through Bundles.

Events
------

TBD

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

- Extension SHOULD be testable with *PHPUnit*.
