<?php
/* @var $this \yii\web\View */
/* @var $static string */
$this->beginPage();
$this->head();
$this->beginBody();
?>
{
    "static": "<?= $static ?>",
    "dynamic": "<?= $this->renderDynamic('return Yii::$app->params[\'dynamic\'];') ?>"
}
<?php
$this->endBody();
$this->endPage();
?>
