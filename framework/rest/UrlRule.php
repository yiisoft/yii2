<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\rest;

use Yii;
use yii\base\InvalidConfigException;
use yii\helpers\Inflector;
use yii\web\CompositeUrlRule;
use yii\web\UrlRule as WebUrlRule;
use yii\web\UrlRuleInterface;

/**
 * UrlRule 用来简化 RESTful API 支持的 URL 规则的创建。
 *
 * UrlRule 最简单的用法是在应用程序配置中声明如下规则，
 *
 * ```php
 * [
 *     'class' => 'yii\rest\UrlRule',
 *     'controller' => 'user',
 * ]
 * ```
 *
 * 上面的代码将创建一整套支持以下 RESTful API 端点的 URL 规则：
 *
 * - `'PUT,PATCH users/<id>' => 'user/update'`：更新用户
 * - `'DELETE users/<id>' => 'user/delete'`：删除用户
 * - `'GET,HEAD users/<id>' => 'user/view'`：返回用户的详细信息/概述/选项
 * - `'POST users' => 'user/create'`：创建一个新用户
 * - `'GET,HEAD users' => 'user/index'`：返回用户的列表/概述/选项
 * - `'users/<id>' => 'user/options'`：响应用户的所有未处理动词
 * - `'users' => 'user/options'`：响应所有未处理的用户集合动词
 *
 * 您可以配置 [[only]] 或者 [[except]] 以禁用上述某些规则。
 * 您可以配置 [[patterns]] 以完全重新定义自己的规则列表。
 * 您可以配置 [[controller]] 以多个控制器 ID，来生成所有这些控制器的规则。
 * 例如，以下代码将禁用 `delete` 规则并为 `user` 和 `post` 控制器生成规则：
 *
 * ```php
 * [
 *     'class' => 'yii\rest\UrlRule',
 *     'controller' => ['user', 'post'],
 *     'except' => ['delete'],
 * ]
 * ```
 *
 * [[controller]]  属性是必需的，应代表一个或多个控制器ID。
 * 如果控制器位于模块内，则每个控制器ID都应以模块ID为前缀。
 * 使用的控制器ID将自动复数（例如 `user` 变为 `users`
 * 如上例所示）。
 *
 * 关于 UrlRule 的更多使用参考，请查看 [Rest 路由指南](guide:rest-routing)。
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class UrlRule extends CompositeUrlRule
{
    /**
     * @var string 每一个模式的公共前缀。
     */
    public $prefix;
    /**
     * @var string 后缀，将被赋值给 [[\yii\web\UrlRule::suffix]] 以生成的每个规则
     */
    public $suffix;
    /**
     * @var string|array 控制器ID（例如 `user`，`post-comment`）表示这些规则在此复合规则将被处理
     * 如果控制器在模块内（例如 `admin/user`），它应该以模块 ID 为前缀。
     *
     * 默认情况下，控制器ID在放入模式时会自动复数以生产规则
     * 如果要明确指定控制器 ID 在模式中的显示方式，
     * 你可以在模式中使用数组，键为模式名，值为控制器实际的 ID，
     * 例如 `['u' => 'user']`。
     *
     * 你还可以传递多个控制器 ID 的数组。此时，复合规则将
     * 为每个指定的控制器生成适用的URL规则。例如 `['user', 'post']`。
     */
    public $controller;
    /**
     * @var array 接受的动作（Action）列表，如果不为空，仅仅是这数组中的动作
     * 会创建相应的 URL 规则。
     * @see patterns
     */
    public $only = [];
    /**
     * @var array 除外的动作（Action）列表，这里的动作都不会创建 URL 规则
     *
     * @see patterns
     */
    public $except = [];
    /**
     * @var array 额外增加的模式，会被加入 [[patterns]] 中，
     * 键为模式名称，值为相应的动作 ID。
     * 这些额外的模式将优先于 [[patterns]] 生效。
     */
    public $extraPatterns = [];
    /**
     * @var array 每个模式替换的标记列表。键为标记名称，
     * 值为相应的替换
     * @see patterns
     */
    public $tokens = [
        '{id}' => '<id:\\d[\\d,]*>',
    ];
    /**
     * @var array 可用的模式对应相应动作的列表，用以创建 URL 规则。
     * 键为模式，值为相应的动作。
     * 模式的格式是 `Verbs Pattern`, 其中 `Verbs` 代表逗号分隔的 HTTP 动词列表（没有空格）。
     * 如果 `Verbs` 指定，意味着所有动词都被允许。
     * `Pattern` 是可选的，它将以 [[prefix]]/[[controller]]/ 为前缀，
     * 其中的标记会被 [[tokens]] 替换。
     */
    public $patterns = [
        'PUT,PATCH {id}' => 'update',
        'DELETE {id}' => 'delete',
        'GET,HEAD {id}' => 'view',
        'POST' => 'create',
        'GET,HEAD' => 'index',
        '{id}' => 'options',
        '' => 'options',
    ];
    /**
     * @var array 用于创建此规则包含的每个 URL 规则的默认配置。
     */
    public $ruleConfig = [
        'class' => 'yii\web\UrlRule',
    ];
    /**
     * @var bool 是否自动复数控制器的 URL名 称。
     * 如果为 true, 则控制器 ID 将以复数形式显示在 URL 中。例如 `user` 控制器
     * 将在URL中显示为 `users` 。
     * @see controller
     */
    public $pluralize = true;


    /**
     * {@inheritdoc}
     */
    public function init()
    {
        if (empty($this->controller)) {
            throw new InvalidConfigException('"controller" must be set.');
        }

        $controllers = [];
        foreach ((array) $this->controller as $urlName => $controller) {
            if (is_int($urlName)) {
                $urlName = $this->pluralize ? Inflector::pluralize($controller) : $controller;
            }
            $controllers[$urlName] = $controller;
        }
        $this->controller = $controllers;

        $this->prefix = trim($this->prefix, '/');

        parent::init();
    }

    /**
     * {@inheritdoc}
     */
    protected function createRules()
    {
        $only = array_flip($this->only);
        $except = array_flip($this->except);
        $patterns = $this->extraPatterns + $this->patterns;
        $rules = [];
        foreach ($this->controller as $urlName => $controller) {
            $prefix = trim($this->prefix . '/' . $urlName, '/');
            foreach ($patterns as $pattern => $action) {
                if (!isset($except[$action]) && (empty($only) || isset($only[$action]))) {
                    $rules[$urlName][] = $this->createRule($pattern, $prefix, $controller . '/' . $action);
                }
            }
        }

        return $rules;
    }

    /**
     * 使用给定的模式和操作创建URL规则。
     * @param string $pattern
     * @param string $prefix
     * @param string $action
     * @return UrlRuleInterface
     */
    protected function createRule($pattern, $prefix, $action)
    {
        $verbs = 'GET|HEAD|POST|PUT|PATCH|DELETE|OPTIONS';
        if (preg_match("/^((?:($verbs),)*($verbs))(?:\\s+(.*))?$/", $pattern, $matches)) {
            $verbs = explode(',', $matches[1]);
            $pattern = isset($matches[4]) ? $matches[4] : '';
        } else {
            $verbs = [];
        }

        $config = $this->ruleConfig;
        $config['verb'] = $verbs;
        $config['pattern'] = rtrim($prefix . '/' . strtr($pattern, $this->tokens), '/');
        $config['route'] = $action;
        if (!empty($verbs) && !in_array('GET', $verbs)) {
            $config['mode'] = WebUrlRule::PARSING_ONLY;
        }
        $config['suffix'] = $this->suffix;

        return Yii::createObject($config);
    }

    /**
     * {@inheritdoc}
     */
    public function parseRequest($manager, $request)
    {
        $pathInfo = $request->getPathInfo();
        foreach ($this->rules as $urlName => $rules) {
            if (strpos($pathInfo, $urlName) !== false) {
                foreach ($rules as $rule) {
                    /* @var $rule WebUrlRule */
                    $result = $rule->parseRequest($manager, $request);
                    if (YII_DEBUG) {
                        Yii::debug([
                            'rule' => method_exists($rule, '__toString') ? $rule->__toString() : get_class($rule),
                            'match' => $result !== false,
                            'parent' => self::className(),
                        ], __METHOD__);
                    }
                    if ($result !== false) {
                        return $result;
                    }
                }
            }
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function createUrl($manager, $route, $params)
    {
        $this->createStatus = WebUrlRule::CREATE_STATUS_SUCCESS;
        foreach ($this->controller as $urlName => $controller) {
            if (strpos($route, $controller) !== false) {
                /* @var $rules UrlRuleInterface[] */
                $rules = $this->rules[$urlName];
                $url = $this->iterateRules($rules, $manager, $route, $params);
                if ($url !== false) {
                    return $url;
                }
            } else {
                $this->createStatus |= WebUrlRule::CREATE_STATUS_ROUTE_MISMATCH;
            }
        }

        if ($this->createStatus === WebUrlRule::CREATE_STATUS_SUCCESS) {
            // create status was not changed - there is no rules configured
            $this->createStatus = WebUrlRule::CREATE_STATUS_PARSING_ONLY;
        }

        return false;
    }
}
