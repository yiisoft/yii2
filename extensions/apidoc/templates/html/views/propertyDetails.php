<?php

use yii\apidoc\helpers\ApiMarkdown;
use yii\apidoc\models\ClassDoc;
use yii\apidoc\models\TraitDoc;
use yii\helpers\ArrayHelper;

/**
 * @var ClassDoc|TraitDoc $type
 * @var yii\web\View $this
 */

$properties = $type->getNativeProperties();
if (empty($properties)) {
	return;
}
ArrayHelper::multisort($properties, 'name');
?>
<h2>Property Details</h2>

<?php foreach ($properties as $property): ?>

	<div class="detailHeader h3" id="<?= $property->name.'-detail' ?>">
		<?= $property->name ?>
		<span class="detailHeaderTag small">
			<?= $property->visibility ?>
			<?php if ($property->getIsReadOnly()) echo ' <em>read-only</em> '; ?>
			<?php if ($property->getIsWriteOnly()) echo ' <em>write-only</em> '; ?>
			property
			<?php if (!empty($property->since)): ?>
				(available since version <?= $property->since ?>)
			<?php endif; ?>
		</span>
	</div>

	<div class="signature"><?= $this->context->renderPropertySignature($property) ?></div>

	<?= ApiMarkdown::process($property->description, $type) ?>

	<?= $this->render('seeAlso', ['object' => $property]); ?>

<?php endforeach; ?>
