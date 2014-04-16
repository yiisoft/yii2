<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\widgets;

use Yii;
use yii\base\Widget;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\web\Response;

/**
 * Pjax is a widget integrating the [pjax](https://github.com/yiisoft/jquery-pjax) jQuery plugin.
 *
 * Pjax only deals with the content enclosed between its [[begin()]] and [[end()]] calls, called the *body content* of the widget.
 * By default, any link click or form submission (for those forms with `data-pjax` attribute) within the body content
 * will trigger an AJAX request. In responding to the AJAX request, Pjax will send the updated body content (based
 * on the AJAX request) to the client which will replace the old content with the new one. The browser's URL will then
 * be updated using pushState. The whole process requires no reloading of the layout or resources (js, css).
 *
 * You may configure [[linkSelector]] to specify which links should trigger pjax, and configure [[formSelector]]
 * to specify which form submission may trigger pjax.
 *
 * You may disable pjax for a specific link inside the container by adding `data-pjax="0"` attribute to this link.
 *
 * The following example shows how to use Pjax with the [[\yii\grid\GridView]] widget so that the grid pagination,
 * sorting and filtering can be done via pjax:
 *
 * ```php
 * use yii\widgets\Pjax;
 *
 * Pjax::begin();
 * echo GridView::widget([...]);
 * Pjax::end();
 * ```
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Pjax extends Widget
{
    /**
     * @var array the HTML attributes for the widget container tag.
     * @see \yii\helpers\Html::renderTagAttributes() for details on how attributes are being rendered.
     */
    public $options = [];
    /**
     * @var string the jQuery selector of the links that should trigger pjax requests.
     * If not set, all links within the enclosed content of Pjax will trigger pjax requests.
     * Note that if the response to the pjax request is a full page, a normal request will be sent again.
     */
    public $linkSelector;
    /**
     * @var string the jQuery selector of the forms whose submissions should trigger pjax requests.
     * If not set, all forms with `data-pjax` attribute within the enclosed content of Pjax will trigger pjax requests.
     * Note that if the response to the pjax request is a full page, a normal request will be sent again.
     */
    public $formSelector;
    /**
     * @var boolean whether to enable push state.
     */
    public $enablePushState = true;
    /**
     * @var boolean whether to enable replace state.
     */
    public $enableReplaceState = false;
    /**
     * @var integer pjax timeout setting (in milliseconds). This timeout is used when making AJAX requests.
     * Use a bigger number if your server is slow. If the server does not respond within the timeout,
     * a full page load will be triggered.
     */
    public $timeout = 1000;
    /**
     * @var boolean|integer how to scroll the page when pjax response is received. If false, no page scroll will be made.
     * Use a number if you want to scroll to a particular place.
     */
    public $scrollTo = false;
    /**
     * @var array additional options to be passed to the pjax JS plugin. Please refer to the
     * [pjax project page](https://github.com/yiisoft/jquery-pjax) for available options.
     */
    public $clientOptions;

    /**
     * @inheritdoc
     */
    public function init()
    {
        if (!isset($this->options['id'])) {
            $this->options['id'] = $this->getId();
        }

        if ($this->requiresPjax()) {
            ob_start();
            ob_implicit_flush(false);
            $view = $this->getView();
            $view->clear();
            $view->beginPage();
            $view->head();
            $view->beginBody();
            if ($view->title !== null) {
                echo Html::tag('title', Html::encode($view->title));
            }
        } else {
            echo Html::beginTag('div', $this->options);
        }
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        if (!$this->requiresPjax()) {
            echo Html::endTag('div');
            $this->registerClientScript();

            return;
        }

        $view = $this->getView();
        $view->endBody();

        // Do not re-send css files as it may override the css files that were loaded after them.
        // This is a temporary fix for https://github.com/yiisoft/yii2/issues/2310
        // It should be removed once pjax supports loading only missing css files
        $view->cssFiles = null;

        $view->endPage(true);

        $content = ob_get_clean();

        // only need the content enclosed within this widget
        $response = Yii::$app->getResponse();
        $response->clearOutputBuffers();
        $response->setStatusCode(200);
        $response->format = Response::FORMAT_HTML;
        $response->content = $content;
        $response->send();

        Yii::$app->end();
    }

    /**
     * @return boolean whether the current request requires pjax response from this widget
     */
    protected function requiresPjax()
    {
        $headers = Yii::$app->getRequest()->getHeaders();

        return $headers->get('X-Pjax') && $headers->get('X-Pjax-Container') === '#' . $this->getId();
    }

    /**
     * Registers the needed JavaScript.
     */
    public function registerClientScript()
    {
        $id = $this->options['id'];
        $this->clientOptions['push'] = $this->enablePushState;
        $this->clientOptions['replace'] = $this->enableReplaceState;
        $this->clientOptions['timeout'] = $this->timeout;
        $this->clientOptions['scrollTo'] = $this->scrollTo;
        $options = Json::encode($this->clientOptions);
        $linkSelector = Json::encode($this->linkSelector !== null ? $this->linkSelector : '#' . $id . ' a');
        $formSelector = Json::encode($this->formSelector !== null ? $this->formSelector : '#' . $id . ' form[data-pjax]');
        $view = $this->getView();
        PjaxAsset::register($view);
        $js = "jQuery(document).pjax($linkSelector, \"#$id\", $options);";
        $js .= "\njQuery(document).on('submit', $formSelector, function (event) {jQuery.pjax.submit(event, '#$id', $options);});";
        $view->registerJs($js);
    }
}
