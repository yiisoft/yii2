<?php

use yii\phpdoc\models\ClassDoc;
use yii\phpdoc\models\InterfaceDoc;
use yii\phpdoc\models\TraitDoc;
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
</div>

<?= $this->render('classSummary', ['item' => $item]); ?>

<a name="properties"></a>
<?php //$this->renderPartial('propertySummary',array('class'=>$item,'protected'=>false)); ?>
<?php //$this->renderPartial('propertySummary',array('class'=>$item,'protected'=>true)); ?>

<a name="methods"></a>
<?php //$this->renderPartial('methodSummary',array('class'=>$item,'protected'=>false)); ?>
<?php //$this->renderPartial('methodSummary',array('class'=>$item,'protected'=>true)); ?>

<a name="events"></a>
<?php //$this->renderPartial('eventSummary',array('class'=>$item)); ?>

<?php //$this->renderPartial('propertyDetails',array('class'=>$item)); ?>
<?php //$this->renderPartial('methodDetails',array('class'=>$item)); ?>
