<?php

/* @var $this \yii\web\View */
/* @var $content string */

use yii\helpers\Html;
use tests\js\assets\AppAsset;

AppAsset::register($this);
?>

<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
    <head>
        <meta charset="<?= Yii::$app->charset ?>">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <?= Html::csrfMetaTags() ?>
        <title><?= Html::encode($this->title) ?></title>
        <?php $this->head() ?>
    </head>
    <body>
        <?php $this->beginBody() ?>
        <div id="mocha"></div>
        <div class="container"><?= $content ?></div>
        <?php $this->endBody() ?>
    </body>
</html>
<?php $this->endPage() ?>
