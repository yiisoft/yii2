<?php

use yii\apidoc\helpers\Markdown;
use yii\apidoc\models\ClassDoc;
use yii\apidoc\models\InterfaceDoc;
use yii\apidoc\models\TraitDoc;
use yii\helpers\ArrayHelper;

/**
 * @var ClassDoc|InterfaceDoc|TraitDoc $type
 * @var boolean $protected
 * @var yii\web\View $this
 */

if ($protected && count($type->getProtectedMethods()) == 0 || !$protected && count($type->getPublicMethods()) == 0) {
	return;
} ?>

<div class="summary docMethod">
<h2><?= $protected ? 'Protected Methods' : 'Public Methods' ?></h2>

<p><a href="#" class="toggle">Hide inherited methods</a></p>

<table class="summaryTable table table-striped table-bordered table-hover">
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
foreach($methods as $method): ?>
	<?php if($protected && $method->visibility == 'protected' || !$protected && $method->visibility != 'protected'): ?>
	<tr<?= $method->definedBy != $type->name ? ' class="inherited"' : '' ?> id="<?= $method->name ?>()">
		<td><?= $this->context->subjectLink($method, $method->name.'()') ?></td>
		<td><?= Markdown::process($method->shortDescription, $method->definedBy) ?></td>
		<td><?= $this->context->typeLink($method->definedBy, $type) ?></td>
	</tr>
	<?php endif; ?>
<?php endforeach; ?>
</table>
</div>