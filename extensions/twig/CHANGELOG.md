Yii Framework 2 twig extension Change Log
=========================================

2.0.0-rc under development
--------------------------

- Bug #2925: Fixed throwing exception when accessing AR property with null value (samdark)
- Bug #3767: Fixed repeated adding of extensions when using config. One may now pass extension instances as well (grachov)
- Bug #3877: Fixed `lexerOptions` throwing exception (dapatrese)
- Bug #4290: Fixed throwing exception when trying to access AR relation that is null (samdark, tenitski)
- Enh #1799: Added `form_begin`, `form_end` to twig extension (samdark)
- Enh #3674: Various enhancements (samdark)
    - Removed `FileLoader` and used `\Twig_Loader_Filesystem` instead.
    - Added support of Yii's aliases.
    - Added `set()` that allows setting object properties.
- Chg #3535: Syntax changes:
    - Removed `form_begin`, `form_end` (samdark)
    - Added `use()` and `ViewRenderer::uses` that are importing classes and namespaces (grachov, samdark)
    - Added widget dynamic functions `*_begin`, `*_end`, `*_widget`, `widget_end` (grachov, samdark)
    - Added more tests (samdark)
- Chg: Renamed `TwigSimpleFileLoader` into `FileLoader` (samdark)

2.0.0-beta April 13, 2014
-------------------------

- Added file based Twig loader for better caching and usability of Twig's file based functions (dev-mraj, samdark)

2.0.0-alpha, December 1, 2013
-----------------------------

- Initial release.
