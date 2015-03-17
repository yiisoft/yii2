<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\authclient\widgets;

use yii\authclient\clients\GoogleOAuth;
use yii\base\InvalidConfigException;
use yii\base\Widget;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;

/**
 * GoogleSignInButton renders Google+ sign-in button.
 * This widget is designed to interact with [[GoogleOAuth]].
 *
 * @see GoogleOAuth
 * @see https://developers.google.com/+/web/signin/
 *
 * @property string|array $callback
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.0
 */
class GoogleSignInButton extends Widget
{
    /**
     * @var GoogleOAuth google auth client instance.
     */
    public $client;
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
        if (!($this->client instanceof GoogleOAuth)) {
            throw new InvalidConfigException('"' . $this->className() . '::client" must be instance of "' . GoogleOAuth::className() . '"');
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

    protected function generateCallback($url = null)
    {
        if (empty($url)) {
            $url = ['auth', 'authclient' => $this->client->id];
        }
        $url = Url::to($url);
        if (strpos($url, '?') === false) {
            $url .= '?';
        } else {
            $url .= '&';
        }

        $callbackName = 'googleSignInCallback' . md5($this->id);
        $js = <<<JS
function $callbackName(authResult) {
    var urlParams = [];
    for (var propName in authResult) {
        urlParams.push(encodeURIComponent(propName) + '=' + encodeURIComponent(authResult[propName]));
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
            ],
            $this->buttonHtmlOptions
        );
        return Html::tag('span', Html::tag('span', '', $buttonHtmlOptions), ['id' => 'signinButton']);
    }
}