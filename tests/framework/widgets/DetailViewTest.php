<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\widgets;

use yiiunit\TestCase;
use yii\base\Arrayable;
use yii\base\ArrayableTrait;
use yii\base\Model;
use yii\widgets\DetailView;

/**
 * @group widgets
 */
class DetailViewTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->mockWebApplication();
    }

    public function testAttributeValue(): void
    {
        $model = new ModelMock();
        $model->id = 'id';

        $detailView = new PublicDetailView([
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

        $this->assertEquals('Id:id', $detailView->renderAttribute($detailView->attributes[0], 0));
        $this->assertEquals('Id:1', $detailView->renderAttribute($detailView->attributes[1], 1));
        $this->assertEquals('Id:1', $detailView->renderAttribute($detailView->attributes[2], 2));
        $this->assertEquals('Id:Displayed id', $detailView->renderAttribute($detailView->attributes[3], 3));
        $this->assertEquals('Id:Displayed id', $detailView->renderAttribute($detailView->attributes[4], 4));
        $this->assertEquals(2, $model->getDisplayedIdCallCount());
    }

    /**
     * @see https://github.com/yiisoft/yii2/issues/13243
     */
    public function testUnicodeAttributeNames(): void
    {
        $model = new UnicodeAttributesModelMock();
        $model->ИдентификаторТовара = 'A00001';
        $model->το_αναγνωριστικό_του = 'A00002';

        $detailView = new PublicDetailView([
            'model' => $model,
            'template' => '{label}:{value}',
            'attributes' => [
                'ИдентификаторТовара',
                'το_αναγνωριστικό_του',
            ],
        ]);

        $this->assertEquals(
            'Идентификатор Товара:A00001',
            $detailView->renderAttribute($detailView->attributes[0], 0)
        );
        $this->assertEquals(
            'Το Αναγνωριστικό Του:A00002',
            $detailView->renderAttribute($detailView->attributes[1], 1)
        );
    }

    public function testAttributeVisible(): void
    {
        $model = new ModelMock();
        $model->id = 'id';

        $detailView = new PublicDetailView([
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
            ],
        ], $detailView->attributes);
        $this->assertEquals(5, $model->getDisplayedIdCallCount());
    }

    public function testRelationAttribute(): void
    {
        $model = new ModelMock();
        $model->id = 'model';
        $model->related = new ModelMock();
        $model->related->id = 'related';

        $detailView = new PublicDetailView([
            'model' => $model,
            'template' => '{label}:{value}',
            'attributes' => [
                'id',
                'related.id',
            ],
        ]);

        $this->assertEquals('Id:model', $detailView->renderAttribute($detailView->attributes[0], 0));
        $this->assertEquals('Related Id:related', $detailView->renderAttribute($detailView->attributes[1], 1));

        // test null relation
        $model->related = null;

        $detailView = new PublicDetailView([
            'model' => $model,
            'template' => '{label}:{value}',
            'attributes' => [
                'id',
                'related.id',
            ],
        ]);

        $this->assertEquals('Id:model', $detailView->renderAttribute($detailView->attributes[0], 0));
        $this->assertEquals('Related Id:<span class="not-set">(not set)</span>', $detailView->renderAttribute($detailView->attributes[1], 1));
    }

    public function testArrayableModel(): void
    {
        $expectedValue = [
            [
                'attribute' => 'id',
                'format' => 'text',
                'label' => 'Id',
                'value' => 1,
            ],
            [
                'attribute' => 'text',
                'format' => 'text',
                'label' => 'Text',
                'value' => 'I`m arrayable',
            ],
        ];

        $model = new ArrayableMock();
        $model->id = 1;
        $model->text = 'I`m arrayable';

        $detailView = new DetailView([
            'model' => $model,
        ]);

        $this->assertEquals($expectedValue, $detailView->attributes);
    }

    public function testObjectModel(): void
    {
        $expectedValue = [
            [
                'attribute' => 'id',
                'format' => 'text',
                'label' => 'Id',
                'value' => 1,
            ],
            [
                'attribute' => 'text',
                'format' => 'text',
                'label' => 'Text',
                'value' => 'I`m an object',
            ],
        ];

        $model = new ModelMock();
        $model->id = 1;
        $model->text = 'I`m an object';

        $detailView = new DetailView([
            'model' => $model,
        ]);

        $this->assertEquals($expectedValue, $detailView->attributes);
    }

    public function testArrayModel(): void
    {
        $expectedValue = [
            [
                'attribute' => 'id',
                'format' => 'text',
                'label' => 'Id',
                'value' => 1,
            ],
            [
                'attribute' => 'text',
                'format' => 'text',
                'label' => 'Text',
                'value' => 'I`m an array',
            ],
        ];

        $model = [
            'id' => 1,
            'text' => 'I`m an array',
        ];

        $detailView = new DetailView([
            'model' => $model,
        ]);

        $this->assertEquals($expectedValue, $detailView->attributes);
    }

    public function testOptionsTags(): void
    {
        $expectedValue = '<tr><th tooltip="Tooltip">Text</th><td class="bg-red">I`m an array</td></tr>';

        $detailView = new PublicDetailView([
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

        foreach ($detailView->attributes as $index => $attribute) {
            $renderingResult = $detailView->renderAttribute($attribute, $index);
            $this->assertEquals($expectedValue, $renderingResult);
        }
    }

    /**
     * @see https://github.com/yiisoft/yii2/issues/15536
     */
    public function testShouldTriggerInitEvent(): void
    {
        $initTriggered = false;
        $model = new ModelMock();
        $model->id = 1;
        $model->text = 'I`m an object';

        $detailView = new DetailView([
            'model' => $model,
            'on init' => function () use (&$initTriggered) {
                $initTriggered = true;
            }
        ]);

        $this->assertTrue($initTriggered);
    }
}

/**
 * Helper Class.
 */
class ArrayableMock implements Arrayable
{
    use ArrayableTrait;

    public $id;

    public $text;
}

/**
 * Helper Class.
 *
 * @property mixed $related
 */
class ModelMock extends Model
{
    public $id;
    public $text;

    private $_related;
    private $_displayedIdCallCount = 0;

    public function getRelated()
    {
        return $this->_related;
    }

    public function setRelated($related): void
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

/**
 * Used for testing attributes containing non-English characters.
 */
class UnicodeAttributesModelMock extends Model
{
    /**
     * Product's ID (Russian).
     * @var mixed
     */
    public $ИдентификаторТовара;
    /**
     * ID (Greek).
     * @var mixed
     */
    public $το_αναγνωριστικό_του;
}

class PublicDetailView extends DetailView
{
    public function renderAttribute($attribute, $index)
    {
        return parent::renderAttribute($attribute, $index);
    }
}
