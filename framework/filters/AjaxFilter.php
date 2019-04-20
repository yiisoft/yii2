<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\filters;

use Yii;
use yii\base\ActionFilter;
use yii\web\BadRequestHttpException;
use yii\web\Request;

/**
 * AjaxFilter 只允许限制 Ajax 请求的访问。
 *
 * ```php
 * public function behaviors()
 * {
 *     return [
 *         [
 *             'class' => 'yii\filters\AjaxFilter',
 *             'only' => ['index']
 *         ],
 *     ];
 * }
 * ```
 *
 * @author Dmitry Dorogin <dmirogin@ya.ru>
 * @since 2.0.13
 */
class AjaxFilter extends ActionFilter
{
    /**
     * @var 当请求不是 Ajax 时要显示的消息的字符串
     */
    public $errorMessage = 'Request must be XMLHttpRequest.';
    /**
     * @var Request 当前请求。如果未设置，将使用 `request` 应用程序组件。
     */
    public $request;


    /**
     * {@inheritdoc}
     */
    public function init()
    {
        if ($this->request === null) {
            $this->request = Yii::$app->getRequest();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function beforeAction($action)
    {
        if ($this->request->getIsAjax()) {
            return true;
        }

        throw new BadRequestHttpException($this->errorMessage);
    }
}
