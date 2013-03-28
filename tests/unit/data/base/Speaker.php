<?php
namespace yiiunit\data\base;

/**
 * Speaker
 */
use yii\base\Model;

class Speaker extends Model
{
	public $firstName;
	public $lastName;

	public $customLabel;
	public $underscore_style;

	protected $protectedProperty;
	private $_privateProperty;

	public function attributeLabels()
	{
		return array(
			'customLabel' => 'This is the custom label',
		);
	}

	public function rules()
	{
		return array(

		);
	}

	public function scenarios()
	{
		return array(
			'test' => array('firstName', 'lastName', '!underscore_style'),
		);
	}
}
