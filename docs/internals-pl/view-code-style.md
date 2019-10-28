Styl kodowania widoków Yii 2
============================

Poniższy styl kodowania jest stosowany w kodzie frameworka Yii 2.x i oficjalnych rozszerzeniach. Nie zmuszamy jednak nikogo do stosowania go we własnych aplikacjach. Wybierz styl, który najbardziej odpowiada Twoim potrzebom.

```php
<?php
// Rozpoczynający tag PHP jest wymagany w każdym pliku szablonu. Pusta linia za rozpoczynającym tagiem jest również wymagana.

// Opisz zmienne przekazane z kontrolera w tym miejscu.
/* @var $this yii\base\View */
/* @var $form yii\widgets\ActiveForm */
/* @var $posts app\models\Post[] */
/* @var $contactMessage app\models\ContactMessage */
// Pusta linia poniżej jest wymagana.

// Deklaracje klas z przestrzeniami nazw.
use yii\helpers\Html;
use yii\widgets\ActiveForm;
// Pusta linia poniżej jest wymagana.

// Ustaw właściwości kontekstu, wywołaj jego settery, zrób inne rzeczy.
$this->title = 'Posts';
?>
<!-- Preferowane są wydzielone bloki PHP dla `foreach`, `for`, `if` itp. -->
<?php foreach ($posts as $post): ?>
    <!-- Zwróć uwagę na wcięcie w tym miejscu. -->
    <h2><?= Html::encode($post['title']) ?></h2>
    <p><?= Html::encode($post['shortDescription']) ?></p>
<!-- `endforeach;`, `endfor;`, `endif;` itd. powinny być użyte tutaj zamiast `}` w przypadku wielu bloków PHP -->
<?php endforeach; ?>

<!-- Deklaracja widżetu może, ale nie musi, być rozbita na kilka linii kodu. -->
<?php $form = ActiveForm::begin([
    'options' => ['id' => 'contact-message-form'],
    'fieldConfig' => ['inputOptions' => ['class' => 'common-input']],
]); ?>
    <!-- Zwróć uwagę na wcięcie w tym miejscu. -->
    <?= $form->field($contactMessage, 'name')->textInput() ?>
    <?= $form->field($contactMessage, 'email')->textInput() ?>
    <?= $form->field($contactMessage, 'subject')->textInput() ?>
    <?= $form->field($contactMessage, 'body')->textArea(['rows' => 6]) ?>

    <div class="form-actions">
        <?= Html::submitButton('Submit', ['class' => 'common-button']) ?>
    </div>
<!-- Zamykające wywołanie widżetu powinno znajdować się w wydzielonym tagu PHP. -->
<?php ActiveForm::end(); ?>
<!-- Kończący znak nowej linii jest wymagany. -->

```
