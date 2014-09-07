<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\smarty;

use Smarty;
use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\StringHelper;
use yii\helpers\Url;
use yii\web\View;

/**
 * Extension provides Yii-specific syntax for Smarty templates.
 *
 * @author Alexander Makarov <sam@rmcreative.ru>
 * @author Henrik Maier <hwmaier@gmail.com>
 */
class Extension
{
    /**
     * @var ViewRenderer
     */
    protected $viewRenderer;

    /**
     * @var Smarty
     */
    protected $smarty;

    /**
     * @param ViewRenderer $viewRenderer
     * @param Smarty $smarty
     */
    public function __construct($viewRenderer, $smarty)
    {
        $this->viewRenderer = $viewRenderer;
        $smarty = $this->smarty = $smarty;

        $smarty->registerPlugin('function', 'path', [$this, 'functionPath']);
        $smarty->registerPlugin('function', 'url', [$this, 'functionUrl']);
        $smarty->registerPlugin('function', 'set', [$this, 'functionSet']);
        $smarty->registerPlugin('function', 'meta', [$this, 'functionMeta']);
        $smarty->registerPlugin('function', 'registerJsFile', [$this, 'functionRegisterJsFile']);
        $smarty->registerPlugin('function', 'registerCssFile', [$this, 'functionRegisterCssFile']);
        $smarty->registerPlugin('block', 'title', [$this, 'blockTitle']);
        $smarty->registerPlugin('block', 'description', [$this, 'blockDescription']);
        $smarty->registerPlugin('block', 'registerJs', [$this, 'blockJavaScript']);
        $smarty->registerPlugin('block', 'registerCss', [$this, 'blockCss']);
        $smarty->registerPlugin('compiler', 'use', [$this, 'compilerUse']);
        $smarty->registerPlugin('modifier', 'void', [$this, 'modifierVoid']);
    }

    /**
     * Smarty template function to get relative URL for using in links
     *
     * Usage is the following:
     *
     * {path route='blog/view' alias=$post.alias user=$user.id}
     *
     * where route is Yii route and the rest of parameters are passed as is.
     *
     * @param array $params
     * @param \Smarty_Internal_Template $template
     *
     * @return string
     */
    public function functionPath($params, \Smarty_Internal_Template $template)
    {
        if (!isset($params['route'])) {
            trigger_error("path: missing 'route' parameter");
        }

        array_unshift($params, $params['route']) ;
        unset($params['route']);

        return Url::to($params, true);
    }

    /**
     * Smarty template function to get absolute URL for using in links
     *
     * Usage is the following:
     *
     * {path route='blog/view' alias=$post.alias user=$user.id}
     *
     * where route is Yii route and the rest of parameters are passed as is.
     *
     * @param array $params
     * @param \Smarty_Internal_Template $template
     *
     * @return string
     */
    public function functionUrl($params, \Smarty_Internal_Template $template)
    {
        if (!isset($params['route'])) {
            trigger_error("path: missing 'route' parameter");
        }

        array_unshift($params, $params['route']) ;
        unset($params['route']);

        return Url::to($params, true);
    }

    /**
     * Smarty compiler function plugin
     * Usage is the following:
     *
     * {use class="app\assets\AppAsset"}
     * {use class="yii\helpers\Html"}
     * {use class='yii\widgets\ActiveForm' type='block'}
     * {use class='@app\widgets\MyWidget' as='my_widget' type='function'}
     *
     * Supported attributes: class, as, type. Type defaults to 'static'.
     *
     * @param $params
     * @param \Smarty_Internal_Template $template
     * @return string
     * @note Even though this method is public it should not be called directly.
     */
    public function compilerUse($params, $template)
    {
        if (!isset($params['class'])) {
            trigger_error("use: missing 'class' parameter");
        }
        // Compiler plugin parameters may include quotes, so remove them
        foreach ($params as $key => $value) {
            $params[$key] = trim($value, '\'""');
        }

        $class = $params['class'];
        $alias = ArrayHelper::getValue($params, 'as', StringHelper::basename($params['class']));
        $type = ArrayHelper::getValue($params, 'type', 'static');

        // Register the class during compile time
        $this->smarty->registerClass($alias, $class);

        if ($type === 'block') {
            // Register widget tag during compile time
            $this->viewRenderer->widgets['blocks'][$alias] = $class;
            $this->smarty->registerPlugin('block', $alias, [$this->viewRenderer, '_widget_block__' . $alias]);

            // Inject code to re-register widget tag during run-time
            return <<<PHP
<?php
    \$_smarty_tpl->getGlobal('_viewRenderer')->widgets['blocks']['$alias'] = '$class';
    try {
        \$_smarty_tpl->registerPlugin('block', '$alias', [\$_smarty_tpl->getGlobal('_viewRenderer'), '_widget_block__$alias']);
    }
    catch (SmartyException \$e) {
        /* Ignore already registered exception during first execution after compilation */
    }
?>
PHP;
        } elseif ($type === 'function') {
            // Register widget tag during compile time
            $this->viewRenderer->widgets['functions'][$alias] = $class;
            $this->smarty->registerPlugin('function', $alias, [$this->viewRenderer, '_widget_function__' . $alias]);

            // Inject code to re-register widget tag during run-time
            return <<<PHP
<?php
    \$_smarty_tpl->getGlobal('_viewRenderer')->widgets['functions']['$alias'] = '$class';
    try {
        \$_smarty_tpl->registerPlugin('function', '$alias', [\$_smarty_tpl->getGlobal('_viewRenderer'), '_widget_function__$alias']);
    }
    catch (SmartyException \$e) {
        /* Ignore already registered exception during first execution after compilation */
    }
?>
PHP;
        }
    }

    /**
     * Smarty modifier plugin
     * Converts any output to void
     * @param mixed $arg
     * @return string
     * @note Even though this method is public it should not be called directly.
     */
    public function modifierVoid($arg)
    {
        return;
    }

    /**
     * Smarty function plugin
     * Usage is the following:
     *
     * {set title="My Page"}
     * {set theme="frontend"}
     * {set layout="main.tpl"}
     *
     * Supported attributes: title, theme, layout
     *
     * @param $params
     * @param \Smarty_Internal_Template $template
     * @return string
     * @note Even though this method is public it should not be called directly.
     */
    public function functionSet($params, $template)
    {
        if (isset($params['title'])) {
            $template->tpl_vars['this']->value->title = Yii::$app->getView()->title = ArrayHelper::remove($params, 'title');
        }
        if (isset($params['theme'])) {
            $template->tpl_vars['this']->value->theme = Yii::$app->getView()->theme = ArrayHelper::remove($params, 'theme');
        }
        if (isset($params['layout'])) {
            Yii::$app->controller->layout = ArrayHelper::remove($params, 'layout');
        }

        // We must have consumed all allowed parameters now, otherwise raise error
        if (!empty($params)) {
            trigger_error('set: Unsupported parameter attribute');
        }
    }

    /**
     * Smarty function plugin
     * Usage is the following:
     *
     * {meta keywords="Yii,PHP,Smarty,framework"}
     *
     * Supported attributes: any; all attributes are passed as
     * parameter array to Yii's registerMetaTag function.
     *
     * @param $params
     * @param \Smarty_Internal_Template $template
     * @return string
     * @note Even though this method is public it should not be called directly.
     */
    public function functionMeta($params, $template)
    {
        $key = isset($params['name']) ? $params['name'] : null;

        Yii::$app->getView()->registerMetaTag($params, $key);
    }

    /**
     * Smarty block function plugin
     * Usage is the following:
     *
     * {title} Web Site Login {/title}
     *
     * Supported attributes: none.
     *
     * @param $params
     * @param $content
     * @param \Smarty_Internal_Template $template
     * @param $repeat
     * @return string
     * @note Even though this method is public it should not be called directly.
     */
    public function blockTitle($params, $content, $template, &$repeat)
    {
        if ($content !== null) {
            Yii::$app->getView()->title = $content;
        }
    }

    /**
     * Smarty block function plugin
     * Usage is the following:
     *
     * {description}
     *     The text between the opening and closing tags is added as
     *     meta description tag to the page output.
     * {/description}
     *
     * Supported attributes: none.
     *
     * @param $params
     * @param $content
     * @param \Smarty_Internal_Template $template
     * @param $repeat
     * @return string
     * @note Even though this method is public it should not be called directly.
     */
    public function blockDescription($params, $content, $template, &$repeat)
    {
        if ($content !== null) {
            // Clean-up whitespace and newlines
            $content = preg_replace('/\s+/', ' ', trim($content));

            Yii::$app->getView()->registerMetaTag(['name' => 'description',
                                                   'content' => $content],
                                                   'description');
        }
    }

    /**
     * Smarty function plugin
     * Usage is the following:
     *
     * {registerJsFile url='http://maps.google.com/maps/api/js?sensor=false' position='POS_END'}
     *
     * Supported attributes: url, key, depends, position and valid HTML attributes for the script tag.
     * Refer to Yii documentation for details.
     * The position attribute is passed as text without the class prefix.
     * Default is 'POS_END'.
     *
     * @param $params
     * @param \Smarty_Internal_Template $template
     * @return string
     * @note Even though this method is public it should not be called directly.
     */
    public function functionRegisterJsFile($params, $template)
    {
        if (!isset($params['url'])) {
            trigger_error("registerJsFile: missing 'url' parameter");
        }

        $url = ArrayHelper::remove($params, 'url');
        $key = ArrayHelper::remove($params, 'key', null);
        $depends = ArrayHelper::remove($params, 'depends', null);
        if (isset($params['position']))
            $params['position'] = $this->getViewConstVal($params['position'], View::POS_END);

        Yii::$app->getView()->registerJsFile($url, $depends, $params, $key);
    }

    /**
     * Smarty block function plugin
     * Usage is the following:
     *
     * {registerJs key='show' position='POS_LOAD'}
     *     $("span.show").replaceWith('<div class="show">');
     * {/registerJs}
     *
     * Supported attributes: key, position. Refer to Yii documentation for details.
     * The position attribute is passed as text without the class prefix.
     * Default is 'POS_READY'.
     *
     * @param $params
     * @param $content
     * @param \Smarty_Internal_Template $template
     * @param $repeat
     * @return string
     * @note Even though this method is public it should not be called directly.
     */
    public function blockJavaScript($params, $content, $template, &$repeat)
    {
        if ($content !== null) {
            $key = isset($params['key']) ? $params['key'] : null;
            $position = isset($params['position']) ? $params['position'] : null;

            Yii::$app->getView()->registerJs($content,
                                             $this->getViewConstVal($position, View::POS_READY),
                                             $key);
        }
    }

    /**
     * Smarty function plugin
     * Usage is the following:
     *
     * {registerCssFile url='@assets/css/normalizer.css'}
     *
     * Supported attributes: url, key, depends and valid HTML attributes for the link tag.
     * Refer to Yii documentation for details.
     *
     * @param $params
     * @param \Smarty_Internal_Template $template
     * @return string
     * @note Even though this method is public it should not be called directly.
     */
    public function functionRegisterCssFile($params, $template)
    {
        if (!isset($params['url'])) {
            trigger_error("registerCssFile: missing 'url' parameter");
        }

        $url = ArrayHelper::remove($params, 'url');
        $key = ArrayHelper::remove($params, 'key', null);
        $depends = ArrayHelper::remove($params, 'depends', null);

        Yii::$app->getView()->registerCssFile($url, $depends, $params, $key);
    }

    /**
     * Smarty block function plugin
     * Usage is the following:
     *
     * {registerCss}
     * div.header {
     *     background-color: #3366bd;
     *     color: white;
     * }
     * {/registerCss}
     *
     * Supported attributes: key and valid HTML attributes for the style tag.
     * Refer to Yii documentation for details.
     *
     * @param $params
     * @param $content
     * @param \Smarty_Internal_Template $template
     * @param $repeat
     * @return string
     * @note Even though this method is public it should not be called directly.
     */
    public function blockCss($params, $content, $template, &$repeat)
    {
        if ($content !== null) {
            $key = isset($params['key']) ? $params['key'] : null;

            Yii::$app->getView()->registerCss($content, $params, $key);
        }
    }

    /**
    * Helper function to convert a textual constant identifier to a View class
    * integer constant value.
    *
    * @param string $string Constant identifier name
    * @param integer $default Default value
    * @return mixed
    */
   protected function getViewConstVal($string, $default)
   {
      $val = @constant('yii\web\View::' . $string);
      return isset($val) ? $val : $default;
   }
} 