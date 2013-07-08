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
</div>
