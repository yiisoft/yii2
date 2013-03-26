<?php
use \yii\web\Controller;

/**
 * SiteController
 */
class SiteController extends Controller
{
	public function actionIndex()
	{
		echo $this->render('index', array(
			'name' => 'Qiang',
		));
	}
}