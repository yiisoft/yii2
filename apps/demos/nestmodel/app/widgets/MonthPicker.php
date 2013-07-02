<?php
namespace app\widgets;
use yii\helpers\Html;

class MonthPicker extends ListBoxPicker {
  //---------------------------------------------------------------------------
  public function __construct() {
    parent::__construct(MonthData::getListData());
  }
}

class MonthData {
  //---------------------------------------------------------------------------
  public static function Init() {
    self::$_listData = array (
      1  => "Jan",
      2  => "Feb",
      3  => "Mar",
      4  => "Apr",
      5  => "May",
      6  => "Jun",
      7  => "Jul",
      8  => "Aug",
      9  => "Sep",
      10 => "Oct",
      11 => "Nov",
      12 => "Dec"
    );
  }
  //---------------------------------------------------------------------------
  public static function getListData() {
    return self::$_listData;
  }
  //---------------------------------------------------------------------------
  private static $_listData;
  //---------------------------------------------------------------------------
}
MonthData::Init();