国际化
=======

国际化（I18N）是指在设计软件时，使它可以无需做大的改变就能够适应不同的语言和地区的需要。对于 Web 应用程序，
这有着特别重要的意义，因为潜在的用户可能会在全球范围内。
Yii 提供的国际化功能支持全方位信息翻译，视图翻译，日期和数字格式化。


## 区域和语言 <span id="locale-language"></span>

区域设置是一组参数以定义用户希望能在他们的用户界面所看到用户的语言，国家和任何特殊的偏好。
它通常是由语言 ID 和区域 ID 组成。例如，ID “en-US” 代表英语和美国的语言环境。为了保持一致性，
在 Yii 应用程序中使用的所有区域 ID 应该规范化为 `ll-CC`，其中 `ll` 是根据两个或三个字母的小写字母语言代码
[ISO-639](http://www.loc.gov/standards/iso639-2/) 和 `CC` 是两个字母的国别代码
[ISO-3166](http://www.iso.org/iso/en/prods-services/iso3166ma/02iso-3166-code-lists/list-en1.html)。
有关区域设置的更多细节可以看
[ICU 项目文档](http://userguide.icu-project.org/locale#TOC-The-Locale-Concept)。

在 Yii中，我们经常用 “language” 来代表一个区域。

一个 Yii 应用使用两种语言：[[yii\base\Application::$sourceLanguage|source language]] 和
[[yii\base\Application::$language|target language]] 。前者指的是写在代码中的语言，后者是向最终用户显示内容的语言。
而信息翻译服务主要是将文本消息从原语言翻译到目标语言。

可以用类似下面的应用程序配置来配置应用程序语言：

```php
return [
    // set target language to be Russian
    'language' => 'ru-RU',
    
    // set source language to be English
    'sourceLanguage' => 'en-US',
    
    ......
];
```

默认的 [[yii\base\Application::$sourceLanguage|source language]] 值是 `en-US`，即美国英语。
建议你保留此默认值不变，因为通常让人将英语翻译成其它语言要比将其它语言翻译成其它语言容易得多。

你经常需要根据不同的因素来动态地设置 [[yii\base\Application::$language|target language]] ，如最终用户的语言首选项。
要在应用程序配置中配置它，你可以使用下面的语句来更改目标语言：

```php
// change target language to Chinese
\Yii::$app->language = 'zh-CN';
```

## 消息翻译 <span id="message-translation"></span>

消息翻译服务用于将一条文本信息从一种语言（通常是 [[yii\base\Application::$sourceLanguage|source language]] ）
翻译成另一种语言（通常是 [[yii\base\Application::$language|target language]]）。
它的翻译原理是通过在语言文件中查找要翻译的信息以及翻译的结果。如果要翻译的信息可以在语言文件中找到，会返回相应的翻译结果；
否则会返回原始未翻译的信息。

为了使用消息翻译服务，需要做如下工作：

* 调用 [[Yii::t()]] 方法且在其中包含每一条要翻译的消息；
* 配置一个或多个消息来源，能在其中找得到要翻译的消息和翻译结果；
* 让译者翻译信息并将它们存储在消息来源。

这个 [[Yii::t()]] 方法的用法如下，

```php
echo \Yii::t('app', 'This is a string to translate!');
```

第一个参数指储存消息来源的类别名称，第二个参数指需要被翻译的消息。

这个 [[Yii::t()]] 方法会调用 `i18n` [应用组件](structure-application-components.md) 
来实现翻译工作。这个组件可以在应用程序中按下面的代码来配置，

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

在上面的代码中，配置了由 [[yii\i18n\PhpMessageSource]] 所支持的消息来源。模式 `app*` 表示所有以 `app`
开头的消息类别名称都使用这个翻译的消息来源。该 [[yii\i18n\PhpMessageSource]] 类使用 PHP 文件来存储消息翻译。
每 PHP 文件对应单一类别的消息。默认情况下，文件名应该与类别名称相同。但是，你可以配置
[[yii\i18n\PhpMessageSource::fileMap|fileMap]] 来映射一个类别到不同名称的 PHP 文件。在上面的例子中，
类别 `app/error` 被映射到PHP文件 `@app/messages/ru-RU/error.php`（假设 `ru-RU` 为目标语言）。如果没有此配置，
该类别将被映射到 `@app/messages/ru-RU/app/error.php` 。

除了在PHP文件中存储消息来源，也可以使用下面的消息来源在不同的存储来存储翻译的消息：

- [[yii\i18n\GettextMessageSource]] 使用 GNU Gettext 的 MO 或 PO 文件保存翻译的消息。
- [[yii\i18n\DbMessageSource]] 使用一个数据库表来存储翻译的消息。


## 消息格式化 <span id="message-formatting"></span>

在要翻译的消息里，你可以嵌入一些占位符，并让它们通过动态的参数值来代替。你甚至可以根据目标语言格式的参数值来使用特殊的占位符。
在本节中，我们将介绍如何用不同的方式来格式化消息。

### 消息参数 <span id="message-parameters"></span>

在待翻译的消息，可以嵌入一个或多个占位符，以便它们可以由给定的参数值取代。通过给不同的参数值，可以动态地改变翻译内容的消息。
在下面的例子中，占位符 `{username}` 在 `“Hello, {username}！”` 中将分别被 `'Alexander'`和`'Qiang'` 所替换。

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

当翻译的消息包含占位符时，应该让占位符保留原样。这是因为调用 `Yii::t()` 时，占位符将被实际参数值代替。

你可以使用 *名称占位符* 或者 *位置占位符*，但不能两者都用在同一个消息里。

前面的例子说明了如何使用名称占位符。即每个占位符的格式为 `{参数名称}` ，你所提供的参数作为关联数组，
其中数组的键是参数名称（没有大括号），数组的值是对应的参数值。

位置占位符是使用基于零的整数序列，在调用 `Yii::t()` 时会参数值根据它们出现位置的顺序分别进行替换。
在下面的例子中，位置占位符 `{0}`，`{1}` 和 `{2}` 将分别被 `$price`，`$count` 和 `$subtotal` 所替换。

```php
$price = 100;
$count = 2;
$subtotal = 200;
echo \Yii::t('app', 'Price: {0}, Count: {1}, Subtotal: {2}', $price, $count, $subtotal);
```

> 提示：大多数情况下你应该使用名称占位符。这是因为参数名称可以让翻译者更好的理解要被翻译的消息。


### 格式化参数 <span id="parameter-formatting"></span>

你可以在消息的占位符指定附加格式的规则，这样的参数值可在替换占位符之前格式化它们。在下面的例子中，
价格参数值将视为一个数并格式化为货币值：

```php
$price = 100;
echo \Yii::t('app', 'Price: {0, number, currency}', $price);
```

> 注意：参数的格式化需要安装 [intl PHP 扩展](http://www.php.net/manual/en/intro.intl.php)。

可以使用缩写的形式或完整的形式来格式化占位符：

```
short form: {PlaceholderName, ParameterType}
full form: {PlaceholderName, ParameterType, ParameterStyle}
```

请参阅 [ICU 文档](http://icu-project.org/apiref/icu4c/classMessageFormat.html)
关于如何指定这样的占位符的说明。

接下来我们会展示一些常用的使用方法。


#### 数字 <span id="number"></span>

参数值应该被格式化为一个数。例如，

```php
$sum = 42;
echo \Yii::t('app', 'Balance: {0, number}', $sum);
```

你可以指定参数的格式为 `integer`（整型），`currency` （货币），或者 `percent` （百分数）：

```php
$sum = 42;
echo \Yii::t('app', 'Balance: {0, number, currency}', $sum);
```

你也可以指定一个自定义模式来格式化数字。 例如，

```php
$sum = 42;
echo \Yii::t('app', 'Balance: {0, number, ,000,000000}', $sum);
```

[格式化参考](http://icu-project.org/apiref/icu4c/classicu_1_1DecimalFormat.html)。


#### 日期 <span id="date"></span>

该参数值应该被格式化为一个日期。 例如，

```php
echo \Yii::t('app', 'Today is {0, date}', time());
```

你可以指定一个可选的参数格式 `short` ，`medium` ，`long` ，或 `full` ：

```php
echo \Yii::t('app', 'Today is {0, date, short}', time());
```

你还可以指定一个自定义模式来格式化日期：

```php
echo \Yii::t('app', 'Today is {0, date, yyyy-MM-dd}', time());
```

[格式化参考](http://icu-project.org/apiref/icu4c/classicu_1_1SimpleDateFormat.html)。


#### 时间 <span id="time"></span>

参数值应该被格式化为一个时间。 例如，

```php
echo \Yii::t('app', 'It is {0, time}', time());
```

你可以指定一个可选的参数格式 `short` ，`medium` ，`long` ，或 `full` ：

```php
echo \Yii::t('app', 'It is {0, time, short}', time());
```

你还可以指定一个自定义模式来格式化时间：

```php
echo \Yii::t('app', 'It is {0, date, HH:mm}', time());
```

[格式化参考](http://icu-project.org/apiref/icu4c/classicu_1_1SimpleDateFormat.html)。


#### 拼写 <span id="spellout"></span>

参数值为一个数并被格式化为它的字母拼写形式。 例如，

```php
// may produce "42 is spelled as forty-two"
echo \Yii::t('app', '{n,number} is spelled as {n, spellout}', ['n' => 42]);
```

#### 序数词 <span id="ordinal"></span>

参数值为一个数并被格式化为一个序数词。 例如，

```php
// may produce "You are the 42nd visitor here!"
echo \Yii::t('app', 'You are the {n, ordinal} visitor here!', ['n' => 42]);
```


#### 持续时间 <span id="duration"></span>

参数值为秒数并被格式化为持续的时间段。 例如，

```php
// may produce "You are here for 47 sec. already!"
echo \Yii::t('app', 'You are here for {n, duration} already!', ['n' => 47]);
```


#### 复数 <span id="plural"></span>

不同的语言有不同的方式来表示复数。 Yii 提供一个便捷的途径，即使是非常复杂的规则也使翻译消息时不同的复数形式行之有效。
取之以直接处理词形变化规则，它是足以面对某些词形变化语言的翻译。 例如，

```php
// When $n = 0, it may produce "There are no cats!"
// When $n = 1, it may produce "There is one cat!"
// When $n = 42, it may produce "There are 42 cats!"
echo \Yii::t('app', 'There {n, plural, =0{are no cats} =1{is one cat} other{are # cats}}!', ['n' => $n]);
```

在上面的多个规则的参数中， `=0` 意味着 `n` 的值是 0 ，`=1` 意味着 `n` 的值是 1 ， 而 `other` 则是对于其它值，
`#` 会被 `n` 中的值给替代。 

复数形式可以是某些非常复杂的语言。下面以俄罗斯为例，`=1` 完全匹配 `n = 1`，而 `one` 匹配 `21` 或 `101`：

```
Здесь {n, plural, =0{котов нет} =1{есть один кот} one{# кот} few{# кота} many{# котов} other{# кота}}!
```

注意，上述信息主要是作为一个翻译的信息，而不是一个原始消息，除非设置应用程序的
[[yii\base\Application::$sourceLanguage|source language]] 为 `ru-RU`。

如果没有找到一个翻译的原始消息，复数规则 [[yii\base\Application::$sourceLanguage|source language]] 将被应用到原始消息。

要了解词形变化形式，你应该指定一个特定的语言，请参考
[rules reference at unicode.org](http://unicode.org/repos/cldr-tmp/trunk/diff/supplemental/language_plural_rules.html)。


#### 选择 <span id="selection"></span>

可以使用 `select` 参数类型来选择基于参数值的短语。例如，

```php
// It may produce "Snoopy is a dog and it loves Yii!"
echo \Yii::t('app', '{name} is a {gender} and {gender, select, female{she} male{he} other{it}} loves Yii!', [
    'name' => 'Snoopy',
    'gender' => 'dog',
]);
```

在上面的表达中， `female` 和 `male` 是可能的参数值，而 `other` 用于处理不与它们中任何一个相匹配的值。对于每一个可能的参数值，
应指定一个短语并把它放在在一对大括号中。


### 指定默认翻译

你可以指定使用默认的翻译，该翻译将作为一个类别，用于不匹配任何其他翻译的后备。这种翻译应标有 `*` 。
为了做到这一点以下内容需要添加到应用程序的配置：

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

现在，你可以使用每一个还没有配置的类别，这跟 Yii 1.1 的行为有点类似。该类别的消息将来自在默认翻译 `basePath` 中的一个文件，
该文件在 `@app/messages` ：

```php
echo Yii::t('not_specified_category', 'message from unspecified category');
```

该消息将来自 `@app/messages/<LanguageCode>/not_specified_category.php` 。


### 翻译模块消息

如果你想翻译一个模块的消息，并避免使用单一翻译文件的所有信息，你可以按照下面的方式来翻译：

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

在上面的例子中，我们使用通配符匹配，然后过滤了所需的文件中的每个类别。取之使用 `fileMap` ，你可以简单地使用类映射的同名文件。
现在你可以直接使用 `Module::t('validation', 'your custom validation message')` 或 `Module::t('form', 'some form label')`。

### 翻译小部件消息

上述模块的翻译规则也同样适用于小部件的翻译规则，例如：

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

你可以简单地使用类映射的同名文件而不是使用 `fileMap` 。现在你直接可以使用 `Menu::t('messages', 'new messages {messages}', ['{messages}' => 10])` 。

> **提示**: 对于小部件也可以使用 i18n 视图，并一样以控制器的规则来应用它们。


### 翻译框架信息

Yii 自带了一些默认的信息验证错误和其他一些字符串的翻译。这些信息都是在 `yii` 类别中。有时候你想纠正应用程序的默认信息翻译。
为了做到这一点，需配置 `i18n` [应用组件](structure-application-components.md) 如下：

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

现在可以把你修改过的翻译放在 `@app/messages/<language>/yii.php`。

### 处理缺少的翻译

如果翻译的消息在消息源文件里找不到，Yii 将直接显示该消息内容。这样一来当你的原始消息是一个有效的冗长的文字时会很方便。
然而，有时它是不能实现我们的需求。你可能需要执行一些自定义处理的情况，这时请求的翻译可能在消息翻译源文件找不到。
这可通过使用 [[yii\i18n\MessageSource::EVENT_MISSING_TRANSLATION|missingTranslation]] - [[yii\i18n\MessageSource]] 的事件来完成。

例如，你可能想要将所有缺失的翻译做一个明显的标记，这样它们就可以很容易地在页面中找到。
为此，你需要先设置一个事件处理程序。这可以在应用程序配置中进行：

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

现在，你需要实现自己的事件处理程序：

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

如果 [[yii\i18n\MissingTranslationEvent::translatedMessage]] 是由事件处理程序设置，它将显示翻译结果。

> 注意：每个消息源会单独处理它缺少的翻译。如果是使用多个消息源，并希望他们把缺少的翻译以同样的方式来处理，
> 你应该给它们每一个消息源指定相应的事件处理程序。


### 使用 `message` 命令 <a name="message-command"></a>

翻译储存在 [[yii\i18n\PhpMessageSource|php files]]，[[yii\i18n\GettextMessageSource|.po files] 或者 [[yii\i18n\DbMessageSource|database]]。
具体见类的附加选项。

首先，你需要创建一个配置文件。确定应该保存在哪里，然后执行命令

```bash
./yii message/config path/to/config.php
```

打开创建的文件，并按照需求来调整参数。特别注意：

* `languages`: 代表你的应用程序应该被翻译成什么语言的一个数组;
* `messagePath`: 存储消息文件的路径，这应与配置中 `i18n` 的 `basePath` 参数一致。

> 注意，这里不支持路径别名，它们必须是配置文件相对路径的位置

一旦你做好了配置文件，你就可以使用命令提取消息

```bash
./yii message path/to/config.php
```

然后你会发现你的文件（如果你已经选择基于文件的翻译）在 `messagePath` 目录。


## 视图的翻译 <span id="view-translation"></span>

有时你可能想要翻译一个完整的视图脚本，而不是翻译单个文本信息。为了实现这一目标，只需简单的翻译视图并在它子目录的下保存一个名称一样的目标语言文件。
例如，如果你想要翻译的视图脚本 `views/site/index.php` 且目标语言是 `ru-RU`，你可以将视图翻译并保存为 `views/site/ru-RU/index.php`。现在
每当你调用 [[yii\base\View::renderFile()]] 或任何其它方法 (如 [[yii\base\Controller::render()]]) 来渲染 `views/site/index.php` 视图，
它最终会使用所翻译的 `views/site/ru-RU/index.php`。

> 注意：如果 [[yii\base\Application::$language|target language]] 跟 [[yii\base\Application::$sourceLanguage|source language]] 相同，
在翻译视图的存在下，将呈现原始视图。


## 格式化日期和数字值 <span id="date-number"></span>

在 [格式化输出数据](output-formatting.md) 一节可获取详细信息。


## 设置 PHP 环境 <span id="setup-environment"></span>

Yii 使用 [PHP intl 扩展](http://php.net/manual/en/book.intl.php) 来提供大多数 I18N 的功能，
如日期和数字格式的 [[yii\i18n\Formatter]] 类和消息格式的 [[yii\i18n\MessageFormatter]] 类。
当 `intl` 扩展没有安装时，两者会提供一个回调机制。然而，该回调机制只适用于目标语言是英语的情况下。
因此，当 I18N 对你来说必不可少时，强烈建议你安装 `intl`。

[PHP intl 扩展](http://php.net/manual/en/book.intl.php) 是基于对于所有不同的语言环境提供格式化规则的 [ICU库](http://site.icu-project.org/)。
不同版本的 ICU 中可能会产生不同日期和数值格式的结果。为了确保你的网站在所有环境产生相同的结果，建议你安装与 `intl` 扩展相同的版本（和 ICU 同一版本）。

要找出所使用的 PHP 是哪个版本的 ICU ，你可以运行下面的脚本，它会给出你所使用的 PHP 和 ICU 的版本。

```php
<?php
echo "PHP: " . PHP_VERSION . "\n";
echo "ICU: " . INTL_ICU_VERSION . "\n";
```

此外，还建议你所使用的 ICU 版本应等于或大于 49 的版本。这确保了可以使用本文档描述的所有功能。例如，
低于 49 版本的 ICU 不支持使用 `#` 占位符来实现复数规则。请参阅 <http://site.icu-project.org/download> 获取可用 ICU 版本的完整列表。
注意，版本编号在 4.8 之后发生了变化（如 ICU4.8，ICU49，50 ICU 等）。

另外，ICU 库中时区数据库的信息可能过时。要更新时区数据库时详情请参阅
[ICU 手册](http://userguide.icu-project.org/datetime/timezone#TOC-Updating-the-Time-Zone-Data) 。而对于 ICU 输出格式使用的时区数据库，
PHP 用的时区数据库可能跟它有关。你可以通过安装 [pecl package `timezonedb`](http://pecl.php.net/package/timezonedb) 的最新版本来更新它。
