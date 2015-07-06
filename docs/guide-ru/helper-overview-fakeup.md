Помощник (Helper, Хелпер)
======= 


>Примечание: Секция в стадиии разработки.

В Yii представлено множество классов, которые помогают упростить процесс выполнения типовых задач кодирования. Например, операции над строками и массивами или генерирование кода в HTML. Эти классы (хелперы) организованы в пространстве имён `yii\helpers` и все являются статическими (т.е. могут содержать только статические свойства и методы, а следовательно - не будут подвержены обработке).

Хелперы используются во время прямого обращения к ним по ключевым словам. Например: 

```php
use yii\helpers\Html;

echo Html::encode('Test > test');
```

> Note: Для поддержки [customizing helper classes](#customizing-helper-classes) Yii декомпозирует каждый основной (core) хелпер-класс на два: базисный (basic) (e.g. `BaseArrayHelper`) и определенный (concrete)  (e.g. `ArrayHelper`). Также важно помнить, что всегда нужно использовать только определенный хелпер и никогда не использовать базисный. 

Основные Хелперы
-------------------

Список основных хелпер-классов релизных версий Yii:

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

Преобразование хелперов <span id="customizing-helper-classes"></span>
--------------------------

Для преобразования основного хелпер-класса (e.g. [[yii\helpers\ArrayHelper]]) необходимо создать новый, основа которого -  хелперы его базисного класса (e.g. [[yii\helpers\BaseArrayHelper]]). Далее следует назвать его также, как назван ваш конкретный класс (e.g. [[yii\helpers\ArrayHelper]]), включая его собственное пространство имён. Только тогда новый класс будет считаться подготовленным к замене исходного во фреймворке.


В следующем примере показано, как преобразовывать класс [[yii\helpers\ArrayHelper::merge()|merge()]] метод класса [[yii\helpers\ArrayHelper]]:

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


Теперь сохраните ваш класс в файле `ArrayHelper.php'. Файл может быть размещен в директории, например, `@app/components`.

Next, in your application's [entry script](structure-entry-scripts.md), add the following line of code
after including the `yii.php` file to tell the [Yii class autoloader](concept-autoloading.md) to load your custom
class instead of the original helper class from the framework:

Теперь, нужно добавить получившийся код в скрипт приложения [entry script](structure-entry-scripts.md), включая `yii.php` файл. Это даст команду [Yii class autoloader](concept-autoloading.md) на загрузку модифицированного класса поверх оригинального хелпера фреймворка:

```php
Yii::$classMap['yii\helpers\ArrayHelper'] = '@app/components/ArrayHelper.php';
```

Следует отметить, что модификация классов-хелперов может иметь смысл только тогда, когда задачей является изменение модели существующей функции или ее хелперов. Для добавления в новую функцию вашего приложения, лучше создавать отдельного Хелпера
