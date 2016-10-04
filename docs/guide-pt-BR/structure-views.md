Views (Visões)
===========

As views (visões) fazem parte da arquitetura [MVC](http://en.wikipedia.org/wiki/Model%E2%80%93view%E2%80%93controller).
São os responsáveis por apresentar dados aos usuários finais. Em um aplicação Web, 
as views (visões) normalmente são criadas sobre o termo de *view templates* 
(modelos de visão) que são arquivos PHP contendo principalmente códigos HTML e 
códigos PHP de apresentação.
A gerência deles são realizadas pelo [componente da aplicação](structure-application-components.md) 
[[yii\web\View|view]] na qual fornece métodos comumente utilizados para facilitar 
a montagem e a renderização da view (visão). Para simplificar, chamaremos sempre 
os arquivos view templates (modelos de visão) ou view template (modelo de visão) 
apenas como views (visões).


## Criando Views (Visões) <span id="creating-views"></span>

Como mencionado anteriormente, uma view (visão) é simplesmente um arquivo PHP 
composto por HTML ou códigos PHP. A view (visão) a seguir, apresenta um formulário 
de login. Como você pode observar, o código PHP geralmente é utilizado para gerar 
conteúdo dinâmico, tais como o título da página e o formulário, enquanto o código 
HTML é utilizado para deixar a página mais apresentável.
 
```php
<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $form yii\widgets\ActiveForm */
/* @var $model app\models\LoginForm */

$this->title = 'Login';
?>
<h1><?= Html::encode($this->title) ?></h1>

<p>Please fill out the following fields to login:</p>

<?php $form = ActiveForm::begin(); ?>
    <?= $form->field($model, 'username') ?>
    <?= $form->field($model, 'password')->passwordInput() ?>
    <?= Html::submitButton('Login') ?>
<?php ActiveForm::end(); ?>
```

Em uma view (visão), você pode acessar a variável `$this` que referencia o 
[[yii\web\View|componente da view (visão)]] para gerenciar e renderizar a view 
(visão) atual.

Além da variável `$this`, podem existir outras variáveis predefinidas na view 
(visão) como a variável `$model` do exemplo anterior. Estas variáveis representam 
os dados que foram informados a view (visão) por meio dos 
[controllers (controladores)](structure-controllers.md) ou de outros objetos que 
desencadeiam a [renderização da view (visão)](#rendering-views). 

> Dica: As variáveis predefinidas são listadas em um bloco de comentário no inicio 
  de uma view (visão), de modo que possam ser reconhecidas pelas IDEs. Além de ser 
  uma ótima maneira de documentar suas views (visões).


### Segurança <span id="security"></span>

Ao criar views (visões) que geram páginas HTML, é importante que você codifique 
ou filtre dados obtidos pelos usuários antes que os apresente. Caso contrário, 
sua aplicação poderá estar sujeita a sofrer um ataque 
[cross-site scripting](http://en.wikipedia.org/wiki/Cross-site_scripting).

Para exibir um texto simples, codifique-o antes chamando o método 
[[yii\helpers\Html::encode()]]. Por exemplo, o código a seguir codifica o nome do 
usuário antes que seja exibido:

```php
<?php
use yii\helpers\Html;
?>

<div class="username">
    <?= Html::encode($user->name) ?>
</div>
```

Para exibir um conteúdo HTML, utilize o método [[yii\helpers\HtmlPurifier]] para 
filtrar o conteúdo primeiro. Por exemplo, o código a seguir filtra o conteúdo que 
foi postado antes que seja exibido:

```php
<?php
use yii\helpers\HtmlPurifier;
?>

<div class="post">
    <?= HtmlPurifier::process($post->text) ?>
</div>
```

> Dica: Enquanto o HTMLPurifier faz um excelente trabalho ao montar uma saída 
  segura, ele não é rápido. Você pode considerar guardar o [cache](caching-overview.md)
  do resultado filtrado caso sua aplicação necessite de o máximo de performance. 


### Organizando as Views (Visões) <span id="organizing-views"></span>

Assim como os [controllers (controladores)](structure-controllers.md) e os 
[models (modelos)](structure-models.md), existem convenções para organizar as 
views (visões).

* Para que as views (visões) sejam renderizadas por um controller (controlador), 
  elas devem ser colocadas sob o diretório `@app/views/IDdoController` por padrão, 
  onde o `IDdoController` refere-se ao [ID do controller](structure-controllers.md#routes). 
  Por exemplo, se a classe controller (controlador) for `PostController`, o diretório 
  será `@app/views/post`; se for `PostCommentController`, o diretório será 
  `@app/views/post-comment`. No caso do controller (controlador) pertencer a um 
  módulo, o diretório será `views/ControllerID` sob o [[yii\base\Module::basePath|diretório do módulo]].
* Para as views (visões) que serão renderizadas por um [widget](structure-widgets.md),
  devem ser colocadas sob o diretório `WidgetPath/views` por padrão, onde `WidgetPath` 
  é o diretório onde encontra-se o arquivo da classe widget.
* Para as views (visões) que serão renderizadas por outros objetos, é recomendado 
  que siga a convenção semelhante à dos widgets.

Você pode personalizar estes diretórios padrão das views (visões) sobrescrevendo 
o método [[yii\base\ViewContextInterface::getViewPath()]] dos controllers 
(controladores) ou dos widgets.


## Renderizando Views (Visões) <span id="rendering-views"></span>

A renderização das views (visões) podem ser feitas nos 
[controllers (controladores)](structure-controllers.md), nos 
[widgets](structure-widgets.md) ou em qualquer lugar que chame os métodos de 
renderização de views (visões),

```
/**
 * @param string $view nome da view ou do caminho do arquivo, dependendo do método de renderização atual
 * @param array $params os dados que serão passados para a view (visão)
 * @return string resultado da renderização
 */
methodName($view, $params = [])
```


### Renderização pelos Controllers (Controladores) <span id="rendering-in-controllers"></span>

Nos [controllers (controladores)](structure-controllers.md), você pode chamar os 
seguintes métodos para renderizar as views (visões):

* [[yii\base\Controller::render()|render()]]: resulta na renderização de uma 
  [view nomeada](#named-views) aplicada em um [layout](#layouts).
* [[yii\base\Controller::renderPartial()|renderPartial()]]: resulta na renderização 
  de uma [view nomeada](#named-views) sem que seja aplicada em qualquer layout.
* [[yii\web\Controller::renderAjax()|renderAjax()]]: resulta na renderização de 
  uma [view nomeada](#named-views) sem que seja aplicada em qualquer layout mas 
  inclui todos os JS/CSS e arquivos registrados. Este método é utilizado 
  frequentemente nas respostas de requisições Web Ajax.
* [[yii\base\Controller::renderFile()|renderFile()]]: renderiza uma view a partir 
  de um caminho de um arquivo ou por uma [alias](concept-aliases.md).
* [[yii\base\Controller::renderContent()|renderContent()]]: renderiza um conteúdo 
  estático que será incorporado no [layout](#layouts) selecionado. Este método 
  está disponível desde a versão 2.0.1.

Por exemplo,

```php
namespace app\controllers;

use Yii;
use app\models\Post;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

class PostController extends Controller
{
    public function actionView($id)
    {
        $model = Post::findOne($id);
        if ($model === null) {
            throw new NotFoundHttpException;
        }

        // renderiza uma view chamada de "view" e aplica um layout nele
        return $this->render('view', [
            'model' => $model,
        ]);
    }
}
```


### Renderização pelos Widgets <span id="rendering-in-widgets"></span>

Nos [widgets](structure-widgets.md), você pode chamar os seguintes métodos do 
widget para renderizar views (visões).

* [[yii\base\Widget::render()|render()]]: resulta na renderização de uma 
  [view nomeada](#named-views).
* [[yii\base\Widget::renderFile()|renderFile()]]: renderiza uma view a partir de 
  um caminho de um arquivo ou por uma [alias](concept-aliases.md).

Por exemplo,

```php
namespace app\components;

use yii\base\Widget;
use yii\helpers\Html;

class ListWidget extends Widget
{
    public $items = [];

    public function run()
    {
        // renderiza uma view chamada "list"
        return $this->render('list', [
            'items' => $this->items,
        ]);
    }
}
```


### Renderização pelas Views (Visões) <span id="rendering-in-views"></span>

Você pode renderizar uma view dentro de outra view chamando um dos seguintes 
métodos fornecidos pelo [[yii\base\View|componente da view]]:

* [[yii\base\View::render()|render()]]: resulta na renderização de uma 
  [view nomeada](#named-views).
* [[yii\web\View::renderAjax()|renderAjax()]]: resulta na renderização de uma 
  [view nomeada](#named-views) e inclui todos os JS/CSS e arquivos registrados. 
  Este método é utilizado frequentemente nas respostas de requisições Web Ajax.
* [[yii\base\View::renderFile()|renderFile()]]: renderiza uma view a partir de 
  um caminho de um arquivo ou por uma [alias](concept-aliases.md).

Por exemplo, no código a seguir, uma view (visão) qualquer renderiza outro arquivo 
de view (visão) chamado `_overview.php` onde ambas encontram-se no mesmo diretório. 
Lembre-se que a `$this` da view (visão) refere-se ao componente da [[yii\base\View|view]]:

```php
<?= $this->render('_overview') ?>
```


### Renderização por Outros Lugares <span id="rendering-in-other-places"></span>

Em qualquer lugar, você pode acessar o componente de aplicação [[yii\base\View|view]] 
pela expressão `Yii::$app->view` e chamar qualquer método mencionado anteriormente 
para renderizar uma view (visão). Por exemplo,

```php
// exibe o arquivo de view (visão) "@app/views/site/license.php"
echo \Yii::$app->view->renderFile('@app/views/site/license.php');
```


### Views (Visões) Nomeadas <span id="named-views"></span>

Ao renderizar uma view (visão), você pode especificar a view usando um nome ou o 
caminho do arquivo/alias. Na maioria dos casos, você usará a primeira maneira, 
pois são mais concisos e flexíveis. Especificamente, chamaremos as views usando 
nomes como *views (visões) nomeadas*.

Um nome da view (visão) resolve o caminho da view (visão) correspondente de 
acordo com as seguintes regras:

* Um nome da view (visão) pode omitir a extensão do arquivo. Neste caso, o `.php` 
  será usado como extensão. Por exemplo, a view chamada `about` corresponderá ao 
  arquivo `about.php`.
* Se o nome da view (visão) iniciar com barras duplas `//`, o caminho correspondente 
  será `@app/views/ViewName`. Ou seja, a view (visão) será localizada sob o 
  [[yii\base\Application::viewPath|diretório das views da aplicação]]. Por exemplo, 
  `//site/about` corresponderá ao `@app/views/site/about.php`.
* Se o nome da view (visão) iniciar com barra `/`, o caminho do arquivo da view 
  será formado pelo nome da view (visão) com o [[yii\base\Module::viewPath|diretório da view]] 
  do [módulo](structure-modules.md) ativo. Se não existir um módulo ativo, o 
  `@app/views/ViewName` será usado. Por exemplo, o `/user/create` corresponderá 
  ao `@app/modules/user/views/user/create.php`, caso o módulo ativo seja `user`. 
  Se não existir um módulo ativo, o caminho do arquivo da view (visão) será 
  `@app/views/user/create.php`.
* Se a view (visão) for renderizada com um [[yii\base\View::context|contexto]] e 
  que o mesmo implemente [[yii\base\ViewContextInterface]], o caminho do arquivo 
  da view será formado pelo nome da view (visão) com o 
  [[yii\base\ViewContextInterface::getViewPath()|diretório da view]] do contexto. 
  Isto se aplica principalmente para as views (visões) renderizadas pelos controllers 
  (controladores) e widgets. Por exemplo, `about` corresponderá ao 
  `@app/views/site/about.php` caso o contexto seja o controller (controlador) 
  `SiteController`.
* Se uma view (visão) for renderizada por outra view (visão), o diretório desta 
  outra view (visão) será usado para formar o caminho do arquivo da view atual. 
  Por exemplo, `item` corresponderá ao `@app/views/post/item.php` se ela for 
  renderizada pela view `@app/views/post/index.php`.

De acordo com as regras acima, ao chamar `$this->render('view')` em um controller 
(controlador) `app\controllers\PostController` será renderizado o arquivo de view 
(visão) `@app/views/post/view.php` e, ao chamar `$this->render('_overview')` nesta 
view, será renderizado o arquivo de visão `@app/views/post/_overview.php`.


### Acessando Dados em Views (Visões) <span id="accessing-data-in-views"></span>

Existem duas abordagens para acessar dados em um view (visão): *push* e *pull*.

Ao passar os dados como o segundo parâmetro nos métodos de renderização de view 
(visão), você estará usando a abordagem *push*.
Os dados devem ser representados por um array com pares de nome-valor. Quando a 
view (visão) estiver sendo renderizada, a função `extract()` do PHP será chamado 
passando este array a fim de extraí-los em variáveis na view (visão).
Por exemplo, o código do controller (controlador) a seguir fornecerá (pela 
abordagem *push*) duas variáveis para a view (visão) `report`: `$foo = 1` e `$bar = 2`.

```php
echo $this->render('report', [
    'foo' => 1,
    'bar' => 2,
]);
```

Na abordagem *pull*, os dados serão recuperados ativamente pelo 
[[yii\base\View|componente da view]] ou por outros objetos que acessam as views 
(por exemplo, `Yii::$app`). Usando o código do exemplo a seguir, você poderá 
acessar o objeto do controller (controlador) pela expressão `$this->context` 
dentro da view. E como resultado, será possível acessar qualquer propriedade ou 
métodos do controller (controlador) na view (visão) `report`, como o ID do 
controller conforme o exemplo a seguir:

```php
The controller ID is: <?= $this->context->id ?>
?>
```

A abordagem *push* normalmente é a forma preferida de acessar dados nas views 
(visões), pelo fato de criar menos dependências nos objetos de contexto. A 
desvantagem é que você precisará montar manualmente os dados em um array o tempo 
todo, o que poderia tornar-se tedioso e propenso a erros se uma view for 
compartilhada e renderizada em lugares diferentes.


### Compartilhando Dados entre as Views (Visões) <span id="sharing-data-among-views"></span>

O [[yii\base\View|componente da view]] fornece a propriedade 
[[yii\base\View::params|params]] que você pode usar para compartilhar dados entre 
as views (visões).

Por exemplo, em uma view `about`, você pode ter o seguinte código para especificar 
o seguimento atual do rastro da aplicação (breadcrumbs).

```php
$this->params['breadcrumbs'][] = 'About Us';
```

Em seguida, no arquivo do [layout](#layouts), que também é uma view, você pode 
exibir o rastro da aplicação (breadcrumbs) usando os dados passados pela 
propriedade [[yii\base\View::params|params]]:

```php
<?= yii\widgets\Breadcrumbs::widget([
    'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
]) ?>
```


## Layouts <span id="layouts"></span>

Os layouts são um tipo especial de views (visões) que representam as partes comuns 
das views (visões). Por exemplo, a maioria das páginas de aplicações Web 
compartilham o mesmo cabeçalho e rodapé. Embora você possa repetir o mesmo 
cabeçalho e rodapé em todas as view, a melhor maneira é fazer isso apenas uma vez 
no layout e incorporar o resultado da renderização de uma view em um lugar 
apropriado no layout.


### Criando Layouts <span id="creating-layouts"></span>

Pelo fato dos layouts também serem views (visões), eles podem ser criados de 
forma semelhante as views (visões) normais. Por padrão, os layouts são guardados 
no diretório `@app/views/layouts`. Para os layouts usados nos 
[módulos](structure-modules.md), devem ser guardados no diretório `views/layouts` 
sob o [[yii\base\Module::basePath|diretório do módulo]].
Você pode personalizar o diretório do layout padrão configurando a propriedade 
[[yii\base\Module::layoutPath]] da aplicação ou do módulo.

O exemplo a seguir mostra como é o layout. Observe que, para fins ilustrativos, 
simplificamos o código do layout. Em prática, você pode adicionar mais conteúdo, 
como tags de cabeçalho, menu principal, etc.

```php
<?php
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $content string */
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"/>
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
</head>
<body>
<?php $this->beginBody() ?>
    <header>My Company</header>
    <?= $content ?>
    <footer>&copy; 2014 by My Company</footer>
<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
```

Como pode ver, o layout gera as tags HTML que são comuns em todas as páginas. Na 
seçao `<body>`, o layout dará *echo* da variável `$content` que representa o 
resultado da renderização do conteúdo das views (visões) e é incorporado ao layout 
quando método [[yii\base\Controller::render()]] for chamado.

Como mostrado no código acima, a maioria dos layouts devem chamar os métodos 
listados a seguir. Estes métodos podem desencadeiam eventos referentes ao processo 
de renderização para que scripts e tags registrados em outros lugares possam ser
inseridos nos locais onde estes métodos forem chamados.

- [[yii\base\View::beginPage()|beginPage()]]: Este método deve ser chamado no início 
  do layout. Ele dispara o evento [[yii\base\View::EVENT_BEGIN_PAGE|EVENT_BEGIN_PAGE]] 
  que indica o início de uma página.
- [[yii\base\View::endPage()|endPage()]]: Este método deve ser chamado no final 
  do layout. Ele dispara o evento [[yii\base\View::EVENT_END_PAGE|EVENT_END_PAGE]] 
  que indica o fim de uma página.
- [[yii\web\View::head()|head()]]: Este método deve ser chamada na seção `<head>` 
  de uma página HTML.  Ele reserva este local para gerar os códigos HTML do 
  cabeçalho que foram registrados (por exemplo, link e meta tags) quando uma 
  página finaliza a renderização.
- [[yii\web\View::beginBody()|beginBody()]]: Este método deve ser chamado no início 
  da seção `<body>` . Ele dispara o evento [[yii\web\View::EVENT_BEGIN_BODY|EVENT_BEGIN_BODY]] 
  e reserva este local para gerar os códigos HTML (por exemplo, JavaScript) 
  voltados para a posição inicial do corpo do layout que foram registrados.
- [[yii\web\View::endBody()|endBody()]]: Este método deve ser chamado no final da 
  seção `<body>`. Ele dispara o evento [[yii\web\View::EVENT_END_BODY|EVENT_END_BODY]] 
  e reserva este local para gerar códigos HTML (por exemplo, JavaScript) voltados 
  para a posição final do corpo do layout que foram registrados.


### Acessando Dados nos Layouts <span id="accessing-data-in-layouts"></span>

Dentro de um layout, você tem acesso a duas variáveis predefinidas: `$this` e 
`$content`. A primeira refere-se ao componente da [[yii\base\View|view]] como as 
views normais, enquanto o segundo contém o resultado da renderização do conteúdo 
de uma view que é gerada pela execução do método [[yii\base\Controller::render()|render()]] 
no controller (controlador).

Se você quiser acessar outros dados nos layouts, você terá que usar a abordagem 
*pull* conforme descrito na subseção 
[Acessando Dados em Views (Visões)](#accessing-data-in-views). Se você quiser 
passar os dados de um conteúdo da view em um layout, poderá usar o método descrito 
na subseção [Compartilhando Dados entre as Views (Visões)](#sharing-data-among-views).


### Usando Layouts <span id="using-layouts"></span>

Como descrito na subseção [Renderização nos Controllers (Controladores)](#rendering-in-controllers), 
quando você renderizar uma view (visão) chamando o método [[yii\base\Controller::render()|render()]] 
em um controller (controlador), o resultado da renderização será aplicado em um layout. 
Por padrão, o layout `@app/views/layouts/main.php` será usado. 

Você pode usar um layout diferente configurando a propriedade 
[[yii\base\Application::layout]] ou [[yii\base\Controller::layout]].
A primeira aplica o layout em todos os controllers (controladores) usados, 
enquanto o segundo sobrescreve a primeira forma pelos controllers (controladores) 
de forma individual.
Por exemplo, o código a seguir faz com que o controller `post` use o 
`@app/views/layouts/post.php` como layout quando renderizar as suas views (visões). 
Os outros controllers (controladores), assumindo que a propriedade `layout` da 
aplicação não foi alterada, usará como padrão o layout `@app/views/layouts/main.php`.
 
```php
namespace app\controllers;

use yii\web\Controller;

class PostController extends Controller
{
    public $layout = 'post';
    
    // ...
}
```

Para os controllers (controladores) que pertencem a um módulo, você pode configurar 
a propriedade [[yii\base\Module::layout|layout]] do módulo para usar um layout em 
particular para estes controllers. 

Devido a propriedade `layout` possa ser configurado em diferentes níveis 
(controllers, módulos, aplicação), por baixo dos panos o Yii determina em duas 
etapas qual é o arquivo de layout que será usado por um controller (controlador) 
em particular.

Na primeira etapa, o Yii determina o nome do layout e o módulo de contexto:

- Se a propriedade [[yii\base\Controller::layout]] do controller (controlador) 
  não for nula, será usado como o nome do layout e o [[yii\base\Controller::module|módulo]] 
  do controller (controlador) como módulo de contexto. 
- Se a propriedade [[yii\base\Controller::layout|layout]] for nula, será buscado 
  através de todos os módulos ancestrais (incluindo a própria aplicação) do 
  controller (controlador) até encontrar o primeiro módulo que a propriedade 
  [[yii\base\Module::layout|layout]] não esteja nula. Este módulo encontrado será 
  usado como o módulo de contexto e o valor de sua propriedade [[yii\base\Module::layout|layout]] 
  como o nome do layout. Se um módulo não for encontrado, significará que nenhum 
  layout será aplicado.
  
Na segunda etapa, ele determina o arquivo do layout de acordo com o valor do layout 
e o modulo de contexto obtidos na primeira etapa. O nome do layout pode ter:

- uma alias de caminho (por exemplo, `@app/views/layouts/main`).
- um caminho absoluto (por exemplo, `/main`): o nome do layout que começa com uma 
  barra. O arquivo do layout será procurado sob o 
  [[yii\base\Application::layoutPath|diretório do layout]] da aplicação, na qual 
  o valor padrão é `@app/views/layouts`.
- um caminho relativo (por exemplo, `main`): o arquivo do layout será procurado 
  sob o [[yii\base\Module::layoutPath|diretório do layout]] do módulo de contexto, 
  na qual o valor padrão é `views/layouts` sob o [[yii\base\Module::basePath|diretório do módulo]].
- um valor booleano `false`: nenhum layout será aplicado.

Caso o nome do layout não tiver uma extensão de arquivo, será usado um `.php` por padrão.


### Layouts Aninhados <span id="nested-layouts"></span>

Algumas vezes, você pode querer que um layout seja usado dentro de outro. Por 
exemplo, você pode querer usar diferentes layouts para cada seção de um página 
Web, onde todos estes layouts compartilharão o mesmo layout básico a fim de gerar 
toda a estrutura de uma página HTML5. Este objetivo pode ser alcançado chamando 
os métodos [[yii\base\View::beginContent()|beginContent()]] e 
[[yii\base\View::endContent()|endContent()]] nos layouts filhos, como mostro no 
exemplo a seguir:

```php
<?php $this->beginContent('@app/views/layouts/base.php'); ?>

...conteúdo do layout filho...

<?php $this->endContent(); ?>
```

Como mostrado no exemplo acima, o conteúdo do layout filho deve ser envolvido 
pelos métodos [[yii\base\View::beginContent()|beginContent()]] e 
[[yii\base\View::endContent()|endContent()]]. O parâmetro passado no 
[[yii\base\View::beginContent()|beginContent()]] indica qual é o layout pai. Ele 
pode ser tanto um arquivo do layout quanto uma alias.

Usando a abordagem mencionada, poderá aninhar os layouts em mais de um nível.


### Usando Blocos <span id="using-blocks"></span>

Os blocos lhe permitem especificar o conteúdo da view (visão) de um local e exibi-lo 
em outro. Geralmente são usados em conjunto com os layouts. Por exemplo, você pode 
definir um bloco no conteúdo de uma view (visão) e exibi-lo no layout.

Para definir um bloco, deverá chamar os métodos [[yii\base\View::beginBlock()|beginBlock()]] 
e [[yii\base\View::endBlock()|endBlock()]].
O bloco pode ser acessado via `$view->blocks[$blockID]`, onde o `$blockID` é o 
identificador único que você associou ao bloco quando o definiu.

O exemplo a seguir mostra como você pode usar blocos para personalizar as partes 
especificas de um layout pelo conteúdo da view (visão).

Primeiramente, no conteúdo da view (visão), defina um ou vários blocos:

```php
...

<?php $this->beginBlock('block1'); ?>

...conteúdo do block1...

<?php $this->endBlock(); ?>

...

<?php $this->beginBlock('block3'); ?>

... conteúdo do block3...

<?php $this->endBlock(); ?>
```

Em seguida, na view (visão) do layout, aplique os blocos se estiverem disponíveis 
ou caso não esteja disponível exiba um conteúdo padrão.

```php
...
<?php if (isset($this->blocks['block1'])): ?>
    <?= $this->blocks['block1'] ?>
<?php else: ?>
    ... conteúdo padrão para o block1 ...
<?php endif; ?>

...

<?php if (isset($this->blocks['block2'])): ?>
    <?= $this->blocks['block2'] ?>
<?php else: ?>
    ... conteúdo padrão para o block2 ...
<?php endif; ?>

...

<?php if (isset($this->blocks['block3'])): ?>
    <?= $this->blocks['block3'] ?>
<?php else: ?>
    ... conteúdo padrão para o block3 ...
<?php endif; ?>
...
```


## Usando Componentes de View (Visão) <span id="using-view-components"></span>

Os [[yii\base\View|componentes de view (visão)]] fornecem muitos recursos 
relacionados às views (visões). Enquanto você pode obter os componentes de view 
(visão) através da criação de instancias individuais da classe [[yii\base\View]] 
ou de suas classes filhas, na maioria dos casos você simplesmente usará o 
componente da aplicação `view`. Você pode configurar estes componentes nas 
[configurações da aplicação](structure-applications.md#application-configurations) 
conforme o exemplo a seguir:

```php
[
    // ...
    'components' => [
        'view' => [
            'class' => 'app\components\View',
        ],
        // ...
    ],
]
```

A seguir, os recursos úteis relacionados às views (visões) que os componentes de 
view (visão) fornecem. Cada um dos recursos são descritos com mais detalhes em seções separadas:

* [temas](output-theming.md): permite que você desenvolva e altere um tema para 
  o seu site.
* [cache de fragmento](caching-fragment.md): permite que guarde o cache de um 
  fragmento de uma página.
* [manipulando client scripts](output-client-scripts.md): suporta o registro e a 
  renderização de CSS e JavaScript.
* [manipulando asset bundle](structure-assets.md): suporta o registro e renderização 
  de [asset bundles](structure-assets.md).
* [motores de templates alternativos](tutorial-template-engines.md): permite que 
  você use outro motor de template, tais como o [Twig](http://twig.sensiolabs.org/) 
  e o [Smarty](http://www.smarty.net/).

Você também pode usar com frequência os seguintes recursos menos úteis quando 
estiver desenvolvendo suas páginas.


### Configuração do Título da Página <span id="setting-page-titles"></span>

Cada página deve ter um título. Normalmente, a tag do título é colocada no início 
de um [layout](#layouts). Mas na prática, o título é muitas vezes determinado 
pelo conteúdo das views (visões) do que pelos os layouts. Para resolver este 
problema, a classe [[yii\web\View]] fornece a propriedade [[yii\web\View::title|title]] 
para você informar o título pelas view (visões) para os layouts.

Para fazer uso deste recurso, em cada view (visão), você pode definir o título da 
página conforme o exemplo a seguir:

```php
<?php
$this->title = 'My page title';
?>
```

E no layout, verifique se você tem o seguinte código sob o elemento `<head>`:

```php
<title><?= Html::encode($this->title) ?></title>
```


### Registrando os Meta Tags <span id="registering-meta-tags"></span>

As páginas Web geralmente precisam gerar vários meta tags necessários para 
diferentes fins. Assim como os títulos, os meta tags precisam estar na seção 
`<head>` e normalmente são criados nos layouts.

Se você quiser especificar quais meta tags precisam ser criados no conteúdo de 
uma view (visão), poderá chamar o método [[yii\web\View::registerMetaTag()]] 
no conteúdo da view, conforme o exemplo a seguir: 

```php
<?php
$this->registerMetaTag(['name' => 'keywords', 'content' => 'yii, framework, php']);
?>
```

O código acima registrará um meta tag "keywords" com o componente da view (visão). 
O meta tag registrado será processado antes que o layout finalize sua renderização. 
O código do HTML a seguir será criado no lugar da chamada [[yii\web\View::head()]] 
no layout:

```php
<meta name="keywords" content="yii, framework, php">
```

Observe que ao chamar o método [[yii\web\View::registerMetaTag()]] muitas vezes, 
registrará diversas meta tags, independente se forem as mesmas meta tags ou não.

Para garantir que existirá apenas uma única instância de um tipo de meta tag, 
você pode especificar uma chave no segundo parâmetro ao chamar o método.
Por exemplo, o código a seguir registra dois meta tags "description". No entanto, 
apenas o segundo será processado.

```html
$this->registerMetaTag(['name' => 'description', 'content' => 'This is my cool website made with Yii!'], 'description');
$this->registerMetaTag(['name' => 'description', 'content' => 'This website is about funny raccoons.'], 'description');
```


### Registrando as Tags Link <span id="registering-link-tags"></span>

Assim como os [meta tags](#registering-meta-tags), as tags link são úteis em muitos 
casos, tais como a personalização do favicon, apontamento de feed RSS ou delegar 
o OpenID para outros servidores. Você pode trabalhar com as tags link de forma 
similar aos meta tags, usando o método [[yii\web\View::registerLinkTag()]]. Por 
exemplo, no conteúdo da view (visão), você pode registrar uma tag link conforme 
o seguinte exemplo,

```php
$this->registerLinkTag([
    'title' => 'Live News for Yii',
    'rel' => 'alternate',
    'type' => 'application/rss+xml',
    'href' => 'http://www.yiiframework.com/rss.xml/',
]);
```

O código acima resultará em 

```html
<link title="Live News for Yii" rel="alternate" type="application/rss+xml" href="http://www.yiiframework.com/rss.xml/">
```

Semelhante ao método [[yii\web\View::registerMetaTag()|registerMetaTags()]], 
você pode especificar uma chave quando chamar o método 
[[yii\web\View::registerLinkTag()|registerLinkTag()]] para evitar a criação de 
tags link repetidas.


## Eventos da View (Visão) <span id="view-events"></span>

[[yii\base\View|Os componentes de view (visão)]] disparam vários eventos durante 
o processo de renderização da view (visão). Você pode usar estes eventos para 
inserir conteúdos nas views (visões) ou processar os resultados da renderização 
antes de serem enviados para os usuários finais.

- [[yii\base\View::EVENT_BEFORE_RENDER|EVENT_BEFORE_RENDER]]: é disparado no 
  início da renderização de um arquivo no controlador. Na função deste evento 
  pode definir a propriedade [[yii\base\ViewEvent::isValid]] como `false` para 
  cancelar o processo de renderização.
- [[yii\base\View::EVENT_AFTER_RENDER|EVENT_AFTER_RENDER]]: é disparado depois 
  da renderização de um arquivo pela chamada do método [[yii\base\View::afterRender()]].
  Na função deste evento pode obter o resultado da renderização por meio da propriedade 
  [[yii\base\ViewEvent::output]] podendo modifica-lo para alterar o resultado da 
  renderização.
- [[yii\base\View::EVENT_BEGIN_PAGE|EVENT_BEGIN_PAGE]]: é disparado pela chamada 
  do método [[yii\base\View::beginPage()]] nos layouts.
- [[yii\base\View::EVENT_END_PAGE|EVENT_END_PAGE]]: é disparado pela chamada do 
  método [[yii\base\View::endPage()]] nos layouts.
- [[yii\web\View::EVENT_BEGIN_BODY|EVENT_BEGIN_BODY]]: é disparado pela chamada 
  do método [[yii\web\View::beginBody()]] nos layouts.
- [[yii\web\View::EVENT_END_BODY|EVENT_END_BODY]]: é disparado pela chamada do 
  método [[yii\web\View::endBody()]] nos layouts.

Por exemplo, o código a seguir insere a data atual no final do corpo da página:

```php
\Yii::$app->view->on(View::EVENT_END_BODY, function () {
    echo date('Y-m-d');
});
```


## Renderizando Páginas Estáticas <span id="rendering-static-pages"></span>

Páginas estáticas referem-se a páginas cujo principal conteúdo é na maior parte 
estática, sem a necessidade de acessar conteúdos dinâmicos pelos controllers 
(controladores).

Você pode produzir páginas estáticas referenciando uma view (visão) no controller 
(controlador) conforme o código a seguir:

```php
public function actionAbout()
{
    return $this->render('about');
}
```

Se o site contiver muitas páginas estáticas, seria um desperdício de tempo repetir 
os códigos semelhantes muitas vezes.
Para resolver este problema, poderá introduzir uma 
[ação standalone](structure-controllers.md#standalone-actions) chamando a classe 
[[yii\web\ViewAction]] em um controller (controlador). Por exemplo,

```php
namespace app\controllers;

use yii\web\Controller;

class SiteController extends Controller
{
    public function actions()
    {
        return [
            'page' => [
                'class' => 'yii\web\ViewAction',
            ],
        ];
    }
}
```

Agora, se você criar uma view (visão) chamada `about` sob o diretório 
`@app/views/site/pages`, será capaz de exibir esta view (visão) conforme a 
seguinte URL:

```
http://localhost/index.php?r=site/page&view=about
```

O parâmetro `view` passado via `GET` informa a classe [[yii\web\ViewAction]] 
qual view (visão) será solicitada. A ação, então, irá procurar a view (visão) 
sob o diretório `@app/views/site/pages`. Você pode configurar a propriedade 
[[yii\web\ViewAction::viewPrefix]] para alterar o diretório onde as views (visões) 
serão procuradas. 


## Boas Práticas <span id="best-practices"></span>

As views (visões) são os responsáveis por apresentar modelos no formato que os 
usuários finais desejarem. Em geral, as view (visões):

* devem conter principalmente códigos de apresentação, tais como o HTML e códigos 
  simples de PHP para formatação, renderização e transferência de dados.
* não devem conter códigos que realiza consultas no banco de dados. Esta 
  responsabilidade pertence aos models (modelos).
* devem evitar ter acesso direto aos dados de requisição, tais como o `$_GET` e 
  o `$_POST`. Esta responsabilidade deve pertencer aos controllers (controladores). 
  Se os dados da requisição forem necessários, deverão ser fornecidas as views (visões) 
  pelos controllers (controladores).
* poderão ler as propriedades dos models (modelos), mas não podem alterá-las.

Para views (visões) mais gerenciáveis, evite criar views (visões) muito complexas 
ou que contenham muitos códigos redundantes. Você pode usar as seguintes técnicas 
para atingir estes objetivos:

* use os [layouts](#layouts) para representar as seções de apresentação comuns 
  (por exemplo, cabeçalho e o rodapé).
* divida uma view (visão) complicada em varias outras menores. As views (visões) 
  menores podem ser renderizadas e montadas em um maior usando os métodos que 
  descrevemos anteriormente.
* crie e use [widgets](structure-widgets.md) como blocos de construção das views 
  (visões).
* crie e use as classes helpers para transformar e formatar os dados nas views 
  (visões).
