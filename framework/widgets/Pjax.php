<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\widgets;

use Yii;
use yii\base\Widget;
use yii\helpers\Json;

/**
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Pjax extends Widget
{
	public $links = 'a';

	public function init()
	{
		ob_start();
		ob_implicit_flush(false);
		echo '<div id="' . $this->getId() . '">';
	}

	public function run()
	{
		echo '</div>';
		$content = ob_get_clean();

		$headers = Yii::$app->getRequest()->getHeaders();
		if ($headers->get('X-Pjax') && ($selector = $headers->get('X-PJax-Container')) === '#' . $this->getId()) {
			// todo: send the response and terminate the application
		} else {
			$this->registerClientScript();
			return $content;
		}
	}

	/**
	 * Registers the needed JavaScript.
	 */
	public function registerClientScript()
	{
		$view = $this->getView();
		PjaxAsset::register($view);
		$js = 'jQuery(document).pjax("' . Json::encode($this->links) . '", "#' . $this->getId() . '");';
		$view->registerJs($js);
	}
}
