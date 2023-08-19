Создание форм
=============

Основным способом использования форм в Yii является использование [[yii\widgets\ActiveForm]]. Этот подход должен быть
применён, когда форма основана на модели. Кроме того, имеются дополнительные методы в [[yii\helpers\Html]], которые
используются для добавления кнопок и текстовых подсказок к любой форме.

Форма, которая отображается на стороне клиента в большинстве случаев соответствует [модели](structure-models.md).
Модель, в свою очередь, проверяет данные из элементов формы на сервере (смотрите раздел [Валидация](input-validation.md)
для более подробных сведений). Когда создаётся форма, основанная на модели, необходимо определить, что же является моделью.
Модель может основываться на классе [Active Record](db-active-record.md), который описывает некоторые данные из базы данных,
или же на базовом классе Model (происходит от [[yii\base\Model]]), который позволяет использовать
произвольный набор элементов формы (например, форма входа).

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

В контроллере будем передавать экземпляр этой модели в представление для виджета [[yii\widgets\ActiveForm|ActiveForm]], который генерирует форму.

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

В вышеприведённом коде [[yii\widgets\ActiveForm::begin()|ActiveForm::begin()]] не только создаёт экземпляр формы, но
также и знаменует её начало. Весь контент, расположенный между [[yii\widgets\ActiveForm::begin()|ActiveForm::begin()]]
и [[yii\widgets\ActiveForm::end()|ActiveForm::end()]], будет завёрнут в HTML-тег `<form>`. Вы можете изменить некоторые 
настройки виджета через передачу массива в его `begin` метод, так же как и в любом другом виджете. В этом случае дополнительный CSS-класс и идентификатор будет прикреплён к открывающемуся тегу `<form>`. Для просмотра всех доступных настроек, пожалуйста, обратитесь к документации API [[yii\widgets\ActiveForm]].

Для создания в форме элемента с меткой и любой применимой валидацией с помощью JavaScript, вызывается [[yii\widgets\ActiveForm::field()|ActiveForm::field()]], который возвращает экземпляр [[yii\widgets\ActiveField]]. Когда этот метод вызывается непосредственно, то результатом будет текстовый элемент (`input type="text"`). Для того, чтобы настроить элемент, можно вызвать один за другим дополнительные методы [[yii\widgets\ActiveField|ActiveField]]:

```php
// элемент формы для ввода пароля
echo $form->field($model, 'password')->passwordInput();
// добавлена подсказка (hint) и настроена метка (label)
echo $form->field($model, 'username')->textInput()->hint('Пожалуйста, введите имя')->label('Имя');
// создание элемента HTML5 для ввода email 
echo $form->field($model, 'email')->input('email');
```

Впоследствии будут созданы `<label>`, `<input>` и другие теги в соответствии с [[yii\widgets\ActiveField::$template|template]], который определён в элементе. Имя элемента формы определяется автоматически из моделей [[yii\base\Model::formName()|form name]] 
и их атрибутов. Например, имя элемента для атрибута `username` в коде, приведённом выше, будет `LoginForm[username]`.
Это правило именования будет учитываться на стороне сервера при получении массива результатов `$_POST['LoginForm']`
для всех элементов формы входа (Login Form).

> Tip: Если в форме только одна модель, и вы хотите упростить имена полей ввода, то можете сделать это, переопределив метод [[yii\base\Model::formName()|formName()]] модели так, чтобы он возвращал пустую строку. Это может пригодиться для получения более красивых URL при фильтрации моделей в [GridView](output-data-widgets.md#grid-view).

Специфический атрибут модели может быть задан более сложным способом. Например, при загрузке файлов или выборе
нескольких значений из списка, в качестве значений атрибуту модели нужно передать массив, для этого к имени можно добавить
`[]`:

```php
// поддерживает загрузку нескольких файлов:
echo $form->field($model, 'uploadFile[]')->fileInput(['multiple'=>'multiple']);

// поддерживает выбор нескольких значений:
echo $form->field($model, 'items[]')->checkboxList(['a' => 'Item A', 'b' => 'Item B', 'c' => 'Item C']);
```

Имена элементов форм следует выбирать, учитывая, что могут возникнуть конфликты. Подробнее об этом в [документации jQuery](https://api.jquery.com/submit/):

> Имена и идентификаторы форм и их элементов не должны совпадать с элементами форм, такими как `submit`, `length` или `method`. Конфликты имен могут вызывать трудно диагностируемые ошибки. Подробнее о способах избегания подобных проблем смотрите [DOMLint](https://kangax.github.io/domlint/).

Дополнительные HTML-элементы можно добавить к форме, используя обычный HTML или методы из класса помощника [[yii\helpers\Html|Html]], как это было сделано с помощью [[yii\helpers\Html::submitButton()|Html::submitButton()]] в примере, приведённом выше. 

> Tip: Если вы используете Twitter Bootstrap CSS в своём приложении, то воспользуйтесь [[yii\bootstrap\ActiveForm]] вместо [[yii\widgets\ActiveForm]]. Он добавит к ActiveForm дополнительные стили, которые сработают в рамках bootstrap CSS.

> Tip: для добавления "звёздочки" к обязательным элементам формы, воспользуйтесь следующим CSS:
>
> ```css
> div.required label.control-label:after {
>     content: " *";
>     color: red;
> }
> ```


Создание выпадающего списка <span id="creating-activeform-dropdownlist"></span>
---------------------

Для создания выпадающего списка можно использовать метод ActiveForm [[yii\widgets\ActiveField::dropDownList()|dropDownList()]]:

```php
use app\models\ProductCategory;

/* @var $this yii\web\View */
/* @var $form yii\widgets\ActiveForm */
/* @var $model app\models\Product */

echo $form->field($model, 'product_category')->dropdownList(
    ProductCategory::find()->select(['category_name', 'id'])->indexBy('id')->column(),
    ['prompt'=>'Select Category']
);
```

Текущее значение поля модели будет автоматически выбрано в списке.

Работа с Pjax <span id="working-with-pjax"></span>
--------------

Виджет [[yii\widgets\Pjax|Pjax]] позволяет обновлять определённую область страницы вместо
перезагрузки всей страницы. Вы можете использовать его для обновления формы после её отсылки.

Для того, чтобы задать, какая из форм будет работать через PJAX, можно воспользоваться
опцией [[yii\widgets\Pjax::$formSelector|$formSelector]]. Если значение не задано, все формы
с атрибутом `data-pjax` внутри PJAX-контента будут работать через PJAX.

```php
use yii\widgets\Pjax;
use yii\widgets\ActiveForm;

Pjax::begin([
    // Опции Pjax
]);
    $form = ActiveForm::begin([
        'options' => ['data' => ['pjax' => true]],
        // остальные опции ActiveForm
    ]);

        // Содержимое ActiveForm

    ActiveForm::end();
Pjax::end();
```
> Tip: Будьте осторожны со ссылками внутри виджета [[yii\widgets\Pjax|Pjax]] так как ответ будет
> также отображаться внутри виджета. Чтобы ссылка работала без PJAX, добавьте к ней HTML-атрибут
> `data-pjax="0"`.

#### Значения кнопок отправки и загрузка файлов

В `jQuery.serializeArray()` имеются определённые проблемы
[при работе с файлами](https://github.com/jquery/jquery/issues/2321) и
[значениями кнопок типа submit](https://github.com/jquery/jquery/issues/2321).
Они не будут исправлены и признаны устаревшими в пользу класса`FormData` из HTML5.

Это означет, что поддержка файлов и значений submit-кнопок через AJAX или виджет
[[yii\widgets\Pjax|Pjax]] зависит от 
[поддержки в браузере](https://developer.mozilla.org/ru/docs/Web/API/FormData#%D1%81%D0%BE%D0%B2%D0%BC%D0%B5%D1%81%D1%82%D0%B8%D0%BC%D0%BE%D1%81%D1%82%D1%8C)
класса `FormData`.


Ещё по теме <span id="further-reading"></span>
---------------

Следующая глава [Валидация](input-validation.md) описывает валидацию отправленной формы как на стороне сервера,
так и на стороне клиента.

Если вы хотите более подробно изучить информацию по использованию форм, то обратитесь к главам:

- [Табличный ввод](input-tabular-input.md) - получение данных нескольких моделей одного вида.
- [Работа с несколькими моделями](input-multiple-models.md) - обработка нескольких разных моделей в рамках одной формы.
- [Загрузка файлов](input-file-upload.md) - использование форм для загрузки файлов.
