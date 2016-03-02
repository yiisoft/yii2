<?php

use yii\apidoc\helpers\ApiMarkdown;
use yii\apidoc\models\ClassDoc;
use yii\apidoc\models\InterfaceDoc;
use yii\apidoc\models\TraitDoc;

/* @var $type ClassDoc|InterfaceDoc|TraitDoc */
/* @var $this yii\web\View */
/* @var $renderer \yii\apidoc\templates\html\ApiRenderer */

$renderer = $this->context;
?>
<h1><?php
    if ($type instanceof InterfaceDoc) {
        echo 'Interface ';
    } elseif ($type instanceof TraitDoc) {
        echo 'Trait ';
    } else {
        if ($type->isFinal) {
            echo 'Final ';
        }
        if ($type->isAbstract) {
            echo 'Abstract ';
        }
        echo 'Class ';
    }
    echo $type->name;
?></h1>
<div id="nav">
<<<<<<< HEAD
    <a href="index.html">All Classes</a>
    <?php if (!($type instanceof InterfaceDoc) && !empty($type->properties)): ?>
        | <a href="#properties">Properties</a>
    <?php endif; ?>
    <?php if (!empty($type->methods)): ?>
        | <a href="#methods">Methods</a>
    <?php endif; ?>
    <?php if ($type instanceof ClassDoc && !empty($type->events)): ?>
        | <a href="#events">Events</a>
    <?php endif; ?>
    <?php if ($type instanceof ClassDoc && !empty($type->constants)): ?>
        | <a href="#constants">Constants</a>
=======
    <a href="/doc/api/2.0">所有类</a>
    <?php if (!($type instanceof InterfaceDoc) && !empty($type->properties)): ?>
        | <a href="#properties">属性</a>
    <?php endif; ?>
    <?php if (!empty($type->methods)): ?>
        | <a href="#methods">方法</a>
    <?php endif; ?>
    <?php if ($type instanceof ClassDoc && !empty($type->events)): ?>
        | <a href="#events">事件</a>
    <?php endif; ?>
    <?php if ($type instanceof ClassDoc && !empty($type->constants)): ?>
        | <a href="#constants">常量</a>
>>>>>>> yiichina/master
    <?php endif; ?>
</div>

<table class="summaryTable docClass table table-bordered">
    <colgroup>
        <col class="col-name" />
        <col class="col-value" />
    </colgroup>
    <?php if ($type instanceof ClassDoc): ?>
<<<<<<< HEAD
        <tr><th>Inheritance</th><td><?= $renderer->renderInheritance($type) ?></td></tr>
    <?php endif; ?>
    <?php if ($type instanceof ClassDoc && !empty($type->interfaces)): ?>
        <tr><th>Implements</th><td><?= $renderer->renderInterfaces($type->interfaces) ?></td></tr>
    <?php endif; ?>
    <?php if (!($type instanceof InterfaceDoc) && !empty($type->traits)): ?>
        <tr><th>Uses Traits</th><td><?= $renderer->renderTraits($type->traits) ?></td></tr>
    <?php endif; ?>
    <?php if ($type instanceof ClassDoc && !empty($type->subclasses)): ?>
        <tr><th>Subclasses</th><td><?= $renderer->renderClasses($type->subclasses) ?></td></tr>
    <?php endif; ?>
    <?php if ($type instanceof InterfaceDoc && !empty($type->implementedBy)): ?>
        <tr><th>Implemented by</th><td><?= $renderer->renderClasses($type->implementedBy) ?></td></tr>
    <?php endif; ?>
    <?php if ($type instanceof TraitDoc && !empty($type->usedBy)): ?>
        <tr><th>Implemented by</th><td><?= $renderer->renderClasses($type->usedBy) ?></td></tr>
    <?php endif; ?>
    <?php if (!empty($type->since)): ?>
        <tr><th>Available since version</th><td><?= $type->since ?></td></tr>
    <?php endif; ?>
    <?php if (($sourceUrl = $renderer->getSourceUrl($type)) !== null): ?>
        <tr>
          <th>Source Code</th>
=======
        <tr><th>继承</th><td><?= $renderer->renderInheritance($type) ?></td></tr>
    <?php endif; ?>
    <?php if ($type instanceof ClassDoc && !empty($type->interfaces)): ?>
        <tr><th>实现</th><td><?= $renderer->renderInterfaces($type->interfaces) ?></td></tr>
    <?php endif; ?>
    <?php if (!($type instanceof InterfaceDoc) && !empty($type->traits)): ?>
        <tr><th>使用特质</th><td><?= $renderer->renderTraits($type->traits) ?></td></tr>
    <?php endif; ?>
    <?php if ($type instanceof ClassDoc && !empty($type->subclasses)): ?>
        <tr><th>子类</th><td><?= $renderer->renderClasses($type->subclasses) ?></td></tr>
    <?php endif; ?>
    <?php if ($type instanceof InterfaceDoc && !empty($type->implementedBy)): ?>
        <tr><th>实现由</th><td><?= $renderer->renderClasses($type->implementedBy) ?></td></tr>
    <?php endif; ?>
    <?php if ($type instanceof TraitDoc && !empty($type->usedBy)): ?>
        <tr><th>实现由</th><td><?= $renderer->renderClasses($type->usedBy) ?></td></tr>
    <?php endif; ?>
    <?php if (!empty($type->since)): ?>
        <tr><th>可用自版本</th><td><?= $type->since ?></td></tr>
    <?php endif; ?>
    <?php if (($sourceUrl = $renderer->getSourceUrl($type)) !== null): ?>
        <tr>
          <th>源码</th>
>>>>>>> yiichina/master
          <td><a href="<?= $sourceUrl ?>"><?= $sourceUrl ?></a></td>
        </tr>
    <?php endif; ?>
</table>

<div id="classDescription">
    <p><strong><?= ApiMarkdown::process($type->shortDescription, $type, true) ?></strong></p>
    <?= ApiMarkdown::process($type->description, $type) ?>

    <?= $this->render('seeAlso', ['object' => $type]) ?>
</div>

<<<<<<< HEAD
<a id="properties"></a>
<?= $this->render('@yii/apidoc/templates/html/views/propertySummary', ['type' => $type, 'protected' => false]) ?>
<?= $this->render('@yii/apidoc/templates/html/views/propertySummary', ['type' => $type, 'protected' => true]) ?>

<a id="methods"></a>
<?= $this->render('@yii/apidoc/templates/html/views/methodSummary', ['type' => $type, 'protected' => false]) ?>
<?= $this->render('@yii/apidoc/templates/html/views/methodSummary', ['type' => $type, 'protected' => true]) ?>

<a id="events"></a>
<?= $this->render('@yii/apidoc/templates/html/views/eventSummary', ['type' => $type]) ?>

<a id="constants"></a>
=======
<a name="properties"></a>
<?= $this->render('@yii/apidoc/templates/html/views/propertySummary', ['type' => $type, 'protected' => false]) ?>
<?= $this->render('@yii/apidoc/templates/html/views/propertySummary', ['type' => $type, 'protected' => true]) ?>

<a name="methods"></a>
<?= $this->render('@yii/apidoc/templates/html/views/methodSummary', ['type' => $type, 'protected' => false]) ?>
<?= $this->render('@yii/apidoc/templates/html/views/methodSummary', ['type' => $type, 'protected' => true]) ?>

<a name="events"></a>
<?= $this->render('@yii/apidoc/templates/html/views/eventSummary', ['type' => $type]) ?>

<a name="constants"></a>
>>>>>>> yiichina/master
<?= $this->render('@yii/apidoc/templates/html/views/constSummary', ['type' => $type]) ?>

<?= $this->render('@yii/apidoc/templates/html/views/propertyDetails', ['type' => $type]) ?>
<?= $this->render('@yii/apidoc/templates/html/views/methodDetails', ['type' => $type]) ?>
<?php if ($type instanceof ClassDoc): ?>
    <?= $this->render('@yii/apidoc/templates/html/views/eventDetails', ['type' => $type]) ?>
<?php endif; ?>
