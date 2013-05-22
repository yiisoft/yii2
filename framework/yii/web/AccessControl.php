<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\web;

use Yii;
use yii\base\Action;
use yii\base\ActionFilter;
use yii\base\HttpException;

/**
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class AccessControl extends ActionFilter
{
	/**
	 * @var callback a callback that will be called if the access should be denied
	 * to the current user. If not set, [[denyAccess()]] will be called.
	 *
	 * The signature of the callback should be as follows:
	 *
	 * ~~~
	 * function ($rule, $action)
	 * ~~~
	 *
	 * where `$rule` is this rule, and `$action` is the current [[Action|action]] object.
	 */
	public $denyCallback;
	/**
	 * @var array the default configuration of access rules. Individual rule configurations
	 * specified via [[rules]] will take precedence when the same property of the rule is configured.
	 */
	public $ruleConfig = array(
		'class' => 'yii\web\AccessRule',
	);
	/**
	 * @var array a list of access rule objects or configuration arrays for creating the rule objects.
	 * If a rule is specified via a configuration array, it will be merged with [[ruleConfig]] first
	 * before it is used for creating the rule object.
	 * @see ruleConfig
	 */
	public $rules = array();

	/**
	 * Initializes the [[rules]] array by instantiating rule objects from configurations.
	 */
	public function init()
	{
		parent::init();
		foreach ($this->rules as $i => $rule) {
			if (is_array($rule)) {
				$this->rules[$i] = Yii::createObject(array_merge($this->ruleConfig, $rule));
			}
		}
	}

	/**
	 * This method is invoked right before an action is to be executed (after all possible filters.)
	 * You may override this method to do last-minute preparation for the action.
	 * @param Action $action the action to be executed.
	 * @return boolean whether the action should continue to be executed.
	 */
	public function beforeAction($action)
	{
		$user = Yii::$app->getUser();
		$request = Yii::$app->getRequest();
		/** @var $rule AccessRule */
		foreach ($this->rules as $rule) {
			if ($allow = $rule->allows($action, $user, $request)) {
				break;
			} elseif ($allow === false) {
				if (isset($rule->denyCallback)) {
					call_user_func($rule->denyCallback, $rule);
				} elseif (isset($this->denyCallback)) {
					call_user_func($this->denyCallback, $rule);
				} else {
					$this->denyAccess($user);
				}
				return false;
			}
		}
		return true;
	}

	/**
	 * Denies the access of the user.
	 * The default implementation will redirect the user to the login page if he is a guest;
	 * if the user is already logged, a 403 HTTP exception will be thrown.
	 * @param User $user the current user
	 * @throws HttpException if the user is already logged in.
	 */
	protected function denyAccess($user)
	{
		if ($user->getIsGuest()) {
			$user->loginRequired();
		} else {
			throw new HttpException(403, Yii::t('yii', 'You are not allowed to perform this action.'));
		}
	}
}
