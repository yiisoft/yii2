Upgrading Instructions for Yii Framework v2
===========================================

!!!IMPORTANT!!!

The following upgrading instructions are cumulative. That is,
if you want to upgrade from version A to version C and there is
version B between A and C, you need to following the instructions
for both A and B.


General upgrade instructions
----------------------------

- Make a backup.
- Clean up your 'assets' folder.
- Replace 'framework' dir with the new one or point Git to a fresh
  release tag and checkout.
- Check if everything is OK, if not — revert to previous stable version and post
  issues to [Yii issue tracker](https://github.com/yiisoft/yii2/issues).


Upgrading from v1.1.x
---------------------

- All framework classes are now namespaced, and the name prefix `C` is removed.

- The format of path alias is changed to `@yii/base/Component`.
  In 1.x, this would be `system.base.CComponent`. See guide for more details.

- The root alias `@yii` now represents the framework installation directory.
   In 1.x, this is named as `system`. We also removed `zii` root alias.

- `Object` serves as the base class that supports properties. And `Component` extends
  from `Object` and supports events and behaviors. Behaviors declared in
  `Component::behaviors()` are attached on demand.

- `CList` is renamed to `Vector`, and `CMap` is renamed to `Dictionary`.
  Other collection classes are dropped in favor of SPL classes.

- `CFormModel` is removed. Please use `yii\base\Model` instead.

- `CDbCriteria` is replaced by `yii\db\Query` which includes methods for
  building a query. `CDbCommandBuilder` is replaced by `yii\db\QueryBuilder`
  which has cleaner and more complete support of query building capabilities.

