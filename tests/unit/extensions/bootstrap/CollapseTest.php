<?php
namespace yiiunit\extensions\bootstrap;

use yii\bootstrap\Collapse;

/**
 * @group bootstrap
 */
class CollapseTest extends BootstrapTestCase
{
    public function testRender()
    {
        Collapse::$counter = 0;
        $output = Collapse::widget([
            'items' => [
                [
                    'label' => 'Collapsible Group Item #1',
                    'content' => 'test content1',
                ],
                [
                    'label' => '<h1>Collapsible Group Item #2</h1>',
                    'content' => '<h2>test content2</h2>',
                    'contentOptions' => [
                        'class' => 'testContentOptions2'
                    ],
                    'options' => [
                        'class' => 'testClass2',
                        'id' => 'testId2'
                    ],
                    'encode' => true
                ],
                [
                    'label' => '<h1>Collapsible Group Item #3</h1>',
                    'content' => '<h2>test content3</h2>',
                    'contentOptions' => [
                        'class' => 'testContentOptions3'
                    ],
                    'options' => [
                        'class' => 'testClass3',
                        'id' => 'testId3'
                    ],
                    'encode' => false
                ],
                [
                    'label' => '<h1>Collapsible Group Item #4</h1>',
                    'content' => '<h1>test content4</h1>',
                ],
            ]
        ]);

        $this->assertEqualsWithoutLE(<<<HTML
<div id="w0" class="panel-group">
<div class="panel panel-default"><div class="panel-heading"><h4 class="panel-title"><a class="collapse-toggle" href="#w0-collapse1" data-toggle="collapse" data-parent="#w0">Collapsible Group Item #1</a>
</h4></div>
<div id="w0-collapse1" class="panel-collapse collapse"><div class="panel-body">test content1</div>
</div></div>
<div id="testId2" class="testClass2 panel panel-default"><div class="panel-heading"><h4 class="panel-title"><a class="collapse-toggle" href="#w0-collapse2" data-toggle="collapse" data-parent="#w0">&lt;h1&gt;Collapsible Group Item #2&lt;/h1&gt;</a>
</h4></div>
<div id="w0-collapse2" class="testContentOptions2 panel-collapse collapse"><div class="panel-body"><h2>test content2</h2></div>
</div></div>
<div id="testId3" class="testClass3 panel panel-default"><div class="panel-heading"><h4 class="panel-title"><a class="collapse-toggle" href="#w0-collapse3" data-toggle="collapse" data-parent="#w0"><h1>Collapsible Group Item #3</h1></a>
</h4></div>
<div id="w0-collapse3" class="testContentOptions3 panel-collapse collapse"><div class="panel-body"><h2>test content3</h2></div>
</div></div>
<div class="panel panel-default"><div class="panel-heading"><h4 class="panel-title"><a class="collapse-toggle" href="#w0-collapse4" data-toggle="collapse" data-parent="#w0">&lt;h1&gt;Collapsible Group Item #4&lt;/h1&gt;</a>
</h4></div>
<div id="w0-collapse4" class="panel-collapse collapse"><div class="panel-body"><h1>test content4</h1></div>
</div></div>
</div>

HTML
        , $output);
    }
}
