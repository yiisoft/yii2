<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

declare(strict_types=1);

namespace yiiunit\framework\jquery\validators;

use Yii;
use yii\validators\FileValidator;
use yii\web\View;
use yiiunit\data\validators\models\FakedValidationModel;

/**
 * @group jquery
 * @group validators
 */
final class FileValidatorJqueryClientScriptTest extends \yiiunit\TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->mockWebApplication();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->destroyApplication();
    }

    public function testClientValidateAttribute(): void
    {
        $modelValidator = new FakedValidationModel();
        $validator = new FileValidator(
            [
                'extensions' => [
                    'jpg',
                    'png',
                ],
                'maxSize' => 1024 * 1024,
                'minSize' => 1024,
            ],
        );

        $modelValidator->attrA = 'test-file.jpg';

        $this->assertSame(
            'yii.validation.file(attribute, messages, {"message":"File upload failed.","skipOnEmpty":true,' .
            '"mimeTypes":[],"wrongMimeType":"Only files with these MIME types are allowed: .",' .
            '"extensions":["jpg","png"],"wrongExtension":"Only files with these extensions are allowed: jpg, png.",' .
            '"minSize":1024,"tooSmall":"The file \u0022{file}\u0022 is too small. Its size cannot be smaller than 1 KiB.",' .
            '"maxSize":1048576,"tooBig":"The file \u0022{file}\u0022 is too big. Its size cannot exceed 1 MiB.",' .
            '"maxFiles":1,"tooMany":"You can upload at most 1 file."});',
            $validator->clientValidateAttribute($modelValidator, 'attrA', new View()),
            "'clientValidateAttribute()' method should return correct validation script.",
        );
        $this->assertSame(
            [
                'message' => 'File upload failed.',
                'skipOnEmpty' => true,
                'mimeTypes' => [],
                'wrongMimeType' => 'Only files with these MIME types are allowed: .',
                'extensions' => [
                    'jpg',
                    'png',
                ],
                'wrongExtension' => 'Only files with these extensions are allowed: jpg, png.',
                'minSize' => 1024,
                'tooSmall' => 'The file "{file}" is too small. Its size cannot be smaller than 1 KiB.',
                'maxSize' => 1048576,
                'tooBig' => 'The file "{file}" is too big. Its size cannot exceed 1 MiB.',
                'maxFiles' => 1,
                'tooMany' => 'You can upload at most 1 file.',

            ],
            $validator->getClientOptions($modelValidator, 'attrA'),
            "'getClientOptions()' method should return correct options array.",
        );

        $validator->validate('someIncorrectValue', $errorMessage);

        $this->assertSame(
            'Please upload a file.',
            $errorMessage,
            'Failed asserting that the generated error message matches the expected one.',
        );
    }

    public function testClientValidateAttributeWithMimeTypes(): void
    {
        $modelValidator = new FakedValidationModel();
        $validator = new FileValidator(
            [
                'mimeTypes' => [
                    'image/jpeg',
                    'image/png',
                ],
                'maxFiles' => 3,
            ],
        );

        $this->assertSame(
            'yii.validation.file(attribute, messages, {"message":"File upload failed.","skipOnEmpty":true,' .
            '"mimeTypes":[/^image\/jpeg$/i,/^image\/png$/i],' .
            '"wrongMimeType":"Only files with these MIME types are allowed: image\/jpeg, image\/png.",' .
            '"extensions":[],"wrongExtension":"Only files with these extensions are allowed: .","maxFiles":3,' .
            '"tooMany":"You can upload at most 3 files."});',
            $validator->clientValidateAttribute($modelValidator, 'attrA', new View()),
            "'clientValidateAttribute()' method should return correct validation script.",
        );

        $clientOptions = $validator->getClientOptions($modelValidator, 'attrA');
        $clientOptions['mimeTypes'] = array_map(
            fn ($pattern) => (string) $pattern,
            $clientOptions['mimeTypes'] ?? [],
        );

        $this->assertSame(
            [
                'message' => 'File upload failed.',
                'skipOnEmpty' => true,
                'mimeTypes' => [
                    '/^image\/jpeg$/i',
                    '/^image\/png$/i',
                ],
                'wrongMimeType' => 'Only files with these MIME types are allowed: image/jpeg, image/png.',
                'extensions' => [],
                'wrongExtension' => 'Only files with these extensions are allowed: .',
                'maxFiles' => 3,
                'tooMany' => 'You can upload at most 3 files.',
            ],
            $clientOptions,
            "'getClientOptions()' method should return correct options array.",
        );

        $validator->validate('someIncorrectValue', $errorMessage);

        $this->assertSame(
            'Please upload a file.',
            $errorMessage,
            'Failed asserting that the generated error message matches the expected one.',
        );
    }

    public function testClientValidateAttributeWithUploadRequired(): void
    {
        $modelValidator = new FakedValidationModel();
        $validator = new FileValidator(['skipOnEmpty' => false]);

        $this->assertSame(
            'yii.validation.file(attribute, messages, {"message":"File upload failed.","skipOnEmpty":false,' .
            '"uploadRequired":"Please upload a file.","mimeTypes":[],' .
            '"wrongMimeType":"Only files with these MIME types are allowed: .","extensions":[],' .
            '"wrongExtension":"Only files with these extensions are allowed: .","maxFiles":1,' .
            '"tooMany":"You can upload at most 1 file."});',
            $validator->clientValidateAttribute($modelValidator, 'attrA', new View()),
            "'clientValidateAttribute()' method should return correct validation script.",
        );
        $this->assertSame(
            [
                'message' => 'File upload failed.',
                'skipOnEmpty' => false,
                'uploadRequired' => 'Please upload a file.',
                'mimeTypes' => [],
                'wrongMimeType' => 'Only files with these MIME types are allowed: .',
                'extensions' => [],
                'wrongExtension' => 'Only files with these extensions are allowed: .',
                'maxFiles' => 1,
                'tooMany' => 'You can upload at most 1 file.',
            ],
            $validator->getClientOptions($modelValidator, 'attrA'),
            "'getClientOptions()' method should return correct options array.",
        );

        $validator->validate('someIncorrectValue', $errorMessage);

        $this->assertSame(
            'Please upload a file.',
            $errorMessage,
            'Failed asserting that the generated error message matches the expected one.',
        );
    }

    public function testClientValidateAttributeWithCustomMessages(): void
    {
        $modelValidator = new FakedValidationModel();
        $validator = new FileValidator(
            [
                'extensions' => ['pdf'],
                'maxSize' => 2048,
                'minSize' => 512,
                'message' => 'Custom file validation message.',
                'wrongExtension' => 'Custom wrong extension message.',
                'tooBig' => 'Custom too big message.',
                'tooSmall' => 'Custom too small message.',
            ],
        );

        $this->assertSame(
            'yii.validation.file(attribute, messages, {"message":"Custom file validation message.",' .
            '"skipOnEmpty":true,"mimeTypes":[],"wrongMimeType":"Only files with these MIME types are allowed: .",' .
            '"extensions":["pdf"],"wrongExtension":"Custom wrong extension message.","minSize":512,' .
            '"tooSmall":"Custom too small message.","maxSize":2048,"tooBig":"Custom too big message.","maxFiles":1,' .
            '"tooMany":"You can upload at most 1 file."});',
            $validator->clientValidateAttribute($modelValidator, 'attrA', new View()),
            "'clientValidateAttribute()' method should return correct validation script.",
        );

        $clientOptions = $validator->getClientOptions($modelValidator, 'attrA');
        $clientOptions['mimeTypes'] = array_map(
            fn ($pattern) => (string) $pattern,
            $clientOptions['mimeTypes'] ?? [],
        );

        $this->assertSame(
            [
                'message' => 'Custom file validation message.',
                'skipOnEmpty' => true,
                'mimeTypes' => [],
                'wrongMimeType' => 'Only files with these MIME types are allowed: .',
                'extensions' => ['pdf'],
                'wrongExtension' => 'Custom wrong extension message.',
                'minSize' => 512,
                'tooSmall' => 'Custom too small message.',
                'maxSize' => 2048,
                'tooBig' => 'Custom too big message.',
                'maxFiles' => 1,
                'tooMany' => 'You can upload at most 1 file.',
            ],
            $clientOptions,
            "'getClientOptions()' method should return correct options array.",
        );

        $validator->validate('someIncorrectValue', $errorMessage);

        $this->assertSame(
            'Please upload a file.',
            $errorMessage,
            'Failed asserting that the generated error message matches the expected one.',
        );
    }

    public function testClientValidateAttributeWithMinimalOptions(): void
    {
        $modelValidator = new FakedValidationModel();
        $validator = new FileValidator();

        $this->assertSame(
            'yii.validation.file(attribute, messages, {"message":"File upload failed.","skipOnEmpty":true,' .
            '"mimeTypes":[],"wrongMimeType":"Only files with these MIME types are allowed: .","extensions":[],' .
            '"wrongExtension":"Only files with these extensions are allowed: .","maxFiles":1,' .
            '"tooMany":"You can upload at most 1 file."});',
            $validator->clientValidateAttribute($modelValidator, 'attrA', new View()),
            "'clientValidateAttribute()' method should return correct validation script.",
        );
        $this->assertSame(
            [
                'message' => 'File upload failed.',
                'skipOnEmpty' => true,
                'mimeTypes' => [],
                'wrongMimeType' => 'Only files with these MIME types are allowed: .',
                'extensions' => [],
                'wrongExtension' => 'Only files with these extensions are allowed: .',
                'maxFiles' => 1,
                'tooMany' => 'You can upload at most 1 file.',
            ],
            $validator->getClientOptions($modelValidator, 'attrA'),
            "'getClientOptions()' method should return correct options array.",
        );

        $validator->validate('someIncorrectValue', $errorMessage);

        $this->assertSame(
            'Please upload a file.',
            $errorMessage,
            'Failed asserting that the generated error message matches the expected one.',
        );
    }

    public function testClientValidateAttributeWithUseJqueryFalse(): void
    {
        Yii::$app->useJquery = false;

        $modelValidator = new FakedValidationModel();
        $validator = new FileValidator(
            [
                'extensions' => [
                    'jpg',
                    'png',
                ],
                'maxSize' => 1024 * 1024,
                'minSize' => 1024,
            ],
        );

        $modelValidator->attrA = 'test-file.jpg';

        $this->assertNull(
            $validator->clientScript,
            "'ClientScript' property should be 'null' when 'useJquery' is 'false'.",
        );
        $this->assertNull(
            $validator->clientValidateAttribute($modelValidator, 'attrA', new View()),
            "'clientValidateAttribute()' method should return 'null' value.",
        );
        $this->assertEmpty(
            $validator->getClientOptions($modelValidator, 'attrA'),
            "'getClientOptions()' method should return an empty array.",
        );

        $validator->validate('someIncorrectValue', $errorMessage);

        $this->assertSame(
            'Please upload a file.',
            $errorMessage,
            'Failed asserting that the generated error message matches the expected one.',
        );
    }
}
