<?php
namespace app\controllers;

use yii\web\Controller;



class DemoController extends Controller {
  //---------------------------------------------------------------------------
  public function actionDemo01() {
    $form1 = new \app\widgets\models\Form1Model();
    $form1->date->update(1,1,2001);
    $form1->modelId = 'h1';
    if ( $form1->bind(\Yii::$app->request->params) ) {
    }
    $params = array();
    $params['form1'] = $form1;

    return $this->render('demo01',$params);
  }
  //---------------------------------------------------------------------------
  public function actionDemo02() {
    $form1 = new \app\widgets\models\Form1Model();
    $form2 = new \app\widgets\models\Form1Model();
    $form3 = new \app\widgets\models\Form1Model();

    $form1->modelId = 'h1';
    $form2->modelId = 'h2';
    $form3->modelId = 'h3';

    if ( $form1->bind(\Yii::$app->request->params) ) {
    }
    if ( $form2->bind(\Yii::$app->request->params) ) {
    }
    if ( $form3->bind(\Yii::$app->request->params) ) {
    }
    $params = array();
    $params['form1'] = $form1;
    $params['form2'] = $form2;
    $params['form3'] = $form3;
    return $this->render('demo02', $params);
  }
  //---------------------------------------------------------------------------
  public function actionDemo03() {
    $form1 = new \app\widgets\models\Form2Model();
    $form2 = new \app\widgets\models\Form2Model();
    $form3 = new \app\widgets\models\Form2Model();
    $form1->modelId = 'h1';
    $form2->modelId = 'h2';
    $form3->modelId = 'h3';

    if ( $form1->bind(\Yii::$app->request->params) ) {
    }
    if ( $form2->bind(\Yii::$app->request->params) ) {
    }
    if ( $form3->bind(\Yii::$app->request->params) ) {
    }
    $params = array();
    $params['form1'] = $form1;
    $params['form2'] = $form2;
    $params['form3'] = $form3;
    return $this->render('demo03', $params);
  }
  //---------------------------------------------------------------------------
  public function actionDemo04() {
    $form1 = new \app\widgets\models\Form3Model(3);
    $form2 = new \app\widgets\models\Form3Model(5);
    $form1->modelId = 'h1';
    $form2->modelId = 'h2';
    if ( $form1->bind(\Yii::$app->request->params) ) {
    }
    if ( $form2->bind(\Yii::$app->request->params) ) {
    }
    $params = array();
    $params['form1'] = $form1;
    $params['form2'] = $form2;
    return $this->render('demo04', $params);
  }
  //---------------------------------------------------------------------------

}