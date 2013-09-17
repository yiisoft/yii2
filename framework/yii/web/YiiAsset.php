<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\web;

use Yii;
use yii\base\View;

/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class YiiAsset extends AssetBundle
{
	public $sourcePath = '@yii/assets';
	public $js = array(
		'yii.js',
	);
	public $depends = array(
		'yii\web\JqueryAsset',
	);

	/**
	 * @inheritdoc
	 */
	public function registerAssets($view)
	{
		parent::registerAssets($view);
		$js[] = "yii.version='" . Yii::getVersion() . "';";
		$request = Yii::$app->getRequest();
		if ($request instanceof Request && $request->enableCsrfValidation) {
			$js[] = "yii.csrfVar='{$request->csrfVar}';";
			$js[] = "yii.csrfToken='{$request->csrfToken}';";
		}
		$view->registerJs(implode("\n", $js), View::POS_END);
	}
}
