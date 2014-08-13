{use class='yii\widgets\ActiveForm' type='block'}
{ActiveForm assign='form' id='login-form' action='/form-handler' options=['class' => 'form-horizontal']}
    {$form->field($model, 'firstName')}
    <div class="form-group">
        <div class="col-lg-offset-1 col-lg-11">
            <input type="submit" value="Login" class="btn btn-primary" />
        </div>
    </div>
{/ActiveForm}