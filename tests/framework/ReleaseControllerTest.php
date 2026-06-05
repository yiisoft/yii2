<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

declare(strict_types=1);

namespace yiiunit\framework;

use ReflectionClass;
use ReflectionMethod;
use yii\base\Exception;
use yii\build\controllers\ReleaseController;
use yii\helpers\FileHelper;
use yiiunit\TestCase;

/**
 * ReleaseControllerTest.
 * @group base
 */
class ReleaseControllerTest extends TestCase
{
    private array $tempPaths = [];

    protected function tearDown(): void
    {
        foreach ($this->tempPaths as $path) {
            FileHelper::removeDirectory($path);
        }
        $this->tempPaths = [];
        parent::tearDown();
    }

    public function testResortChangelogConvertsFixEntriesToBugsAndSortsThem(): void
    {
        $controller = $this->createController();
        $method = new ReflectionMethod($controller, 'resortChangelog');
        $method->setAccessible(true);

        $this->assertSame(
            [
                '',
                '- Bug #275: Fix logging (samdark)',
                '- Bug #283: Fix first sentence extraction (samdark)',
                '- Bug #294: Make dependency optional (samdark)',
                '- Bug #339: Fix static replacement (samdark)',
                '- Bug #360: Fix URL processing (samdark)',
                '- Enh #244: Uniform descriptions (samdark)',
                '- Chg #352: Remove deprecated code (samdark)',
                '- New #131: Add template (samdark)',
                '',
                '',
            ],
            $method->invoke($controller, [
                '- Chg #352: Remove deprecated code (samdark)',
                '- Bug #294: Make dependency optional (samdark)',
                '- Fix #360: Fix URL processing (samdark)',
                '- Enh #244: Uniform descriptions (samdark)',
                '- Bug #339: Fix static replacement (samdark)',
                '- New #131: Add template (samdark)',
                '- Fix #275: Fix logging (samdark)',
                '- Bug #283: Fix first sentence extraction (samdark)',
            ])
        );
    }

    public function testResortChangelogsUpdatesDevelopmentHeaderToRequestedVersion(): void
    {
        $basePath = sys_get_temp_dir() . '/yii-release-controller-test-' . str_replace('.', '', uniqid('', true));
        $this->tempPaths[] = $basePath;
        mkdir($basePath . '/extensions/apidoc', 0777, true);
        $file = $basePath . '/extensions/apidoc/CHANGELOG.md';
        file_put_contents(
            $file,
            "Yii Framework 2 apidoc extension Change Log\n"
            . "===========================================\n\n"
            . "4.0.0 under development\n"
            . "-----------------------\n\n"
            . "- Enh #2: Test enhancement (samdark)\n"
            . "- Fix #1: Test fix (samdark)\n"
        );

        $controller = $this->createController();
        $controller->basePath = $basePath;
        $method = new ReflectionMethod($controller, 'resortChangelogs');
        $method->setAccessible(true);
        $method->invoke($controller, ['apidoc'], '3.0.9');

        $this->assertSame(
            "Yii Framework 2 apidoc extension Change Log\n"
            . "===========================================\n\n"
            . "3.0.9 under development\n"
            . "-----------------------\n\n"
            . "- Bug #1: Test fix (samdark)\n"
            . "- Enh #2: Test enhancement (samdark)\n\n",
            file_get_contents($file)
        );
    }

    public function testResortChangelogsFailsWhenVersionSectionIsMissing(): void
    {
        $basePath = sys_get_temp_dir() . '/yii-release-controller-test-' . str_replace('.', '', uniqid('', true));
        $this->tempPaths[] = $basePath;
        mkdir($basePath . '/extensions/apidoc', 0777, true);
        $file = $basePath . '/extensions/apidoc/CHANGELOG.md';
        $contents = "Yii Framework 2 apidoc extension Change Log\n"
            . "===========================================\n\n"
            . "3.0.8 November 24, 2025\n"
            . "-----------------------\n\n"
            . "- Bug #1: Test change (samdark)\n";
        file_put_contents($file, $contents);

        $controller = $this->createController();
        $controller->basePath = $basePath;
        $method = new ReflectionMethod($controller, 'resortChangelogs');
        $method->setAccessible(true);

        try {
            $method->invoke($controller, ['apidoc'], '3.0.9');
            $this->fail('Expected missing changelog version exception.');
        } catch (Exception $e) {
            $this->assertStringContainsString('3.0.9', $e->getMessage());
        }

        $this->assertSame($contents, file_get_contents($file));
    }

    public function testSplitChangelogUsesExactVersionBoundary(): void
    {
        $basePath = sys_get_temp_dir() . '/yii-release-controller-test-' . str_replace('.', '', uniqid('', true));
        $this->tempPaths[] = $basePath;
        mkdir($basePath, 0777, true);
        $file = $basePath . '/CHANGELOG.md';
        file_put_contents(
            $file,
            "Yii Framework 2 Change Log\n"
            . "==========================\n\n"
            . "2.0.50 May 09, 2026\n"
            . "-------------------\n\n"
            . "- Bug #1: Test change (samdark)\n"
        );

        $controller = $this->createController();
        $method = new ReflectionMethod($controller, 'splitChangelog');
        $method->setAccessible(true);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('2.0.5');

        $method->invoke($controller, $file, '2.0.5');
    }

    private function createController(): ReleaseController
    {
        return (new ReflectionClass(ReleaseController::class))->newInstanceWithoutConstructor();
    }
}
