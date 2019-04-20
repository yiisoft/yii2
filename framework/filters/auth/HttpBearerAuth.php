<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\filters\auth;

/**
 * HttpBearerAuth 是支持基于 HTTP 承载令牌的身份验证方法的操作筛选器。
 *
 * 您可以使用 HttpBearerAuth，将其作为行为附加到控制器或模块，如下所示：
 *
 * ```php
 * public function behaviors()
 * {
 *     return [
 *         'bearerAuth' => [
 *             'class' => \yii\filters\auth\HttpBearerAuth::className(),
 *         ],
 *     ];
 * }
 * ```
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class HttpBearerAuth extends HttpHeaderAuth
{
    /**
     * {@inheritdoc}
     */
    public $header = 'Authorization';
    /**
     * {@inheritdoc}
     */
    public $pattern = '/^Bearer\s+(.*?)$/';
    /**
     * @var string HTTP 身份验证范围
     */
    public $realm = 'api';


    /**
     * {@inheritdoc}
     */
    public function challenge($response)
    {
        $response->getHeaders()->set('WWW-Authenticate', "Bearer realm=\"{$this->realm}\"");
    }
}
