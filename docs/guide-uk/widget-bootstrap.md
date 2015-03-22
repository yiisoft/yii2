Bootstrap Віджет
=================

> Замітка: Цей розділ знаходиться на стадії розробки.

З коробки Yii включає підтримку [Bootstrap 3](http://getbootstrap.com/) розмітку і компоненти фреймворку
(також відомий як "Twitter Bootstrap"). Bootstrap є чудовим, *адаптивним* фреймворком, що може значно прискорити
процес розробки клієнтської частини сайту.

Ядро Bootstrap представлене двома частинами:

- Основи CSS, такі як система макету сітки, типографія, допоміжні класи і *адаптивні* утиліти.
- Готові до використання компоненти, такі як форми, меню, *нумерація сторінок*, модальні вікна, *вкладки (таблиці)* і т.д.

Основи
------

*Yii не містить bootstrap основ початкового завантаження PHP коду з HTML є дуже простий сам по собі в цьому випадку.*
Ви можете знайти детальну інформацію про використання основ в [bootstrap documentation website](http://getbootstrap.com/css/).
Про те Yii забезпечує зручний спосіб підключення bootstrap *активів* на ваших сторінкаї з доданням всього одного рядка в `AppAsset.php`
файлі розташованого в каталозі `@app/assets`:

```php
public $depends = [
    'yii\web\YiiAsset',
    'yii\bootstrap\BootstrapAsset', // цей рядок
];
```

Використання bootstrap через менеджер завантаження активів Yii дозволяє звести до мінімуму *свої* ресурси і об'єднати
з вашими власними ресурсами коли це необхідно.

Yii віджет
-----------

Найбільш складні компоненти bootstrap загорнуті в Yii віджети, щоб забезпечити більш надійний синтаксис та інтегрувати його з
можливостями фреймворку. Всі віджети належать до простору імен `\yii\bootstrap`:

- [[yii\bootstrap\ActiveForm|ActiveForm]]
- [[yii\bootstrap\Alert|Alert]]
- [[yii\bootstrap\Button|Button]]
- [[yii\bootstrap\ButtonDropdown|ButtonDropdown]]
- [[yii\bootstrap\ButtonGroup|ButtonGroup]]
- [[yii\bootstrap\Carousel|Carousel]]
- [[yii\bootstrap\Collapse|Collapse]]
- [[yii\bootstrap\Dropdown|Dropdown]]
- [[yii\bootstrap\Modal|Modal]]
- [[yii\bootstrap\Nav|Nav]]
- [[yii\bootstrap\NavBar|NavBar]]
- [[yii\bootstrap\Progress|Progress]]
- [[yii\bootstrap\Tabs|Tabs]]

*Використання .less файлів в Bootstrap*
-------------------------------------------

Якщо ви хочете включити [Bootstrap css directly in your less files](http://getbootstrap.com/getting-started/#customizing)
вам необхідно відключити завантаження оригінальних bootstrap css файлів.
Ви можете зробити це встановивши CSS властивість [[yii\bootstrap\BootstrapAsset|BootstrapAsset]] порожньою.
Для цього вам необхідно налаштувати `assetManager` [application component](structure-application-components.md) наступним чином:

```php
    'assetManager' => [
        'bundles' => [
            'yii\bootstrap\BootstrapAsset' => [
                'css' => [],
            ]
        ]
    ]
```