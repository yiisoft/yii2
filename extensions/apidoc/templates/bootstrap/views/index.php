<?php

use yii\apidoc\models\ClassDoc;
use yii\apidoc\models\InterfaceDoc;
use yii\apidoc\models\TraitDoc;

/* @var $types ClassDoc[]|InterfaceDoc[]|TraitDoc[] */
/* @var $this yii\web\View */
/* @var $renderer \yii\apidoc\templates\html\ApiRenderer */

$renderer = $this->context;

if (isset($readme)) {
    echo \yii\apidoc\helpers\ApiMarkdown::process($readme);
}

?><h1>Class Reference</h1>

<table class="summaryTable docIndex table table-bordered table-striped table-hover">
    <colgroup>
        <col class="col-package" />
        <col class="col-class" />
        <col class="col-description" />
    </colgroup>
    <tr>
        <th>Class</th>
        <th>Description</th>
    </tr>
<?php
ksort($types);
foreach ($types as $i => $class):
?>
    <tr>
        <td><?= $renderer->createTypeLink($class, $class, $class->name) ?></td>
        <td><?= \yii\apidoc\helpers\ApiMarkdown::process($class->shortDescription, $class, true) ?></td>
    </tr>
<?php endforeach; ?>
</table>
