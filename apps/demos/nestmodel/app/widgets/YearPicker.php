<?php
namespace app\widgets;
use yii\helpers\Html;
class YearPicker extends ListBoxPicker{
  //---------------------------------------------------------------------------
  public function __construct() {
    parent::__construct(YearData::getListData());
  }
  //---------------------------------------------------------------------------
}
class YearData {
  //---------------------------------------------------------------------------
  public static function Init() {
    self::$_listData = array ();
    for ( $ii = 2001; $ii <= 2020; $ii++) {
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
YearData::Init();