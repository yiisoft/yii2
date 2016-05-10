<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\web;

use Yii;
use yii\base\Component;
use yii\helpers\Json;

/**
 * JsonResponseFormatter formats the given data into a JSONP response content.
 *
 * It is used by [[Response]] to format response data.
 *
 * To configure properties like [[callback]] or [[callbackParam]], you can configure the `response`
 * application component like the following:
 *
 * ```php
 * 'response' => [
 *     // ...
 *     'formatters' => [
 *         \yii\web\Response::FORMAT_JSONP => [
 *              'class' => \yii\web\JsonpResponseFormatter::class,
 *              'callback' => 'jsonpProcess', //
 *              // ...
 *         ],
 *     ],
 * ],
 * ```
 *
 * @author Misbahul D Munir <misbahuldmunir@gmail.com>
 * @since 2.1
 */
class JsonpResponseFormatter extends Component implements ResponseFormatterInterface
{
    /**
     * @var string the name of the GET parameter that specifies the callback.
     * @see callback
     */
    public $callbackParam = 'callback';
    /**
     * @var string callback used. If not set, callback will use from GET.
     */
    public $callback;

    /**
     * Formats the specified response.
     * @param Response $response the response to be formatted.
     */
    public function format($response)
    {
        $response->getHeaders()->set('Content-Type', 'application/javascript; charset=UTF-8');
        $callback = $this->callback ? : Yii::$app->getRequest()->get($this->callbackParam);
        if ($callback !== null) {
            $response->content = sprintf('%s(%s);', $callback, Json::htmlEncode($response->data));
        } elseif ($response->data !== null) {
            $response->content = '';
            Yii::warning("The 'jsonp' response requires that jsonpCallback parameter to be set.", __METHOD__);
        }
    }
}
