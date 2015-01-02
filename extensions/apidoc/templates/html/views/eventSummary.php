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
    <h2>事件</h2>

    <p><a href="#" class="toggle">隐藏继承事件</a></p>

    <table class="summary-table table table-striped table-bordered table-hover">
    <colgroup>
        <col class="col-event" />
        <col class="col-type" />
        <col class="col-description" />
        <col class="col-defined" />
    </colgroup>
    <tr>
        <th>事件</th><th>类型</th><th>描述</th><th>定义在</th>
    </tr>
    <?php foreach ($events as $event): ?>
    <tr<?= $event->definedBy != $type->name ? ' class="inherited"' : '' ?> id="<?= $event->name ?>">
        <td><?= $renderer->createSubjectLink($event) ?></td>
        <td><?= $renderer->createTypeLink($event->types) ?></td>
        <td>
            <?= ApiMarkdown::process($event->shortDescription, $event->definedBy, true) ?>
            <?php if (!empty($event->since)): ?>
                （可用自版本 <?= $event->since ?>）
            <?php endif; ?>
        </td>
        <td><?= $renderer->createTypeLink($event->definedBy) ?></td>
    </tr>
    <?php endforeach; ?>
    </table>
</div>
