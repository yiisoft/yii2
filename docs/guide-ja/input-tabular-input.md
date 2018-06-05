表形式インプットでデータを収集する
==================================

時として、一つのフォームで同じ種類の複数のモデルを扱わなければならないことがあります。
例えば、それぞれが「名前-値」の形で保存され、`Setting` [アクティブ・レコード](db-active-record.md)
モデルとして表される複数の設定項目を扱うフォームです。
この種のフォームは「表形式インプット」と呼ばれることもよくあります。
これとは対照的な、異なる種類のさまざまなモデルを扱うことについては、[複数のモデルを持つ複雑なフォーム](input-multiple-models.md) のセクションで扱います。

以下に、表形式インプットを Yii で実装する方法を示します。

カバーすべき三つの異なる状況があり、それぞれ少しずつ異なる処理をしなければなりません。
- 特定の数のデータベース・レコードを更新する
- 不特定の数の新しいレコードを作成する
- 一つのページでレコードを更新、作成、および、削除する

前に説明した単一モデルのフォームとは対照的に、モデルの配列を扱うことになります。
この配列がビューに渡されて、各モデルのためのインプット・フィールドが表のような形式で表示されます。
そして、複数のモデルを一度にロードしたり検証したりするために [[yii\base\Model]] のヘルパ・メソッドを使用します。

- [[yii\base\Model::loadMultiple()|Model::loadMultiple()]] - 送信されたデータをモデルの配列にロードします。
- [[yii\base\Model::validateMultiple()|Model::validateMultiple()]] - モデルの配列を検証します。

### 特定の数のレコードを更新する

コントローラのアクションから始めましょう。

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

上記のコードでは、データベースからモデルを読み出すときに [[yii\db\ActiveQuery::indexBy()|indexBy()]] を使って、
モデルのプライマリ・キーでインデックスされた配列にデータを投入しています。このインデックスが、後で、
フォーム・フィールドを特定するために使われます。[[yii\base\Model::loadMultiple()|Model::loadMultiple()]] が
POST から来るフォーム・データを複数のモデルに代入し、[[yii\base\Model::validateMultiple()|Model::validateMultiple()]] が全てのモデルを一度に検証します。
保存するときには、`validateMultiple()` を使ってモデルの検証を済ませていますので、[[yii\db\ActiveRecord::save()|save()]]
のパラメータに `false` を渡して、二度目の検証を実行しないようにしています。

次に、`update` ビューの中にあるフォームです。

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

ここで全ての設定項目について、それぞれ、項目名を示すラベルと、項目の値を入れたインプットをレンダリングしています。
インプットの名前に適切なインデックスを追加することが肝腎です。というのは、`loadMultiple` がそれを見て、どのモデルにどの値を代入するかを決定するからです。

### 不特定の数の新しいレコードを動的に作成する

新しいレコードを作成するのは、モデルのインスタンスを作成する部分を除いて、更新の場合と同じです。

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

ここでは、デフォルトで一個のモデルを含む `$settings` 配列を初期値として作成し、少なくとも一個のテキスト・フィールドが常にビューに表示されるようにしています。
そして、受信したインプットの行数に合せて、配列にモデルを追加しています。

ビューでは javascript を使ってインプットの行を動的に追加することが出来ます。

### 更新、作成、削除を一つのページに組み合わせる

> Note: このセクションはまだ執筆中です。
>
> まだ内容がありません。

(未定)
