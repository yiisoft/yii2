Testes
=======

O teste é uma parte importante do desenvolvimento de software. Se estamos conscientes disso ou não, Realizamos testes continuamente.
Por exemplo, enquanto escrevemos uma classe PHP, podemos depurá-lo passo a passo ou simplismente usar declarações echo ou die para verificar se a implantação está de acordo com nosso plano inicial. 
No caso de uma aplicação web, estamos entrando em alguns  testes de dados em forma de assegurar que a página interage com a gente como esperado.

O processo de teste pode ser automatizado de modo que cada momento em que precisamos para verificar alguma coisa, só precisamos chamar o código que faz isso por nós. O código que verifica o resultado coincide com o que temos planejado é chamado de teste e o processo de sua criação e posterior execução é conhecido como teste automatizado,
que é o principal tema destes capítulos de testes.


Desenvolvendo com testes
------------------

Test-Driven Development (TDD), and Behavior-Driven Development (BDD) são abordagens de desenvolvimento de software, 
descrevendo o comportamento de um trecho de código ou todo o recurso como um conjunto de cenários ou testes antes de escrever 
código real e só então criar a aplicação que permite que estes testes passem verificando se comportamento a que se destina é conseguido.

O processo de desenvolvimento de uma funcionalidade é a seguinte:

- Criar um novo teste que descreve uma funcionalidade a ser implementada.
- Execute o novo teste e verifique se ele falha. isto é esperado já que não há nenhuma implementação ainda.
- Escrever um código simples para fazer o novo teste passar.
- Executar todos os testes e garantir que todos eles passam.
- Melhorar código e certificar-se de testes ainda estão OK.

Depois feito o processo é repetido novamente para outras funcionalidades ou melhorias. 
Se uma funcionalidade existente deve ser alterada, os testes devem ser mudadas também.

> Dica:  Se você sentir que você está perdendo tempo fazendo um monte de pequenas e simples iterações, experimente cobrindo mais por você.
> Cenários de teste é para que você faça mais antes de executar testes novamente. Se você está depurando muito, tente fazer o oposto.

A razão para criar testes antes de fazer qualquer implementação é que ela nos permite focar no que queremos alcançar e totalmente mergulhar "como fazê-lo" depois.
Normalmente, leva a melhores abstrações e manutenção de teste mais fácil quando se trata de ajustes na funcionalidade ou de menos componentes acoplados.
  
Assim, para resumir as vantagens de tal abordagem são as seguintes:

- Mantém-se focado em uma coisa de cada vez que resulta em uma melhor planejamento e implementação.
- Resultados cobertos por testes para mais funcionalidade em maior detalhe, ou seja, se os testes são OK provavelmente nada está quebrado.

No longo prazo, geralmente, dá-lhe um boa melhoria na produtividade.

> Dica: Se você quiser saber mais sobre os princípios de levantamento de requisitos de software e modelagem do assunto
> esta é uma referência boa para aprender [Domain Driven Development (DDD)] (https://en.wikipedia.org/wiki/Domain-driven_design).

Quando e como testar
------------------

Enquanto a primeira abordagem de teste descrito acima faz sentido em longo prazo para projetos relativamente complexos e que seria um exagero
para os mais simples. Existem alguns indicadores de quando é apropriado:

- Projeto já é grande e complexo.
- Requisitos do projeto estão começando a ficarem complexos. Projeto cresce constantemente.
- Projeto pretende ser a longo prazo.
- O custo da falha é muito alta.

Não há nada errado na criação de testes que abrangem o comportamento de implementação existente.

- Projeto é um legado para ser gradualmente renovada.
- Você tem um projeto para trabalhar e não tem testes.

Em alguns casos, qualquer forma de teste automatizado poderia ser um exagero:

- Projeto é simples e não está ficando mais complexo.
- É um projeto emporal eque deixarão de trabalhar nele.

Ainda assim, se você tiver tempo é bom automatizar testes nestes casos também.

Outras leituras
-------------

- Test Driven Development: By Example / Kent Beck. ISBN: 0321146530.
