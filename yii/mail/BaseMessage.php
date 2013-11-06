<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\mail;

use yii\base\InvalidParamException;
use yii\base\Object;
use yii\helpers\FileHelper;
use Yii;

/**
 * BaseMessage represent the single email message.
 * It functionality depends on application component 'email',
 * which should provide the actual email sending functionality as well as
 * default message configuration.
 *
 * @see BaseMailer
 *
 * @property \yii\mail\BaseMailer $mailer mailer component instance. This property is read-only.
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.0
 */
abstract class BaseMessage extends Object implements MessageInterface
{
	/**
	 * @return \yii\mail\BaseMailer mailer component instance.
	 */
	public function getMailer()
	{
		return Yii::$app->getComponent('mail');
	}

	/**
	 * @inheritdoc
	 */
	public function send()
	{
		return $this->getMailer()->send($this);
	}

	/**
	 * @inheritdoc
	 */
	public function renderHtml($view, $params = [])
	{
		$this->html($this->getMailer()->render($view, $params, $this->getMailer()->htmlLayout));
		return $this;
	}

	/**
	 * @inheritdoc
	 */
	public function renderText($view, $params = [])
	{
		$this->text($this->getMailer()->render($view, $params, $this->getMailer()->textLayout));
		return $this;
	}

	/**
	 * @inheritdoc
	 */
	public function body($view, $params = [])
	{
		if (is_array($view)) {
			$this->renderHtml($view['html'], $params);
			$this->renderText($view['text'], $params);
		} else {
			$html = $this->getMailer()->render($view, $params, $this->getMailer()->htmlLayout);
			$this->html($html);
			$this->text(strip_tags($html));
		}
		return $this;
	}

	/**
	 * PHP magic method that returns the string representation of this object.
	 * @return string the string representation of this object.
	 */
	public function __toString()
	{
		// __toString cannot throw exception
		// use trigger_error to bypass this limitation
		try {
			return $this->toString();
		} catch (\Exception $e) {
			trigger_error($e->getMessage());
			return '';
		}
	}
}