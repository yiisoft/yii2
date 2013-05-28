<?php

namespace app\models;

use yii\base\Model;

/**
 * ContactForm is the model behind the contact form.
 */
class ContactForm extends Model
{
	public $name;
	public $email;
	public $subject;
	public $body;
	public $verifyCode;

	/**
	 * @return array the validation rules.
	 */
	public function rules()
	{
		return array(
			// name, email, subject and body are required
			array('name, email, subject, body', 'required'),
			// email has to be a valid email address
			array('email', 'email'),
			// verifyCode needs to be entered correctly
			array('verifyCode', 'captcha'),
		);
	}

	/**
	 * @return array customized attribute labels
	 */
	public function attributeLabels()
	{
		return array(
			'verifyCode' => 'Verification Code',
		);
	}

	/**
	 * Sends an email to the specified email address using the information collected by this model.
	 * @param string $email the target email address
	 * @return boolean whether the model passes validation
	 */
	public function contact($email)
	{
		if ($this->validate()) {
			$name = '=?UTF-8?B?' . base64_encode($this->name) . '?=';
			$subject = '=?UTF-8?B?' . base64_encode($this->subject) . '?=';
			$headers = "From: $name <{$this->email}>\r\n" .
				"Reply-To: {$this->email}\r\n" .
				"MIME-Version: 1.0\r\n" .
				"Content-type: text/plain; charset=UTF-8";
			mail($email, $subject, $this->body, $headers);
			return true;
		} else {
			return false;
		}
	}
}
