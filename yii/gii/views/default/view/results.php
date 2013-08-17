<?php

use yii\gii\Generator;
use yii\gii\CodeFile;

/**
 * @var yii\base\View $this
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
	<pre><?php echo $results; ?></pre>
</div>
