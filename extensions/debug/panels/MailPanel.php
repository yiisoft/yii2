<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\debug\panels;

use Yii;
use yii\base\Event;
use yii\debug\models\search\Mail;
use yii\debug\Panel;
use yii\mail\BaseMailer;
use yii\helpers\FileHelper;
use yii\mail\MessageInterface;

/**
 * Debugger panel that collects and displays the generated emails.
 *
 * @property array $messages Messages. This property is read-only.
 *
 * @author Mark Jebri <mark.github@yandex.ru>
 * @since 2.0
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


    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        Event::on(BaseMailer::className(), BaseMailer::EVENT_AFTER_SEND, function ($event) {

            /* @var $message MessageInterface */
            $message = $event->message;
            $messageData = [
                    'isSuccessful' => $event->isSuccessful,
                    'from' => $this->convertParams($message->getFrom()),
                    'to' => $this->convertParams($message->getTo()),
                    'reply' => $this->convertParams($message->getReplyTo()),
                    'cc' => $this->convertParams($message->getCc()),
                    'bcc' => $this->convertParams($message->getBcc()),
                    'subject' => $message->getSubject(),
                    'charset' => $message->getCharset(),
            ];

            // add more information when message is a SwiftMailer message
            if ($message instanceof \yii\swiftmailer\Message) {
                /* @var $swiftMessage \Swift_Message */
                $swiftMessage = $message->getSwiftMessage();

                $body = $swiftMessage->getBody();
                if (empty($body)) {
                    $parts = $swiftMessage->getChildren();
                    foreach ($parts as $part) {
                        if (!($part instanceof \Swift_Mime_Attachment)) {
                            /* @var $part \Swift_Mime_MimePart */
                            if ($part->getContentType() == 'text/plain') {
                                $messageData['charset'] = $part->getCharset();
                                $body = $part->getBody();
                                break;
                            }
                        }
                    }
                }

                $messageData['body'] = $body;
                $messageData['time'] = $swiftMessage->getDate();
                $messageData['headers'] = $swiftMessage->getHeaders();

            }

            // store message as file
            $fileName = $event->sender->generateMessageFileName();
            FileHelper::createDirectory(Yii::getAlias($this->mailPath));
            file_put_contents(Yii::getAlias($this->mailPath) . '/' . $fileName, $message->toString());
            $messageData['file'] = $fileName;

            $this->_messages[] = $messageData;
        });
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'Mail';
    }

    /**
     * @inheritdoc
     */
    public function getSummary()
    {
        return Yii::$app->view->render('panels/mail/summary', ['panel' => $this, 'mailCount' => count($this->data)]);
    }

    /**
     * @inheritdoc
     */
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

    /**
     * @inheritdoc
     */
    public function save()
    {
        return $this->getMessages();
    }

    /**
     * Returns info about messages of current request. Each element is array holding
     * message info, such as: time, reply, bc, cc, from, to and other.
     * @return array messages
     */
    public function getMessages()
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
