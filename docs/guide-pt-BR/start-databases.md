Trabalhando com Bancos de Dados
===============================

Esta seção descreverá como criar uma nova página que exibe informações de países obtidos de uma tabela de banco de dados chamada `pais`. Para isso, você
configurará uma conexão com o banco de dados, criará uma classe de
[Active Record](db-active-record.md), definirá uma [action](structure-controllers.md) e criará uma [view](structure-views.md).

Ao longo deste tutorial, você aprenderá como:

* configurar uma conexão de BD
* definir uma classe Active Record
* consultar dados usando a classe de Active Record
* exibir dados em uma view de forma paginada

Perceba que para terminar essa seção, você deve ter conhecimento e experiência
básicos em bancos de dados. Em particular, você deve saber como criar um banco de dados e como executar instruções SQL usando uma ferramenta cliente de bancos de dados.


Preparando o Banco de Dados <span id="preparing-database"></span>
---------------------------

Para começar, crie um banco de dados chamado `yii2basico`, de onde você
obterá os dados em sua aplicação. Você pode criar um banco de dados SQLite, MySQL,
PostgreSQL, MSSQL ou Oracle, já que o Yii tem suporte embutido a vários gerenciadores de bancos de dados. Por questões de simplicidade, será assumido o uso do MySQL
na descrição a seguir.

> Info: O MariaDB costumava ser um substituto transparente do MySQL. Isto já não é mais totalmente verdade. Caso você queira usar recursos avançados como suporte a `JSON` no MariaDB, por favor, consulte a extensão do MariaDB listada mais à frente.

Em seguida, crie uma tabela chamada `pais` no banco de dados e insira alguns
dados de exemplo. Você pode rodar as seguintes declarações SQL para fazer isso:

```sql
CREATE TABLE `pais` (
  `codigo` CHAR(2) NOT NULL PRIMARY KEY,
  `nome` CHAR(52) NOT NULL,
  `populacao` INT(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `pais` VALUES ('AU','Austrália',24016400);
INSERT INTO `pais` VALUES ('BR','Brasil',205722000);
INSERT INTO `pais` VALUES ('CA','Canadá',35985751);
INSERT INTO `pais` VALUES ('CN','China',1375210000);
INSERT INTO `pais` VALUES ('DE','Alemanha',81459000);
INSERT INTO `pais` VALUES ('FR','França',64513242);
INSERT INTO `pais` VALUES ('GB','Reino Unido',65097000);
INSERT INTO `pais` VALUES ('IN','Índia',1285400000);
INSERT INTO `pais` VALUES ('RU','Rússia',146519759);
INSERT INTO `pais` VALUES ('US','Estados Unidos',322976000);
```

Neste ponto, você tem um banco de dados chamado `yii2basico` e dentro dele uma
tabela `pais` com três colunas, contendo dez linhas de dados.

Configurando uma Conexão de BD <span id="configuring-db-connection"></span>
------------------------------

Antes de prosseguir, certifique-se de que você possui instalados tanto a
extensão [PDO](https://www.php.net/manual/pt_BR/book.pdo.php) do PHP quanto o driver
PDO para o gerenciador de banco de dados que você está usando (por exemplo, `pdo_mysql` para o MySQL).
Este é um requisito básico se a sua aplicação usa um banco de dados relacional.

Tendo esses instalados, abra o arquivo `config/db.php` e mude os parâmetros conforme seu banco de dados.
Por padrão, o arquivo contém o seguinte:

```php
<?php

return [
    'class' => 'yii\db\Connection',
    'dsn' => 'mysql:host=localhost;dbname=yii2basico',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8',
];
```

O arquivo `config/db.php` é uma típica ferramenta de [configuração](concept-configurations.md) baseada em arquivo. Este arquivo de configuração em particular especifica
os parâmetros necessários para criar e inicializar uma instância [[yii\db\Connection]]
por meio da qual você pode fazer consultas SQL ao banco de dados subjacente.

A conexão configurada acima pode ser acessada no código da aplicação através da expressão `Yii::$app->db`.

> Info: O arquivo `config/db.php` será absorvido (incluso) pela configuração principal da
  aplicação `config/web.php`, que especifica como a instância da [aplicação](structure-applications.md)
  deve ser inicializada. Para mais informações, por favor, consulte a seção [Configurações](concept-configurations.md).

Se você precisa trabalhar com bancos de dados para os quais não há suporte nativo no Yii, consulte as seguintes extensões:

- [Informix](https://github.com/edgardmessias/yii2-informix)
- [IBM DB2](https://github.com/edgardmessias/yii2-ibm-db2)
- [Firebird](https://github.com/edgardmessias/yii2-firebird)
- [MariaDB](https://github.com/sam-it/yii2-mariadb)

Criando um Active Record <span id="creating-active-record"></span>
------------------------

Para representar e buscar os dados da tabela `pais`, crie uma classe que deriva de [Active Record](db-active-record.md) chamada `Pais` e salve-a
no arquivo `models/Pais.php`.

```php
<?php

namespace app\models;

use yii\db\ActiveRecord;

class Pais extends ActiveRecord
{
}
```

A classe `Pais` estende de [[yii\db\ActiveRecord]]. Você não precisa escrever
nenhum código nela! Só com o código acima, o Yii descobrirá o nome da tabela
associada a partir do nome da classe.

> Info: Se não houver nenhuma correspondência direta do nome da classe com o nome
  da tabela, você pode sobrescrever o método [[yii\db\ActiveRecord::tableName()]]
  para especificar explicitamente o nome da tabela associada.

Usando a classe `Pais`, você pode manipular facilmente os dados na tabela
`pais`, conforme é demonstrado nos fragmentos de código a seguir:

```php
use app\models\Pais;

// obtém todas as linhas da tabela pais e as ordena pela coluna "nome"
$paises = Pais::find()->orderBy('nome')->all();

// obtém a linha cuja chave primária é "BR"
$pais = Pais::findOne('BR');

// exibe "Brasil"
echo $pais->nome;

// altera o nome do país para "Brazil" e o salva no banco de dados
$pais->nome = 'Brazil';
$pais->save();
```

> Info: O Active Record é uma maneira poderosa de acessar e manipular dados
  do banco de dados de uma forma orientada a objetos. Você pode encontrar informações
  mais detalhadas na seção [Active Record](db-active-record.md. Alternativamente,
  você também pode interagir com o banco de dados usando um método de acesso a
  dados em baixo nível chamado [Objeto de Acesso a Dados (Data Access Objects)](db-dao.md).


Criando uma Action <span id="creating-action"></span>
------------------

Para disponibiliar os dados de países aos usuários finais, você precisa criar uma nova
action. Em vez de colocar a nova action no controller `site`
como você fez nas seções anteriores, faz mais sentido criar um novo controller
especificamente para todas as actions relacionadas aos dados de países. Chame
este novo controller de `PaisController`, e crie uma action `index` nele,
conforme o exemplo a seguir:

```php
<?php

namespace app\controllers;

use yii\web\Controller;
use yii\data\Pagination;
use app\models\Pais;

class PaisController extends Controller
{
    public function actionIndex()
    {
        $query = Pais::find();

        $paginacao = new Pagination([
            'defaultPageSize' => 5,
            'totalCount' => $query->count(),
        ]);

        $paises = $query->orderBy('nome')
            ->offset($paginacao->offset)
            ->limit($paginacao->limit)
            ->all();

        return $this->render('index', [
            'paises' => $paises,
            'paginacao' => $paginacao,
        ]);
    }
}
```

Salve o código acima no arquivo `controllers/PaisController.php`.

A action `index` chama `Pais::find()`. Este método do Active Record constrói
uma consulta ao BD e retorna todos os dados da tabela `pais`. Para limitar o
número de países retornados a cada requisição, a consulta é paginada com a ajuda
de um objeto [[yii\data\Pagination]]. O objeto `Pagination` serve para dois propósitos:

* Define as cláusulas `offset` e `limit` da declaração SQL representada pela query
  (consulta) de modo que apenas retorne uma única página de dados por vez (no exemplo, no máximo
  5 linhas por página).
* É usado na view para exibir um paginador que consiste de uma lista de botões de páginas, conforme será explicado na próxima subseção.

No final do código, a action `index` renderiza uma view chamada `index` e envia a ela os dados dos países e as informações de paginação.


Criando uma View <span id="creating-view"></span>
----------------

Dentro do diretório `views`, primeiro crie um subdiretório chamado `pais`.
Esta pasta será usada para guardar todas as views renderizadas pelo controller
`PaisController`. Dentro do diretório `views/pais`, crie um arquivo `index.php`
contendo o seguinte:

```php
<?php
use yii\helpers\Html;
use yii\widgets\LinkPager;
?>
<h1>Países</h1>
<ul>
<?php foreach ($paises as $pais): ?>
    <li>
        <?= Html::encode("{$pais->nome} ({$pais->codigo})") ?>:
        <?= $pais->populacao ?>
    </li>
<?php endforeach; ?>
</ul>

<?= LinkPager::widget(['pagination' => $paginacao]) ?>
```

A view tem duas seções relativas à exibição dos dados dos países. Na primeira parte,
os dados de países fornecidos são percorridos e renderizados como uma lista HTML.
Na segunda parte, um widget [[yii\widgets\LinkPager]] é renderizado usando as
informações de paginação passadas pela action. O widget `LinkPager` exibe uma
lista de botões de páginas. Clicar em qualquer um deles vai atualizar os dados dos países conforme a página correspondente.


Conferindo <span id="trying-it-out"></span>
--------

Para ver se todo os códigos acima funcionam, use o seu navegador para acessar a seguinte URL:

```
https://hostname/index.php?r=pais/index
```

![Lista de Países](images/start-country-list.png)

Primeiramente, você verá uma lista exibindo cinco países. Abaixo dos países,
você verá um paginador com quatro botões. Se você clicar no botão "2", você
verá a página exibir outros cinco países do banco de dados: a segunda
página de registros. Observe mais cuidadosamente e você perceberá que a URL no
browser mudou para

```
https://hostname/index.php?r=pais/index&page=2
```

Por trás das cortinas, [[yii\data\Pagination|Pagination]] está fornecendo toda
a funcionalidade necessária para paginar um conjunto de dados:

* Inicialmente, [[yii\data\Pagination|Pagination]] representa a primeira página,
  que reflete a consulta SELECT de países com a cláusula `LIMIT 5 OFFSET 0`.
  Como resultado, os primeiros cinco países serão buscados e exibidos.
* O widget [[yii\widgets\LinkPager|LinkPager]] renderiza os botões das páginas
  usando as URLs criadas pelo [[yii\data\Pagination::createUrl()|Pagination]].
  As URLs conterão um parâmetro `page`, que representa os diferentes números de
  páginas.
* Se você clicar no botão de página "2", uma nova requisição para a rota
  `pais/index` será disparada e tratada. [[yii\data\Pagination|Pagination]] lê
  o parâmetro `page` da URL e define o número da página atual como sendo 2. A nova
  consulta de países então terá a cláusula `LIMIT 5 OFFSET 5` e retornará os
  próximos cinco países para a exibição.


Resumo <span id="summary"></span>
------

Nesta seção, você aprendeu como trabalhar com um banco de dados. Você também
aprendeu como buscar e exibir dados em páginas com a ajuda do
[[yii\data\Pagination]] e do [[yii\widgets\LinkPager]].

Na próxima seção, você aprenderá como usar a poderosa ferramenta de geração de códigos,
chamada [Gii](tool-gii.md), para ajudá-lo a implementar rapidamente algumas
funcionalidades comumente necessárias, tais como as operações CRUD
(Criar-Ler-Atualizar-Excluir) para trabalhar com os dados de uma tabela do
banco de dados. Na verdade, todo o código que você acabou de escrever pode ser
gerado automaticamente no Yii usando a ferramenta Gii.
