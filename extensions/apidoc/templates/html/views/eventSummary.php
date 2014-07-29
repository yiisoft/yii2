<?php

use yii\apidoc\helpers\ApiMarkdown;
use yii\apidoc\models\ClassDoc;
use yii\helpers\ArrayHelper;

/* @var $type ClassDoc */
/* @var $this yii\web\View */
/* @var $renderer \yii\apidoc\templates\html\ApiRenderer */

$renderer = $this->context;

if (empty($type->events)) {
    return;
}
$events = $type->events;
ArrayHelper::multisort($events, 'name');
?>
<div class="summary doc-event">
    <h2>Events</h2>

    <p><a href="#" class="toggle">Hide inherited events</a></p>

    <table class="summary-table table table-striped table-bordered table-hover">
    <colgroup>
        <col class="col-event" />
        <col class="col-type" />
        <col class="col-description" />
        <col class="col-defined" />
    </colgroup>
    <tr>
        <th>Event</th><th>Type</th><th>Description</th><th>Defined By</th>
    </tr>
    <?php foreach ($events as $event): ?>
    <tr<?= $event->definedBy != $type->name ? ' class="inherited"' : '' ?> id="<?= $event->name ?>">
        <td><?= $renderer->createSubjectLink($event) ?></td>
        <td><?= $renderer->createTypeLink($event->types) ?></td>
        <td>
            <?= ApiMarkdown::process($event->shortDescription, $event->definedBy, true) ?>
            <?php if (!empty($event->since)): ?>
                (available since version <?= $event->since ?>)
            <?php endif; ?>
        </td>
        <td><?= $renderer->createTypeLink($event->definedBy) ?></td>
    </tr>
    <?php endforeach; ?>
    </table>
</div>
