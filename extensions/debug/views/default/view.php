<?php

use yii\bootstrap\ButtonDropdown;
use yii\bootstrap\ButtonGroup;
use yii\helpers\Html;

/**
 * @var \yii\base\View $this
 * @var array $summary
 * @var string $tag
 * @var array $manifest
 * @var \yii\debug\Panel[] $panels
 * @var \yii\debug\Panel $activePanel
 */

$this->title = 'Yii Debugger';
?>
<div class="default-view">
	<div id="yii-debug-toolbar">
		<div class="yii-debug-toolbar-block title">
			Yii Debugger
		</div>
		<?php foreach ($panels as $panel): ?>
			<?= $panel->getSummary() ?>
		<?php endforeach; ?>
	</div>

	<div class="container">
		<div class="row">
			<div class="col-lg-2">
				<div class="list-group">
					<?php
					foreach ($panels as $id => $panel) {
						$label = '<i class="glyphicon glyphicon-chevron-right"></i>' . Html::encode($panel->getName());
						echo Html::a($label, ['view', 'tag' => $tag, 'panel' => $id], [
							'class' => $panel === $activePanel ? 'list-group-item active' : 'list-group-item',
						]);
					}
					?>
				</div>
			</div>
			<div class="col-lg-10">
				<div class="callout callout-danger">
					<?php
						$count = 0;
						$items = [];
						foreach ($manifest as $meta) {
							$label = $meta['tag'] . ': ' . $meta['method'] . ' ' . $meta['url'] . ($meta['ajax'] ? ' (AJAX)' : '')
								. ', ' . date('Y-m-d h:i:s a', $meta['time'])
								. ', ' . $meta['ip'];
							$url = ['view', 'tag' => $meta['tag'], 'panel' => $activePanel->id];
							$items[] = [
								'label' => $label,
								'url' => $url,
							];
							if (++$count >= 10) {
								break;
							}
						}
						echo ButtonGroup::widget([
							'buttons' => [
								Html::a('All', ['index'], ['class' => 'btn btn-default']),
								ButtonDropdown::widget([
									'label' => 'Last 10',
									'options' => ['class' => 'btn-default'],
									'dropdown' => ['items' => $items],
								]),
							],
						]);
						echo "\n" . $summary['tag'] . ': ' . $summary['method'] . ' ' . Html::a(Html::encode($summary['url']), $summary['url']);
						echo ' at ' . date('Y-m-d h:i:s a', $summary['time']) . ' by ' . $summary['ip'];
					?>
				</div>
				<?= $activePanel->getDetail() ?>
			</div>
		</div>
	</div>
</div>
