This folder contains official Yii 2 extensions.

To add a new extension named `xyz` (must be in lower case), take the following steps:

1. create a folder named `xyz` under `yii` and put all relevant source code there;
2. create the following accessory files (please refer to any existing extension):
   * `composer.json`
   * `README.md`
   * `CHANGELOG.md`
   * `LICENSE.md`
3. ask Qiang to create a subsplit for `xyz` and a composer package named `yii2-xyz`;
4. If an extension depends on external bower/npm packages:
   * in the `composer.json` file of the extension, list the dependencies in the format of `'bower-asset/PackageName': '1.1'`;
   * create an asset bundle class to list the needed js/css files from the package. The `sourcePath`
     property of the bundle should point to the distribution path of the package, such as
     `@bower/PackageName`, or `@bower/PackageName/dist`.
5. modify `/composer.json` and add `yiisoft/yii2-xyz` to the `replace` section. Also add any bower/npm
   dependencies to the `require` section.
