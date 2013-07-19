<?php

namespace app\models;

class Post extends \yii\db\ActiveRecord
{
    /**
	 * The followings are the available columns in table 'tbl_post':
	 * @var integer $id
	 * @var string $title
	 * @var string $content
	 * @var integer $status
	 * @var integer $create_time
	 * @var integer $update_time
	 * @var integer $author_id
	 */
	const STATUS_DRAFT=1;
	const STATUS_PUBLISHED=2;
	const STATUS_ARCHIVED=3;
    
    public function getTags()
    {
        return $this->hasMany('PostTag', array('post_id' => 'id'));
    }
    
    function rules() {
        return array(
            array('title, content, status', 'required'),
			array('status', 'in', 'range'=>array(1,2,3)),
			array('title', 'string', 'max'=>128),
        );
    }
}