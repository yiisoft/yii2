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
     * @var int|null status of the URL creation after the last [[createUrl()]] call.
     * @since 2.0.12
     */
    protected $createStatus;


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
        $this->createStatus = UrlRule::CREATE_STATUS_SUCCESS;
        foreach ($this->rules as $rule) {
            /* @var $rule UrlRule */
            if (($url = $rule->createUrl($manager, $route, $params)) !== false) {
                $this->createStatus = UrlRule::CREATE_STATUS_SUCCESS;
                return $url;
            } elseif (
                $this->createStatus === null
                || !method_exists($rule, 'getCreateUrlStatus')
                || $rule->getCreateUrlStatus() === null
            ) {
                $this->createStatus = null;
            } else {
                $this->createStatus |= $rule->getCreateUrlStatus();
            }
        }

        if ($this->createStatus === UrlRule::CREATE_STATUS_SUCCESS) {
            // create status was not changed - there is no rules configured
            $this->createStatus = UrlRule::CREATE_STATUS_PARSING_ONLY;
        }
        return false;
    }

    /**
     * Returns status of the URL creation after the last [[createUrl()]] call.
     *
     * @return null|int
     * @since 2.0.12
     */
    public function getCreateUrlStatus() {
        return $this->createStatus;
    }
}
