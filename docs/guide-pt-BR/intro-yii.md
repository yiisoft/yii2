O que é o Yii
=============

Yii é um framework PHP de alta performance baseado em componentes para desenvolvimento rápido de aplicações web modernas.
O nome Yii (pronunciado `ii`) significa "simples e evolutivo" em chinês. Ele também pode ser considerado um acrônimo de **Yes It Is** (*Sim, ele é*)!


Yii é melhor para que tipo de aplicações?
------------------------

Yii é um framework de programação web genérico, o que significa que ele pode
ser usado para o desenvolvimento de todo tipo de aplicações web usando PHP.
Por causa de sua arquitetura baseada em componentes e suporte sofisticado a
caching, ele é especialmente adequado para o desenvolvimento de aplicações de
larga escala como portais, fóruns, sistemas de gerenciamento de conteúdo (CMS),
projetos de e-commerce, Web services RESTful e assim por diante.


Como o Yii se Compara a Outros Frameworks?
------------------------------------------

Se já estiver familiarizado com um outro framework, você pode gostar de saber como o Yii se compara:

- Como a maioria dos frameworks PHP, o Yii implementa o padrão de arquitetura MVC
  (Modelo-Visão-Controlador) e promove a organização do código baseada nesse padrão.
- Yii tem a filosofia de que o código deveria ser escrito de uma maneira simples,
  porém elegante. O Yii nunca vai tentar exagerar no projeto só para seguir estritamente algum padrão de projeto.
- Yii é um framework completo fornecendo muitas funcionalidades comprovadas
  e prontas para o uso, tais como: construtores de consultas (query builders) e
  ActiveRecord tanto para bancos de dados relacionais quanto para NoSQL; suporte ao
  desenvolvimento de APIs RESTful; suporte a caching de múltiplas camadas; e mais.
- Yii é extremamente extensível. Você pode personalizá-lo ou substituir quase
  todas as partes do código central (core). Você também pode tirar vantagem de sua
  sólida arquitetura de extensões para utilizar ou desenvolver extensões
  que podem ser redistribuídas.
- Alta performance é sempre um objetivo principal do Yii.

Yii não é um show de um homem só, ele é apoiado por uma [forte equipe de desenvolvedores do código central (core)][yii_team]
bem como por uma ampla comunidade de profissionais constantemente
contribuindo com o desenvolvimento do Yii. A equipe de desenvolvedores do Yii
acompanha de perto às últimas tendências do desenvolvimento Web e as
melhores práticas e funcionalidades encontradas em outros frameworks e projetos.
As mais relevantes e melhores práticas e características encontradas em outros lugares
são incorporadas regularmente no core do framework e expostas via interfaces
simples e elegantes.

[yii_team]: https://www.yiiframework.com/team

Versões do Yii
--------------

Atualmente, o Yii tem duas versões principais disponíveis: a 1.1 e a 2.0. A Versão
1.1 é a antiga geração e agora está em modo de manutenção. A versão 2.0 é uma
reescrita completa do Yii, adotando as tecnologias e protocolos mais recentes, incluindo Composer, PSR, namespaces, traits, e assim por diante. A versão 2.0 representa
a geração atual do framework e receberá os nossos esforços principais de
desenvolvimento nos próximos anos. Este guia trata principalmente da versão 2.0.


Requisitos e Pré-requisitos
---------------------------

Yii 2.0 requer PHP 7.3.0 ou superior. Você pode encontrar requisitos mais
detalhados para recursos específicos executando o verificador de requisitos
(requirement checker) incluído em cada lançamento do Yii.

Utilizar o Yii requer conhecimentos básicos sobre programação orientada a objetos
(OOP), uma vez que o Yii é um framework puramente OOP.
O Yii 2.0 também utiliza as funcionalides mais recentes do PHP, tais como [namespaces](https://www.php.net/manual/pt_BR/language.namespaces.php) e [traits](https://www.php.net/manual/pt_BR/language.oop5.traits.php). Compreender esses conceitos lhe ajudará a entender mais facilmente o Yii 2.0.

