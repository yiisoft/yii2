Yii Framework 2 redis extension Change Log
==========================================

2.0.0 beta under development
----------------------------

- Bug #1993: afterFind event in AR is now called after relations have been populated (cebe, creocoder)
- Enh #1773: keyPrefix property of Session and Cache is not restricted to alnum characters anymore (cebe)
- Chg #2281: Renamed `ActiveRecord::create()` to `populateRecord()` and changed signature. This method will not call instantiate() anymore (cebe)

2.0.0 alpha, December 1, 2013
-----------------------------

- Initial release.
