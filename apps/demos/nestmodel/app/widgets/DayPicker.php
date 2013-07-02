<?php
namespace app\widgets;
use yii\helpers\Html;

class DayPicker extends ListBoxPicker{
  //---------------------------------------------------------------------------
  public function __construct() {
    parent::__construct(DayData::getListData());
  }
  //---------------------------------------------------------------------------
}

class DayData {
  //---------------------------------------------------------------------------
  public static function Init() {
    self::$_listData = array ();
    for ( $ii = 1; $ii <= 31; $ii++) {
      self::$_listData[$ii] = $ii;
    }
  }
  //---------------------------------------------------------------------------
  public static function getListData() {
    return self::$_listData;
  }
  //---------------------------------------------------------------------------
  private static $_listData;
  //---------------------------------------------------------------------------
}
DayData::Init();
