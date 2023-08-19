Visões (Views)
===========

As views fazem parte da arquitetura [MVC](https://pt.wikipedia.org/wiki/MVC).
São a parte do código responsável por apresentar dados aos usuários finais. Em um aplicação Web,
views geralmente são criadas em termos de *view templates* (modelos de view)
 que são arquivos PHP contendo principalmente códigos HTML e
códigos PHP de apresentação.
Os modelos de view são gerenciados pelo [componente da aplicação](structure-application-components.md)
[[yii\web\View|view]] que fornece métodos comumente utilizados para facilitar
a montagem e a renderização da view em si. Para simplificar, geralmente chamamos os modelos de view ou seus arquivos simplesmente
de view.


## Criando Views  <span id="creating-views"></span>

Conforme já mencionado, uma view é simplesmente um arquivo PHP
composto por HTML ou códigos PHP. O código a seguir representa uma view que exibe um formulário
de login. Como você pode ver, o código PHP é utilizado para gerar
as partes de conteúdo dinâmicas, tais como o título da página e o formulário, enquanto o código HTML dispõe os itens na página de uma forma apresentável.

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

<p>Por favor, preencha os seguintes campos para entrar:</p>

<?php $form = ActiveForm::begin(); ?>
    <?= $form->field($model, 'username') ?>
    <?= $form->field($model, 'password')->passwordInput() ?>
    <?= Html::submitButton('Entrar') ?>
<?php ActiveForm::end(); ?>
```

Em uma view, você pode acessar a variável `$this` que referencia o
[[yii\web\View|componente view]] responsável por gerenciar e renderizar a view
 em questão.

Além de `$this`, pode haver outras variáveis predefinidas na view, tal como
`$model` no exemplo acima. Essas variáveis representam
os dados que foram enviados à view por meio dos
[controllers](structure-controllers.md) ou de outros objetos que
desencadeiam a [renderização da view ](#rendering-views).

> Dica: As variáveis predefinidas são listadas em um bloco de comentário no inicio
  de uma view para que possam ser reconhecidas pelas IDEs. Além de ser também
  uma ótima maneira de documentar suas views.


### Segurança <span id="security"></span>

Ao criar views que geram páginas HTML, é importante que você codifique
e/ou filtre os dados que vêm de usuários antes de exibí-los. Caso contrário,
sua aplicação poderá estar sujeita a um ataque de
[cross-site scripting](https://pt.wikipedia.org/wiki/Cross-site_scripting).

Para exibir um texto simples, codifique-o antes por chamar o método
[[yii\helpers\Html::encode()]]. Por exemplo, o código a seguir codifica o nome do
usuário antes de exibí-lo:

```php
<?php
use yii\helpers\Html;
?>

<div class="username">
    <?= Html::encode($user->name) ?>
</div>
```

Para exibir conteúdo HTML, use [[yii\helpers\HtmlPurifier]] para
filtrar o conteúdo primeiro. Por exemplo, o código a seguir filtra o conteúdo de `$post->text` antes de exibí-lo:

```php
<?php
use yii\helpers\HtmlPurifier;
?>

<div class="post">
    <?= HtmlPurifier::process($post->text) ?>
</div>
```

> Dica: Embora o HTMLPurifier faça um excelente trabalho em tornar a saída de dados
  segura, ele não é rápido. Você deveria considerar guardar em [cache](caching-overview.md)
  o resultado filtrado se sua aplicação precisa de alta performance.


### Organizando as Views  <span id="organizing-views"></span>

Assim como para os [controllers](structure-controllers.md) e para os
[models](structure-models.md), existem convenções para organizar as
views.

* Views renderizadas por um controller deveriam ser colocadas sob o diretório `@app/views/IDdoController` por padrão, onde `IDdoController` refere-se ao [ID do controller](structure-controllers.md#routes).
  Por exemplo, se a classe do controller for `PostController`, o diretório
  será `@app/views/post`; se for `PostCommentController`, o diretório será
  `@app/views/post-comment`. Caso o controller pertença a um
  módulo, o diretório seria `views/IDdoController` sob o [[yii\base\Module::basePath|diretório do módulo]].
* Views renderizadas em um [widget](structure-widgets.md) deveriam ser
  colocadas sob o diretório `WidgetPath/views` por padrão, onde `WidgetPath`
  é o diretório o arquivo da classe do widget.
* Para views renderizadas por outros objetos, é recomendado
  que você siga a convenção semelhante à dos widgets.

Você pode personalizar os diretórios padrões das views sobrescrevendo
o método [[yii\base\ViewContextInterface::getViewPath()]] dos controllers ou dos widgets.


## Renderizando Views  <span id="rendering-views"></span>

Você pode renderizar views em
[controllers](structure-controllers.md), em [widgets](structure-widgets.md) ou em qualquer outro lugar chamando os métodos de renderização da view. Esses métodos compartilham uma assinatura similar, como a seguir:

```
/**
 * @param string $view nome da view ou caminho do arquivo, dependendo do método de renderização
 * @param array $params os dados passados para a view
 * @return string resultado da renderização
 */
methodName($view, $params = [])
```


### Renderização em Controllers <span id="rendering-in-controllers"></span>

Nos [controllers](structure-controllers.md), você pode chamar os
seguintes métodos para renderizar as views:

* [[yii\base\Controller::render()|render()]]: renderiza uma [view nomeada](#named-views) e aplica um [layout](#layouts) ao resultado da renderização.
* [[yii\base\Controller::renderPartial()|renderPartial()]]: renderiza
  uma [view nomeada](#named-views) sem qualquer layout.
* [[yii\web\Controller::renderAjax()|renderAjax()]]: renderiza uma [view nomeada](#named-views) sem qualquer layout
  e injeta todos os arquivos JS/CSS registrados. É geralmente utilizado
  em respostas de requisições Web Ajax.
* [[yii\base\Controller::renderFile()|renderFile()]]: renderiza uma view a partir
  de um caminho de arquivo ou a partir de um [alias](concept-aliases.md).
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

        // renderiza uma view chamada "exibir" e aplica um layout a ela
        return $this->render('exibir', [
            'model' => $model,
        ]);
    }
}
```


### Renderização em Widgets <span id="rendering-in-widgets"></span>

Nos [widgets](structure-widgets.md), você pode chamar os seguintes métodos do
widget para renderizar views:

* [[yii\base\Widget::render()|render()]]: renderiza uma [view nomeada](#named-views).
* [[yii\base\Widget::renderFile()|renderFile()]]: renderiza uma view a partir de
  um caminho de arquivo ou a partir de um [alias](concept-aliases.md).

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
        // renderiza uma view chamada "listar"
        return $this->render('listar', [
            'items' => $this->items,
        ]);
    }
}
```


### Renderização em Views  <span id="rendering-in-views"></span>

Você pode renderizar uma view dentro de outra chamando um dos seguintes
métodos fornecidos pelo [[yii\base\View|componente view]]:

* [[yii\base\Controller::render()|render()]]: renderiza uma [view nomeada](#named-views).
* [[yii\web\Controller::renderAjax()|renderAjax()]]: renderiza uma [view nomeada](#named-views) sem qualquer layout
  e injeta todos os arquivos JS/CSS registrados. É geralmente utilizado
  em respostas de requisições Web Ajax.
* [[yii\base\Controller::renderFile()|renderFile()]]: renderiza uma view a partir
  de um caminho de arquivo ou a partir de um [alias](concept-aliases.md).

Por exemplo, no código a seguir, uma view qualquer renderiza outro arquivo
de view chamado `_visao-geral.php` que encontram-se em seu mesmo diretório.
Lembre-se que `$this` na view referencia o componente [[yii\base\View|view]]:

```php
<?= $this->render('_visao-geral') ?>
```


### Renderização em Outros Lugares <span id="rendering-in-other-places"></span>

Em qualquer lugar, você pode acessar o componente de aplicação [[yii\base\View|view]]
pela expressão `Yii::$app->view` e então chamar qualquer método mencionado anteriormente
para renderizar uma view. Por exemplo,

```php
// exibe a view "@app/views/site/license.php"
echo \Yii::$app->view->renderFile('@app/views/site/license.php');
```


### Views Nomeadas <span id="named-views"></span>

Ao renderizar uma view, você pode especificá-la usando seu nome, ou o caminho do arquivo, ou um alias. Na maioria dos casos,
você usará a primeira maneira por ser mais concisa e flexível. Quando especificamos views por nome, chamamos essas views de *views nomeadas*.

Um nome de view é convertido no caminho de arquivo da view correspondente de
acordo com as seguintes regras:

* Um nome de view pode omitir a extensão do arquivo. Neste caso, o `.php`
  será usado como extensão. Por exemplo, a view chamada `sobre` corresponderá ao
  arquivo `sobre.php`.
* Se o nome da view iniciar com barras duplas `//`, o caminho correspondente
  seria `@app/views/ViewName`. Ou seja, a view será localizada sob o
  [[yii\base\Application::viewPath|diretório das views da aplicação]]. Por exemplo,
  `//site/sobre` corresponderá ao `@app/views/site/sobre.php`.
* Se o nome da view iniciar com uma barra simples `/`, o caminho do arquivo da view
  será formado pelo nome da view com o [[yii\base\Module::viewPath|diretório da view]]
  do [módulo](structure-modules.md) ativo. Se não houver um módulo ativo, o
  `@app/views/ViewName` será usado. Por exemplo, `/usuario/criar` corresponderá
  a `@app/modules/user/views/usuario/criar.php` caso o módulo ativo seja `user`.
  Se não existir um módulo ativo, o caminho do arquivo da view será
  `@app/views/usuario/criar.php`.
* Se a view for renderizada com um [[yii\base\View::context|contexto]] e
  que implemente [[yii\base\ViewContextInterface]], o caminho do arquivo
  da view será formado por prefixar o [[yii\base\ViewContextInterface::getViewPath()|diretório da view]] do contexto ao nome da view.
  Isto se aplica principalmente às views renderizadas em controllers e widgets. Por exemplo,
  `sobre` corresponderá a `@app/views/site/sobre.php` caso o contexto seja o controller
  `SiteController`.
* Se uma view for renderizada dentro de outra, o diretório que contém esta
  outra view será usado para formar o caminho de seu arquivo.
  Por exemplo, `item` corresponderá a `@app/views/post/item.php` se ela for
  renderizada dentro da view `@app/views/post/index.php`.

De acordo com as regras acima, chamar `$this->render('exibir')` em um controller `app\controllers\PostController` vai realmente renderizar o arquivo de view
 `@app/views/post/exibir.php` e, chamar `$this->render('_visaogeral')` nessa view (`exibir.php`) vai renderizar o arquivo de visão `@app/views/post/_visaogeral.php`.


### Acessando Dados em Views  <span id="accessing-data-in-views"></span>

Existem duas abordagens para acessar dados em uma view : *push* e *pull*.

Ao passar os dados como o segundo parâmetro nos métodos de renderização de view, você estará usando a abordagem *push*.
Os dados devem ser representados por um array com pares de nome-valor. Quando a
view estiver sendo renderizada, a função `extract()` do PHP será executada sobre essa array a fim de extrair seus dados em variáveis na view.
Por exemplo, o renderização da view a seguir, em um controller, disponibilizará (pela
abordagem *push*) duas variáveis para a view  `relatorio`: `$foo = 1` e `$bar = 2`.

```php
echo $this->render('relatorio', [
    'foo' => 1,
    'bar' => 2,
]);
```

A abordagem *pull* ativamente obtém os dados do
[[yii\base\View|componente view]] ou de outros objetos acessíveis nas views
(por exemplo, `Yii::$app`). Usando o código a seguir como exemplo, dentro da view você pode acessar seu objeto controller usando a expressão `$this->context`.
E como resultado, será possível acessar quaisquer propriedades ou
métodos do controller, como o seu ID, na view `relatorio`:

```php
O ID do controller é: <?= $this->context->id ?>
?>
```

A abordagem *push* normalmente é a forma preferida de acessar dados nas views
por que as torna menos dependentes de objetos de contexto. A
desvantagem é que você precisa montar manualmente os dados em um array o tempo
todo, o que poderia se tornar tedioso e propenso a erros se uma view for
compartilhada e renderizada em lugares diferentes.


### Compartilhando Dados entre as Views  <span id="sharing-data-among-views"></span>

O [[yii\base\View|componente view]] fornece a propriedade
[[yii\base\View::params|params]] que você pode usar para compartilhar dados entre
as views.

Por exemplo, em uma view `sobre`, você pode ter o seguinte código que especifica
o seguimento atual do "rastro de navegação" (breadcrumbs):

```php
$this->params['breadcrumbs'][] = 'Sobre nós';
```

Em seguida, no arquivo [layout](#layouts), que também é uma view, você pode
exibir o "rastro de navegação" (breadcrumbs) usando os dados passados pela
propriedade [[yii\base\View::params|params]]:

```php
<?= yii\widgets\Breadcrumbs::widget([
    'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
]) ?>
```


## Layouts <span id="layouts"></span>

Layouts são um tipo especial de view que representam as partes comuns
de múltiplas views. Por exemplo, as páginas da maioria das aplicações Web
compartilham o mesmo cabeçalho e rodapé. Embora você possa repetir o mesmo
cabeçalho e rodapé em todas as view, a melhor maneira é fazer isso apenas uma vez
no layout e incorporar o resultado da renderização de uma view em um lugar
apropriado no layout.


### Criando Layouts <span id="creating-layouts"></span>

Visto que os layouts também são views, eles podem ser criados de
forma semelhante às views normais. Por padrão, layouts são salvos
no diretório `@app/views/layouts`. Layouts usados em um
[módulo](structure-modules.md) devem ser salvos no diretório `views/layouts`
sob o [[yii\base\Module::basePath|diretório do módulo]].
Você pode personalizar o diretório padrão de layouts configurando a propriedade
[[yii\base\Module::layoutPath]] da aplicação ou do módulo.

O exemplo a seguir mostra como é um layout. Observe que, para fins ilustrativos,
simplificamos bastante o código do layout. Na prática, você pode querer adicionar mais conteúdos a ele, tais
como tags no head, menu principal, etc.

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
    <header>Minha Empresa</header>
    <?= $content ?>
    <footer>&copy; 2014 por Minhas Empresa</footer>
<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
```

Conforme pode ver, o layout gera as tags HTML que são comuns a todas as páginas. Na
seçao `<body>`, o layout vai inserir a variável `$content` que representa o
resultado da renderização do conteúdo das views e é enviado ao layout
quando método [[yii\base\Controller::render()]] for chamado.

A maioria dos layouts devem chamar os métodos listados a seguir, conforme ocorreu no código acima. Estes métodos essencialmente desencadeiam eventos referentes ao processo
de renderização para que scripts e tags registrados em outros lugares possam ser
inseridos nos locais onde eles (os métodos) forem chamados.

- [[yii\base\View::beginPage()|beginPage()]]: Este método deve ser chamado no início
  do layout. Ele dispara o evento [[yii\base\View::EVENT_BEGIN_PAGE|EVENT_BEGIN_PAGE]]
  que indica o início de uma página.
- [[yii\base\View::endPage()|endPage()]]: Este método deve ser chamado no final
  do layout. Ele dispara o evento [[yii\base\View::EVENT_END_PAGE|EVENT_END_PAGE]]
  que indica o fim de uma página.
- [[yii\web\View::head()|head()]]: Este método deve ser chamado na seção `<head>`
  de uma página HTML. Ele gera um marcador que será substituído por código HTML (por exemplo, tags `<link>` e `<meta>`) quando a página termina a renderização.
- [[yii\web\View::beginBody()|beginBody()]]: Este método deve ser chamado no início
  da seção `<body>` . Ele dispara o evento [[yii\web\View::EVENT_BEGIN_BODY|EVENT_BEGIN_BODY]] e gera um marcador que será substituído por código HTML que estiver registrado para essa posição (por exemplo, algum código JavaScript).
- [[yii\web\View::endBody()|endBody()]]: Este método deve ser chamado no final da
  seção `<body>`. Ele dispara o evento [[yii\web\View::EVENT_END_BODY|EVENT_END_BODY]]
  e gera um marcador que será substituído por código HTML que estiver registrado para essa posição (por exemplo, algum código JavaScript).


### Acessando Dados nos Layouts <span id="accessing-data-in-layouts"></span>

Dentro de um layout, você tem acesso a duas variáveis predefinidas: `$this` e
`$content`. A primeira se refere ao componente [[yii\base\View|view]] como em views normais, enquanto a segunda contém o resultado da renderização do conteúdo
de uma view que é gerada por chamar o método [[yii\base\Controller::render()|render()]]
no controller.

Se você quiser acessar outros dados nos layouts, você terá de usar a abordagem
*pull* conforme descrito na subseção
[Acessando Dados em Views ](#accessing-data-in-views). Se você quiser
passar os dados do conteúdo da view para um layout, poderá usar o método descrito
na subseção [Compartilhando Dados entre as Views ](#sharing-data-among-views).


### Usando Layouts <span id="using-layouts"></span>

Conforme descrito na subseção [Renderização em Controllers](#rendering-in-controllers),
quando você renderiza uma view chamando o método [[yii\base\Controller::render()|render()]]
em um controller, será aplicado um layout ao resultado da renderização. Por padrão, o layout `@app/views/layouts/main.php` será usado.

Você pode usar um layout diferente configurando ou a propriedade
[[yii\base\Application::layout]] ou a [[yii\base\Controller::layout]].
A primeira especifica o layout usado por todos os controllers,
enquanto a segunda é usada para controllers de forma individual, sobrescrevendo a primeira.
Por exemplo, o código a seguir faz com que o controller `post` usar o
`@app/views/layouts/post.php` como layout quando renderizar as suas views.
Outros controllers, assumindo que a propriedade `layout` da
aplicação não tenha sido alterada, usarão o layout padrão `@app/views/layouts/main.php`.

```php
namespace app\controllers;

use yii\web\Controller;

class PostController extends Controller
{
    public $layout = 'post';

    // ...
}
```

Para os controllers que pertencem a um módulo, você também pode configurar
a propriedade [[yii\base\Module::layout|layout]] do módulo para usar um layout em
particular para esses controllers.

Visto que a propriedade `layout` pode ser configurada em diferentes níveis
(controllers, módulos, aplicação), por trás das cortinas o Yii determina, em duas
etapas, qual arquivo de layout será usado por um controller em particular.

Na primeira etapa, o Yii determina o valor da propriedade do layout e o módulo de contexto:

- Se a propriedade [[yii\base\Controller::layout]] do controller
  não for `null`, ela será usada e o [[yii\base\Controller::module|módulo]]
  do controller será usado como módulo de contexto.
- Se a propriedade [[yii\base\Controller::layout|layout]] for `null`, o Yii pesquisará
  através de todos os módulos ancestrais do
  controller (incluindo a própria aplicação) até encontrar o primeiro módulo cuja propriedade
  [[yii\base\Module::layout|layout]] não for `null`. O módulo encontrado será
  usado como módulo de contexto e o valor de sua propriedade [[yii\base\Module::layout|layout]]
  como o layout escolhido. Se nenhum módulo for encontrado, nenhum
  layout será aplicado.

Na segunda etapa, o Yii determina o real arquivo de layout de acordo com o valor da propriedade layout e com o modulo de contexto obtidos na primeira etapa. O valor da propriedade layout pode ser:

- uma alias de caminho (por exemplo, `@app/views/layouts/main`).
- um caminho absoluto (por exemplo, `/main`): o valor começa com uma
  barra. O arquivo de layout será procurado sob o
  [[yii\base\Application::layoutPath|diretório de layouts]] da aplicação, cujo valor padrão é `@app/views/layouts`.
- um caminho relativo (por exemplo, `main`): o arquivo de layout será procurado
  sob o [[yii\base\Module::layoutPath|diretório de layouts]] do módulo de contexto,
  cujo valor padrão é `views/layouts` sob o [[yii\base\Module::basePath|diretório do módulo]].
- um valor booleano `false`: nenhum layout será aplicado.

Se o valor da propriedade layout não tiver uma extensão de arquivo, será usada a extensão `.php` por padrão.


### Layouts Aninhados <span id="nested-layouts"></span>

Às vezes, você pode querer que um layout seja usado dentro de outro. Por
exemplo, em diferentes seções de um site, você pode querer usar diferentes layouts, e todos esses layouts compartilharão o mesmo layout básico a fim de produzir
toda a estrutura da página HTML. Você pode fazer isso por chamar
os métodos [[yii\base\View::beginContent()|beginContent()]] e
[[yii\base\View::endContent()|endContent()]] nos layouts filhos, como no
exemplo a seguir:

```php
<?php $this->beginContent('@app/views/layouts/base.php'); ?>

...conteúdo do layout filho aqui...

<?php $this->endContent(); ?>
```

Como mostrado acima, o conteúdo do layout filho deve ser inserido entre os métodos [[yii\base\View::beginContent()|beginContent()]] e
[[yii\base\View::endContent()|endContent()]]. O parâmetro passado para o
[[yii\base\View::beginContent()|beginContent()]] indica qual é o layout pai. Ele
pode ser um arquivo de layout ou mesmo um alias.

Usando a abordagem acima, você pode aninhar os layouts em mais de um nível.


### Usando Blocos <span id="using-blocks"></span>

Blocos permitem que você especifique o conteúdo da view em um local e o exiba
em outro. Geralmente são usados em conjunto com os layouts. Por exemplo, você pode
definir um bloco no conteúdo de uma view e exibi-lo no layout.

Para definir um bloco, chame os métodos [[yii\base\View::beginBlock()|beginBlock()]]
e [[yii\base\View::endBlock()|endBlock()]].
O bloco pode então ser acessado via `$view->blocks[$blockID]`, onde o `$blockID` é o
identificador único que você associou ao bloco quando o definiu.

O exemplo a seguir mostra como você pode usar blocos para personalizar as partes
especificas de um layout pelo conteúdo da view.

Primeiramente, no conteúdo da view, defina um ou vários blocos:

```php
...

<?php $this->beginBlock('bloco1'); ?>

...conteúdo do bloco1...

<?php $this->endBlock(); ?>

...

<?php $this->beginBlock('bloco3'); ?>

... conteúdo do bloco3...

<?php $this->endBlock(); ?>
```

Em seguida, no layout, renderize os blocos se estiverem disponíveis
ou exiba um conteúdo padrão se não estiverem.

```php
...
<?php if (isset($this->blocks['bloco1'])): ?>
    <?= $this->blocks['bloco1'] ?>
<?php else: ?>
    ... conteúdo padrão para o bloco1 ...
<?php endif; ?>

...

<?php if (isset($this->blocks['bloco2'])): ?>
    <?= $this->blocks['bloco2'] ?>
<?php else: ?>
    ... conteúdo padrão para o bloco2 ...
<?php endif; ?>

...

<?php if (isset($this->blocks['bloco3'])): ?>
    <?= $this->blocks['bloco3'] ?>
<?php else: ?>
    ... conteúdo padrão para o bloco3 ...
<?php endif; ?>
...
```


## Usando Componentes View  <span id="using-view-components"></span>

Os [[yii\base\View|componentes view]] fornecem muitos recursos. Embora você possa obtê-los por criar instancias individuais de [[yii\base\View]]
ou de suas classes filhas, na maioria dos casos você usará o
componente `view` da aplicação. Você pode configurar este componente nas
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

Componentes de view fornecem úteis recursos relacionados. Cada um deles está descrito com mais detalhes em seções separadas:

* [temas](output-theming.md): permite que você desenvolva e altere temas para
  o seu site.
* [fragmento de cache](caching-fragment.md): permite que você guarde em cache um
  fragmento de uma página.
* [manipulação de client scripts](output-client-scripts.md): permite que você registre e renderize CSS e JavaScript.
* [manipulando asset bundle](structure-assets.md): permite que você registre e renderize [recursos estáticos (asset bundles)](structure-assets.md).
* [template engines alternativos](tutorial-template-engines.md): permite que você use outros template engines, tais como o [Twig](https://twig.symfony.com/)
  e [Smarty](https://www.smarty.net/).

Você também pode usar os seguintes recursos que, embora simples, são úteis quando estiver desenvolvendo suas páginas.


### Configurando Títulos de Página <span id="setting-page-titles"></span>

Cada página deve ter um título. Normalmente, a tag `<title>` é exibida em um [layout](#layouts). Mas, na prática, o título é muitas vezes determinado
no conteúdo das views, em vez de nos layouts. Para resolver este
problema, a classe [[yii\web\View]] fornece a propriedade [[yii\web\View::title|title]] para você passar o título a partir das views para o layout.

Para fazer uso deste recurso, em cada view, você pode definir o título da
página conforme o exemplo a seguir:

```php
<?php
$this->title = 'Título da Minha Página';
?>
```

E, no layout, certifique-se de ter o seguinte código dentro da seção `<head>`:

```php
<title><?= Html::encode($this->title) ?></title>
```


### Registrando os Meta Tags <span id="registering-meta-tags"></span>

Páginas Web geralmente precisam gerar variadas meta tags necessárias a
diversas finalidades. Assim como os títulos, as meta tags precisam estar na seção
`<head>` e normalmente são geradas nos layouts.

Se você quiser especificar quais meta tags gerar no conteúdo das views, poderá chamar o método [[yii\web\View::registerMetaTag()]]
na view, conforme o exemplo a seguir:

```php
<?php
$this->registerMetaTag(['name' => 'keywords', 'content' => 'yii, framework, php']);
?>
```

O código acima registrará uma meta tag "keywords" com o componente view.
A meta tag registrada será renderizadas depois de o layout finalizar sua renderização. O código HTML a seguir será gerado e inserido no local onde você chama [[yii\web\View::head()]] no layout:

```php
<meta name="keywords" content="yii, framework, php">
```

Observe que se você chamar o método [[yii\web\View::registerMetaTag()]] muitas vezes, ele registrará diversas meta tags, independente se forem as mesmas ou não.

Para garantir que exista apenas uma única instância de um tipo de meta tag,
você pode especificar uma chave no segundo parâmetro ao chamar o método.
Por exemplo, o código a seguir registra dois meta tags "description". No entanto,
apenas o segundo será renderizado.

```php
$this->registerMetaTag(['name' => 'description', 'content' => 'Este é o meu website feito com Yii!'], 'descricao');
$this->registerMetaTag(['name' => 'description', 'content' => 'Este website é sobre coisas divertidas.'], 'descricao');
```


### Registrando Tags Link <span id="registering-link-tags"></span>

Assim como as [meta tags](#registering-meta-tags), as tags link são úteis em muitos
casos, tais como a personalização do favicon, apontamento para feed RSS ou delegação do OpenID para outros servidores.
Você pode trabalhar com as tags link de forma similar às meta tags, usando o método [[yii\web\View::registerLinkTag()]]. Por
exemplo, na view, você pode registrar uma tag link como segue:

```php
$this->registerLinkTag([
    'title' => 'Notícias sobre o Yii',
    'rel' => 'alternate',
    'type' => 'application/rss+xml',
    'href' => 'https://www.yiiframework.com/rss.xml/',
]);
```

O código acima resultará em

```html
<link title="Notícias sobre o Yii" rel="alternate" type="application/rss+xml" href="https://www.yiiframework.com/rss.xml/">
```

Assim como no método [[yii\web\View::registerMetaTag()|registerMetaTags()]],
você também pode especificar uma chave quando chamar o método
[[yii\web\View::registerLinkTag()|registerLinkTag()]] para evitar a criação de
tags link repetidas.


## Eventos da View  <span id="view-events"></span>

[[yii\base\View|Componentes view]] disparam vários eventos durante
o processo de renderização da view. Você pode usar estes eventos para
inserir conteúdo nas views ou processar os resultados da renderização
antes de serem enviados para os usuários finais.

- [[yii\base\View::EVENT_BEFORE_RENDER|EVENT_BEFORE_RENDER]]: disparado no
  início da renderização de um arquivo em um controller. Funções registradas para esse evento (handlers) podem
  definir a propriedade [[yii\base\ViewEvent::isValid]] como `false` para
  cancelar o processo de renderização.
- [[yii\base\View::EVENT_AFTER_RENDER|EVENT_AFTER_RENDER]]: disparado depois
  da renderização de um arquivo pela chamada de [[yii\base\View::afterRender()]].
  Funções registradas para esse evento (handlers) podem capturar o resultado da renderização por meio da propriedade
  [[yii\base\ViewEvent::output]] e podem modificá-lo para alterar o resultado final.
- [[yii\base\View::EVENT_BEGIN_PAGE|EVENT_BEGIN_PAGE]]: disparado pela chamada
  do método [[yii\base\View::beginPage()]] nos layouts.
- [[yii\base\View::EVENT_END_PAGE|EVENT_END_PAGE]]: disparado pela chamada do
  método [[yii\base\View::endPage()]] nos layouts.
- [[yii\web\View::EVENT_BEGIN_BODY|EVENT_BEGIN_BODY]]: disparado pela chamada
  do método [[yii\web\View::beginBody()]] nos layouts.
- [[yii\web\View::EVENT_END_BODY|EVENT_END_BODY]]: disparado pela chamada do
  método [[yii\web\View::endBody()]] nos layouts.

Por exemplo, o código a seguir insere a data atual no final do corpo da página:

```php
\Yii::$app->view->on(View::EVENT_END_BODY, function () {
    echo date('Y-m-d');
});
```


## Renderizando Páginas Estáticas <span id="rendering-static-pages"></span>

Páginas estáticas referem-se a páginas cujo principal conteúdo é, na maior parte,
estático, sem a necessidade de acessar dados dinâmicos provenientes dos controllers.

Você pode retornar páginas estáticas por colocar seu código na view e então, em um controller, usar o código a seguir:

```php
public function actionAbout()
{
    return $this->render('about');
}
```

Se o site contiver muitas páginas estáticas, seria tedioso repetir
os códigos similares muitas vezes.
Para resolver este problema, você pode inserir uma
[action "externa" (standalone action)](structure-controllers.md#standalone-actions) chamando a classe
[[yii\web\ViewAction]] em um controller. Por exemplo:

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

Agora, se você criar uma view chamada `sobre` no diretório
`@app/views/site/pages`, poderá exibir por meio da
seguinte URL:

```
http://localhost/index.php?r=site/page&view=sobre
```

O parâmetro `view` passado via `GET` informa à classe [[yii\web\ViewAction]]
qual view foi solicitada. A action, então, irá procurar essa view informada
dentro do diretório `@app/views/site/pages`. Você pode configurar a propriedade
[[yii\web\ViewAction::viewPrefix]] para alterar o diretório onde as views
serão procuradas.


## Boas Práticas <span id="best-practices"></span>

Views são responsáveis por apresentar models (modelos) no formato que os
usuários finais desejam. Em geral, views:

* devem conter principalmente código de apresentação, tal como o HTML, e trechos
  simples de PHP para percorrer, formatar e renderizar dados.
* não devem conter código de consulta ao banco de dados. Consultas assim devem ser feitas nos models.
* devem evitar acessar diretamente os dados da requisição, tais como `$_GET` e
  `$_POST` pois essa tarefa cabe aos controllers.
  Se os dados da requisição forem necessários, deverão ser fornecidos às views
  pelos controllers.
* podem ler as propriedades dos models, mas não devem alterá-las.

Para tornar as views mais gerenciáveis, evite criar views muito complexas
ou que contenham muito código redundante. Você pode usar as seguintes técnicas
para atingir este objetivo:

* use [layouts](#layouts) para representar as seções de apresentação comuns
  (por exemplo, cabeçalho e rodapé).
* divida uma view complicada em varias outras menores. As views
  menores podem ser renderizadas e montadas em uma maior usando os métodos descritos anteriormente.
* crie e use [widgets](structure-widgets.md) como blocos de construção das views.
* crie e use as classes helper (auxiliares) para transformar e formatar os dados nas views.
