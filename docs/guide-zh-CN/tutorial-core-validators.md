核心验证器（Core Validators）
===============

Yii 提供一系列常用的核心验证器，位于 `yii\validators` 命名空间之下。
为了避免使用冗长的类名，你可以直接用**别名**来指定相应的核心验证器。
比如你可以用 `required` 别名代指 [[yii\validators\RequiredValidator]] 类：

```php
public function rules()
{
    return [
        [['email', 'password'], 'required'],
    ];
}
```

[[yii\validators\Validator::builtInValidators]] 属性声明了所有被支持的验证器别名。

下面，我们将详细介绍每一款验证器的主要用法和属性。


## [[yii\validators\BooleanValidator|boolean（布尔型）]] <span id="boolean"></span>

```php
[
    // 检查 "selected" 是否为 0 或 1，无视数据类型
    ['selected', 'boolean'],

    // 检查 "deleted" 是否为布尔类型，即 true 或 false
    ['deleted', 'boolean', 'trueValue' => true, 'falseValue' => false, 'strict' => true],
]
```

该验证器检查输入值是否为一个布尔值。

- `trueValue`： 代表**真**的值。默认为 `'1'`。
- `falseValue`：代表**假**的值。默认为 `'0'`。
- `strict`：是否要求待测输入必须严格匹配 `trueValue` 或 `falseValue`。默认为 `false`。


> Note: 因为通过 HTML 表单传递的输入数据都是字符串类型，所以一般情况下你都需要保持
  [[yii\validators\BooleanValidator::strict|strict]] 属性为假。


## [[yii\captcha\CaptchaValidator|captcha（验证码）]] <span id="captcha"></span>

```php
[
    ['verificationCode', 'captcha'],
]
```

该验证器通常配合 [[yii\captcha\CaptchaAction]] 以及 [[yii\captcha\Captcha]]
使用，以确保某一输入与 [[yii\captcha\Captcha|CAPTCHA]] 小部件所显示的验证代码（verification code）相同。

- `caseSensitive`：对验证码的比对是否要求大小写敏感。默认为 false。
- `captchaAction`：指向用于渲染 CAPTCHA 图片的 [[yii\captcha\CaptchaAction|CAPTCHA action]] 
  的 [路由](structure-controllers.md#routes)。默认为 `'site/captcha'`。
- `skipOnEmpty`：当输入为空时，是否跳过验证。
  默认为 false，也就是输入值为必需项。
  

## [[yii\validators\CompareValidator|compare（比对）]] <span id="compare"></span>

```php
[
    // 检查 "password" 属性的值是否与 "password_repeat" 的值相同
    ['password', 'compare'],

    // 和上一个相同，只是明确指定了需要对比的属性字段
    ['password', 'compare', 'compareAttribute' => 'password_repeat'],
    
    // 检查年龄是否大于等于 30
    ['age', 'compare', 'compareValue' => 30, 'operator' => '>='],
]
```

该验证器比对两个特定输入值之间的关系
是否与 `operator` 属性所指定的相同。

- `compareAttribute`：用于与原属性相比对的属性名称。
  当该验证器被用于验证某目标属性时，
  该属性会默认为目标属性加后缀 `_repeat`。
  举例来说，若目标属性为 `password`，则该属性默认为 `password_repeat`。
- `compareValue`：用于与输入值相比对的常量值。
  当该属性与 `compareAttribute` 属性同时被指定时，该属性优先被使用。
- `operator`：比对操作符。默认为 `==`，意味着检查输入值是否与 `compareAttribute` 或 `compareValue` 的值相等。
  该属性支持如下操作符：
     * `==`：检查两值是否相等。比对为非严格模式。
     * `===`：检查两值是否全等。比对为严格模式。
     * `!=`：检查两值是否不等。比对为非严格模式。
     * `!==`：检查两值是否不全等。比对为严格模式。
     * `>`：检查待测目标值是否大于给定被测值。
     * `>=`：检查待测目标值是否大于等于给定被测值。
     * `<`：检查待测目标值是否小于给定被测值。
     * `<=`：检查待测目标值是否小于等于给定被测值。
- `type`: 默认的比对类型是'[[yii\validators\CompareValidator::TYPE_STRING|string]]'，此时将按照字节逐个对比。
  当需要比对的值是数字时，需要设置类型[[yii\validators\CompareValidator::$type|$type]]为
  '[[yii\validators\CompareValidator::TYPE_NUMBER|number]]'，启用数字对比模式。

### 比对日期值

比对验证器只能用来对比字符串和数字。如果你需要比对日期，有两种方式。
如果需要比对一个固定的日期值，只需要使用
[[yii\validators\DateValidator|date]]验证器并设置对应的属性
[[yii\validators\DateValidator::$min|$min]] 或 [[yii\validators\DateValidator::$max|$max]] 。
如果需要比对如表单提交的两个日期，比如一个`fromDate`和一个`toDate`项，
你可以结合比对验证器和日期验证器同时使用，如下所示：

```php
['fromDate', 'date', 'timestampAttribute' => 'fromDate'],
['toDate', 'date', 'timestampAttribute' => 'toDate'],
['fromDate', 'compare', 'compareAttribute' => 'toDate', 'operator' => '<', 'enableClientValidation' => false],
```

因为验证器会按照顺序执行，
将首先验证 `fromDate` 和 `toDate` 字段是一个有效的日期值，最终将被转换成一个系统可识别的格式。
此后这两个值将使用比对验证器进行比对。
因日期验证器只提供服务端使用，当前不提供客户端验证，
故 [[yii\validators\CompareValidator::$enableClientValidation|$enableClientValidation]]
在比对验证器将同样被设置为 `false` 。


## [[yii\validators\DateValidator|date（日期）]] <span id="date"></span>

此日期 [[yii\validators\DateValidator|date]] 验证器有三种不同的使用方式：


```php
[
    [['from_date', 'to_date'], 'date'],
    [['from_datetime', 'to_datetime'], 'datetime'],
    [['some_time'], 'time'],
]
```

该验证器检查输入值是否为适当格式的 date，time，或者 datetime。
另外，它还可以帮你把输入值转换为一个 UNIX 时间戳并保存到 
[[yii\validators\DateValidator::timestampAttribute|timestampAttribute]] 所指定的属性里。

- `format`：被验证值的日期/时间格式。
   这里的值可以是 [ICU manual](https://unicode-org.github.io/icu/userguide/format_parse/datetime/#datetime-format-syntax) 中定义的日期时间格式。
   另外还可以设置以 `php:` 开头的字符串，用来表示PHP可以识别的日期时间格式。
   `Datetime` 日期时间类。请参考 <https://www.php.net/manual/zh/datetime.createfromformat.php> 获取更多支持的格式。
   如果没有设置，默认值将使用 `Yii::$app->formatter->dateFormat` 中的值。
   请参考 [[yii\validators\DateValidator::$format|API 文档]] 以获取更详细的说明。

- `timestampAttribute`：输入的日期时间将被转换为时间戳后设置到的属性的名称。
  可以设置为和被验证的属性相同。如果相同，
  原始值将在验证结束后被时间戳覆盖。
  请参考 ["Handling date input with the DatePicker"](https://github.com/yiisoft/yii2-jui/blob/master/docs/guide/topics-date-picker.md) 以获取更多使用事例。

  从版本 2.0.4 开始，支持
  使用 [[yii\validators\DateValidator::$timestampAttributeFormat|$timestampAttributeFormat]] 设置日期时间格式
  和使用 [[yii\validators\DateValidator::$timestampAttributeTimeZone|$timestampAttributeTimeZone]] 设置时区。
  
  注意，如果使用 `timestampAttribute`，被验证的值将被转换为UTC标准的时间戳，
  所以使用 [[yii\validators\DateValidator::timeZone|input time zone]] 输入的时区将被转换为UTC时间。

- 自版本 2.0.4 开始支持
  直接对 [[yii\validators\DateValidator::$min|minimum]] 和 [[yii\validators\DateValidator::$max|maximum]] 设置时间戳。

如果输入的值是可选的，可以设置一个 [默认值验证器](#default) 来确保空值通过验证器验证后的值是 `null`，
否则数据库可能会保存类似 `0000-00-00` 的值，
或表单日期选择器会显示 `1970-01-01` 。

```php
[
    [['from_date', 'to_date'], 'default', 'value' => null],
    [['from_date', 'to_date'], 'date'],
],
```

## [[yii\validators\DefaultValueValidator|default（默认值）]] <span id="default"></span>

```php
[
    // 若 "age" 为空，则将其设为 null
    ['age', 'default', 'value' => null],

    // 若 "country" 为空，则将其设为 "USA"
    ['country', 'default', 'value' => 'USA'],

    // 若 "from" 和 "to" 为空，则分别给他们分配自今天起，3 天后和 6 天后的日期。
    [['from', 'to'], 'default', 'value' => function ($model, $attribute) {
        return date('Y-m-d', strtotime($attribute === 'to' ? '+3 days' ：'+6 days'));
    }],
]
```

该验证器并不进行数据验证。
而是，给为空的待测属性分配默认值。

- `value`：默认值，或一个返回默认值的 PHP Callable 对象（即回调函数）。
  它们会分配给检测为空的待测属性。PHP 回调方法的样式如下：

```php
function foo($model, $attribute) {
    // ... 计算 $value ...
    return $value;
}
```

> Info: 如何判断待测值是否为空，
  被写在另外一个话题的
  [处理空输入](input-validation.md#handling-empty-inputs)章节。


## [[yii\validators\NumberValidator|double（双精度浮点型）]] <span id="double"></span>

```php
[
    // 检查 "salary" 是否为浮点数
    ['salary', 'double'],
]
```

该验证器检查输入值是否为双精度浮点数。等效于 [number](#number) 验证器。

- `max`：上限值（含界点）。若不设置，则验证器不检查上限。
- `min`：下限值（含界点）。若不设置，则验证器不检查下限。


## [[yii\validators\EachValidator|each（循环验证）]] <span id="each"></span>

> Info: 此验证器自版本 2.0.4 后可用。

```php
[
    // 检查是否每个分类编号都是一个整数
    ['categoryIDs', 'each', 'rule' => ['integer']],
]
```

此验证器只能验证数组格式的属性。此验证器将判断数组中的 *每个* 元素是否都符合验证规则。
在上面的例子中，`categoryIDs` 属性必须是一个数组
且每个元素将被使用 `integer` 验证器进行验证。

- `rule`：保存验证规则的数组。数组中第一个元素表示验证器类名或
  验证器的别名。数组中其余键值对将被用来配置此验证器规则。
- `allowMessageFromRule`：是否使用规则中指定的验证器返回的错误信息。默认值为 `true`。
  如果设置为 `false`，将使用 `message` 作为错误信息。

> Note: 如果被验证的值不是一个数组，将被认为验证失败，
  并且返回 `message` 设定的错误信息。
  
  
## [[yii\validators\EmailValidator|email（电子邮件）]] <span id="email"></span>

```php
[
    // 检查 "email" 是否为有效的邮箱地址
    ['email', 'email'],
]
```

该验证器检查输入值是否为有效的邮箱地址。

- `allowName`：检查是否允许带名称的电子邮件地址 (e.g. `张三 <John.san@example.com>`)。 默认为 false。
- `checkDNS`：检查邮箱域名是否存在，且有没有对应的 A 或 MX 记录。
  不过要知道，有的时候该项检查可能会因为临时性 DNS 故障而失败，
  哪怕它其实是有效的。默认为 false。
- `enableIDN`：验证过程是否应该考虑 IDN（internationalized domain names，国际化域名，也称多语种域名，比如中文域名）。
  默认为 false。要注意但是为使用 IDN 验证功能，
  请先确保安装并开启 `intl` PHP 扩展，不然会导致抛出异常。


## [[yii\validators\ExistValidator|exist（存在性）]] <span id="exist"></span>

```php
[
    // a1 需要在 "a1" 属性所代表的字段内存在
    ['a1', 'exist'],

    // a1 必需存在，但检验的是 a1 的值在字段 a2 中的存在性
    ['a1', 'exist', 'targetAttribute' => 'a2'],

    // a1 和 a2 的值都需要存在，且它们都能收到错误提示
    [['a1', 'a2'], 'exist', 'targetAttribute' => ['a1', 'a2']],

    // a1 和 a2 的值都需要存在，只有 a1 能接收到错误信息
    ['a1', 'exist', 'targetAttribute' => ['a1', 'a2']],

    // 通过同时在 a2 和 a3 字段中检查 a2 和 a1 的值来确定 a1 的存在性
    ['a1', 'exist', 'targetAttribute' => ['a2', 'a1' => 'a3']],

    // a1 必需存在，若 a1 为数组，则其每个子元素都必须存在。
    ['a1', 'exist', 'allowArray' => true],
    
    // type_id 需要存在于 ProductType 类中定义的表中的“id”列中
    ['type_id', 'exist', 'targetClass' => ProductType::class, 'targetAttribute' => ['type_id' => 'id']],    
    
    // 与前一个相同，但使用已定义的关联“type”
    ['type_id', 'exist', 'targetRelation' => 'type'],
]
```

该验证器检查输入值是否在某表字段中存在。
它只对[活动记录](db-active-record.md)
类型的模型类属性起作用，
能支持对一个或多过字段的验证。

可以使用此验证器验证单个数据列或多个数据列
（如多个列不同的组合是否存在）。

- `targetClass`：用于查找输入值的目标 [AR](db-active-record.md) 类。
  若不设置，则会使用正在进行验证的当前模型类。
- `targetAttribute`：用于检查输入值存在性的 `targetClass` 的模型属性。
  若不设置，它会直接使用待测属性名（整个参数数组的首元素）。
  除了指定为字符串以外，你也可以用数组的形式，同时指定多个用于验证的表字段，
  数组的键和值都是代表字段的属性名，值表示 `targetClass` 的待测数据源字段，而键表示当前模型的待测属性名。
  若键和值相同，你可以只指定值。（如:`['a2']` 就代表 `['a2'=>'a2']`）
- `targetRelation`: since version 2.0.14 you can use convenient attribute `targetRelation`, which overrides the `targetClass` and `targetAttribute` attributes using specs from the requested relation.  
- `filter`：用于检查输入值存在性必然会进行数据库查询，而该属性为用于进一步筛选该查询的过滤条件。
  可以为代表额外查询条件的字符串或数组（关于查询条件的格式，请参考 [[yii\db\Query::where()]]）；
  或者样式为 `function ($query)` 的匿名函数，
  `$query` 参数为你希望在该函数内进行修改的 [[yii\db\Query|Query]] 对象。
- `allowArray`：是否允许输入值为数组。默认为 false。
  若该属性为 true 且输入值为数组，则数组的每个元素都必须在目标字段中存在。
  值得注意的是，若用吧 `targetAttribute` 设为多元素数组来验证被测值在多字段中的存在性时，该属性不能设置为 true。


## [[yii\validators\FileValidator|file（文件）]] <span id="file"></span>

```php
[
    // 检查 "primaryImage" 是否为 PNG, JPG 或 GIF 格式的上传图片。
    // 文件大小必须小于  1MB
    ['primaryImage', 'file', 'extensions' => ['png', 'jpg', 'gif'], 'maxSize' => 1024*1024*1024],
]
```

该验证器检查输入值是否为一个有效的上传文件。

- `extensions`：可接受上传的文件扩展名列表。它可以是数组，
  也可以是用空格或逗号分隔各个扩展名的字符串 (如 "gif, jpg")。
  扩展名大小写不敏感。默认为 null，
  意味着所有扩展名都被接受。
- `mimeTypes`：可接受上传的 MIME 类型列表。
  它可以是数组，也可以是用空格或逗号分隔各个 
  MIME 的字符串 (如 "image/jpeg, image/png")。
  Mime 类型名是大小写不敏感的。默认为 null，
  意味着所有 MIME 类型都被接受。
  请参考 [common media types](https://en.wikipedia.org/wiki/Media_type) 获取更多详细内容。
- `minSize`：上传文件所需最少多少 Byte 的大小。默认为 null，代表没有下限。
- `maxSize`：上传文件所需最多多少 Byte 的大小。默认为 null，代表没有上限。
- `maxFiles`：给定属性最多能承载多少个文件。
  默认为 1，代表只允许单文件上传。
  若值大于一，那么输入值必须为包含最多 `maxFiles` 个上传文件元素的数组。
- `checkExtensionByMimeType`：是否通过文件的 MIME 类型来判断其文件扩展。
  若由 MIME 判定的文件扩展与给定文件的扩展不一样，则文件会被认为无效。
  默认为 true，代表执行上述检测。

`FileValidator` 通常与 [[yii\web\UploadedFile]] 共同使用。
请参考 [文件上传](input-file-upload.md)章节来了解有关文件上传与上传文件的检验的全部内容。


## [[yii\validators\FilterValidator|filter（过滤器）]] <span id="filter"></span>

```php
[
    // trim 掉 "username" 和 "email" 输入
    [['username', 'email'], 'filter', 'filter' => 'trim', 'skipOnArray' => true],

    // 标准化 "phone" 输入
    ['phone', 'filter', 'filter' => function ($value) {
        // 在此处标准化输入的电话号码
        return $value;
    }],
    
    // 标准化 "phone" 使用方法 "normalizePhone"
    ['phone', 'filter', 'filter' => [$this, 'normalizePhone']],
    
    public function normalizePhone($value) {
        return $value;
    }
]
```

该验证器并不进行数据验证。而是给输入值应用一个过滤器，
并在验证后把它赋值回原属性变量。

- `filter`：用于定义过滤器的 PHP 回调函数。可以为全局函数名，匿名函数，或其他。
  该函数的样式必须是 `function ($value) { return $newValue; }`。该属性不能省略，必须设置。
- `skipOnArray`：是否在输入值为数组时跳过过滤器。默认为 false。
  请注意如果过滤器不能处理数组输入，你就应该把该属性设为 true。
  否则可能会导致 PHP Error 的发生。

> Tip: 如果你只是想要用 trim 处理下输入值，你可以直接用 [trim](#trim) 验证器的。

> Tip: 有许多的PHP方法结构和 `filter` 需要的结构一致。
> 比如使用类型转换方法 ([intval](https://www.php.net/manual/zh/function.intval.php)，
> [boolval](https://www.php.net/manual/zh/function.boolval.php), ...) 来确保属性为指定的类型，
> 你可以简单的设置这些方法名而不是重新定义一个匿名函数：
>
> ```php
> ['property', 'filter', 'filter' => 'boolval'],
> ['property', 'filter', 'filter' => 'intval'],
> ```


## [[yii\validators\ImageValidator|image（图片）]] <span id="image"></span>

```php
[
    // 检查 "primaryImage" 是否为适当尺寸的有效图片
    ['primaryImage', 'image', 'extensions' => 'png, jpg',
        'minWidth' => 100, 'maxWidth' => 1000,
        'minHeight' => 100, 'maxHeight' => 1000,
    ],
]
```

该验证器检查输入值是否为代表有效的图片文件。它继承自 [file](#file) 验证器，
并因此继承有其全部属性。除此之外，
它还支持以下为图片检验而设的额外属性：

- `minWidth`：图片的最小宽度。默认为 null，代表无下限。
- `maxWidth`：图片的最大宽度。默认为 null，代表无上限。
- `minHeight`：图片的最小高度。 默认为 null，代表无下限。
- `maxHeight`：图片的最大高度。默认为 null，代表无上限。

## [[yii\validators\IpValidator|ip（IP地址）]] <span id="ip"></span>
```php
[
    // 检查 "ip_address" 是否为一个有效的 IPv4 或 IPv6 地址
    ['ip_address', 'ip'],

    // 检查 "ip_address" 是否为一个有效的 IPv6 地址或子网地址，
    // 被检查的值将被展开成为一个完整的 IPv6 表示方法。
    ['ip_address', 'ip', 'ipv4' => false, 'subnet' => null, 'expandIPv6' => true],

    // 检查 "ip_address" 是否为一个有效的 IPv4 或 IPv6 地址，
    // 允许地址存在一个表示非的字符 `!`
    ['ip_address', 'ip', 'negation' => true],
]
```

此验证器检查属性的值是否是一个有效的 IPv4/IPv6 地址或子网地址。
如果标准记法或 IPv6 扩展记法被启用，原始值将被改变。

此验证器有以下参数：

- `ipv4`: 是否启用 IPv4 地址检测。默认为 true 。
- `ipv6`: 是否启用 IPv6 地址检测。默认为 true。
- `subnet`: 是否启用 CIDR 子网检测，类似 `192.168.10.0/24`
     * `true` - 子网是必须的，如果不是标准 CIDR 格式将被拒绝
     * `false` - 地址不能有 CIDR
     * `null` - CIDR 是可选的

    默认值为 false。
- `normalize`: 是否在地址没有 CIDR 时添加 CIDR 最小值作为后缀 (IPv4 为 32、IPv6 为 128)
仅当 `subnet` 不为 `false`时使用。例如：
    * `10.0.1.5` 将被转换为标准的`10.0.1.5/32`
    * `2008:db0::1` 将被转换为标准的 `2008:db0::1/128`

    默认为 false。
- `negation`: 是否允许被检测地址在首位存在代表非的字符 `!` 。默认为 false。
- `expandIPv6`: 是否将简化的 IPv6 地址扩展为标准记法格式。
例如， `2008:db0::1` 将被扩展成 `2008:0db0:0000:0000:0000:0000:0000:0001`。默认为 false。
- `ranges`: 允许或禁止的 IPv4 或 IPv6 范围的数组。

    当此数组为空或此参数没有设置时，所有IP地址都被允许。
    否则，将逐个对比此规则，当找到第一条适用时停止。
    如果没有任何规则适用待验证的IP地址，IP地址将被禁止。
    
    例如：
    ```php
    [
         'client_ip', 'ip', 'ranges' => [
             '192.168.10.128'
             '!192.168.10.0/24',
             'any' // 允许任何其它IP地址
         ]
    ]
    ```
在这个例子中，除了 `192.168.10.0/24` 子网之外的所有 IPv4 和 IPv6 地址都被允许。
IPv4 地址 `192.168.10.128` 同样时允许的，因为这条规则在约束规则之前适用。
- `networks`: 网络别名数组， 可以用在 `ranges` 中。数组格式：
    * key - 别名
    * value - 一组字符串。每个子字符串可以是一个范围，IP地址或另一个别名。子字符串也可以
    设置一个表示非的字符 `!` (和 `negation` 不相关)。

    下列别名为默认定义：
    
    * `*`: `any`
    * `any`: `0.0.0.0/0, ::/0`
    * `private`: `10.0.0.0/8, 172.16.0.0/12, 192.168.0.0/16, fd00::/8`
    * `multicast`: `224.0.0.0/4, ff00::/8`
    * `linklocal`: `169.254.0.0/16, fe80::/10`
    * `localhost`: `127.0.0.0/8', ::1`
    * `documentation`: `192.0.2.0/24, 198.51.100.0/24, 203.0.113.0/24, 2001:db8::/32`
    * `system`: `multicast, linklocal, localhost, documentation`

> Info: 此验证器自版本 2.0.7 后可用。

## [[yii\validators\RangeValidator|in（范围）]] <span id="in"></span>

```php
[
    // 检查 "level" 是否为 1、2 或 3 中的一个
    ['level', 'in', 'range' => [1, 2, 3]],
]
```

该验证器检查输入值是否存在于给定列表的范围之中。

- `range`：用于检查输入值的给定值列表。
- `strict`：输入值与给定值直接的比较是否为严格模式（也就是类型与值都要相同，即全等）。
  默认为 false。
- `not`：是否对验证的结果取反。默认为 false。当该属性被设置为 true，
  验证器检查输入值是否**不在**给定列表内。
- `allowArray`：是否接受输入值为数组。当该值为 true 且输入值为数组时，
  数组内的每一个元素都必须在给定列表内存在，否则返回验证失败。


## [[yii\validators\NumberValidator|integer（整数）]] <span id="integer"></span>

```php
[
    // 检查 "age" 是否为整数
    ['age', 'integer'],
]
```

该验证器检查输入值是否为整形。

- `max`：上限值（含界点）。若不设置，则验证器不检查上限。
- `min`：下限值（含界点）。若不设置，则验证器不检查下限。


## [[yii\validators\RegularExpressionValidator|match（正则表达式）]] <span id="match"></span>

```php
[
    // 检查 "username" 是否由字母开头，且只包含单词字符
    ['username', 'match', 'pattern' => '/^[a-z]\w*$/i']
]
```

该验证器检查输入值是否匹配指定正则表达式。

- `pattern`：用于检测输入值的正则表达式。该属性是必须的，
  若不设置则会抛出异常。
- `not`：是否对验证的结果取反。默认为 false，
  代表输入值匹配正则表达式时验证成功。如果设为 true，
  则输入值不匹配正则时返回匹配成功。


## [[yii\validators\NumberValidator|number（数字）]] <span id="number"></span>

```php
[
    // 检查 "salary" 是否为数字
    ['salary', 'number'],
]
```

该验证器检查输入值是否为数字。他等效于 [double](#double) 验证器。

- `max`：上限值（含界点）。若不设置，则验证器不检查上限。
- `min`：下限值（含界点）。若不设置，则验证器不检查下限。


## [[yii\validators\RequiredValidator|required（必填）]] <span id="required"></span>

```php
[
    // 检查 "username" 与 "password" 是否为空
    [['username', 'password'], 'required'],
]
```

该验证器检查输入值是否为空，还是已经提供了。

- `requiredValue`：所期望的输入值。若没设置，意味着输入不能为空。
- `strict`：检查输入值时是否检查类型。默认为 false。
  当没有设置 `requiredValue` 属性时，若该属性为 true，
  验证器会检查输入值是否严格为 null；若该属性设为 false，
  该验证器会用一个更加宽松的规则检验输入值是否为空。
  当设置了 `requiredValue` 属性时，若该属性为 true，输入值与 `requiredValue` 的比对会同时检查数据类型。

> Note: 如何判断待测值是否为空，被写在另外一个话题的
  [处理空输入](input-validation.md#handling-empty-inputs)章节。


## [[yii\validators\SafeValidator|safe（安全）]] <span id="safe"></span>

```php
[
    // 标记 "description" 为安全属性
    ['description', 'safe'],
]
```

该验证器并不进行数据验证。而是把一个属性标记为
[安全属性](structure-models.md#safe-attributes)。


## [[yii\validators\StringValidator|string（字符串）]] <span id="string"></span>

```php
[
    // 检查 "username" 是否为长度 4 到 24 之间的字符串
    ['username', 'string', 'length' => [4, 24]],
]
```

该验证器检查输入值是否为特定长度的字符串。并检查属性的值是否为某个特定长度。

- `length`：指定待测输入字符串的长度限制。
  该属性可以被指定为以下格式之一：
     * 证书：the exact length that the string should be of;
     * 单元素数组：代表输入字符串的最小长度 (e.g. `[8]`)。这会重写 `min` 属性。
     * 包含两个元素的数组：代表输入字符串的最小和最大长度(e.g. `[8, 128]`)。
     这会同时重写 `min` 和 `max` 属性。
- `min`：输入字符串的最小长度。若不设置，则代表不设下限。
- `max`：输入字符串的最大长度。若不设置，则代表不设上限。
- `encoding`：待测字符串的编码方式。若不设置，则使用应用自身的 [[yii\base\Application::charset|charset]] 属性值，
  该值默认为 `UTF-8`。


## [[yii\validators\FilterValidator|trim（译为修剪/裁边）]] <span id="trim"></span>

```php
[
    // trim 掉 "username" 和 "email" 两侧的多余空格
    [['username', 'email'], 'trim'],
]
```

该验证器并不进行数据验证。而是，trim 掉输入值两侧的多余空格。
注意若该输入值为数组，那它会忽略掉该验证器。


## [[yii\validators\UniqueValidator|unique（唯一性）]] <span id="unique"></span>

```php
[
    // a1 需要在 "a1" 属性所代表的字段内唯一
    ['a1', 'unique'],

    // a1 需要唯一，但检验的是 a1 的值在字段 a2 中的唯一性
    ['a1', 'unique', 'targetAttribute' => 'a2'],

    // a1 和 a2 的组合需要唯一，且它们都能收到错误提示
    [['a1', 'a2'], 'unique', 'targetAttribute' => ['a1', 'a2']],

    // a1 和 a2 的组合需要唯一，只有 a1 能接收错误提示
    ['a1', 'unique', 'targetAttribute' => ['a1', 'a2']],

    // 通过同时在 a2 和 a3 字段中检查 a2 和 a3 的值来确定 a1 的唯一性
    ['a1', 'unique', 'targetAttribute' => ['a2', 'a1' => 'a3']],
]
```

该验证器检查输入值是否在某表字段中唯一。
它只对[活动记录](db-active-record.md)类型的模型类属性起作用，
能支持对一个或多过字段的验证。

- `targetClass`：用于查找输入值的目标 [AR](db-active-record.md) 类。
  若不设置，则会使用正在进行验证的当前模型类。
- `targetAttribute`：用于检查输入值唯一性的 `targetClass` 的模型属性。
  若不设置，它会直接使用待测属性名（整个参数数组的首元素）。
  除了指定为字符串以外，你也可以用数组的形式，同时指定多个用于验证的表字段，数组的键和值都是代表字段的属性名，
  值表示 `targetClass` 的待测数据源字段，而键表示当前模型的待测属性名。
  若键和值相同，你可以只指定值。（如:`['a2']` 就代表 `['a2'=>'a2']`）
- `filter`：用于检查输入值唯一性必然会进行数据库查询，
  而该属性为用于进一步筛选该查询的过滤条件。可以为代表额外查询条件的字符串或数组
  （关于查询条件的格式，请参考 [[yii\db\Query::where()]]）；或者样式为 `function ($query)` 的匿名函数，
  `$query` 参数为你希望在该函数内进行修改的 [[yii\db\Query|Query]] 对象。


## [[yii\validators\UrlValidator|url（网址）]] <span id="url"></span>

```php
[
    // 检查 "website" 是否为有效的 URL。若没有 URI 方案，
    // 则给 "website" 属性加 "http://" 前缀
    ['website', 'url', 'defaultScheme' => 'http'],
]
```

该验证器检查输入值是否为有效 URL。

- `validSchemes`：用于指定那些 URI 方案会被视为有效的数组。默认为 `['http', 'https']`，
  代表 `http` 和 `https` URLs 会被认为有效。
- `defaultScheme`：若输入值没有对应的方案前缀，会使用的默认 URI 方案前缀。
  默认为 null，代表不修改输入值本身。
- `enableIDN`：验证过程是否应该考虑 IDN（internationalized domain names，国际化域名，也称多语种域名，比如中文域名）。
  默认为 false。要注意但是为使用 IDN 验证功能，
  请先确保安装并开启 `intl` PHP 扩展，不然会导致抛出异常。

> Note: 验证器检查 URL 结构和主机部分是否正确。
  它不会检查URL的其余部分，也不是为防止XSS或任何其他攻击而设计的。请参阅 [安全最佳实践](security-best-practices.md)
  有关开发应用程序时威胁防范的更多信息的文章。
