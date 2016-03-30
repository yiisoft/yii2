Хелперы
=======

> Note: Этот раздел находиться в стадии разработки.

Yii предоставляет много классов, которые помогают упростить общие задачи программирования, такие как манипуляция со строками или массивами, генерация HTML кода, и так далее. Все helper классы организованны в рамках пространства имен `yii\helpers` и являются статическими методами
 (это означает, что они содержат в себе только статические свойства и методы и объекты статического класса создать нельзя).

Вы можете использовать helper класс с помощью вызова одного из статических методов, как показано ниже:

```php
use yii\helpers\Html;

echo Html::encode('Test > test');
```

> Note: Помощь в [настройке helper классов](#customizing-helper-classes), в Yii каждый основной helper состоит из двух классов: базовый класс (например `BaseArrayHelper`) и конкретный класс (например `ArrayHelper`).
  Когда вы используете helper, вы должны использовать только конкретные версии классов и никогда не испольовать базовые классы.


Встроенные хелперы
------------------

В этой версии Yii предоставлются следующие основные helper классы:

- [ArrayHelper](helper-array.md)
- Console
- FileHelper
- FormatConverter
- [Html](helper-html.md)
- HtmlPurifier
- Imagine (provided by yii2-imagine extension)
- Inflector
- Json
- Markdown
- StringHelper
- [Url](helper-url.md)
- VarDumper


Настройка хелперов <span id="customizing-helper-classes"></span>
--------------------------

Для настройки основных helper классов (например [[yii\helpers\ArrayHelper]]), вы должны создать расширяющийся класс из помощников соотвествующих базовых классов (например [[yii\helpers\BaseArrayHelper]]) и дать похожее название, вашему классу, с соотвествующим конкретному классу (например [[yii\helpers\ArrayHelper]]), в том числе его пространство имен. Тогда созданный класс заменит оригинальную реальзацию в фреимворке.

В следующих примерах показывается как настроить [[yii\helpers\ArrayHelper::merge()|merge()]] метод
[[yii\helpers\ArrayHelper]] класса:

```php
<?php

namespace yii\helpers;

class ArrayHelper extends BaseArrayHelper
{
    public static function merge($a, $b)
    {
        // your custom implementation
    }
}
```

Сохраните ваш класс в файле с именем `ArrayHelper.php`. Файл должен находиться в другой директории, например `@app/components`.

Далее, в приложении [входной скрипт](structure-entry-scripts.md), добавьте следующую строчку кода
после подключения `yii.php` файла, которая сообщит [автозагрузка классов Yii](concept-autoloading.md) загрузить
ваш класс вместо оригинального helper класса фреимворка:

```php
Yii::$classMap['yii\helpers\ArrayHelper'] = '@app/components/ArrayHelper.php';
```

Обратите внимание что пользовательская настройка helper классов полезна только, если вы хотите изменить поведение существующей функции helper классов. Если вы хотите добавить дополнительные функции, для использования в вашем приложении, будет лучше создать отдельный helper.
