<?php

use yii\apidoc\helpers\ApiMarkdown;
use yii\apidoc\models\ClassDoc;
use yii\apidoc\models\InterfaceDoc;
use yii\apidoc\models\TraitDoc;
use yii\helpers\ArrayHelper;

/* @var $type ClassDoc|InterfaceDoc|TraitDoc */
/* @var $protected boolean */
/* @var $this yii\web\View */
/* @var $renderer \yii\apidoc\templates\html\ApiRenderer */

$renderer = $this->context;

if ($protected && count($type->getProtectedMethods()) == 0 || !$protected && count($type->getPublicMethods()) == 0) {
    return;
} ?>

<div class="summary doc-method">
<<<<<<< HEAD
<h2><?= $protected ? 'Protected Methods' : 'Public Methods' ?></h2>

<p><a href="#" class="toggle">Hide inherited methods</a></p>
=======
<h2><?= $protected ? '受保护的方法' : '公共方法' ?></h2>

<p><a href="#" class="toggle">隐藏继承方法</a></p>
>>>>>>> yiichina/master

<table class="summary-table table table-striped table-bordered table-hover">
<colgroup>
    <col class="col-method" />
    <col class="col-description" />
    <col class="col-defined" />
</colgroup>
<tr>
<<<<<<< HEAD
  <th>Method</th><th>Description</th><th>Defined By</th>
=======
  <th>方法</th><th>描述</th><th>定义在</th>
>>>>>>> yiichina/master
</tr>
<?php
$methods = $type->methods;
ArrayHelper::multisort($methods, 'name');
foreach ($methods as $method): ?>
    <?php if ($protected && $method->visibility == 'protected' || !$protected && $method->visibility != 'protected'): ?>
    <tr<?= $method->definedBy != $type->name ? ' class="inherited"' : '' ?> id="<?= $method->name ?>()">
        <td><?= $renderer->createSubjectLink($method, $method->name.'()') ?></td>
        <td><?= ApiMarkdown::process($method->shortDescription, $method->definedBy, true) ?></td>
        <td><?= $renderer->createTypeLink($method->definedBy, $type) ?></td>
    </tr>
    <?php endif; ?>
<?php endforeach; ?>
</table>
</div>
