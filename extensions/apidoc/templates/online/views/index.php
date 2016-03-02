<?php

use yii\apidoc\models\ClassDoc;
use yii\apidoc\models\InterfaceDoc;
use yii\apidoc\models\TraitDoc;

/* @var $types ClassDoc[]|InterfaceDoc[]|TraitDoc[] */
/* @var $this yii\web\View */

<<<<<<< HEAD
?><h1>Class Reference</h1>
=======
?><h1>类参考手册</h1>
>>>>>>> yiichina/master

<table class="summaryTable docIndex">
    <colgroup>
        <col class="col-package" />
        <col class="col-class" />
        <col class="col-description" />
    </colgroup>
    <tr>
<<<<<<< HEAD
        <th>Class</th>
        <th>Description</th>
=======
        <th>类</th>
        <th>描述</th>
>>>>>>> yiichina/master
    </tr>
<?php
ksort($types);
foreach ($types as $i => $class):
?>
    <tr>
        <td><?= $this->context->createTypeLink($class, $class, $class->name) ?></td>
        <td><?= \yii\apidoc\helpers\ApiMarkdown::process($class->shortDescription, $class, true) ?></td>
    </tr>
<?php endforeach; ?>
</table>
