<?php 
use yii\helpers\Html; 
?>
<h1>Configuration</h1>
<?php
echo $this->context->renderPartial('panels/config/_data_table',[ 'caption' => 'Application Configuration', 'values' => $app]);

if (!empty($extensions)) {
	echo $this->context->renderPartial('panels/config/_data_table',[ 'caption' => 'Installed Extensions', 'values' => $extensions]);	
}

echo $this->context->renderPartial('panels/config/_data_table',[ 'caption' => 'PHP Configuration', 'values' => $php]);
?>
<div><?php echo Html::a('Show phpinfo »', ['phpinfo'], ['class' => 'btn btn-primary']); ?></div>