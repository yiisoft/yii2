<?php

namespace app\models;

class PostTag extends \yii\db\ActiveRecord
{
    /**
	 * The followings are the available columns in table 'tbl_post_tag':
	 * @var integer $id
     * @var integer $post_id
	 * @var string $name
	 */

    function rules() {
        return array(
            //array('post_id, name', 'required'),
			array('name', 'string', 'max'=>32),
        );
    }
    
    public function scenarios(){
        return array(
            'update' => array('name'),
            'create' => array('name'),
        );
    }
}