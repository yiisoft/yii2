Internationalization
====================

Internationalization (I18N) refers to the process of designing a software application so that it can be adapted to
various languages and regions without engineering changes. For Web applications, this is of particular importance
because the potential users may be worldwide. Yii offers a full spectrum of I18N features that support message
translation, view translation, date and number formatting.


## Locale and Language <span id="locale-language"></span>

### Locale

Locale is a set of parameters that defines the user's language, country and any special variant preferences
that the user wants to see in their user interface. It is usually identified by an ID consisting of a language
ID and a region ID. 

For example, the ID `en-US` stands for the locale of "English and the United States".

For consistency reasons, all locale IDs used in Yii applications should be canonicalized to the format of
`ll-CC`, where `ll` is a two- or three-letter lowercase language code according to
[ISO-639](http://www.loc.gov/standards/iso639-2/) and `CC` is a two-letter country code according to
[ISO-3166](https://en.wikipedia.org/wiki/ISO_3166-1#Current_codes).
More details about locale can be found in the
[documentation of the ICU project](http://userguide.icu-project.org/locale#TOC-The-Locale-Concept).

### Language

In Yii, we often use the term "language" to refer to a locale.

A Yii application uses two kinds of languages: 
* [[yii\base\Application::$sourceLanguage|source language]]: This refers to the language in which the text messages in the source code are written.
* [[yii\base\Application::$language|target language]]: This is the language that should be used to display content to end users.

The so-called message translation service mainly translates a text message from source language to target language.

### Configuration
You can configure application languages in the "application configuration" like the following:

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
US English. It is **recommended** that you keep this default value unchanged. Usually it is much easier
to find people who can translate from "English to other languages" than from "non-English to non-English".

You often need to set the [[yii\base\Application::$language|target language]] dynamically based on different 
factors, such as the language preference of end users. Instead of configuring it in the application configuration,
you can use the following statement to change the target language:

```php
// change target language to Chinese
\Yii::$app->language = 'zh-CN';
```

> Tip: If your source language varies among different parts of your code, you can
> override the source language for different message sources, which are described in the next section.

## Message Translation <span id="message-translation"></span>

### From source language to target language
The message translation service translates a text message from one language (usually the [[yii\base\Application::$sourceLanguage|source language]])
to another (usually the [[yii\base\Application::$language|target language]]). 

It does the translation by looking up the message to be translated in a message source which stores the original messages and the translated messages. If the message is found, the corresponding translated message will be returned; otherwise the original message will be 
returned untranslated.

### How to implement
To use the message translation service, you mainly need to do the following work:

1. Wrap every text message that needs to be translated in a call to the [[Yii::t()]] method.
2. Configure one or multiple message sources in which the message translation service can look for translated messages.
3. Let the translators translate messages and store them in the message source(s).


#### 1. Wrap a text message
The method [[Yii::t()]] can be used like the following,

```php
echo \Yii::t('app', 'This is a string to translate!');
```

where the second parameter refers to the text message to be translated, while the first parameter refers to 
the name of the category which is used to categorize the message. 

#### 2. Configure one or multiple message sources
The [[Yii::t()]] method will call the `i18n` [application component](structure-application-components.md) `translate`
method to perform the actual translation work. The component can be configured in the application configuration as follows,

```php
'components' => [
    // ...
    'i18n' => [
        'translations' => [
            'app*' => [
                '__class' => yii\i18n\PhpMessageSource::class,
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

In the above code, a message source supported by [[yii\i18n\PhpMessageSource]] is being configured. 

##### Category wildcards with `*` symbol

The pattern `app*` indicates that all message categories whose names start with `app` should be translated using this
message source. 

#### 3. Let the translators translate messages and store them in the message source(s)

The [[yii\i18n\PhpMessageSource]] class uses PHP files with a simple PHP array to store message translations. 
These files contain a map of the messages in `source language` to the translation in the `target language`.

> Info: You can automatically generate these PHP files by using the [`message` command](#message-command),
> which will be introduced later in this chapter.

Each PHP file corresponds to the messages of a single category. By default, the file name should be the same as
the category name. Example for `app/messages/nl-NL/main.php:`

```php
<?php

/**
* Translation map for nl-NL
*/
return [
    'welcome' => 'welkom'
];

```


##### File mapping

You may configure [[yii\i18n\PhpMessageSource::fileMap|fileMap]] to map a category to a PHP file with a different naming approach. 

In the above example, the category `app/error` is mapped to the PHP file `@app/messages/ru-RU/error.php` 
(assuming `ru-RU` is the target language). 
However, without this configuration the category would be mapped to `@app/messages/ru-RU/app/error.php` instead.

#####  Other storage types

Besides storing the messages in PHP files, you may also use the following message sources to store translated messages
in different storage:

- [[yii\i18n\GettextMessageSource]] uses GNU Gettext MO or PO files to maintain translated messages.
- [[yii\i18n\DbMessageSource]] uses a database table to store translated messages.


## Message Formatting <span id="message-formatting"></span>

When translating a message, you can embed some placeholders and have them replaced by dynamic parameter values.
You can even use special placeholder syntax to have the parameter values formatted according to the target language.
In this subsection, we will describe different ways of formatting messages.

### Message Parameters <span id="message-parameters"></span>

In a message to be translated, you can embed one or multiple parameters (also called placeholders) so that they can be
replaced by the given values. By giving different sets of values, you can variate the translated message dynamically.
In the following example, the placeholder `{username}` in the message `'Hello, {username}!'` will be replaced
by `'Alexander'` and `'Qiang'`, respectively.

```php
$username = 'Alexander';
// display a translated message with username being "Alexander"
echo \Yii::t('app', 'Hello, {username}!', [
    'username' => $username,
]);

$username = 'Qiang';
// display a translated message with username being "Qiang"
echo \Yii::t('app', 'Hello, {username}!', [
    'username' => $username,
]);
```

While translating a message containing placeholders, you should leave the placeholders as is. This is because the placeholders
will be replaced with the actual values when you call `Yii::t()` to translate a message.

The previous example shows how you can use named placeholders. That is, each placeholder is written in the format of 
`{name}`, and you provide an associative array whose keys are the placeholder names
(without the curly brackets) and whose values are the corresponding values placeholder to be replaced with.

> Note: Some characters such as `.`, `-` or `=` are not allowed in placeholder names. Use `_` instead.

Positional placeholders use zero-based integer sequence as names which are replaced by the provided values
according to their positions in the call of `Yii::t()`. In the following example, the positional placeholders
`{0}`, `{1}` and `{2}` will be replaced by the values of `$price`, `$count` and `$subtotal`, respectively.

```php
$price = 100;
$count = 2;
$subtotal = 200;
echo \Yii::t('app', 'Price: {0}, Count: {1}, Subtotal: {2}', [$price, $count, $subtotal]);
```

In case of a single positional parameter its value could be specified without wrapping it into array:

```php
echo \Yii::t('app', 'Price: {0}', $price);
```

> Tip: In most cases you should use named placeholders. This is because the names will make the translators
> understand better the whole messages being translated.


### Parameter Formatting <span id="parameter-formatting"></span>

You can specify additional formatting rules in the placeholders of a message so that the parameter values can be 
formatted properly before they replace the placeholders. In the following example, the price parameter value will be
treated as a number and formatted as a currency value:

```php
$price = 100;
echo \Yii::t('app', 'Price: {0,number,currency}', $price);
```

> Note: Parameter formatting requires the installation of the [intl PHP extension](http://www.php.net/manual/en/intro.intl.php).

You can use either the short form or the full form to specify a placeholder with formatting:

```
short form: {name,type}
full form: {name,type,style}
```

> Note: If you need to use special characters such as `{`, `}`, `'`, `#`, wrap them in `'`:
> 
```php
echo Yii::t('app', "Example of string with ''-escaped characters'': '{' '}' '{test}' {count,plural,other{''count'' value is # '#{}'}}", ['count' => 3]);
```

Complete format is described in the [ICU documentation](http://icu-project.org/apiref/icu4c/classMessageFormat.html).
In the following we will show some common usages.


#### Number <span id="number"></span>

The parameter value is treated as a number. For example,

```php
$sum = 42;
echo \Yii::t('app', 'Balance: {0,number}', $sum);
```

You can specify an optional parameter style as `integer`, `currency`, or `percent`:

```php
$sum = 42;
echo \Yii::t('app', 'Balance: {0,number,currency}', $sum);
```

You can also specify a custom pattern to format the number. For example,

```php
$sum = 42;
echo \Yii::t('app', 'Balance: {0,number,,000,000000}', $sum);
```

Characters used in the custom format could be found in
[ICU API reference](http://icu-project.org/apiref/icu4c/classicu_1_1DecimalFormat.html) under "Special Pattern Characters"
section.
 
 
The value is always formatted according to the locale you are translating to i.e. you cannot change decimal or thousands
separators, currency symbol etc. without changing translation locale. If you need to customize these you can
use [[yii\i18n\Formatter::asDecimal()]] and [[yii\i18n\Formatter::asCurrency()]].

#### Date <span id="date"></span>

The parameter value should be formatted as a date. For example,

```php
echo \Yii::t('app', 'Today is {0,date}', time());
```

You can specify an optional parameter style as `short`, `medium`, `long`, or `full`:

```php
echo \Yii::t('app', 'Today is {0,date,short}', time());
```

You can also specify a custom pattern to format the date value:

```php
echo \Yii::t('app', 'Today is {0,date,yyyy-MM-dd}', time());
```

[Formatting reference](http://icu-project.org/apiref/icu4c/classicu_1_1SimpleDateFormat.html#details).


#### Time <span id="time"></span>

The parameter value should be formatted as a time. For example,

```php
echo \Yii::t('app', 'It is {0,time}', time());
```

You can specify an optional parameter style as `short`, `medium`, `long`, or `full`:

```php
echo \Yii::t('app', 'It is {0,time,short}', time());
```

You can also specify a custom pattern to format the time value:

```php
echo \Yii::t('app', 'It is {0,date,HH:mm}', time());
```

[Formatting reference](http://icu-project.org/apiref/icu4c/classicu_1_1SimpleDateFormat.html#details).


#### Spellout <span id="spellout"></span>

The parameter value should be treated as a number and formatted as a spellout. For example,

```php
// may produce "42 is spelled as forty-two"
echo \Yii::t('app', '{n,number} is spelled as {n,spellout}', ['n' => 42]);
```

By default the number is spelled out as cardinal. It could be changed:

```php
// may produce "I am forty-seventh agent"
echo \Yii::t('app', 'I am {n,spellout,%spellout-ordinal} agent', ['n' => 47]);
```

Note that there should be no space after `spellout,` and before `%`.

To get a list of options available for locale you're using check 
"Numbering schemas, Spellout" at [http://intl.rmcreative.ru/](http://intl.rmcreative.ru/).

#### Ordinal <span id="ordinal"></span>

The parameter value should be treated as a number and formatted as an ordinal name. For example,

```php
// may produce "You are the 42nd visitor here!"
echo \Yii::t('app', 'You are the {n,ordinal} visitor here!', ['n' => 42]);
```

Ordinal supports more ways of formatting for languages such as Spanish:

```php
// may produce 471ª
echo \Yii::t('app', '{n,ordinal,%digits-ordinal-feminine}', ['n' => 471]);
```

Note that there should be no space after `ordinal,` and before `%`.

To get a list of options available for locale you're using check 
"Numbering schemas, Ordinal" at [http://intl.rmcreative.ru/](http://intl.rmcreative.ru/).

#### Duration <span id="duration"></span>

The parameter value should be treated as the number of seconds and formatted as a time duration string. For example,

```php
// may produce "You are here for 47 sec. already!"
echo \Yii::t('app', 'You are here for {n,duration} already!', ['n' => 47]);
```

Duration supports more ways of formatting:

```php
// may produce 130:53:47
echo \Yii::t('app', '{n,duration,%in-numerals}', ['n' => 471227]);
```

Note that there should be no space after `duration,` and before `%`.

To get a list of options available for locale you're using check 
"Numbering schemas, Duration" at [http://intl.rmcreative.ru/](http://intl.rmcreative.ru/).

#### Plural <span id="plural"></span>

Different languages have different ways to inflect plurals. Yii provides a convenient way for translating messages in
different plural forms that works well even for very complex rules. Instead of dealing with the inflection rules directly,
it is sufficient to provide the translation of inflected words in certain situations only. For example,

```php
// When $n = 0, it may produce "There are no cats!"
// When $n = 1, it may produce "There is one cat!"
// When $n = 42, it may produce "There are 42 cats!"
echo \Yii::t('app', 'There {n,plural,=0{are no cats} =1{is one cat} other{are # cats}}!', ['n' => $n]);
```

In the plural rule arguments above, `=` means explicit value. So `=0` means exactly zero, `=1` means exactly one.
`other` stands for any other value. `#` is replaced with the value of `n` formatted according to target language.

Plural forms can be very complicated in some languages. In the following Russian example, `=1` matches exactly `n = 1` 
while `one` matches `21` or `101`:

```
Здесь {n,plural,=0{котов нет} =1{есть один кот} one{# кот} few{# кота} many{# котов} other{# кота}}!
```

These `other`, `few`, `many` and other special argument names vary depending on language. To learn which ones you should
specify for a particular locale, please refer to "Plural Rules, Cardinal" at [http://intl.rmcreative.ru/](http://intl.rmcreative.ru/). 
Alternatively you can refer to [rules reference at unicode.org](http://cldr.unicode.org/index/cldr-spec/plural-rules).

> Note: The above example Russian message is mainly used as a translated message, not an original message, unless you set
> the [[yii\base\Application::$sourceLanguage|source language]] of your application as `ru-RU` and translating from Russian.
>
> When a translation is not found for an original message specified in `Yii::t()` call, the plural rules for the
> [[yii\base\Application::$sourceLanguage|source language]] will be applied to the original message.

There's an `offset` parameter for the cases when the string is like the following:
 
```php
$likeCount = 2;
echo Yii::t('app', 'You {likeCount,plural,
    offset: 1
    =0{did not like this}
    =1{liked this}
    one{and one other person liked this}
    other{and # others liked this}
}', [
    'likeCount' => $likeCount
]);

// You and one other person liked this
```

#### Ordinal selection <span id="ordinal-selection">

The parameter type of `selectordinal` is meant to choose a string based on language rules for ordinals for the
locale you are translating to:

```php
$n = 3;
echo Yii::t('app', 'You are the {n,selectordinal,one{#st} two{#nd} few{#rd} other{#th}} visitor', ['n' => $n]);
// For English it outputs:
// You are the 3rd visitor

// Translation
'You are the {n,selectordinal,one{#st} two{#nd} few{#rd} other{#th}} visitor' => 'Вы {n,selectordinal,other{#-й}} посетитель',

// For Russian translation it outputs:
// Вы 3-й посетитель
```

The format is very close to what's used for plurals. To learn which arguments you should specify for a particular locale,
please refer to "Plural Rules, Ordinal" at [http://intl.rmcreative.ru/](http://intl.rmcreative.ru/). 
Alternatively you can refer to [rules reference at unicode.org](http://unicode.org/repos/cldr-tmp/trunk/diff/supplemental/language_plural_rules.html).

#### Selection <span id="selection"></span>

You can use the `select` parameter type to choose a phrase based on the parameter value. For example, 

```php
// It may produce "Snoopy is a dog and it loves Yii!"
echo \Yii::t('app', '{name} is a {gender} and {gender,select,female{she} male{he} other{it}} loves Yii!', [
    'name' => 'Snoopy',
    'gender' => 'dog',
]);
```

In the expression above, both `female` and `male` are possible parameter values, while `other` handles values that 
do not match either one of them. Following each possible parameter value, you should specify a phrase and enclose
it in a pair of curly brackets.


### Specifying default message source <span id="default-message-source"></span>

You can specify default message source that will be used as a fallback for category that doesn't match any
configured category. You can do that by configuring a wildcard category `*`. In order to do that, add the following
to the application config:

```php
//configure i18n component

'i18n' => [
    'translations' => [
        '*' => [
            '__class' => yii\i18n\PhpMessageSource::class
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

### Translating module messages <span id="module-translation"></span>

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
            '__class' => \yii\i18n\PhpMessageSource::class,
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

### Translating widgets messages <span id="widget-translation"></span>

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
            '__class' => \yii\i18n\PhpMessageSource::class,
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

> Note: For widgets you also can use i18n views, with the same rules as for controllers being applied to them too.


### Translating framework messages <span id="framework-translation"></span>

Yii comes with the default translation messages for validation errors and some other strings. These messages are all
in the category `yii`. Sometimes you want to correct the default framework message translation for your application.
In order to do so, configure the `i18n` [application component](structure-application-components.md) like the following:

```php
'i18n' => [
    'translations' => [
        'yii' => [
            '__class' => yii\i18n\PhpMessageSource::class,
            'sourceLanguage' => 'en-US',
            'basePath' => '@app/messages'
        ],
    ],
],
```

Now you can place your adjusted translations to `@app/messages/<language>/yii.php`.

### Handling missing translations <span id="missing-translations"></span>

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
                '__class' => yii\i18n\PhpMessageSource::class,
                'fileMap' => [
                    'app' => 'app.php',
                    'app/error' => 'error.php',
                ],
                'on missingTranslation' => [app\components\TranslationEventHandler::class, 'handleMissingTranslation']
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

Translations can be stored in [[yii\i18n\PhpMessageSource|php files]], [[yii\i18n\GettextMessageSource|.po files]] or in a [[yii\i18n\DbMessageSource|database]]. See specific classes for additional options.

First of all you need to create a configuration file. Decide where you want to store it and then issue the command 

```bash
./yii message/config-template path/to/config.php
```

Open the created file and adjust the parameters to fit your needs. Pay special attention to:

* `languages`: an array representing what languages your app should be translated to;
* `messagePath`: path where to store message files, which should match the `i18n`'s `basePath` parameter stated in config.

You may also use './yii message/config' command to dynamically generate configuration file with specified options via cli.
For example, you can set `languages` and `messagePath` parameters like the following:

```bash
./yii message/config --languages=de,ja --messagePath=messages path/to/config.php
```

To get list of available options execute next command:

```bash
./yii help message/config
```

Once you're done with the configuration file you can finally extract your messages with the command:

```bash
./yii message path/to/config.php
```

Also, you may use options to dynamically change parameters for extraction.

You will then find your files (if you've chosen file based translations) in your `messagePath` directory.


## View Translation <span id="view-translation"></span>

Instead of translating individual text messages, sometimes you may want to translate a whole view script.
To achieve this goal, simply translate the view and save it under a subdirectory whose name is the same as 
target language. For example, if you want to translate the view script `views/site/index.php` and the target
language is `ru-RU`, you may translate the view and save it as  the file `views/site/ru-RU/index.php`. Now
whenever you call [[yii\base\View::renderFile()]] or any method that invoke this method (e.g. [[yii\base\Controller::render()]])
to render the view `views/site/index.php`, it will end up rendering the translated view `views/site/ru-RU/index.php`, instead. 

> Note: If the [[yii\base\Application::$language|target language]] is the same as [[yii\base\Application::$sourceLanguage|source language]]
> original view will be rendered regardless of presence of translated view.


## Formatting Date and Number Values <span id="date-number"></span>

See the [Data Formatting](output-formatting.md) section for details.


## Setting Up PHP Environment <span id="setup-environment"></span>

Yii uses the [PHP intl extension](http://php.net/manual/en/book.intl.php) to provide most of its I18N features,
such as the date and number formatting of the [[yii\i18n\Formatter]] class and the message formatting using [[yii\i18n\MessageFormatter]].
Both classes provide a fallback mechanism when the `intl` extension is not installed. However, the fallback implementation
only works well for English target language. So it is highly recommended that you install `intl` when I18N is needed.

The [PHP intl extension](http://php.net/manual/en/book.intl.php) is based on the [ICU library](http://site.icu-project.org/) which
provides the knowledge and formatting rules for all different locales. Different versions of ICU may produce different
formatting result of date and number values. To ensure your website produces the same results across all environments,
it is recommended that you install the same version of the `intl` extension (and thus the same version of ICU)
in all environments.

To find out which version of ICU is used by PHP, you can run the following script, which will give you the PHP and ICU version being used.

```php
<?php
echo "PHP: " . PHP_VERSION . "\n";
echo "ICU: " . INTL_ICU_VERSION . "\n";
echo "ICU Data: " . INTL_ICU_DATA_VERSION . "\n";
```

It is also recommended that you use an ICU version equal or greater than version 49. This will ensure you can use all the features
described in this document. For example, an ICU version below 49 does not support using `#` placeholders in plural rules.
Please refer to <http://site.icu-project.org/download> for a complete list of available ICU versions. Note that the version 
numbering has changed after the 4.8 release (e.g., ICU 4.8, ICU 49, ICU 50, etc.)

Additionally the information in the time zone database shipped with the ICU library may be outdated. Please refer
to the [ICU manual](http://userguide.icu-project.org/datetime/timezone#TOC-Updating-the-Time-Zone-Data) for details
on updating the time zone database. While for output formatting the ICU timezone database is used, the time zone database
used by PHP may be relevant too. You can update it by installing the latest version of the [pecl package `timezonedb`](http://pecl.php.net/package/timezonedb).
