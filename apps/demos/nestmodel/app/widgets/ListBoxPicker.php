<?php
namespace app\widgets;
use \yii\helpers\Html;

class ListBoxPicker {
  //---------------------------------------------------------------------------
  public function __construct($listData) {
    $this->listData = $listData;
  }
  //---------------------------------------------------------------------------
  public function attach($model,$attribute) {
    $this->model = $model;
    $this->attribute = $attribute;
  }
  //---------------------------------------------------------------------------
  function run() {
    if($this->model !== null ) {
      echo Html::activeListBox($this->model, $this->attribute, $this->listData , array('size'=>1, 'style'=>'width:5em'));
    }
  }
  //---------------------------------------------------------------------------
  protected $model;
  protected $attribute;
  protected $listData;
  //---------------------------------------------------------------------------
}