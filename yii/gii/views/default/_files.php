<?php

use yii\gii\Generator;
use yii\helpers\Html;
use yii\gii\CodeFile;

/**
 * @var $this \yii\base\View
 * @var $generator \yii\gii\Generator
 * @var CodeFile[] $files
 * @var array $answers
 */
?>
<table class="table table-bordered table-striped table-condensed code-files">
	<thead>
		<tr>
			<th class="file">Code File</th>
			<th class="action">Action</th>
			<th>
				<?php
				$count = 0;
				foreach ($files as $file) {
					if ($file->operation !== CodeFile::OP_SKIP) {
						$count++;
					}
				}
				if ($count > 1) {
					echo '<input type="checkbox" id="check-all">';
				}
				?>
			</th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ($files as $i => $file): ?>
		<tr class="<?php echo $file->operation; ?>">
			<td class="file">
				<?php echo Html::a(Html::encode($file->getRelativePath()), array('code', 'file' => $i), array('class' => 'view-code', 'rel' => $file->path)); ?>
				<?php if ($file->operation === CodeFile::OP_OVERWRITE): ?>
					<?php echo Html::a('diff', array('diff', 'file' => $i), array('class' => 'view-code label label-warning', 'rel' => $file->path)); ?>
				<?php endif; ?>
			</td>
			<td class="action">
				<?php
				if ($file->operation === CodeFile::OP_SKIP) {
					echo 'unchanged';
				} else {
					echo $file->operation;
				}
				?>
			</td>
			<td class="check">
				<?php
				if ($file->operation === CodeFile::OP_SKIP) {
					echo '&nbsp;';
				} else {
					$key = md5($file->path);
					echo Html::checkBox("answers[$key]", isset($answers) ? isset($answers[$key]) : ($file->operation === CodeFile::OP_NEW));
				}
				?>
			</td>
		</tr>
		<?php endforeach; ?>
	</tbody>
</table>
