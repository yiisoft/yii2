<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\widgets;

use Yii;
use yii\base\Widget;
use yii\base\View;

/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Clip extends Widget
{
	/**
	 * @var string the ID of this clip.
	 */
	public $id;
	/**
	 * @var View the view object for keeping the clip. If not set, the view registered with the application
	 * will be used.
	 */
	public $view;
	/**
	 * @var boolean whether to render the clip content in place. Defaults to false,
	 * meaning the captured clip will not be displayed.
	 */
	public $renderInPlace = false;

	/**
	 * Starts recording a clip.
	 */
	public function init()
	{
		ob_start();
		ob_implicit_flush(false);
	}

	/**
	 * Ends recording a clip.
	 * This method stops output buffering and saves the rendering result as a named clip in the controller.
	 */
	public function run()
	{
		$clip = ob_get_clean();
		if ($this->renderClip) {
			echo $clip;
		}
		$view = $this->view !== null ? $this->view : Yii::$app->getView();
		$view->clips[$this->id] = $clip;
	}
}