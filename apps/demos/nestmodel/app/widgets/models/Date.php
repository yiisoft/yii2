<?php
namespace app\widgets\models;

class Date extends \yii\base\Model {
  //---------------------------------------------------------------------------
  /** @var integer The day*/
  public $day;
  //---------------------------------------------------------------------------
  /** @var integer The year*/
  public $year;
  //---------------------------------------------------------------------------
  /** @var integer The month*/
  public $month;
  //---------------------------------------------------------------------------
  public function scenarios() {
    return array(
      'default' => array('day','month','year'),
    );
  }
  //---------------------------------------------------------------------------
  function format(){
    return sprintf("%02d/%02d/%d",$this->day,$this->month,$this->year);
  }
  //---------------------------------------------------------------------------
  function update($dd,$mm,$yy) {
    $this->day = $dd;
    $this->month = $mm;
    $this->year = $yy;
  }
  //---------------------------------------------------------------------------
}
