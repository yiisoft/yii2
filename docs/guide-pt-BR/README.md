Guia Definitivo para Yii 2.0
============================

Esse tutorial está disponível sob os [termos da documentação do Yii](https://www.yiiframework.com/doc/terms/).

Todos os Direitos Reservados.

2014 (c) Yii Software LLC.


Introdução
----------

* [Sobre o Yii](intro-yii.md)
* [Migrando a partir da versão 1.1](intro-upgrade-from-v1.md)


Primeiros Passos
----------------

* [O que você precisa saber](start-prerequisites.md)
* [Instalando o Yii](start-installation.md)
* [Executando Aplicações](start-workflow.md)
* [Dizendo "Olá!"](start-hello.md)
* [Trabalhando com Formulários](start-forms.md)
* [Trabalhando com Bancos de Dados](start-databases.md)
* [Gerando Código com Gii](start-gii.md)
* [Seguindo em Frente](start-looking-ahead.md)


Estrutura da Aplicação
--------------------------

* [Visão Geral](structure-overview.md)
* [Scripts de Entrada](structure-entry-scripts.md)
* [Aplicações](structure-applications.md)
* [Componentes de Aplicação](structure-application-components.md)
* [Controladores (Controllers)](structure-controllers.md)
* [Modelos (Models)](structure-models.md)
* [Visões (Views)](structure-views.md)
* [Módulos](structure-modules.md)
* [Filtros](structure-filters.md)
* [Widgets](structure-widgets.md)
* [Assets](structure-assets.md)
* [Extensões](structure-extensions.md)


Tratando Requisições
-------------------------

* [Visão Geral](runtime-overview.md)
* [Preparação do Ambiente (Bootstrapping)](runtime-bootstrapping.md)
* [Roteamento e Criação de URL](runtime-routing.md)
* [Requisições](runtime-requests.md)
* [Respostas](runtime-responses.md)
* [Sessões e Cookies](runtime-sessions-cookies.md)
* [Tratamento de Erros](runtime-handling-errors.md)
* [Gerenciamento de Logs](runtime-logging.md)


Conceitos Chave
---------------

* [Componentes](concept-components.md)
* [Propriedades](concept-properties.md)
* [Eventos](concept-events.md)
* [Comportamentos](concept-behaviors.md)
* [Configurações](concept-configurations.md)
* [Apelidos (Aliases)](concept-aliases.md)
* [Carregamento Automático de Classes (Autoloading)](concept-autoloading.md)
* [Service Locator](concept-service-locator.md)
* [Container de Injeção de Dependência](concept-di-container.md)


Trabalhando com Banco de Dados
------------------------------

* [Objetos de Acesso a Dados - (Database Access Objects)](db-dao.md): Conectando a um banco de dados, consultas básicas, transações e manipulação de esquema
* [Construtor de Consulta (Query Builder)](db-query-builder.md): Consultando o banco de dados usando uma camada de abstração simples
* [Active Record](db-active-record.md): Sobre o Active Record ORM, recuperando e manipulando registros e definindo relacionamentos
* [Migrações (Migrations)](db-migrations.md): Aplica controle de versão para seus banco de dados em um ambiente de desenvolvimento em equipe
* [Sphinx](https://www.yiiframework.com/extension/yiisoft/yii2-sphinx/doc/guide)
* [Redis](https://www.yiiframework.com/extension/yiisoft/yii2-redis/doc/guide)
* [MongoDB](https://www.yiiframework.com/extension/yiisoft/yii2-mongodb/doc/guide)
* [ElasticSearch](https://www.yiiframework.com/extension/yiisoft/yii2-elasticsearch/doc/guide)


Coletando Dados de Usuários
---------------------------

* [Criando Formulários](input-forms.md)
* [Validando Dados](input-validation.md)
* [Recebendo Arquivos (Upload)](input-file-upload.md)
* [Coletando Dados Tabulares](input-tabular-input.md)
* [Coletando Dados para Múltiplos Models](input-multiple-models.md)
* [Extendendo o ActiveForm no Client Side](input-form-javascript.md)


Exibindo Dados
---------------

* [Formatação de Dados](output-formatting.md)
* [Paginação](output-pagination.md)
* [Ordenação](output-sorting.md)
* [Provedores de Dados (Data Providers)](output-data-providers.md)
* [Widgets de Dados](output-data-widgets.md)
* [Trabalhando com Client Scripts](output-client-scripts.md)
* [Temas](output-theming.md)


Segurança
--------

* [Visão Geral](security-overview.md)
* [Autenticação](security-authentication.md)
* [Autorização](security-authorization.md)
* [Trabalhando com Senhas](security-passwords.md)
* [Criptografia](security-cryptography.md)
* [Auth Clients](https://www.yiiframework.com/extension/yiisoft/yii2-authclient/doc/guide)
* [Melhores Práticas](security-best-practices.md)


Cache
-------

* [Visão Geral](caching-overview.md)
* [Cache de Dados](caching-data.md)
* [Cache de Fragmento](caching-fragment.md)
* [Cache de Página](caching-page.md)
* [Cache HTTP](caching-http.md)


Web Services RESTful
------------------------

* [Introdução](rest-quick-start.md)
* [Recursos](rest-resources.md)
* [Controladores (Controllers)](rest-controllers.md)
* [Roteamento](rest-routing.md)
* [Formatação de Respostas](rest-response-formatting.md)
* [Autenticação](rest-authentication.md)
* [Taxa de Limite de Acessos](rest-rate-limiting.md)
* [Versionamento](rest-versioning.md)
* [Tratamento de Erros](rest-error-handling.md)


Ferramentas de Desenvolvimento
------------------------------

* [Barra de Ferramentas de Depuração e Depurador](https://www.yiiframework.com/extension/yiisoft/yii2-debug/doc/guide)
* [Gerando Código usando o Gii](https://www.yiiframework.com/extension/yiisoft/yii2-gii/doc/guide)
* [Gerando Documentação da API](https://www.yiiframework.com/extension/yiisoft/yii2-apidoc)


Testes
------

* [Visão Geral](test-overview.md)
* [Configuração do ambiente de testes](test-environment-setup.md)
* [Testes Unitários](test-unit.md)
* [Testes Funcionais](test-functional.md)
* [Testes de Aceitação](test-acceptance.md)
* [Fixtures](test-fixtures.md)


Tópicos Especiais
-----------------

* [Template Avançado de Projetos](https://github.com/yiisoft/yii2-app-advanced/blob/master/docs/guide-pt-BR)
* [Construindo uma Aplicação a Partir do Zero](tutorial-start-from-scratch.md)
* [Comandos de Console](tutorial-console.md)
* [Validadores Nativos](tutorial-core-validators.md)
* [Docker](tutorial-docker.md)
* [Internacionalização](tutorial-i18n.md)
* [Envio de E-mails](tutorial-mailing.md)
* [Ajustes de Desempenho](tutorial-performance-tuning.md)
* [Ambiente de Hospedagem Compartilhada](tutorial-shared-hosting.md)
* [Motores de Template (Template Engines)](tutorial-template-engines.md)
* [Trabalhando com Código de Terceiros](tutorial-yii-integration.md)
* [Usando Yii como um Microframework](tutorial-yii-as-micro-framework.md)



Widgets
-------

* [GridView](https://www.yiiframework.com/doc-2.0/yii-grid-gridview.html)
* [ListView](https://www.yiiframework.com/doc-2.0/yii-widgets-listview.html)
* [DetailView](https://www.yiiframework.com/doc-2.0/yii-widgets-detailview.html)
* [ActiveForm](https://www.yiiframework.com/doc-2.0/guide-input-forms.html#activerecord-based-forms-activeform)
* [Pjax](https://www.yiiframework.com/doc-2.0/yii-widgets-pjax.html)
* [Menu](https://www.yiiframework.com/doc-2.0/yii-widgets-menu.html)
* [LinkPager](https://www.yiiframework.com/doc-2.0/yii-widgets-linkpager.html)
* [LinkSorter](https://www.yiiframework.com/doc-2.0/yii-widgets-linksorter.html)
* [Widgets Bootstrap](https://www.yiiframework.com/extension/yiisoft/yii2-bootstrap/doc/guide)
* [Widgets jQuery UI](https://www.yiiframework.com/extension/yiisoft/yii2-jui/doc/guide)


Helpers - Funções Auxiliares
-------

* [Visão Geral](helper-overview.md)
* [ArrayHelper](helper-array.md)
* [Html](helper-html.md)
* [Url](helper-url.md)
