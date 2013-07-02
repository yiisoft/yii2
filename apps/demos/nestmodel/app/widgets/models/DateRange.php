<?php
namespace app\widgets\models;

class DateRange extends \yii\base\Model {
  //---------------------------------------------------------------------------
  public function __construct(){
    $this->date1 = new \app\widgets\models\Date();
    $this->date2 = new \app\widgets\models\Date();
    $this->addModel($this->date1,'d1');
    $this->addModel($this->date2,'d2');
  }
  //---------------------------------------------------------------------------
  /** @var Date The day*/
  public $date1;
  //---------------------------------------------------------------------------
  /** @var Date The day*/
  public $date2;
  //---------------------------------------------------------------------------
  public function format() {
    return sprintf("%s - %s",$this->date1->format() , $this->date2->format());
  }
  //---------------------------------------------------------------------------
}
