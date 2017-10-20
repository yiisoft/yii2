<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\rest;

use Yii;
use yii\base\InvalidConfigException;
use yii\web\CompositeUrlRule;
use yii\web\UrlRule as WebUrlRule;
use yii\web\UrlRuleInterface;

/**
 * GroupUrlRule represents a collection of URL rules sharing the same prefix in their patterns and routes.
 *
 * GroupUrlRule is best used by a module which often uses module ID as the prefix for the URL rules.
 * For example, the following code creates a rule for the `admin` module:
 *
 * ```php
 * new GroupUrlRule([
 *     'prefix' => 'v1',
 *     'rules' => [
 *         'user',
 *         [
 *             'controller' => ['customer', 'post'],
 *             'pluralize' => false,
 *             'only' => ['view'],
 *         ],
 *     ],
 * ]);
 * ```
 *
 * The above code will create a whole set of URL rules supporting the following RESTful API endpoints:
 *
 * - `'PUT,PATCH v1/users/<id>' => 'v1/user/update'`: update a user
 * - `'DELETE v1/users/<id>' => 'v1/user/delete'`: delete a user
 * - `'GET,HEAD v1/users/<id>' => 'v1/user/view'`: return the details/overview/options of a user
 * - `'POST v1/users' => 'v1/user/create'`: create a new user
 * - `'GET,HEAD v1/users' => 'v1/user/index'`: return a list/overview/options of users
 * - `'v1/users/<id>' => 'v1/user/options'`: process all unhandled verbs of a user
 * - `'v1/users' => 'v1/user/options'`: process all unhandled verbs of user collection
 * - `'GET,HEAD v1/customer/<id>' => 'v1/customer/view'`: return the details/overview/options of a customer
 * - `'GET,HEAD v1/post/<id>' => 'v1/post/view'`: return the details/overview/options of a post
 *
 * The above example assumes the prefix for patterns and routes are the same. They can be made different
 * by configuring [[prefix]] and [[routePrefix]] separately.
 * Note: [[prefix]] can not be resolved to [[yii\rest\UrlRule::prefix]];
 * [[routePrefix]] can be resolved to [[yii\rest\UrlRule::controllerPrefix]]
 *
 * Using a GroupUrlRule is more efficient than directly declaring the individual rules it contains.
 * This is because GroupUrlRule can quickly determine if it should process a URL parsing or creation request
 * by simply checking if the prefix matches.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @author BSCheshir <bscheshir.work@gmail.com>
 * @since 2.0.13
 */
class GroupUrlRule extends CompositeUrlRule
{
    /**
     * @var array the rules contained within this composite rule. Please refer to [[UrlManager::rules]]
     * for the format of this property.
     *
     * A special shortcut format can be used if a rule only specifies [[\yii\rest\UrlRule::controller]]
     * That is, instead of using a configuration array, one can use an array with the array key
     * being as the controller ID in the pattern, and the array value the actual controller ID.
     * For example, `'rules' => ['u' => 'user']`. You can omit the array key `'rules' => ['user']`
     * To use [[\yii\rest\UrlRule::controller]] as default, the controller ID will be pluralized
     * automatically when it is put in the patterns of the generated rules.
     *
     * @see prefix
     * @see controllerPrefix
     * @see \yii\rest\UrlRule::controller
     */
    public $rules = [];
    /**
     * @var string the prefix for the pattern part of every rule declared in [[rules]].
     * The prefix and the pattern will be separated with a slash.
     */
    public $prefix;
    /**
     * @var string the prefix for the controller part of every rule declared in [[rules]].
     * The prefix and the route will be separated with a slash.
     * If this property is not set, it will take the value of [[prefix]].
     * @see \yii\rest\UrlRule::controllerPrefix
     */
    public $controllerPrefix;
    /**
     * @var array the default configuration of URL rules. Individual rule configurations
     * specified via [[rules]] will take precedence when the same property of the rule is configured.
     */
    public $ruleConfig = ['class' => 'yii\rest\UrlRule'];


    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->prefix = trim($this->prefix, '/');
        $this->controllerPrefix = $this->controllerPrefix === null ? $this->prefix : trim($this->controllerPrefix, '/');
        parent::init();
    }

    /**
     * @inheritdoc
     */
    protected function createRules()
    {
        $rules = [];
        foreach ($this->rules as $key => $rule) {
            if (!is_array($rule)) {
                if (!is_string($rule)) {
                    throw new InvalidConfigException('Single rule of group REST URL rule must be a array or a string.');
                }
                $rule = ['controller' => [$key => $rule]];
            }
            $rule['prefix'] = isset($rule['prefix']) ?
                ltrim($this->prefix . '/' . $rule['prefix'], '/') :
                $this->prefix;
            $rule['controllerPrefix'] = isset($rule['controllerPrefix']) ?
                ltrim($this->controllerPrefix . '/' . $rule['controllerPrefix'], '/') :
                $this->controllerPrefix;

            $rule = Yii::createObject(array_merge($this->ruleConfig, $rule));
            if (!$rule instanceof UrlRuleInterface) {
                throw new InvalidConfigException('URL rule class must implement UrlRuleInterface.');
            }
            $rules[] = $rule;
        }

        return $rules;
    }

    /**
     * @inheritdoc
     */
    public function parseRequest($manager, $request)
    {
        $pathInfo = $request->getPathInfo();
        if ($this->prefix === '' || strpos($pathInfo . '/', $this->prefix . '/') === 0) {
            return parent::parseRequest($manager, $request);
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function createUrl($manager, $route, $params)
    {
        if ($this->controllerPrefix === '' || strpos($route, $this->controllerPrefix . '/') === 0) {
            return parent::createUrl($manager, $route, $params);
        }

        $this->createStatus = WebUrlRule::CREATE_STATUS_ROUTE_MISMATCH;

        return false;
    }
}
