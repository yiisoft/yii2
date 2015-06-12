Internationalization
====================

Internationalization (I18N) refers to the process of designing a software application so that it can be adapted to
various languages and regions without engineering changes. For Web applications, this is of particular importance
because the potential users may be worldwide. Yii offers a full spectrum of I18N features that support message
translation, view translation, date and number formatting.


## Locale and Language <span id="locale-language"></span>

Locale is a set of parameters that defines the user's language, country and any special variant preferences 
that the user wants to see in their user interface. It is usually identified by an ID consisting of a language 
ID and a region ID. For example, the ID `en-US` stands for the locale of English and United States. 
For consistency, all locale IDs used in Yii applications should be canonicalized to the format of 
`ll-CC`, where `ll` is a two- or three-letter lowercase language code according to
[ISO-639](http://www.loc.gov/standards/iso639-2/) and `CC` is a two-letter country code according to
[ISO-3166](http://www.iso.org/iso/en/prods-services/iso3166ma/02iso-3166-code-lists/list-en1.html).
More details about locale can be found in check the 
[documentation of the ICU project](http://userguide.icu-project.org/locale#TOC-The-Locale-Concept).

In Yii, we often use the term "language" to refer to a locale.

A Yii application uses two kinds of languages: [[yii\base\Application::$sourceLanguage|source language]] and
[[yii\base\Application::$language|target language]]. The former refers to the language in which the text messages
in the source code are written, while the latter is the language that should be used to display content to end users.
The so-called message translation service mainly translates a text message from source language to target language.

You can configure application languages in the application configuration like the following:

```php
return [
    // set target language to be Russian
    'language' => 'ru-RU',
    
    // set source language to be English
    'sourceLanguage' => 'en-US',
    
    ......
];
```

The default value for the [[yii\base\Application::$sourceLanguage|source language]] is `en-US`, meaning
US English. It is recommended that you keep this default value unchanged, because it is usually much easier
to find people who can translate from English to other languages than from non-English to non-English.

You often need to set the [[yii\base\Application::$language|target language]] dynamically based on different 
factors, such as the language preference of end users. Instead of configuring it in the application configuration,
you can use the following statement to change the target language:

```php
// change target language to Chinese
\Yii::$app->language = 'zh-CN';
```

## Message Translation <span id="message-translation"></span>

Message translation service translates a text message from one language (usually the [[yii\base\Application::$sourceLanguage|source language]])
to another (usually the [[yii\base\Application::$language|target language]]). It does the translation by looking
up the message to be translated in a message source which stores the original messages and the translated messages.
If the message is found, the corresponding translated message will be returned; otherwise the message will be returned
untranslated.

To use message translation service, you mainly need to do the following work:

* Wrap every text message that needs to be translated in a call to the [[Yii::t()]] method;
* Configure one or multiple message sources in which the message translation service can look for translated messages;
* Let the translators to translate messages and store them in the message source(s).

The method [[Yii::t()]] can be used like the following,

```php
echo \Yii::t('app', 'This is a string to translate!');
```

where the second parameter refers to the text message to be translated, while the first parameter refers to 
the name of the category which is used to categorize the message. 

The [[Yii::t()]] method will call the `i18n` [application component](structure-application-components.md) 
to perform the actual translation work. The component can be configured in the application configuration as follows,

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

In the above code, a message source supported by [[yii\i18n\PhpMessageSource]] is being configured. The pattern
`app*` indicates that all message categories whose names start with `app` should be translated using this
message source. The [[yii\i18n\PhpMessageSource]] class uses PHP files to store message translations. Each
PHP file corresponds to the messages of a single category. By default, the file name should be the same as
the category name. However, you may configure [[yii\i18n\PhpMessageSource::fileMap|fileMap]] to map a category
to a PHP file with a different naming approach. In the above example, the category `app/error` is mapped to
the PHP file `@app/messages/ru-RU/error.php` (assuming `ru-RU` is the target language). Without this configuration,
the category would be mapped to `@app/messages/ru-RU/app/error.php`, instead.

Beside storing the messages in PHP files, you may also use the following message sources to store translated messages
in different storage:

- [[yii\i18n\GettextMessageSource]] uses GNU Gettext MO or PO files to maintain translated messages.
- [[yii\i18n\DbMessageSource]] uses a database table to store translated messages.


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

For example, you may want to mark all the missing translations with something notable, so that they can be easily found at the page.
First you need to setup an event handler. This can be done in the application configuration:

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

Now you need to implement your own event handler:

```php
<?php

namespace app\components;

use yii\i18n\MissingTranslationEvent;

class TranslationEventHandler
{
    public static function handleMissingTranslation(MissingTranslationEvent $event)
    {
        $event->translatedMessage = "@MISSING: {$event->category}.{$event->message} FOR LANGUAGE {$event->language} @";
    }
}
```

If [[yii\i18n\MissingTranslationEvent::translatedMessage]] is set by the event handler it will be displayed as the translation result.

> Note: each message source handles its missing translations separately. If you are using several message sources
> and wish them to treat the missing translations in the same way, you should assign the corresponding event handler to each of them.


### Using the `message` command <a name="message-command"></a>

Translations can be stored in [[yii\i18n\PhpMessageSource|php files]], [[yii\i18n\GettextMessageSource|.po files] or to [[yii\i18n\DbMessageSource|database]]. See specific classes for additional options.

First of all you need to create a configuration file. Decide where you want to store it and then issue the command 

```shell
./yii message/config-template path/to/config.php
```

Open the created file and adjust the parameters to fit your needs. Pay special attention to:

* `languages`: an array representing what languages your app should be translated to;
* `messagePath`: path where to store message files, which should match the `i18n`'s `basePath` parameter stated in config.

You may also use './yii message/config' command to dynamically generate configuration file with specified options via cli.
For example, you can set `languages` and `messagePath` parameters like the following:

```shell
./yii message/config --languages=de,ja --messagePath=messages path/to/config.php
```

To get list of available options execute next command:

```shell
./yii help message/config
```

Once you're done with the config file you can finally extract your messages with the command:

```shell
./yii message path/to/config.php
```

Also, you may use options to dynamically change parameters for extraction.

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
