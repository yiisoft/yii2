<?php
use yii\helpers\Html;

if (empty($values)): ?>
<h3><?php echo $caption; ?></h3>
<p>Empty.</p>
<?php else:	?>
<h3><?php echo $caption; ?></h3>
<table class="table table-condensed table-bordered table-striped table-hover" style="table-layout: fixed;">
	<thead>
		<tr>
			<th style="width: 200px;">Name</th>
			<th>Value</th>
		</tr>
	</thead>
	<?php foreach($values as $name => $value): ?>
		<tr>
			<th style="width: 200px;"><?php echo Html::encode($name); ?></th>
			<td><?php echo htmlspecialchars(var_export($value, true), ENT_QUOTES|ENT_SUBSTITUTE, \Yii::$app->charset, TRUE); ?></td>
		</tr>
	<?php endforeach; ?>
</tbody>
</table>
<?php endif; ?>