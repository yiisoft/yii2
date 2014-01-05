<?php

/**
 * @var yii\apidoc\models\BaseDoc $object
 * @var yii\web\View $this
 */

$see = [];
foreach($object->tags as $tag) {
	/** @var $tag phpDocumentor\Reflection\DocBlock\Tag\SeeTag */
	if (get_class($tag) == 'phpDocumentor\Reflection\DocBlock\Tag\SeeTag') {
		$see[] = $tag->getReference();
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
	<li><?= $ref ?></li>
<?php endforeach; ?>
</ul>
</div>
