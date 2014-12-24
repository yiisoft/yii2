<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\twig;

use yii\base\InvalidCallException;
use yii\helpers\Inflector;
use yii\helpers\StringHelper;
use yii\helpers\Url;

/**
 * Extension provides Yii-specific syntax for Twig templates.
 *
 * @author Andrey Grachov <andrey.grachov@gmail.com>
 * @author Alexander Makarov <sam@rmcreative.ru>
 */
class Extension extends \Twig_Extension
{
    /**
     * @var array used namespaces
     */
    protected $namespaces = [];
    /**
     * @var array used class aliases
     */
    protected $aliases = [];
    /**
     * @var array used widgets
     */
    protected $widgets = [];


    /**
     * Creates new instance
     *
     * @param array $uses namespaces and classes to use in the template
     */
    public function __construct(array $uses = [])
    {
        $this->addUses($uses);
    }

    /**
     * @inheritdoc
     */
    public function getNodeVisitors()
    {
        return [
            new Optimizer(),
        ];
    }

    /**
     * @inheritdoc
     */
    public function getFunctions()
    {
        $options = [
            'is_safe' => ['html'],
        ];
        $functions = [
            new \Twig_SimpleFunction('use', [$this, 'addUses'], $options),
            new \Twig_SimpleFunction('*_begin', [$this, 'beginWidget'], $options),
            new \Twig_SimpleFunction('*_end', [$this, 'endWidget'], $options),
            new \Twig_SimpleFunction('widget_end', [$this, 'endWidget'], $options),
            new \Twig_SimpleFunction('*_widget', [$this, 'widget'], $options),
            new \Twig_SimpleFunction('path', [$this, 'path']),
            new \Twig_SimpleFunction('url', [$this, 'url']),
            new \Twig_SimpleFunction('void', function(){}),
            new \Twig_SimpleFunction('set', [$this, 'setProperty']),
        ];

        $options = array_merge($options, [
            'needs_context' => true,
        ]);
        $functions[] = new \Twig_SimpleFunction('register_*', [$this, 'registerAsset'], $options);
        foreach (['begin_page', 'end_page', 'begin_body', 'end_body', 'head'] as $helper) {
            $functions[] = new \Twig_SimpleFunction($helper, [$this, 'viewHelper'], $options);
        }
        return $functions;
    }

    public function registerAsset($context, $asset)
    {
        return $this->resolveAndCall($asset, 'register', [
            isset($context['this']) ? $context['this'] : null,
        ]);
    }

    public function beginWidget($widget, $config = [])
    {
        $widget = $this->resolveClassName($widget);
        $this->widgets[] = $widget;
        return $this->call($widget, 'begin', [
            $config,
        ]);
    }

    public function endWidget($widget = null)
    {
        if ($widget === null) {
            if (empty($this->widgets)) {
                throw new InvalidCallException('Unexpected end_widget() call. A matching begin_widget() is not found.');
            }
            $this->call(array_pop($this->widgets), 'end');
        } else {
            array_pop($this->widgets);
            $this->resolveAndCall($widget, 'end');
        }
    }

    public function widget($widget, $config = [])
    {
        return $this->resolveAndCall($widget, 'widget', [
            $config,
        ]);
    }

    public function viewHelper($context, $name = null)
    {
        if ($name !== null && isset($context['this'])) {
            $this->call($context['this'], Inflector::variablize($name));
        }
    }

    public function resolveAndCall($className, $method, $arguments = null)
    {
        return $this->call($this->resolveClassName($className), $method, $arguments);
    }

    public function call($className, $method, $arguments = null)
    {
        $callable = [$className, $method];
        if ($arguments === null) {
            return call_user_func($callable);
        } else {
            return call_user_func_array($callable, $arguments);
        }
    }

    public function resolveClassName($className)
    {
        $className = Inflector::id2camel($className, '_');
        if (isset($this->aliases[$className])) {
            return $this->aliases[$className];
        }
        foreach ($this->namespaces as $namespace) {
            $resolvedClassName = $namespace . '\\' . $className;
            if (class_exists($resolvedClassName)) {
                return $this->aliases[$className] = $resolvedClassName;
            }
        }
        return $className;
    }

    public function addUses($args)
    {
        foreach ((array) $args as $key => $value) {
            $value = str_replace('/', '\\', $value);
            if (is_int($key)) {
                // namespace or class import
                if (class_exists($value)) {
                    // class import
                    $this->aliases[StringHelper::basename($value)] = $value;
                } else {
                    // namespace
                    $this->namespaces[] = $value;
                }
            } else {
                // aliased class import
                $this->aliases[$key] = $value;
            }
        }
    }

    public function path($path, $args = [])
    {
        return Url::to(array_merge([$path], $args));
    }

    public function url($path, $args = [])
    {
        return Url::to(array_merge([$path], $args), true);
    }

    public function setProperty($object, $property, $value)
    {
        $object->$property = $value;
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'yii2-twig';
    }
}