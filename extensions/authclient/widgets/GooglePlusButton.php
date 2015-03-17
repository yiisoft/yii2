<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\authclient\widgets;

use yii\authclient\clients\GooglePlus;
use yii\base\InvalidConfigException;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;

/**
 * GooglePlusButton renders Google+ sign-in button.
 * This widget is designed to interact with [[GooglePlus]].
 *
 * @see GooglePlus
 * @see https://developers.google.com/+/web/signin/
 *
 * @property \yii\authclient\clients\GooglePlus $client auth client instance.
 * @property string|array $callback
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.0
 */
class GooglePlusButton extends AuthChoiceItem
{
    /**
     * @var array button tag HTML options, which will be merged with the default ones.
     */
    public $buttonHtmlOptions = [];

    /**
     * @var string|array name of the JavaScript function, which should be used as sign-in callback.
     * If blank default one will be generated: it will redirect page to the auth action using auth result
     * as GET parameters.
     * You may pass an array configuration of the URL here, which
     * will be used creating default callback.
     */
    private $_callback;

    /**
     * @param string $callback
     */
    public function setCallback($callback)
    {
        $this->_callback = $callback;
    }

    /**
     * @return string
     */
    public function getCallback()
    {
        if (empty($this->_callback)) {
            $this->_callback = $this->generateCallback();
        } elseif (is_array($this->_callback)) {
            $this->_callback = $this->generateCallback($this->_callback);
        }
        return $this->_callback;
    }

    /**
     * Initializes the widget.
     */
    public function init()
    {
        if (!($this->client instanceof GooglePlus)) {
            throw new InvalidConfigException('"' . $this->className() . '::client" must be instance of "' . GooglePlus::className() . '"');
        }
    }

    /**
     * Runs the widget.
     */
    public function run()
    {
        $this->registerClientScript();
        return $this->renderButton();
    }

    /**
     * Generates JavaScript callback function, which will be used to handle auth response.
     * @param array $url auth callback URL.
     * @return string JavaScript function name.
     */
    protected function generateCallback($url = [])
    {
        if (empty($url)) {
            $url = $this->authChoice->createClientUrl($this->client);
        } else {
            $url = Url::to($url);
        }
        if (strpos($url, '?') === false) {
            $url .= '?';
        } else {
            $url .= '&';
        }

        $callbackName = 'googleSignInCallback' . md5($this->id);
        $js = <<<JS
function $callbackName(authResult) {
    var urlParams = [];

    if (authResult['code']) {
        urlParams.push('code=' + encodeURIComponent(authResult['code']));
    } else if (authResult['error']) {
        urlParams.push('error=' + encodeURIComponent(authResult['error']));
        urlParams.push('error_description=' + encodeURIComponent(authResult['error_description']));
    } else {
        for (var propName in authResult) {
            var propValue = authResult[propName];
            if (typeof propValue != 'object') {
                urlParams.push(encodeURIComponent(propName) + '=' + encodeURIComponent(propValue));
            }
        }
    }

    window.location = '$url' + urlParams.join('&');
}
JS;
        $this->view->registerJs($js, View::POS_END, __CLASS__ . '#' . $this->id);

        return $callbackName;
    }

    /**
     * Registers necessary JavaScript.
     */
    protected function registerClientScript()
    {
        $js = <<<JS
(function() {
    var po = document.createElement('script'); po.type = 'text/javascript'; po.async = true;
    po.src = 'https://apis.google.com/js/client:plusone.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(po, s);
})();
JS;
        $this->view->registerJs($js, View::POS_END, __CLASS__);
    }

    /**
     * Renders sign-in button.
     * @return string button HTML.
     */
    protected function renderButton()
    {
        $buttonHtmlOptions = array_merge(
            [
                'class' => 'g-signin',
                'data-callback' => $this->getCallback(),
                'data-clientid' => $this->client->clientId,
                'data-cookiepolicy' => 'single_host_origin',
                'data-requestvisibleactions' => null,
                'data-scope' => $this->client->scope,
                'data-accesstype' => 'offline',
                'data-width' => 'iconOnly',
                //'data-approvalprompt' => 'force',
            ],
            $this->buttonHtmlOptions
        );
        return Html::tag('span', Html::tag('span', '', $buttonHtmlOptions), ['id' => 'signinButton']);
    }
}