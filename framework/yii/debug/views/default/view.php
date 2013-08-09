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
			<?php echo $panel->getSummary(); ?>
		<?php endforeach; ?>
	</div>

	<div class="container">
		<div class="row">
			<div class="col-lg-2">
				<ul class="nav nav-pills nav-stacked">
					<?php
					foreach ($panels as $id => $panel) {
						$link = Html::a(Html::encode($panel->getName()), array('view', 'tag' => $tag, 'panel' => $id));
						echo Html::tag('li', $link, array('class' => $panel === $activePanel ? 'active' : null));
					}
					?>
				</ul>
			</div><!--/span-->
			<div class="col-lg-10">
				<div class="callout callout-danger">
					<?php
						$count = 0;
						$items = array();
						foreach ($manifest as $meta) {
							$label = $meta['tag'] . ': ' . $meta['method'] . ' ' . $meta['url'] . ($meta['ajax'] ? ' (AJAX)' : '')
								. ', ' . date('Y-m-d h:i:s a', $meta['time'])
								. ', ' . $meta['ip'];
							$url = array('view', 'tag' => $meta['tag'], 'panel' => $activePanel->id);
							$items[] = array(
								'label' => $label,
								'url' => $url,
							);
							if (++$count >= 10) {
								break;
							}
						}
						echo ButtonGroup::widget(array(
							'buttons' => array(
								Html::a('All', array('index'), array('class' => 'btn btn-default')),
								ButtonDropdown::widget(array(
									'label' => 'Last 10',
									'options' => array('class' => 'btn-default'),
									'dropdown' => array('items' => $items),
								)),
							),
						));
						echo "\n" . $summary['tag'] . ': ' . $summary['method'] . ' ' . Html::a(Html::encode($summary['url']), $summary['url']);
						echo ' at ' . date('Y-m-d h:i:s a', $summary['time']) . ' by ' . $summary['ip'];
					?>
				</div>
				<?php echo $activePanel->getDetail(); ?>
			</div>
		</div>
	</div>
</div>
