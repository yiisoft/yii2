<?php
/* @var $this \yii\web\View */
/* @var $content string */
?>
<?php $this->beginPage(); ?>
<!DOCTYPE html>
<html>
<head>
    <title>Test</title>
    <?php $this->head(); ?>
</head>
<body>
<?php $this->beginBody(); ?>

<?= $content ?>

<?php $this->endBody(); ?>
</body>
</html>
<?php $this->endPage(); ?>
