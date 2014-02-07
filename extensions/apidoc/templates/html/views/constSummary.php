<?php

use yii\apidoc\helpers\ApiMarkdown;
use yii\apidoc\models\ClassDoc;
use yii\helpers\ArrayHelper;

/**
 * @var ClassDoc $type
 * @var yii\web\View $this
 */

if (empty($type->constants)) {
	return;
}
$constants = $type->constants;
ArrayHelper::multisort($constants, 'name');
?>
<div class="summary docConst">
<h2>Constants</h2>

<p><a href="#" class="toggle">Hide inherited constants</a></p>

<table class="summaryTable table table-striped table-bordered table-hover">
<colgroup>
	<col class="col-const" />
	<col class="col-description" />
	<col class="col-defined" />
</colgroup>
<tr>
  <th>Constant</th><th>Value</th><th>Description</th><th>Defined By</th>
</tr>
<?php foreach($constants as $constant): ?>
	<tr<?= $constant->definedBy != $type->name ? ' class="inherited"' : '' ?> id="<?= $constant->name ?>">
	  <td><?= $constant->name ?><a name="<?= $constant->name ?>-detail"></a></td>
	  <td><?= $constant->value ?></td>
	  <td><?= APiMarkdown::process($constant->shortDescription . "\n" . $constant->description, $constant->definedBy, true) ?></td>
	  <td><?= $this->context->typeLink($constant->definedBy) ?></td>
	</tr>
<?php endforeach; ?>
</table>
</div>