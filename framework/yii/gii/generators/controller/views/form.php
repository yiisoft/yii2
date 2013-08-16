<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/**
 * @var yii\base\View $this
 * @var yii\widgets\ActiveForm $form
 * @var yii\gii\generators\controller\Generator $generator
 */
?>
<?php echo $form->field($generator, 'controller')->hint('
	Controller ID is case-sensitive and can contain module ID(s). For example:
	<ul>
		<li><code>order</code> generates <code>OrderController.php</code></li>
		<li><code>order-item</code> generates <code>OrderItemController.php</code></li>
		<li><code>admin/user</code> generates <code>UserController.php</code> within the <code>admin</code> module.</li>
	</ul>
'); ?>
<?php echo $form->field($generator, 'baseClass')->hint('
	This is the class that the new controller class will extend from.
	Please make sure the class exists and can be autoloaded.
'); ?>
<?php echo $form->field($generator, 'actions')->hint('
	Provide one or multiple action IDs to generate empty action method(s) in the controller.
	Separate multiple action IDs with commas or spaces.
'); ?>
