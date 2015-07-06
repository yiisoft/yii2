Yii Versioning
==============

This document summarizes the versioning policy of Yii. Our current versioning strategy can be
described as [ferver](https://github.com/jonathanong/ferver), which we considered is more practical
and reasonable than [Semantic Versioning](http://semver.org/) (See [#7408](https://github.com/yiisoft/yii2/issues/7408) for more references).

Within the core developer team, we have emphasized several times that it is important to keep 2.0.x releases 100% BC-compatible.
But this is an ideal plan. The ferver article has given out a real world example that this is hard to achieve in practice,
regardless you are using semver or not.

In summary, our versioning policy is as follows:

## Patch Releases `2.x.Y`

Patch releases, which should be 100% BC-compatible. Ideally, we hope they contain bug fixes only so that it reduces
the chance of breaking BC. Practically, since 2.0.x is released more frequently, we are also adding minor features
to it so that users can enjoy them earlier.

* Maintained on a branch named `2.x`
* Mainly contain bug fixes and minor feature enhancements
* No major features.
* Must be 100% backward compatible to ensure worry-free upgrade. Only exception is security issues that may require breaking BC.
* Release cycle is around 1 to 2 months.
* No pre-releases (alpha, beta, RC) needed.
* Should be merged back to master branch constantly (at least once every week manually).


## Minor Releases `2.X.0`

BC-breaking releases, which contains major features and changes that may break BC. Upgrading from earlier versions may
not be trivial, but a complete upgrade guide or even script will be available.

* Developed on master branch
* Mainly contain new features and bug fixes
* Contain minor features and bug fixes merged from patch releases
* May contain BC-breaking changes which are recorded in `UPGRADE-2.X.md` file
* Release cycle is around 6 to 8 months
* Require pre-releases: `2.X.0-alpha`, `2.X.0-beta`, `2.X.0-rc`
* Requires major news releases and marketing effort.


## Major Releases `X.0.0`

It's like 2.0 over 1.0. We expect this only happens every 3 to 5 years, depending on external technology advancement
(such as PHP upgraded from 5.0 to 5.4).

> Note: Official extensions are following the same versioning policy but could be released independently from
the framework i.e. version number mismatch between framework and extension is expected.
