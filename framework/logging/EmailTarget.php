<?php
/**
 * CEmailLogRoute class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2011 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CEmailLogRoute sends selected log messages to email addresses.
 *
 * The target email addresses may be specified via {@link setEmails emails} property.
 * Optionally, you may set the email {@link setSubject subject}, the
 * {@link setSentFrom sentFrom} address and any additional {@link setHeaders headers}.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id: CEmailLogRoute.php 3001 2011-02-24 16:42:44Z alexander.makarow $
 * @package system.logging
 * @since 1.0
 */
class CEmailLogRoute extends CLogRoute
{
	/**
	 * @var array list of destination email addresses.
	 */
	private $_email = array();
	/**
	 * @var string email subject
	 */
	private $_subject;
	/**
	 * @var string email sent from address
	 */
	private $_from;
	/**
	 * @var array list of additional headers to use when sending an email.
	 */
	private $_headers = array();

	/**
	 * Sends log messages to specified email addresses.
	 * @param array $logs list of log messages
	 */
	protected function processLogs($logs)
	{
		$message = '';
		foreach ($logs as $log)
			$message .= $this->formatLogMessage($log[0], $log[1], $log[2], $log[3]);
		$message = wordwrap($message, 70);
		$subject = $this->getSubject();
		if ($subject === null)
			$subject = Yii::t('yii', 'Application Log');
		foreach ($this->getEmails() as $email)
			$this->sendEmail($email, $subject, $message);
	}

	/**
	 * Sends an email.
	 * @param string $email single email address
	 * @param string $subject email subject
	 * @param string $message email content
	 */
	protected function sendEmail($email, $subject, $message)
	{
		$headers = $this->getHeaders();
		if (($from = $this->getSentFrom()) !== null)
			$headers[] = "From:  {$from}";
		mail($email, $subject, $message, implode("\r\n", $headers));
	}

	/**
	 * @return array list of destination email addresses
	 */
	public function getEmails()
	{
		return $this->_email;
	}

	/**
	 * @param mixed $value list of destination email addresses. If the value is
	 * a string, it is assumed to be comma-separated email addresses.
	 */
	public function setEmails($value)
	{
		if (is_array($value))
			$this->_email = $value;
		else
			$this->_email = preg_split('/[\s,]+/', $value, -1, PREG_SPLIT_NO_EMPTY);
	}

	/**
	 * @return string email subject. Defaults to CEmailLogRoute::DEFAULT_SUBJECT
	 */
	public function getSubject()
	{
		return $this->_subject;
	}

	/**
	 * @param string $value email subject.
	 */
	public function setSubject($value)
	{
		$this->_subject = $value;
	}

	/**
	 * @return string send from address of the email
	 */
	public function getSentFrom()
	{
		return $this->_from;
	}

	/**
	 * @param string $value send from address of the email
	 */
	public function setSentFrom($value)
	{
		$this->_from = $value;
	}

	/**
	 * @return array additional headers to use when sending an email.
	 * @since 1.1.4
	 */
	public function getHeaders()
	{
		return $this->_headers;
	}

	/**
	 * @param mixed $value list of additional headers to use when sending an email.
	 * If the value is a string, it is assumed to be line break separated headers.
	 * @since 1.1.4
	 */
	public function setHeaders($value)
	{
		if (is_array($value))
			$this->_headers = $value;
		else
			$this->_headers = preg_split('/\r\n|\n/', $value, -1, PREG_SPLIT_NO_EMPTY);
	}
}