<?php

use yii\apidoc\helpers\ApiMarkdown;
use yii\apidoc\models\ClassDoc;
use yii\apidoc\models\TraitDoc;
use yii\helpers\ArrayHelper;

/**
 * @var ClassDoc|TraitDoc $type
 * @var yii\web\View $this
 */

$methods = $type->getNativeMethods();
if (empty($methods)) {
	return;
}
ArrayHelper::multisort($methods, 'name');
?>
<h2>Method Details</h2>

<?php foreach($methods as $method): ?>

	<div class="detailHeader h3" id="<?= $method->name . '()-detail' ?>">
		<?= $method->name ?>()
		<span class="detailHeaderTag small">
			<?= $method->visibility ?>
			method
			<?php if (!empty($method->since)): ?>
				(available since version <?= $method->since ?>)
			<?php endif; ?>
		</span>
	</div>

	<table class="summaryTable table table-striped table-bordered table-hover">
		<tr><td colspan="3">
			<div class="signature2"><?= $this->context->renderMethodSignature($method) ?></div>
		</td></tr>
		<?php if (!empty($method->params) || !empty($method->return) || !empty($method->exceptions)): ?>
			<?php foreach($method->params as $param): ?>
				<tr>
				  <td class="paramNameCol"><?= $param->name ?></td>
				  <td class="paramTypeCol"><?= $this->context->typeLink($param->types) ?></td>
				  <td class="paramDescCol"><?= ApiMarkdown::process($param->description, $type) ?></td>
				</tr>
			<?php endforeach; ?>
			<?php if (!empty($method->return)): ?>
				<tr>
				  <td class="paramNameCol"><?= 'return'; ?></td>
				  <td class="paramTypeCol"><?= $this->context->typeLink($method->returnTypes); ?></td>
				  <td class="paramDescCol"><?= ApiMarkdown::process($method->return, $type); ?></td>
				</tr>
			<?php endif; ?>
			<?php foreach($method->exceptions as $exception => $description): ?>
				<tr>
				  <td class="paramNameCol"><?= 'throws' ?></td>
				  <td class="paramTypeCol"><?= $this->context->typeLink($exception) ?></td>
				  <td class="paramDescCol"><?= ApiMarkdown::process($description, $type) ?></td>
				</tr>
			<?php endforeach; ?>
		<?php endif; ?>
	</table>

<!--	--><?php //$this->renderPartial('sourceCode',array('object'=>$method)); ?>

	<p><strong><?= ApiMarkdown::process($method->shortDescription, $type, true) ?></strong></p>
	<?= ApiMarkdown::process($method->description, $type) ?>

	<?= $this->render('seeAlso', ['object' => $method]); ?>

<?php endforeach; ?>