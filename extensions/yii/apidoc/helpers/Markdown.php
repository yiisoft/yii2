<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\apidoc\helpers;

use phpDocumentor\Reflection\DocBlock\Type\Collection;
use yii\apidoc\models\MethodDoc;
use yii\apidoc\models\TypeDoc;
use yii\apidoc\templates\BaseRenderer;

/**
 * A Markdown helper with support for class reference links.
 *
 * @author Carsten Brandt <mail@cebe.cc>
 * @since 2.0
 */
class Markdown extends \yii\helpers\Markdown
{
	/**
	 * @var BaseRenderer
	 */
	public static $renderer;

	/**
	 * Converts markdown into HTML
	 *
	 * @param string $content
	 * @param TypeDoc $context
	 * @return string
	 */
	public static function process($content, $context)
	{
		$content = trim(parent::process($content, []));
		if (!strncmp($content, '<p>', 3) && substr($content, -4, 4) == '</p>') {
			$content = substr($content, 3, -4);
		}

		$content = preg_replace_callback('/\[\[([\w\d\\\\\(\):]+)(\|[\w\d ]*)?\]\]/xm', function($matches) use ($context) {
			$object = $matches[1];
			$title = (empty($matches[2]) || $matches[2] == '|') ? null : substr($matches[2], 1);

			if (($pos = strpos($object, '::')) !== false) {
				$typeName = substr($object, 0, $pos);
				$subjectName = substr($object, $pos + 2);
				// Collection resolves relative types
				$typeName = (new Collection([$typeName], $context->phpDocContext))->__toString();
				$type = static::$renderer->context->getType($typeName);
				if ($type === null) {
					return '<span style="background: #f00;">' . $typeName . '::' . $subjectName . '</span>';
				} else {
					if (($subject = $type->findSubject($subjectName)) !== null) {
						if ($title === null) {
							$title = $type->name . '::' . $subject->name;
							if ($subject instanceof MethodDoc) {
								$title .= '()';
							}
						}
						return static::$renderer->subjectLink($subject, $title);
					} else {
						return '<span style="background: #ff0;">' . $type->name . '</span><span style="background: #f00;">::' . $subjectName . '</span>';
					}
				}
			} elseif (($subject = $context->findSubject($object)) !== null) {
				return static::$renderer->subjectLink($subject, $title);
			}
			// Collection resolves relative types
			$object = (new Collection([$object], $context->phpDocContext))->__toString();
			if (($type = static::$renderer->context->getType($object)) !== null) {
				return static::$renderer->typeLink($type, $title);
			}
			return '<span style="background: #f00;">' . $object . '</span>';
		}, $content);

		return $content;
	}
}