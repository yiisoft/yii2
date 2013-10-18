<?php
namespace yiiunit\data\base;

use yii\base\Model;

/**
 * Singer
 */
class Singer extends Model
{
	public $fistName;
	public $lastName;

	public function rules()
	{
		return array(
			array('lastName', 'default', 'value' => 'Lennon'),
			array('lastName', 'required'),
			array('underscore_style', 'yii\captcha\CaptchaValidator'),
		);
	}
}
