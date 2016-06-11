Como Fazer um "Hello World"
=====================

Esta seção descreve como criar uma nova página de "Hello" em sua aplicação.
Para atingir este objetivo, você criará uma [ação](structure-controllers.md#creating-actions)
(action) e uma [visão](structure-views.md) (view):

* A aplicação enviará a requisição de página para a ação
* e a ação por sua vez renderizará a view que mostra as palavras "Hello"
  para o usuário final.

Através deste tutorial, você aprenderá três coisas:

1. Como criar uma [ação](structure-controllers.md) para responder às requisições,
2. como criar uma [visão](structure-views.md) para compor o conteúdo da resposta, e
3. como uma aplicação envia as requisições para as [ações](structure-controllers.md#creating-actions).


Criando uma Ação <span id="creating-action"></span>
----------------

Para a tarefa "Hello", você criará uma [ação](structure-controllers.md#creating-actions)
`say` que lê um parâmetro `message` da requisição e exibe essa mensagem de volta
para o usuário. Se a requisição não fornecer um parâmetro `message`, a ação
exibirá a mensagem padrão "Hello".

> Informação: [Ações](structure-controllers.md#creating-actions) são os objetos aos
  quais os usuários finais podem se referir diretamente para execução. As ações são
  agrupadas nos [controladores](structure-controllers.md) (controllers). O resultado
  da execução de uma ação é a resposta que o usuário final receberá.

As ações devem ser declaradas em [controladores](structure-controllers.md). Por
motivo de simplicidade, você pode declarar a ação `say` na classe já existente
`SiteController`. Este controlador está definido no arquivo `controllers/SiteController.php`.
Segue aqui o início da nova ação:

```php
<?php

namespace app\controllers;

use yii\web\Controller;

class SiteController extends Controller
{
    // ...código existente...

    public function actionSay($message = 'Hello')
    {
        return $this->render('say', ['message' => $message]);
    }
}
```

No código acima, a ação `say` está definida como um método chamado `actionSay`
na classe `SiteController`. O Yii usa o prefixo `action` para diferenciar métodos
de ações de métodos que não são de ações em uma classe de controlador. O nome
após o prefixo `action` é mapeado para o ID da ação.

Quando se trata de dar nome às suas ações, você deveria entender como o Yii
trata os IDs de ações. Os IDs das ações são sempre referenciados em minúsculo.
Se o ID de uma ação necessitar de múltiplas palavras, elas serão concatenadas
por hífens (por exemplo, `create-comment`). Os nomes de métodos de ações são mapeados
para os IDs das ações removendo-se os hífens dos IDs, colocando em maiúsculo a
primeira letra de cada palavra, e prefixando o resultado com a palavra `action`. Por exemplo,
o ID de ação `create-comment` corresponde ao método de ação `actionCreateComment`.

O método de ação em nosso exemplo recebe um parâmetro `$message`, cujo valor
padrão é "Hello" (exatamente da mesma forma que você define um valor padrão para
  qualquer argumento de função ou método no PHP). Quando a aplicação recebe a
requisição e determina que a ação `say` é responsável por tratar a requisição,
a aplicação alimentará este parâmetro com o parâmetro de mesmo nome encontrado
na requisição. Em outras palavras, se a requisição inclui um parâmetro `message`
com o valor `"Goodbye"`, a variável `$message` na ação receberá esse valor.

Dentro do método da ação, [[yii\web\Controller::render()|render()]] é chamado
para renderizar um arquivo de [visão](structure-views.md) chamado `say`. O
parâmetro `message` também é passado para a visão de modo que ele possa ser usado
ali. O resultado da renderização é retornado pelo método da ação. Esse resultado
será recebido pela aplicação, e exibido para o usuário final no navegador (como
parte de uma págian HTML completa).


Criando uma Visão <span id="creating-view"></span>
-----------------

As [visões](structure-views.md) são scripts que você escreve para gerar o conteúdo
de uma resposta. Para a tarefa "Hello", você criará uma visão `say` que imprime o
parâmetro `message` recebido do método da ação:

```php
<?php
use yii\helpers\Html;
?>
<?= Html::encode($message) ?>
```

A visão `say` deve ser salva no arquivo `views/site/say.php`. Quando o método
[[yii\web\Controller::render()|render()]] é chamado em uma ação, ele procurará
um arquivo PHP chamado de `views/IDdoController/NomeDaView.php`.

Perceba que no código acima o parâmetro `message` é [[yii\helpers\Html::encode()|codificado como HTML]]
antes de ser impresso. Isso é necessário, já que o parâmetro vem de um usuário final,
tornando-o vulnerável a [ataques de cross-site scripting (XSS)](http://en.wikipedia.org/wiki/Cross-site_scripting)
onde coloca-se código JavaScript malicioso no parâmetro.

Naturalmente, você pode colocar mais conteúdo na visão `say`. O conteúdo pode consistir
de tags HTML, texto puro, ou até mesmo instruções de PHP. De fato a visão `say` é
apenas um script PHP que é executado pelo método [[yii\web\Controller::render()|render()]].
O conteúdo impresso pelo script da visão será retornado à aplicação como o resultado
da resposta. A aplicação, por sua vez, retornará este resultado para o usuário final.


Testando <span id="trying-it-out"></span>
--------

Após criar a ação e a visão, você pode acessar a nova página através da seguinte URL:

```
http://hostname/index.php?r=site/say&message=Hello+World
```

![Hello World](images/start-hello-world.png)

Esta URL resultará em uma página exibindo "Hello World". Esta página compartilha
o mesmo cabeçalho e rodapé das outras páginas da aplicação.

Se você omitir o parâmetro `message` na URL, você veria a página exibindo somente
"Hello". Isso é porque `message` é passado como um parâmetro para o método `actionSay()`,
e quando ele é omitido, o valor padrão `"Hello"` será usado ao invés dele.

> Informação: A nova página compartilha o mesmo cabeçalho e rodapé de outras páginas
  porque o método [[yii\web\Controller::render()|render()]] irá automaticamente
  incluir o resultado da visão `say` em um [layout](structure-views.md#layouts) 
  que neste caso está localizado em `views/layouts/main.php`.

O parâmetro `r` na URL acima requer mais explicação. Ele significa [rota](runtime-routing.md),
um ID abrangente e único de uma aplicação que se refere a uma ação. O formato da rota
é `IDdoController/IDdaAction`. Quando a aplicação recebe uma requisição, ela
verificará este parâmetro, usando a parte `IDdoController` para determinar qual
classe de controlador deve ser instanciada para tratar a requisição. Então o
controlador usará a parte `IDdaAction` para determinar qual ação deverá ser
instanciada para fazer o trabalho. No caso deste exemplo, a rota `site/say` será
resolvida como a classe de controlador `SiteController` e a ação `say`. Como
resultado, o método `SiteController::actionSay()` será chamado para tratar a requisição.

> Informação: Assim como as ações, os controladores também possuem IDs que os identificam
  de maneira única em uma aplicação. Os IDs de controladores seguem as mesmas regras
  de nomenclatura que os IDs de ações. Os nomes das classes de controlllers
  derivam dos IDs de controladores removendo-se os hífens dos IDs, convertendo a
  primeira letra de cada palavra em maiúscula, e adicionando o sufixo `Controller`.
  Por exemplo, o ID de controlador `post-comment` corresponde ao nome de classe
  de controlador `PostCommentController`.


Resumo <span id="summary"></span>
------

Nesta seção, você tocou as partes do controlador (controller) e da visão (view)
do padrão de arquitetura MVC. Você criou uma ação (action) como parte de um controlador
para tratar uma requisição específica. E você também criou uma visão para compor
o conteúdo da resposta. Neste exemplo simples acima, nenhum modelo (model) foi
utilizado, já que os único dado sendo exibido foi o parâmetro `message`.

Você também aprendeu sobre as rotas no Yii, que agem como a ponte entre as
requisições do usuário e as ações do controlador.

Na próxima seção, você aprenderá como criar um modelo e adicionar uma nova
página contendo um formulário HTML.
