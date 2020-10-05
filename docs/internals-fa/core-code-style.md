رعایت اصول و سبک کدنویسی فریمورک Yii2
===============================
<p dir='rtl' align='right'>
سبک کدنویسی که در نسخه 2 فریمورک و extension های رسمی استفاده میشه دارای اصول، قواعد و قانون های خودش هست. پس اگر تصمیم دارید چیزی به هسته اضافه کنید باید این قواعد رو در نظر بگیرید حتی در غیر این صورت هم رعایت این موارد خالی از لطف نیست و توصیه میکنم این کارُ انجام بدین. در حالی که میتونید راحت باشید، شما مجبور به رعایت این سبک در application خودتون نیستید...
</p>
<p dir='rtl' align='right'>
میتونید برای دریافت پیکره بندی CodeSniffer اینجا رو مطالعه کنید: https://github.com/yiisoft/yii2-coding-standards
</p>

## 1. نگاه کلی
<p dir='rtl' align='right'>
به طور کلی ما از سبک PSR-2 استفاده میکنیم و هر چیزی که در این سبک وجود داره اینجا هم هست.
(https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md)</p>

<p dir='rtl' align='right'> در فایل ها باید از برچسب های php?> و =?> استفاده شود.</p>
<p dir='rtl' align='right'> در پایان هر فایل باید یک خط جدید(newline) داشته باشید.</p>
<p dir='rtl' align='right'> encoding فایل برای کد های php باید UTF-8 without BOM باشد.</p>
<p dir='rtl' align='right'>  به جای tab از 4 فضای خالی(space) استفاده کنید.</p>
<p dir='rtl' align='right'> نام کلاس ها باید به صورت StudlyCaps تعریف شوند.</p>
<p dir='rtl' align='right'> ثابت های داخل کلاس تماما باید با حروف بزرگ و گاهی با جداکننده "_" تعریف شوند.</p>
<p dir='rtl' align='right'> نام متد ها و پراپرتی ها باید به صورت camelCase تعریف شوند.</p>
<p dir='rtl' align='right'> پراپرتی های خصوصی(private) باید با "_" شروع شوند.</p>
<p dir='rtl' align='right'> همیشه از elseif جای else if استفاده کنید.</p>

## 2. فایل ها

<p dir='rtl' align='right'> در فایل ها باید از برچسب های php?> و =?> استفاده کرد نه از ?> .</p>
<p dir='rtl' align='right'> در انتهای فایل های php نباید از تگ <? استفاده کنید.</p>
<p dir='rtl' align='right'>  در انتهای هر خط نباید space وجود داشته باشد</p>
<p dir='rtl' align='right'> پسوند فایل هایی که شامل کد php هستند باید php. باشد.</p>
<p dir='rtl' align='right'> encoding فایل برای کد های php باید UTF-8 without BOM باشد.</p>


## 3. نام کلاس ها
<p dir='rtl' align='right'>
نام کلاس ها باید به صورت StudlyCaps تعریف شوند. به عنوان مثال, `Controller`, `Model`.</p>

## 4. کلاس ها

<p dir='rtl' align='right'> نام کلاس ها باید به صورت CamelCase تعریف شوند.</p>
<p dir='rtl' align='right'> آکولاد باز باید در خط بعدی، زیر نام کلاس نوشته شود.</p>
<p dir='rtl' align='right'> تمام کلاس ها باید بلاک مستندات مطابق استاندارد PHPDoc داشته باشند.</p>
<p dir='rtl' align='right'> برای تمام کد های داخل کلاس باید با 4 space فاصله ایجاد کنید.</p>
 <p dir='rtl' align='right'> فقط یک کلاس داخل هر فایل php باید موجود باشد.</p>
<p dir='rtl' align='right'> تمام کلاس ها باید namespaced داشته باشند.</p>
<p dir='rtl' align='right'> نام کلاس باید معال نام فایل و namespace باید مطابق مسیر آن باشد.</p>

```php
/**
 * Documentation
 */
class MyClass extends \yii\base\BaseObject implements MyInterface
{
    // code
}
```

### 4.1. ثابت ها
<p dir='rtl' align='right'>
ثابت های داخل کلاس تماما باید با حروف بزرگ و گاهی با جداکننده "_" تعریف شوند.<p>

```php
<?php
class Foo
{
    const VERSION = '1.0';
    const DATE_APPROVED = '2012-06-01';
}
```
### 4.2. پراپرتی ها

<p dir='rtl' align='right'> از کلید واژه های public، protected و private استفاده کنید.</p>
<p dir='rtl' align='right'> پراپرتی های public و protected باید در بالای کلاس و قبل از متد ها تعریف شوند. private هم همینطور اما ممکن هست کاهی قبل از متدی که با آن مرتبط هست آورده شود.</p>
<p dir='rtl' align='right'> ترتیب تعریف پراپرتی ها باید به صورت اول public، دوم protected و سپس private باشد! هیچ قانون سختی برای رعایت این مورد نیست...</p>
<p dir='rtl' align='right'> برای خوانایی بهتر میتونید از خط خالی بین گروه های public، protected و private استفاده کنید.
<p dir='rtl' align='right'> متغییر های private باید مثل varName_$ باشند.</p>
<p dir='rtl' align='right'> اعضای عمومی داخل کلاس باید به صورت camelCase تعریف شوند.(حرف اول کوچک، با CamelCase فرق میکنه).</p>
<p dir='rtl' align='right'> بهتره از نام هایی مثل i$ و j$ استفاده نکنید.</p>

```php
<?php
class Foo
{
    public $publicProp1;
    public $publicProp2;

    protected $protectedProp;

    private $_privateProp;


    public function someMethod()
    {
        // ...
    }
}
```

### 4.3. متد ها

<p dir='rtl' align='right'> توابع و متد ها باید camelCase باشند.</p>
<p dir='rtl' align='right'> نام باید هدف رو نشون بده.</p>
<p dir='rtl' align='right'> از کلید واژه های public، protected و private استفاده کنید.</p>
<p dir='rtl' align='right'> آکولاد باز باید در خط بعدی یعنی زیر نام متد قرار بگیره.</p>

```php
/**
 * Documentation
 */
class Foo
{
    /**
     * Documentation
     */
    public function bar()
    {
        // code
        return $value;
    }
}
```

### 4.4 بلوک های PHPDoc

<p dir='rtl' align='right'> برای متد ها باید مستندات بنویسید(PHPDoc).</p>
<p dir='rtl' align='right'> در PHPDoc نوع param@, var@, property@ و return@ باید مشخص شود(bool, int, string, array یا null).</p>
<p dir='rtl' align='right'> برای تایپ آرایه در PHPDoc از []ClassName استفاده کنید.</p>
<p dir='rtl' align='right'> خط اول PHPDoc باید هدف یک متد رو شرح بده.</p>
<p dir='rtl' align='right'> اگر متد ها چیزی رو بررسی میکنن مثل isActive بخش PHPDoc رو باید با عبارت Checks whether شروع کنید.</p>
<p dir='rtl' align='right'> return@ در PHPDoc  یاید دقیقا مشخص کنه چی بازگردانده میشود.</p>

```php
/**
 * Checks whether the IP is in subnet range
 *
 * @param string $ip an IPv4 or IPv6 address
 * @param int $cidr the CIDR lendth
 * @param string $range subnet in CIDR format e.g. `10.0.0.0/8` or `2001:af::/64`
 * @return bool whether the IP is in subnet range
 */
 private function inRange($ip, $cidr, $range)
 {
   // ...
 }
```

### 4.5 Constructors

<p dir='rtl' align='right'> `__construct` باید به جای استایل PHP 4 constructors استفاده شود.</p>

## 5 PHP

### 5.1 نوع ها

<p dir='rtl' align='right'> تمام انواع و مقادیر باید با حروف کوچک نوشته شوند مثل true, false, null و array.</p>
<p dir='rtl' align='right'> تغییر نوع یک متغییر خیلی بده، به این مثال توجه کنید:</p>


```php
public function save(Transaction $transaction, $argument2 = 100)
{
    $transaction = new Connection; // bad
    $argument2 = 200; // good
}
```

### 5.2 رشته ها

<p dir='rtl' align='right'> اگر رشته ی شما شامل متغییر های دیگه این نیست از تک کوتیشن جای دابل کوتیشن استفاده کنید.</p>

```php
$str = 'Like this.';
```

<p dir='rtl' align='right'> دو روش زیر مناسب برای جایگزینی هستند:</p>

```php
$str1 = "Hello $username!";
$str2 = "Hello {$username}!";
```
<p dir='rtl' align='right'>
حالت زی مجاز نیست:</p>

```php
$str3 = "Hello ${username}!";
```

#### الحاق

<p dir='rtl' align='right'> برای الحاق قبل و بعد کاراکتر dot فاصله بذارید</p>

```php
$name = 'Yii' . ' Framework';
```

<p dir='rtl' align='right'> و اگر رشته ی شما بلند بود میتونید اینطور عمل کنید:</p>

```php
$sql = "SELECT *"
    . "FROM `post` "
    . "WHERE `id` = 121 ";
```

### 5.3 آرایه ها

<p dir='rtl' align='right'> برای تعریف آرایه ها از نحوه ی کوتاه اون یعنی [] استفاده کنید.</p>
<p dir='rtl' align='right'> از ایندکس منفی در آرایه ها استفاده نکنید.</p>
<p dir='rtl' align='right'> روش های زیر قابل قبول و مناسب هستند:</p>

```php
$arr = [3, 14, 15, 'Yii', 'Framework'];
```

```php
$arr = [
    3, 14, 15,
    92, 6, $test,
    'Yii', 'Framework',
];
```

```php
$config = [
    'name' => 'Yii',
    'options' => ['usePHP' => true],
];
```

### 5.4 دستورات کنترلی

<p dir='rtl' align='right'> در دستورات کنترلی قبل و بعد پرانتز space بذارید.</p>
<p dir='rtl' align='right'> آکولاد باز در همان خط دستور قرار میگیرد.</p>
<p dir='rtl' align='right'> آکولاد بسته در خط جدید.</p>
<p dir='rtl' align='right'> برای دستورات یک خطی همیشه از پرانتز استفاده کنید.</p>

```php
if ($event === null) {
    return new Event();
}
if ($event instanceof CoolEvent) {
    return $event->instance();
}
return null;


// the following is NOT allowed:
if (!$model && null === $event)
    throw new Exception('test');
```

<p dir='rtl' align='right'>بعد از return از else استفاده نکنید</p>

```php
$result = $this->getResult();
if (empty($result)) {
    return true;
} else {
    // process result
}
```
<p dir='rtl' align='right'>اینطوری بهتره</p>


```php
$result = $this->getResult();
if (empty($result)) {
   return true;
}

// process result
```

#### switch

<p dir='rtl' align='right'> از فرمت زیر برای switch استفاده کنید
</p>
```php
switch ($this->phpType) {
    case 'string':
        $a = (string) $value;
        break;
    case 'integer':
    case 'int':
        $a = (int) $value;
        break;
    case 'boolean':
        $a = (bool) $value;
        break;
    default:
        $a = null;
}
```

### 5.5 function calls

<p dir='rtl' align='right'>روش مناسب صدا زدن توابع همراه با پارامتر ها هم اینطور صحیحه</p>

```php
doIt(2, 3);

doIt(['a' => 'b']);

doIt('a', [
    'a' => 'b',
    'c' => 'd',
]);
```

### 5.6  تعریف Anonymous functions (lambda)

<p dir='rtl' align='right'> در توابع بی نام بین function/use فضای خالی(space) بذارید.</p>

```php
// good
$n = 100;
$sum = array_reduce($numbers, function ($r, $x) use ($n) {
    $this->doMagic();
    $r += $x * $n;
    return $r;
});

// bad
$n = 100;
$mul = array_reduce($numbers, function($r, $x) use($n) {
    $this->doMagic();
    $r *= $x * $n;
    return $r;
});
```

مستند نویسی 
-------------

<p dir='rtl' align='right'> [phpDoc](http://phpdoc.org/) رو بخونید و موارد اونُ رعایت کنید.</p>
<p dir='rtl' align='right'> کد بدون مستندات مجاز نیست.</p>
<p dir='rtl' align='right'> تمام کلاس ها باید شامل بلاک مستندات در ابتدای فایل باشند.</p>
<p dir='rtl' align='right'> نیازی به نوشتن return@ ندارید اگر متد شما اگر چیزی را برنمیگرداند.</p>
<p dir='rtl' align='right'> به مثال های زیر توجه کنید:</p>

  ```php
    <?php
    /**
     * Returns the errors for all attribute or a single attribute.
     * @param string $attribute attribute name. Use null to retrieve errors for all attributes.
     * @property array An array of errors for all attributes. Empty array is returned if no error.
     * The result is a two-dimensional array. See [[getErrors()]] for detailed description.
     * @return array errors for all attributes or the specified attribute. Empty array is returned if no error.
     * Note that when returning errors for all attributes, the result is a two-dimensional array, like the following:
     * ...
     */
    public function getErrors($attribute = null)
  ```

#### فایل

```php
<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */
```

#### کلاس

```php
/**
 * Component is the base class that provides the *property*, *event* and *behavior* features.
 *
 * @include @yii/docs/base-Component.md
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Component extends \yii\base\BaseObject
```


#### توابع / متد

```php
/**
 * Returns the list of attached event handlers for an event.
 * You may manipulate the returned [[Vector]] object by adding or removing handlers.
 * For example,
 *
 * ```
 * $component->getEventHandlers($eventName)->insertAt(0, $eventHandler);
 * ```
 *
 * @param string $name the event name
 * @return Vector list of attached event handlers for the event
 * @throws Exception if the event is not defined
 */
public function getEventHandlers($name)
{
    if (!isset($this->_e[$name])) {
        $this->_e[$name] = new Vector;
    }
    $this->ensureBehaviors();
    return $this->_e[$name];
}
```

#### نظرات

<p dir='rtl' align='right'> از // برای کامنت گذاری استفاده کنید نه از #.</p>
<p dir='rtl' align='right'> در خطوطی که کامنت گذاشتین نباید کد بنویسید، یعنی اون خط برای اون کامنت باید باشه.</p>

قوانین بیشتر
----------------

<p dir='rtl' align='right'> تا جایی که میتونید از تابع empty به جای === استفاده کنید.</p>
<p dir='rtl' align='right'> اگر شرایط تو در تویی در کد شما وجود نداره return زود هنگام یا ساده تر بگم return وسط متد مشکلی نخواهد داشت.</p>
<p dir='rtl' align='right'> همیشه از static جای self به جز موارد زیر استفاده کنید:</p>
<p dir='rtl' align='right'>1) دسترسی به ثابت ها باید با self انجام بشه.</p>
<p dir='rtl' align='right'>2) دسترسی به پراپرتی های خصوصی باید با self انجام بشه.</p>
<p dir='rtl' align='right'>3) مجاز به استفاده از self برای صدا زدن توابع در مواقعی مثل فراخوانی بازگشتی هستید.</p>


namespace ها
----------------
<p dir='rtl' align='right'> از حرف کوچک استفاده کنید.</p>
<p dir='rtl' align='right'> از فرم جمع اسم ها برای نشان دادن یک شی استفاده کنید مثل validators.</p>
<p dir='rtl' align='right'> از فرم منفرد اسم ها برای قابلیت ها و امکانات استفاده کنید مثل web.</p>
<p dir='rtl' align='right'> بهتره فضای نام تک کلمه ای باشه در غیر این صورت از camelCase استفاده کنید.</p>

