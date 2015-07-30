Como Colaborar Com a Tradução Para o Português do Brasil
========================================================

O Yii tem tradução para vários idiomas, incluindo o Português do Brasil. Existem
duas áreas onde a contribuição para a tradução é muito bem-vindo. A primeira é a
documentação e a segunda são as mensagens do framework.

Mensagens do Framework
----------------------

O Framework tem dois tipos de mensagens: as exceções que são destinadas para o
desenvolvedor e nunca são traduzidas, e as mensagens que são realmente visíveis
para o usuário final, tais como erros de validação.

Os passos para iniciar a tradução de mensagens são:

1. Com o `console` entre na pasta `yii2/framework`  e execute o seguinte comando:
   `yii message/extract messages/config.php`.
2. As mensagens a serem traduzidas encontram-se no seguinte caminho:
   `framework/messages/pt-BR/yii.php`. Certifique-se de salvar o arquivo com a
   codificação UTF-8 (Plain).
3. Após realizar as devidas traduções o passo seguinte é enviar as suas
   modificações para o respositório do Yii no Github.
   [Veja aqui](https://github.com/yiisoft/yii2/blob/master/docs/internals/git-workflow.md)
   os passos necessários para o envio dos arquivos.

Para manter as traduções sempre atualizadas, certifique-se que seu fork do Yii
esteja com a última versão. Em seguida, basta executar o comando
`yii message/extract messages/config.php` novamente e o mesmo irá adicionar
automaticamente as novas mensagens a serem traduzidas.

No arquivo de tradução cada elemento do array representa a tradução de uma
mensagem. Sendo que a "chave" representa o texto a ser traduzido e o "valor" a
sua tradução. Se o "valor" estiver vazio, a mensagem é considerada como não
traduzida. As mensagens que não precisam de tradução terão seus valores cercadas
por um par de '@@'. Atentar para algumas mensagens que estão no formato de plural,
para isso verifique a [seção i18n do guia](../guide-pt-BR/tutorial-i18n.md) para
mais detalhes.

Documentação
------------

As traduções da documentação para o português do Brasil devem ficar dentro do
diretório `docs/` seguindo o padrão  `docs/<original>-<pt-BR>` onde `<original>`
corresponde ao nome da pasta tal como `guide` ou `internals`.

Com a tradução do documento concluída, você pode obter um diff das mudanças desde
a última tradução, para isso, execute o seguinte comando a partir do diretório
`build/` do framework:

```
build translation "../docs/guide" "../docs/guide-pt-BR" > report-guide-pt-BR.html
```

Antes de iniciar seus trabalhos de tradução certifique-se que o arquivo em qual
irá trabalhar esteja disponível para ser traduzido. Para isso, acesse a
[planilha no Google Docs](https://docs.google.com/spreadsheets/d/1pAMe-qsKK0poEsQwGI2HLFmj4afKSkEUd_1qegU5YqQ).


Regras e Observações
--------------------

- Alguns termos não tem uma tradução muito boa para o português, em casos como
  esse convém escrever a palavra ou expressão em inglês primeiro em seguida sua
  possível tradução em parênteses.
- Se você acredita que alguma parte de sua tradução não faz sentido e você não
  tem certeza de como traduzi-la corretamente. Coloque este bloco de texto em
  *itálico*, isso ajudará na revisão.
- Para reduzir erros de digitação você pode utilizar um editor de texto como o
  MS Word para escrever pequenos blocos textos e em seguida copiar estes blocos
  para um editor visual de Markdown como o http://dillinger.io/.

### Convenções Para Tradução

- action — ação
- application system - sistema
- project template — template de projetos
- controller — controller (controlador)
- eager loading — eager loading (carregamento na inicialização)
- lazy loading — lazy loading (carregamento retardado)
- model — model (modelo)
- query builder — query builder (construtor de consulta)
- view — view (visão)
- note — observação
- info — informação
- tip — dica
- warning - atenção
- attribute label - label do atributo
- inline action — ação inline
- standalone action — ação standalone
- advanced project template — template avançado de projetos
- basic project template — template básico de projetos
- behaviors — behaviors (comportamentos)
- pretty URL — URL amigável (pretty URL)
- class member variable - atributo da classe

### Termos Sem Tradução

- active record
- alias
- cache
- CamelCase, camel-case
- core
- framework
- hash
- helper
- id
- runtime
- widget
- backend
- frontend
- web service
- template
- query string
- case-sensitive
- case-insensitive
- callback