<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\apidoc\helpers;

use cebe\markdown\latex\GithubMarkdown;
use yii\apidoc\models\TypeDoc;
use yii\apidoc\renderers\BaseRenderer;
use yii\helpers\Markdown;

/**
 * A Markdown helper with support for class reference links.
 *
 * @author Carsten Brandt <mail@cebe.cc>
 * @since 2.0
 */
class ApiMarkdownLaTeX extends GithubMarkdown
{
    use ApiMarkdownTrait;

    /**
     * @var BaseRenderer
     */
    public static $renderer;

    protected $renderingContext;


    protected function inlineMarkers()
    {
        return array_merge(parent::inlineMarkers(), [
            '[[' => 'parseApiLinksLatex',
        ]);
    }

    protected function parseApiLinksLatex($text)
    {
        list($html, $offset) = $this->parseApiLinks($text);

        $latex = '\texttt{'.str_replace(['\\textbackslash', '::'], ['\allowbreak{}\\textbackslash', '\allowbreak{}::\allowbreak{}'], $this->escapeLatex(strip_tags($html))).'}';

        return [$latex, $offset];
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
        if (!isset(Markdown::$flavors['api-latex'])) {
            Markdown::$flavors['api-latex'] = new static;
        }

        if (is_string($context)) {
            $context = static::$renderer->apiContext->getType($context);
        }
        Markdown::$flavors['api-latex']->renderingContext = $context;

        if ($paragraph) {
            return Markdown::processParagraph($content, 'api-latex');
        } else {
            return Markdown::process($content, 'api-latex');
        }
    }
}
