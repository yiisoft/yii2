Design Decisions
================

This document lists the design decisions that we have made after extensive discussions. Unless there are very strong
reasons, these decisions should be kept for consistency. Any change to these decisions should get agreement among
the core developers.

1. **[When to support path aliases](https://github.com/yiisoft/yii2/pull/3079#issuecomment-40312268)**
   we should support path alias for properties that are configurable because using path aliases in configurations 
   are very convenient. In other cases, we should restrict the support for path aliases.
