<?php

use yii\apidoc\helpers\Markdown;
use yii\apidoc\models\ClassDoc;
/**
 * @var ClassDoc $type
 * @var yii\web\View $this
 */

if (empty($type->events)) {
	return;
} ?>
<div class="summary docEvent">
<h2>Events</h2>

<p><a href="#" class="toggle">Hide inherited events</a></p>

<table class="summaryTable table table-striped table-bordered table-hover">
<colgroup>
	<col class="col-event" />
	<col class="col-description" />
	<col class="col-defined" />
</colgroup>
<tr>
  <th>Event</th><th>Type</th><th>Description</th><th>Defined By</th>
</tr>
<?php foreach($type->events as $event): ?>
<tr<?= $event->definedBy != $type->name ? ' class="inherited"' : '' ?> id="<?= $event->name ?>">
	<td><?= $this->context->subjectLink($event) ?></td>
	<td><?= $this->context->typeLink($event->types) ?></td>
	<td>
		<?= Markdown::process($event->shortDescription, $type) ?>
		<?php if(!empty($event->since)): ?>
			(available since version <?php echo $event->since; ?>)
		<?php endif; ?>
	</td>
	<td><?= $this->context->typeLink($event->definedBy) ?></td>
</tr>
<?php endforeach; ?>
</table>
</div>