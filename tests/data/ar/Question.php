<?php
namespace yiiunit\data\ar;

/**
 * Class Question
 *
 * @property integer $id
 * @property string $title
 * @property array $tags
 * @property array $related
 * @property array $points
 *
 * @author Ievgen Sentiabov <ievgen.sentiabov@gmail.com>
 */
class Question extends ActiveRecord
{
    public static function tableName()
    {
        return 'question';
    }
}
