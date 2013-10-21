<?php

use yii\helpers\Html;

/**
 * @var \yii\base\View $this
 * @var array $manifest
 */

$this->title = 'Yii Debugger';
?>
<div class="default-index">
	<div id="yii-debug-toolbar">
		<div class="yii-debug-toolbar-block title">
			Yii Debugger
		</div>
	</div>

	<div class="container">
		<div class="row">
			<h1>Available Debug Data</h1>
			<table class="table table-condensed table-bordered table-striped table-hover" style="table-layout: fixed;">
				<thead>
					<tr>
						<th style="width: 120px;">Tag</th>
						<th style="width: 170px;">Time</th>
						<th style="width: 120px;">IP</th>
						<th style="width: 70px;">Method</th>
						<th>URL</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($manifest as $tag => $data): ?>
					<tr>
						<td><?= Html::a($tag, ['view', 'tag' => $tag]) ?></td>
						<td><?= date('Y-m-d h:i:sa', $data['time']) ?></td>
						<td><?= $data['ip'] ?></td>
						<td><?= $data['method'] ?></td>
						<td><?= $data['url'] ?></td>
					</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
	</div>
</div>
