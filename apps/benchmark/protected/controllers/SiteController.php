<?php

use yii\web\Controller;

class SiteController extends Controller
{
	public $defaultAction = 'hello';

	public function actionHello()
	{
		return 'hello world';
	}
}
