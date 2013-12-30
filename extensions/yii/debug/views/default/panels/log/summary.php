<?php
use yii\log\Target;
use yii\log\Logger;
?>

<?php
$title = 'Logged ' . count($data['messages']) . ' messages';
$errorCount = count(Target::filterMessages($data['messages'], Logger::LEVEL_ERROR));
$warningCount = count(Target::filterMessages($data['messages'], Logger::LEVEL_WARNING));
$output = [];

if ($errorCount) {
	$output[] = "<span class=\"label label-important\">$errorCount</span>";
	$title .= ", $errorCount errors";
}

if ($warningCount) {
	$output[] = "<span class=\"label label-warning\">$warningCount</span>";
	$title .= ", $warningCount warnings";
}
?>

<div class="yii-debug-toolbar-block">
	<a href="<?php echo $panel->getUrl(); ?>" title="<?php echo $title ?>">Log
		<span class="label"><?php echo count($data['messages']); ?></span>
		<?php echo implode('&nbsp;', $output); ?>
	</a>
</div>