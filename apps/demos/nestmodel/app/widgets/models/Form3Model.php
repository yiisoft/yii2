<?php
namespace app\widgets\models;

class Form3Model extends \yii\base\Model {
  //---------------------------------------------------------------------------
  public function __construct($count) {
    $this->ranges = array();
    for ( $ii = 1; $ii <= $count; $ii++ ) {
      $range = new \app\widgets\models\DateRange();
      $this->ranges[$ii] = $range;
      $this->addModel($range,'d'.$ii);
    }
  }
  //---------------------------------------------------------------------------
  /** @var array*/
  public $ranges;
  //---------------------------------------------------------------------------
}