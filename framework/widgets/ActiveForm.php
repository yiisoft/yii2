<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\widgets;

use yii\base\Widget;

/**
 * ActiveForm ...
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class ActiveForm extends Widget
{
	/**
	 * @var mixed the form action URL (see {@link CHtml::normalizeUrl} for details about this parameter).
	 * If not set, the current page URL is used.
	 */
	public $action = '';
	/**
	 * @var string the form submission method. This should be either 'post' or 'get'.
	 * Defaults to 'post'.
	 */
	public $method = 'post';
	/**
	 * @var string the CSS class name for error messages. Defaults to 'errorMessage'.
	 * Individual {@link error} call may override this value by specifying the 'class' HTML option.
	 */
	public $errorMessageCssClass = 'errorMessage';
	/**
	 * @var array additional HTML attributes that should be rendered for the form tag.
	 */
	public $htmlOptions = array();
	/**
	 * @var boolean whether to enable data validation via AJAX. Defaults to false.
	 * When this property is set true, you should respond to the AJAX validation request on the server side as shown below:
	 * <pre>
	 * public function actionCreate()
	 * {
	 *     $model=new User;
	 *     if(isset($_POST['ajax']) && $_POST['ajax']==='user-form')
	 *     {
	 *         echo CActiveForm::validate($model);
	 *         Yii::app()->end();
	 *     }
	 *     ......
	 * }
	 * </pre>
	 */
	public $enableAjaxValidation = false;
	/**
	 * @var boolean whether to enable client-side data validation. Defaults to false.
	 *
	 * When this property is set true, client-side validation will be performed by validators
	 * that support it (see {@link CValidator::enableClientValidation} and {@link CValidator::clientValidateAttribute}).
	 *
	 * @see error
	 * @since 1.1.7
	 */
	public $enableClientValidation = false;


	public function errorSummary($model, $options = array())
	{
	}

	public function error($model, $attribute, $options = array())
	{
	}

	public function label($model, $attribute, $options = array())
	{
	}

	public function input($type, $model, $attribute, $options = array())
	{
		return '';
	}

	public function textInput($model, $attribute, $options = array())
	{
		return $this->input('text', $model, $attribute, $options);
	}

	public function hiddenInput($model, $attribute, $options = array())
	{
		return $this->input('hidden', $model, $attribute, $options);
	}

	public function passwordInput($model, $attribute, $options = array())
	{
		return $this->input('password', $model, $attribute, $options);
	}

	public function fileInput($model, $attribute, $options = array())
	{
		return $this->input('file', $model, $attribute, $options);
	}

	public function textarea($model, $attribute, $options = array())
	{
	}

	public function radio($model, $attribute, $value = '1', $options = array())
	{
	}

	public function checkbox($model, $attribute, $value = '1', $options = array())
	{
	}

	public function dropDownList($model, $attribute, $items, $options = array())
	{
	}

	public function listBox($model, $attribute, $items, $options = array())
	{
	}

	public function checkboxList($model, $attribute, $items, $options = array())
	{
	}

	public function radioList($model, $attribute, $items, $options = array())
	{
	}
}
