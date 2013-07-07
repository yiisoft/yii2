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
?>
<div class="default-index">
	<div class="navbar">
		<div class="navbar-inner">
			<div class="container">
				<span class="brand">Yii Debugger</span>
			</div>
		</div>
	</div>

	<div class="container-fluid">
		<div class="row-fluid">
			<div class="span2">
					<ul class="nav nav-tabs nav-list nav-stacked">
						<?php
						foreach ($panels as $id => $panel) {
							$link = Html::a(Html::encode($panel->getName()), array('debug/default/index', 'tag' => $tag, 'panel' => $id));
							echo Html::tag('li', $link, array('class' => $panel === $activePanel ? 'active' : null));
						}
						?>
					</ul>
			</div><!--/span-->
			<div class="span10">
				<div class="meta alert alert-info">
					<div class="btn-group">
						<button class="btn dropdown-toggle" data-toggle="dropdown">
							View others
							<span class="caret"></span>
						</button>
						<ul class="dropdown-menu">
							<?php foreach ($manifest as $tag2 => $meta2) {
								$label = $meta2['method'] . ' ' . $meta2['url'] . ($meta2['ajax'] ? ' (AJAX)' : '')
									. ', ' . date('Y/m/d h:i:sa', $meta2['time'])
									. ', ' . $meta2['ip'] . ', ' . $tag2;
								$url = array('debug/default/index', 'tag' => $tag2);
								echo '<li>' . Html::a(Html::encode($label), $url) . '</li>';
							} ?>
						</ul>
					</div>
					Debugging:
					<?php echo $meta['method']; ?>
					<?php echo Html::a(Html::encode($meta['url']), $meta['url']); ?>
					<?php echo $meta['ajax'] ? ' (AJAX)' : ''; ?>
					at <?php echo date('Y/m/d h:i:sa', $meta['time']); ?>
					by <?php echo $meta['ip']; ?>
				</div>
				<?php echo $activePanel->getDetail(); ?>
			</div>
		</div>
	</div>
</div>
