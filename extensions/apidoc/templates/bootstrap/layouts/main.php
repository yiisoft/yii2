<?php

use yii\apidoc\renderers\BaseRenderer;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\helpers\Html;

/**
 * @var yii\web\View $this
 */

\yii\apidoc\templates\bootstrap\assets\AssetBundle::register($this);

// Navbar hides initial content when jumping to in-page anchor
// https://github.com/twbs/bootstrap/issues/1768
$this->registerJs(<<<JS
    var shiftWindow = function () { scrollBy(0, -50) };
    if (location.hash) shiftWindow();
    window.addEventListener("hashchange", shiftWindow);
JS
,
    \yii\web\View::POS_READY
);

$this->beginPage();
?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>"/>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="language" content="en" />
    <?php $this->head() ?>
    <title><?= Html::encode($this->context->pageTitle) ?></title>
</head>
<body>

<?php $this->beginBody() ?>
<div class="wrap">
    <?php
    NavBar::begin([
        'brandLabel' => $this->context->pageTitle,
        'brandUrl' => ($this->context->apiUrl === null && $this->context->guideUrl !== null) ? './guide-index.html' : './index.html',
        'options' => [
            'class' => 'navbar-inverse navbar-fixed-top',
        ],
        'renderInnerContainer' => false,
        'view' => $this,
    ]);
    $nav = [];

    if ($this->context->apiUrl !== null) {
        $nav[] = ['label' => 'Class reference', 'url' => rtrim($this->context->apiUrl, '/') . '/index.html'];
        if (!empty($this->context->extensions)) {
            $extItems = [];
            foreach ($this->context->extensions as $ext) {
                $extItems[] = [
                    'label' => $ext,
                    'url' => "./ext-{$ext}-index.html",
                ];
            }
            $nav[] = ['label' => 'Extensions', 'items' => $extItems];
        }
    }

    if ($this->context->guideUrl !== null) {
        $nav[] = ['label' => 'Guide', 'url' => rtrim($this->context->guideUrl, '/') . '/' . BaseRenderer::GUIDE_PREFIX . 'index.html'];
    }

    echo Nav::widget([
        'options' => ['class' => 'navbar-nav'],
        'items' => $nav,
        'view' => $this,
        'params' => [],
    ]);
    NavBar::end();
    ?>

    <?= $content ?>

</div>

<footer class="footer">
    <?php /* <p class="pull-left">&copy; My Company <?= date('Y') ?></p> */ ?>
    <p class="pull-right"><small>Page generated on <?= date('r') ?></small></p>
    <?= Yii::powered() ?>
</footer>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
