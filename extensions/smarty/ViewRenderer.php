<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\smarty;

use Yii;
use Smarty;
use yii\web\View;
use yii\base\Widget;
use yii\base\ViewRenderer as BaseViewRenderer;
use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;

/**
 * SmartyViewRenderer allows you to use Smarty templates in views.
 *
 * @author Alexander Makarov <sam@rmcreative.ru>
 * @author Henrik Maier <hwmaier@gmail.com>
 * @since 2.0
 */
class ViewRenderer extends BaseViewRenderer
{
    /**
     * @var string the directory or path alias pointing to where Smarty cache will be stored.
     */
    public $cachePath = '@runtime/Smarty/cache';
    /**
     * @var string the directory or path alias pointing to where Smarty compiled templates will be stored.
     */
    public $compilePath = '@runtime/Smarty/compile';

    /**
     * @var array Add additional directories to Smarty's search path for plugins.
     */
    public $pluginDirs = [];
    /**
     * @var array Class imports similar to the use tag
     */
    public $imports = [];
    /**
     * @var array Widget declarations
     */
    public $widgets = ['functions' => [], 'blocks' => []];
    /**
     * @var Smarty The Smarty object used for rendering
     */
    protected $smarty;

    /**
     * @var array additional Smarty options
     * @see http://www.smarty.net/docs/en/api.variables.tpl
     */
    public $options = [];

    /**
     * @var string extension class name
     */
    public $extensionClass = '\yii\smarty\Extension';


    /**
     * Instantiates and configures the Smarty object.
     */
    public function init()
    {
        $this->smarty = new Smarty();
        $this->smarty->setCompileDir(Yii::getAlias($this->compilePath));
        $this->smarty->setCacheDir(Yii::getAlias($this->cachePath));

        foreach ($this->options as $key => $value) {
            $this->smarty->$key = $value;
        }

        $this->smarty->setTemplateDir([
            dirname(Yii::$app->getView()->getViewFile()),
            Yii::$app->getViewPath(),
        ]);

        // Add additional plugin dirs from configuration array, apply Yii's dir convention
        foreach ($this->pluginDirs as &$dir) {
            $dir = $this->resolveTemplateDir($dir);
        }
        $this->smarty->addPluginsDir($this->pluginDirs);

        if (isset($this->imports)) {
            foreach(($this->imports) as $tag => $class) {
                $this->smarty->registerClass($tag, $class);
            }
        }
        // Register block widgets specified in configuration array
        if (isset($this->widgets['blocks'])) {
            foreach(($this->widgets['blocks']) as $tag => $class) {
                $this->smarty->registerPlugin('block', $tag, [$this, '_widget_block__' . $tag]);
                $this->smarty->registerClass($tag, $class);
            }
        }
        // Register function widgets specified in configuration array
        if (isset($this->widgets['functions'])) {
            foreach(($this->widgets['functions']) as $tag => $class) {
                $this->smarty->registerPlugin('function', $tag, [$this, '_widget_func__' . $tag]);
                $this->smarty->registerClass($tag, $class);
            }
        }

        new $this->extensionClass($this, $this->smarty);

        $this->smarty->default_template_handler_func = [$this, 'aliasHandler'];
    }

    /**
     * The directory can be specified in Yii's standard convention
     * using @, // and / prefixes or no prefix for view relative directories.
     *
     * @param string $dir directory name to be resolved
     * @return string the resolved directory name
     */
    protected function resolveTemplateDir($dir)
    {
        if (strncmp($dir, '@', 1) === 0) {
            // e.g. "@app/views/dir"
            $dir = Yii::getAlias($dir);
        } elseif (strncmp($dir, '//', 2) === 0) {
            // e.g. "//layouts/dir"
            $dir = Yii::$app->getViewPath() . DIRECTORY_SEPARATOR . ltrim($dir, '/');
        } elseif (strncmp($dir, '/', 1) === 0) {
            // e.g. "/site/dir"
            if (Yii::$app->controller !== null) {
                $dir = Yii::$app->controller->module->getViewPath() . DIRECTORY_SEPARATOR . ltrim($dir, '/');
            } else {
                // No controller, what to do?
            }
        } else {
            // relative to view file
            $dir = dirname(Yii::$app->getView()->getViewFile()) . DIRECTORY_SEPARATOR . $dir;
        }

        return $dir;
    }

    /**
     * Mechanism to pass a widget's tag name to the callback function.
     *
     * Using a magic function call would not be necessary if Smarty would
     * support closures. Smarty closure support is announced for 3.2,
     * until its release magic function calls are used to pass the
     * tag name to the callback.
     *
     * @param string $method
     * @param array $args
     * @throws InvalidConfigException
     * @throws \BadMethodCallException
     * @return string
     */
    public function __call($method, $args)
    {
        $methodInfo = explode('__', $method);
        if (count($methodInfo) === 2) {
            $alias = $methodInfo[1];
            if (isset($this->widgets['functions'][$alias])) {
                if (($methodInfo[0] === '_widget_func') && (count($args) === 2)) {
                    return $this->widgetFunction($this->widgets['functions'][$alias], $args[0], $args[1]);
                }
            } elseif (isset($this->widgets['blocks'][$alias])) {
                if (($methodInfo[0] === '_widget_block') && (count($args) === 4)) {
                    return $this->widgetBlock($this->widgets['blocks'][$alias], $args[0], $args[1], $args[2], $args[3]);
                }
            } else {
                throw new InvalidConfigException('Widget "' . $alias . '" not declared.');
            }
        }

        throw new \BadMethodCallException('Method does not exist: ' . $method);
    }

    /**
     * Smarty plugin callback function to support widget as Smarty blocks.
     * This function is not called directly by Smarty but through a
     * magic __call wrapper.
     *
     * Example usage is the following:
     *
     *    {ActiveForm assign='form' id='login-form'}
     *        {$form->field($model, 'username')}
     *        {$form->field($model, 'password')->passwordInput()}
     *        <div class="form-group">
     *            <input type="submit" value="Login" class="btn btn-primary" />
     *        </div>
     *    {/ActiveForm}
     */
    private function widgetBlock($class, $params, $content, \Smarty_Internal_Template $template, &$repeat)
    {
        // Check if this is the opening ($content is null) or closing tag.
        if ($content === null) {
            $params['class'] = $class;
            // Figure out where to put the result of the widget call, if any
            $assign = ArrayHelper::remove($params, 'assign', false);
            ob_start();
            ob_implicit_flush(false);
            $widget = Yii::createObject($params);
            Widget::$stack[] = $widget;
            if ($assign) {
                $template->assign($assign, $widget);
            }
        } else {
            $widget = array_pop(Widget::$stack);
            echo $content;
            $out = $widget->run();
            return ob_get_clean() . $out;
        }
    }

    /**
     * Smarty plugin callback function to support widgets as Smarty functions.
     * This function is not called directly by Smarty but through a
     * magic __call wrapper.
     *
     * Example usage is the following:
     *
     * {GridView dataProvider=$provider}
     *
     */
    private function widgetFunction($class, $params, \Smarty_Internal_Template $template)
    {
        $repeat = false;
        $this->widgetBlock($class, $params, null, $template, $repeat); // $widget->init(...)
        return $this->widgetBlock($class, $params, '', $template, $repeat); // $widget->run()
    }

    /**
     * Renders a view file.
     *
     * This method is invoked by [[View]] whenever it tries to render a view.
     * Child classes must implement this method to render the given view file.
     *
     * @param View $view the view object used for rendering the file.
     * @param string $file the view file.
     * @param array $params the parameters to be passed to the view file.
     * @return string the rendering result
     */
    public function render($view, $file, $params)
    {
        /* @var $template \Smarty_Internal_Template */
        $template = $this->smarty->createTemplate($file, null, null, empty($params) ? null : $params, false);

        // Make Yii params available as smarty config variables
        $template->config_vars = Yii::$app->params;

        $template->assign('app', \Yii::$app);
        $template->assign('this', $view);

        return $template->fetch();
    }

    /**
     * Resolves Yii alias into file path
     *
     * @param string $type
     * @param string $name
     * @param string $content
     * @param string $modified
     * @param Smarty $smarty
     * @return bool|string path to file or false if it's not found
     */
    public function aliasHandler($type, $name, &$content, &$modified, Smarty $smarty)
    {
        $file = Yii::getAlias($name);
        return $file !== false && is_file($file) ? $file : false;
    }
}
