Executando Aplicações
=====================

Após instalar o Yii, você tem uma aplicação Yii funcional que pode ser acessada
pela URL `https://hostname/basico/web/index.php` ou `https://hostname/index.php`,
dependendo de sua configuração. Esta seção introduzirá a funcionalidade embutida
da aplicação, como o código é organizado e como a aplicação trata as requisições em geral.

> Info: Por questões de simplicidade, por todo este tutorial de "Primeiros Passos"
  assume-se que você definiu `basico/web` como a raiz de documentos do seu
  servidor Web e configurou a URL de acesso de sua aplicação como `https://hostname/index.php`
  ou algo semelhante.
  Por favor, ajuste as URLs em nossas descrições conforme necessário.

Observe que, ao contrário do framework em si, após o template de projeto ser instalado, ele é todo seu. Você está livre para adicionar ou remover código e modificar o template conforme precisar.


Funcionalidade <span id="functionality"></span>
--------------

O template básico de projetos instalado contém quatro páginas:

* A página inicial, exibida quando você acessa a URL `https://hostname/index.php`,
* a página "About" (Sobre),
* a página "Contact" (Contato), que exibe um formulário de contato que permite
  que usuários finais entrem em contato com você via e-mail,
* e a página "Login", que exibe um formulário de login que pode ser usado para aurenticar usuários finais.
  Tente fazer o login com "admin/admin", e você perceberá que o item do menu principal "Login" mudará para "Logout".

Essas páginas compartilham o mesmo cabeçalho e rodapé. O cabeçalho contém uma barra de menu principal que permite a navegação entre as diferentes páginas.

Você também deverá ver uma barra de ferramentas no rodapé da janela do navegador.
Essa é uma [ferramenta de depuração](tool-debugger.md) muito útil fornecida pelo Yii para
registrar e exibir várias informações de depuração, tais como: mensagens de logs, status de respostas, as consultas de banco de dados executadas, e assim por diante.

Além da aplicação Web, existe um script de console chamado `yii`, que está localizado no diretório raiz da aplicação.
Esse script pode ser usado para executar rotinas em segundo plano e tarefas de manutenção da aplicação que são descritas na seção [Comandos de Console](tutorial-console.md).


Estrutura da Aplicação <span id="application-structure"></span>
----------------------

Os diretórios e arquivos mais importantes em sua aplicação, assumindo que o diretório raiz dela é o `basico`, são:

```
basico/                  caminho base de sua aplicação
    composer.json       usado pelo Composer, descreve informações de pacotes
    config/             contém as configurações da aplicação e outras
        console.php     a configuração da aplicação de console
        web.php         a configuração da aplicação Web
    commands/           contém classes de comandos do console
    controllers/        contém classes de controllers (controladores)
    models/             contém classes de models (modelos)
    runtime/            contém arquivos gerados pelo Yii durante o tempo de execução, tais como logs e arquivos de cache
    vendor/             contém os pacotes do Composer instalados, incluindo o próprio Yii framework
    views/              contém arquivos de views (visões)
    web/                raiz da aplicação Web, contém os arquivos acessíveis pela Web
        assets/         contém os arquivos de assets (javascript e css) publicados pelo Yii
        index.php       o script de entrada (ou bootstrap) para a aplicação
    yii                 o script de execução dos comandos de console do Yii
```

Em geral, os arquivos na aplicação podem ser divididos em dois tipos: aqueles em `basico/web` e aqueles em outros diretórios.
Os primeiros podem ser acessados diretamente via HTTP (ou seja, em um navegador), enquanto os demais não podem e deveriam ser acessados.

O Yii implementa o padrão de arquitetura [modelo-visão-controlador (MVC)](https://wikipedia.org/wiki/Model-view-controller),
que se reflete na organização de diretórios acima. O diretório `models` contém
todas as [classes de modelos](structure-models.md), o diretório `views` contém todos
os [scripts de visões](structure-views.md), e o diretório `controllers` contém
todas as [classes de controladores](structure-controllers.md).

O diagrama a seguir demonstra a estrutura estática de uma aplicação.

![Estrutura Estática de uma Aplicação](images/application-structure.png)

Cada aplicação tem um script de entrada `web/index.php` que é o único script PHP
acessível pela Web na aplicação. O script de entrada recebe uma requisição e cria uma instância de [aplicação](structure-applications.md) para tratar a requisição.
A [aplicação](structure-applications.md) resolve ("traduz") a requisição com a ajuda de seus [componentes](concept-components.md) e despacha a requisição para os elementos do MVC. São usados [Widgets](structure-widgets.md) nas [views](structure-views.md) para ajudar a construir elementos de interface de usuário complexos e dinâmicos.


Ciclo de Vida da Requisição <span id="request-lifecycle"></span>
---------------------------

O diagrama a seguir demonstra como uma aplicação trata uma requisição.

![Ciclo de Vida da Requisição](images/request-lifecycle.png)

1. Um usuário faz uma requisição ao [script de entrada](structure-entry-scripts.md) `web/index.php`.
2. O script de entrada carrega a [configuração](concept-configurations.md) da
   aplicação e cria uma instância de [aplicação](structure-applications.md) para
   tratar a requisição.
3. A aplicação resolve ("traduz") a [rota](runtime-routing.md) solicitada com a ajuda do componente [request](runtime-requests.md) da aplicação.
4. A aplicação cria uma instância de um [controller](structure-controllers.md)
   para tratar a requisição.
5. O controller cria uma instância de uma [action](structure-controllers.md) (ação) e aplica os filtros para a ação.
6. Se qualquer filtro falhar, a ação é cancelada.
7. Se todos os filtros passarem, a ação é executada.
8. A ação carrega alguns modelos (models) de dados, possivelmente a partir de um banco de dados.
9. A ação renderiza uma view, passando a ela os modelos de dados.
10. O resultado renderizado é retornado pelo componente
    [response](runtime-responses.md) (resposta) da aplicação.
11. O componente response envia o resultado renderizado para o navegador do usuário.

