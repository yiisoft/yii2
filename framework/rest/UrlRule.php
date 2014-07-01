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

/**
 * UrlRule is provided to simplify the creation of URL rules for RESTful API support.
 *
 * The simplest usage of UrlRule is to declare a rule like the following in the application configuration,
 *
 * ```php
 * [
 *     'class' => 'yii\rest\UrlRule',
 *     'controller' => 'user',
 * ]
 * ```
 *
 * The above code will create a whole set of URL rules supporting the following RESTful API endpoints:
 *
 * - `'PUT,PATCH users/<id>' => 'user/update'`: update a user
 * - `'DELETE users/<id>' => 'user/delete'`: delete a user
 * - `'GET,HEAD users/<id>' => 'user/view'`: return the details/overview/options of a user
 * - `'POST users' => 'user/create'`: create a new user
 * - `'GET,HEAD users' => 'user/index'`: return a list/overview/options of users
 * - `'users/<id>' => 'user/options'`: process all unhandled verbs of a user
 * - `'users' => 'user/options'`: process all unhandled verbs of user collection
 *
 * You may configure [[only]] and/or [[except]] to disable some of the above rules.
 * You may configure [[patterns]] to completely redefine your own list of rules.
 * You may configure [[controller]] with multiple controller IDs to generate rules for all these controllers.
 * For example, the following code will disable the `delete` rule and generate rules for both `user` and `post` controllers:
 *
 * ```php
 * [
 *     'class' => 'yii\rest\UrlRule',
 *     'controller' => ['user', 'post'],
 *     'except' => ['delete'],
 * ]
 * ```
 *
 * The property [[controller]] is required and should represent one or multiple controller IDs.
 * Each controller ID should be prefixed with the module ID if the controller is within a module.
 * The controller ID used in the pattern will be automatically pluralized (e.g. `user` becomes `users`
 * as shown in the above examples).
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class UrlRule extends CompositeUrlRule
{
    /**
     * @var string the common prefix string shared by all patterns.
     */
    public $prefix;
    /**
     * @var string the suffix that will be assigned to [[\yii\web\UrlRule::suffix]] for every generated rule.
     */
    public $suffix;
    /**
     * @var string|array the controller ID (e.g. `user`, `post-comment`) that the rules in this composite rule
     * are dealing with. It should be prefixed with the module ID if the controller is within a module (e.g. `admin/user`).
     *
     * By default, the controller ID will be pluralized automatically when it is put in the patterns of the
     * generated rules. If you want to explicitly specify how the controller ID should appear in the patterns,
     * you may use an array with the array key being as the controller ID in the pattern, and the array value
     * the actual controller ID. For example, `['u' => 'user']`.
     *
     * You may also pass multiple controller IDs as an array. If this is the case, this composite rule will
     * generate applicable URL rules for EVERY specified controller. For example, `['user', 'post']`.
     */
    public $controller;
    /**
     * @var array list of acceptable actions. If not empty, only the actions within this array
     * will have the corresponding URL rules created.
     * @see patterns
     */
    public $only = [];
    /**
     * @var array list of actions that should be excluded. Any action found in this array
     * will NOT have its URL rules created.
     * @see patterns
     */
    public $except = [];
    /**
     * @var array patterns for supporting extra actions in addition to those listed in [[patterns]].
     * The keys are the patterns and the values are the corresponding action IDs.
     * These extra patterns will take precedence over [[patterns]].
     */
    public $extraPatterns = [];
    /**
     * @var array list of tokens that should be replaced for each pattern. The keys are the token names,
     * and the values are the corresponding replacements.
     * @see patterns
     */
    public $tokens = [
        '{id}' => '<id:\\d[\\d,]*>',
    ];
    /**
     * @var array list of possible patterns and the corresponding actions for creating the URL rules.
     * The keys are the patterns and the values are the corresponding actions.
     * The format of patterns is `Verbs Pattern`, where `Verbs` stands for a list of HTTP verbs separated
     * by comma (without space). If `Verbs` is not specified, it means all verbs are allowed.
     * `Pattern` is optional. It will be prefixed with [[prefix]]/[[controller]]/,
     * and tokens in it will be replaced by [[tokens]].
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
     * @var array the default configuration for creating each URL rule contained by this rule.
     */
    public $ruleConfig = [
        'class' => 'yii\web\UrlRule',
    ];
    /**
     * @var boolean whether to automatically pluralize the URL names for controllers.
     * If true, a controller ID will appear in plural form in URLs. For example, `user` controller
     * will appear as `users` in URLs.
     * @see controller
     */
    public $pluralize = true;

    /**
     * @inheritdoc
     */
    public function init()
    {
        if (empty($this->controller)) {
            throw new InvalidConfigException('"controller" must be set.');
        }

        $controllers = [];
        foreach ((array) $this->controller as $urlName => $controller) {
            if (is_integer($urlName)) {
                $urlName = $this->pluralize ? Inflector::pluralize($controller) : $controller;
            }
            $controllers[$urlName] = $controller;
        }
        $this->controller = $controllers;

        $this->prefix = trim($this->prefix, '/');

        parent::init();
    }

    /**
     * @inheritdoc
     */
    protected function createRules()
    {
        $only = array_flip($this->only);
        $except = array_flip($this->except);
        $patterns = array_merge($this->patterns, $this->extraPatterns);
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
     * Creates a URL rule using the given pattern and action.
     * @param string $pattern
     * @param string $prefix
     * @param string $action
     * @return \yii\web\UrlRuleInterface
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
        if (!in_array('GET', $verbs)) {
            $config['mode'] = \yii\web\UrlRule::PARSING_ONLY;
        }
        $config['suffix'] = $this->suffix;

        return Yii::createObject($config);
    }

    /**
     * @inheritdoc
     */
    public function parseRequest($manager, $request)
    {
        $pathInfo = $request->getPathInfo();
        foreach ($this->rules as $urlName => $rules) {
            if (strpos($pathInfo, $urlName) !== false) {
                foreach ($rules as $rule) {
                    /* @var $rule \yii\web\UrlRule */
                    if (($result = $rule->parseRequest($manager, $request)) !== false) {
                        Yii::trace("Request parsed with URL rule: {$rule->name}", __METHOD__);

                        return $result;
                    }
                }
            }
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function createUrl($manager, $route, $params)
    {
        foreach ($this->controller as $urlName => $controller) {
            if (strpos($route, $controller) !== false) {
                foreach ($this->rules[$urlName] as $rule) {
                    /* @var $rule \yii\web\UrlRule */
                    if (($url = $rule->createUrl($manager, $route, $params)) !== false) {
                        return $url;
                    }
                }
            }
        }

        return false;
    }
}
