Темизация
=========

Темизация — это способ заменить один набор [представлений](structure-views.md) другим без переписывания кода, что
замечательно подходит для изменения внешнего вида приложения.

Для того, чтобы начать использовать темизацию, настройте свойство [[yii\base\View::theme|theme]] компонента
приложения `view`. Конфигурация настраивает объект [[yii\base\Theme]], который отвечает за то, как как именно
заменяются файлы отображений. Главным образом, стоит настроить следующие свойства [[yii\base\Theme]]:

- [[yii\base\Theme::basePath]]: базовая директория, в которой размещены темизированные ресурсы (CSS, JS, изображения,
  и так далее).
- [[yii\base\Theme::baseUrl]]: базовый URL для доступа к темизированным ресурсам.
- [[yii\base\Theme::pathMap]]: правила замены файлов представлений. Подробно описаны далее.
 
Например, если вы вызываете `$this->render('about')` в `SiteController`, то будет использоваться файл отображения
`@app/views/site/about.php`. Тем не менее, если вы включите темизацию как показано ниже, то вместо него будет
использоваться `@app/themes/basic/site/about.php`. 

```php
return [
    'components' => [
        'view' => [
            'theme' => [
                'basePath' => '@app/themes/basic',
                'baseUrl' => '@web/themes/basic',
                'pathMap' => [
                    '@app/views' => '@app/themes/basic',
                ],
            ],
        ],
    ],
];
```

> Информация: При настройке тем поддерживаются псевдонимы пути. При замене отображений они преобразуются в реальные
  пути в файловой системе или URL.

Вы можете обратиться к объекту [[yii\base\Theme]] через свойство [[yii\base\View::theme]]. Например,
в файле отображения, это будет выглядеть следующим образом (объект view доступен как `$this`):

```php
$theme = $this->theme;

// returns: $theme->baseUrl . '/img/logo.gif'
$url = $theme->getUrl('img/logo.gif');

// returns: $theme->basePath . '/img/logo.gif'
$file = $theme->getPath('img/logo.gif');
```

Свойство [[yii\base\Theme::pathMap]] определяет то, как заменяются файлы представлений. Свойство принимает массив пар 
ключ-значение где ключи являются путями к оригинальным файлам, которые мы хотим заменить, а значения — соответствующими 
путями к файлам из темы. Замена основана на частичном совпадении: если путь к представлению начинается с любого из ключей 
массива [[yii\base\Theme::pathMap|pathMap]], то соответствующая ему часть будет заменена значением из того же массива.
Для приведённой выше конфигурации `@app/views/site/about.php` частично совпадает с ключом `@app/views` и будет
заменён на `@app/themes/basic/site/about.php`.


## Темизация модулей <span id="theming-modules"></span>

Для того, чтобы темизировать модули, свойство [[yii\base\Theme::pathMap]] может быть настроено следующим образом:

```php
'pathMap' => [
    '@app/views' => '@app/themes/basic',
    '@app/modules' => '@app/themes/basic/modules', // <-- !!!
],
```

Это позволит вам темизировать `@app/modules/blog/views/comment/index.php` в `@app/themes/basic/modules/blog/views/comment/index.php`.


## Темизация виджетов <span id="theming-widgets"></span>

Для того, чтобы темизировать виджеты вы можете настроить свойство [[yii\base\Theme::pathMap]] следующим образом:

```php
'pathMap' => [
    '@app/views' => '@app/themes/basic',
    '@app/widgets' => '@app/themes/basic/widgets', // <-- !!!
],
```

Это позволит вам темизировать `@app/widgets/currency/views/index.php` в `@app/themes/basic/widgets/currency/index.php`.


## Наследование тем <span id="theme-inheritance"></span>

Иногда требуется создать базовую тему, задающую общий вид приложения и далее изменять этот вид в зависимости, например,
от сегодняшнего праздника. Добиться этого можно при помощи наследования тем. При этом один путь к файлу ставится в 
соответствие нескольким путям из темы:

```php
'pathMap' => [
    '@app/views' => [
        '@app/themes/christmas',
        '@app/themes/basic',
    ],
]
```

В этом случае представление `@app/views/site/about.php` темизируется либо в `@app/themes/christmas/site/index.php`, 
либо в `@app/themes/basic/site/index.php` в зависимости от того, в какой из тем есть нужный файл. Если файлы присутствуют
и там и там, используется первый из них. На практике большинство темизированных файлов будут расположены
в `@app/themes/basic`, а их версии для праздников в `@app/themes/christmas`.
