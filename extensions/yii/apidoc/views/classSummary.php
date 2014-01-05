<?php

use yii\apidoc\models\ClassDoc;
use yii\apidoc\models\InterfaceDoc;
use yii\apidoc\models\TraitDoc;
/**
 * @var ClassDoc|InterfaceDoc|TraitDoc $item
 * @var yii\web\View $this
 */

?><table class="summaryTable docClass">
<colgroup>
	<col class="col-name" />
	<col class="col-value" />
</colgroup>
<?php if ($item instanceof ClassDoc): ?>
<tr>
  <th>Inheritance</th>
  <td><?php echo $this->context->renderInheritance($item); ?></td>
</tr>
<?php endif; ?>
<?php if(!empty($item->interfaces)): ?>
<tr>
  <th>Implements</th>
  <td><?php echo $this->context->renderImplements($item); ?></td>
</tr>
<?php endif; ?>
<?php if(!($item instanceof InterfaceDoc) && !empty($item->traits)): ?>
<tr>
  <th>Uses Traits</th>
  <td><?php echo $this->context->renderTraitUses($item); ?></td>
</tr>
<?php endif; ?>
<?php if($item instanceof ClassDoc && !empty($item->subclasses)): ?>
<tr>
  <th>Subclasses</th>
  <td><?php echo $this->context->renderSubclasses($item); ?></td>
</tr>
<?php endif; ?>
<?php if(!empty($item->since)): ?>
<tr>
  <th>Since</th>
  <td><?php echo $item->since; ?></td>
</tr>
<?php endif; ?>
<?php if(!empty($item->version)): ?>
<tr>
  <th>Version</th>
  <td><?php echo $item->version; ?></td>
</tr>
<?php endif; ?>
<tr>
  <th>Source Code</th>
<!--  <td>--><?php //echo $this->renderSourceLink($item->sourcePath); ?><!--</td>-->
</tr>
</table>

<div id="classDescription">
<?php echo $item->description; ?>
</div>