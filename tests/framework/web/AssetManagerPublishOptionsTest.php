<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

declare(strict_types=1);

namespace yiiunit\framework\web;

use Yii;
use yii\base\InvalidConfigException;
use yii\helpers\FileHelper;
use yii\web\AssetManager;
use yiiunit\TestCase;

/**
 * Tests publishing a directory with publish options, with and without [[AssetManager::linkAssets]].
 *
 * @group web
 */
class AssetManagerPublishOptionsTest extends TestCase
{
    private const SOURCE_FILES = [
        '.hidden',
        'css/app.css',
        'img/logo.png',
        'js/app.js',
        'js/.hidden.js',
        'js/i18n/en.js',
        'php/secret.php',
    ];

    /**
     * @var string
     */
    private $runtimePath;
    /**
     * @var string
     */
    private $sourcePath;
    /**
     * @var string
     */
    private $assetsPath;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mockApplication();

        $this->runtimePath = FileHelper::normalizePath(Yii::getAlias('@yiiunit/runtime') . '/assetPublishOptions');
        FileHelper::removeDirectory($this->runtimePath);
        $this->sourcePath = $this->runtimePath . DIRECTORY_SEPARATOR . 'source';
        $this->assetsPath = $this->runtimePath . DIRECTORY_SEPARATOR . 'assets';
        foreach (self::SOURCE_FILES as $file) {
            $path = $this->sourcePath . DIRECTORY_SEPARATOR . $file;
            FileHelper::createDirectory(dirname($path));
            file_put_contents($path, $file);
        }
        FileHelper::createDirectory($this->assetsPath);
    }

    protected function tearDown(): void
    {
        FileHelper::removeDirectory($this->runtimePath);
        parent::tearDown();
    }

    private function createManager(array $config = []): AssetManager
    {
        return new AssetManager(array_merge([
            'basePath' => $this->assetsPath,
            'baseUrl' => '/assets',
        ], $config));
    }

    /**
     * @return string[] relative paths of all files in a published directory, sorted
     */
    private function listFiles(string $dir): array
    {
        $files = [];
        foreach (FileHelper::findFiles($dir) as $file) {
            $files[] = str_replace('\\', '/', substr($file, strlen($dir) + 1));
        }
        sort($files);

        return $files;
    }

    private function assertAllLinked(string $dir): void
    {
        $this->assertFalse(is_link($dir), 'The published directory must not be a symlink.');
        foreach ($this->listFiles($dir) as $file) {
            $path = $dir . DIRECTORY_SEPARATOR . $file;
            $this->assertTrue(is_link($path), "$file must be a symlink.");
            $this->assertSame(realpath($this->sourcePath . DIRECTORY_SEPARATOR . $file), realpath($path));
        }
    }

    private function assertNoTemporaryDirectories(): void
    {
        $this->assertSame([], glob($this->assetsPath . DIRECTORY_SEPARATOR . '*.tmp-*'));
    }

    public function testLinkAssetsWithoutFiltersLinksWholeDirectory(): void
    {
        $am = $this->createManager(['linkAssets' => true]);

        [$path] = $am->publish($this->sourcePath);

        $this->assertTrue(is_link($path));
        $this->assertFileExists($path . '/php/secret.php');
    }

    public function testLinkAssetsHonoursOnly(): void
    {
        $am = $this->createManager(['linkAssets' => true]);

        [$path, $url] = $am->publish($this->sourcePath, ['only' => ['/js/*']]);

        $this->assertSame(['js/app.js'], $this->listFiles($path));
        $this->assertAllLinked($path);
        $this->assertFileDoesNotExist($path . '/php/secret.php');
        $this->assertSame($am->getPublishedPath($this->sourcePath), $path);
        $this->assertSame($am->getPublishedUrl($this->sourcePath), $url);
        $this->assertNoTemporaryDirectories();
    }

    public function testLinkAssetsHonoursExcept(): void
    {
        $am = $this->createManager(['linkAssets' => true]);

        [$path] = $am->publish($this->sourcePath, ['except' => ['/php/']]);

        $this->assertSame(['css/app.css', 'img/logo.png', 'js/app.js', 'js/i18n/en.js'], $this->listFiles($path));
        $this->assertAllLinked($path);
    }

    /**
     * @dataProvider filterOptionsProvider
     */
    public function testLinkAssetsPublishesSameFilesAsCopy(array $options): void
    {
        [$copied] = $this->createManager()->publish($this->sourcePath, $options);
        $copiedFiles = $this->listFiles($copied);
        FileHelper::removeDirectory($copied);

        [$linked] = $this->createManager(['linkAssets' => true])->publish($this->sourcePath, $options);

        $this->assertSame($copiedFiles, $this->listFiles($linked));
        $this->assertAllLinked($linked);
    }

    public static function filterOptionsProvider(): array
    {
        return [
            'only' => [['only' => ['*.js', '*.css']]],
            'only directory' => [['only' => ['/js/*']]],
            'except' => [['except' => ['*.php']]],
            'except directory' => [['except' => ['i18n/', 'php/']]],
            'only and except' => [['only' => ['*.js'], 'except' => ['i18n/']]],
            'case insensitive' => [['only' => ['*.JS'], 'caseSensitive' => false]],
            'not recursive' => [['recursive' => false, 'beforeCopy' => static function ($from) {
                return true;
            }]],
            'files with beforeCopy' => [['files' => ['js/i18n', 'css/app.css', 'php'], 'beforeCopy' => static function ($from) {
                return substr($from, -4) !== '.php';
            }]],
            'files with afterCopy' => [['files' => ['js', 'img/logo.png'], 'afterCopy' => static function () {
            }]],
        ];
    }

    public function testLinkAssetsHonoursBeforeCopyOption(): void
    {
        $am = $this->createManager(['linkAssets' => true]);

        [$path] = $am->publish($this->sourcePath, [
            'beforeCopy' => static function ($from, $to) {
                return basename($from) !== 'php';
            },
        ]);

        // a custom beforeCopy replaces the default one, which skips the dotfiles
        $this->assertSame(
            ['.hidden', 'css/app.css', 'img/logo.png', 'js/.hidden.js', 'js/app.js', 'js/i18n/en.js'],
            $this->listFiles($path)
        );
        $this->assertAllLinked($path);
    }

    public function testLinkAssetsHonoursBeforeCopyProperty(): void
    {
        $am = $this->createManager([
            'linkAssets' => true,
            'beforeCopy' => static function ($from, $to) {
                return substr($from, -4) !== '.php';
            },
        ]);

        [$path] = $am->publish($this->sourcePath);

        $this->assertFalse(is_link($path));
        $this->assertFileDoesNotExist($path . '/php/secret.php');
        $this->assertFileExists($path . '/js/app.js');
    }

    public function testLinkAssetsCallsAfterCopyForLinks(): void
    {
        $calls = [];
        $am = $this->createManager([
            'linkAssets' => true,
            'afterCopy' => static function ($from, $to) use (&$calls) {
                if (is_file($from)) {
                    $calls[] = [$from, is_link($to)];
                }
            },
        ]);

        [$path] = $am->publish($this->sourcePath, ['only' => ['/css/*']]);

        $this->assertSame([[$this->sourcePath . DIRECTORY_SEPARATOR . 'css' . DIRECTORY_SEPARATOR . 'app.css', true]], $calls);
        $this->assertSame(['css/app.css'], $this->listFiles($path));
    }

    public function testLinkAssetsReusesPublishedDirectory(): void
    {
        [$path] = $this->createManager(['linkAssets' => true])->publish($this->sourcePath, ['only' => ['/js/*']]);
        file_put_contents($path . '/marker', '');

        [$path2] = $this->createManager(['linkAssets' => true])->publish($this->sourcePath, ['only' => ['/js/*']]);

        $this->assertSame($path, $path2);
        $this->assertFileExists($path . '/marker');
        $this->assertNoTemporaryDirectories();
    }

    public function testLinkAssetsWithFiltersAndForceCopyRecreatesLinks(): void
    {
        [$path] = $this->createManager(['linkAssets' => true])->publish($this->sourcePath, ['except' => ['/php/']]);
        file_put_contents($path . '/marker', '');
        file_put_contents($this->sourcePath . '/js/i18n/de.js', '');
        unlink($this->sourcePath . '/img/logo.png');

        $am = $this->createManager(['linkAssets' => true, 'forceCopy' => true]);
        $this->assertSame($path, $am->publish($this->sourcePath, ['except' => ['/php/']])[0]);

        $this->assertSame(['css/app.css', 'js/app.js', 'js/i18n/de.js', 'js/i18n/en.js'], $this->listFiles($path));
        $this->assertAllLinked($path);
        $this->assertNoTemporaryDirectories();
    }

    public function testLinkAssetsWithFiltersForceCopyOptionOverridesProperty(): void
    {
        [$path] = $this->createManager(['linkAssets' => true])->publish($this->sourcePath, ['only' => ['/js/*']]);
        file_put_contents($path . '/marker', '');

        $this->createManager(['linkAssets' => true, 'forceCopy' => true])
            ->publish($this->sourcePath, ['only' => ['/js/*'], 'forceCopy' => false]);
        $this->assertFileExists($path . '/marker');

        $this->createManager(['linkAssets' => true])
            ->publish($this->sourcePath, ['only' => ['/js/*'], 'forceCopy' => true]);
        $this->assertFileDoesNotExist($path . '/marker');
    }

    public function testLinkAssetsWithoutFiltersIgnoresForceCopy(): void
    {
        [$path] = $this->createManager(['linkAssets' => true])->publish($this->sourcePath);

        $this->createManager(['linkAssets' => true, 'forceCopy' => true])->publish($this->sourcePath);

        $this->assertTrue(is_link($path));
        $this->assertFileExists($this->sourcePath . '/php/secret.php');
    }

    public function testLinkAssetsWithFiltersReplacesWholeDirectoryLink(): void
    {
        $am = $this->createManager(['linkAssets' => true]);
        $path = $am->getPublishedPath($this->sourcePath);
        symlink($this->sourcePath, $path);

        $this->assertSame($path, $am->publish($this->sourcePath, ['only' => ['/js/*']])[0]);

        $this->assertSame(['js/app.js'], $this->listFiles($path));
        $this->assertAllLinked($path);
        $this->assertFileExists($this->sourcePath . '/php/secret.php', 'The source must not be touched.');
        $this->assertNoTemporaryDirectories();
    }

    public function testLinkAssetsWithFiltersPublishesEmptyDirectoryWhenNothingMatches(): void
    {
        [$path] = $this->createManager(['linkAssets' => true])->publish($this->sourcePath, ['only' => ['*.none']]);

        $this->assertDirectoryExists($path);
        $this->assertSame([], $this->listFiles($path));
    }

    public function testFilesWithLinkAssetsLinksEachEntry(): void
    {
        $am = $this->createManager(['linkAssets' => true]);

        [$path, $url] = $am->publish($this->sourcePath, ['files' => ['js', 'css/app.css']]);

        $this->assertFalse(is_link($path));
        $this->assertTrue(is_link($path . '/js'));
        $this->assertTrue(is_link($path . '/css/app.css'));
        $this->assertFalse(is_link($path . '/css'));
        $this->assertSame(['css/app.css', 'js/.hidden.js', 'js/app.js', 'js/i18n/en.js'], $this->listFiles($path));
        $this->assertSame($am->getPublishedPath($this->sourcePath), $path);
        $this->assertSame($am->getPublishedUrl($this->sourcePath), $url);
        $this->assertNoTemporaryDirectories();

        // a file added to a linked directory is published at once
        file_put_contents($this->sourcePath . '/js/new.js', '');
        $this->assertFileExists($path . '/js/new.js');
    }

    public function testFilesWithLinkAssetsReplacesWholeDirectoryLink(): void
    {
        $am = $this->createManager(['linkAssets' => true]);
        $path = $am->getPublishedPath($this->sourcePath);
        symlink($this->sourcePath, $path);

        $am->publish($this->sourcePath, ['files' => ['css']]);

        $this->assertFalse(is_link($path));
        $this->assertSame(['css/app.css'], $this->listFiles($path));
        $this->assertFileExists($this->sourcePath . '/php/secret.php', 'The source must not be touched.');
    }

    public function testFilesWithLinkAssetsAndForceCopyRecreatesLinks(): void
    {
        [$path] = $this->createManager(['linkAssets' => true])->publish($this->sourcePath, ['files' => ['js', 'css']]);
        file_put_contents($path . '/marker', '');

        $this->createManager(['linkAssets' => true, 'forceCopy' => true])
            ->publish($this->sourcePath, ['files' => ['js', 'css']]);

        $this->assertFalse(is_link($path));
        $this->assertTrue(is_link($path . '/js'));
        $this->assertTrue(is_link($path . '/css'));
        $this->assertFileDoesNotExist($path . '/marker');
        $this->assertFileExists($this->sourcePath . '/js/app.js', 'The source must not be touched.');
        $this->assertNoTemporaryDirectories();
    }

    public function testFilesUsesCallbacks(): void
    {
        $calls = [];
        $am = $this->createManager([
            'beforeCopy' => static function ($from) use (&$calls) {
                $calls[] = basename($from);

                return basename($from) !== 'en.js';
            },
        ]);

        [$path] = $am->publish($this->sourcePath, ['files' => ['js/i18n', 'css/app.css']]);

        // the custom beforeCopy replaces the default one, and is called for the directories on the way to the entries
        $this->assertSame(['css/app.css'], $this->listFiles($path));
        sort($calls);
        $this->assertSame(['app.css', 'css', 'en.js', 'i18n', 'js'], $calls);
    }

    public function testFilesWithLinkAssetsAndBeforeCopyLinksEachFile(): void
    {
        $am = $this->createManager([
            'linkAssets' => true,
            'beforeCopy' => static function ($from) {
                return basename($from) !== 'en.js';
            },
        ]);

        [$path] = $am->publish($this->sourcePath, ['files' => ['js', 'css']]);

        $this->assertSame(['css/app.css', 'js/.hidden.js', 'js/app.js'], $this->listFiles($path));
        $this->assertAllLinked($path);
        $this->assertFalse(is_link($path . '/js'));
    }

    public function testFilesCopiesEachEntry(): void
    {
        $am = $this->createManager();

        [$path] = $am->publish($this->sourcePath, ['files' => ['js', 'css/app.css', 'php/']]);

        $this->assertSame(['css/app.css', 'js/app.js', 'js/i18n/en.js', 'php/secret.php'], $this->listFiles($path));
        $this->assertFalse(is_link($path . '/js'));
        $this->assertFalse(is_link($path . '/css/app.css'));
        $this->assertNoTemporaryDirectories();
    }

    public function testFilesReusesPublishedDirectory(): void
    {
        [$path] = $this->createManager()->publish($this->sourcePath, ['files' => ['js']]);
        file_put_contents($path . '/marker', '');

        $this->createManager()->publish($this->sourcePath, ['files' => ['js']]);

        $this->assertFileExists($path . '/marker');
    }

    public function testFilesWithForceCopyCopiesAgain(): void
    {
        [$path] = $this->createManager()->publish($this->sourcePath, ['files' => ['js']]);
        file_put_contents($this->sourcePath . '/js/i18n/de.js', '');

        $this->createManager()->publish($this->sourcePath, ['files' => ['js'], 'forceCopy' => true]);

        $this->assertSame(['js/app.js', 'js/i18n/de.js', 'js/i18n/en.js'], $this->listFiles($path));
    }

    /**
     * @dataProvider invalidFilesProvider
     */
    public function testFilesValidation(array $options, string $message): void
    {
        $am = $this->createManager(['linkAssets' => true]);

        try {
            $am->publish($this->sourcePath, $options);
            $this->fail('InvalidConfigException was not thrown.');
        } catch (InvalidConfigException $e) {
            $this->assertStringContainsString($message, $e->getMessage());
        }
        $this->assertFileDoesNotExist($am->getPublishedPath($this->sourcePath));
        $this->assertNoTemporaryDirectories();
    }

    public static function invalidFilesProvider(): array
    {
        $notInside = 'which is not an existing relative path inside';

        return [
            'empty' => [['files' => []], 'must be a non-empty array'],
            'not array' => [['files' => 'js'], 'must be a non-empty array'],
            'not string' => [['files' => [1]], 'lists a non-string value'],
            'empty string' => [['files' => ['']], $notInside],
            'absolute' => [['files' => ['/js']], $notInside],
            'drive' => [['files' => ['C:\\js']], $notInside],
            'parent' => [['files' => ['js/../php']], $notInside],
            'outside' => [['files' => ['../source/js']], $notInside],
            'current' => [['files' => ['.']], $notInside],
            'missing' => [['files' => ['fonts']], $notInside],
            'overlapping' => [['files' => ['js', 'js/app.js']], 'which is already published by'],
            'duplicate' => [['files' => ['js', 'js/']], 'which is already published by'],
            'with only' => [['files' => ['js'], 'only' => ['*.js']], 'cannot be combined with "only"'],
            'with except' => [['files' => ['js'], 'except' => ['*.php']], 'cannot be combined with "except"'],
            'with filter' => [
                ['files' => ['js'], 'filter' => static function () {
                    return true;
                }],
                'cannot be combined with "filter"',
            ],
        ];
    }
}
