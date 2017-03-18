Стиль кодирования представлений Yii 2
=====================================

Данный стиль кодирования используется для представлений в ядре Yii 2.x и официальных представлениях. Мы не заставляем
вас использовать данный стиль кодирования для ваших приложений. Не стесняйтесь использовать тот стиль, который вам
больше подходит.

```php
<?php
// Открывающий PHP тег должен быть в каждом файле шаблона. Пустая строка после открывающего тега также необходима.

// Описывайте входные переменные, переданные сюда контроллером.
/* @var $this yii\base\View */
/* @var $form yii\widgets\ActiveForm */
/* @var $posts app\models\Post[] */
/* @var $contactMessage app\models\ContactMessage */
// Пустая строка ниже необходима.

// Описание классов с пространствами имён.
use yii\helpers\Html;
use yii\widgets\ActiveForm;
// Пустая строка ниже необходима.

// Установка свойств контекста, вызов сеттеров и другие действия.
$this->title = 'Posts';
?>
<!-- Отдельные блоки PHP являются предпочтительными для foreach, for, if и т.д. -->
<?php foreach ($posts as $post): ?>
    <!-- Заметьте здесь есть отступ. -->
    <h2><?= Html::encode($post['title']) ?></h2>
    <p><?= Html::encode($post['shortDescription']) ?></p>
<!-- `endforeach;`, `endfor;`, `endif;`, и другие должны использоваться вместо `}` в случае использования множественных PHP блоков -->
<?php endforeach; ?>

<!-- Описание виджета может быть, а может и не быть, разбито на разных строках. -->
<?php $form = ActiveForm::begin([
    'options' => ['id' => 'contact-message-form'],
    'fieldConfig' => ['inputOptions' => ['class' => 'common-input']],
]); ?>
    <!-- Заметьте здесь есть отступ. -->
    <?= $form->field($contactMessage, 'name')->textInput() ?>
    <?= $form->field($contactMessage, 'email')->textInput() ?>
    <?= $form->field($contactMessage, 'subject')->textInput() ?>
    <?= $form->field($contactMessage, 'body')->textArea(['rows' => 6]) ?>

    <div class="form-actions">
        <?= Html::submitButton('Submit', ['class' => 'common-button']) ?>
    </div>
<!-- Завершающий вызов виджета должен быть в индивидуальном PHP теге. -->
<?php ActiveForm::end(); ?>
<!-- Завершающий символ переноса строки обязателен. -->

```
