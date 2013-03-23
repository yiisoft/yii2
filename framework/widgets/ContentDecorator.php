<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\widgets;

use Yii;
use yii\base\InvalidConfigException;
use yii\base\Widget;
use yii\base\View;

/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class ContentDecorator extends Widget
{
	/**
	 * @var View the view object for rendering [[viewName]]. If not set, the view registered with the application
	 * will be used.
	 */
	public $view;
	/**
	 * @var string the name of the view that will be used to decorate the content enclosed by this widget.
	 * Please refer to [[View::findViewFile()]] on how to set this property.
	 */
	public $viewName;
	/**
	 * @var array the parameters (name=>value) to be extracted and made available in the decorative view.
	 */
	public $params = array();

	/**
	 * Starts recording a clip.
	 */
	public function init()
	{
		if ($this->viewName === null) {
			throw new InvalidConfigException('ContentDecorator::viewName must be set.');
		}
		ob_start();
		ob_implicit_flush(false);
	}

	/**
	 * Ends recording a clip.
	 * This method stops output buffering and saves the rendering result as a named clip in the controller.
	 */
	public function run()
	{
		$params = $this->params;
		$params['content'] = ob_get_clean();
		$view = $this->view !== null ? $this->view : Yii::$app->getView();
		echo $view->render($this->viewName, $params);
	}
}
