<?php
namespace app\widgets;
class Form1 {
  //---------------------------------------------------------------------------
  public function __construct($model) {
    $this->model = $model;
  }
  //---------------------------------------------------------------------------
  public $date;
  public $controller;

  public function run() {
    $params = array();
    $params['model'] = $this->model;
    echo \Yii::$app->view->renderFile('@app/widgets/Form1_.php', $params);
  }
}

