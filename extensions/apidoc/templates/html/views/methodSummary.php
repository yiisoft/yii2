<?php

use yii\apidoc\helpers\ApiMarkdown;
use yii\apidoc\models\ClassDoc;
use yii\apidoc\models\InterfaceDoc;
use yii\apidoc\models\TraitDoc;
use yii\helpers\ArrayHelper;

/**
 * @var ClassDoc|InterfaceDoc|TraitDoc $type
 * @var boolean $protected
 * @var yii\web\View $this
 * @var \yii\apidoc\templates\html\ApiRenderer $renderer
 */

$renderer = $this->context;

if ($protected && count($type->getProtectedMethods()) == 0 || !$protected && count($type->getPublicMethods()) == 0) {
    return;
} ?>

<div class="summary doc-method">
<h2><?= $protected ? 'Protected Methods' : 'Public Methods' ?></h2>

<p><a href="#" class="toggle">Hide inherited methods</a></p>

<table class="summary-table table table-striped table-bordered table-hover">
<colgroup>
    <col class="col-method" />
    <col class="col-description" />
    <col class="col-defined" />
</colgroup>
<tr>
  <th>Method</th><th>Description</th><th>Defined By</th>
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
