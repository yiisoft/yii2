<?php

use yii\apidoc\helpers\Markdown;
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
		<?php if(!empty($method->params) || !empty($method->return) || !empty($method->exceptions)): ?>
			<?php foreach($method->params as $param): ?>
				<tr>
				  <td class="paramNameCol"><?= $param->name ?></td>
				  <td class="paramTypeCol"><?= $this->context->typeLink($param->types) ?></td>
				  <td class="paramDescCol"><?= Markdown::process($param->description, $type) ?></td>
				</tr>
			<?php endforeach; ?>
			<?php if(!empty($method->return)): ?>
				<tr>
				  <td class="paramNameCol"><?= 'return'; ?></td>
				  <td class="paramTypeCol"><?= $this->context->typeLink($method->returnTypes); ?></td>
				  <td class="paramDescCol"><?= Markdown::process($method->return, $type); ?></td>
				</tr>
			<?php endif; ?>
			<?php foreach($method->exceptions as $exception => $description): ?>
				<tr>
				  <td class="paramNameCol"><?= 'throws' ?></td>
				  <td class="paramTypeCol"><?= $this->context->typeLink($exception) ?></td>
				  <td class="paramDescCol"><?= Markdown::process($description, $type) ?></td>
				</tr>
			<?php endforeach; ?>
		<?php endif; ?>
	</table>

<!--	--><?php //$this->renderPartial('sourceCode',array('object'=>$method)); ?>

	<p><?= Markdown::process($method->shortDescription, $type) ?></strong></p>
	<p><?= Markdown::process($method->description, $type) ?></p>

	<?= $this->render('seeAlso', ['object' => $method]); ?>

<?php endforeach; ?>