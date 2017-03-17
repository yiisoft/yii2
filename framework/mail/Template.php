<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\mail;

use Yii;
use yii\base\Object;
use yii\base\ViewContextInterface;

/**
 * Template serves for the message composition from templates, ensuring view rendering in isolation mode.
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.1
 */
class Template extends Object implements ViewContextInterface
{
    /**
     * @var MessageInterface related mail message instance.
     */
    public $message;
    /**
     * @var \yii\base\View view instance, which should be used for rendering.
     */
    public $view;
    /**
     * @var string path ro the directory containing view files.
     */
    public $viewPath;
    /**
     * @var string|false HTML layout view name. This is the layout used to render HTML mail body.
     * The property can take the following values:
     *
     * - a relative view name: a view file relative to [[viewPath]], e.g., 'layouts/html'.
     * - a path alias: an absolute view file path specified as a path alias, e.g., '@app/mail/html'.
     * - a boolean false: the layout is disabled.
     */
    public $htmlLayout = false;
    /**
     * @var string|false text layout view name. This is the layout used to render TEXT mail body.
     * Please refer to [[htmlLayout]] for possible values that this property can take.
     */
    public $textLayout = false;


    /**
     * @inheritdoc
     */
    public function getViewPath()
    {
        return $this->viewPath;
    }

    /**
     * Renders this template.
     * @param string|array $view the view to be used for rendering the message body.
     * @param array $params the parameters (name-value pairs) that will be extracted and made available in the view file.
     */
    public function compose($view, $params = [])
    {
        if (is_array($view)) {
            if (isset($view['html'])) {
                $html = $this->render($view['html'], $params, $this->htmlLayout);
            }
            if (isset($view['text'])) {
                $text = $this->render($view['text'], $params, $this->textLayout);
            }
        } else {
            $html = $this->render($view, $params, $this->htmlLayout);
        }

        if (isset($html)) {
            $this->message->setHtmlBody($html);
        }
        if (isset($text)) {
            $this->message->setTextBody($text);
        } elseif (isset($html)) {
            if (preg_match('~<body[^>]*>(.*?)</body>~is', $html, $match)) {
                $html = $match[1];
            }
            // remove style and script
            $html = preg_replace('~<((style|script))[^>]*>(.*?)</\1>~is', '', $html);
            // strip all HTML tags and decoded HTML entities
            $text = html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, Yii::$app ? Yii::$app->charset : 'UTF-8');
            // improve whitespace
            $text = preg_replace("~^[ \t]+~m", '', trim($text));
            $text = preg_replace('~\R\R+~mu', "\n\n", $text);
            $this->message->setTextBody($text);
        }
    }

    /**
     * Renders the specified view with optional parameters and layout.
     * The view will be rendered using the [[view]] component.
     * @param string $view the view name or the path alias of the view file.
     * @param array $params the parameters (name-value pairs) that will be extracted and made available in the view file.
     * @param string|bool $layout layout view name or path alias. If false, no layout will be applied.
     * @return string the rendering result.
     */
    public function render($view, $params = [], $layout = false)
    {
        $output = $this->view->render($view, $params, $this);
        if ($layout === false) {
            return $output;
        }
        return $this->view->render($layout, ['content' => $output], $this);
    }
}