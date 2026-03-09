<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

declare(strict_types=1);

namespace yiiunit\framework\web;

use Yii;
use yii\base\InvalidArgumentException;
use yii\base\InvalidConfigException;
use yii\helpers\FileHelper;
use yii\web\AssetBundle;
use yii\web\AssetConverter;
use yii\web\AssetConverterInterface;
use yii\web\AssetManager;
use yiiunit\TestCase;

/**
 * @group web
 */
class AssetManagerTest extends TestCase
{
    private $assetsPath;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mockApplication();

        Yii::setAlias('@web', '/');
        Yii::setAlias('@webroot', '@yiiunit/data/web');
        Yii::setAlias('@testAssetsPath', '@webroot/assets');
        Yii::setAlias('@testAssetsUrl', '@web/assets');
        Yii::setAlias('@testSourcePath', '@webroot/assetSources');

        $this->assetsPath = Yii::getAlias('@testAssetsPath');
        $this->cleanAssetsDir();
    }

    protected function tearDown(): void
    {
        $this->cleanAssetsDir();
        parent::tearDown();
    }

    private function cleanAssetsDir(): void
    {
        $handle = opendir($this->assetsPath);
        if ($handle === false) {
            return;
        }
        while (($file = readdir($handle)) !== false) {
            if ($file === '.' || $file === '..' || $file === '.gitignore') {
                continue;
            }
            $path = $this->assetsPath . DIRECTORY_SEPARATOR . $file;
            if (is_dir($path)) {
                FileHelper::removeDirectory($path);
            } else {
                FileHelper::unlink($path);
            }
        }
        closedir($handle);
    }

    private function createManager(array $config = []): AssetManager
    {
        return new AssetManager(array_merge([
            'basePath' => '@testAssetsPath',
            'baseUrl' => '@testAssetsUrl',
        ], $config));
    }
    public function testInitResolvesBasePathAlias(): void
    {
        $am = $this->createManager();

        $this->assertSame(realpath(Yii::getAlias('@testAssetsPath')), $am->basePath);
    }

    public function testInitTrimsTrailingSlashFromBaseUrl(): void
    {
        $am = $this->createManager(['baseUrl' => '@testAssetsUrl/']);

        $this->assertSame('/assets', $am->baseUrl);
    }
    public function testCheckBasePathPermissionThrowsOnNonExistentDir(): void
    {
        $am = $this->createManager();
        $am->basePath = '/non/existent/path';

        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessage('The directory does not exist: /non/existent/path');

        $am->checkBasePathPermission();
    }

    public function testCheckBasePathPermissionThrowsOnNonWritableDir(): void
    {
        $path = Yii::getAlias('@webroot') . '/readOnlyAssetsManagerTest';
        FileHelper::removeDirectory($path);
        mkdir($path, 0555);

        if (is_writable($path)) {
            FileHelper::removeDirectory($path);
            $this->markTestSkipped('chmod is unreliable on this system.');
        }

        $am = $this->createManager();
        $am->basePath = $path;

        try {
            $this->expectException(InvalidConfigException::class);
            $this->expectExceptionMessage('The directory is not writable by the Web process');
            $am->checkBasePathPermission();
        } finally {
            chmod($path, 0755);
            FileHelper::removeDirectory($path);
        }
    }

    public function testCheckBasePathPermissionSkipsSecondCheck(): void
    {
        $am = $this->createManager();
        $am->checkBasePathPermission();
        $am->basePath = '/non/existent/path';
        $am->checkBasePathPermission();

        $this->assertSame('/non/existent/path', $am->basePath);
    }
    public function testGetBundleReturnsDummyWhenBundlesDisabled(): void
    {
        $am = $this->createManager();
        $am->bundles = false;

        $bundle = $am->getBundle(AssetManagerTestSimpleBundle::class);

        $this->assertInstanceOf(AssetBundle::class, $bundle);
        $this->assertNull($bundle->sourcePath);
        $this->assertSame([], $bundle->js);
        $this->assertSame([], $bundle->css);
    }

    public function testGetBundleReturnsDummyForFalseEntry(): void
    {
        $am = $this->createManager();
        $am->bundles = [
            AssetManagerTestSimpleBundle::class => false,
        ];

        $bundle = $am->getBundle(AssetManagerTestSimpleBundle::class);

        $this->assertNull($bundle->sourcePath);
        $this->assertSame([], $bundle->js);
        $this->assertSame([], $bundle->css);
    }

    public function testGetBundleReturnsCachedInstance(): void
    {
        $am = $this->createManager();
        $existing = new AssetManagerTestSimpleBundle();
        $am->bundles = [
            AssetManagerTestSimpleBundle::class => $existing,
        ];

        $result = $am->getBundle(AssetManagerTestSimpleBundle::class);

        $this->assertSame($existing, $result);
    }

    public function testGetBundleLoadsFromArrayConfig(): void
    {
        $am = $this->createManager();
        $am->bundles = [
            AssetManagerTestSimpleBundle::class => [
                'css' => ['override.css'],
            ],
        ];

        $bundle = $am->getBundle(AssetManagerTestSimpleBundle::class, false);

        $this->assertSame(['override.css'], $bundle->css);
    }

    public function testGetBundleThrowsOnInvalidConfig(): void
    {
        $am = $this->createManager();
        $am->bundles = [
            AssetManagerTestSimpleBundle::class => 'invalid-string',
        ];

        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessage('Invalid asset bundle configuration: ' . AssetManagerTestSimpleBundle::class);

        $am->getBundle(AssetManagerTestSimpleBundle::class);
    }

    public function testGetBundleLoadsNewBundleByClassName(): void
    {
        $am = $this->createManager();

        $bundle = $am->getBundle(AssetManagerTestSimpleBundle::class, false);

        $this->assertInstanceOf(AssetManagerTestSimpleBundle::class, $bundle);
    }

    public function testGetBundlePublishesByDefault(): void
    {
        $am = $this->createManager();

        $bundle = $am->getBundle(AssetManagerTestSourceBundle::class);

        $this->assertNotNull($bundle->basePath);
        $this->assertDirectoryExists($bundle->basePath);
    }

    public function testLoadDummyBundleCachesInstance(): void
    {
        $am = $this->createManager();
        $am->bundles = false;

        $first = $am->getBundle(AssetManagerTestSimpleBundle::class);
        $second = $am->getBundle(AssetManagerTestSimpleBundle::class);

        $this->assertSame($first, $second);
    }
    public function testGetConverterCreatesDefaultInstance(): void
    {
        $am = $this->createManager();

        $converter = $am->getConverter();

        $this->assertInstanceOf(AssetConverterInterface::class, $converter);
        $this->assertInstanceOf(AssetConverter::class, $converter);
    }

    public function testGetConverterFromArrayConfig(): void
    {
        $am = $this->createManager();
        $am->setConverter(['forceConvert' => true]);

        $converter = $am->getConverter();

        $this->assertInstanceOf(AssetConverter::class, $converter);
        $this->assertTrue($converter->forceConvert);
    }

    public function testGetConverterFromStringConfig(): void
    {
        $am = $this->createManager();
        $am->setConverter(AssetConverter::class);

        $converter = $am->getConverter();

        $this->assertInstanceOf(AssetConverter::class, $converter);
    }

    public function testGetConverterFromInstance(): void
    {
        $am = $this->createManager();
        $instance = new AssetConverter();
        $am->setConverter($instance);

        $this->assertSame($instance, $am->getConverter());
    }

    public function testGetConverterCachesInstance(): void
    {
        $am = $this->createManager();

        $first = $am->getConverter();
        $second = $am->getConverter();

        $this->assertSame($first, $second);
    }
    public function testPublishFileByPath(): void
    {
        $am = $this->createManager();
        $filePath = Yii::getAlias('@webroot') . '/data.txt';

        $result = $am->publish($filePath);

        $this->assertCount(2, $result);
        $this->assertFileExists($result[0]);
        $this->assertStringEndsWith(DIRECTORY_SEPARATOR . 'data.txt', $result[0]);
        $this->assertStringStartsWith('/assets/', $result[1]);
        $this->assertStringEndsWith('/data.txt', $result[1]);
    }

    public function testPublishDirectoryByPath(): void
    {
        $am = $this->createManager();
        $dirPath = Yii::getAlias('@testSourcePath');

        $result = $am->publish($dirPath);

        $this->assertCount(2, $result);
        $this->assertDirectoryExists($result[0]);
        $this->assertStringStartsWith('/assets/', $result[1]);
    }

    public function testPublishReturnsCachedResult(): void
    {
        $am = $this->createManager();
        $filePath = Yii::getAlias('@webroot') . '/data.txt';

        $first = $am->publish($filePath);
        $second = $am->publish($filePath);

        $this->assertSame($first, $second);
    }

    public function testPublishThrowsOnNonExistentPath(): void
    {
        $am = $this->createManager();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The file or directory to be published does not exist');

        $am->publish('/non/existent/file.txt');
    }

    public function testPublishThrowsOnNonReadablePath(): void
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'yii_test_');
        chmod($tmpFile, 0000);

        if (is_readable($tmpFile)) {
            chmod($tmpFile, 0644);
            unlink($tmpFile);
            $this->markTestSkipped('chmod is unreliable on this system.');
        }

        try {
            $am = $this->createManager();
            $this->expectException(InvalidArgumentException::class);
            $this->expectExceptionMessage('The file or directory to be published is not readable');
            $am->publish($tmpFile);
        } finally {
            chmod($tmpFile, 0644);
            unlink($tmpFile);
        }
    }

    public function testPublishFileWithLinkAssets(): void
    {
        $am = $this->createManager(['linkAssets' => true]);
        $filePath = Yii::getAlias('@webroot') . '/data.txt';

        $result = $am->publish($filePath);

        $this->assertFileExists($result[0]);
        $this->assertTrue(is_link($result[0]));
    }

    public function testPublishDirectoryWithLinkAssets(): void
    {
        $am = $this->createManager(['linkAssets' => true]);
        $dirPath = Yii::getAlias('@testSourcePath');

        $result = $am->publish($dirPath);

        $this->assertDirectoryExists($result[0]);
        $this->assertTrue(is_link($result[0]));

        FileHelper::unlink($result[0]);
    }

    public function testPublishFileWithLinkAssetsRethrowsOnSymlinkFailure(): void
    {
        Yii::$app->errorHandler->register();

        try {
            $am = $this->createManager(['linkAssets' => true]);
            $filePath = Yii::getAlias('@webroot') . '/data.txt';

            $dir = $this->invokeMethod($am, 'hash', [$filePath]);
            $dstDir = $am->basePath . DIRECTORY_SEPARATOR . $dir;
            $dstFile = $dstDir . DIRECTORY_SEPARATOR . basename($filePath);

            FileHelper::createDirectory($dstDir, 0775, true);
            if (!@symlink('/non/existent/target', $dstFile)) {
                $this->markTestSkipped('Cannot create dangling symlinks on this system.');
            }

            $this->expectException(\yii\base\ErrorException::class);
            $am->publish($filePath);
        } finally {
            restore_error_handler();
            restore_exception_handler();
        }
    }

    public function testPublishDirectoryWithLinkAssetsRethrowsOnSymlinkFailure(): void
    {
        Yii::$app->errorHandler->register();

        try {
            $am = $this->createManager(['linkAssets' => true]);
            $dirPath = Yii::getAlias('@testSourcePath');

            $dir = $this->invokeMethod($am, 'hash', [$dirPath]);
            $dstDir = $am->basePath . DIRECTORY_SEPARATOR . $dir;

            if (!@symlink('/non/existent/target', $dstDir)) {
                $this->markTestSkipped('Cannot create dangling symlinks on this system.');
            }

            $this->expectException(\yii\base\ErrorException::class);
            $am->publish($dirPath);
        } finally {
            restore_error_handler();
            restore_exception_handler();
        }
    }

    public function testPublishFileWithFileMode(): void
    {
        $am = $this->createManager(['fileMode' => 0644]);
        $filePath = Yii::getAlias('@webroot') . '/data.txt';

        $result = $am->publish($filePath);

        $this->assertFileExists($result[0]);
        $this->assertSame(0644, fileperms($result[0]) & 0777);
    }

    public function testPublishFileWithAppendTimestamp(): void
    {
        $am = $this->createManager(['appendTimestamp' => true]);
        $filePath = Yii::getAlias('@webroot') . '/data.txt';

        $result = $am->publish($filePath);

        $this->assertMatchesRegularExpression('/data\.txt\?v=\d+$/', $result[1]);
    }

    public function testPublishFileWithoutAppendTimestamp(): void
    {
        $am = $this->createManager(['appendTimestamp' => false]);
        $filePath = Yii::getAlias('@webroot') . '/data.txt';

        $result = $am->publish($filePath);

        $this->assertStringNotContainsString('?v=', $result[1]);
    }

    public function testPublishDirectoryWithAfterCopy(): void
    {
        $called = false;
        $am = $this->createManager([
            'afterCopy' => function ($from, $to) use (&$called) {
                $called = true;
            },
        ]);

        $am->publish(Yii::getAlias('@testSourcePath'));

        $this->assertTrue($called);
    }

    public function testPublishDirectoryOptionsAfterCopyOverridesClassAfterCopy(): void
    {
        $classCalled = false;
        $optionsCalled = false;
        $am = $this->createManager([
            'afterCopy' => function ($from, $to) use (&$classCalled) {
                $classCalled = true;
            },
        ]);

        $am->publish(Yii::getAlias('@testSourcePath'), [
            'afterCopy' => function ($from, $to) use (&$optionsCalled) {
                $optionsCalled = true;
            },
        ]);

        $this->assertTrue($optionsCalled);
        $this->assertFalse($classCalled);
    }

    public function testPublishDirectoryWithForceCopyOption(): void
    {
        $copyCount = 0;
        $am = $this->createManager([
            'beforeCopy' => function ($from, $to) use (&$copyCount) {
                $copyCount++;
                return strncmp(basename($from), '.', 1) !== 0;
            },
        ]);
        $dirPath = Yii::getAlias('@testSourcePath');

        $am->publish($dirPath);
        $copyCount = 0;
        $this->setInaccessibleProperty($am, '_published', []);

        $am->publish($dirPath, ['forceCopy' => true]);

        $this->assertGreaterThan(0, $copyCount);
    }

    public function testPublishDirectoryWithForceCopyProperty(): void
    {
        $copyCount = 0;
        $am = $this->createManager([
            'forceCopy' => true,
            'beforeCopy' => function ($from, $to) use (&$copyCount) {
                $copyCount++;
                return strncmp(basename($from), '.', 1) !== 0;
            },
        ]);
        $dirPath = Yii::getAlias('@testSourcePath');

        $am->publish($dirPath);
        $copyCount = 0;
        $this->setInaccessibleProperty($am, '_published', []);

        $am->publish($dirPath);

        $this->assertGreaterThan(0, $copyCount);
    }

    public function testPublishDirectoryForceCopyOptionFalseOverridesProperty(): void
    {
        $copyCount = 0;
        $am = $this->createManager([
            'forceCopy' => true,
            'beforeCopy' => function ($from, $to) use (&$copyCount) {
                $copyCount++;
                return strncmp(basename($from), '.', 1) !== 0;
            },
        ]);
        $dirPath = Yii::getAlias('@testSourcePath');

        $this->setInaccessibleProperty($am, '_published', []);
        $am->publish($dirPath);
        $firstCopyCount = $copyCount;

        $copyCount = 0;
        $this->setInaccessibleProperty($am, '_published', []);
        $am->publish($dirPath, ['forceCopy' => false]);

        $this->assertSame(0, $copyCount);
        $this->assertGreaterThan(0, $firstCopyCount);
    }
    public function testGetPublishedPathForPublishedFile(): void
    {
        $am = $this->createManager();
        $filePath = Yii::getAlias('@webroot') . '/data.txt';
        $am->publish($filePath);

        $result = $am->getPublishedPath($filePath);

        $this->assertStringEndsWith(DIRECTORY_SEPARATOR . 'data.txt', $result);
        $this->assertFileExists($result);
    }

    public function testGetPublishedPathForUnpublishedFile(): void
    {
        $am = $this->createManager();
        $filePath = Yii::getAlias('@webroot') . '/data.txt';

        $result = $am->getPublishedPath($filePath);

        $this->assertStringEndsWith(DIRECTORY_SEPARATOR . 'data.txt', $result);
        $this->assertStringContainsString(DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR, $result);
    }

    public function testGetPublishedPathForDirectory(): void
    {
        $am = $this->createManager();
        $dirPath = Yii::getAlias('@testSourcePath');

        $result = $am->getPublishedPath($dirPath);

        $this->assertStringNotContainsString('data.txt', $result);
        $this->assertStringNotContainsString(basename($dirPath), $result);
        $this->assertStringStartsWith($am->basePath . DIRECTORY_SEPARATOR, $result);
    }

    public function testGetPublishedPathReturnsFalseForNonExistentPath(): void
    {
        $am = $this->createManager();

        $this->assertFalse($am->getPublishedPath('/non/existent/path'));
    }

    public function testGetPublishedPathUsesCache(): void
    {
        $am = $this->createManager();
        $filePath = Yii::getAlias('@webroot') . '/data.txt';
        $published = $am->publish($filePath);

        $this->assertSame($published[0], $am->getPublishedPath($filePath));
    }
    public function testGetPublishedUrlForPublishedFile(): void
    {
        $am = $this->createManager();
        $filePath = Yii::getAlias('@webroot') . '/data.txt';
        $am->publish($filePath);

        $result = $am->getPublishedUrl($filePath);

        $this->assertStringEndsWith('/data.txt', $result);
        $this->assertStringStartsWith('/assets/', $result);
    }

    public function testGetPublishedUrlForUnpublishedFile(): void
    {
        $am = $this->createManager();
        $filePath = Yii::getAlias('@webroot') . '/data.txt';

        $result = $am->getPublishedUrl($filePath);

        $this->assertStringEndsWith('/data.txt', $result);
        $this->assertStringStartsWith('/assets/', $result);
    }

    public function testGetPublishedUrlForDirectory(): void
    {
        $am = $this->createManager();
        $dirPath = Yii::getAlias('@testSourcePath');

        $result = $am->getPublishedUrl($dirPath);

        $this->assertStringStartsWith('/assets/', $result);
        $this->assertStringNotContainsString('data.txt', $result);
        $this->assertStringNotContainsString(basename($dirPath), $result);
    }

    public function testGetPublishedUrlReturnsFalseForNonExistentPath(): void
    {
        $am = $this->createManager();

        $this->assertFalse($am->getPublishedUrl('/non/existent/path'));
    }

    public function testGetPublishedUrlUsesCache(): void
    {
        $am = $this->createManager();
        $filePath = Yii::getAlias('@webroot') . '/data.txt';
        $published = $am->publish($filePath);

        $this->assertSame($published[1], $am->getPublishedUrl($filePath));
    }
    public function testHashWithCustomCallback(): void
    {
        $am = $this->createManager([
            'hashCallback' => function ($path) {
                return 'custom-hash';
            },
        ]);
        $filePath = Yii::getAlias('@webroot') . '/data.txt';

        $result = $am->publish($filePath);

        $this->assertStringContainsString('custom-hash', $result[0]);
        $this->assertStringContainsString('custom-hash', $result[1]);
    }
    public function testGetAssetUrlWithRelativeAsset(): void
    {
        $am = $this->createManager();
        $bundle = new AssetManagerTestSimpleBundle();
        $bundle->basePath = Yii::getAlias('@webroot/js');
        $bundle->baseUrl = '/js';

        $result = $am->getAssetUrl($bundle, 'jquery.js');

        $this->assertSame('/js/jquery.js', $result);
    }

    public function testGetAssetUrlWithAbsoluteUrl(): void
    {
        $am = $this->createManager();
        $bundle = new AssetManagerTestSimpleBundle();
        $bundle->baseUrl = '/js';

        $result = $am->getAssetUrl($bundle, 'http://example.com/jquery.js');

        $this->assertSame('http://example.com/jquery.js', $result);
    }

    public function testGetAssetUrlWithProtocolRelativeUrl(): void
    {
        $am = $this->createManager();
        $bundle = new AssetManagerTestSimpleBundle();
        $bundle->baseUrl = '/js';

        $result = $am->getAssetUrl($bundle, '//cdn.example.com/jquery.js');

        $this->assertSame('//cdn.example.com/jquery.js', $result);
    }

    public function testGetAssetUrlWithAppendTimestampEnabled(): void
    {
        $am = $this->createManager(['appendTimestamp' => true]);
        $bundle = new AssetManagerTestSimpleBundle();
        $bundle->basePath = Yii::getAlias('@webroot/assetSources');
        $bundle->baseUrl = '/sources';

        $result = $am->getAssetUrl($bundle, 'js/jquery.js');

        $this->assertMatchesRegularExpression('/^\/sources\/js\/jquery\.js\?v=\d+$/', $result);
    }

    public function testGetAssetUrlWithAppendTimestampDisabled(): void
    {
        $am = $this->createManager(['appendTimestamp' => false]);
        $bundle = new AssetManagerTestSimpleBundle();
        $bundle->basePath = Yii::getAlias('@webroot/assetSources');
        $bundle->baseUrl = '/sources';

        $result = $am->getAssetUrl($bundle, 'js/jquery.js');

        $this->assertSame('/sources/js/jquery.js', $result);
    }

    public function testGetAssetUrlWithPerAssetTimestampOverride(): void
    {
        $am = $this->createManager(['appendTimestamp' => false]);
        $bundle = new AssetManagerTestSimpleBundle();
        $bundle->basePath = Yii::getAlias('@webroot/assetSources');
        $bundle->baseUrl = '/sources';

        $result = $am->getAssetUrl($bundle, 'js/jquery.js', true);

        $this->assertMatchesRegularExpression('/^\/sources\/js\/jquery\.js\?v=\d+$/', $result);
    }

    public function testGetAssetUrlWithPerAssetTimestampDisableOverride(): void
    {
        $am = $this->createManager(['appendTimestamp' => true]);
        $bundle = new AssetManagerTestSimpleBundle();
        $bundle->basePath = Yii::getAlias('@webroot/assetSources');
        $bundle->baseUrl = '/sources';

        $result = $am->getAssetUrl($bundle, 'js/jquery.js', false);

        $this->assertSame('/sources/js/jquery.js', $result);
    }

    public function testGetAssetUrlWithAssetMapExactMatch(): void
    {
        $am = $this->createManager([
            'assetMap' => [
                'jquery.js' => 'jquery/dist/jquery.min.js',
            ],
        ]);
        $bundle = new AssetManagerTestSimpleBundle();
        $bundle->baseUrl = '/js';

        $result = $am->getAssetUrl($bundle, 'jquery.js');

        $this->assertSame('/assets/jquery/dist/jquery.min.js', $result);
    }

    public function testGetAssetUrlWithAssetMapSuffixMatch(): void
    {
        $am = $this->createManager([
            'assetMap' => [
                'jquery.min.js' => 'jquery/dist/jquery.js',
            ],
        ]);
        $bundle = new AssetManagerTestSimpleBundle();
        $bundle->basePath = Yii::getAlias('@webroot/js');
        $bundle->baseUrl = '/js';

        $result = $am->getAssetUrl($bundle, 'path/to/jquery.min.js');

        $this->assertSame('/assets/jquery/dist/jquery.js', $result);
    }

    public function testGetAssetUrlWithAssetMapWebAlias(): void
    {
        $am = $this->createManager([
            'assetMap' => [
                'jquery.js' => '@web/js/jquery.custom.js',
            ],
        ]);
        $bundle = new AssetManagerTestSimpleBundle();
        $bundle->baseUrl = '/js';

        $result = $am->getAssetUrl($bundle, 'jquery.js');

        $this->assertSame('/js/jquery.custom.js', $result);
    }
    public function testGetAssetPathForRelativeAsset(): void
    {
        $am = $this->createManager();
        $bundle = new AssetManagerTestSimpleBundle();
        $bundle->basePath = Yii::getAlias('@webroot/js');

        $result = $am->getAssetPath($bundle, 'jquery.js');

        $this->assertSame(Yii::getAlias('@webroot/js') . '/jquery.js', $result);
    }

    public function testGetAssetPathReturnsFalseForAbsoluteUrl(): void
    {
        $am = $this->createManager();
        $bundle = new AssetManagerTestSimpleBundle();
        $bundle->basePath = Yii::getAlias('@webroot/js');

        $this->assertFalse($am->getAssetPath($bundle, 'http://example.com/jquery.js'));
    }

    public function testGetAssetPathWithAssetMapReturnsBasePath(): void
    {
        $am = $this->createManager([
            'assetMap' => [
                'jquery.js' => 'mapped/jquery.js',
            ],
        ]);
        $bundle = new AssetManagerTestSimpleBundle();
        $bundle->basePath = Yii::getAlias('@webroot/js');

        $result = $am->getAssetPath($bundle, 'jquery.js');

        $this->assertSame($am->basePath . '/mapped/jquery.js', $result);
    }

    public function testGetAssetPathWithAssetMapAbsoluteReturnsFalse(): void
    {
        $am = $this->createManager([
            'assetMap' => [
                'jquery.js' => 'http://cdn.example.com/jquery.js',
            ],
        ]);
        $bundle = new AssetManagerTestSimpleBundle();

        $this->assertFalse($am->getAssetPath($bundle, 'jquery.js'));
    }
    public function testResolveAssetWithSourcePathSuffixMatch(): void
    {
        $am = $this->createManager([
            'assetMap' => [
                'assetSources/js/jquery.js' => 'mapped/jquery.js',
            ],
        ]);
        $bundle = new AssetManagerTestSourceBundle();

        $result = $this->invokeMethod($am, 'resolveAsset', [$bundle, 'js/jquery.js']);

        $this->assertSame('mapped/jquery.js', $result);
    }

    public function testResolveAssetWithSourcePathSuffixMatchRequiresSourcePath(): void
    {
        $am = $this->createManager([
            'assetMap' => [
                'assetSources/js/jquery.js' => 'mapped/jquery.js',
            ],
        ]);
        $bundle = new AssetManagerTestSimpleBundle();
        $bundle->sourcePath = null;

        $result = $this->invokeMethod($am, 'resolveAsset', [$bundle, 'js/jquery.js']);

        $this->assertFalse($result);
    }

    public function testResolveAssetReturnsFalseWhenNoMatch(): void
    {
        $am = $this->createManager([
            'assetMap' => [
                'other.js' => 'mapped/other.js',
            ],
        ]);
        $bundle = new AssetManagerTestSimpleBundle();

        $result = $this->invokeMethod($am, 'resolveAsset', [$bundle, 'jquery.js']);

        $this->assertFalse($result);
    }
    public function testGetActualAssetUrlWithWebAliasInAssetMap(): void
    {
        $am = $this->createManager([
            'assetMap' => [
                'app.js' => '@web/dist/app.js',
            ],
        ]);
        $bundle = new AssetManagerTestSimpleBundle();
        $bundle->baseUrl = '/bundles';

        $result = $am->getActualAssetUrl($bundle, 'app.js');

        $this->assertSame('/dist/app.js', $result);
    }

    public function testGetActualAssetUrlWithNonWebAliasInAssetMap(): void
    {
        $am = $this->createManager([
            'assetMap' => [
                'app.js' => 'dist/app.js',
            ],
        ]);
        $bundle = new AssetManagerTestSimpleBundle();
        $bundle->baseUrl = '/bundles';

        $result = $am->getActualAssetUrl($bundle, 'app.js');

        $this->assertSame('/assets/dist/app.js', $result);
    }

    public function testGetActualAssetUrlNoAssetMapUsesBundle(): void
    {
        $am = $this->createManager();
        $bundle = new AssetManagerTestSimpleBundle();
        $bundle->baseUrl = '/bundles';

        $result = $am->getActualAssetUrl($bundle, 'app.js');

        $this->assertSame('/bundles/app.js', $result);
    }

    public function testGetActualAssetUrlWithAbsoluteResolvedAsset(): void
    {
        $am = $this->createManager([
            'assetMap' => [
                'app.js' => 'http://cdn.example.com/app.js',
            ],
        ]);
        $bundle = new AssetManagerTestSimpleBundle();

        $result = $am->getActualAssetUrl($bundle, 'app.js');

        $this->assertSame('http://cdn.example.com/app.js', $result);
    }

    public function testGetActualAssetUrlWithSlashPrefixedResolvedAsset(): void
    {
        $am = $this->createManager([
            'assetMap' => [
                'app.js' => '/static/app.js',
            ],
        ]);
        $bundle = new AssetManagerTestSimpleBundle();

        $result = $am->getActualAssetUrl($bundle, 'app.js');

        $this->assertSame('/static/app.js', $result);
    }

    public function testGetActualAssetUrlWithNonRelativeBundleAsset(): void
    {
        $am = $this->createManager();
        $bundle = new AssetManagerTestSimpleBundle();
        $bundle->baseUrl = '/bundles';

        $result = $am->getActualAssetUrl($bundle, 'http://example.com/app.js');

        $this->assertSame('http://example.com/app.js', $result);
    }
}

class AssetManagerTestSimpleBundle extends AssetBundle
{
    public $basePath = '@webroot/js';
    public $baseUrl = '@web/js';
    public $js = [
        'jquery.js',
    ];
}

class AssetManagerTestSourceBundle extends AssetBundle
{
    public $sourcePath = '@testSourcePath';
    public $js = [
        'js/jquery.js',
    ];
    public $css = [
        'css/stub.css',
    ];
}
