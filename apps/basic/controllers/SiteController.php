<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use app\models\LoginForm;
use app\models\ContactForm;
use app\models\Post;
use app\models\PostTag;
use yii\helpers\Html;

class SiteController extends Controller
{
	public function actions()
	{
		return array(
			'captcha' => array(
				'class' => 'yii\web\CaptchaAction',
				'fixedVerifyCode' => YII_ENV === 'test' ? 'testme' : null,
			),
		);
	}

	public function actionIndex()
	{
		return $this->render('index');
	}

	public function actionLogin()
	{
		$model = new LoginForm();
		if ($model->load($_POST) && $model->login()) {
			return $this->redirect(array('site/index'));
		} else {
			return $this->render('login', array(
				'model' => $model,
			));
		}
	}

	public function actionLogout()
	{
		Yii::$app->user->logout();
		return $this->redirect(array('site/index'));
	}

	public function actionContact()
	{
		$model = new ContactForm;
		if ($model->load($_POST) && $model->contact(Yii::$app->params['adminEmail'])) {
			Yii::$app->session->setFlash('contactFormSubmitted');
			return $this->refresh();
		} else {
			return $this->render('contact', array(
				'model' => $model,
			));
		}
	}

	public function actionAbout()
	{
		return $this->render('about');
	}
    
    public function actionPost($id = null, $deleteTag = null){
        if(!empty($deleteTag)){
            PostTag::find($deleteTag)->delete();
            return $this->redirect(array('site/post', 'id' => $id,));
        }
        if(!empty($id)){
            $model = Post::find($id);
        }
        if(empty($model)){
            $model = new Post;
        }
       
        //--- Update
        if ($model->load($_POST) && $model->save()) {
            $populatedTags = PostTag::populate($_POST, array(
                'scenario' => 'create',
                'post_id' => $model->id,
            ), 'update');
            echo '<h2>';
            if($_POST['submit']=='validate'){
                $status = PostTag::validateMultiple($populatedTags);
                echo 'Validation - ';
            }elseif($_POST['submit']=='save'){
                $status = PostTag::saveMultiple($populatedTags);
                echo 'Saving - ';
            }
            echo $status ? 'success' : 'failed','</h2>';
        }
        
        //--- Display Posts list
        /* @var $posts Post[] */
        $posts = Post::find()->all();
        foreach ($posts as $post){
            echo Html::a($post->title, array('site/post', 'id' => $post->id,)), '<br>';
        }
        
        //--- Display Post form
        echo Html::tag('h1', $model->isNewRecord ? 'Create new post' : 'Update post '.$model->title);
        
        $form = \yii\widgets\ActiveForm::begin(array(
            'options' => array('class' => 'form-horizontal', 'id' => 'post-form'),
            'fieldConfig' => array('inputOptions' => array('class' => 'input-xlarge')),
        ));
        echo $form->field($model, 'title')->textInput();
        echo $form->field($model, 'content')->textArea(array('rows' => 5, 'cols' => 60));
        
        //--- Display tags
        echo Html::tag('h4', 'Tags');
        if(!$model->isNewRecord || !empty($populatedTags)){
            $tags = empty($populatedTags) ? $model->tags : $populatedTags;
            $index = 0;
            foreach ($tags as $tag){
                echo '<div>';
                $arr = '['.$index++.']';
                echo $form->field($tag, $arr.'name')->textInput();
                if($tag->id){
                    echo $form->field($tag, $arr.'id', array('template' => "{input}"))->hiddenInput();
                    echo Html::a('[x]', array('site/post', 'id' => $post->id, 'deleteTag' => $tag->id,));
                }else{
                    echo Html::a('[x]', '#', array('onClick' => 'return removeTag(event) && false;',));
                }
                echo '</div>';
            }
        }
        echo Html::tag('div', '', array('id' => 'newTags',));
        echo Html::a('Add tag', '#', array('onClick' => 'addTag(); return false',)), '<br>';
        
        echo Html::submitButton('Validate', array('class' => 'btn btn-primary', 'name' => 'submit', 'value' => 'validate',));
        echo Html::submitButton('Save', array('class' => 'btn btn-primary', 'name' => 'submit', 'value' => 'save',));
        \yii\widgets\ActiveForm::end();

        $tagModel = new PostTag;
        $emptyTag = Html::tag('div',
                $form->field($tagModel, '[]name')->textInput().
                Html::a('[x]', '#', array('onClick' => 'return removeTag(event) && false;',))
        );
        echo Html::tag('div',$emptyTag, array('id' => 'blankTag', 'style' => 'display:none',));
        echo <<<'JS'
<script type="text/javascript">
function addTag(){
    var tagField = document.getElementById('blankTag').firstChild.cloneNode(true);
    var container = document.getElementById('newTags');
    container.appendChild(tagField);
}
function removeTag(e){
    var target = (e.target) ? e.target : e.srcElement;
    var container = target.parentNode;
    container.parentNode.removeChild(container);
    return false;
}
</script>
JS;
    } 
}
