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
	<div class="span3">
		<div class="well sidebar-nav">
			<ul class="nav nav-list">
			<?php
				foreach ($panels as $panel) {
					$link = Html::a(Html::encode($panel->getName()), array('debug/default/index', 'tag' => $tag, 'panel' => $panel->id));
					echo Html::tag('li', $link, array('class' => $panel === $activePanel ? 'active' : null));
				}
			?>
			</ul>
		</div><!--/.well -->
	</div><!--/span-->
	<div class="span9">
		<?php echo $activePanel->getDetail(); ?>
	</div>
</div>

