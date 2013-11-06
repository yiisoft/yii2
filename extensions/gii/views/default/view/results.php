<?php
/**
 * @var yii\web\View $this
 * @var yii\gii\Generator $generator
 * @var string $results
 * @var boolean $hasError
 */
?>
<div class="default-view-results">
	<?php
	if ($hasError) {
		echo '<div class="alert alert-danger">There was something wrong when generating the code. Please check the following messages.</div>';
	} else {
		echo '<div class="alert alert-success">' . $generator->successMessage() . '</div>';
	}
	?>
	<pre><?= nl2br($results) ?></pre>
</div>
