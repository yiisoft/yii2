Віджети Bootstrap
=================

> Примітка: Цей розділ знаходиться на стадії розробки.

З коробки Yii включає підтримку розмітки [Bootstrap 3](http://getbootstrap.com/) та компонентів фреймворку
(також відомий як "Twitter Bootstrap"). Bootstrap є чудовим, адаптивним фреймворком, який може значно прискорити
процес розробки клієнтської частини сайту.

Ядро Bootstrap представлене двома частинами:

- Основи CSS, такі як система макету сітки, типографія, допоміжні класи і адаптивні утиліти.
- Готові до використання компоненти, такі як форми, меню, нумерація сторінок (pagination), модальні вікна, вкладки (tabs) і т.д.

Основи
------

Yii не загортає основи bootstrap в код PHP, оскільки в цьому випадку, сам по собі HTML є дуже простим.
Ви можете знайти детальну інформацію про використання основ на [сайті документації bootstrap](http://getbootstrap.com/css/).
Проте Yii забезпечує зручний спосіб підключення ресурсів bootstrap до ваших сторінок за допомогою всього одного рядка до
файла `AppAsset.php`, розташованого в каталозі `@app/assets`:

```php
public $depends = [
    'yii\web\YiiAsset',
    'yii\bootstrap\BootstrapAsset', // цей рядок
];
```

Використання bootstrap через менеджер ресурсів Yii дозволяє мінімізувати свої ресурси і обʼєднати із вашими власними
ресурсами коли це необхідно.

Віджети Yii
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

Якщо ви хочете включити [CSS Bootstrap напряму до ваших less файлів](http://getbootstrap.com/getting-started/#customizing)
вам необхідно відключити завантаження оригінальних bootstrap css файлів.
Ви можете зробити це, встановивши CSS властивість [[yii\bootstrap\BootstrapAsset|BootstrapAsset]] порожньою.
Для цього вам необхідно налаштувати [компонент додатка](structure-application-components.md) `assetManager` наступним чином:

```php
    'assetManager' => [
        'bundles' => [
            'yii\bootstrap\BootstrapAsset' => [
                'css' => [],
            ]
        ]
    ]
```