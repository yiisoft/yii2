Internationalization
====================

> Note: This section is under development.

Internationalization (I18N) refers to the process of designing a software application so that it can be adapted to
various languages and regions without engineering changes. For Web applications, this is of particular importance
because the potential users may be worldwide.

Locale and Language
-------------------

There are two languages defined in Yii application: [[yii\base\Application::$sourceLanguage|source language]] and
[[yii\base\Application::$language|target language]].

Source language is the language original application messages are written in such as:

```php
echo \Yii::t('app', 'I am a message!');
```

> **Tip**: Default is English and it's not recommended to change it. The reason is that it's easier to find people translating from
> English to any language than from non-English to non-English.

Target language is what's currently used. It's defined in application configuration like the following:

```php
// ...
return [
    'id' => 'applicationID',
    'basePath' => dirname(__DIR__),
    'language' => 'ru-RU' // <- here!
    // ...
]
```

Later you can easily change it in runtime:

```php
\Yii::$app->language = 'zh-CN';
```

Format is `ll-CC` where `ll` is  two- or three-letter lowercase code for a language according to
[ISO-639](http://www.loc.gov/standards/iso639-2/) and `CC` is country code according to
[ISO-3166](http://www.iso.org/iso/en/prods-services/iso3166ma/02iso-3166-code-lists/list-en1.html).

If there's no translation for `ru-RU` Yii will try `ru` as well before failing.

> **Note**: you can further customize details specifying language
> [as documented in the ICU project](http://userguide.icu-project.org/locale#TOC-The-Locale-Concept).

Message translation
-------------------

### Basics

Yii basic message translation in its basic variant works without additional PHP extension. What it does is finding a
translation of the message from source language into target language. Message itself is specified as the second
`\Yii::t` method parameter:

```php
echo \Yii::t('app', 'This is a string to translate!');
```

Yii tries to load appropriate translation from one of the message sources defined via `i18n` component configuration:

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
handling everything that begins with `app`. You can also specify default translation, for more info see [this example](i18n.md#examples).

`class` defines which message source is used. The following message sources are available:

- PhpMessageSource that uses PHP files.
- GettextMessageSource that uses GNU Gettext MO or PO files.
- DbMessageSource that uses database.

`basePath` defines where to store messages for the currently used message source. In this case it's `messages` directory
 in your application directory. In case of using database this option should be skipped.

`sourceLanguage` defines which language is used in `\Yii::t` second argument. If not specified, application's source
language is used.

`fileMap` specifies how message categories specified in the first argument of `\Yii::t()` are mapped to files when
`PhpMessageSource` is used. In the example we're defining two categories `app` and `app/error`.

Instead of configuring `fileMap` you can rely on convention which is `BasePath/messages/LanguageID/CategoryName.php`.

#### Named placeholders

You can add parameters to a translation message that will be substituted with the corresponding value after translation.
The format for this is to use curly brackets around the parameter name as you can see in the following example:

```php
$username = 'Alexander';
echo \Yii::t('app', 'Hello, {username}!', [
    'username' => $username,
]);
```

Note that the parameter assignment is without the brackets.

#### Positional placeholders

```php
$sum = 42;
echo \Yii::t('app', 'Balance: {0}', $sum);
```

> **Tip**: Try keep message strings meaningful and avoid using too many positional parameters. Remember that
> translator has source string only so it should be obvious about what will replace each placeholder.

### Advanced placeholder formatting


In order to use advanced features you need to install and enable [intl](http://www.php.net/manual/en/intro.intl.php) PHP
extension. After installing and enabling it you will be able to use extended syntax for placeholders. Either short form
`{placeholderName, argumentType}` that means default setting or full form `{placeholderName, argumentType, argumentStyle}`
that allows you to specify formatting style.

Full reference is [available at ICU website](http://icu-project.org/apiref/icu4c/classMessageFormat.html) but since it's
a bit cryptic we have our own reference below.

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

Or specify custom pattern:

```php
$sum = 42;
echo \Yii::t('app', 'Balance: {0, number, ,000,000000}', $sum);
```

[Formatting reference](http://icu-project.org/apiref/icu4c/classicu_1_1DecimalFormat.html).

#### Dates

```php
echo \Yii::t('app', 'Today is {0, date}', time());
```

Built in formats (`short`, `medium`, `long`, `full`):

```php
echo \Yii::t('app', 'Today is {0, date, short}', time());
```

Custom pattern:

```php
echo \Yii::t('app', 'Today is {0, date, YYYY-MM-dd}', time());
```

[Formatting reference](http://icu-project.org/apiref/icu4c/classicu_1_1SimpleDateFormat.html).

#### Time

```php
echo \Yii::t('app', 'It is {0, time}', time());
```

Built in formats (`short`, `medium`, `long`, `full`):

```php
echo \Yii::t('app', 'It is {0, time, short}', time());
```

Custom pattern:

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

Different languages have different ways to inflect plurals. Some rules are very complex so it's very handy that this
functionality is provided without the need to specify inflection rule. Instead it only requires your input of inflected
word in certain situations.

```php
echo \Yii::t('app', 'There {n, plural, =0{are no cats} =1{is one cat} other{are # cats}}!', ['n' => 0]);
```

Will give us "There are no cats!".

In the plural rule arguments above `=0` means exactly zero, `=1` stands for exactly one `other` is for any other number.
`#` is replaced with the `n` argument value. It's not that simple for languages other than English. Here's an example
for Russian:

```
Здесь {n, plural, =0{котов нет} =1{есть один кот} one{# кот} few{# кота} many{# котов} other{# кота}}!
```

In the above it worth mentioning that `=1` matches exactly `n = 1` while `one` matches `21` or `101`.

Note that if you are using placeholder twice and one time it's used as plural another one should be used as number else
you'll get "Inconsistent types declared for an argument: U_ARGUMENT_TYPE_MISMATCH" error:

```
Total {count, number} {count, plural, one{item} other{items}}.
```

To learn which inflection forms you should specify for your language you can referrer to
[rules reference at unicode.org](http://unicode.org/repos/cldr-tmp/trunk/diff/supplemental/language_plural_rules.html).

#### Selections

You can select phrases based on keywords. The pattern in this case specifies how to map keywords to phrases and
provides a default phrase.

```php
echo \Yii::t('app', '{name} is {gender} and {gender, select, female{she} male{he} other{it}} loves Yii!', [
    'name' => 'Snoopy',
    'gender' => 'dog',
]);
```

Will produce "Snoopy is dog and it loves Yii!".

In the expression `female` and `male` are possible values. `other` handler values that do not match. Strings inside
brackets are sub-expressions so could be just a string or a string with more placeholders.

### Specifying default translation

You can specify default translation that will be used as a fallback for categories that don't match any other translation.
This translation should be marked with `*`. In order to do it add the following to the config file (for the `yii2-basic` application it will be `web.php`):

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

Now you can use categories without configuring each one that is similar to Yii 1.1 behavior.
Messages for the category will be loaded from a file under default translation `basePath` that is `@app/messages`:

```php
echo Yii::t('not_specified_category', 'message from unspecified category');
```

Message will be loaded from `@app/messages/<LanguageCode>/not_specified_category.php`.

### Translating module messages

If you want to translate messages for a module and avoid using a single translation file for all messages, you can make it like the following:

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

In the example above we are using wildcard for matching and then filtering each category per needed file. Instead of using `fileMap` you can simply
use convention of category mapping to the same named file and use `Module::t('validation', 'your custom validation message')` or `Module::t('form', 'some form label')` directly.

### Translating widgets messages

Same rules can be applied for widgets too, for example:

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

Instead of using `fileMap` you can simply use convention of category mapping to the same named file and use `Menu::t('messages', 'new messages {messages}', ['{messages}' => 10])` directly.

> **Note**: For widgets you also can use i18n views, same rules as for controllers are applied to them too.


### Translating framework messages

Sometimes you want to correct default framework message translation for your application. In order to do so configure
`i18n` component like the following:

```php
'components' => [
    'i18n' => [
        'translations' => [
            'yii' => [
                'class' => 'yii\i18n\PhpMessageSource',
                'sourceLanguage' => 'en-US',
                'basePath' => '/path/to/my/message/files'
            ],
        ],
    ],
],
```

Now you can place your adjusted translations to `/path/to/my/message/files`.

### Handling missing translations

If the translation is missing at the source, Yii displays the requested message content. Such behavior very convenient
in case your raw message is a valid verbose text. However, sometimes it is not enough.
You may need to perform some custom processing of the situation, when requested translation is missing at the source.
This can be achieved via 'missingTranslation' event of the [[yii\i18n\MessageSource]].

For example: lets mark all missing translations with something notable, so they can be easily found at the page.
First we need to setup event handler, this can be done via configuration:

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
                'on missingTranslation' => ['TranslationEventHandler', 'handleMissingTranslation']
            ],
        ],
    ],
],
```

Now we need to implement own handler:

```php
use yii\i18n\MissingTranslationEvent;

class TranslationEventHandler
{
    public static function(MissingTranslationEvent $event) {
        $event->translatedMessage = "@MISSING: {$event->category}.{$event->message} FOR LANGUAGE {$event->language} @";
    }
}
```

If [[yii\i18n\MissingTranslationEvent::translatedMessage]] is set by event handler it will be displayed as translation result.

> Attention: each message source handles its missing translations separately. If you are using several message sources
  and wish them treat missing translation in the same way, you should assign corresponding event handler to each of them.

Views
-----

You can use i18n in your views to provide support for different languages. For example, if you have view `views/site/index.php` and
you want to create special case for russian language, you create `ru-RU` folder under the view path of current controller/widget and
put there file for russian language as follows `views/site/ru-RU/index.php`.

> **Note**: If language is specified as `en-US` and there are no corresponding views, Yii will try views under `en`
> before using original ones.

i18n formatter
--------------

i18n formatter component is the localized version of formatter that supports formatting of date, time and numbers based
on current locale. In order to use it you need to configure formatter application component as follows:

```php
return [
    // ...
    'components' => [
        'formatter' => [
            'class' => 'yii\i18n\Formatter',
        ],
    ],
];
```

After configuring the component can be accessed as `Yii::$app->formatter`.

Note that in order to use i18n formatter you need to install and enable
[intl](http://www.php.net/manual/en/intro.intl.php) PHP extension.

In order to learn about formatter methods refer to its API documentation: [[yii\i18n\Formatter]].
