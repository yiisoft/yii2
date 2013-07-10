<?php

use yii\helpers\Html;

/**
 * @var \yii\base\View $this
 * @var array $summary
 * @var string $tag
 * @var array $manifest
 * @var \yii\debug\Panel[] $panels
 * @var \yii\debug\Panel $activePanel
 */

$this->registerAssetBundle('yii/bootstrap/dropdown');
$this->title = 'Yii Debugger';
?>
<div class="default-view">
	<div class="navbar">
		<div class="navbar-inner">
			<div class="container">
				<div class="yii-debug-toolbar-block title">
					Yii Debugger
				</div>
				<?php foreach ($panels as $panel): ?>
					<?php echo $panel->getSummary(); ?>
				<?php endforeach; ?>
			</div>
		</div>
	</div>

	<div class="container-fluid">
		<div class="row-fluid">
			<div class="span2">
					<ul class="nav nav-tabs nav-list nav-stacked">
						<?php
						foreach ($panels as $id => $panel) {
							$link = Html::a(Html::encode($panel->getName()), array('view', 'tag' => $tag, 'panel' => $id));
							echo Html::tag('li', $link, array('class' => $panel === $activePanel ? 'active' : null));
						}
						?>
					</ul>
			</div><!--/span-->
			<div class="span10">
				<div class="meta alert alert-info">
					<div class="btn-group">
						<?php echo Html::a('All', array('index'), array('class' => 'btn')); ?>
						<button class="btn dropdown-toggle" data-toggle="dropdown">
							Last 10
							<span class="caret"></span>
						</button>
						<ul class="dropdown-menu">
							<?php
							$count = 0;
							foreach ($manifest as $meta) {
								$label = $meta['tag'] . ': ' . $meta['method'] . ' ' . $meta['url'] . ($meta['ajax'] ? ' (AJAX)' : '')
									. ', ' . date('Y-m-d h:i:s a', $meta['time'])
									. ', ' . $meta['ip'];
								$url = array('view', 'tag' => $meta['tag'], 'panel' => $activePanel->id);
								echo '<li>' . Html::a(Html::encode($label), $url) . '</li>';
								if (++$count >= 10) {
									break;
								}
							}
							?>
						</ul>
					</div>
					<?php echo $summary['tag']; ?>:
					<?php echo $summary['method']; ?>
					<?php echo Html::a(Html::encode($summary['url']), $summary['url'], array('class' => 'label')); ?>
					<?php echo $summary['ajax'] ? ' (AJAX)' : ''; ?>
					at <?php echo date('Y-m-d h:i:s a', $summary['time']); ?>
					by <?php echo $summary['ip']; ?>
				</div>
				<?php echo $activePanel->getDetail(); ?>
			</div>
		</div>
	</div>
</div>
