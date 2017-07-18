Trabalhando com Formulários
===========================

Nesta seção descreve como se cria uma nova página com um formulário para obter
dados a partir dos usuários. A página exibirá uma formulário com um campo para 
o nome e uma para o e-mail. Depois de obter essas duas informações a partir do 
usuário, a página exibirá os valores inseridos de volta para a confirmação.

Para atingir este objetivo, além de criar uma [ação](structure-controllers.md) (action) e 
duas [visões](structure-views.md) (view), você também criará uma [modelo](structure-models.md) (model).

Através deste tutorial, você aprenderá como:

* Criar um [modelo](structure-models.md) (model) para representar os dados inseridos pelo usuário por meio de um formulário
* Declarar regras (rules) para validar os dados inseridos
* Criar um formulário HTML em uma [visão](structure-views.md) (view)


Criando uma Modelo (Model) <span id="creating-model"></span>
----------------

Os dados a serem solicitados pelo usuário será representados por uma classe modelo
`EntryForm` como mostro a seguir e salvos no arquivo `models/EntryForm.php`. Por 
favor consulte a seção [Autoloading de Classes](concept-autoloading.md) para mais 
detalhes sobre convenção de nomenclatura dos arquivos de classes.

```php
<?php

namespace app\models;

use Yii;
use yii\base\Model;

class EntryForm extends Model
{
    public $name;
    public $email;

    public function rules()
    {
        return [
            [['name', 'email'], 'required'],
            ['email', 'email'],
        ];
    }
}
```

A classe estende de [[yii\base\Model]], uma classe base fornecida pelo Yii, 
comumente usados para representar dados do formulário.

> Informação: O [[yii\base\Model]] é usado como pai das classes modelos que *não* 
são associadas com tabelas do banco de dados.
O [[yii\db\ActiveRecord]] é normalmente usado como pai das classes modelos que 
correspondem a tabelas do banco de dados.

A classe `EntryForm` contém dois atributos públicos, `name` e `email`, que são 
usados para guardar os dados fornecidos pelo usuário. Ele também contém um método
chamado `rules()`, que retorna um conjunto de regras para validação dos dados. 
As regras de validação declaradas no código acima permitem que:

* tanto os valores do `name` quanto do `email` sejam obrigatórios
* os dados do `email` devem ser um e-mail válido sintaticamente

Se você tiver um objeto `EntryForm` populado com dados fornecidos pelo usuário,
você pode chamar o [[yii\base\Model::validate()|validate()]] para iniciar as 
rotinas de validação dos dados. A validação dos dados falhar, a propriedade 
[[yii\base\Model::hasErrors|hasErrors]] será definida como *true* e você pode
saber quais erros ocorrerão pela validação através de [[yii\base\Model::getErrors|errors]].

```php
<?php
$model = new EntryForm();
$model->name = 'Qiang';
$model->email = 'bad';
if ($model->validate()) {
    // Bom!
} else {
    // Falha!
    // Utilize $model->getErrors()
}
```


Criando uma Ação <span id="creating-action"></span>
------------------

Em seguida, você precisará criar uma ação `entry` no controlador `site` que será usado no novo modelo. O processo de criação e utilização das ações são explicadas na seção 
[Como Fazer um "Hello World"](start-hello.md).

```php
<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use app\models\EntryForm;

class SiteController extends Controller
{
    // ...código existente...

    public function actionEntry()
    {
        $model = new EntryForm();

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            // dados válidos recebidos pelo $model

            // fazer alguma coisa aqui sobre $model ...

            return $this->render('entry-confirm', ['model' => $model]);
        } else {
            // Ou a página é exibida inicialmente ou existe algum erro de validação
            return $this->render('entry', ['model' => $model]);
        }
    }
}
```

A primeira ação cria um objeto `EntryForm`. Ele, então, tenta popular o modelo 
(model) com os dados vindos do `$_POST`, fornecidos pelo [[yii\web\Request::post()]]
no Yii. Se o modelo (model) for populado com sucesso (por exemplo, se o usuário
enviar o formulário HTML), a ação chamará o [[yii\base\Model::validate()|validate()]] 
para certifique-se que os valores fornecidos são válidos.

> Informação: A expressão `Yii::$app` representa a instância da 
  [aplicação](structure-applications.md), que é globalmente acessível via singleton. 
  Também é um [service locator](concept-service-locator.md) que fornece componentes 
  tais como `request`, `response`, `db`, etc. para suportar a funcionalidade específica.
  No código acima, o componente `request` da instância da aplicação é usada para
  acessar os dados do `$_POST`.

Se tudo tiver certo, a ação renderizará a visão chamada `entry-confirm` para 
confirmar os dados enviados pelo usuário. Se ao enviar o formulário não 
houver dados ou se os dados tiverem erros, a visão `entry` será renderizada, 
em que o formulário será exibigo, juntamente com as mensagens de erros da 
validação.

> Nota: Neste exemplo muito simples, acabamos de renderizar um página de confirmação
  mediante a dados válidos enviados de uma formulário. Em prática, você poderia 
  considerar usar [[yii\web\Controller::refresh()|refresh()]] ou [[yii\web\Controller::redirect()|redirect()]]
  para evitar [problemas ao reenviar formulários](http://en.wikipedia.org/wiki/Post/Redirect/Get).


Criando Visões <span id="creating-views"></span>
--------------

Finalmente, crie dois arquivos de visões chamados de `entry-confirm` e `entry`. 
Estas visões serão renderizados pela ação `entry`, como descrito anteriormente.

A visão `entry-confirm` simplesmente exibe os dados dos campos `name` e `email`. 
Deverá ser salvo no arquivo `views/site/entry-confirm.php`.

```php
<?php
use yii\helpers\Html;
?>
<p>You have entered the following information:</p>

<ul>
    <li><label>Name</label>: <?= Html::encode($model->name) ?></li>
    <li><label>Email</label>: <?= Html::encode($model->email) ?></li>
</ul>
```

A visão `entry` exibe um formulário HTML. Deverá ser salvo no arquivo `views/site/entry.php`.

```php
<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
?>
<?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'name') ?>

    <?= $form->field($model, 'email') ?>

    <div class="form-group">
        <?= Html::submitButton('Submit', ['class' => 'btn btn-primary']) ?>
    </div>

<?php ActiveForm::end(); ?>
```

A visão usa o poderoso [widget](structure-widgets.md) chamado 
[[yii\widgets\ActiveForm|ActiveForm]] para criar o formulário HTML. Os métodos 
`begin()` e `end()` do widget renderizam a abertura e o fechamento da tag do 
formulário. Entre os dois métodos chamados, os campos de entrada são criados pelo
método [[yii\widgets\ActiveForm::field()|field()]]. O primeiro campo de entrada
é para o "name" (nome) e o segundo é para o "email". Após os campos de entrada,
o método [[yii\helpers\Html::submitButton()]] é chamado para criar o botão 
de enviar.


Testando <span id="trying-it-out"></span>
-------------

Para ver como ele funciona, utilize seu navegador para acessar a seguinte URL:

```
http://hostname/index.php?r=site/entry
```

Você verá uma página exibindo um formulário com dois campos de entrada. Na frente 
de cada campo, um *label* indicando quais dados devem ser inseridos. Se você clicar
no botão de enviar sem informar nenhum dado, ou se você não fornecer um e-mail 
válido, você verá uma mensagem de erro após cada campo de entrada.

![Form with Validation Errors](images/start-form-validation.png)

Após informar um nome e e-mail válidos e clicar no botão de enviar, você verá uma 
nova página exibindo os dados informados por você.

![Confirmation of Data Entry](images/start-entry-confirmation.png)



### Explicação da Mágica <span id="magic-explained"></span>

Você pode querer saber como o formulário HTML trabalha por baixo dos panos, porque 
parece quase mágica exibir um *label* para cada campo de entrada e mostrar mensagens
de erro quando você não informa dados corretos sem recarregar a página.

Sim, a validação dos dados inicialmente é feito no lado do cliente usando JavaScript
e posteriormente realizada no lado do servidor via PHP.
O [[yii\widgets\ActiveForm]] é inteligente o suficiente para extrair as regras de 
validação declaradas no `EntryForm`, transformando-as em códigos JavaScript e utilizando 
o JavaScript para realizar as validações dos dados. No caso do JavaScript estiver desabilitado
em seu navegador, a validação ainda será realizada pelo lado do servidor, como mostrado
no método `actionEntry()`. Isso garante que os dados serão validados em qualquer 
circunstância.

> Aviso: A validação feita pelo lado do cliente é uma conveniência que fornece uma melhor
  experiência para o usuário. A validação feita pelo lado do servidor é sempre necessária
  com ou sem validação no lado do cliente.

Os *labels* dos campos de entrada são geradas pelo método `field()`, usando os nomes 
das propriedades do modelo (model).
Por exemplo, um *label* chamado `Name` será gerado para a propriedade `name`. 

Você pode personalizar um *label* em uma visão utilizando o seguinte código:

```php
<?= $form->field($model, 'name')->label('Your Name') ?>
<?= $form->field($model, 'email')->label('Your Email') ?>
```

> Informação: O Yii fornece muitos destes widgets para lhe ajudar a criar rapidamente
  complexas e dinâmicos layouts.
  Como você vai aprender mais tarde, escrever um novo widget é também extremamenet fácil.
  Você pode querer transformar grande parte do seu código de visão em reutilizáveis 
  widget para simplificar o desenvolvimento de visões no futuro.

 
Resumo <span id="summary"></span>
-------

Nesta seção, você tocou em cada parte do padrão de arquitetura MVC. 
Aprendeu como criar uma classe modelo (model) para representar os dados do usuário 
e validá-los.

Também aprendeu como obter os dados enviados pelos usuários e como exibi-los de
volta no navegador. Esta é uma tarefa que poderia levar muito tempo ao desenvolver
uma aplicação, mas o Yii fornece widgets inteligentes para gerar estas tarefas de 
forma simples.
Na próxima seção, você aprenderá como trabalhar com banco de dados, os quais são 
necessários em quase todas as aplicações.
