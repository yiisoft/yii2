<?php

use yii\apidoc\helpers\ApiMarkdown;
use yii\apidoc\models\ClassDoc;
use yii\apidoc\models\TraitDoc;
use yii\helpers\ArrayHelper;

/* @var $type ClassDoc|TraitDoc */
/* @var $protected boolean */
/* @var $this yii\web\View */
/* @var $renderer \yii\apidoc\templates\html\ApiRenderer */

$renderer = $this->context;

if ($protected && count($type->getProtectedProperties()) == 0 || !$protected && count($type->getPublicProperties()) == 0) {
    return;
} ?>

<div class="summary doc-property">
<h2><?= $protected ? '受保护的属性' : '公共属性' ?></h2>

<p><a href="#" class="toggle">隐藏继承属性</a></p>

<table class="summary-table table table-striped table-bordered table-hover">
<colgroup>
    <col class="col-property" />
    <col class="col-type" />
    <col class="col-description" />
    <col class="col-defined" />
</colgroup>
<tr>
  <th>属性</th><th>类型</th><th>描述</th><th>定义在</th>
</tr>
<?php
$properties = $type->properties;
ArrayHelper::multisort($properties, 'name');
foreach ($properties as $property): ?>
    <?php if ($protected && $property->visibility == 'protected' || !$protected && $property->visibility != 'protected'): ?>
    <tr<?= $property->definedBy != $type->name ? ' class="inherited"' : '' ?> id="<?= $property->name ?>">
        <td><?= $renderer->createSubjectLink($property) ?></td>
        <td><?= $renderer->createTypeLink($property->types) ?></td>
        <td><?= ApiMarkdown::process($property->shortDescription, $property->definedBy, true) ?></td>
        <td><?= $renderer->createTypeLink($property->definedBy) ?></td>
    </tr>
    <?php endif; ?>
<?php endforeach; ?>
</table>
</div>
