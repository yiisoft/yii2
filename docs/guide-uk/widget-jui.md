jQuery UI Віджет
=================

> Замітка: Цей розділ знаходиться на стадії розробки.

Yii включає підтримку [jQuery UI](http://api.jqueryui.com/) бібліотеки в офіційному розширенні. jQuery UI є
зберігачем набору інтерфейсів користувача, що забезпечує роботу з ефектами, віджетами і темами та побудований з
використанням jQuery JavaScript бібілотеки.

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

Yii віджети
-----------

Найбільш складні jQuery UI компоненти загорнуті в Yii віджети, щоб запезпечити більш надійний синтаксис та інтегрувати
з можливостями платформи. Всі віджети належать до `\yii\jui` простору імен:

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

В наступних розділах рязглядаються деякі випадки використання цих віджетів.

Обробка вводу дати з допомогою DatePicker <span id="datepicker-date-input"></span>
---------------------------------------

Завдяки [[yii\jui\DatePicker|DatePicker]] віджету, обробку дати введеною користувачем, можна зробити дуже зручним способом.
В наступному прикладі ми будемо використовувати модель `Task` яка має атрибут `deadline`, який повинен бути встановлений користувачем
використовуючи [ActiveForm](input-forms.md). Значення атрибуту будуть збережені в якості мітки часу Unix в базі даних.

В цій ситуації є 3 компоненти, *що виконуються разом:*

- Це, віджет [[yii\jui\DatePicker|DatePicker]], який використовується в формі для відображення поля введення атрибуту моделі.
- Це, компонент додатку [formatter](output-formatter.md), який відповідає за формат дати, що відображається користувачеві.
- Це, [DateValidator](tutorial-core-validators.md#date), який перевіряє що ввів користувач і конвертує в мітку часу Unix.

Спершу ми додамо до поля введення вибору дати у формі, використовуючи метод [[yii\widgets\ActiveField::widget()|widget()]] поля форми:

```php
<?= $form->field($model, 'deadline')->widget(\yii\jui\DatePicker::className(), [
    // якщо ви використовуєте bootstrap, наступний рядок буде встановлювати правильний стиль для поля вводу
    'options' => ['class' => 'form-control'],
    // ... ви можете налаштувати більше властивостей DatePicker тут
]) ?>
```

Другим кроком буде налаштування валідатора дати в [model's rules() method](input-validation.md#declaring-rules):

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
значень в базі данних у вигляді `NULL`.

Формат по-замовчуванню, вибір дати і валідація значення дати міститься в `Yii::$app->formatter->dateFormat`, так що, ви можете використовувати
ці властивості, щоб налаштувати формат дати для всієї програми.
Щоб змінити формат дати ви повинні налаштувати [[yii\validators\DateValidator::format]] та [[yii\jui\DatePicker::dateFormat]].