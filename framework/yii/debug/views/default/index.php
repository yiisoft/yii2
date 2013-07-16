<?php

use yii\helpers\Html;

/**
 * @var \yii\base\View $this
 * @var array $manifest
 */

$this->title = 'Yii Debugger';
?>
<div class="default-index">
	<div class="navbar">
		<div class="navbar-inner">
			<div class="container">
				<div class="yii-debug-toolbar-block title">
					Yii Debugger
				</div>
			</div>
		</div>
	</div>

	<div class="container-fluid">
		<div class="row-fluid">
			<h1>Available Debug Data</h1>
			<table class="table table-condensed table-bordered table-striped table-hover" style="table-layout: fixed;">
				<thead>
					<tr>
						<th style="width: 120px;">Tag</th>
						<th style="width: 160px;">Time</th>
						<th style="width: 120px;">IP</th>
						<th style="width: 60px;">Method</th>
						<th>URL</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($manifest as $tag => $data): ?>
					<tr>
						<td><?php echo Html::a($tag, array('view', 'tag' => $tag)); ?></td>
						<td><?php echo date('Y-m-d h:i:sa', $data['time']); ?></td>
						<td><?php echo $data['ip']; ?></td>
						<td><?php echo $data['method']; ?></td>
						<td><?php echo $data['url']; ?></td>
					</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
	</div>
</div>
