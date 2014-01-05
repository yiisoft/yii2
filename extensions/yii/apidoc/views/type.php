<?php

use yii\apidoc\models\ClassDoc;
use yii\apidoc\models\InterfaceDoc;
use yii\apidoc\models\TraitDoc;
/**
 * @var ClassDoc|InterfaceDoc|TraitDoc $type
 * @var yii\web\View $this
 * @var \yii\apidoc\components\OfflineRenderer $renderer
 */

$renderer = $this->context;
?>
<h1><?php
	if ($type instanceof InterfaceDoc) {
		echo 'Interface ';
	} elseif ($type instanceof TraitDoc) {
		echo 'Trait ';
	} else {
		if ($type->isFinal) {
			echo 'Final ';
		}
		if ($type->isAbstract) {
			echo 'Abstract ';
		}
		echo 'Class ';
	}
	echo $type->name;
?></h1>
<div id="nav">
	<a href="index.html">All Classes</a>
	<?php if(!($type instanceof InterfaceDoc) && !empty($type->properties)): ?>
		| <a href="#properties">Properties</a>
	<?php endif; ?>
	<?php if(!empty($type->methods)): ?>
		| <a href="#methods">Methods</a>
	<?php endif; ?>
	<?php if($type instanceof ClassDoc && !empty($type->events)): ?>
		| <a href="#events">Events</a>
	<?php endif; ?>
	<?php if($type instanceof ClassDoc && !empty($type->constants)): ?>
		| <a href="#constants">Constants</a>
	<?php endif; ?>
</div>

<table class="summaryTable docClass">
	<colgroup>
		<col class="col-name" />
		<col class="col-value" />
	</colgroup>
	<?php if ($type instanceof ClassDoc): ?>
		<tr><th>Inheritance</th><td><?= $renderer->renderInheritance($type) ?></td></tr>
	<?php endif; ?>
	<?php if ($type instanceof ClassDoc && !empty($type->interfaces)): ?>
		<tr><th>Implements</th><td><?= $renderer->renderInterfaces($type->interfaces) ?></td></tr>
	<?php endif; ?>
	<?php if(!($type instanceof InterfaceDoc) && !empty($type->traits)): ?>
		<tr><th>Uses Traits</th><td><?= $renderer->renderTraits($type->traits) ?></td></tr>
	<?php endif; ?>
	<?php if($type instanceof ClassDoc && !empty($type->subclasses)): ?>
		<tr><th>Subclasses</th><td><?= $renderer->renderClasses($type->subclasses) ?></td></tr>
	<?php endif; ?>
	<?php if ($type instanceof InterfaceDoc && !empty($type->implementedBy)): ?>
		<tr><th>Implemented by</th><td><?= $renderer->renderClasses($type->implementedBy) ?></td></tr>
	<?php endif; ?>
	<?php if ($type instanceof TraitDoc && !empty($type->usedBy)): ?>
		<tr><th>Implemented by</th><td><?= $renderer->renderClasses($type->usedBy) ?></td></tr>
	<?php endif; ?>
	<?php if(!empty($type->since)): ?>
		<tr><th>Available since version</th><td><?= $type->since ?></td></tr>
	<?php endif; ?>
	<tr>
	  <th>Source Code</th>
	  <td><?php // TODO echo $this->renderSourceLink($type->sourcePath) ?></td>
	</tr>
</table>

<div id="classDescription">
	<strong><?= $type->shortDescription ?></strong>
	<p><?= nl2br($type->description) ?></p>
</div>

<a name="properties"></a>
<?= $this->render('propertySummary', ['type' => $type,'protected' => false]) ?>
<?= $this->render('propertySummary', ['type' => $type,'protected' => true]) ?>

<a name="methods"></a>
<?= $this->render('methodSummary', ['type' => $type, 'protected' => false]) ?>
<?= $this->render('methodSummary', ['type' => $type, 'protected' => true]) ?>

<a name="events"></a>
<?= $this->render('eventSummary', ['type' => $type]) ?>

<a name="constants"></a>
<?= $this->render('constSummary', ['type' => $type]) ?>

<?php //$this->renderPartial('propertyDetails',array('type'=>$type)); ?>
<?php //$this->renderPartial('methodDetails',array('type'=>$type)); ?>
