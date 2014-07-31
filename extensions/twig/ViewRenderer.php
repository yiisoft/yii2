<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\twig;

use Yii;
use yii\base\View;
use yii\base\ViewRenderer as BaseViewRenderer;

/**
 * TwigViewRenderer allows you to use Twig templates in views.
 *
 * @property array $lexerOptions @see self::$lexerOptions. This property is write-only.
 *
 * @author Alexander Makarov <sam@rmcreative.ru>
 * @since 2.0
 */
class ViewRenderer extends BaseViewRenderer
{
    /**
     * @var string the directory or path alias pointing to where Twig cache will be stored. Set to false to disable
     * templates cache.
     */
    public $cachePath = '@runtime/Twig/cache';
    /**
     * @var array Twig options.
     * @see http://twig.sensiolabs.org/doc/api.html#environment-options
     */
    public $options = [];
    /**
     * @var array Objects or static classes.
     * Keys of the array are names to call in template, values are objects or names of static classes.
     * Example: `['html' => '\yii\helpers\Html']`.
     * In the template you can use it like this: `{{ html.a('Login', 'site/login') | raw }}`.
     */
    public $globals = [];
    /**
     * @var array Custom functions.
     * Keys of the array are names to call in template, values are names of functions or static methods of some class.
     * Example: `['rot13' => 'str_rot13', 'a' => '\yii\helpers\Html::a']`.
     * In the template you can use it like this: `{{ rot13('test') }}` or `{{ a('Login', 'site/login') | raw }}`.
     */
    public $functions = [];
    /**
     * @var array Custom filters.
     * Keys of the array are names to call in template, values are names of functions or static methods of some class.
     * Example: `['rot13' => 'str_rot13', 'jsonEncode' => '\yii\helpers\Json::encode']`.
     * In the template you can use it like this: `{{ 'test'|rot13 }}` or `{{ model|jsonEncode }}`.
     */
    public $filters = [];
    /**
     * @var array Custom extensions.
     * Example: `['Twig_Extension_Sandbox', new \Twig_Extension_Text()]`
     */
    public $extensions = [];
    /**
     * @var array Twig lexer options.
     *
     * Example: Smarty-like syntax:
     * ```php
     * [
     *     'tag_comment'  => ['{*', '*}'],
     *     'tag_block'    => ['{', '}'],
     *     'tag_variable' => ['{$', '}']
     * ]
     * ```
     * @see http://twig.sensiolabs.org/doc/recipes.html#customizing-the-syntax
     */
    public $lexerOptions = [];
    /**
     * @var array namespaces and classes to import.
     *
     * Example:
     *
     * ```php
     * [
     *     'yii\bootstrap',
     *     'app\assets',
     *     \yii\bootstrap\NavBar::className(),
     * ]
     * ```
     */
    public $uses = [];
    /**
     * @var \Twig_Environment twig environment object that renders twig templates
     */
    public $twig;


    public function init()
    {
        $this->twig = new \Twig_Environment(null, array_merge([
            'cache' => Yii::getAlias($this->cachePath),
            'charset' => Yii::$app->charset,
        ], $this->options));

        // Adding custom globals (objects or static classes)
        if (!empty($this->globals)) {
            $this->addGlobals($this->globals);
        }

        // Adding custom functions
        if (!empty($this->functions)) {
            $this->addFunctions($this->functions);
        }

        // Adding custom filters
        if (!empty($this->filters)) {
            $this->addFilters($this->filters);
        }

        $this->addExtensions([new Extension($this->uses)]);

        // Adding custom extensions
        if (!empty($this->extensions)) {
            $this->addExtensions($this->extensions);
        }

        $this->twig->addGlobal('app', \Yii::$app);

        // Change lexer syntax (must be set after other settings)
        if (!empty($this->lexerOptions)) {
            $this->setLexerOptions($this->lexerOptions);
        }
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
     *
     * @return string the rendering result
     */
    public function render($view, $file, $params)
    {
        $this->twig->addGlobal('this', $view);
        $loader = new \Twig_Loader_Filesystem(dirname($file));

        foreach (Yii::$aliases as $alias => $path) {
            if (is_dir($path)) {
                $loader->addPath($path, substr($alias, 1));
            }
        }
        $this->twig->setLoader($loader);

        return $this->twig->render(pathinfo($file, PATHINFO_BASENAME), $params);
    }

    /**
     * Adds global objects or static classes
     * @param array $globals @see self::$globals
     */
    public function addGlobals($globals)
    {
        foreach ($globals as $name => $value) {
            if (!is_object($value)) {
                $value = new ViewRendererStaticClassProxy($value);
            }
            $this->twig->addGlobal($name, $value);
        }
    }

    /**
     * Adds custom functions
     * @param array $functions @see self::$functions
     */
    public function addFunctions($functions)
    {
        $this->_addCustom('Function', $functions);
    }

    /**
     * Adds custom filters
     * @param array $filters @see self::$filters
     */
    public function addFilters($filters)
    {
        $this->_addCustom('Filter', $filters);
    }

    /**
     * Adds custom extensions
     * @param array $extensions @see self::$extensions
     */
    public function addExtensions($extensions)
    {
        foreach ($extensions as $extName) {
            $this->twig->addExtension(is_object($extName) ? $extName : new $extName());
        }
    }

    /**
     * Sets Twig lexer options to change templates syntax
     * @param array $options @see self::$lexerOptions
     */
    public function setLexerOptions($options)
    {
        $lexer = new \Twig_Lexer($this->twig, $options);
        $this->twig->setLexer($lexer);
    }

    /**
     * Adds custom function or filter
     * @param string $classType 'Function' or 'Filter'
     * @param array $elements Parameters of elements to add
     * @throws \Exception
     */
    private function _addCustom($classType, $elements)
    {
        $classFunction = 'Twig_' . $classType . '_Function';

        foreach ($elements as $name => $func) {
            $twigElement = null;

            switch ($func) {
                // Just a name of function
                case is_string($func):
                    $twigElement = new $classFunction($func);
                    break;
                // Name of function + options array
                case is_array($func) && is_string($func[0]) && isset($func[1]) && is_array($func[1]):
                    $twigElement = new $classFunction($func[0], $func[1]);
                    break;
            }

            if ($twigElement !== null) {
                $this->twig->{'add'.$classType}($name, $twigElement);
            } else {
                throw new \Exception("Incorrect options for \"$classType\" $name.");
            }
        }
    }
}
