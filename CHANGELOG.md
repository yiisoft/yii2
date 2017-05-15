# 版本修改历史

- Enh 调整BaseActiveRecord对旧版本的处理方式，记录更改的记录属性而非保留全部旧属性
- Enh 默认BaseActiveRecord的insert，update，delete3个场景
- BUG ActiveQeuryTrait在采用indexby时，当遇到mongodb的对象主键时出错