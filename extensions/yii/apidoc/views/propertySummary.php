<?php

use yii\apidoc\models\ClassDoc;
use yii\apidoc\models\InterfaceDoc;
use yii\apidoc\models\TraitDoc;
/**
 * @var ClassDoc|InterfaceDoc|TraitDoc $type
 * @var boolean $protected
 * @var yii\web\View $this
 */

if ($protected && count($type->getProtectedProperties()) == 0 || !$protected && count($type->getPublicProperties()) == 0) {
	return;
} ?>

<div class="summary docProperty">
<h2><?= $protected ? 'Protected Properties' : 'Public Properties' ?></h2>

<p><a href="#" class="toggle">Hide inherited properties</a></p>

<table class="summaryTable">
<colgroup>
	<col class="col-property" />
	<col class="col-type" />
	<col class="col-description" />
	<col class="col-defined" />
</colgroup>
<tr>
  <th>Property</th><th>Type</th><th>Description</th><th>Defined By</th>
</tr>
<?php foreach($type->properties as $property): ?>
<?php if($protected && $property->visibility == 'protected' || !$protected && $property->visibility != 'protected'): ?>
<tr<?= $property->definedBy != $type->name ? ' class="inherited"' : '' ?> id="<?= $property->name ?>">
  <td><?php echo $this->context->subjectLink($property); ?></td>
  <td><?php echo $this->context->typeLink($property->types); ?></td>
  <td><?php echo $property->shortDescription; ?></td>
  <td><?php echo $this->context->typeLink($property->definedBy); ?></td>
</tr>
<?php endif; ?>
<?php endforeach; ?>
</table>
</div>