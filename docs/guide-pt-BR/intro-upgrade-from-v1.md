Atualizando a partir da Versão 1.1
==================================

Existem muitas diferenças entre as versões 1.1 e 2.0 do Yii, uma vez que o
framework foi completamente reescrito na 2.0.
Por causa disso, atualizar a partir da versão 1.1 não é tão trivial quanto atualizar de versões menores. Neste guia você encontrará as principais diferenças entre as duas versões.

Se você nunca usou o Yii 1.1 antes, você pode pular com segurança esta seção
e ir diretamente para "[Instalando o Yii](start-installation.md)".

Por favor, note que o Yii 2.0 introduz outras novas funcionalidades além das que
são abordadas neste resumo. Recomenda-se fortemente que você leia o guia definitivo por completo para aprender todas elas. É possível que algumas funcionalidades
que antes você tinha de desenvolver por conta própria agora façam parte do
código principal.


Instalação
----------

O Yii 2.0 utiliza plenamente o [Composer](https://getcomposer.org/), o gerenciador
de pacotes PHP. Tanto a instalação do núcleo do framework quanto das extensões são feitas através do Composer. Por favor, consulte a seção
[Instalando o Yii](start-installation.md) para aprender como instalar
o Yii 2.0. Se você quer criar novas extensões ou tornar compatíveis as suas extensões
existentes do 1.1 com o 2.0, por favor consulte a seção [Criando Extensões](structure-extensions.md#creating-extensions)
do guia.


Requisitos do PHP
-----------------

O Yii 2.0 requer o PHP 5.4 ou superior, que é uma versão de grande melhoria sobre a versão 5.2, que era exigida pelo Yii 1.1.
Como resultado, existem muitas diferenças na linguagem às quais você deve dar a devida atenção.
Segue abaixo um resumo das principais mudanças do PHP:

- [Namespaces](https://www.php.net/manual/pt_BR/language.namespaces.php).
- [Funções anônimas](https://www.php.net/manual/pt_BR/functions.anonymous.php).
- A sintaxe curta de arrays `[...elementos...]` é utilizada ao invés de `array(...elementos...)`.
- Tags curtas de *echo* `<?=` são usadas nos arquivos de view. É seguro utilizá-las a partir do PHP 5.4.
- [Classes e interfaces da SPL](https://www.php.net/manual/pt_BR/book.spl.php).
- [Late Static Bindings](https://www.php.net/manual/pt_BR/language.oop5.late-static-bindings.php).
- [Date e Time](https://www.php.net/manual/pt_BR/book.datetime.php).
- [Traits](https://www.php.net/manual/pt_BR/language.oop5.traits.php).
- [intl](https://www.php.net/manual/pt_BR/book.intl.php). O Yii 2.0 utiliza a extensão
  `intl` do PHP para suportar as funcionalidades de internacionalização.


Namespace
---------

A mudança mais óbvia no Yii 2.0 é o uso de namespaces. Praticamente todas as
classes do *core* possuem namespace, por exemplo, `yii\web\Request`. O prefixo "C"
não é mais utilizado nos nomes de classes. O esquema de nomenclatura agora segue
a estrutura de diretórios. Por exemplo, `yii\web\Request` indica que o arquivo
da classe correspondente é `web/Request.php` sob a pasta do Yii Framework.

(Você pode utilizar qualquer classe do *core* sem explicitamente incluir o arquivo
dessa classe, graças ao carregador de classes do Yii).


Component e Object
------------------

O Yii 2.0 divide a classe `CComponent` do 1.1 em duas classes: [[yii\base\BaseObject]]
e [[yii\base\Component]]. A classe [[yii\base\BaseObject|BaseObject]] é uma classe base
leve que permite a definição das [propriedades de objetos](concept-properties.md)
via getters e setters. A classe [[yii\base\Component|Component]] estende de
[[yii\base\BaseObject|BaseObject]] e suporta [eventos](concept-events.md) e
[comportamentos (behaviors)](concept-behaviors.md).


Se a sua classe não precisa de eventos nem de comportamentos,
você deveria considerar utilizar [[yii\base\BaseObject|BaseObject]] como classe base.
Esse geralmente é o caso de classes que representam estruturas básicas de dados.


Configuração de Objetos
-----------------------

A classe [[yii\base\BaseObject|BaseObject]] introduz uma maneira uniforme de configurar
objetos. Qualquer classe descendente de [[yii\base\BaseObject|BaseObject]] deveria
declarar seu construtor (se necessário) da seguinte maneira, para que ela
seja configurada adequadamente:

```php
class MinhaClasse extends \yii\base\BaseObject
{
    public function __construct($param1, $param2, $config = [])
    {
        // ... inicialização antes da configuração ser aplicada

        parent::__construct($config);
    }

    public function init()
    {
        parent::init();

        // ... inicialização depois da configuração ser aplicada
    }
}
```

No código acima, o último parâmetro do construtor deve receber um array de
configuração que contém pares de nome-valor para a inicialização das propriedades
no final do construtor. Você pode sobrescrever o método [[yii\base\BaseObject::init()|init()]]
para fazer o trabalho de inicialização que deve ser feito após a configuração
ter sido aplicada.

Seguindo esta convenção, você poderá criar e configurar novos objetos usando um
array de configuração:

```php
$object = Yii::createObject([
    'class' => 'MinhaClasse',
    'property1' => 'abc',
    'property2' => 'cde',
], [$param1, $param2]);
```

Mais detalhes sobre configurações podem ser encontrados na seção de
[Configurações](concept-configurations.md).


Eventos
-------

No Yii 1 os eventos eram criados definindo-se um método `on`-alguma-coisa
(por exemplo, `onBeforeSave`). No Yii 2 você pode usar qualquer nome de
evento. Você dispara um evento chamando o método
[[yii\base\Component::trigger()|trigger()]]:

```php
$evento = new \yii\base\Event;
$componente->trigger($nomeDoEvento, $evento);
```

Para anexar um ouvinte (handler) a um evento, use o método [[yii\base\Component::on()|on()]]:

```php
$componente->on($nomeDoEvento, $handler);
// Para desanexar o handler, utilize:
// $componente->off($nomeDoEvento, $handler);
```

Há muitas melhorias nas funcionalidades de evento. Para mais detalhes,
por favor, consulte a seção [Eventos](concept-events.md).


Path Aliases
------------

O Yii 2.0 expande o uso de *path aliases* (apelidos de caminhos) tanto para caminhos de arquivos e diretórios como para URLs. Agora ele requer que um nome de alias
comece com o caractere `@` para diferenciar entre aliases e caminhos e URLs normais de arquivos e diretórios. Por exemplo, o alias `@yii` se refere ao diretório de
instalação do Yii. Os path aliases são suportados na maior porte do código do core
do Yii. Por exemplo, o método [[yii\caching\FileCache::cachePath]] pode receber
tanto um path alias quanto um caminho de diretório normal.

Um path alias também está intimamente relacionado a um namespace de classe.
É recomendado que um path alias seja definido para cada namespace raiz, desta forma
permitindo que você use o auto-carregamento de classes do Yii sem qualquer
configuração adicional. Por exemplo, como `@yii` se refere ao diretório de
instalação do Yii, uma classe como `yii\web\Request` pode ser carregada
automaticamente. Se você utilizar uma biblioteca de terceiros, tal como o Zend
Framework, você pode definir um path alias `@Zend` que se refere ao diretório
de instalação desse framework. Uma vez que você tenha feito isso, o Yii também
poderá carregar automaticamente qualquer classe nessa biblioteca do Zend Framework.

Você pode encontrar mais informações sobre *path aliases* na seção [Aliases](concept-aliases.md).


Views (Visões)
--------------

A mudança mais significante das views no Yii 2 é que a variável especial `$this`
em uma view não se refere mais ao controller ou widget atual. Ao invés disso, `$this` agora se refer[ a um objeto **view**, um novo conceito
introduzido no 2.0. O objeto *view* é do tipo [[yii\web\View]] e representa a
parte da visão do padrão MVC. Se você quiser acessar o controller ou
o widget em uma visão, você pode utilizar `$this->context`.

Para renderizar uma view parcial (partial view) dentro de outra view, você usa
`$this->render()`, e não `$this->renderPartial()`. Agora a chamada de `render`
também precisa ser explicitamente impressa com *echo*, uma vez que o métood
`render()` retorna o resultado da renderização ao invés de exibi-lo diretamente.
Por exemplo:

```php
echo $this->render('_item', ['item' => $item]);
```

Além de utilizar o PHP como linguagem de template principal, o Yii 2.0 também
é equipado com suporte oficial a duas populares engines de template: Smarty e
Twig. A engine de template do Prado não é mais suportada. Para utilizar essas
engines de template, você precisa configurar o componente de aplicação `view`
definindo a propriedade [[yii\base\View::$renderers|View::$renderers]]. Por favor
consulte a seção [Template Engines](tutorial-template-engines.md) para mais
detalhes.


Models (Modelos)
----------------

O Yii 2.0 usa o [[yii\base\Model]] como base, semelhante à `CModel` no 1.1.
A classe `CFormModel` foi removida inteiramente. Ao invés dela, no Yii 2 você
deve estender a classe [[yii\base\Model]] parar criar uma classe de model de formulário.

O Yii 2.0 introduz um novo método chamado [[yii\base\Model::scenarios()|scenarios()]]
para declarar os cenários suportados, para indicar sob qual cenário um atributo
precisa ser validado ou pode ser considerado safe (seguro) ou não, etc. Por exemplo:

```php
public function scenarios()
{
    return [
        'backend' => ['email', 'cargo'],
        'frontend' => ['email', '!cargo'],
    ];
}
```

No código acima, dois cenários são declarados: `backend` e `frontend`. Para o
cenário `backend`, os atributos `email` e `cargo` são seguros (safe) e podem ser
atribuídos em massa. Para o cenário `frontend`, `email` pode ser atribuído em
massa enquanto `cargo` não. Tanto `email` quanto `role` devem ser validados utilizando-se
*rules* (regras).

O método [[yii\base\Model::rules()|rules()]] ainda é usado para declarar regras
de validação. Perceba que devido à introdução do método [[yii\base\Model::scenarios()|scenarios()]],
não existe mais o validador `unsafe` (inseguro).

Na maioria dos casos, você não precisa sobrescrever [[yii\base\Model::scenarios()|scenarios()]]
se o método [[yii\base\Model::rules()|rules()]] especifica completamente os
cenários que existirão, e se não houver necessidade para declarar atributos
`unsafe`.

Para aprender mais sobre models, por favor consulte a seção [Models (Modelos)](basic-models.md).


Controllers (Controladores)
---------------------------

O Yii 2.0 utiliza a [[yii\web\Controller]] como classe base dos controllers de maneira semelhante à `CWebController` no Yii 1.1. A
[[yii\base\Action]] é a classe base para classes de actions (ações).

O impacto mais óbvio destas mudanças em seu código é que uma action de um controller
deve sempre retornar o conteúdo que você quer renderizar ao invés de dar *echo* nele:

```php
public function actionView($id)
{
    $model = \app\models\Post::findOne($id);
    if ($model) {
        return $this->render('exibir', ['model' => $model]);
    } else {
        throw new \yii\web\NotFoundHttpException;
    }
}
```

Por favor, consulte a seção [Controllers (Controladores)](structure-controllers.md) para mais detalhes.


Widgets
-------

O Yii 2.0 usa [[yii\base\Widget]] como a classe base dos widgets, de maneira
semelhante à `CWidget` no Yii 1.1.

Para obter um melhor suporte ao framework nas IDEs, o Yii 2.0 introduz uma nova
sintaxe para utilização de widgets. Os métodos estáticos [[yii\base\Widget::begin()|begin()]],
[[yii\base\Widget::end()|end()]] e [[yii\base\Widget::widget()|widget()]] foram
introduzidos, para serem utilizados do seguinte modo:

```php
use yii\widgets\Menu;
use yii\widgets\ActiveForm;

// Note que você tem que dar um "echo" no resultado para exibi-lo
echo Menu::widget(['items' => $items]);

// Passando um array para inicializar as propriedades do objeto
$form = ActiveForm::begin([
    'options' => ['class' => 'form-horizontal'],
    'fieldConfig' => ['inputOptions' => ['class' => 'input-xlarge']],
]);
... campos do formulário aqui ...
ActiveForm::end();
```

Por favor, consulte a seção [Widgets](structure-widgets.md) para mais detalhes.


Temas
-----

Os temas funcionam de maneira completamente diferente no 2.0. Agora eles se baseiam
em um mecanismo de mapeamento de caminhos que mapeia um caminho de arquivo de view fonte
a um caminho de arquivo de view com o tema. Por exemplo, se o mapa de caminho de um tema
é `['/web/views' => '/web/themes/basic']`, então a versão com tema deste arquivo
de view `/web/views/site/index.php` será `/web/themes/basic/site/index.php`.
Por esse motivo, os temas agora podem ser aplicados a qualquer arquivo de view,
até mesmo uma view renderizada fora do contexto de um controller ou widget.

Além disso, não há mais um componente `CThemeManager`. Em vez disso, `theme` é
uma propriedade configurável do componente `view` da aplicação.

Por favor, consulte a seção [Temas](tutorial-theming.md) para mais detalhes.


Aplicações de Console
---------------------

As aplicações de console agora são organizadas como controllers
assim como as aplicações web. Os controllers de console devem estender de [[yii\console\Controller]],
de maneira semelhante à `CConsoleCommand` no 1.1.

Para rodar um comando do console, use `yii <rota>`, onde `<rota>` representa a rota de
um controller (por exemplo, `sitemap/index`). Argumentos anônimos adicionais são
passados como parâmetros à action correspondente no controller, enquanto
argumentos com nome são "convertidos" de acordo com as declarações em [[yii\console\Controller::options()]].

O Yii 2.0 suporta a geração automática de informação de ajuda do comando a partir de blocos de comentários.

Por favor consulte a seção [Comandos de Console](tutorial-console.md) para mais detalhes.


I18N
----

O Yii 2.0 remove os formatadores de data e número embutidos em favor do módulo
[intl do PECL do PHP](https://pecl.php.net/package/intl).

A tradução de mensagens agora é realizada pelo componente `i18n` da aplicação.
Este componente gerencia um conjunto de fontes de mensagens, o  que permite a você usar diferentes fontes de mensagens baseadas em categorias de mensagens.

Por favor consulte a seção [Internacionalização](tutorial-i18n.md) para mais detalhes.


Action Filters (Filtros de Ação)
--------------------------------

Agora os filtros de ação (action filters) são implementados via comportamentos (behaviors).
Para definir um novo filtro personalizado, estenda de [[yii\base\ActionFilter]].
Para usar um filtro, anexe a classe do filtro ao controller como um behavior. Por exemplo, para usar o filtro [[yii\filters\AccessControl]],
você teria o seguinte código em um controller:

```php
public function behaviors()
{
    return [
        'access' => [
            'class' => 'yii\filters\AccessControl',
            'rules' => [
                [
                    'allow' => true,
                    'actions' => ['admin'],
                    'roles' => ['@']
                ],
            ],
        ],
    ];
}
```

Por favor, consulte a seção [Filtragem](structure-filters.md) para mais detalhes.


Assets
------

O Yii 2.0 introduz um novo conceito chamado de *asset bundle* (pacote de recursos estáticos)
que substitui o conceito de script packages (pacotes de script) encontrado no Yii 1.1.

Um *asset bundle* é uma coleção de arquivos de assets (por exemplo, arquivos JavaScript,
arquivos CSS, arquivos de imagens, etc.) dentro de um diretório. Cada *asset bundle*
é representado por uma classe que estende [[yii\web\AssetBundle]]. Ao registrar
um *asset bundle* via [[yii\web\AssetBundle::register()]], você torna os assets
deste pacote acessíveis via Web. Ao contrário do Yii 1, a página que registra o
*bundle* automaticamente conterá as referências aos arquivos JavaScript e CSS
especificados naquele *bundle*.

Por favor consulte a seção [Gerenciando Assets](output-assets.md) para mais detalhes.


Helpers - Classes Auxiliares
-------

O Yii 2.0 introduz muitas classes auxiliares (helpers) estáticas comumente usadas, incluindo:

* [[yii\helpers\Html]]
* [[yii\helpers\ArrayHelper]]
* [[yii\helpers\StringHelper]]
* [[yii\helpers\FileHelper]]
* [[yii\helpers\Json]]

Por favor, consulte a seção [Visão Geral](helper-overview.md) dos helpers para mais detalhes.

Forms
-----

O Yii 2.0 introduz o conceito de campos (*fields*) para a construção de
formulários usando [[yii\widgets\ActiveForm]]. Um *field* é um container
consistindo de um *label*, um *input*, uma mensagem de erro, e/ou um texto
de ajuda. Um *field* é representado como um objeto [[yii\widgets\ActiveField|ActiveField]].
Usando *fields* você pode construir um formulário de maneira mais limpa do que antes:

```php
<?php $form = yii\widgets\ActiveForm::begin(); ?>
    <?= $form->field($model, 'login') ?>
    <?= $form->field($model, 'senha')->passwordInput() ?>
    <div class="form-group">
        <?= Html::submitButton('Entrar') ?>
    </div>
<?php yii\widgets\ActiveForm::end(); ?>
```

Por favor, consulte a seção [Criando um Formulário](input-forms.md) para mais detalhes.


Query Builder (Construtor de Consultas)
---------------------------------------

No 1.1, a construção de consultas estava espalhada por diversas classes, incluindo
a `CDbCommand`, a `CDbCriteria` e a `CDbCommandBuilder`. O Yii 2.0 representa uma
consulta do banco de dados em termos de um objeto [[yii\db\Query|Query]] que pode
ser convertido em uma instrução SQL com a ajuda do [[yii\db\QueryBuilder|QueryBuilder]]
que está por trás das cortinas.
Por exemplo:

```php
$query = new \yii\db\Query();
$query->select('id, nome')
      ->from('usuario')
      ->limit(10);

$command = $query->createCommand();
$sql = $command->sql;
$rows = $command->queryAll();
```

E o melhor de tudo, estes métodos de construção de consultas também podem ser utilizados ao trabalhar com o [Active Record](db-active-record.md).

Por favor, consulte a seção [Query Builder](db-query-builder.md) para mais detalhes.


Active Record
-------------

O Yii 2.0 introduz várias mudanças ao [Active Record](db-active-record.md). As
duas mais óbvias envolvem a construção de consultas simples e o tratamento de consultas relacionais.

A classe `CDbCriteria` do 1.1 foi substituída pela [[yii\db\ActiveQuery]] do Yii 2.
Essa classe estende de [[yii\db\Query]], e assim herda todos os métodos de
construção de consultas. Você chama [[yii\db\ActiveRecord::find()]] para começar a construir uma consulta:

```php
// Para obter todos os clientes *ativos* e ordená-los pelo ID:
$customers = Cliente::find()
    ->where(['status' => $ativo])
    ->orderBy('id')
    ->all();
```

Para declarar um relacionamento, simplesmente defina um método getter que retorne
um objeto [[yii\db\ActiveQuery|ActiveQuery]]. O nome da propriedade definida pelo
getter representa o nome do relacionamento. Por exemplo, o código a seguir
declara um relacionamento `pedidos` (no 1.1, você teria que declarar as relações
em um local central, `relations()`):

```php
class Cliente extends \yii\db\ActiveRecord
{
    public function getPedidos()
    {
        return $this->hasMany('Pedido', ['id_cliente' => 'id']);
    }
}
```

Agora você pode usar `$cliente->pedidos` para acessar os pedidos de um cliente a partir da tabela relacionada.
Você também pode usar o código a seguir para realizar uma consulta relacional imediata (*on-the-fly*) com uma condição personalizada:

```php
$pedidos = $cliente->getPedidos()->andWhere('status=1')->all();
```

Ao fazer o eager loading (carregamento antecipado) de um relacionamento,
o Yii 2.0 faz isso de maneira diferente do 1.1. Em particular, no 1.1 uma consulta
JOIN seria criada para selecionar tanto o registro primário quanto os de
relacionamentos. No Yii 2.0, duas instruções SQL são executadas sem usar JOIN:
a primeira instrução retorna os registros primários e a segunda retorna os registros
relacionados por filtrar pelas chaves primárias dos registros primários.

Em vez de retornar objetos [[yii\db\ActiveRecord|ActiveRecord]], você pode
encadear o método [[yii\db\ActiveQuery::asArray()|asArray()]] ao construir uma
consulta para retornar um grande número de registros. Isso fará com que o resultado
da consulta retorne como arrays, o que pode reduzir significativamente o tempo
de CPU e memória necessários para um grande número de registros. Por exemplo,

```php
$clientes = Cliente::find()->asArray()->all();
```

Outra mudança é que você não pode mais definir valores padrão de atributos através
de propriedades públicas. Se você precisar disso, você deve defini-las no método
init na classe do seu registro.

```php
public function init()
{
    parent::init();
    $this->status = self::STATUS_NOVO;
}
```

Havia alguns problemas ao sobrescrever o construtor de uma classe ActiveRecord
no 1.1. Estes não ocorrem mais na versão 2.0. Perceba que ao adicionar parâmetros
ao construtor você pode ter que sobrescrever o método [[yii\db\ActiveRecord::instantiate()]].

Existem muitas outras mudanças e melhorias no Active Record. Por favor, consulte
a seção [Active Record](db-active-record.md) para mais detalhes.


Comportamentos (Behaviors) do Active Record
-----------------------

No Yii 2, removemos a classe base de behaviors `CActiveRecordBehavior`. Se você quer criar um Active Record Behavior, você terá que estender diretamente de `yii\base\Behavior`. Se a classe behavior class precisa responder a algums eventos da classe que a possui, você deve sobrescrever o método `events()` conforme a seguir:

```php
namespace app\components;

use yii\db\ActiveRecord;
use yii\base\Behavior;

class MeuComportamento extends Behavior
{
    // ...

    public function events()
    {
        return [
            ActiveRecord::EVENT_BEFORE_VALIDATE => 'beforeValidate',
        ];
    }

    public function beforeValidate($event)
    {
        // ...
    }
}
```


User e IdentityInterface
------------------------

A classe `CWebUser` do 1.1 foi substituída pela [[yii\web\User]] e não há
mais a classe `CUserIdentity`. Em vez disso, você deve implementar a interface
[[yii\web\IdentityInterface]] que é muito mais simples de usar. O template avançado
de projetos fornece um exemplo de como fazer isso.

Por favor, consulte as seções [Autenticação](security-authentication.md),
[Autorização](security-authorization.md) e [Template Avançado de Projetos](tutorial-advanced-app.md)
para mais detalhes.


Gerenciamento de URLs
---------------------

O gerenciamento de URLs no Yii 2 é semelhante ao do 1.1. Uma grande melhoria é que o gerenciamento de URLs agora suporta parâmetros opcionais. Por exemplo,
se você tiver uma regra declarada como a seguir, ela vai corresponder tanto a `post/popular` quanto a `post/1/popular`. No 1.1, você teria que usar duas
regrar para fazer isso.

```php
[
    'pattern' => 'post/<page:\d+>/<tag>',
    'route' => 'post/index',
    'defaults' => ['page' => 1],
]
```

Por favor, consulte a seção [Roteamento e Criação de URL](runtime-routing.md) para mais detalhes.

Uma importante mudança nas convenções de nomes para rotas é que actions e controllers com nomes em estilo camel case agora, quando referenciados em rotas, são convertidos para minúsculas separando cada palavra com um hífen. Por exemplo, o ID de controller `GestaoDeClientesController` deve ser referenciado em uma rota como `gestao-de-clientes`.

Veja as seções [IDs de Controllers](structure-controllers.md#controller-ids) e [IDs de Actions](structure-controllers.md#action-ids) para mais detalhes.


Utilizando o Yii 1.1 e o 2.x juntos
-----------------------------------

Se você tem código legado do Yii 1.1 que você quer utilizar com o Yii 2.0, por favor, consulte a seção [Usando Yii 1.1 e 2.0 juntos](tutorial-yii-integration.md).
