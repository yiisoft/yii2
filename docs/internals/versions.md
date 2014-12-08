Yii Versioning
==============

This document summarizes the versioning policy of Yii. In general, Yii follows the [Semantic Versioning](http://semver.org/).

## Patch Releases `2.x.Y`
 
* Maintained on a branch named `2.x`
* Mainly contain bug fixes and minor feature enhancements
* No major features.
* Must be 100% backward compatible to ensure worry-free upgrade. Only exception is security issues that may require breaking BC.
* Release cycle is around 1 to 2 months.
* No pre-releases (alpha, beta, RC) needed.
* Should be merged back to master branch constantly (at least once every week manually).


## Minor Releases `2.X.0`

* Developed on master branch
* Mainly contain new features and bug fixes
* Contain minor features and bug fixes merged from patch releases
* May contain BC-breaking changes which are recorded in `UPGRADE-2.X.md` file
* Release cycle is around 6 to 8 months
* Require pre-releases: `2.X.0-alpha`, `2.X.0-beta`, `2.X.0-rc`
* Requires major news releases and marketing effort.


## Major Releases `X.0.0`

None in plan. 
