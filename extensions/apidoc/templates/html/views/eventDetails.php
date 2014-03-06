<?php

use yii\apidoc\helpers\ApiMarkdown;
use yii\apidoc\models\ClassDoc;
use yii\helpers\ArrayHelper;

/**
 * @var ClassDoc $type
 * @var yii\web\View $this
 */

$events = $type->getNativeEvents();
if (empty($events)) {
	return;
}
ArrayHelper::multisort($events, 'name');
?>
<h2>Event Details</h2>
<?php foreach($events as $event): ?>
	<div class="detailHeader h3" id="<?= $event->name.'-detail' ?>">
		<?= $event->name ?>
		<span class="detailHeaderTag small">
		event
		<?php if(!empty($event->since)): ?>
			(available since version <?= $event->since ?>)
		<?php endif; ?>
		</span>
	</div>

	<?php /*
	<div class="signature">
		<?php echo $event->trigger->signature; ?>
	</div>*/ ?>

	<?= ApiMarkdown::process($event->description, $type); ?>

	<?= $this->render('seeAlso', ['object' => $event]); ?>

<?php endforeach; ?>
