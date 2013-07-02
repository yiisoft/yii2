<?php
namespace app\widgets\models;

class Form2Model extends \yii\base\Model {
  //---------------------------------------------------------------------------
  public function __construct(){
    $this->range = new \app\widgets\models\DateRange();
    $this->addModel($this->range,'dd');
  }
  //---------------------------------------------------------------------------
  /** @var DateRange*/
  public $range;
  //---------------------------------------------------------------------------
  public function format() {
    return sprintf("%s",$this->range->format());
  }
  //---------------------------------------------------------------------------
}