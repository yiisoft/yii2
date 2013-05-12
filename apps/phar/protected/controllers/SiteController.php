<?php

use \yii\web\Controller;

class SiteController extends Controller
{
	public function actionIndex()
	{
		echo $this->render('index');
	}

	public function actionAction1()
	{
		echo $this->render('action1');
	}

	public function actionAction2()
	{
		echo $this->render('action2');
	}
}
