This folder contains official Yii 2 extensions.

To add a new extension named `xyz` (must be in lower case), take the following steps:

1. create a folder named `xyz` under `yii` and put all relevant source code there;
2. create the following accessory files (please refer to any existing extension):
   * `composer.json`
   * `README.md`
   * `CHANGELOG.md`
   * `LICENSE.md`
3. ask Qiang to create a subsplit for `xyz` and a composer package named `yii2-xyz`;
4. modify `/composer.json` and add `xyz` to the `replace` section;
