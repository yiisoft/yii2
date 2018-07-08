国际化（Internationalization）
===========================

国际化（I18N）是指在设计软件时，使它可以无需做大的改变就能够适应不同的语言和地区的需要。对于 Web 应用程序，
这有着特别重要的意义，因为潜在的用户可能会在全球范围内。
Yii 提供的国际化功能支持全方位信息翻译，
视图翻译，日期和数字格式化。


## 区域和语言（Locale and Language） <span id="locale-language"></span>

### 区域（Locale）

区域设置是一组参数以定义用户希望能在他们的用户界面所看到用户的语言，
国家和任何特殊的偏好。
它通常是由语言 ID 和区域 ID 组成。

例如，ID “en-US” 代表英语和美国的语言环境。为了保持一致性，

在 Yii 应用程序中使用的所有区域 ID 应该规范化为 `ll-CC`，
其中 `ll` 是根据两个或三个字母的小写字母语言代码
[ISO-639](http://www.loc.gov/standards/iso639-2/) 和 `CC` 是两个字母的国别代码
[ISO-3166](http://www.iso.org/iso/en/prods-services/iso3166ma/02iso-3166-code-lists/list-en1.html)。
有关区域设置的更多细节可以看
[ICU 项目文档](http://userguide.icu-project.org/locale#TOC-The-Locale-Concept)。

### 语言（Language）

在 Yii中，我们经常用 “language” 来代表一个区域。

Yii 应用程序使用两种语言：
* [[yii\base\Application::$sourceLanguage|源语言]]：前者指的是写在代码中的语言，后者是向最终用户显示内容的语言。
* [[yii\base\Application::$language|目标语言]]：而信息翻译服务主要是将文本消息从原语言翻译到目标语言。

所谓的消息翻译服务主要将文本消息从源语言转换为目标语言。

### 配置（Configuration）
可以用类似下面的应用程序配置来配置应用程序语言：

```php
return [
    // 设置目标语言为俄语
    'language' => 'ru-RU',
    
    // 设置源语言为英语
    'sourceLanguage' => 'en-US',
    
    ......
];
```

默认的 [[yii\base\Application::$sourceLanguage|源语言]] 值是 `en-US`，即美国英语。
建议你保留此默认值不变，
因为通常让人将英语翻译成其它语言要比将其它语言翻译成其它语言容易得多。

你经常需要根据不同的因素来动态地设置 [[yii\base\Application::$language|目标语言]] ，
如最终用户的语言首选项。要在应用程序配置中配置它，
你可以使用下面的语句来更改目标语言：

```php
// 改变目标语言为中文
\Yii::$app->language = 'zh-CN';
```

> Tip: 如果您的源语言在代码的不同部分中有所不同，那么您可以覆盖不同消息源的源语言，
> 这将在下一节中介绍。

## 消息翻译（Message Translation） <span id="message-translation"></span>

### 从源语言到目标语言（From source language to target language）
消息翻译服务用于将一条文本信息从一种语言（通常是 [[yii\base\Application::$sourceLanguage|源语言]] ）
翻译成另一种语言（通常是 [[yii\base\Application::$language|目标语言]]）。

它的翻译原理是通过在语言文件中查找要翻译的信息以及翻译的结果。如果要翻译的信息可以在语言文件中找到，会返回相应的翻译结果；
否则会返回原始未翻译的信息。

### 如何实现（How to implement）
为了使用消息翻译服务，需要做如下工作：

1. 调用 [[Yii::t()]] 方法且在其中包含每一条要翻译的消息；
2. 配置一个或多个消息来源，能在其中找得到要翻译的消息和翻译结果；
3. 让译者翻译信息并将它们存储在消息来源。


#### 1. 包裹一条消息（Wrap a text message）
这个 [[Yii::t()]] 方法的用法如下，

```php
echo \Yii::t('app', 'This is a string to translate!');
```

第一个参数指储存消息来源的类别名称，
第二个参数指需要被翻译的消息。

#### 2. 配置一个或多个消息源（Configure one or multiple message sources）
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

在上面的代码中，正在配置 [[yii\i18n\PhpMessageSource]] 支持的消息源。

##### 带 `*` 符号的类别通配符（Category wildcards with `*` symbol）

在上面的代码中，配置了由 [[yii\i18n\PhpMessageSource]] 所支持的消息来源。模式 `app*` 表示所有以 `app`
开头的消息类别名称都使用这个翻译的消息来源。

#### 3. 让译员翻译消息并将它们存储在消息源中（Let the translators translate messages and store them in the message source(s)）

[[yii\i18n\PhpMessageSource]] 类使用PHP文件和一个简单的 PHP 数组来存储消息转换。
这些文件包含 `源语言` 中的消息到 `目标语言` 中的翻译的映射。

> Info: 您可以使用 [`message` 命令](#message-command) 自动生成这些 PHP 文件，
> 这将在本章后面介绍。

每个PHP文件对应于单个类别的消息。 默认情况下，文件名应与类别名称相同。
`app/messages/nl-NL/main.php` 的例子：

```php
<?php

/**
* Translation map for nl-NL
*/
return [
    'welcome' => 'welkom'
];

```


##### 文件映射（File mapping）

[[yii\i18n\PhpMessageSource::fileMap|fileMap]] 来映射一个类别到不同名称的 PHP 文件。 

在上面的例子中，类别 `app/error` 被映射到PHP文件 `@app/messages/ru-RU/error.php`（假设 `ru-RU` 为目标语言）。
如果没有此配置，
该类别将被映射到 `@app/messages/ru-RU/app/error.php` 。

#####  其他存储类型（Other storage types）

除了在PHP文件中存储消息来源，
也可以使用下面的消息来源在不同的存储来存储翻译的消息：

- [[yii\i18n\GettextMessageSource]] 使用 GNU Gettext 的 MO 或 PO 文件保存翻译的消息。
- [[yii\i18n\DbMessageSource]] 使用一个数据库表来存储翻译的消息。


## 消息格式化（Message Formatting） <span id="message-formatting"></span>

在要翻译的消息里，你可以嵌入一些占位符，并让它们通过动态的参数值来代替。
你甚至可以根据目标语言格式的参数值来使用特殊的占位符。
在本节中，我们将介绍如何用不同的方式来格式化消息。

### 消息参数（Message Parameters） <span id="message-parameters"></span>

在待翻译的消息，可以嵌入一个或多个占位符，以便它们可以由给定的参数值取代。
通过给不同的参数值，可以动态地改变翻译内容的消息。
在下面的例子中，
占位符 `{username}` 在 `“Hello, {username}！”` 中将分别被 `'Alexander'`和`'Qiang'` 所替换。

```php
$username = 'Alexander';
// 输出：“Hello, Alexander”
echo \Yii::t('app', 'Hello, {username}!', [
    'username' => $username,
]);

$username = 'Qiang';
// 输出：“Hello, Qiang”
echo \Yii::t('app', 'Hello, {username}!', [
    'username' => $username,
]);
```

当翻译的消息包含占位符时，应该让占位符保留原样。
这是因为调用 `Yii::t()` 时，占位符将被实际参数值代替。

你可以使用 *名称占位符* 或者 *位置占位符*，但不能两者都用在同一个消息里。

前面的例子说明了如何使用名称占位符。即每个占位符的格式为 `{参数名称}` ，你所提供的参数作为关联数组，
其中数组的键是参数名称（没有大括号），
数组的值是对应的参数值。

位置占位符是使用基于零的整数序列，在调用 `Yii::t()` 时会参数值根据它们出现位置的顺序分别进行替换。
在下面的例子中，位置占位符 `{0}`，`{1}` 和 `{2}` 
将分别被 `$price`，`$count` 和 `$subtotal` 所替换。

```php
$price = 100;
$count = 2;
$subtotal = 200;
echo \Yii::t('app', 'Price: {0}, Count: {1}, Subtotal: {2}', $price, $count, $subtotal);
```

在单个位置参数的情况下，它的值可以被指定而不包含在数组中：

```php
echo \Yii::t('app', 'Price: {0}', $price);
```

> Tip: 大多数情况下你应该使用名称占位符。
> 这是因为参数名称可以让翻译者更好的理解要被翻译的消息。


### 格式化参数 <span id="parameter-formatting"></span>

你可以在消息的占位符指定附加格式的规则，
这样的参数值可在替换占位符之前格式化它们。在下面的例子中，
价格参数值将视为一个数并格式化为货币值：

```php
$price = 100;
echo \Yii::t('app', 'Price: {0, number, currency}', $price);
```

> Note: 参数的格式化需要安装 [intl PHP 扩展](http://www.php.net/manual/en/intro.intl.php)。

可以使用缩写的形式或完整的形式来格式化占位符：

```
short form: {PlaceholderName, ParameterType}
full form: {PlaceholderName, ParameterType, ParameterStyle}
```

> Note: 如果您需要使用特殊字符（如 `{`，`}`，`'`，`#`，请使用 `'`：
> 
```php
echo Yii::t('app', "Example of string with ''-escaped characters'': '{' '}' '{test}' {count,plural,other{''count'' value is # '#{}'}}", ['count' => 3]);
```

请参阅 [ICU 文档](http://icu-project.org/apiref/icu4c/classMessageFormat.html)
关于如何指定这样的占位符的说明。接下来我们会展示一些常用的使用方法。


#### 数字（Number） <span id="number"></span>

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

自定义格式中使用的字符可以在“特殊模式字符”一节的
[ICU API 参考](http://icu-project.org/apiref/icu4c/classicu_1_1DecimalFormat.html)
中找到。


该值始终根据您翻译的区域设置进行格式设置，即无需更改翻译区域设置即可更改小数或千位分隔符，货币符号等。 
如果你需要定制这些，你可以使用
[[yii\i18n\Formatter::asDecimal()]] 和 [[yii\i18n\Formatter::asCurrency()]]。

#### 日期（Date） <span id="date"></span>

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

[格式化参考](http://icu-project.org/apiref/icu4c/classicu_1_1SimpleDateFormat.html#details)。


#### 时间（Time） <span id="time"></span>

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

[格式化参考](http://icu-project.org/apiref/icu4c/classicu_1_1SimpleDateFormat.html#details)。


#### 拼写（Spellout） <span id="spellout"></span>

参数值为一个数并被格式化为它的字母拼写形式。 例如，

```php
// 输出："42 is spelled as forty-two"
echo \Yii::t('app', '{n,number} is spelled as {n, spellout}', ['n' => 42]);
```

默认情况下，该数字拼写为基数。 它可以改变：

```php
// may produce "I am forty-seventh agent"
echo \Yii::t('app', 'I am {n,spellout,%spellout-ordinal} agent', ['n' => 47]);
```

请注意，在 `spellout,` 之后和 `%` 之前不应该有空格。

要获取可用于您正在使用的语言环境的选项列表，请查看
[http://intl.rmcreative.ru/](http://intl.rmcreative.ru/) 上的“编号模式，拼写”。

#### 序数词（Ordinal） <span id="ordinal"></span>

参数值为一个数并被格式化为一个序数词。 例如，

```php
// 输出："You are the 42nd visitor here!"
echo \Yii::t('app', 'You are the {n, ordinal} visitor here!', ['n' => 42]);
```

Ordinal 支持更多格式化西班牙语等语言的方式：

```php
// may produce 471ª
echo \Yii::t('app', '{n,ordinal,%digits-ordinal-feminine}', ['n' => 471]);
```

请注意，在 `ordinal,` 之后和 `%` 之前不应该有空格。

要获取可用于您正在使用的语言环境的选项列表，请查看
[http://intl.rmcreative.ru/](http://intl.rmcreative.ru/) 上的“编号模式，序号”。

#### 持续时间（Duration） <span id="duration"></span>

参数值为秒数并被格式化为持续的时间段。 例如，

```php
// 输出："You are here for 47 sec. already!"
echo \Yii::t('app', 'You are here for {n, duration} already!', ['n' => 47]);
```

持续时间支持更多格式化方法：

```php
// may produce 130:53:47
echo \Yii::t('app', '{n,duration,%in-numerals}', ['n' => 471227]);
```

请注意，在 `duration,` 之后和 `%` 之前不应该有空格。

要获取您正在使用的区域设置的可用选项列表，请查看
[http://intl.rmcreative.ru/](http://intl.rmcreative.ru/) 上的“编号模式，持续时间”。

#### 复数（Plural） <span id="plural"></span>

不同的语言有不同的方式来表示复数。 Yii 提供一个便捷的途径，
即使是非常复杂的规则也使翻译消息时不同的复数形式行之有效。
取之以直接处理词形变化规则，它是足以面对某些词形变化语言的翻译。 例如，

```php
// 当 $n = 0 时，输出："There are no cats!"
// 当 $n = 1 时，输出："There is one cat!"
// 当 $n = 42 时，输出："There are 42 cats!"
echo \Yii::t('app', 'There {n, plural, =0{are no cats} =1{is one cat} other{are # cats}}!', ['n' => $n]);
```

在上面的多个规则的参数中， `=0` 意味着 `n` 的值是 0 ，`=1` 意味着 `n` 的值是 1 ， 而 `other` 则是对于其它值，
`#` 会被 `n` 中的值给替代。 

复数形式可以是某些非常复杂的语言。下面以俄罗斯为例，`=1` 完全匹配 `n = 1`，
而 `one` 匹配 `21` 或 `101`：

```
Здесь {n, plural, =0{котов нет} =1{есть один кот} one{# кот} few{# кота} many{# котов} other{# кота}}!
```

注意，上述信息主要是作为一个翻译的信息，
而不是一个原始消息，除非设置应用程序的
[[yii\base\Application::$sourceLanguage|源语言]] 为 `ru-RU`。

> Note: 除非您将应用程序的 [[yii\base\Application::$sourceLanguage|源语言]] 
> 设置为“RU-RU”，并且从以下语言转换而来，上面的示例俄语消息主要用作翻译的消息，而不是原始消息俄语。
>
> 当在 `Yii::t()` 调用中指定的原始消息未找到翻译时，
> [[yii\base\Application::$sourceLanguage|源语言]] 复数规则将应用于原始消息。

对于字符串如下所示的情况，有一个 `offset` 参数：
 
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

#### 序数选择（Ordinal selection） <span id="ordinal-selection">

`selectordinal` 的参数类型旨在为您所翻译的语言环境选择一个基于语序规则的字符串：
一个基于语序规则的字符串：

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

格式与复数使用的格式非常接近。 要了解您应为特定语言环境指定哪些参数，请参阅
[http://intl.rmcreative.ru/](http://intl.rmcreative.ru/) 上的“复数规则，序数”。
或者，您可以参考 [unicode.org上的规则参考](http://unicode.org/repos/cldr-tmp/trunk/diff/supplemental/language_plural_rules.html)。

#### 选择（Selection） <span id="selection"></span>

可以使用 `select` 参数类型来选择基于参数值的短语。例如，

```php
// 输出："Snoopy is a dog and it loves Yii!"
echo \Yii::t('app', '{name} is a {gender} and {gender, select, female{she} male{he} other{it}} loves Yii!', [
    'name' => 'Snoopy',
    'gender' => 'dog',
]);
```

在上面的表达中， `female` 和 `male` 是可能的参数值，
而 `other` 用于处理不与它们中任何一个相匹配的值。对于每一个可能的参数值，
应指定一个短语并把它放在在一对大括号中。


### 指定默认翻译（Specifying default message source） <span id="default-message-source"></span>

你可以指定使用默认的翻译，该翻译将作为一个类别，用于不匹配任何其他翻译的后备。
这种翻译应标有 `*` 。
为了做到这一点以下内容需要添加到应用程序的配置：

```php
//配置 i18n 组件

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

### 翻译模块消息（Translating module messages） <span id="module-translation"></span>

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
现在你可以直接使用 `Module::t('validation', 'your custom validation message')` 
或 `Module::t('form', 'some form label')`。

### 翻译小部件消息（Translating widgets messages） <span id="widget-translation"></span>

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

你可以简单地使用类映射的同名文件而不是使用 `fileMap` 。
现在你直接可以使用 `Menu::t('messages', 'new messages {messages}', ['{messages}' => 10])` 。

> Tip: 对于小部件也可以使用 i18n 视图，并一样以控制器的规则来应用它们。


### 翻译框架信息（Translating framework messages） <span id="framework-translation"></span>

Yii 自带了一些默认的信息验证错误和其他一些字符串的翻译。
这些信息都是在 `yii` 类别中。有时候你想纠正应用程序的默认信息翻译。
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

### 处理缺少的翻译（Handling missing translations） <span id="missing-translations"></span>

如果翻译的消息在消息源文件里找不到，Yii 将直接显示该消息内容。这样一来当你的原始消息是一个有效的冗长的文字时会很方便。
然而，有时它是不能实现我们的需求。你可能需要执行一些自定义处理的情况，
这时请求的翻译可能在消息翻译源文件找不到。
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

> Note: 每个消息源会单独处理它缺少的翻译。如果是使用多个消息源，并希望他们把缺少的翻译以同样的方式来处理，
> 你应该给它们每一个消息源指定相应的事件处理程序。


### 使用 `message` 命令（Using the `message` command） <a name="message-command"></a>

翻译储存在 [[yii\i18n\PhpMessageSource|php 文件]]，[[yii\i18n\GettextMessageSource|.po 文件] 或者 [[yii\i18n\DbMessageSource|数据库]]。具体见类的附加选项。

首先，你需要创建一个配置文件。确定应该保存在哪里，然后执行命令

```bash
./yii message/config path/to/config.php
```

打开创建的文件，并按照需求来调整参数。特别注意：

* `languages`: 代表你的应用程序应该被翻译成什么语言的一个数组;
* `messagePath`: 存储消息文件的路径，这应与配置中 `i18n` 的 `basePath` 参数一致。

您也可以使用 './yii message/config' 命令通过 cli 动态生成带有指定选项的配置文件。
例如，你可以像下面这样设置 `languages` 和 `messagePath` 参数：

```bash
./yii message/config --languages=de,ja --messagePath=messages path/to/config.php
```

要获取可用选项列表，请执行下一个命令：

```bash
./yii help message/config
```

一旦你完成了配置文件，你最终可以用命令提取你的消息：

```bash
./yii message path/to/config.php
```

另外，您可以使用选项来动态更改提取参数。

然后你会发现你的文件（如果你已经选择基于文件的翻译）在 `messagePath` 目录。


## 视图的翻译（View Translation） <span id="view-translation"></span>

有时你可能想要翻译一个完整的视图文件，而不是翻译单条文本消息。为了达到这一目的，
只需简单的翻译视图并在它子目录下保存一个名称一样的目标语言文件。
例如，如果你想要翻译的视图文件为 `views/site/index.php` 且目标语言是 `ru-RU`，
你可以将视图翻译并保存为 `views/site/ru-RU/index.php`。现在
每当你调用 [[yii\base\View::renderFile()]] 或任何其它方法 (如 [[yii\base\Controller::render()]]) 来渲染 `views/site/index.php` 视图，
它最终会使用所翻译的 `views/site/ru-RU/index.php`。

> Note: 如果 [[yii\base\Application::$language|目标语言]] 跟 [[yii\base\Application::$sourceLanguage|源语言]] 相同，
在翻译视图的存在下，将呈现原始视图。


## 格式化日期和数字值（Formatting Date and Number Values） <span id="date-number"></span>

在 [格式化输出数据](output-formatting.md) 一节可获取详细信息。


## 设置 PHP 环境（Setting Up PHP Environment） <span id="setup-environment"></span>

Yii 使用 [PHP intl 扩展](http://php.net/manual/en/book.intl.php) 来提供大多数 I18N 的功能，
如日期和数字格式的 [[yii\i18n\Formatter]] 类和消息格式的 [[yii\i18n\MessageFormatter]] 类。
当 `intl` 扩展没有安装时，两者会提供一个回调机制。然而，该回调机制只适用于目标语言是英语的情况下。
因此，当 I18N 对你来说必不可少时，强烈建议你安装 `intl`。

[PHP intl 扩展](http://php.net/manual/en/book.intl.php) 是基于对于所有不同的语言环境
提供格式化规则的 [ICU库](http://site.icu-project.org/)。
不同版本的 ICU 中可能会产生不同日期和数值格式的结果。
为了确保你的网站在所有环境产生相同的结果，
建议你安装与 `intl` 扩展相同的版本（和 ICU 同一版本）。

要找出所使用的 PHP 是哪个版本的 ICU ，你可以运行下面的脚本，它会给出你所使用的 PHP 和 ICU 的版本。

```php
<?php
echo "PHP: " . PHP_VERSION . "\n";
echo "ICU: " . INTL_ICU_VERSION . "\n";
echo "ICU Data: " . INTL_ICU_DATA_VERSION . "\n";
```

此外，还建议你所使用的 ICU 版本应等于或大于 49 的版本。这确保了可以使用本文档描述的所有功能。例如，
低于 49 版本的 ICU 不支持使用 `#` 占位符来实现复数规则。
请参阅 <http://site.icu-project.org/download> 获取可用 ICU 版本的完整列表。
注意，版本编号在 4.8 之后发生了变化（如 ICU4.8，ICU49，50 ICU 等）。

另外，ICU 库中时区数据库的信息可能过时。要更新时区数据库时详情请参阅
[ICU 手册](http://userguide.icu-project.org/datetime/timezone#TOC-Updating-the-Time-Zone-Data) 。
而对于 ICU 输出格式使用的时区数据库，PHP 用的时区数据库可能跟它有关。
你可以通过安装 [pecl package `timezonedb`](http://pecl.php.net/package/timezonedb) 的最新版本来更新它。
