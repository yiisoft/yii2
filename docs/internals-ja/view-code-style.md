Yii 2 ビュー・コード・スタイル
==============================

下記のコード・スタイルが Yii 2.x コアと公式エクステンションのビュー・ファイルに用いられています。私たちは、あなたが自分のアプリケーションにこのコード・スタイルを使うことを強制するものではありません。あなたにとってより良いコード・スタイルを自由に選んでください。

```php
<?php
// 冒頭の PHP タグは全てのテンプレート・ファイルで不可欠。冒頭のタグに続く空行も同じく必須。

// コントローラから渡される入力変数をここで説明。
/* @var $this yii\base\View */
/* @var $form yii\widgets\ActiveForm */
/* @var $posts app\models\Post[] */
/* @var $contactMessage app\models\ContactMessage */
// 下の空行は必要。

// 名前空間に属するクラスの宣言。
use yii\helpers\Html;
use yii\widgets\ActiveForm;
// 下の空行は必要。

// コンテキストのプロパティを設定したり、コンテキストのセッターを呼んだり、その他のことをする。
$this->title = 'Posts';
?>
<!-- foreach、for, if などには、独立した PHP ブロックを使う方が良い -->
<?php foreach ($posts as $post): ?>
    <!-- インデントのレベルに注目 -->
    <h2><?= Html::encode($post['title']) ?></h2>
    <p><?= Html::encode($post['shortDescription']) ?></p>
<!-- 複数の PHP ブロックが使われる場合にそなえて、`}` ではなく、`endforeach;`、`endfor;`、`endif;` などを使う -->
<?php endforeach; ?>

<!-- ウィジェットの宣言は複数のコード行に分かれても良いし、分かれなくても良い -->
<?php $form = ActiveForm::begin([
    'options' => ['id' => 'contact-message-form'],
    'fieldConfig' => ['inputOptions' => ['class' => 'common-input']],
]); ?>
    <!-- インデントのレベルに注目 -->
    <?= $form->field($contactMessage, 'name')->textInput() ?>
    <?= $form->field($contactMessage, 'email')->textInput() ?>
    <?= $form->field($contactMessage, 'subject')->textInput() ?>
    <?= $form->field($contactMessage, 'body')->textArea(['rows' => 6]) ?>

    <div class="form-actions">
        <?= Html::submitButton('Submit', ['class' => 'common-button']) ?>
    </div>
<!-- ウィジェットの終了の呼び出しは、独立した PHP タグを持つべき -->
<?php ActiveForm::end(); ?>
<!-- 末尾の改行文字は必須 -->

```
