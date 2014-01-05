<?php

use yii\apidoc\models\ClassDoc;
use yii\apidoc\models\TraitDoc;
/**
 * @var ClassDoc|TraitDoc $type
 * @var yii\web\View $this
 */

$methods = $type->getNativeMethods();
if (empty($methods)) {
	return;
} ?>
<h2>Method Details</h2>

<?php foreach($methods as $method): ?>

	<div class="detailHeader" id="<?= $method->name . '()-detail' ?>">
		<?= $method->name ?>()
		<span class="detailHeaderTag">
			method
			<?php if (!empty($method->since)): ?>
				(available since version <?php echo $method->since; ?>)
			<?php endif; ?>
		</span>
	</div>

	<table class="summaryTable">
		<tr><td colspan="3">
			<div class="signature2">
			<?= $this->context->renderMethodSignature($method) ?>
			</div>
		</td></tr>
		<?php if(!empty($method->params) || !empty($method->return)): ?>
			<?php foreach($method->params as $param): ?>
				<tr>
				  <td class="paramNameCol"><?= $param->name ?></td>
				  <td class="paramTypeCol"><?= $this->context->typeLink($param->types) ?></td>
				  <td class="paramDescCol"><?= $param->description ?></td>
				</tr>
			<?php endforeach; ?>
			<?php if(!empty($method->return)): ?>
				<tr>
				  <td class="paramNameCol"><?= '{return}'; ?></td>
				  <td class="paramTypeCol"><?= $this->context->typeLink($method->returnTypes); ?></td>
				  <td class="paramDescCol"><?= $method->return; ?></td>
				</tr>
			<?php endif; ?>
		<?php endif; ?>
	</table>

<!--	--><?php //$this->renderPartial('sourceCode',array('object'=>$method)); ?>

	<p><strong><?= $method->shortDescription ?></strong></p>
	<p><?= nl2br($method->description) ?></p>

	<?= $this->render('seeAlso', ['object' => $method]); ?>

<?php endforeach; ?>