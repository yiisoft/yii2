Создание форм
==============

Основной способ использования форм в Yii является использование [[yii\widgets\ActiveForm]]. Этот подход должен быть
применён, когда форма основана на модели. Кроме того, имеются дополнительные методы в [[yii\helpers\Html]], которые
используются для добавления кнопок и текстовых подсказок к любой форме.

Форме, которая отображается на стороне клиента, в большинстве случаев соответствует [модели](structure-models.md).
Модель в свою очередь проверяет данные из элементов формы на сервере (посмотрите раздел [Валидация](input-validation.md)
для более подробных сведений). Когда создаётся форма, основанная на модели, необходимо определить, что же является моделью.
Модель может основываться на классе [Active Record](db-active-record.md), который описывает некоторые данные из базы данных,
или модель может основываться на базовом классе Model (происходит от [[yii\base\Model]]), который позволяет использовать
произвольный набор элементов формы, например форма входа.

В следующем примере показано, как создать модель формы, основанной на базовом классе Model:

```php
<?php

class LoginForm extends \yii\base\Model
{
    public $username;
    public $password;

    public function rules()
    {
        return [
            // тут определяются правила валидации
        ];
    }
}
```

В контроллере, будем передать экземпляр этой модели в вид, для [[yii\widgets\ActiveForm|ActiveForm]] виджета, который
генерирует форму.

```php
<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;

$form = ActiveForm::begin([
    'id' => 'login-form',
    'options' => ['class' => 'form-horizontal'],
]) ?>
    <?= $form->field($model, 'username') ?>
    <?= $form->field($model, 'password')->passwordInput() ?>

    <div class="form-group">
        <div class="col-lg-offset-1 col-lg-11">
            <?= Html::submitButton('Вход', ['class' => 'btn btn-primary']) ?>
        </div>
    </div>
<?php ActiveForm::end() ?>
```

В вышеизложенном коде, [[yii\widgets\ActiveForm::begin()|ActiveForm::begin()]] не только создаёт экземпляр формы, но
также и знаменует её начало. Весь контент, расположенный между [[yii\widgets\ActiveForm::begin()|ActiveForm::begin()]]
и [[yii\widgets\ActiveForm::end()|ActiveForm::end()]], будет завёрнут в HTML `<form>` тег. Вы можете настроить некоторые 
настройки виджета через передачу массива в его `begin` метод, также как и в любом другом виджете. В этом случае, дополнительный
CSS класс и идентификатор ID будет прикреплён к открывающемуся тегу `<form>`. Для просмотра всех доступных настроек,
пожалуйста обратитесь к API документации [[yii\widgets\ActiveForm]].

Для создания в форме элемента с меткой и любой применимой Javascript валидацией, вызывается [[yii\widgets\ActiveForm::field()|ActiveForm::field()]],
который возвращает экземпляр [[yii\widgets\ActiveField]]. Когда этот метод вызывается непосредственно, то результатом 
будет текстовый элемент (`input type="text"`). Для того, чтобы настроить элемент, можно вызвать одни за одним дополнительные
методы [[yii\widgets\ActiveField|ActiveField]]:

```php
// элемент формы password
<?= $form->field($model, 'password')->passwordInput() ?>
// добавлена подсказка hint и настроена метка label
<?= $form->field($model, 'username')->textInput()->hint('Пожалуйста, введите имя')->label('Имя') ?>
// создание HTML5 email элемента
<?= $form->field($model, 'email')->input('email') ?>
```

Впоследствии будет созданы `<label>`, `<input>` и другие теги в соответствии с [[yii\widgets\ActiveField::$template|template]],
который определён в элементе. Имя элемента формы определяется автоматически из моделей [[yii\base\Model::formName()|form name]] 
и их атрибутов. Например, имя элемента для атрибута `username` в коде, приведённом выше, будет `LoginForm[username]`.
Это правило наименование будет учитываться на стороне сервера при получении массива результатов `$_POST['LoginForm']`
для всех элементов формы входа (Login Form).

Специфический атрибут модели может быть задан через более сложный способ. Например, при загрузки файлов или выборе
нескольких значений из списка, в качестве значений атрибуту модели нужно передать массив, для этого к имени можно добавить
`[]`:

```php
// поддерживает загрузку нескольких файлов:
echo $form->field($model, 'uploadFile[]')->fileInput(['multiple'=>'multiple']);

// поддерживает выбор нескольких значений:
echo $form->field($model, 'items[]')->checkboxList(['a' => 'Item A', 'b' => 'Item B', 'c' => 'Item C']);
```

Дополнительные HTML элементы могут быть добавлены к форме используя обычный HTML или методы из класса помощника [[yii\helpers\Html|Html]],
как это было сделано с помощью [[yii\helpers\Html::submitButton()|Html::submitButton()]] в примере, что выше. 

> Подсказка: Если вы использует Twitter Bootstrap CSS в своём приложении, то воспользуйтесь
> [[yii\bootstrap\ActiveForm]] вместо [[yii\widgets\ActiveForm]]. Он добавит к ActiveForm дополнительные стили, которые
> сработают в рамках bootstrap CSS.

> Подсказка: для добавления "звёздочки" к обязательным элементам формы воспользуйтесь следующим CSS: 
>
> ```css
> div.required label:after {
>     content: " *";
>     color: red;
> }
> ```

Следующая глава [Validating Input](input-validation.md) описывает валидацию отправленной формы как на стороне сервера,
так и на стороне клиента.

Если вы хотите более подробно изучить информацию по использованию форм, то обратитесь к главам:

- [Collecting tabular input](input-tabular-input.md) for collecting data for multiple models of the same kind.
- [Работа с несколькими моделями](input-multiple-models.md) для обработки нескольких разных моеделй в рамках одной формы.
- [Загрузка файлов](input-file-upload.md) - как использовать формы для загрузки файлов.
