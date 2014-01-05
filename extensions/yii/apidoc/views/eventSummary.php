<?php

use yii\apidoc\models\ClassDoc;
/**
 * @var ClassDoc $item
 * @var yii\web\View $this
 */

if (empty($item->events)) {
	return;
} ?>
<div class="summary docEvent">
<h2>Events</h2>

<p><a href="#" class="toggle">Hide inherited events</a></p>

<table class="summaryTable">
<colgroup>
	<col class="col-event" />
	<col class="col-description" />
	<col class="col-defined" />
</colgroup>
<tr>
  <th>Event</th><th>Description</th><th>Defined By</th>
</tr>
<?php foreach($item->events as $event): ?>
<tr<?= $event->definedBy != $item->name ? ' class="inherited"' : '' ?> id="<?= $event->name ?>">
  <td><?= $this->context->subjectLink($event) ?></td>
  <td><?= $event->shortDescription ?></td>
  <td><?= $this->context->typeLink($event->definedBy) ?></td>
</tr>
<?php endforeach; ?>
</table>
</div>