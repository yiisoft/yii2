<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\web;

use Yii;
use yii\base\Object;

/**
 * CompositeUrlRule is the base class for URL rule classes that consist of multiple simpler rules.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
abstract class CompositeUrlRule extends Object implements UrlRuleInterface
{
    /**
     * @var UrlRuleInterface[] the URL rules contained in this composite rule.
     * This property is set in [[init()]] by the return value of [[createRules()]].
     */
    protected $rules = [];

    /**
     * @var string|null status of URL creation after last [[createUrl()]] call.
     * @since 2.0.12
     */
    public $createStatus;


    /**
     * Creates the URL rules that should be contained within this composite rule.
     * @return UrlRuleInterface[] the URL rules
     */
    abstract protected function createRules();

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->rules = $this->createRules();
    }

    /**
     * @inheritdoc
     */
    public function parseRequest($manager, $request)
    {
        foreach ($this->rules as $rule) {
            /* @var $rule UrlRule */
            $result = $rule->parseRequest($manager, $request);
            if (YII_DEBUG) {
                Yii::trace([
                    'rule' => method_exists($rule, '__toString') ? $rule->__toString() : get_class($rule),
                    'match' => $result !== false,
                    'parent' => self::className()
                ], __METHOD__);
            }
            if ($result !== false) {
                return $result;
            }
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function createUrl($manager, $route, $params)
    {
        $this->createStatus = UrlRule::CREATE_STATUS_ROUTE_MISMATCH;
        foreach ($this->rules as $rule) {
            /* @var $rule UrlRule */
            if (($url = $rule->createUrl($manager, $route, $params)) !== false) {
                $this->createStatus = UrlRule::CREATE_STATUS_SUCCESS;
                return $url;
            } elseif ($this->createStatus === null || !isset($rule->createStatus)) {
                $this->createStatus = null;
            } elseif ($rule->createStatus === UrlRule::CREATE_STATUS_PARAMS_MISMATCH) {
                $this->createStatus = UrlRule::CREATE_STATUS_PARAMS_MISMATCH;
            }
        }

        return false;
    }
}
