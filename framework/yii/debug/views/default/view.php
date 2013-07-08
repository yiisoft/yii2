<?php

use yii\helpers\Html;

/**
 * @var \yii\base\View $this
 * @var array $meta
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
							foreach ($manifest as $tag2 => $meta2) {
								$label = $meta2['method'] . ' ' . $meta2['url'] . ($meta2['ajax'] ? ' (AJAX)' : '')
									. ', ' . date('Y-m-d h:i:sa', $meta2['time'])
									. ', ' . $meta2['ip'] . ', ' . $tag2;
								$url = array('view', 'tag' => $tag2);
								echo '<li>' . Html::a(Html::encode($label), $url) . '</li>';
								if (++$count >= 10) {
									break;
								}
							}
							?>
						</ul>
					</div>
					Debugging:
					<?php echo $meta['method']; ?>
					<?php echo Html::a(Html::encode($meta['url']), $meta['url']); ?>
					<?php echo $meta['ajax'] ? ' (AJAX)' : ''; ?>
					at <?php echo date('Y-m-d h:i:sa', $meta['time']); ?>
					by <?php echo $meta['ip']; ?>
				</div>
				<?php echo $activePanel->getDetail(); ?>
			</div>
		</div>
	</div>
</div>
