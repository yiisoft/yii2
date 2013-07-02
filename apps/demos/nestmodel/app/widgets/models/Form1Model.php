<?php
namespace app\widgets\models;

class Form1Model extends \yii\base\Model {
  public function __construct(){
    $this->date = new \app\widgets\models\Date();
    $this->addModel($this->date,'dd');
  }
  //---------------------------------------------------------------------------
  /** @var Date*/
  public $date;
  //---------------------------------------------------------------------------
  /**
   * @return string
   */
  public function format() {
    return sprintf("%s",$this->date->format());
  }
  //---------------------------------------------------------------------------
}
