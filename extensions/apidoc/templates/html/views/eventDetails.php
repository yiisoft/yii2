<?php

use yii\apidoc\helpers\ApiMarkdown;
use yii\apidoc\models\ClassDoc;
use yii\helpers\ArrayHelper;

/**
 * @var ClassDoc $type
 * @var yii\web\View $this
 * @var \yii\apidoc\templates\html\ApiRenderer $renderer
 */

$renderer = $this->context;

$events = $type->getNativeEvents();
if (empty($events)) {
    return;
}
ArrayHelper::multisort($events, 'name');
?>
<h2>Event Details</h2>

<div class="event-doc">
<?php foreach ($events as $event): ?>
    <div class="detail-header h3" id="<?= $event->name.'-detail' ?>">
        <a href="#" class="tool-link" title="go to top"><span class="glyphicon glyphicon-arrow-up"></span></a>
        <?= $renderer->createSubjectLink($event, '<span class="glyphicon icon-hash"></span>', [
            'title' => 'direct link to this method',
            'class' => 'tool-link hash',
        ]) ?>

        <?php if (($sourceUrl = $renderer->getSourceUrl($event->definedBy, $event->startLine)) !== null): ?>
            <a href="<?= str_replace('/blob/', '/edit/', $sourceUrl) ?>" class="tool-link" title="edit on github"><span class="glyphicon glyphicon-pencil"></span></a>
            <a href="<?= $sourceUrl ?>" class="tool-link" title="view source on github"><span class="glyphicon glyphicon-eye-open"></span></a>
        <?php endif; ?>

        <?= $event->name ?>
        <span class="detail-header-tag small">
        event
        <?php if (!empty($event->since)): ?>
            (available since version <?= $event->since ?>)
        <?php endif; ?>
        </span>
    </div>

    <div class="doc-description">
        <?= ApiMarkdown::process($event->description, $type) ?>

        <?= $this->render('seeAlso', ['object' => $event]) ?>
    </div>

<?php endforeach; ?>
</div>
