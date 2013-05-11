<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\logging;

/**
 * EmailTarget sends selected log messages to the specified email addresses.
 *
 * The target email addresses may be specified via [[emails]] property.
 * Optionally, you may set the email [[subject]], [[sentFrom]] address and
 * additional [[headers]].
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class EmailTarget extends Target
{
	/**
	 * @var array list of destination email addresses.
	 */
	public $emails = array();
	/**
	 * @var string email subject
	 */
	public $subject;
	/**
	 * @var string email sent-from address
	 */
	public $sentFrom;
	/**
	 * @var array list of additional headers to use when sending an email.
	 */
	public $headers = array();

	/**
	 * Sends log messages to specified email addresses.
	 * @param array $messages the messages to be exported. See [[Logger::messages]] for the structure
	 * of each message.
	 */
	public function export($messages)
	{
		$body = '';
		foreach ($messages as $message) {
			$body .= $this->formatMessage($message);
		}
		$body = wordwrap($body, 70);
		$subject = $this->subject === null ? \Yii::t('yii|Application Log') : $this->subject;
		foreach ($this->emails as $email) {
			$this->sendEmail($subject, $body, $email, $this->sentFrom, $this->headers);
		}
	}

	/**
	 * Sends an email.
	 * @param string $subject email subject
	 * @param string $body email body
	 * @param string $sentTo sent-to email address
	 * @param string $sentFrom sent-from email address
	 * @param array $headers additional headers to be used when sending the email
	 */
	protected function sendEmail($subject, $body, $sentTo, $sentFrom, $headers)
	{
		if ($sentFrom !== null) {
			$headers[] = "From:  {$sentFrom}";
		}
		mail($sentTo, $subject, $body, implode("\r\n", $headers));
	}
}
