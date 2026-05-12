<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\base;

use PHPUnit\Framework\Attributes\Group;
use yii\base\DynamicModel;
use yii\base\InvalidArgumentException;
use yii\base\InvalidConfigException;
use yii\base\Model;
use yiiunit\data\base\BeforeValidateFailsModel;
use yiiunit\data\base\ComplexModel1;
use yiiunit\data\base\ComplexModel2;
use yiiunit\data\base\CustomScenariosModel;
use yiiunit\data\base\HintModel;
use yiiunit\data\base\InvalidRulesModel;
use yiiunit\data\base\RulesModel;
use yiiunit\data\base\Singer;
use yiiunit\data\base\Speaker;
use yiiunit\data\base\ValidatorInstanceRulesModel;
use yiiunit\data\base\WriteOnlyModel;
use yiiunit\TestCase;

/**
 * Unit tests for the {@see Model} class.
 *
 * {@see HintModel} for hint test helper.
 * {@see BeforeValidateFailsModel} for beforeValidate failure test helper.
 * {@see ValidatorInstanceRulesModel} for validator instance rules test helper.
 */
#[Group('base')]
final class ModelTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->mockApplication();
    }

    public function testGetAttributeLabel(): void
    {
        $speaker = new Speaker();

        self::assertSame(
            'First Name',
            $speaker->getAttributeLabel('firstName'),
            "Should be 'First Name'.",
        );
        self::assertSame(
            'This is the custom label',
            $speaker->getAttributeLabel('customLabel'),
            'Should return the custom label string.',
        );
        self::assertSame(
            'Underscore Style',
            $speaker->getAttributeLabel('underscore_style'),
            "Should be auto-generated as 'Underscore Style'.",
        );
    }

    public function testGetAttributes(): void
    {
        $speaker = new Speaker();

        $speaker->firstName = 'Qiang';
        $speaker->lastName = 'Xue';

        self::assertSame(
            [
                'firstName' => 'Qiang',
                'lastName' => 'Xue',
                'customLabel' => null,
                'underscore_style' => null,
            ],
            $speaker->getAttributes(),
            'Should return all attributes when no filter is applied.',
        );

        self::assertSame(
            [
                'firstName' => 'Qiang',
                'lastName' => 'Xue',
            ],
            $speaker->getAttributes(
                [
                    'firstName',
                    'lastName',
                ],
            ),
            'Should be returned when requested explicitly.',
        );
        self::assertSame(
            [
                'firstName' => 'Qiang',
                'lastName' => 'Xue',
            ],
            $speaker->getAttributes(
                null,
                [
                    'customLabel',
                    'underscore_style',
                ],
            ),
            'Should not appear in result.'
        );

        self::assertSame(
            [
                'firstName' => 'Qiang',
            ],
            $speaker->getAttributes(
                [
                    'firstName',
                    'lastName',
                ],
                [
                    'lastName',
                    'customLabel',
                    'underscore_style',
                ],
            ),
            'Should return only non-excluded requested attributes.'
        );
    }

    public function testSetAttributes(): void
    {
        // by default mass assignment doesn't work at all
        $speaker = new Speaker();

        $speaker->setAttributes(
            [
                'firstName' => 'Qiang',
                'underscore_style' => 'test',
            ],
        );

        self::assertNull(
            $speaker->firstName,
            "Should be 'null' with 'default' scenario (no safe attributes).",
        );
        self::assertNull(
            $speaker->underscore_style,
            "Should be 'null' with 'default' scenario (no safe attributes).",
        );

        // in the test scenario
        $speaker = new Speaker();

        $speaker->setScenario('test');
        $speaker->setAttributes(
            [
                'firstName' => 'Qiang',
                'underscore_style' => 'test',
            ],
        );

        self::assertNull(
            $speaker->underscore_style,
            "Should be 'null' because it is not safe in 'test' scenario.",
        );
        self::assertSame(
            'Qiang',
            $speaker->firstName,
            "Should be set in 'test' scenario."
        );

        $speaker->setAttributes(
            [
                'firstName' => 'Qiang',
                'underscore_style' => 'test',
            ],
            false,
        );

        self::assertSame(
            'test',
            $speaker->underscore_style,
            "Should be set when safeOnly is 'false'.",
        );
        self::assertSame(
            'Qiang',
            $speaker->firstName,
            "Should remain set when safeOnly is 'false'.",
        );
    }

    public function testLoad(): void
    {
        $singer = new Singer();

        self::assertSame(
            'Singer',
            $singer->formName(),
            'Should return class name as formName by default.',
        );

        $post = ['firstName' => 'Qiang'];

        Speaker::$formName = '';

        $model = new Speaker();

        $model->setScenario('test');

        self::assertTrue(
            $model->load($post),
            "Should return 'true' with empty formName and matching data.",
        );
        self::assertSame(
            'Qiang',
            $model->firstName,
            "Should be loaded as 'Qiang' with empty formName.",
        );

        Speaker::$formName = 'Speaker';

        $model = new Speaker();

        $model->setScenario('test');

        self::assertTrue(
            $model->load(['Speaker' => $post]),
            "Should return 'true' when data is nested under formName.",
        );
        self::assertSame(
            'Qiang',
            $model->firstName,
            "Should be loaded as 'Qiang' under 'Speaker' key.",
        );

        Speaker::$formName = 'Speaker';

        $model = new Speaker();

        $model->setScenario('test');

        self::assertFalse(
            $model->load(['Example' => []]),
            "Should return 'false' when formName key does not match.",
        );
        self::assertNull(
            $model->firstName,
            "Should remain 'null' when 'load()' fails.",
        );
    }

    public function testLoadMultipleWithEmptyFormName(): void
    {
        $data = [
            [
                'firstName' => 'Thomas',
                'lastName' => 'Anderson',
            ],
            [
                'firstName' => 'Agent',
                'lastName' => 'Smith',
            ],
        ];

        Speaker::$formName = '';

        $neo = new Speaker();

        $neo->setScenario('test');

        $smith = new Speaker();

        $smith->setScenario('test');

        self::assertTrue(
            Speaker::loadMultiple(
                [
                    $neo,
                    $smith,
                ],
                $data,
            ),
            "Should return 'true' with empty formName.",
        );
        self::assertSame(
            'Thomas',
            $neo->firstName,
            "Should be 'Thomas'.",
        );
        self::assertSame(
            'Smith',
            $smith->lastName,
            "Should be 'Smith' with formName.",
        );
    }

    public function testLoadMultipleWithMatchingFormName(): void
    {
        $data = [
            [
                'firstName' => 'Thomas',
                'lastName' => 'Anderson',
            ],
            [
                'firstName' => 'Agent',
                'lastName' => 'Smith',
            ],
        ];

        Speaker::$formName = 'Speaker';

        $neo = new Speaker();

        $neo->setScenario('test');

        $smith = new Speaker();

        $smith->setScenario('test');

        self::assertTrue(
            Speaker::loadMultiple(
                [
                    $neo,
                    $smith,
                ],
                ['Speaker' => $data],
                'Speaker',
            ),
            "Should return 'true' when formName matches.",
        );
        self::assertSame(
            'Thomas',
            $neo->firstName,
            "Should be 'Thomas' with formName.",
        );
        self::assertSame(
            'Smith',
            $smith->lastName,
            "Should be 'Smith' with formName.",
        );
    }

    public function testLoadMultipleWithMismatchedFormName(): void
    {
        $data = [
            [
                'firstName' => 'Thomas',
                'lastName' => 'Anderson',
            ],
            [
                'firstName' => 'Agent',
                'lastName' => 'Smith',
            ],
        ];

        Speaker::$formName = 'Speaker';

        $neo = new Speaker();

        $neo->setScenario('test');

        $smith = new Speaker();

        $smith->setScenario('test');

        self::assertFalse(
            Speaker::loadMultiple(
                [
                    $neo,
                    $smith,
                ],
                ['Speaker' => $data],
                'Morpheus',
            ),
            "Should return 'false' when formName does not match.",
        );
        self::assertNull(
            $neo->firstName,
            "Should remain 'null' when 'loadMultiple()' fails.",
        );
        self::assertNull(
            $smith->lastName,
            "Should remain 'null' when 'loadMultiple()' fails.",
        );
    }

    public function testActiveAttributes(): void
    {
        // by default mass assignment doesn't work at all
        $speaker = new Speaker();

        self::assertEmpty(
            $speaker->activeAttributes(),
            "Should be empty in 'default' scenario.",
        );

        $speaker = new Speaker();

        $speaker->setScenario('test');

        self::assertSame(
            [
                'firstName',
                'lastName',
                'underscore_style',
            ],
            $speaker->activeAttributes(),
            'Should match test scenario rules.',
        );
    }

    public function testActiveAttributesAreUnique(): void
    {
        // by default mass assignment doesn't work at all
        $speaker = new Speaker();

        self::assertEmpty(
            $speaker->activeAttributes(),
            "Should be empty in 'default' scenario.",
        );

        $speaker = new Speaker();

        $speaker->setScenario('duplicates');

        self::assertSame(
            [
                'firstName',
                'underscore_style',
            ],
            $speaker->activeAttributes(),
            'Should be unique even with duplicate rules.',
        );
    }

    public function testIsAttributeSafe(): void
    {
        // by default mass assignment doesn't work at all
        $speaker = new Speaker();

        self::assertFalse(
            $speaker->isAttributeSafe('firstName'),
            "Should not be safe in 'default' scenario.",
        );

        $speaker = new Speaker();

        $speaker->setScenario('test');

        self::assertTrue(
            $speaker->isAttributeSafe('firstName'),
            "Should be safe in 'test' scenario.",
        );
    }

    /**
     * TODO: review PR https://github.com/yiisoft/yii2/pull/18265
     */
    public function testIsAttributeSafeForNumericAttribute(): void
    {
        $model = new RulesModel();
        $model->rules = [
            [
                ['123456'],
                'safe',
            ]
        ];

        self::assertFalse(
            $model->isAttributeSafe('123456'),
            'Numeric string attributes should not be recognized as safe.',
        );
    }

    public function testSafeScenariosWithRequiredRule(): void
    {
        $model = new RulesModel();

        $model->rules = [
            [
                [
                    'account_id',
                    'user_id'
                ],
                'required',
            ],
        ];

        $model->scenario = Model::SCENARIO_DEFAULT;

        self::assertSame(
            [
                'account_id',
                'user_id',
            ],
            $model->safeAttributes(),
            "Should match required rule in 'default' scenario.",
        );
        self::assertSame(
            [
                'account_id',
                'user_id',
            ],
            $model->activeAttributes(),
            "Should match required rule in 'default' scenario.",
        );

        $model->scenario = 'update'; // not existing scenario

        self::assertEmpty(
            $model->safeAttributes(),
            'Should be empty for non-existing scenario.',
        );
        self::assertEmpty(
            $model->activeAttributes(),
            'Should be empty for non-existing scenario.',
        );
    }

    public function testSafeScenariosWithMultipleRules(): void
    {
        $model = new RulesModel();

        $model->rules = [
            [
                [
                    'account_id',
                    'user_id',
                ],
                'required',
            ],
            [
                ['user_id'],
                'number',
                'on' => [
                    'create',
                    'update',
                ],
            ],
            [
                [
                    'email',
                    'name',
                ],
                'required',
                'on' => 'create',
            ],
        ];

        $model->scenario = Model::SCENARIO_DEFAULT;

        self::assertSame(
            [
                'account_id',
                'user_id',
            ],
            $model->safeAttributes(),
            "Should have 'account_id' and 'user_id' as safe.",
        );
        self::assertSame(
            [
                'account_id',
                'user_id',
            ],
            $model->activeAttributes(),
            "Should have 'account_id' and 'user_id' as active.",
        );

        $model->scenario = 'update';

        self::assertSame(
            [
                'account_id',
                'user_id',
            ],
            $model->safeAttributes(),
            "Should have 'account_id' and 'user_id' as safe.",
        );
        self::assertSame(
            [
                'account_id',
                'user_id',
            ],
            $model->activeAttributes(),
            "Should have 'account_id' and 'user_id' as active.",
        );

        $model->scenario = 'create';

        self::assertSame(
            [
                'account_id',
                'user_id',
                'email',
                'name',
            ],
            $model->safeAttributes(),
            "Should include 'email' and 'name' as safe.",
        );
        self::assertSame(
            [
                'account_id',
                'user_id',
                'email',
                'name',
            ],
            $model->activeAttributes(),
            "Should include 'email' and 'name' as active.",
        );
    }

    public function testSafeScenariosWithExplicitOnRule(): void
    {
        $model = new RulesModel();

        $model->rules = [
            [
                [
                    'account_id',
                    'user_id',
                ],
                'required',
            ],
            [
                ['user_id'],
                'number',
                'on' => [
                    'create',
                    'update',
                ],
            ],
            [
                [
                    'email',
                    'name',
                ],
                'required',
                'on' => 'create',
            ],
            [
                [
                    'email',
                    'name',
                ],
                'required',
                'on' => 'update',
            ],
        ];

        $model->scenario = Model::SCENARIO_DEFAULT;

        self::assertSame(
            [
                'account_id',
                'user_id',
            ],
            $model->safeAttributes(),
            "Should only have 'account_id' and 'user_id' as safe with multi-scenario rules.",
        );
        self::assertSame(
            [
                'account_id',
                'user_id',
            ],
            $model->activeAttributes(),
            "Should only have 'account_id' and 'user_id' as active with multi-scenario rules.",
        );

        $model->scenario = 'update';

        self::assertSame(
            [
                'account_id',
                'user_id',
                'email',
                'name',
            ],
            $model->safeAttributes(),
            "Should include 'email' and 'name' as safe with explicit on rule.",
        );
        self::assertSame(
            [
                'account_id',
                'user_id',
                'email',
                'name',
            ],
            $model->activeAttributes(),
            "Should include 'email' and 'name' as active with explicit on rule.",
        );

        $model->scenario = 'create';

        self::assertSame(
            ['account_id', 'user_id', 'email', 'name'],
            $model->safeAttributes(),
            "Should include 'email' and 'name' as safe with explicit on rule.",
        );
        self::assertSame(
            [
                'account_id',
                'user_id',
                'email',
                'name',
            ],
            $model->activeAttributes(),
            "Should include 'email' and 'name' as active with explicit on rule.",
        );
    }

    public function testUnsafeAttributeMassAssignment(): void
    {
        $model = new RulesModel();

        $model->rules = [
            [
                [
                    'name',
                    '!email',
                ],
                'required',
            ], // Name is safe to set, but email is not. Both are required
        ];

        self::assertSame(
            ['name'],
            $model->safeAttributes(),
            "Should be safe when 'email' is marked unsafe.",
        );
        self::assertSame(
            ['name', 'email'],
            $model->activeAttributes(),
            "Should be active even if 'email' is unsafe.",
        );

        $model->attributes = [
            'name' => 'mdmunir',
            'email' => 'mdm@mun.com',
        ];

        self::assertNull(
            $model->email,
            'Should not be set via mass assignment.',
        );
        self::assertFalse(
            $model->validate(),
            "Should fail because required 'email' is 'null'.",
        );
    }

    public function testUnsafeAttributeWithDefaultValue(): void
    {
        $model = new RulesModel();

        $model->rules = [
            [
                ['name'],
                'required',
            ],
            [
                ['!user_id'],
                'default',
                'value' => '3426',
            ],
        ];
        $model->attributes = [
            'name' => 'mdmunir',
            'user_id' => '62792684',
        ];

        self::assertTrue(
            $model->validate(),
            "Should pass with 'name' set and default value for 'user_id'.",
        );
        self::assertSame(
            '3426',
            $model->user_id,
            'Should get default value instead of mass-assigned value.',
        );
    }

    public function testUnsafeAttributeOverrideSafeAndRequired(): void
    {
        $model = new RulesModel();

        $model->rules = [
            [
                [
                    'name',
                    'email',
                ],
                'required',
            ],
            [
                ['!email'],
                'safe',
            ],
        ];

        self::assertSame(
            ['name'],
            $model->safeAttributes(),
            "Should be safe when 'email' is overridden as unsafe.",
        );

        $model->attributes = [
            'name' => 'mdmunir',
            'email' => 'm2792684@mdm.com',
        ];

        self::assertFalse(
            $model->validate(),
            "Should fail because unsafe 'email' cannot be mass-assigned.",
        );
    }

    public function testUnsafeAttributeScenarioBehavior(): void
    {
        $model = new RulesModel();

        $model->rules = [
            [
                [
                    'name',
                    'email',
                ],
                'required',
            ],
            [
                ['email'],
                'email',
            ],
            [
                ['!email'],
                'safe',
                'on' => 'update',
            ],
        ];

        $model->setScenario(RulesModel::SCENARIO_DEFAULT);

        self::assertSame(
            [
                'name',
                'email',
            ],
            $model->safeAttributes(),
            "Should be safe in 'default' scenario.",
        );

        $model->attributes = [
            'name' => 'mdmunir',
            'email' => 'm2792684@mdm.com',
        ];

        self::assertTrue(
            $model->validate(),
            "Should pass in 'default' scenario with both attributes set.",
        );

        $model->setScenario('update');

        self::assertSame(
            ['name'],
            $model->safeAttributes(),
            'Should be safe in update scenario.',
        );

        $model->attributes = [
            'name' => 'D426',
            'email' => 'd426@mdm.com',
        ];

        self::assertSame(
            'm2792684@mdm.com',
            $model->email,
            'Should retain the existing email in update scenario.',
        );
    }

    public function testErrorsInitialState(): void
    {
        $speaker = new Speaker();

        self::assertEmpty(
            $speaker->getErrors(),
            'Should have no errors.',
        );
        self::assertEmpty(
            $speaker->getErrors('firstName'),
            "Should have no errors for 'firstName'.",
        );
        self::assertEmpty(
            $speaker->getFirstErrors(),
            'Should have no first errors.',
        );
        self::assertFalse(
            $speaker->hasErrors(),
            'Should report no errors.',
        );
        self::assertFalse(
            $speaker->hasErrors('firstName'),
            "Should report no errors for 'firstName'.",
        );
    }

    public function testAddError(): void
    {
        $speaker = new Speaker();

        $speaker->addError('firstName', 'Something is wrong!');

        self::assertSame(
            ['firstName' => ['Something is wrong!']],
            $speaker->getErrors(),
            'Should return added error.',
        );
        self::assertSame(
            ['Something is wrong!'],
            $speaker->getErrors('firstName'),
            "Should return the error 'array'.",
        );
    }

    public function testMultipleErrors(): void
    {
        $speaker = new Speaker();

        $speaker->addError('firstName', 'Something is wrong!');
        $speaker->addError('firstName', 'Totally wrong!');

        self::assertSame(
            [
                'firstName' => [
                    'Something is wrong!',
                    'Totally wrong!',
                ],
            ],
            $speaker->getErrors(),
            "Should return both errors for 'firstName'.",
        );
        self::assertSame(
            ['Something is wrong!', 'Totally wrong!'],
            $speaker->getErrors('firstName'),
            'Should return both errors.',
        );
        self::assertTrue(
            $speaker->hasErrors(),
            "Should return 'true' after 'addError()'.",
        );
        self::assertTrue(
            $speaker->hasErrors('firstName'),
            "Should return 'true'.",
        );
        self::assertFalse(
            $speaker->hasErrors('lastName'),
            "Should return 'false'.",
        );
        self::assertSame(
            ['firstName' => 'Something is wrong!'],
            $speaker->getFirstErrors(),
            'Should return first error per attribute.',
        );
        self::assertSame(
            'Something is wrong!',
            $speaker->getFirstError('firstName'),
            'Should return the first error.',
        );
        self::assertNull(
            $speaker->getFirstError('lastName'),
            "Should return 'null' when no errors.",
        );
    }

    public function testErrorSummary(): void
    {
        $speaker = new Speaker();

        $speaker->addError('firstName', 'Something is wrong!');
        $speaker->addError('firstName', 'Totally wrong!');
        $speaker->addError('lastName', 'Another one!');

        self::assertSame(
            [
                'firstName' => [
                    'Something is wrong!',
                    'Totally wrong!',
                ],
                'lastName' => ['Another one!'],
            ],
            $speaker->getErrors(),
            'Should include errors for both attributes.',
        );
        self::assertSame(
            ['Something is wrong!', 'Totally wrong!', 'Another one!'],
            $speaker->getErrorSummary(true),
            'Should return all errors.',
        );
        self::assertSame(
            ['Something is wrong!', 'Another one!'],
            $speaker->getErrorSummary(false),
            'Should return first error per attribute.',
        );
    }

    public function testClearErrors(): void
    {
        $speaker = new Speaker();

        $speaker->addError('firstName', 'Something is wrong!');
        $speaker->addError('firstName', 'Totally wrong!');
        $speaker->addError('lastName', 'Another one!');

        $speaker->clearErrors('firstName');

        self::assertSame(
            ['lastName' => ['Another one!']],
            $speaker->getErrors(),
            "Should return only 'lastName' errors after clearing 'firstName'.",
        );

        $speaker->clearErrors();

        self::assertEmpty(
            $speaker->getErrors(),
            "Should return an empty 'array' after clearing all errors.",
        );
        self::assertFalse(
            $speaker->hasErrors(),
            "Should return 'false' after clearErrors.",
        );
    }

    public function testAddErrors(): void
    {
        $singer = new Singer();

        $errors = ['firstName' => ['Something is wrong!']];

        $singer->addErrors($errors);

        self::assertSame(
            $singer->getErrors(),
            $errors,
            "Should add a single error 'array'.",
        );

        $singer->clearErrors();

        $singer->addErrors(['firstName' => 'Something is wrong!']);

        self::assertSame(
            $singer->getErrors(),
            ['firstName' => ['Something is wrong!']],
            "Should wrap a string error into an 'array'.",
        );

        $singer->clearErrors();

        $errors = [
            'firstName' => [
                'Something is wrong!',
                'Totally wrong!',
            ],
        ];

        $singer->addErrors($errors);

        self::assertSame(
            $singer->getErrors(),
            $errors,
            'Should add multiple errors for one attribute.',
        );

        $singer->clearErrors();

        $errors = [
            'firstName' => ['Something is wrong!'],
            'lastName' => ['Another one!'],
        ];

        $singer->addErrors($errors);

        self::assertSame(
            $singer->getErrors(),
            $errors,
            'Should add errors for multiple attributes.',
        );

        $singer->clearErrors();

        $errors = [
            'firstName' => [
                'Something is wrong!',
                'Totally wrong!',
            ],
            'lastName' => ['Another one!'],
        ];

        $singer->addErrors($errors);

        self::assertSame(
            $singer->getErrors(),
            $errors,
            'Should handle mixed error counts across attributes.',
        );

        $singer->clearErrors();

        $errors = [
            'firstName' => [
                'Something is wrong!',
                'Totally wrong!',
            ],
            'lastName' => [
                'Another one!',
                'Totally wrong!',
            ],
        ];

        $singer->addErrors($errors);

        self::assertSame(
            $singer->getErrors(),
            $errors,
            'Should add multiple errors for multiple attributes.',
        );
    }

    public function testArraySyntax(): void
    {
        $speaker = new Speaker();

        // get
        self::assertNull(
            $speaker['firstName'],
            "Should return 'null' for unset attribute.",
        );

        // isset
        self::assertFalse(
            isset($speaker['firstName']),
            "Should return 'false' for 'null' attribute.",
        );
        self::assertFalse(
            isset($speaker['unExistingField']),
            "Should return 'false' for non-existing field.",
        );

        // set
        $speaker['firstName'] = 'Qiang';

        self::assertSame(
            'Qiang',
            $speaker['firstName'],
            'Should return the set value.',
        );
        self::assertTrue(
            isset($speaker['firstName']),
            "Should return 'true' after setting attribute.",
        );

        // iteration
        $attributes = [];

        foreach ($speaker as $key => $attribute) {
            $attributes[$key] = $attribute;
        }

        self::assertSame(
            [
                'firstName' => 'Qiang',
                'lastName' => null,
                'customLabel' => null,
                'underscore_style' => null,
            ],
            $attributes,
            'Should yield all attributes.',
        );

        // unset
        unset($speaker['firstName']);

        // exception isn't expected here
        self::assertNull(
            $speaker['firstName'],
            "Should return 'null' after unset.",
        );
        self::assertFalse(
            isset($speaker['firstName']),
            "Should return 'false' after unset.",
        );
    }

    public function testDefaults(): void
    {
        $singer = new Model();

        self::assertEmpty(
            $singer->rules(),
            "Should return an empty 'array' by default.",
        );
        self::assertEmpty(
            $singer->attributeLabels(),
            "Should return an empty 'array' by default.",
        );
    }

    public function testDefaultScenarios(): void
    {
        $singer = new Singer();

        self::assertSame(
            [
                'default' => [
                    'lastName',
                    'underscore_style',
                    'test',
                ],
            ],
            $singer->scenarios(),
            'Singer scenarios should match its validation rules.',
        );

        $scenarios = [
            'default' => [
                'id',
                'name',
                'description',
            ],
            'administration' => [
                'name',
                'description',
                'is_disabled',
            ],
        ];

        $model = new ComplexModel1();

        self::assertSame(
            $scenarios,
            $model->scenarios(),
            'Should be derived from its rules.',
        );

        $scenarios = [
            'default' => [
                'id',
                'name',
                'description',
            ],
            'suddenlyUnexpectedScenario' => [
                'name',
                'description',
            ],
            'administration' => [
                'id',
                'name',
                'description',
                'is_disabled',
            ],
        ];

        $model = new ComplexModel2();

        self::assertSame(
            $scenarios,
            $model->scenarios(),
            'Should merge custom and rule-derived scenarios.',
        );
    }

    public function testValidatorsWithDifferentScenarios(): void
    {
        $model = new CustomScenariosModel();

        self::assertCount(
            3,
            $model->getActiveValidators(),
            "Should have '3' active validators in 'default' scenario.",
        );
        self::assertCount(
            2,
            $model->getActiveValidators('name'),
            "Should have '2' validators in 'default' scenario.",
        );

        $model->setScenario('secondScenario');

        self::assertCount(
            2,
            $model->getActiveValidators(),
            "Should have '2' active validators.",
        );
        self::assertCount(
            2,
            $model->getActiveValidators('id'),
            "Should have '2' validators in 'secondScenario'.",
        );
        self::assertCount(
            0,
            $model->getActiveValidators('name'),
            "Should have no validators in 'secondScenario'.",
        );
    }

    public function testIsAttributeRequired(): void
    {
        $singer = new Singer();

        self::assertFalse(
            $singer->isAttributeRequired('firstName'),
            'Should not be marked as required.',
        );
        self::assertTrue(
            $singer->isAttributeRequired('lastName'),
            'Should be marked as required.',
        );

        // attribute is not marked as required when a conditional validation is applied using `$when`.
        // the condition should not be applied because this info may be retrieved before model is loaded with data
        $singer->firstName = 'qiang';

        self::assertFalse(
            $singer->isAttributeRequired('test'),
            'Should not be marked as required.',
        );

        $singer->firstName = 'cebe';

        self::assertFalse(
            $singer->isAttributeRequired('test'),
            'Should not be marked as required regardless of data.',
        );
    }

    public function testCreateValidators(): void
    {
        self::expectException(
            InvalidConfigException::class,
        );
        self::expectExceptionMessage(
            'Invalid validation rule: a rule must specify both attribute names and validator type.',
        );

        $invalid = new InvalidRulesModel();

        $invalid->createValidators();
    }

    /**
     * Ensure 'safe' validator works for write-only properties.
     * Normal validator can not work here though.
     */
    public function testValidateWriteOnly(): void
    {
        $model = new WriteOnlyModel();

        $model->setAttributes(['password' => 'test'], true);

        self::assertSame(
            'test',
            $model->passwordHash,
            'Should accept mass assignment via safe validator.',
        );
        self::assertTrue(
            $model->validate(),
            'Should pass validation.',
        );
    }

    public function testValidateAttributeNames(): void
    {
        $model = new ComplexModel1();

        $model->name = 'Some value';

        self::assertTrue(
            $model->validate(['name']),
            "Should validate only 'name' attribute",
        );
        self::assertTrue(
            $model->validate('name'),
            "Should validate only 'name' attribute",
        );
        self::assertFalse(
            $model->validate(),
            'Should validate all attributes',
        );
    }

    public function testFormNameWithAnonymousClass(): void
    {
        $model = require __DIR__ . '/stubs/AnonymousModelClass.php';

        self::expectException(
            InvalidConfigException::class,
        );
        self::expectExceptionMessage(
            'The "formName()" method should be explicitly defined for anonymous models',
        );

        $model->formName();
    }

    public function testExcludeEmptyAttributesFromSafe(): void
    {
        $model = new DynamicModel(['' => 'emptyFieldValue']);

        $model->addRule('', 'safe');

        self::assertEmpty(
            $model->safeAttributes(),
            'Should be excluded from safe attributes.',
        );
        self::assertSame(
            [''],
            $model->attributes(),
            'Should still appear in attributes list.',
        );
    }

    public function testAttributeHintsDefault(): void
    {
        $model = new Model();

        self::assertEmpty(
            $model->attributeHints(),
            "Should return an empty 'array'.",
        );
    }

    public function testGetAttributeHint(): void
    {
        $model = new HintModel();
        self::assertSame(
            'Enter your full name',
            $model->getAttributeHint('name'),
            'Should return the configured hint text.',
        );
        self::assertSame(
            '',
            $model->getAttributeHint('nonexistent'),
            "Should return an empty 'string' for nonexistent attribute.",
        );
    }

    public function testValidateReturnsFalseWhenBeforeValidateFails(): void
    {
        $model = new BeforeValidateFailsModel();

        self::assertFalse(
            $model->validate(),
            "Should return 'false' when beforeValidate returns 'false'.",
        );
    }

    public function testValidateWithUnknownScenario(): void
    {
        $model = new CustomScenariosModel();

        $model->setScenario('nonexistent');

        self::expectException(
            InvalidArgumentException::class,
        );
        self::expectExceptionMessage(
            'Unknown scenario: nonexistent',
        );

        $model->validate();
    }

    public function testIsAttributeActive(): void
    {
        $model = new CustomScenariosModel();

        self::assertTrue(
            $model->isAttributeActive('id'),
            "Should be active in 'default' scenario.",
        );
        self::assertTrue(
            $model->isAttributeActive('name'),
            "Should be active in 'default' scenario.",
        );

        $model->setScenario('secondScenario');

        self::assertTrue(
            $model->isAttributeActive('id'),
            "Should be active in 'secondScenario'.",
        );
        self::assertFalse(
            $model->isAttributeActive('name'),
            "Should not be active in 'secondScenario'.",
        );
    }

    public function testValidateMultiple(): void
    {
        $model1 = new ComplexModel1();

        $model1->name = 'Name 1';
        $model1->description = 'Desc 1';
        $model1->id = 1;

        $model2 = new ComplexModel1();

        $model2->name = 'Name 2';
        $model2->description = 'Desc 2';
        $model2->id = 2;

        self::assertTrue(
            Model::validateMultiple([$model1, $model2]),
            'All valid models should pass validateMultiple.',
        );
    }

    public function testValidateMultipleWithInvalid(): void
    {
        $model1 = new ComplexModel1();

        $model1->name = 'Name 1';
        $model1->id = 1;

        $model2 = new ComplexModel1();

        self::assertFalse(
            Model::validateMultiple([$model1, $model2]),
            "Should return 'false' when any model is invalid.",
        );
    }

    public function testFields(): void
    {
        $speaker = new Speaker();

        $fields = $speaker->fields();

        self::assertIsArray(
            $fields,
            'Should return an array.',
        );
        self::assertContains(
            'firstName',
            $fields,
            "Should contain 'firstName'.",
        );
        self::assertContains(
            'lastName',
            $fields,
            "Should contain 'lastName'.",
        );
    }

    public function testClone(): void
    {
        $model = new Speaker();

        $model->firstName = 'Test';

        $model->addError('firstName', 'Error');

        self::assertTrue(
            $model->hasErrors(),
            'Original model should have errors.',
        );

        $clone = clone $model;

        self::assertFalse(
            $clone->hasErrors(),
            'Cloned model should have no errors.',
        );
        self::assertSame(
            'Test',
            $clone->firstName,
            'Cloned model should preserve attribute values.',
        );
    }

    public function testGetFirstErrorReturnsNullForNoErrors(): void
    {
        $speaker = new Speaker();

        self::assertNull(
            $speaker->getFirstError('firstName'),
            "Should return 'null' when no errors exist.",
        );
        self::assertSame(
            [],
            $speaker->getFirstErrors(),
            "Should return an empty 'array' when no errors exist.",
        );
    }

    public function testCreateValidatorsWithValidatorInstance(): void
    {
        $model = new ValidatorInstanceRulesModel();

        $validators = $model->createValidators();

        self::assertCount(
            1,
            $validators,
            'Validator instance in rules should be accepted as a single validator.',
        );
    }

    public function testLoadMultipleWithEmptyModels(): void
    {
        self::assertFalse(
            Model::loadMultiple([], ['data']),
            "Should return 'false' for an empty models 'array'.",
        );
    }

    public function testValidateClearErrorsFalse(): void
    {
        $model = new ComplexModel1();

        $model->id = 1;
        $model->name = 'test';

        $model->addError('name', 'manual error');
        $model->validate(null, false);

        self::assertTrue(
            $model->hasErrors('name'),
            "Should persist when clearErrors parameter is 'false'.",
        );
        self::assertContains(
            'manual error',
            $model->getErrors('name'),
            'Original error should still be present.',
        );
    }

    public function testAfterValidateEventFires(): void
    {
        $fired = false;

        $model = new ComplexModel1();

        $model->id = 1;
        $model->name = 'test';

        $model->on(
            Model::EVENT_AFTER_VALIDATE,
            static function () use (&$fired): void {
                $fired = true;
            },
        );

        $model->validate();

        self::assertTrue(
            $fired,
            'Should fire during validation.',
        );
    }

    public function testBeforeValidateEventFires(): void
    {
        $fired = false;

        $model = new ComplexModel1();

        $model->id = 1;
        $model->name = 'test';

        $model->on(
            Model::EVENT_BEFORE_VALIDATE,
            static function () use (&$fired): void {
                $fired = true;
            },
        );

        $model->validate();

        self::assertTrue(
            $fired,
            'Should fire during validation.',
        );
    }

    public function testSetAttributesUnsafeIsIgnored(): void
    {
        $model = new RulesModel();

        $model->rules = [
            [
                ['name'],
                'required',
            ],
        ];

        $model->setAttributes(
            [
                'name' => 'ok',
                'email' => 'bad',
            ],
            true,
        );

        self::assertSame(
            'ok',
            $model->name,
            'Should be set as it is a safe attribute.',
        );
        self::assertNull(
            $model->email,
            'Should be ignored as it is an unsafe attribute.',
        );
    }

    public function testIsAttributeRequiredWithConditionalWhen(): void
    {
        $singer = new Singer();

        self::assertTrue(
            $singer->isAttributeRequired('lastName'),
            "Should be required without a 'when' condition.",
        );
        self::assertFalse(
            $singer->isAttributeRequired('test'),
            "Should not be required when a 'when' condition is present.",
        );
    }
}
