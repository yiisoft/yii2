<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\helpers\base;

use Yii;
use yii\base\InvalidParamException;
use yii\web\Request;
use yii\base\Model;

/**
 * Html provides a set of static methods for generating commonly used HTML tags.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Html2 extends Html
{
	/**
	 * Generates an appropriate input name for the specified attribute name or expression.
	 *
	 * This method generates a name that can be used as the input name to collect user input
	 * for the specified attribute. The name is generated according to the [[Model::formName|form name]]
	 * of the model and the given attribute name. For example, if the form name of the `Post` model
	 * is `Post`, then the input name generated for the `content` attribute would be `Post[content]`.
	 *
	 * See [[getAttributeName()]] for explanation of attribute expression.
	 *
	 * @param Model $model the model object
	 * @param string $attribute the attribute name or expression
	 * @return string the generated input name
	 * @throws InvalidParamException if the attribute name contains non-word characters.
	 */
	public static function getInputName($model, $attribute)
	{
		$oo = '';
		$mm = $model->owner;
		while ( $mm !== null) {
			$oo = $mm->modelId . '_' . $oo;
			$mm = $mm->owner;
		}
		return $oo . $model->modelId . '_' . $attribute;
	}
}
