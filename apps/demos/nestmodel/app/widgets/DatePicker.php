<?php
namespace app\widgets;

class DatePicker {
  //---------------------------------------------------------------------------
  public function __construct() {
    $this->winDay = new DayPicker();
    $this->winMonth = new MonthPicker();
    $this->winYear = new YearPicker();
  }
  //---------------------------------------------------------------------------
  public function attach($model,$attribute = null) {
    if ( $attribute !== null ) {
      $model = $model->$attribute;
      $model->modelId = $attribute;
    }
    $this->winDay->attach($model,'day');
    $this->winMonth->attach($model,'month');
    $this->winYear->attach($model,'year');
    return true;

  }
  //---------------------------------------------------------------------------
  function run() {
    $this->winDay->run();
    $this->winMonth->run();
    $this->winYear->run();
  }
  //---------------------------------------------------------------------------
  private $winDay;
  private $winMonth;
  private $winYear;
}