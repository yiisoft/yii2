Віджети jQuery UI
=================

> Примітка: Цей розділ знаходиться на стадії розробки.

Yii включає підтримку бібліотеки [jQuery UI](http://api.jqueryui.com/) в офіційному розширенні. jQuery UI є
зберігачем набору інтерфейсів користувача, який забезпечує роботу з ефектами, віджетами і темами, що побудований
на основі JavaScript-бібілотеки jQuery.

Встановлення
------------

Кращим способом для встановлення даного розширення є встановлення через [composer](http://getcomposer.org/download/).

Запустіть

```
php composer.phar require --prefer-dist yiisoft/yii2-jui "*"
```

або додайте

```
"yiisoft/yii2-jui": "*"
```

в потрібний розділ `composer.json` файлу.

Віджети Yii
-----------

Найбільш складні компоненти jQuery UI загорнуті в Yii віджети, щоб запезпечити більш надійний синтаксис та інтегрувати
з можливостями фреймворку. Всі віджети належать до простору імен `\yii\jui`:

- [[yii\jui\Accordion|Accordion]]
- [[yii\jui\AutoComplete|AutoComplete]]
- [[yii\jui\DatePicker|DatePicker]]
- [[yii\jui\Dialog|Dialog]]
- [[yii\jui\Draggable|Draggable]]
- [[yii\jui\Droppable|Droppable]]
- [[yii\jui\Menu|Menu]]
- [[yii\jui\ProgressBar|ProgressBar]]
- [[yii\jui\Resizable|Resizable]]
- [[yii\jui\Selectable|Selectable]]
- [[yii\jui\Slider|Slider]]
- [[yii\jui\SliderInput|SliderInput]]
- [[yii\jui\Sortable|Sortable]]
- [[yii\jui\Spinner|Spinner]]
- [[yii\jui\Tabs|Tabs]]

В наступних розділах розглядаються деякі приклади використання цих віджетів.

Обробка вводу дати з допомогою DatePicker <span id="datepicker-date-input"></span>
-----------------------------------------

Збирання вводу дат від користувачів можливо виконати даже зручним способом, завдяки віджету [[yii\jui\DatePicker|DatePicker]].
В наступному прикладі ми будемо використовувати модель `Task`, яка має атрибут `deadline`, який повинен бути встановлений
користувачем, використовуючи [ActiveForm](input-forms.md). Значення атрибуту буде збережено в якості мітки часу Unix в базі даних.

В цій ситуації є 3 компоненти, що взаємодіють між собою:

- Віджет [[yii\jui\DatePicker|DatePicker]], який використовується в формі для відображення поля введення атрибуту моделі.
- Компонент додатку [formatter](output-formatter.md), який відповідає за формат дати, що відображається користувачеві.
- [DateValidator](tutorial-core-validators.md#date), який перевіряє що ввів користувач і конвертує в мітку часу Unix.

Спершу ми додамо до поле введення вибору дати до формі, використовуючи метод [[yii\widgets\ActiveField::widget()|widget()]] поля форми:

```php
<?= $form->field($model, 'deadline')->widget(\yii\jui\DatePicker::className(), [
    // якщо ви використовуєте bootstrap, наступний рядок буде встановлювати правильний стиль для поля вводу
    'options' => ['class' => 'form-control'],
    // ... ви можете налаштувати більше властивостей DatePicker тут
]) ?>
```

Другим кроком буде налаштування валідатора дати в [методі моделі rules()](input-validation.md#declaring-rules):

```php
public function rules()
{
    return [
        // ...

        // забезпечить збереження порожних значень в базі данних у вигляді NULL
        ['deadline', 'default', 'value' => null],

        // валідація дати і перезапис `deadline` з міткою часу Unix
        ['deadline', 'date', 'timestampAttribute' => 'deadline'],
    ];
}
```

Ми можемо також додати [стандартні значення фільтру](input-validation.md#handling-empty-inputs), щоб забезпечити збереження порожніх
значень в базі данних у вигляді `NULL`. Ви можете пропустити цей крок, якщо значення дати є [обовʼязковим](tutorial-core-validators.md#required).

Формат за замовчуванням для вибору дати і валідації значення дати міститься в `Yii::$app->formatter->dateFormat`, таким чином,
ви можете використовувати ці властивості, щоб налаштувати формат дати для всього додатку.
Щоб змінити формат дати ви повинні налаштувати [[yii\validators\DateValidator::format]] та [[yii\jui\DatePicker::dateFormat]].