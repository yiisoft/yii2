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
		return [
			[['lastName'], 'default', 'value' => 'Lennon'],
			[['lastName'], 'required'],
			[['underscore_style'], 'yii\captcha\CaptchaValidator'],
		];
	}
}
