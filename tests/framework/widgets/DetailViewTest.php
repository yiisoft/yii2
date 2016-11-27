<?php
/**
 * @author Bennet Klarhoelter <boehsermoe@me.com>
 */

namespace yiiunit\framework\widgets;

use yii\base\Arrayable;
use yii\base\ArrayableTrait;
use yii\base\Object;
use yii\widgets\DetailView;

/**
 * @group widgets
 */
class DetailViewTest extends \yiiunit\TestCase
{
    /** @var DetailView */
    public $detailView;

    protected function setUp()
    {
        parent::setUp();

        $this->mockWebApplication();
    }

    public function testAttributeValue()
    {
        $model = new ObjectMock();
        $model->id = 'id';

        $this->detailView = new PublicDetailView([
            'model' => $model,
            'template' => '{label}:{value}',
            'attributes' => [
                'id',
                [
                    'attribute' => 'id',
                    'value' => 1,
                ],
                [
                    'attribute' => 'id',
                    'value' => '1',
                ],
                [
                    'attribute' => 'id',
                    'value' => $model->getDisplayedId(),
                ],
                [
                    'attribute' => 'id',
                    'value' => function ($model) {
                        return $model->getDisplayedId();
                    },
                ],
            ],
        ]);

        $this->assertEquals('Id:id', $this->detailView->renderAttribute($this->detailView->attributes[0], 0));
        $this->assertEquals('Id:1', $this->detailView->renderAttribute($this->detailView->attributes[1], 1));
        $this->assertEquals('Id:1', $this->detailView->renderAttribute($this->detailView->attributes[2], 2));
        $this->assertEquals('Id:Displayed id', $this->detailView->renderAttribute($this->detailView->attributes[3], 3));
        $this->assertEquals('Id:Displayed id', $this->detailView->renderAttribute($this->detailView->attributes[4], 4));
        $this->assertEquals(2, $model->getDisplayedIdCallCount());
    }

    public function testAttributeVisible()
    {
        $model = new ObjectMock();
        $model->id = 'id';

        $this->detailView = new PublicDetailView([
            'model' => $model,
            'template' => '{label}:{value}',
            'attributes' => [
                [
                    'attribute' => 'id',
                    'value' => $model->getDisplayedId(),
                ],
                [
                    'attribute' => 'id',
                    'value' => $model->getDisplayedId(),
                    'visible' => false,
                ],
                [
                    'attribute' => 'id',
                    'value' => $model->getDisplayedId(),
                    'visible' => true,
                ],
                [
                    'attribute' => 'id',
                    'value' => function ($model) {
                        return $model->getDisplayedId();
                    },
                ],
                [
                    'attribute' => 'id',
                    'value' => function ($model) {
                        return $model->getDisplayedId();
                    },
                    'visible' => false,
                ],
                [
                    'attribute' => 'id',
                    'value' => function ($model) {
                        return $model->getDisplayedId();
                    },
                    'visible' => true,
                ],
            ],
        ]);

        $this->assertEquals([
            0 => [
                'attribute' => 'id',
                'format' => 'text',
                'label' => 'Id',
                'value' => 'Displayed id',
            ],
            2 => [
                'attribute' => 'id',
                'format' => 'text',
                'label' => 'Id',
                'value' => 'Displayed id',
                'visible' => true,
            ],
            3 => [
                'attribute' => 'id',
                'format' => 'text',
                'label' => 'Id',
                'value' => 'Displayed id',
            ],
            5 => [
                'attribute' => 'id',
                'format' => 'text',
                'label' => 'Id',
                'value' => 'Displayed id',
                'visible' => true,
            ]
        ], $this->detailView->attributes);
        $this->assertEquals(5, $model->getDisplayedIdCallCount());
    }

    public function testRelationAttribute()
    {
        $model = new ObjectMock();
        $model->id = 'model';
        $model->related = new ObjectMock();
        $model->related->id = 'related';

        $this->detailView = new PublicDetailView([
            'model' => $model,
            'template' => '{label}:{value}',
            'attributes' => [
                'id',
                'related.id',
            ],
        ]);

        $this->assertEquals('Id:model', $this->detailView->renderAttribute($this->detailView->attributes[0], 0));
        $this->assertEquals('Related Id:related', $this->detailView->renderAttribute($this->detailView->attributes[1], 1));

        // test null relation
        $model->related = null;

        $this->detailView = new PublicDetailView([
            'model' => $model,
            'template' => '{label}:{value}',
            'attributes' => [
                'id',
                'related.id',
            ],
        ]);

        $this->assertEquals('Id:model', $this->detailView->renderAttribute($this->detailView->attributes[0], 0));
        $this->assertEquals('Related Id:<span class="not-set">(not set)</span>', $this->detailView->renderAttribute($this->detailView->attributes[1], 1));
    }

    public function testArrayableModel()
    {
        $expectedValue = [
            [
                'attribute' => 'id',
                'format' => 'text',
                'label' => 'Id',
                'value' => 1
            ],
            [
                'attribute' => 'text',
                'format' => 'text',
                'label' => 'Text',
                'value' => 'I`m arrayable'
            ],
        ];

        $model = new ArrayableMock();
        $model->id = 1;
        $model->text = 'I`m arrayable';

        $this->detailView = new DetailView([
            'model' => $model,
        ]);

        $this->assertEquals($expectedValue, $this->detailView->attributes);
    }

    public function testObjectModel()
    {
        $expectedValue = [
            [
                'attribute' => 'id',
                'format' => 'text',
                'label' => 'Id',
                'value' => 1
            ],
            [
                'attribute' => 'text',
                'format' => 'text',
                'label' => 'Text',
                'value' => 'I`m an object'
            ],
        ];

        $model = new ObjectMock();
        $model->id = 1;
        $model->text = 'I`m an object';

        $this->detailView = new DetailView([
            'model' => $model,
        ]);

        $this->assertEquals($expectedValue, $this->detailView->attributes);
    }

    public function testArrayModel()
    {
        $expectedValue = [
            [
                'attribute' => 'id',
                'format' => 'text',
                'label' => 'Id',
                'value' => 1
            ],
            [
                'attribute' => 'text',
                'format' => 'text',
                'label' => 'Text',
                'value' => 'I`m an array'
            ],
        ];

        $model = [
            'id' => 1,
            'text' => 'I`m an array'
        ];

        $this->detailView = new DetailView([
            'model' => $model,
        ]);

        $this->assertEquals($expectedValue, $this->detailView->attributes);
    }

    public function testOptionsTags()
    {
        $expectedValue = '<tr><th tooltip="Tooltip">Text</th><td class="bg-red">I`m an array</td></tr>';

        $this->detailView = new PublicDetailView([
            'model' => [
                'text' => 'I`m an array',
            ],
            'attributes' => [
                [
                    'attribute' => 'text',
                    'label' => 'Text',
                    'contentOptions' => ['class' => 'bg-red'],
                    'captionOptions' => ['tooltip' => 'Tooltip'],
                ],
            ],
        ]);

        foreach ($this->detailView->attributes as $index=>$attribute) {
            $a = $this->detailView->renderAttribute($attribute, $index);
            $this->assertEquals($expectedValue, $a);
        }
    }
}

/**
 * Helper Class
 */
class ArrayableMock implements Arrayable
{
    use ArrayableTrait;

    public $id;

    public $text;
}

/**
 * Helper Class
 */
class ObjectMock extends Object
{
    public $id;
    public $text;

    private $_related;
    private $_displayedIdCallCount = 0;

    public function getRelated()
    {
        return $this->_related;
    }

    public function setRelated($related)
    {
        $this->_related = $related;
    }

    public function getDisplayedId()
    {
        $this->_displayedIdCallCount++;

        return "Displayed $this->id";
    }

    public function getDisplayedIdCallCount()
    {
        return $this->_displayedIdCallCount;
    }
}

class PublicDetailView extends DetailView
{
    public function renderAttribute($attribute, $index)
    {
        return parent::renderAttribute($attribute, $index);
    }
}
