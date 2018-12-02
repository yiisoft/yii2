<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\rest;

use Yii;
use yii\filters\auth\CompositeAuth;
use yii\filters\ContentNegotiator;
use yii\filters\RateLimiter;
use yii\filters\VerbFilter;
use yii\web\Response;

/**
 * Controller 是 RESTful API 控制器类的基类。
 *
 * Controller 在 RESTful API 请求处理周期中实现以下步骤：
 *
 * 1. 处理响应格式（参见 [[ContentNegotiator]]）;
 * 2. 验证请求方法（参见 [[verbs()]]）;
 * 3. 验证用户    （参见 [[\yii\filters\auth\AuthInterface]]）;
 * 4. 速率限制    （参见 [[RateLimiter]]）;
 * 5. 格式化响应数据（参见 [[serializeData()]]）。
 *
 * 关于 Controller 的更多使用参考，请查看 [Rest 控制器指南](guide:rest-controllers)。
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Controller extends \yii\web\Controller
{
    /**
     * @var string|array Serializer 的配置，用于格式化响应数据。
     */
    public $serializer = 'yii\rest\Serializer';
    /**
     * {@inheritdoc}
     */
    public $enableCsrfValidation = false;


    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'contentNegotiator' => [
                'class' => ContentNegotiator::className(),
                'formats' => [
                    'application/json' => Response::FORMAT_JSON,
                    'application/xml' => Response::FORMAT_XML,
                ],
            ],
            'verbFilter' => [
                'class' => VerbFilter::className(),
                'actions' => $this->verbs(),
            ],
            'authenticator' => [
                'class' => CompositeAuth::className(),
            ],
            'rateLimiter' => [
                'class' => RateLimiter::className(),
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function afterAction($action, $result)
    {
        $result = parent::afterAction($action, $result);
        return $this->serializeData($result);
    }

    /**
     * 声明允许的 HTTP verbs（HTTP 动词）。
     * 有关如何声明允许的动词，参考 [[VerbFilter::actions]]。
     * @return array 被允许的HTTP 动词。
     */
    protected function verbs()
    {
        return [];
    }

    /**
     * 序列化指定的数据。
     * 默认的实现是通过 [[serializer]] 属性指定的配置创建一个序列化类，
     * 然后用这个序列化类序列化指定的数据。
     * @param mixed $data 要序列化的数据。
     * @return mixed 已序列化的数据。
     */
    protected function serializeData($data)
    {
        return Yii::createObject($this->serializer)->serialize($data);
    }
}
