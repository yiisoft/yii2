Project Organization
====================

This document describes the organization of the Yii 2 development repositories.
 
1. Individual Core extensions and application templates are maintained in
   separate *independent* GitHub projects under the [yiisoft](https://github.com/yiisoft) Github organization.
    
   Extension repository names are prefixed with `yii2-`, e.g. `yii2-gii` for the `gii` extension.
   The composer package name is equal to the Github repository path, e.g. `yiisoft/yii2-gii`.
   
   Application template repository names are prefixed with `yii2-app-`, e.g. `yii2-app-basic` for the `basic` application template.
   The composer package name is equal to the Github repository path, e.g. `yiisoft/yii2-app-basic`.
   
   Each extension/app project will
 
   * maintain its tutorial doc in its "docs" folder. The API doc will be generated on-the-fly when the extension/app is being released.
   * maintain its own test code in its "tests" folder.
   * maintain its own message translations and all other relevant meta code.
   * track issues via the corresponding GitHub project.
      
   Extension repositories will be released independently as needed, Application templates will be released together with the framework.
   See [versioning policy](versions.md) for more details.

2. The `yiisoft/yii2` project is the main repository for developing Yii 2 framework.
   This repository provides the composer package [yiisoft/yii2-dev](https://packagist.org/packages/yiisoft/yii2-dev).
   It contains the core framework code, framework unit tests, the definitive guide, and a set of build tools for framework development and release.
   
   Core framework bugs and feature requests are tracked in the issue tracker of this Github project.
   
3. The repository `yiisoft/yii2-framework` is a read-only git subsplit of the `framework` directory of the dev project repository and
   provides the composer package [yiisoft/yii2](https://packagist.org/packages/yiisoft/yii2) which is the official package to be
   used when installing the framework.

4. For development the apps and extensions can be included in the dev project structure using the
   [build dev/app](git-workflow.md#prepare-the-test-environment)-Command.