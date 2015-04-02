Internationalization
====================

> Note: This section is under development.

Internationalization (I18N) refers to the process of designing a software application so that it can be adapted to
various languages and regions without engineering changes. For Web applications, this is of particular importance
because the potential users may be worldwide.

Yii offers several tools that help with internationalization of a website such as message translation and
number- and date-formatting.

Locale and Language
-------------------

There are two languages defined in the Yii application: [[yii\base\Application::$sourceLanguage|source language]] and
[[yii\base\Application::$language|target language]].

The source language is the language in which the original application messages are written directly in the code such as:

```php
echo \Yii::t('app', 'I am a message!');
```

The target language is the language that should be used to display the current page, i.e. the language that original messages need
to be translated to. It is defined in the application configuration like the following:

```php
return [
    'id' => 'applicationID',
    'basePath' => dirname(__DIR__),
    // ...
    'language' => 'ru-RU', // <- here!
    // ...
]
```

> **Tip**: The default value for the [[yii\base\Application::$sourceLanguage|source language]] is English and it is
> recommended to keep this value. The reason is that it's easier to find people translating from
> English to any language than from non-English to non-English.

You may set the application language at runtime to the language that the user has chosen.
This has to be done at a point before any output is generated so that it affects all the output correctly.
Therefor just change the application property to the desired value:

```php
\Yii::$app->language = 'zh-CN';
```

The format for the language/locale is `ll-CC` where `ll` is a two- or three-letter lowercase code for a language according to
[ISO-639](http://www.loc.gov/standards/iso639-2/) and `CC` is the country code according to
[ISO-3166](http://www.iso.org/iso/en/prods-services/iso3166ma/02iso-3166-code-lists/list-en1.html).

> **Note**: For more information on the concept and syntax of locales, check the
> [documentation of the ICU project](http://userguide.icu-project.org/locale#TOC-The-Locale-Concept).

Message translation
-------------------

Message translation is used to translate the messages that are output by an application to different languages
so that users from different countries can use the application in their native language.

The message translation feature in Yii works simply as finding a
translation of the message from a source language into a target language.
To use the message translation feature you wrap your original message strings with a call to the [[Yii::t()]] method.
The first parameter of this method takes a category which helps to distinguish the source of messages in different parts
of the application and the second parameter is the message itself.

```php
echo \Yii::t('app', 'This is a string to translate!');
```

Yii tries to load an appropriate translation according to the current [[yii\base\Application::$language|application language]]
from one of the message sources defined in the `i18n` [application component](structure-application-components.md).
A message source is a set of files or a database that provides translation messages.
The following configuration example defines a messages source that takes the messages from PHP files:

```php
'components' => [
    // ...
    'i18n' => [
        'translations' => [
            'app*' => [
                'class' => 'yii\i18n\PhpMessageSource',
                //'basePath' => '@app/messages',
                //'sourceLanguage' => 'en-US',
                'fileMap' => [
                    'app' => 'app.php',
                    'app/error' => 'error.php',
                ],
            ],
        ],
    ],
],
```

In the above `app*` is a pattern that specifies which categories are handled by the message source. In this case we're
handling everything that begins with `app`. Message files are located in `@app/messages`, the `messages` directory
in your application directory. The [[yii\i18n\PhpMessageSource::fileMap|fileMap]] array
defines which file is to be used for which category.
Instead of configuring `fileMap` you can rely on the convention which is to use the category name as the file name
(e.g. category `app/error` will result in the file name `app/error.php` under the [[yii\i18n\PhpMessageSource::basePath|basePath]].

When translating the message for `\Yii::t('app', 'This is a string to translate!')` with the application language being `ru-RU`, Yii
will first look for a file `@app/messages/ru-RU/app.php` to retrieve the list of available translations.
If there is no such file under `ru-RU`, it will try `ru` as well before failing.

Beside storing the messages in PHP files (using [[yii\i18n\PhpMessageSource|PhpMessageSource]]), Yii provides two other
classes:

- [[yii\i18n\GettextMessageSource]] that uses GNU Gettext MO or PO files.
- [[yii\i18n\DbMessageSource]] that uses a database.


### Named placeholders

You can add parameters to a translation message that will be substituted with the corresponding value after translation.
The format for this is to use curly brackets around the parameter name as you can see in the following example:

```php
$username = 'Alexander';
echo \Yii::t('app', 'Hello, {username}!', [
    'username' => $username,
]);
```

Note that the parameter assignment is without the brackets.

### Positional placeholders

```php
$sum = 42;
echo \Yii::t('app', 'Balance: {0}', $sum);
```

> **Tip**: Try to keep the message strings meaningful and avoid using too many positional parameters. Remember that
> the translator has only the source string, so it should be obvious about what will replace each placeholder.

### Advanced placeholder formatting

In order to use the advanced features you need to install and enable the [intl PHP extension](http://www.php.net/manual/en/intro.intl.php).
After installing and enabling it you will be able to use the extended syntax for placeholders: either the short form
`{placeholderName, argumentType}` that uses the default style, or the full form `{placeholderName, argumentType, argumentStyle}`
that allows you to specify the formatting style.

A complete reference is available at the [ICU website](http://icu-project.org/apiref/icu4c/classMessageFormat.html) but we will show some examples in the following.

#### Numbers

```php
$sum = 42;
echo \Yii::t('app', 'Balance: {0, number}', $sum);
```

You can specify one of the built-in styles (`integer`, `currency`, `percent`):

```php
$sum = 42;
echo \Yii::t('app', 'Balance: {0, number, currency}', $sum);
```

Or specify a custom pattern:

```php
$sum = 42;
echo \Yii::t('app', 'Balance: {0, number, ,000,000000}', $sum);
```

[Formatting reference](http://icu-project.org/apiref/icu4c/classicu_1_1DecimalFormat.html).

#### Dates

```php
echo \Yii::t('app', 'Today is {0, date}', time());
```

Built in formats are `short`, `medium`, `long`, and `full`:

```php
echo \Yii::t('app', 'Today is {0, date, short}', time());
```

You may also specify a custom pattern:

```php
echo \Yii::t('app', 'Today is {0, date, yyyy-MM-dd}', time());
```

[Formatting reference](http://icu-project.org/apiref/icu4c/classicu_1_1SimpleDateFormat.html).

#### Time

```php
echo \Yii::t('app', 'It is {0, time}', time());
```

Built in formats are `short`, `medium`, `long`, and `full`:

```php
echo \Yii::t('app', 'It is {0, time, short}', time());
```

You may also specify a custom pattern:

```php
echo \Yii::t('app', 'It is {0, date, HH:mm}', time());
```

[Formatting reference](http://icu-project.org/apiref/icu4c/classicu_1_1SimpleDateFormat.html).


#### Spellout

```php
echo \Yii::t('app', '{n,number} is spelled as {n, spellout}', ['n' => 42]);
```

#### Ordinal

```php
echo \Yii::t('app', 'You are {n, ordinal} visitor here!', ['n' => 42]);
```

Will produce "You are 42nd visitor here!".

#### Duration

```php
echo \Yii::t('app', 'You are here for {n, duration} already!', ['n' => 47]);
```

Will produce "You are here for 47 sec. already!".

#### Plurals

Different languages have different ways to inflect plurals. Yii provides a convenient way for translating messages in
different plural forms that works well even for very complex rules. Instead of dealing with the inflection rules directly,
it is sufficient to provide the translation of inflected words in certain situations only.

```php
echo \Yii::t('app', 'There {n, plural, =0{are no cats} =1{is one cat} other{are # cats}}!', ['n' => $n]);
```

Will give us

- "There are no cats!" for `$n = 0`,
- "There is one cat!" for `$n = 1`,
- and "There are 42 cats!" for `$n = 42`.

In the plural rule arguments above, `=0` means exactly zero, `=1` stands for exactly one, and `other` is for any other number.
`#` is replaced with the value of `n`. It's not that simple for languages other than English. Here's an example
for Russian:

```
Здесь {n, plural, =0{котов нет} =1{есть один кот} one{# кот} few{# кота} many{# котов} other{# кота}}!
```

In the above it's worth mentioning that `=1` matches exactly `n = 1` while `one` matches `21` or `101`.

Note, that you can not use the Russian example in `Yii::t()` directly if your
[[yii\base\Application::$sourceLanguage|source language]] isn't set to `ru-RU`. This however is not recommended, instead such
strings should go into message files or message database (in case DB source is used). Yii uses the plural rules of the
translated language strings and is falling back to the plural rules of the source language if the translation isn't available.

To learn which inflection forms you should specify for your language, you can refeer to the
[rules reference at unicode.org](http://unicode.org/repos/cldr-tmp/trunk/diff/supplemental/language_plural_rules.html).

#### Selections

You can select phrases based on keywords. The pattern in this case specifies how to map keywords to phrases and
provides a default phrase.

```php
echo \Yii::t('app', '{name} is a {gender} and {gender, select, female{she} male{he} other{it}} loves Yii!', [
    'name' => 'Snoopy',
    'gender' => 'dog',
]);
```

Will produce "Snoopy is a dog and it loves Yii!".

In the expression above, `female` and `male` are possible values, while `other` handles values that do not match. A string inside
the brackets is a sub-expression, so it could be a plain string or a string with nested placeholders in it.

### Specifying default translation

You can specify default translations that will be used as a fallback for categories that don't match any other translation.
This translation should be marked with `*`. In order to do it add the following to the application config:

```php
//configure i18n component

'i18n' => [
    'translations' => [
        '*' => [
            'class' => 'yii\i18n\PhpMessageSource'
        ],
    ],
],
```

Now you can use categories without configuring each one, which is similar to Yii 1.1 behavior.
Messages for the category will be loaded from a file under the default translation `basePath` that is `@app/messages`:

```php
echo Yii::t('not_specified_category', 'message from unspecified category');
```

The message will be loaded from `@app/messages/<LanguageCode>/not_specified_category.php`.

### Translating module messages

If you want to translate the messages for a module and avoid using a single translation file for all the messages, you can do it like the following:

```php
<?php

namespace app\modules\users;

use Yii;

class Module extends \yii\base\Module
{
    public $controllerNamespace = 'app\modules\users\controllers';

    public function init()
    {
        parent::init();
        $this->registerTranslations();
    }

    public function registerTranslations()
    {
        Yii::$app->i18n->translations['modules/users/*'] = [
            'class' => 'yii\i18n\PhpMessageSource',
            'sourceLanguage' => 'en-US',
            'basePath' => '@app/modules/users/messages',
            'fileMap' => [
                'modules/users/validation' => 'validation.php',
                'modules/users/form' => 'form.php',
                ...
            ],
        ];
    }

    public static function t($category, $message, $params = [], $language = null)
    {
        return Yii::t('modules/users/' . $category, $message, $params, $language);
    }

}
```

In the example above we are using wildcard for matching and then filtering each category per needed file. Instead of using `fileMap`, you can simply
use the convention of the category mapping to the same named file.
Now you can use `Module::t('validation', 'your custom validation message')` or `Module::t('form', 'some form label')` directly.

### Translating widgets messages

The same rule as applied for Modules above can be applied for widgets too, for example:

```php
<?php

namespace app\widgets\menu;

use yii\base\Widget;
use Yii;

class Menu extends Widget
{

    public function init()
    {
        parent::init();
        $this->registerTranslations();
    }

    public function registerTranslations()
    {
        $i18n = Yii::$app->i18n;
        $i18n->translations['widgets/menu/*'] = [
            'class' => 'yii\i18n\PhpMessageSource',
            'sourceLanguage' => 'en-US',
            'basePath' => '@app/widgets/menu/messages',
            'fileMap' => [
                'widgets/menu/messages' => 'messages.php',
            ],
        ];
    }

    public function run()
    {
        echo $this->render('index');
    }

    public static function t($category, $message, $params = [], $language = null)
    {
        return Yii::t('widgets/menu/' . $category, $message, $params, $language);
    }

}
```

Instead of using `fileMap` you can simply use the convention of the category mapping to the same named file.
Now you can use `Menu::t('messages', 'new messages {messages}', ['{messages}' => 10])` directly.

> **Note**: For widgets you also can use i18n views, with the same rules as for controllers being applied to them too.


### Translating framework messages

Yii comes with the default translation messages for validation errors and some other strings. These messages are all
in the category `yii`. Sometimes you want to correct the default framework message translation for your application.
In order to do so, configure the `i18n` [application component](structure-application-components.md) like the following:

```php
'i18n' => [
    'translations' => [
        'yii' => [
            'class' => 'yii\i18n\PhpMessageSource',
            'sourceLanguage' => 'en-US',
            'basePath' => '@app/messages'
        ],
    ],
],
```

Now you can place your adjusted translations to `@app/messages/<language>/yii.php`.

### Handling missing translations

Even if the translation is missing from the source, Yii will display the requested message content. Such behavior is very convenient
in case your raw message is a valid verbose text. However, sometimes it is not enough.
You may need to perform some custom processing of the situation, when the requested translation is missing from the source.
This can be achieved using the [[yii\i18n\MessageSource::EVENT_MISSING_TRANSLATION|missingTranslation]]-event of [[yii\i18n\MessageSource]].

For example, let us mark all the missing translations with something notable so that they can be easily found at the page.
First we need to setup an event handler. This can be done in the application configuration:

```php
'components' => [
    // ...
    'i18n' => [
        'translations' => [
            'app*' => [
                'class' => 'yii\i18n\PhpMessageSource',
                'fileMap' => [
                    'app' => 'app.php',
                    'app/error' => 'error.php',
                ],
                'on missingTranslation' => ['app\components\TranslationEventHandler', 'handleMissingTranslation']
            ],
        ],
    ],
],
```

Now we need to implement our own event handler:

```php
<?php

namespace app\components;

use yii\i18n\MissingTranslationEvent;

class TranslationEventHandler
{
    public static function handleMissingTranslation(MissingTranslationEvent $event) {
        $event->translatedMessage = "@MISSING: {$event->category}.{$event->message} FOR LANGUAGE {$event->language} @";
    }
}
```

If [[yii\i18n\MissingTranslationEvent::translatedMessage]] is set by the event handler it will be displayed as the translation result.

> Note: each message source handles its missing translations separately. If you are using several message sources
> and wish them to treat the missing translations in the same way, you should assign the corresponding event handler to each of them.


### Using the `message` command <a name="message-command"></a>

Translations can be stored in [[yii\i18n\PhpMessageSource|php files]], [[yii\i18n\GettextMessageSource|.po files] or to [[yii\i18n\DbMessageSource|database]]. See specific classes for additional options.

First of all you need to create a config file. Decide where you want to store it and then issue the command 

```bash
./yii message/config path/to/config.php
```

Open the created file and adjust the parameters to fit your needs. Pay special attention to:

* `languages`: an array representing what languages your app should be translated to;
* `messagePath`: path where to store message files, which should match the `i18n`'s `basePath` parameter stated in config.

> Note that aliases are not supported here, they must be real path relative to the config file location

Once you're done with the config file you can finally extract your messages with the command

```bash
./yii message path/to/config.php
```

You will then find your files (if you've choosen file based translations) in your `messagePath` directory.

Views
-----

Instead of translating messages as described in the last section,
you can also use `i18n` in your views to provide support for different languages. For example, if you have a view `views/site/index.php` and
you want to create a special version for the Russian language, you create a `ru-RU` folder under the view path of the current controller/widget and
put the file for the Russian language as `views/site/ru-RU/index.php`. Yii will then load the file for the current language if it exists
and fall back to the original view file if none was found.

> **Note**: If language is specified as `en-US` and there are no corresponding views, Yii will try views under `en`
> before using original ones.


Formatting Number and Date values
---------------------------------

See the [data formatter section](output-formatting.md) for details.


Setting up your PHP environment <span id="setup-environment"></span>
-------------------------------

Yii uses the [PHP intl extension](http://php.net/manual/en/book.intl.php) to provide most of its internationalization features
such as the number and date formatting of the [[yii\i18n\Formatter]] class and the message formatting using [[yii\i18n\MessageFormatter]].
Both classes provides a fallback implementation that provides basic functionality in case `intl` is not installed.
This fallback implementation however only works well for sites in English language and even there can not provide the
rich set of features that is available with the PHP intl extension, so its installation is highly recommended.

The [PHP intl extension](http://php.net/manual/en/book.intl.php) is based on the [ICU library](http://site.icu-project.org/) which
provides the knowledge and formatting rules for all the different locales. According to this fact the formatting of dates and numbers
and also the supported syntax available for message formatting differs between different versions of the ICU library that is compiled with
you PHP binary.

To ensure your website works with the same output in all environments it is recommended to install the PHP intl extension
in all environments and verify that the version of the ICU library compiled with PHP is the same.

To find out which version of ICU is used by PHP you can run the following script, which will give you the PHP and ICU version used.

```php
<?php
echo "PHP: " . PHP_VERSION . "\n";
echo "ICU: " . INTL_ICU_VERSION . "\n";
```

We recommend an ICU version greater or equal to version ICU 49 to be able to use all the features described in this document.
One major feature that is missing in Versions below 49 is the `#` placeholder in plural rules.
See <http://site.icu-project.org/download> for a list of available ICU versions. Note that the version numbering has changed after the
4.8 release so that the first digits are now merged: the sequence is ICU 4.8, ICU 49, ICU 50.

Additionally the information in the time zone database shipped with the ICU library may be outdated. Please refer
to the [ICU manual](http://userguide.icu-project.org/datetime/timezone#TOC-Updating-the-Time-Zone-Data) for details
on updating the time zone database. While for output formatting the ICU timezone database is used, the time zone database
used by PHP may be relevant too. You can update it by installing the latest version of the [pecl package `timezonedb`](http://pecl.php.net/package/timezonedb).
