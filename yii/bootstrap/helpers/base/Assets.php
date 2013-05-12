<?php
/**
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @link http://www.yiiframework.com/
 * @license http://www.yiiframework.com/license/
 */

namespace yii\bootstrap\helpers\base;
use Yii;

/**
 * Assets provides methods to register bootstrap assets.
 *
 * @author Antonio Ramirez <amigo.cobos@gmail.com>
 * @since 2.0
 */
class Assets
{
	public static function registerBundle($responsive = false)
	{
		$bundle = $responsive ? 'yii/bootstrap' : 'yii/bootstrap-responsive';

		Yii::$app->getView()->registerAssetBundle($bundle);
	}
}