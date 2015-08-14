Translation workflow
====================

Yii is translated in many languages in order to be useful for international applications and developers. Two main areas
where contribution is very welcome are documentation and framework messages.

Framework messages
------------------

The framework has two types of messages: exceptions that are intended to developer and are never translated and messages
that are actually visible to the end user such as validation errors.

In order to start with message translation:

1. Check `framework/messages/config.php` and make sure your language is listed in `languages`. If not,
   add your language there (remember to keep the list in alphabetical order). The format of language code
   should follow [IETF language tag spec](http://en.wikipedia.org/wiki/IETF_language_tag), for example,
   `ru`, `zh-CN`.
2. Go to `framework` and run `yii message/extract messages/config.php`.
3. Translate messages in `framework/messages/your_language/yii.php`. Make sure file is saved using UTF-8 encoding.
4. [Make a pull request](git-workflow.md).

In order to keep your translation up to date you may run `yii message/extract messages/config.php` again. It will
automatically re-extract messages keeping unchanged ones intact.

In the translation file each array element represents the translation (value) of a message (key). If the value is empty,
the message is considered as not translated. Messages that no longer need translation will have their translations
enclosed between a pair of '@@' marks. Message string can be used with plural forms format. Check [i18n section
of the guide](../guide/tutorial-i18n.md) for details.

Documentation
-------------

Put documentation translations under `docs/<original>-<language>` where `<original>` is the original documentation name
such as `guide` or `internals` and `<language>` is the language code of the language docs are translated to. For the
Russian guide translation it is `docs/guide-ru`.

After initial work is done you can get what's changed since last translation of the file using a special command from
`build` directory:

```
php build translation "../docs/guide" "../docs/guide-ru" "Russian guide translation report" > report_guide_ru.html
```

If it will complain about composer, perform `composer install` in the source root dir.
