<?php

use yii\apidoc\models\ClassDoc;
use yii\apidoc\models\InterfaceDoc;
use yii\apidoc\models\TraitDoc;
/**
 * @var ClassDoc[]|InterfaceDoc[]|TraitDoc[] $items
 * @var yii\web\View $this
 */

?><h1>Class Reference</h1>

<table class="summaryTable docIndex">
<colgroup>
	<col class="col-package" />
	<col class="col-class" />
	<col class="col-description" />
</colgroup>
<tr>
  <th>Class</th><th>Description</th>
</tr>
<?php
ksort($items);
foreach($items as $i=>$class): ?>
<tr>
  <td><?php echo $this->context->typeLink($class, $class->name); ?></td>
  <td><?php echo $class->shortDescription; ?></td>
</tr>
<?php endforeach; ?>
</table>
