<?php

/* @var $object yii\apidoc\models\BaseDoc */
/* @var $this yii\web\View */

$type = $object instanceof \yii\apidoc\models\TypeDoc ? $object : $object->definedBy;

$see = [];
foreach ($object->tags as $tag) {
    /** @var $tag phpDocumentor\Reflection\DocBlock\Tag\SeeTag */
    if (get_class($tag) == 'phpDocumentor\Reflection\DocBlock\Tag\SeeTag') {
        $ref = $tag->getReference();
        if (strpos($ref, '://') === false) {
            $ref = '[[' . $ref . ']]';
        }
        $see[] = rtrim(\yii\apidoc\helpers\ApiMarkdown::process($ref . ' ' . $tag->getDescription(), $type, true), ". \r\n");
    }
}
if (empty($see)) {
    return;
} elseif (count($see) == 1) {
    echo '<p>See also ' . reset($see) . '.</p>';
} else {
    echo '<p>See also:</p><ul>';
    foreach ($see as $ref) {
        if (substr($ref, -1, 1) != '>') {
            $ref .= '.';
        }
        echo "<li>$ref</li>";
    }
    echo '</ul>';
}
