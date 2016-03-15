Ordenação
=======

Ao exibir várias linhas de dados, muitas vezes é necessário que os dados sejam ordenados de acordo com algumas colunas especificadas pelos usuários finais. O Yii utiliza um objeto [[yii\data\Sort]] para representar as informações sobre um esquema de ordenação. Em particular, 

* [[yii\data\Sort::$attributes|attributes]] especifica os *atributos* através dos quais os dados podem ser ordenados.
  Um atributo pode ser simples como um [atributo do model](structure-models.md#attributes). Ele também pode ser um composto por uma combinação de múltiplos atributos de model ou colunas do DB. Mais detalhes serão mostrados logo a seguir.
* [[yii\data\Sort::$attributeOrders|attributeOrders]] dá as instruções de ordenação requisitadas para cada atributo.
* [[yii\data\Sort::$orders|orders]] dá a direção da ordenação das colunas.

Para usar [[yii\data\Sort]], primeiro declare quais atributos podem ser ordenados. Em seguida, pegue a requisição com as informações de ordenação através de
[[yii\data\Sort::$attributeOrders|attributeOrders]] ou [[yii\data\Sort::$orders|orders]]
E use-os para personalizar a consulta de dados. Por exemplo:

```php
use yii\data\Sort;

$sort = new Sort([
    'attributes' => [
        'age',
        'name' => [
            'asc' => ['first_name' => SORT_ASC, 'last_name' => SORT_ASC],
            'desc' => ['first_name' => SORT_DESC, 'last_name' => SORT_DESC],
            'default' => SORT_DESC,
            'label' => 'Name',
        ],
    ],
]);

$articles = Article::find()
    ->where(['status' => 1])
    ->orderBy($sort->orders)
    ->all();
```

No exemplo abaixo, dois atributos são declarados para o objeto [[yii\data\Sort|Sort]]: `age` e `name`. 

O atributo `age` é um atributo *simples* que corresponde ao atributo `age` da classe Active Record `Article`. É equivalente a seguinte declaração:

```php
'age' => [
    'asc' => ['age' => SORT_ASC],
    'desc' => ['age' => SORT_DESC],
    'default' => SORT_ASC,
    'label' => Inflector::camel2words('age'),
]
```

O atributo `name` é um atributo *composto* definido por `first_name` e `last_name` de `Article`. Declara-se com a seguinte estrutura de array:

- Os elementos `asc` e `desc` determina a direção da ordenação dos atributos em ascendente ou descendente respectivamente. Seus valores representam as colunas e as direções pelas quais os dados devem ser classificados. Você pode especificar uma ou várias colunas para indicar uma ordenação simples ou composta.
- O elemento `default` especifica a direção pela qual o atributo deve ser ordenado quando requisitado. O padrão é a ordem crescente, ou seja, se a ordenação não for definida previamente e você pedir para ordenar por esse atributo, os dados serão ordenados por esse atributo em ordem crescente.
- O elemento `label` especifica o rótulo deve ser usado quando executar [[yii\data\Sort::link()]] para criar um link de ordenação. 
Se não for definida, [[yii\helpers\Inflector::camel2words()]] será chamado para gerar um rótulo do nome do atributo.
Perceba que não será HTML-encoded.

> Observação: Você pode alimentar diretamente o valor de [[yii\data\Sort::$orders|orders]] para a consulta do banco de dados para implementar a sua cláusula `ORDER BY`. Não utilize [[yii\data\Sort::$attributeOrders|attributeOrders]] porque alguns dos atributos podem ser compostos e não podem ser reconhecidos pela consulta do banco de dados.

Você pode chamar [[yii\data\Sort::link()]] para gerar um hyperlink em que os usuários finais podem clicar para solicitar a ordenação dos dados pelo atributo especificado. Você também pode chamar [[yii\data\Sort::createUrl()]] para criar um URL ordenáveis.
Por exemplo:

```php
// especifica a rota que a URL a ser criada deve usar
// Se você não especificar isso, a atual rota requisitada será utilizada 
$sort->route = 'article/index';

// exibe links direcionando a ordenação por ‘name‘ e ‘age‘, respectivamente
echo $sort->link('name') . ' | ' . $sort->link('age');

// exibe: /index.php?r=article/index&sort=age
echo $sort->createUrl('age');
```

O [[yii\data\Sort]] verifica o parâmetro `sort` da consulta para determinar quais atributos estão sendo requisitados para ordenação.
Você pode especificar uma ordenação padrão através de [[yii\data\Sort::defaultOrder]] quando o parâmetro de consulta não está fornecido.
Você também pode personalizar o nome do parâmetro de consulta configurando  propriedade [[yii\data\Sort::sortParam|sortParam]].
