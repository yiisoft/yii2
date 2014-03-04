<?php

/**
 * @var yii\apidoc\models\BaseDoc $object
 * @var yii\web\View $this
 */

$see = [];
foreach ($object->tags as $tag) {
	/** @var $tag phpDocumentor\Reflection\DocBlock\Tag\SeeTag */
	if (get_class($tag) == 'phpDocumentor\Reflection\DocBlock\Tag\SeeTag') {
		$ref = $tag->getReference();
		if (strpos($ref, '://') === false) {
			$see[] = '[[' . $ref . ']]';
		} else {
			$see[] = $ref;
		}
	}
}
if (empty($see)) {
	return;
}
?>
<div class="SeeAlso">
<h4>See Also</h4>
<ul>
<?php foreach($see as $ref): ?>
	<li><?= \yii\apidoc\helpers\ApiMarkdown::process($ref, $object->definedBy, true) ?></li>
<?php endforeach; ?>
</ul>
</div>
