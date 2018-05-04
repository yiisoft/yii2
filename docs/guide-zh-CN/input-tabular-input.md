收集列表输入
=============

有时你需要在一个表单中以单一的形式处理多个模型。例如，有多个设置，
每个设置存储为一个 name-value，并通过 `Setting` [活动记录](db-active-record.md)
模型来表示。这种形式也常被称为“列表输入”。与此相反，
处理不同模型的不同类型，在
[多模型同时输入](input-multiple-models.md)章节中介绍。

下面展示了如何在 Yii 中收集列表输入。

在三种不同的情况下，所需处理的略有不同：
- 从数据库中更新一组固定的记录
- 创建一个动态的新记录集
- 更新、创建和删除一页记录

与之前介绍的单一模型表单相反，我们现在用的是一个数组类的模型。这个数组将
每个模型传递到视图并以一种类似于表格的方式来显示表单字段。
我们使用 [[yii\base\Model]] 助手类方法来一次性地加载和验证多模型数据：

- [[yii\base\Model::loadMultiple()|Model::loadMultiple()]] 将数据加载到一个数组中。
- [[yii\base\Model::validateMultiple()|Model::validateMultiple()]] 验证一系列模型。

### 更新一组固定的记录

让我们从控制器的动作开始：

```php
<?php

namespace app\controllers;

use Yii;
use yii\base\Model;
use yii\web\Controller;
use app\models\Setting;

class SettingsController extends Controller
{
    // ...

    public function actionUpdate()
    {
        $settings = Setting::find()->indexBy('id')->all();

        if (Model::loadMultiple($settings, Yii::$app->request->post()) && Model::validateMultiple($settings)) {
            foreach ($settings as $setting) {
                $setting->save(false);
            }
            return $this->redirect('index');
        }

        return $this->render('update', ['settings' => $settings]);
    }
}
```

在上面的代码中，当用模型来从数据库获取数据时，我们使用 [[yii\db\ActiveQuery::indexBy()|indexBy()]] 
来让模型的主键成为一个数组的索引。其中 [[yii\base\Model::loadMultiple()|Model::loadMultiple()]] 
用于接收以 POST 方式提交的表单数据并填充多个模型，
[[yii\base\Model::validateMultiple()|Model::validateMultiple()]] 一次验证多个模型。
正如我们之前验证的模型，使用了 `validateMultiple()`，现在通过传递 `false` 
作为 [[yii\db\ActiveRecord::save()|save()]]的一个参数使其不会重复验证两次。

现在在 `update` 视图的表单：

```php
<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;

$form = ActiveForm::begin();

foreach ($settings as $index => $setting) {
    echo $form->field($setting, "[$index]value")->label($setting->name);
}

ActiveForm::end();
```

在这里，我们为每个设置渲染了名字和一个带值的输入。重要的是给 input name 增加添加适当的索引，
因为这是由 [[yii\base\Model::loadMultiple()|Model::loadMultiple()]] 来决定以哪些值来填补哪个模型。

### 创建一组动态的新记录

创造新的记录与修改记录很相似，除部分实例化模型不同之外：

```php
public function actionCreate()
{
    $count = count(Yii::$app->request->post('Setting', []));
    $settings = [new Setting()];
    for($i = 1; $i < $count; $i++) {
        $settings[] = new Setting();
    }

    // ...
}
```

在这里，我们创建了一个初始的 `$settings` 数组包含一个默认的模型，所以始终至少有一个文本字段是可见的。
此外，我们为每个可能会收到的输入行添加更多的模型。

在视图中，可以使用 JavaScript 来动态地添加新的输入行。

### 把更新，创建和删除结合在一个页面上

> Note: 此章节正在开发中。
>
> 还没有内容。

TBD
