<?php

use yii\apidoc\models\ClassDoc;
/**
 * @var ClassDoc $item
 * @var yii\web\View $this
 */

if (empty($item->constants)) {
	return;
} ?>
<div class="summary docConst">
<h2>Constants</h2>

<p><a href="#" class="toggle">Hide inherited constants</a></p>

<table class="summaryTable">
<colgroup>
	<col class="col-const" />
	<col class="col-description" />
	<col class="col-defined" />
</colgroup>
<tr>
  <th>Constant</th><th>Description</th><th>Defined By</th>
</tr>
<?php foreach($item->constants as $constant): ?>
<tr<?= $constant->definedBy != $item->name ? ' class="inherited"' : '' ?> id="<?= $constant->name ?>">
  <td><?= $this->context->subjectLink($constant) ?></td>
  <td><?= $constant->shortDescription ?></td>
  <td><?= $this->context->typeLink($constant->definedBy) ?></td>
</tr>
<?php endforeach; ?>
</table>
</div>