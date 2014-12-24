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
   For better maintenability, we will not add any additional auth clients to the core extension. They should be done 
   in terms of user extensions. 
