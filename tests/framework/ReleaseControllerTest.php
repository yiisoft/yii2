<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

declare(strict_types=1);

namespace yiiunit\framework;

use ReflectionClass;
use yii\build\controllers\ReleaseController;
use yiiunit\TestCase;

require_once __DIR__ . '/../../build/controllers/ReleaseController.php';

/**
 * ReleaseControllerTest.
 * @group base
 */
class ReleaseControllerTest extends TestCase
{
    public function testResortChangelogConvertsFixEntriesToBugsAndSortsThem(): void
    {
        $controller = (new ReflectionClass(ReleaseController::class))->newInstanceWithoutConstructor();
        $method = new \ReflectionMethod($controller, 'resortChangelog');
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
}
