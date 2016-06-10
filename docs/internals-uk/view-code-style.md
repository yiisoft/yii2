Оформлення коду представлень у Yii 2
====================================

Нижченаведений стиль кодування використовується у представленнях основи Yii 2.x та у представленнях офіційних розширень.
Команда розробників не наполягає на використанні цього стилю для вашого додатка. Вільно обирайте те, що підходить вам більше.

```php
<?php
// Початковий тег PHP, за яким йде пустий рядок, є обовʼязковим для усіх файлів шаблонів.

// Опис вхідних змінних, які передає контролер.
/* @var $this yii\base\View */
/* @var $form yii\widgets\ActiveForm */
/* @var $posts app\models\Post[] */
/* @var $contactMessage app\models\ContactMessage */
// Пустий рядок після є необхідним.

// Декларування класів з просторів імен.
use yii\helpers\Html;
use yii\widgets\ActiveForm;
// Пустий рядок після є необхідним.

// Призначення властивостей контексту, виклики їх сеттерів, інші речі.
$this->title = 'Posts';
?>
<!-- Для foreach, for, if, і т. п. краще використовувати роздільні блоки PHP. -->
<?php foreach ($posts as $post): ?>
    <!-- Тут зверніть увагу на відступи. -->
    <h2><?= Html::encode($post['title']) ?></h2>
    <p><?= Html::encode($post['shortDescription']) ?></p>
<!-- `endforeach;`, `endfor;`, `endif;`, і т. п. потрібно використовувати замість `}` у випадку використання багатьох блоків PHP -->
<?php endforeach; ?>

<!-- При декларуванні віджету код може міститись як на одному так і на багатьох рядках. -->
<?php $form = ActiveForm::begin([
    'options' => ['id' => 'contact-message-form'],
    'fieldConfig' => ['inputOptions' => ['class' => 'common-input']],
]); ?>
    <!-- Тут зверніть увагу на відступи. -->
    <?= $form->field($contactMessage, 'name')->textInput() ?>
    <?= $form->field($contactMessage, 'email')->textInput() ?>
    <?= $form->field($contactMessage, 'subject')->textInput() ?>
    <?= $form->field($contactMessage, 'body')->textArea(['rows' => 6]) ?>

    <div class="form-actions">
        <?= Html::submitButton('Обробити', ['class' => 'common-button']) ?>
    </div>
<!-- Виклик завершення віджету має окремий блок PHP. -->
<?php ActiveForm::end(); ?>
<!-- Кінцевий знак нового рядка є обовʼязковим. -->

```
