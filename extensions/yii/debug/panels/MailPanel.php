<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\debug\panels;

use Yii;
use yii\debug\models\search\Mail;
use yii\debug\Panel;

/**
 * Debugger panel that collects and displays the generated emails.
 */
class MailPanel extends Panel
{
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
        $mail = Yii::$app->getMail();
		return $mail::getSavedMessages();
	}
}
