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
class ApiMarkdown extends Markdown
{
	/**
	 * @var BaseRenderer
	 */
	public static $renderer;
	/**
	 * @var ApiMarkdown
	 */
	private static $_instance;

	private $context;

	public function highlight(&$block, &$markup)
	{
		// TODO improve code highlighting
		if (strncmp($block['text'], '<?php', 5) === 0) {
			$text = highlight_string(trim($block['text']), true);
		} else {
			$text = highlight_string("<?php\n".trim($block['text']), true);
			$text = str_replace('&lt;?php', '', $text);
		}
		// remove <code> tags added by php
		$text = substr(trim($text), 6, -7);

		$code = '<pre><code';
		if (isset($block['language']))
		{
			if ($block['language'] !== 'php') {
				return false;
			}
			$code .= ' class="language-'.$block['language'].'"';
		}
		$code .= '>'.$text.'</code></pre>'."\n";

		$markup .= $code;
		return true;
	}

	public function init()
	{
		$this->registerBlockHander('code', [$this, 'highlight']);
		$this->registerBlockHander('fenced', [$this, 'highlight']);

		$context = &$this->context;
		// register marker for code links
		$this->unregisterInlineMarkerHandler('[');
		$this->registerInlineMarkerHandler('[[', function($text, &$markup) use (&$context) {

			if (preg_match('/^\[\[([\w\d\\\\\(\):$]+)(\|[^\]]*)?\]\]/', $text, $matches)) {

				$offset = strlen($matches[0]);

				$object = $matches[1];
				$title = (empty($matches[2]) || $matches[2] == '|') ? null : substr($matches[2], 1);

				if (($pos = strpos($object, '::')) !== false) {
					$typeName = substr($object, 0, $pos);
					$subjectName = substr($object, $pos + 2);
					if ($context !== null) {
						// Collection resolves relative types
						$typeName = (new Collection([$typeName], $context->phpDocContext))->__toString();
					}
					$type = static::$renderer->context->getType($typeName);
					if ($type === null) {
						static::$renderer->context->errors[] = [
							'file' => ($context !== null) ? $context->sourceFile : null,
							'message' => 'broken link to ' . $typeName . '::' . $subjectName . (($context !== null) ? ' in ' . $context->name : ''),
						];
						$markup .= '<span style="background: #f00;">' . $typeName . '::' . $subjectName . '</span>';
					} else {
						if (($subject = $type->findSubject($subjectName)) !== null) {
							if ($title === null) {
								$title = $type->name . '::' . $subject->name;
								if ($subject instanceof MethodDoc) {
									$title .= '()';
								}
							}
							$markup .= static::$renderer->subjectLink($subject, $title);
						} else {
							static::$renderer->context->errors[] = [
								'file' => ($context !== null) ? $context->sourceFile : null,
								'message' => 'broken link to ' . $type->name . '::' . $subjectName . (($context !== null) ? ' in ' . $context->name : ''),
							];
							$markup .= '<span style="background: #ff0;">' . $type->name . '</span><span style="background: #f00;">::' . $subjectName . '</span>';
						}
					}
					return $offset;
				} elseif ($context !== null && ($subject = $context->findSubject($object)) !== null) {
					$markup .= static::$renderer->subjectLink($subject, $title);
					return $offset;
				}
				if ($context !== null) {
					// Collection resolves relative types
					$object = (new Collection([$object], $context->phpDocContext))->__toString();
				}
				if (($type = static::$renderer->context->getType($object)) !== null) {
					$markup .= static::$renderer->typeLink($type, $title);
					return $offset;
				}
				static::$renderer->context->errors[] = [
					'file' => ($context !== null) ? $context->sourceFile : null,
					'message' => 'broken link to ' . $object . (($context !== null) ? ' in ' . $context->name : ''),
				];
				$markup .= '<span style="background: #f00;">' . $object . '</span>';
				return $offset;
			} else {
				$markup .= '[[';
				return 2;
			}
		});
		$this->registerInlineMarkerHandler('[', null);
	}

	/**
	 * Converts markdown into HTML
	 *
	 * @param string $content
	 * @param TypeDoc $context
	 * @return string
	 */
	public static function process($content, $context = null, $line = false)
	{
		if (static::$_instance === null) {
			static::$_instance = new static;
		}

		if (is_string($context)) {
			$context = static::$renderer->context->getType($context);
		}
		static::$_instance->context = $context;

		if ($line) {
			return static::$_instance->parseLine($content);
		} else {
			return static::$_instance->parse($content);
		}
	}
}
