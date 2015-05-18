Url хелпер
==========

Url хелпер предоставляет набор статических методов для управления урлами.


## Получение общих урлов <span id="getting-common-urls"></span>

Вы можете использовать два метода получения общих урлов: домашний Урл (Home) и базовый Урл (Base) текущего запроса.
Используйте следующий код, чтобы получить домашний Урл:

```php
$relativeHomeUrl = Url::home();
$absoluteHomeUrl = Url::home(true);
$httpsAbsoluteHomeUrl = Url::home('https');
```

Если вы не передали параметров, то получите относительный урл. Вы можете передать `true`, чтобы получить абсолютный урл
для текущего протокола или явно указать протокол (`https`, `http`).

Чтобы получить базовый Урл текущего запроса:

```php
$relativeBaseUrl = Url::base();
$absoluteBaseUrl = Url::base(true);
$httpsAbsoluteBaseUrl = Url::base('https');
```

Единственный параметр данного метода работает также как и `Url::home()`.

## Создание Урлов <span id="creating-urls"></span>

Чтобы создать урл для соответствующего роута используйте метод `Url::toRoute()`. Метод использует [[\yii\web\UrlManager]].
Для того чтобы создать урл:

```php
$url = Url::toRoute(['product/view', 'id' => 42]);
```
 
Вы можете задать роут строкой, например, `site/index`. А также вы можете использовать массив, если хотите задать
дополнительные параметры запроса для урла. Формат массива должен быть следующим:

```php
// сгенерирует: /index.php?r=site/index&param1=value1&param2=value2
['site/index', 'param1' => 'value1', 'param2' => 'value2']
```

Если вы хотите создать урл с якорем, то вы можете использовать параметр массива с ключом `#`. Например:

```php
// сгенерирует: /index.php?r=site/index&param1=value1#name
['site/index', 'param1' => 'value1', '#' => 'name']
```

Роут может быть и абсолютным, и относительным. Абсолютный урл начинается со слеша (например, `/site/index`),
относительный - без (например, `site/index` or `index`). Относительный урл будет сконвертирован в абсолютный по следующим
правилам:

- Если роут пустая строка, то будет использовано текущее значение [[\yii\web\Controller::route|route]];
- Если роут не содержит слешей (например, `index`), то он будет считаться экшеном текущего контролера и будет определен
  с помощью [[\yii\web\Controller::uniqueId]];
- Если роут начинается не со слеша (например, `site/index`), то он будет считаться относительным роутом текущего модуля
  и будет определен с помощью [[\yii\base\Module::uniqueId|uniqueId]].

Начиная с версии 2.0.2, вы можете задавать роуты с помощью [псевдонимов](concept-aliases.md). В этом случае, сначала
псевдоним будет сконвертирован в соответсвующий роут, который будет преобразован в абсолютный в соответсвии с вышеописанными
правилами.

Примеры использования метода:

```php
// /index.php?r=site/index
echo Url::toRoute('site/index');

// /index.php?r=site/index&src=ref1#name
echo Url::toRoute(['site/index', 'src' => 'ref1', '#' => 'name']);

// /index.php?r=post/edit&id=100     псевдоним "@postEdit" задан как "post/edit"
echo Url::toRoute(['@postEdit', 'id' => 100]);

// http://www.example.com/index.php?r=site/index
echo Url::toRoute('site/index', true);

// https://www.example.com/index.php?r=site/index
echo Url::toRoute('site/index', 'https');
```

Другой метод `Url::to()` очень похож на [[toRoute()]]. Единственное отличие: входным параметром должен быть массив.
Если будет передана строка, то она будет воспринята как урл.

Первый аргумент может быть:

- массивом: будет вызван [[toRoute()]], чтобы сгенерировать урл. Например: `['site/index']`, `['post/index', 'page' => 2]`.
  В разделе [[toRoute()]] подробно описано как задавать роут;
- Строка, начинающася с `@`, будет обработана как псевдоним. Будет возвращено соответствующее значение псевдонима;
- Пустая строка: вернет текущий урл;
- Обычная строка: вернет строку без имзенений

Когда у метода задан второй параметр `$scheme` (строка или true), то сгенерированный урл будет с протоколом
(полученным из [[\yii\web\UrlManager::hostInfo]]). Если в `$url` указан протокол, то его значение будет заменено.

Пример использования:

```php
// /index.php?r=site/index
echo Url::to(['site/index']);

// /index.php?r=site/index&src=ref1#name
echo Url::to(['site/index', 'src' => 'ref1', '#' => 'name']);

// /index.php?r=post/edit&id=100     псевдоним "@postEdit" задан как "post/edit"
echo Url::to(['@postEdit', 'id' => 100]);

// Текущий урл
echo Url::to();

// /images/logo.gif
echo Url::to('@web/images/logo.gif');

// images/logo.gif
echo Url::to('images/logo.gif');

// http://www.example.com/images/logo.gif
echo Url::to('@web/images/logo.gif', true);

// https://www.example.com/images/logo.gif
echo Url::to('@web/images/logo.gif', 'https');
```

Начиная с версии 2.0.3, вы можете использовать [[yii\helpers\Url::current()]], чтобы создавать урл на основе текущего
запрошенного роута и его GET-параметров. Вы можете изменить, удалить или добавить новые GET-параметры передав в метод
параметр `$params`. Например:

```php
// предположим $_GET = ['id' => 123, 'src' => 'google'], а текущий роут "post/view"

// /index.php?r=post/view&id=123&src=google
echo Url::current();

// /index.php?r=post/view&id=123
echo Url::current(['src' => null]);
// /index.php?r=post/view&id=100&src=google
echo Url::current(['id' => 100]);
```


## Запоминание урлов <span id="remember-urls"></span>

Существуют задачи, когда вам необходимо запомнить урл и потом использовать его в процессе одного или нескольких
последовательных запросов. Это может быть достигнуто следующим образом:

```php
// Запомнить текущий урл
Url::remember();

// Запомнить определенный урл. Входные параметры смотрите на примере Url::to().
Url::remember(['product/view', 'id' => 42]);

// Запомнить урл под определенным именем
Url::remember(['product/view', 'id' => 42], 'product');
```

В следующем запросе мы можем получить сохраненный урл следующим образом:

```php
$url = Url::previous();
$productUrl = Url::previous('product');
```
                        
## Проверить относительность урла <span id="checking-relative-urls"></span>

Чтобы проверить относительный урл или нет (например, если в нем не содержится информации о хосте), вы можете использовать
следующий код:

```php
$isRelative = Url::isRelative('test/it');
```
