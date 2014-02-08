<?php

use yii\helpers\Html;
use yii\gii\CodeFile;

/**
 * @var \yii\web\View $this
 * @var \yii\gii\Generator $generator
 * @var CodeFile[] $files
 * @var array $answers
 */
?>
<div class="default-view-files">
	<p>Click on the above <code>Generate</code> button to generate the files selected below:</p>

	<table class="table table-bordered table-striped table-condensed">
		<thead>
			<tr>
				<th class="file">Code File</th>
				<th class="action">Action</th>
				<?php
				$fileChangeExists = false;
				foreach ($files as $file) {
					if ($file->operation !== CodeFile::OP_SKIP) {
						$fileChangeExists = true;
						echo '<th><input type="checkbox" id="check-all"></th>';
						break;
					}
				}
				?>
				
			</tr>
		</thead>
		<tbody>
			<?php foreach ($files as $file): ?>
			<?php
			if ($file->operation === CodeFile::OP_OVERWRITE) {
				$trClass = 'warning';
			} elseif ($file->operation === CodeFile::OP_SKIP) {
				$trClass = 'active';
			} elseif ($file->operation === CodeFile::OP_CREATE) {
				$trClass = 'success';
			} else {
				$trClass = '';
			}
			?>
			<tr class="<?= "$file->operation $trClass" ?>">
				<td class="file">
					<?= Html::a(Html::encode($file->getRelativePath()), ['preview', 'file' => $file->id], ['class' => 'preview-code', 'data-title' => $file->getRelativePath()]) ?>
					<?php if ($file->operation === CodeFile::OP_OVERWRITE): ?>
						<?= Html::a('diff', ['diff', 'file' => $file->id], ['class' => 'diff-code label label-warning', 'data-title' => $file->getRelativePath()]) ?>
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
				<?php if ($fileChangeExists): ?>
				<td class="check">
					<?php
					if ($file->operation === CodeFile::OP_SKIP) {
						echo '&nbsp;';
					} else {
						echo Html::checkBox("answers[{$file->id}]", isset($answers) ? isset($answers[$file->id]) : ($file->operation === CodeFile::OP_CREATE));
					}
					?>
				</td>
				<?php endif; ?>
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>

	<div class="modal fade" id="preview-modal" tabindex="-1" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
					<h4><a class="modal-refresh glyphicon glyphicon-refresh" href="#"></a> <span class="modal-title">Modal title</span></h4>
				</div>
				<div class="modal-body">
					<p>Please wait ...</p>
				</div>
			</div>
		</div>
	</div>
</div>
