<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\web;

use yii\base\Action;
use yii\base\InvalidParamException;

/**
 * ViewAction represents an action that displays a view according to a user-specified parameter.
 *
 * By default, the view being displayed is specified via the `view` GET parameter.
 * The name of the GET parameter can be customized via [[\yii\base\ViewAction::$viewParam]].
 * If the user doesn't provide the GET parameter, the default view specified by [[\yii\base\ViewAction::$defaultView]]
 * will be displayed.
 *
 * Users specify a view in the format of `path/to/view`, which translates to the view name
 * `ViewPrefix/path/to/view` where `ViewPrefix` is given by [[\yii\base\ViewAction::$viewPrefix]].
 *
 * Note, the user specified view can only contain word characters, dots and dashes and
 * the first letter must be a word letter.
 *
 * @property string $requestedView The name of the view requested by the user.
 * This is in the format of 'path/to/view'.
 *
 * @author Alexander Makarov <sam@rmcreative.ru>
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class ViewAction extends Action
{
    /**
     * @var string the name of the GET parameter that contains the requested view name. Defaults to 'view'.
     */
    public $viewParam = 'view';

    /**
     * @var string the name of the default view when [[\yii\base\ViewAction::$viewParam]] GET parameter is not provided
     * by user. Defaults to 'index'. This should be in the format of 'path/to/view', similar to that given in
     * the GET parameter.
     * @see \yii\base\ViewAction::$viewPrefix
     */
    public $defaultView = 'index';

    /**
     * @var string the base path for the views. Defaults to 'pages'.
     * The base path will be prefixed to any user-specified page view.
     * For example, if a user requests for `tutorial/chap1`, the corresponding view name will
     * be `pages/tutorial/chap1`, assuming the base path is `pages`.
     * The actual view file is determined by [[\yii\base\View::getViewFile()]].
     * @see \yii\base\View::getViewFile()
     */
    public $viewPrefix = 'pages';

    /**
     * @var mixed the name of the layout to be applied to the views.
     * This will be assigned to [[\yii\base\Controller::$layout]] before the view is rendered.
     * Defaults to null, meaning the controller's layout will be used.
     * If false, no layout will be applied.
     */
    public $layout;

    /**
     * @var string Used to store controller layout during executin and then restore it
     */
    private $_controllerLayout;

    /**
     * Runs the action.
     * This method displays the view requested by the user.
     * @throws NotFoundHttpException if the view file cannot be found
     */
    public function run()
    {
        $viewPath = $this->getViewPath();

        if($this->layout !== null) {
            $this->_controllerLayout = $this->controller->layout;
            $this->controller->layout = $this->layout;
        }

        try {
            return $this->controller->render($viewPath);
        } catch (InvalidParamException $e) {
            if (YII_DEBUG) {
                throw new NotFoundHttpException($e->getMessage());
            } else {
                throw new NotFoundHttpException(
                    \Yii::t('yii', 'The requested view "{name}" was not found.', ['name' => $viewPath])
                );
            }
        }
    }

    /**
     * @inheritdoc
     */
    protected function afterRun()
    {
        if ($this->layout !== null) {
            $this->controller->layout = $this->_controllerLayout;
        }
        parent::afterRun();
    }

    /**
     * Obtain view path from GET
     *
     * @return string view path
     * @throws NotFoundHttpException if view path doesn't match allowed format
     */
    protected function getViewPath()
    {
        $viewPath = \Yii::$app->request->get($this->viewParam);
        if (empty($viewPath) || !is_string($viewPath)) {
            $viewPath = $this->defaultView;
        }

        if (!preg_match('/^\w[\w\/\-]*$/', $viewPath)) {
            if (YII_DEBUG) {
                throw new NotFoundHttpException("The requested view \"$viewPath\" should start with a word char and contain word chars, forward slashes and dashes only.");
            } else {
                throw new NotFoundHttpException(\Yii::t('yii', 'The requested view "{name}" was not found.', ['name' => $viewPath]));
            }
        }

        if (!empty($this->viewPrefix)) {
            $viewPath = $this->viewPrefix . '/' . $viewPath;
            return $viewPath;
        }
        return $viewPath;
    }
}
 