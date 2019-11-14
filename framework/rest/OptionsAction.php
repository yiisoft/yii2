<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\rest;

use Yii;

/**
 * OptionsAction 通过发送回 `Allow` 请求头来响应 OPTIONS 请求。
 *
 * 关于 OptionsAction 的更多使用参考，请查看 [Rest 控制器指南](guide:rest-controllers)。
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class OptionsAction extends \yii\base\Action
{
    /**
     * @var array 此数据集 URL 所允许的 HTTP 动词。
     */
    public $collectionOptions = ['GET', 'POST', 'HEAD', 'OPTIONS'];
    /**
     * @var array 此资源 URL 所允许的 HTTP 动词。
     */
    public $resourceOptions = ['GET', 'PUT', 'PATCH', 'DELETE', 'HEAD', 'OPTIONS'];


    /**
     *  响应 OPTIONS 请求。
     * @param string $id
     */
    public function run($id = null)
    {
        if (Yii::$app->getRequest()->getMethod() !== 'OPTIONS') {
            Yii::$app->getResponse()->setStatusCode(405);
        }
        $options = $id === null ? $this->collectionOptions : $this->resourceOptions;
        $headers = Yii::$app->getResponse()->getHeaders();
        $headers->set('Allow', implode(', ', $options));
        $headers->set('Access-Control-Allow-Methods', implode(', ', $options));
    }
}
