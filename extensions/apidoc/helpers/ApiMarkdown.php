<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\apidoc\helpers;

use cebe\markdown\GithubMarkdown;
use phpDocumentor\Reflection\DocBlock\Type\Collection;
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
    use ApiMarkdownTrait;

    /**
     * @var BaseRenderer
     */
    public static $renderer;

    protected $renderingContext;

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

    public static function highlight($code, $language)
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

    /**
     * @inheritDoc
     */
    protected function renderHeadline($block)
    {
        $content = $this->parseInline($block['content']);
        $hash = Inflector::slug(strip_tags($content));
        $hashLink = "<a href=\"#$hash\" name=\"$hash\" class=\"hashlink\">&para;</a>";
        $tag = 'h' . $block['level'];

        return "<$tag>$content $hashLink</$tag>";
    }

    /**
     * Converts markdown into HTML
     *
     * @param string $content
     * @param TypeDoc $context
     * @param boolean $paragraph
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
        Markdown::$flavors['api']->renderingContext = $context;

        if ($paragraph) {
            return Markdown::processParagraph($content, 'api');
        } else {
            return Markdown::process($content, 'api');
        }
    }
}
