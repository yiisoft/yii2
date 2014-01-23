<?php

use yii\apidoc\helpers\Markdown;
use yii\apidoc\models\ClassDoc;
/**
 * @var ClassDoc $type
 * @var yii\web\View $this
 */

$events = $type->getNativeEvents();
if (empty($events)) {
	return;
} ?>
<h2>Event Details</h2>
<?php foreach($events as $event): ?>
	<div class="detailHeader h3" id="<?= $event->name.'-detail' ?>">
		<?php echo $event->name; ?>
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

	<p><?= Markdown::process($event->description, $type); ?></p>

	<?= $this->render('seeAlso', ['object' => $event]); ?>

<?php endforeach; ?>
