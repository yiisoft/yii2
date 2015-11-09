<?php
namespace yiiunit\data\base;
use yii\base\Model;
/**
 * Person
 */
class PersonModel extends Model
{
    const GENDER_FEMENINE = F;
    const GENDER_MASCULINE = M;
  
    const EYECOLOR_BLACK = 'black';
    const EYECOLOR_BLUE = 'blue';
    const EYECOLOR_GREEN = 'green';

    const BODYTYPE_PETITE = 'petite';
    const BODYTYPE_AVERAGE = 'average';
    const BODYTYPE_TALL = 'tall';

    public $firstName;
    public $lastName;
  
    public $gender_index;
    public $eyecolor_index;
  
    // in centimeters
    public $height;
  
    public function catalogues()
    {
        return [
            'gender_index' => [
                self::GENDER_FEMENINE => Yii::t('yii', 'Femenine'),
                self::GENDER_MASCULINE => Yii::t('yii', 'Masculine'),
            ],
            'eyecolor_index' => [
                self::EYECOLOR_BLACK => '#000000',
                self::EYECOLOR_BLUE => '#0000FF',
                self::EYECOLOR_GREEN => '#00FF00',
            ],
            'bodyTypeIndex' => [
                self::BODYTYPE_PETITE => Yii::t('yii', 'Petite'),
                self::BODYTYPE_AVERAGE => Yii::t('yii', 'Average'),
                self::BODYTYPE_TALL => Yii::t('yii', 'Tall'),
            ],
        ];
    }
    
    public function getBodyTypeIndex()
    {
        if ($this->height < 155) {
            return self::BODYTYPE_PETITE;
        } elseif($this->height < 175) {
            return self::BODTYPE_AVERAGE;
        } else {
            return self::BODYTYPE_TAL;
        }
    }
    
    public function getGender()
    {
        return $this->getAttributeTerminology('gender_index');
    }
    
    public function getEyeColor()
    {
        return $this->getAttributeTerminology('eyecolor_index');
    }
    
    public function getBodyType()
    {
        return $this->getAttributeTerminology('bodyTypeIndex');
    }
    
    public function rules()
    {
        return [
            [
                ['gender_index'],
                'in',
                'range' => array_keys(self::getCatalogue('gender_index'))
            ],
            [
                ['eyecolor_index'],
                'in',
                'range' => array_keys(self::getCatalogue('eyecolor_index'))
            ],
        ];
    }
}
