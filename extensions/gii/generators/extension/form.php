<?php
/**
 * @var yii\web\View $this
 * @var yii\widgets\ActiveForm $form
 * @var yii\gii\generators\module\Generator $generator
 */
?>
<div class="module-form">
<?php
	echo $form->field($generator, 'vendorName');
	echo $form->field($generator, 'packageName');
	echo $form->field($generator, 'namespace');
	echo $form->field($generator, 'type')->dropDownList($generator->optsType());
	echo $form->field($generator, 'keywords');
	echo $form->field($generator, 'license')->dropDownList($generator->optsLicense());
	echo $form->field($generator, 'title');
	echo $form->field($generator, 'description');
	echo $form->field($generator, 'authorName');
	echo $form->field($generator, 'authorEmail');
	echo $form->field($generator, 'outputPath');
?>
</div>

<?php
$js = <<< EOS
	$('#generator-packagename').keyup(function(){
		$('#generator-namespace').val($('#generator-vendorname').val()+'\\\'+$('#generator-packagename').val());
	});
EOS;
$this->registerJs($js);
