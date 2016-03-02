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
<<<<<<< HEAD
    <h2>Events</h2>

    <p><a href="#" class="toggle">Hide inherited events</a></p>
=======
    <h2>事件</h2>

    <p><a href="#" class="toggle">隐藏继承事件</a></p>
>>>>>>> yiichina/master

    <table class="summary-table table table-striped table-bordered table-hover">
    <colgroup>
        <col class="col-event" />
        <col class="col-type" />
        <col class="col-description" />
        <col class="col-defined" />
    </colgroup>
    <tr>
<<<<<<< HEAD
        <th>Event</th><th>Type</th><th>Description</th><th>Defined By</th>
=======
        <th>事件</th><th>类型</th><th>描述</th><th>定义在</th>
>>>>>>> yiichina/master
    </tr>
    <?php foreach ($events as $event): ?>
    <tr<?= $event->definedBy != $type->name ? ' class="inherited"' : '' ?> id="<?= $event->name ?>">
        <td><?= $renderer->createSubjectLink($event) ?></td>
        <td><?= $renderer->createTypeLink($event->types) ?></td>
        <td>
            <?= ApiMarkdown::process($event->shortDescription, $event->definedBy, true) ?>
            <?php if (!empty($event->since)): ?>
<<<<<<< HEAD
                (available since version <?= $event->since ?>)
=======
                （可用自版本 <?= $event->since ?>）
>>>>>>> yiichina/master
            <?php endif; ?>
        </td>
        <td><?= $renderer->createTypeLink($event->definedBy) ?></td>
    </tr>
    <?php endforeach; ?>
    </table>
</div>
