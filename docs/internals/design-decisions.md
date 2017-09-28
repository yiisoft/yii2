Design Decisions
================

This document lists the design decisions that we have made after extensive discussions. Unless there are very strong
reasons, these decisions should be kept for consistency. Any change to these decisions should get agreement among
the core developers.

1. **[When to support path aliases](https://github.com/yiisoft/yii2/pull/3079#issuecomment-40312268)**
   we should support path alias for properties that are configurable because using path aliases in configurations 
   are very convenient. In other cases, we should restrict the support for path aliases.
2. **When to translate messages**
   messages should be translated when these are displayed to non-tech end user and make sense to him. HTTP status messages,
   exceptions about the code etc. should not be translated. Console messages are always in English because of encoding
   and codepage handling difficulties.
3. **[Adding new auth client support](https://github.com/yiisoft/yii2/issues/1652)**
   For better maintainability, we will not add any additional auth clients to the core extension. They should be done 
   in terms of user extensions. 
4. **When using closures** it is recommended to **include all passed parameters** in the signature even if not all of them are
   used. This way modifying or copying code is easier because all information is directly visible and it is not necessary to
   look up which params are actually available in the documentation. ([#6584](https://github.com/yiisoft/yii2/pull/6584), [#6875](https://github.com/yiisoft/yii2/issues/6875))
5. Prefer **int over unsigned int** in database schema. Using int has the benefit that it can be represented in PHP as an integer.
   If unsigned, for 32 bit system, we would have to use string to represent it.
   Also although unsigned int doubles the size, if you have a table that needs such big number space,
   then it's safer to use bigint or mediumint rather than relying on unsigned.
   <https://github.com/yiisoft/yii/pull/1923#issuecomment-11881967>
6. [Helpers vs separate non-static classes](https://github.com/yiisoft/yii2/pull/12661#issuecomment-251599463)
7. **Setters method chaining** should be avoided if there are methods in the classs returning meaningful values. Chaining could be
   supported if a class is a builder where all setters are modifying internal state: https://github.com/yiisoft/yii2/issues/13026
8. **Global exception/error handler** is used instead of local try-catch because it is reliable in terms of catching destructors and everything that happens outside the scope of the `run()` method such as bootstrap. See [#14348](https://github.com/yiisoft/yii2/issues/14348).
