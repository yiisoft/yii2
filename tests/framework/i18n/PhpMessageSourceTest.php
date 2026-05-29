<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

declare(strict_types=1);

namespace yiiunit\framework\i18n;

use Yii;
use yii\base\InvalidArgumentException;
use yiiunit\framework\i18n\stubs\ExposedPhpMessageSource;
use yiiunit\TestCase;

/**
 * Unit tests for {@see \yii\i18n\PhpMessageSource} category path-traversal hardening.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 2.0.56
 * @group i18n
 */
final class PhpMessageSourceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->mockApplication();
    }

    /**
     * Provides categories that resolve outside [[PhpMessageSource::basePath]] and must be rejected.
     *
     * @return array<string, array{string}> traversal, absolute, and stream-wrapper category inputs.
     */
    public function unsafeCategoryProvider(): array
    {
        return [
            'parent traversal' => ['../../etc/passwd'],
            'leading traversal segment' => ['../secret'],
            'embedded traversal segment' => ['app/../../config/db'],
            'current-dir segment' => ['app/./db'],
            'absolute unix path' => ['/etc/passwd'],
            'windows absolute path' => ['C:\\Windows\\system32'],
            'php stream wrapper' => ['php://filter/resource=config/db'],
            'phar stream wrapper' => ['phar://archive.phar/config'],
        ];
    }

    /**
     * Provides valid categories paired with their expected `basePath`-relative file name.
     *
     * @return array<string, array{string, string}> input category and resolved relative path.
     */
    public function safeCategoryProvider(): array
    {
        return [
            'simple' => ['app', 'app'],
            'slash namespace' => ['app/error', 'app/error'],
            'backslash namespace' => ['modules\\users\\validation', 'modules/users/validation'],
            'dashes and dots' => ['app-name/sub.module', 'app-name/sub.module'],
        ];
    }

    /**
     * @dataProvider unsafeCategoryProvider
     */
    public function testThrowInvalidArgumentExceptionForUnsafeCategory(string $category): void
    {
        $source = new ExposedPhpMessageSource(['basePath' => '@yiiunit/data/i18n/messages']);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid message category:');

        $source->exposeMessageFilePath($category, 'en');
    }

    /**
     * @dataProvider safeCategoryProvider
     */
    public function testReturnExpectedPathForSafeCategory(string $category, string $expectedRelative): void
    {
        $source = new ExposedPhpMessageSource(
            [
                'basePath' => '@yiiunit/data/i18n/messages',
            ],
        );

        $expected = Yii::getAlias('@yiiunit/data/i18n/messages') . '/en/' . $expectedRelative . '.php';

        $this->assertSame(
            $expected,
            $source->exposeMessageFilePath($category, 'en'),
            'Namespace separators must be preserved under basePath.',
        );
    }

    public function testReturnFileMapPathForMappedCategory(): void
    {
        $source = new ExposedPhpMessageSource(
            [
                'basePath' => '@yiiunit/data/i18n/messages',
                'fileMap' => ['weird/../category' => 'safe.php'],
            ],
        );

        $expected = Yii::getAlias('@yiiunit/data/i18n/messages') . '/en/safe.php';

        $this->assertSame(
            $expected,
            $source->exposeMessageFilePath('weird/../category', 'en'),
            'fileMap entry must bypass category validation.',
        );
    }
}
