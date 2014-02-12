<?php

namespace yii\debug\panels;

use Yii;
use yii\base\Event;
use yii\debug\models\search\Mail;
use yii\debug\Panel;
use yii\mail\BaseMailer;
use yii\helpers\FileHelper;

/**
 * Debugger panel that collects and displays the generated emails.
 */
class MailPanel extends Panel
{

	/**
	 * @var string path where all emails will be saved. should be an alias.
	 */
	public $mailPath = '@runtime/debug/mail';
	/**
	 * @var array current request sent messages
	 */
	private $_messages = [];

	public function init()
	{
		parent::init();
		Event::on(BaseMailer::className(), BaseMailer::EVENT_AFTER_SEND, function ($event) {

			$message = $event->message->getSwiftMessage();
			$textBody = $message->getBody();
			$fileName = $event->sender->generateMessageFileName();

			FileHelper::createDirectory(Yii::getAlias($this->mailPath));
			file_put_contents(Yii::getAlias($this->mailPath) . '/' . $fileName, $message->toString());

			$this->_messages[] = [
					'isSuccessful' => $event->isSuccessful,
					'time' => $message->getDate(),
					'headers' => $message->getHeaders(),
					'from' => $this->convertParams($message->getFrom()),
					'to' => $this->convertParams($message->getTo()),
					'reply' => $this->convertParams($message->getReplyTo()),
					'cc' => $this->convertParams($message->getCc()),
					'bcc' => $this->convertParams($message->getBcc()),
					'subject' => $message->getSubject(),
					'body' => $textBody,
					'charset' => $message->getCharset(),
					'file' => $fileName,
			];
		});
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

	private function convertParams($attr)
	{
		if (is_array($attr)) {
			$attr = implode(', ', array_keys($attr));
		}
		return $attr;
	}

}
