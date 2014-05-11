<?php

use yii\apidoc\templates\bootstrap\SideNavWidget;

/**
 * @var yii\web\View $this
 * @var string $content
 */

$this->beginContent('@yii/apidoc/templates/bootstrap/layouts/main.php'); ?>

<div class="row">
    <div class="col-md-2">
        <?php
        asort($headlines);
        $nav = [];
        foreach ($headlines as $file => $headline) {
            if (basename($file) == 'README.md') {
                $nav[] = [
                    'label' => $headline,
                    'url' => $this->context->generateGuideUrl($file),
                    'active' => isset($currentFile) && ($file == $currentFile),
                ];
                unset($headlines[$file]);
            }
        }
        foreach ($headlines as $file => $headline) {
            $nav[] = [
                'label' => $headline,
                'url' => $this->context->generateGuideUrl($file),
                'active' => isset($currentFile) && ($file == $currentFile),
            ];
        } ?>
        <?= SideNavWidget::widget([
            'id' => 'navigation',
            'items' => $nav,
            'view' => $this,
        ]) ?>
    </div>
    <div class="col-md-9 guide-content" role="main">
        <?= $content ?>
    </div>
</div>

<?php $this->endContent(); ?>
