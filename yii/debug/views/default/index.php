<?php

use yii\helpers\Html;

/**
 * @var \yii\base\View $this
 * @var string $tag
 * @var \yii\debug\Panel[] $panels
 * @var \yii\debug\Panel $activePanel
 */
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
				<div class="well sidebar-nav">
					<ul class="nav nav-list">
						<?php
						foreach ($panels as $id => $panel) {
							$link = Html::a(Html::encode($panel->getName()), array('debug/default/index', 'tag' => $tag, 'panel' => $id));
							echo Html::tag('li', $link, array('class' => $panel === $activePanel ? 'active' : null));
						}
						?>
					</ul>
				</div><!--/.well -->
			</div><!--/span-->
			<div class="span10">
				<?php echo $activePanel->getDetail(); ?>
			</div>
		</div>
	</div>
</div>
