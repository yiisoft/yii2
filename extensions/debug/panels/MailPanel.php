<?php

namespace yii\debug\panels;

use Yii;
use yii\base\Event;
use yii\debug\models\search\Mail;
use yii\debug\Panel;
use yii\mail\BaseMailer;

/**
 * Debugger panel that collects and displays the generated emails.
 */
class MailPanel extends Panel
{
	private $_messages = [];

	public function init()
	{
		parent::init();
		Event::on(BaseMailer::className(), BaseMailer::EVENT_AFTER_SEND, function($event)
			{
				$yiiMessage = $event->message;
				$message = $yiiMessage->getSwiftMessage();

				$textBody = $message->getBody();
				if ($textBody === null) {
					$children = $message->getChildren();
					if (count($children)) {
						foreach ($children as $swiftMimePart) {
							if ($swiftMimePart instanceof \Swift_MimePart && $swiftMimePart->getContentType() == 'text/plain') {
								$textBody = $swiftMimePart->getBody();
								break;
							}
						}
					}
				} elseif ($message->getContentType() == 'text/html') {
					$textBody = null;
				}

				$this->_messages[] = [
					'isSuccessful' => $event->isSuccessful,
					'time' => $message->getDate(),
					'headers' => $message->getHeaders(),
					'from'=> $this->convertParams($message->getFrom()),
					'to' => $this->convertParams($message->getTo()),
					'reply' => $this->convertParams($message->getReplyTo()),
					'cc' => $this->convertParams($message->getCc()),
					'bcc' => $this->convertParams($message->getBcc()),
					'subject'=> $message->getSubject(),
					'body' => $textBody,
					'charset' => $message->getCharset(),
					'file' => $event->file,
				];
			});
	}

	private function convertParams($attr)
	{
		if (is_array($attr)) {
			$attr = implode(', ', array_keys($attr));
		}
		return $attr;
	}

	public function getName()
	{
		return 'Mail';
	}

	public function getSummary()
	{
		return Yii::$app->view->render('panels/mail/summary', ['panel' => $this, 'mailCount' => count($this->data)]);
	}

	public function getDetail()
	{
		$searchModel = new Mail();
		$dataProvider = $searchModel->search(Yii::$app->request->get(), $this->data);

		return Yii::$app->view->render('panels/mail/detail', [
				'panel' => $this,
				'dataProvider' => $dataProvider,
				'searchModel' => $searchModel
			]);
	}

	public function save()
	{
		return $this->_messages;
	}
}
