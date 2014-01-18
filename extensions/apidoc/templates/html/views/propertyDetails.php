<?php

use yii\apidoc\helpers\Markdown;
use yii\apidoc\models\ClassDoc;
use yii\apidoc\models\TraitDoc;
/**
 * @var ClassDoc|TraitDoc $type
 * @var yii\web\View $this
 */

$properties = $type->getNativeProperties();
if (empty($properties)) {
	return;
} ?>
<h2>Property Details</h2>

<?php foreach($properties as $property): ?>

	<div class="detailHeader" id="<?= $property->name.'-detail' ?>">
		<?php echo $property->name; ?>
		<span class="detailHeaderTag">
			property
			<?php if($property->getIsReadOnly()) echo ' <em>read-only</em> '; ?>
			<?php if($property->getIsWriteOnly()) echo ' <em>write-only</em> '; ?>
			<?php if(!empty($property->since)): ?>
				(available since version <?php echo $property->since; ?>)
			<?php endif; ?>
		</span>
	</div>

	<div class="signature">
	<?php echo $this->context->renderPropertySignature($property); ?>
	</div>

	<p><?= Markdown::process($property->description, $type) ?></p>

	<?= $this->render('seeAlso', ['object' => $property]); ?>

<?php endforeach; ?>
