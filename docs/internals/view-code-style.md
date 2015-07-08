Yii2 view code style
====================

The following code style is used for Yii 2.x core and official extensions view files. We aren't forcing you to use this code style for your application. Feel free to choose what suits you better.

```php
<?php
// Leading PHP tag is a must in every template file. Empty line after leading PHP tag is also required.

// Describe input variables passed by controller here.
/* @var $this yii\base\View */
/* @var $form yii\widgets\ActiveForm */
/* @var $posts app\models\Post[] */
/* @var $contactMessage app\models\ContactMessage */
// Empty line below is necessary.

// Namespaced classes declaration.
use yii\helpers\Html;
use yii\widgets\ActiveForm;
// Empty line below is necessary.

// Set context properties, call its setters, do other things.
$this->title = 'Posts';
?>
<!-- Separate PHP blocks are preferred for foreach, for, if etc. -->
<?php foreach ($posts as $post): ?>
    <!-- Note indentation level here. -->
    <h2><?= Html::encode($post['title']) ?></h2>
    <p><?= Html::encode($post['shortDescription']) ?></p>
<!-- `endforeach;`, `endfor;`, `endif;`, etc. should be used instead of `}` in case multiple PHP blocks are used -->
<?php endforeach; ?>

<!-- Widget declaration may or may not be exploded into multiple LOCs. -->
<?php $form = ActiveForm::begin([
    'options' => ['id' => 'contact-message-form'],
    'fieldConfig' => ['inputOptions' => ['class' => 'common-input']],
]); ?>
    <!-- Note indentation level here. -->
    <?= $form->field($contactMessage, 'name')->textInput() ?>
    <?= $form->field($contactMessage, 'email')->textInput() ?>
    <?= $form->field($contactMessage, 'subject')->textInput() ?>
    <?= $form->field($contactMessage, 'body')->textArea(['rows' => 6]) ?>

    <div class="form-actions">
        <?= Html::submitButton('Submit', ['class' => 'common-button']) ?>
    </div>
<!-- Ending widget call should have individual PHP tag. -->
<?php ActiveForm::end(); ?>
<!-- Trailing newline character is mandatory. -->

```
