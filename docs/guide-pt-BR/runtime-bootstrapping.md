Inicialização (Bootstrapping)
=============================

A inicialização refere-se ao processo de preparação do ambiente antes que uma 
aplicação comece a resolver e processar um pedido de requisição. A inicialização 
é feita em duas etapas:
O [script de entrada](structure-entry-scripts.md) e a 
[aplicação](structure-applications.md).

No [script de entrada](structure-entry-scripts.md), a classe de autoloaders de 
diferentes bibliotecas são registradas. Inclui o autoloader do Composer através 
do seu arquivo `autoload.php` e o autoloader do Yii através do seu arquivo `Yii`. 
O script de entrada, em seguida, carrega a [configuração](concept-configurations.md) 
da aplicação e cria uma instância da [aplicação](structure-applications.md).

No construtor da aplicação, as seguintes etapas de inicialização serão realizadas:

1. O método [[yii\base\Application::preInit()|preInit()]] é chamado, na qual 
   algumas propriedades da aplicação de alta prioridade serão configuradas, como 
   o [[yii\base\Application::basePath|basePath]].
2. Registra o [[yii\base\Application::errorHandler|manipulador de erro]].
3. Inicializa as propriedades da aplicação a partir da configuração da aplicação.
4. O método [[yii\base\Application::init()|init()]] é chamado, que por sua vez 
   chamará o método [[yii\base\Application::bootstrap()|bootstrap()]] para executar 
   os componentes de inicialização.
   - Inclui o arquivo `vendor/yiisoft/extensions.php` de manifesto da extensão.
   - Cria e executa os [componentes de inicialização](structure-extensions.md#bootstrapping-classes) 
     declaradas pelas extensões.
   - Cria e executa os [componentes da aplicação](structure-application-components.md) 
     e/ou os [módulos](structure-modules.md) declarados na 
     [propriedade bootstrap](structure-applications.md#bootstrap) da aplicação.

Como as etapas de inicialização tem que ser feitos antes da manipulação de *cada* 
requisição, é muito importante que mantenha este processo limpo e otimizado o 
máximo possível.

Tente não registrar muitos componentes de inicialização. Um componente de 
inicialização é necessário apenas se quiser participar de todo o ciclo de vida 
do processo da requisição. Por exemplo, se um módulo precisar registrar uma 
análise de regras de URL adicionais, deve ser listados na 
[propriedade bootstrap](structure-applications.md#bootstrap) de modo que as novas 
regras de URL possam ter efeito antes que sejam usados para resolver as requisições.

No modo de produção, habilite um cache de bytecode, como o [PHP OPcache] ou [APC], 
para minimizar o tempo necessário para a inclusão e análise os arquivos PHP.

[PHP OPcache]: http://php.net/manual/en/intro.opcache.php
[APC]: http://php.net/manual/en/book.apc.php

Algumas aplicações de larga escala possuem [configurações](concept-configurations.md) 
complexas, que são divididos em vários arquivos menores. Se este for o caso, 
considere guardar o cache de todo o array da configuração e carregue-o 
diretamente a partir deste cache antes da criação da instância da aplicação no 
script de entrada.

