<?php

namespace yiiunit;

use yii\base\NotSupportedException;
use Yii;

/**
 * This is the base class for all yii framework unit tests, which requires
 * external vendor libraries to function.
 */
class VendorTestCase extends TestCase
{
	/**
	 * This method is called before the first test of this test class is run.
	 * Attempts to load vendor autoloader.
	 * @throws \yii\base\NotSupportedException
	 */
	public static function setUpBeforeClass()
	{
		$vendorDir = __DIR__ . '/../../vendor';
		if (!is_dir($vendorDir)) {
			// this is used by `yii2-dev`
			$vendorDir = __DIR__ . '/../../../../../vendor';
		}
		Yii::setAlias('@vendor', $vendorDir);
		$vendorAutoload = $vendorDir . '/autoload.php';
		if (file_exists($vendorAutoload)) {
			require_once($vendorAutoload);
		} else {
			throw new NotSupportedException("Vendor autoload file '{$vendorAutoload}' is missing.");
		}
	}
}
