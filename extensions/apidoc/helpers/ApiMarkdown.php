<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\apidoc\helpers;

use cebe\markdown\GithubMarkdown;
use phpDocumentor\Reflection\DocBlock\Type\Collection;
use yii\apidoc\models\MethodDoc;
use yii\apidoc\models\TypeDoc;
use yii\apidoc\renderers\BaseRenderer;
use yii\helpers\Inflector;
use yii\helpers\Markdown;

/**
 * A Markdown helper with support for class reference links.
 *
 * @author Carsten Brandt <mail@cebe.cc>
 * @since 2.0
 */
class ApiMarkdown extends GithubMarkdown
{
	/**
	 * @var BaseRenderer
	 */
	public static $renderer;

	protected $context;

	public function prepare()
	{
		parent::prepare();

		// add references to guide pages
		$this->references = array_merge($this->references, static::$renderer->guideReferences);
	}

	/**
	 * @inheritDoc
	 */
	protected function identifyLine($lines, $current)
	{
		if (strncmp($lines[$current], '~~~', 3) === 0) {
			return 'fencedCode';
		}
		return parent::identifyLine($lines, $current);
	}

	/**
	 * Consume lines for a fenced code block
	 */
	protected function consumeFencedCode($lines, $current)
	{
		// consume until ```
		$block = [
			'type' => 'code',
			'content' => [],
		];
		$line = rtrim($lines[$current]);
		if (strncmp($lines[$current], '~~~', 3) === 0) {
			$fence = '~~~';
			$language = 'php';
		} else {
			$fence = substr($line, 0, $pos = strrpos($line, '`') + 1);
			$language = substr($line, $pos);
		}
		if (!empty($language)) {
			$block['language'] = $language;
		}
		for($i = $current + 1, $count = count($lines); $i < $count; $i++) {
			if (rtrim($line = $lines[$i]) !== $fence) {
				$block['content'][] = $line;
			} else {
				break;
			}
		}
		return [$block, $i];
	}

	/**
	 * Renders a code block
	 */
	protected function renderCode($block)
	{
		if (isset($block['language'])) {
			$class = isset($block['language']) ? ' class="language-' . $block['language'] . '"' : '';
			return "<pre><code$class>" . $this->highlight(implode("\n", $block['content']) . "\n", $block['language']) . '</code></pre>';
		} else {
			return parent::renderCode($block);
		}
	}

	protected function highlight($code, $language)
	{
		if ($language !== 'php') {
			return htmlspecialchars($code, ENT_NOQUOTES, 'UTF-8');
		}

		// TODO improve code highlighting
		if (strncmp($code, '<?php', 5) === 0) {
			$text = @highlight_string(trim($code), true);
		} else {
			$text = highlight_string("<?php ".trim($code), true);
			$text = str_replace('&lt;?php', '', $text);
			if (($pos = strpos($text, '&nbsp;')) !== false) {
				$text = substr($text, 0, $pos) . substr($text, $pos + 6);
			}
		}
		// remove <code><span style="color: #000000">\n and </span>tags added by php
		$text = substr(trim($text), 36, -16);

		return $text;
	}

	protected function inlineMarkers()
	{
		return array_merge(parent::inlineMarkers(), [
			'[[' => 'parseApiLinks',
		]);
	}

	protected function parseApiLinks($text)
	{
		$context = $this->context;

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
				$type = static::$renderer->apiContext->getType($typeName);
				if ($type === null) {
					static::$renderer->apiContext->errors[] = [
						'file' => ($context !== null) ? $context->sourceFile : null,
						'message' => 'broken link to ' . $typeName . '::' . $subjectName . (($context !== null) ? ' in ' . $context->name : ''),
					];
					return [
						'<span style="background: #f00;">' . $typeName . '::' . $subjectName . '</span>',
						$offset
					];
				} else {
					if (($subject = $type->findSubject($subjectName)) !== null) {
						if ($title === null) {
							$title = $type->name . '::' . $subject->name;
							if ($subject instanceof MethodDoc) {
								$title .= '()';
							}
						}
						return [
							static::$renderer->createSubjectLink($subject, $title),
							$offset
						];
					} else {
						static::$renderer->apiContext->errors[] = [
							'file' => ($context !== null) ? $context->sourceFile : null,
							'message' => 'broken link to ' . $type->name . '::' . $subjectName . (($context !== null) ? ' in ' . $context->name : ''),
						];
						return [
							'<span style="background: #ff0;">' . $type->name . '</span><span style="background: #f00;">::' . $subjectName . '</span>',
							$offset
						];
					}
				}
			} elseif ($context !== null && ($subject = $context->findSubject($object)) !== null) {
				return [
					static::$renderer->createSubjectLink($subject, $title),
					$offset
				];
			}

			if ($context !== null) {
				// Collection resolves relative types
				$object = (new Collection([$object], $context->phpDocContext))->__toString();
			}
			if (($type = static::$renderer->apiContext->getType($object)) !== null) {
				return [
					static::$renderer->createTypeLink($type, null, $title),
					$offset
				];
			} elseif (strpos($typeLink = static::$renderer->createTypeLink($object, null, $title), '<a href') !== false) {
				return [
					$typeLink,
					$offset
				];
			}
			static::$renderer->apiContext->errors[] = [
				'file' => ($context !== null) ? $context->sourceFile : null,
				'message' => 'broken link to ' . $object . (($context !== null) ? ' in ' . $context->name : ''),
			];
			return [
				'<span style="background: #f00;">' . $object . '</span>',
				$offset
			];
		}
		return ['[[', 2];
	}

	/**
	 * @inheritDoc
	 */
	protected function renderHeadline($block)
	{
		$content = $this->parseInline($block['content']);
		$hash = Inflector::slug(strip_tags($content));
		$hashLink = "<a href=\"#$hash\" name=\"$hash\">&para;</a>";
		$tag = 'h' . $block['level'];
		return "<$tag>$content $hashLink</$tag>";
	}

	/**
	 * Converts markdown into HTML
	 *
	 * @param string $content
	 * @param TypeDoc $context
	 * @param bool $paragraph
	 * @return string
	 */
	public static function process($content, $context = null, $paragraph = false)
	{
		if (!isset(Markdown::$flavors['api'])) {
			Markdown::$flavors['api'] = new static;
		}

		if (is_string($context)) {
			$context = static::$renderer->apiContext->getType($context);
		}
		Markdown::$flavors['api']->context = $context;

		if ($paragraph) {
			return Markdown::processParagraph($content, 'api');
		} else {
			return Markdown::process($content, 'api');
		}
	}
}
