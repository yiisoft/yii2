<?php
namespace app\widgets;
use app\widgets\DatePicker;


class DateRangePicker {
  //---------------------------------------------------------------------------
  public function __construct() {
    $this->winDate1 = new DatePicker();
    $this->winDate2 = new DatePicker();
  }
  //---------------------------------------------------------------------------
  public function attach($model,$attribute=null) {
    if ( $attribute !== null ) {
      $model = $model->$attribute;
      $model->modelId = $attribute;
    }
    $this->winDate1->attach($model->date1);
    $this->winDate2->attach($model->date2);
  }
  //---------------------------------------------------------------------------
  public function run() {
    echo 'Start : ';
    $this->winDate1->run();
    echo '&nbsp;&nbsp;&nbsp; End : ';
    $this->winDate2->run();
  }
  //---------------------------------------------------------------------------
  private $winDate1;
  private $winDate2;
}
