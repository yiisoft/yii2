<?php

use yii\apidoc\models\ClassDoc;
use yii\apidoc\models\InterfaceDoc;
use yii\apidoc\models\TraitDoc;
/**
 * @var ClassDoc|InterfaceDoc|TraitDoc $item
 * @var yii\web\View $this
 */

?>
<h1><?php
	if ($item instanceof InterfaceDoc) {
		echo 'Interface ';
	} elseif ($item instanceof TraitDoc) {
		echo 'Trait ';
	} else {
		if ($item->isFinal) {
			echo 'Final ';
		}
		if ($item->isAbstract) {
			echo 'Abstract ';
		}
		echo 'Class ';
	}
	echo $item->name;
?></h1>
<div id="nav">
	<a href="index.html">All Classes</a>
	<?php if(!($item instanceof InterfaceDoc) && !empty($item->properties)): ?>
		| <a href="#properties">Properties</a>
	<?php endif; ?>
	<?php if(!empty($item->methods)): ?>
		| <a href="#methods">Methods</a>
	<?php endif; ?>
	<?php if($item instanceof ClassDoc && !empty($item->events)): ?>
		| <a href="#events">Events</a>
	<?php endif; ?>
	<?php if($item instanceof ClassDoc && !empty($item->constants)): ?>
		| <a href="#constants">Constants</a>
	<?php endif; ?>
</div>

<?= $this->render('classSummary', ['item' => $item]) ?>

<a name="properties"></a>
<?= $this->render('propertySummary', ['item' => $item,'protected' => false]) ?>
<?= $this->render('propertySummary', ['item' => $item,'protected' => true]) ?>

<a name="methods"></a>
<?= $this->render('methodSummary', ['item' => $item, 'protected' => false]) ?>
<?= $this->render('methodSummary', ['item' => $item, 'protected' => true]) ?>

<a name="events"></a>
<?= $this->render('eventSummary', ['item' => $item]) ?>

<a name="constants"></a>
<?= $this->render('constSummary', ['item' => $item]) ?>

<?php //$this->renderPartial('propertyDetails',array('class'=>$item)); ?>
<?php //$this->renderPartial('methodDetails',array('class'=>$item)); ?>
