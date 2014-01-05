<?php

use yii\apidoc\components\OfflineRenderer;
use yii\apidoc\models\ClassDoc;
use yii\apidoc\models\InterfaceDoc;
use yii\apidoc\models\TraitDoc;
/**
 * @var ClassDoc|InterfaceDoc|TraitDoc $item
 * @var yii\web\View $this
 * @var OfflineRenderer $renderer
 */
$renderer = $this->context;

?><table class="summaryTable docClass">
	<colgroup>
		<col class="col-name" />
		<col class="col-value" />
	</colgroup>
	<?php if ($item instanceof ClassDoc): ?>
		<tr><th>Inheritance</th><td><?= $renderer->renderInheritance($item) ?></td></tr>
	<?php endif; ?>
	<?php if ($item instanceof ClassDoc && !empty($item->interfaces)): ?>
		<tr><th>Implements</th><td><?= $renderer->renderInterfaces($item->interfaces) ?></td></tr>
	<?php endif; ?>
	<?php if(!($item instanceof InterfaceDoc) && !empty($item->traits)): ?>
		<tr><th>Uses Traits</th><td><?= $renderer->renderTraits($item->traits) ?></td></tr>
	<?php endif; ?>
	<?php if($item instanceof ClassDoc && !empty($item->subclasses)): ?>
		<tr><th>Subclasses</th><td><?= $renderer->renderClasses($item->subclasses) ?></td></tr>
	<?php endif; ?>
	<?php if ($item instanceof InterfaceDoc && !empty($item->implementedBy)): ?>
		<tr><th>Implemented by</th><td><?= $renderer->renderClasses($item->implementedBy) ?></td></tr>
	<?php endif; ?>
	<?php if ($item instanceof TraitDoc && !empty($item->usedBy)): ?>
		<tr><th>Implemented by</th><td><?= $renderer->renderClasses($item->usedBy) ?></td></tr>
	<?php endif; ?>
	<?php if(!empty($item->since)): ?>
		<tr><th>Available since version</th><td><?= $item->since ?></td></tr>
	<?php endif; ?>
	<tr>
	  <th>Source Code</th>
	  <td><?php // TODO echo $this->renderSourceLink($item->sourcePath) ?></td>
	</tr>
</table>

<div id="classDescription">
	<strong><?= $item->shortDescription ?></strong>
	<p><?= nl2br($item->description) ?></p>
</div>