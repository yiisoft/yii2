{use class='yii\helpers\Html'}
{use class='yii\bootstrap\Nav' type='function'}
{use class='yii\bootstrap\NavBar' type='block'}

{NavBar brandLabel=$app->name brandUrl=$app->homeUrl
 options=['class' => 'test']}

    {Nav options=['class' => 'test2'] items=[
        ['label' => 'Home', 'url' => 'http://example.com/']
    ]}

{/NavBar}
