Url хелпер
==========

Url хелпер предоставляет набор статических методов для управления URL.


## Получение общих URL <span id="getting-common-urls"></span>

Вы можете использовать два метода получения общих URL: домашний URL (Home) и базовый URL (Base) текущего запроса.
Используйте следующий код, чтобы получить домашний URL:

```php
$relativeHomeUrl = Url::home();
$absoluteHomeUrl = Url::home(true);
$httpsAbsoluteHomeUrl = Url::home('https');
```

Если вы не передали параметров, то получите относительный URL. Вы можете передать `true`, чтобы получить абсолютный URL
для текущего протокола или явно указать протокол (`https`, `http`).

Чтобы получить базовый URL текущего запроса:

```php
$relativeBaseUrl = Url::base();
$absoluteBaseUrl = Url::base(true);
$httpsAbsoluteBaseUrl = Url::base('https');
```

Единственный параметр данного метода работает также как и `Url::home()`.

## Создание URL <span id="creating-urls"></span>

Чтобы создать URL для соответствующего роута используйте метод `Url::toRoute()`. Метод использует [[\yii\web\UrlManager]].
Для того чтобы создать URL:

```php
$url = Url::toRoute(['product/view', 'id' => 42]);
```
 
Вы можете задать роут строкой, например, `site/index`. А также вы можете использовать массив, если хотите задать
дополнительные параметры запроса для URL. Формат массива должен быть следующим:

```php
// сгенерирует: /index.php?r=site/index&param1=value1&param2=value2
['site/index', 'param1' => 'value1', 'param2' => 'value2']
```

Если вы хотите создать URL с якорем, то вы можете использовать параметр массива с ключом `#`. Например:

```php
// сгенерирует: /index.php?r=site/index&param1=value1#name
['site/index', 'param1' => 'value1', '#' => 'name']
```

Роут может быть и абсолютным, и относительным. Абсолютный URL начинается со слеша (например, `/site/index`),
относительный - без (например, `site/index` or `index`). Относительный URL будет сконвертирован в абсолютный по следующим
правилам:

- Если роут пустая строка, то будет использовано текущее значение [[\yii\web\Controller::route|route]];
- Если роут не содержит слешей (например, `index`), то он будет считаться экшеном текущего контролера и будет определен
  с помощью [[\yii\web\Controller::uniqueId]];
- Если роут начинается не со слеша (например, `site/index`), то он будет считаться относительным роутом текущего модуля
  и будет определен с помощью [[\yii\base\Module::uniqueId|uniqueId]].

Начиная с версии 2.0.2, вы можете задавать роуты с помощью [псевдонимов](concept-aliases.md). В этом случае, сначала
псевдоним будет сконвертирован в соответствующий роут, который будет преобразован в абсолютный в соответствии с вышеописанными
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
Если будет передана строка, то она будет воспринята как URL.

Первый аргумент может быть:

- массивом: будет вызван [[toRoute()]], чтобы сгенерировать URL. Например: `['site/index']`, `['post/index', 'page' => 2]`.
  В разделе [[toRoute()]] подробно описано как задавать роут;
- Строка, начинающаяся с `@`, будет обработана как псевдоним. Будет возвращено соответствующее значение псевдонима;
- Пустая строка: вернет текущий URL;
- Обычная строка: вернет строку без изменений

Когда у метода задан второй параметр `$scheme` (строка или true), то сгенерированный URL будет с протоколом
(полученным из [[\yii\web\UrlManager::hostInfo]]). Если в `$url` указан протокол, то его значение будет заменено.

Пример использования:

```php
// /index.php?r=site/index
echo Url::to(['site/index']);

// /index.php?r=site/index&src=ref1#name
echo Url::to(['site/index', 'src' => 'ref1', '#' => 'name']);

// /index.php?r=post/edit&id=100     псевдоним "@postEdit" задан как "post/edit"
echo Url::to(['@postEdit', 'id' => 100]);

// Текущий URL
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

Начиная с версии 2.0.3, вы можете использовать [[yii\helpers\Url::current()]], чтобы создавать URL на основе текущего
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


## Запоминание URL <span id="remember-urls"></span>

Существуют задачи, когда вам необходимо запомнить URL и потом использовать его в процессе одного или нескольких
последовательных запросов. Это может быть достигнуто следующим образом:

```php
// Запомнить текущий URL
Url::remember();

// Запомнить определенный URL. Входные параметры смотрите на примере Url::to().
Url::remember(['product/view', 'id' => 42]);

// Запомнить URL под определенным именем
Url::remember(['product/view', 'id' => 42], 'product');
```

В следующем запросе мы можем получить сохраненный URL следующим образом:

```php
$url = Url::previous();
$productUrl = Url::previous('product');
```
                        
## Проверить относительность URL <span id="checking-relative-urls"></span>

Чтобы проверить относительный URL или нет (например, если в нем не содержится информации о хосте), вы можете использовать
следующий код:

```php
$isRelative = Url::isRelative('test/it');
```
