Tests
=====

Las pruebas son una parte importante del desarrollo de software.  Seamos conscientes
de ello o no, ralizamos pruebas contínuamente.
Por ejemplo, cuando escribimos una clase en PHP, podemos depurarla paso a paso o
simplemente usar declaraciones `echo` o `die` para verificar que la implementación
funciona conforme a nuestro plan inicial.  En el caso de una aplicación web, introducimos
algunos datos de prueba en los formularios para asegurarnos de que la página interactúa
con nosotros como esperábamos.

El proceso de testeo se puede automatizar para que cada vez que necesitemos verificar
algo, solamente necesitemos invocar el código que lo hace por nosotros.  El código que
verifica que el restulado coincide con lo que habíamos planeado se llama *test* y el proceso
de su creación y posterior ejecución es conocido como *testeo automatizado*, que es el
principal tema de estos capítulos sobre testeo.


## Desarrollo con tests

El Desarrollo Dirigido por Pruebas (_Test-Driven Development_ o TDD) y el Desarrollo
Dirigido por Corpotamientos (_Behavior-Driven Development_ o BDD) son enfoques para
desarrollar software, en los que se describe el comportamiento de un trozo de código
o de toda la funcionalidad como un conjunto de escenarios o pruebas antes de escribir
el código real y sólo entonces crear la implementación que permite pasar esos tests
verificando que se ha logrado el comportamiento pretendido.

El proceso de desarrollo de una funcionalidad es el siguiente:

- Crear un nuevo test que describe una funcionalidad a implementar.
- Ejecutar el nuevo test y asegurarse de que falla.  Esto es lo esperado, dado que todavía no hay ninguna implementación.
- Escribir un código sencillo para superar el nuevo test.
- Ejecutar todos los tests y asegurarse de que se pasan todos.
- Mejorar el código y asegurarse de que los tests siguen superándose.

Una vez hecho, se repite el proceso de neuvo para otra funcionalidad o mejora.
Si se va a cambiar la funcionalidad existente, también hay que cambiar los tests.

> Tip: Si siente que está perdiendo tiempo haciendo un montón de iteraciones pequeñas
> y simples, intente cubrir más por cada escenario de test, de modo que haga más cosas antes
> de ejecutar los tests de nuevo.  Si está depurando demasiado, intente hacer lo contrario.

La razón para crear los tests antes de hacer ninguna implementación es que eso nos permite
centrarnos en lo que queremos alcanzar y sumergirnos totalmente en «cómo hacerlo» después.
Normalmente conduce a mejores abstracciones y a un más fácil mantenimiento de los tests
cuando toque hacer ajustes a las funcionalidades o componentes menos acoplados.

Para resumir, las ventajas de este enfoque son las siguientes:

- Le mantiene centrado en una sola cosa en cada momento, lo que resulta en una mejor planificación e implementación.
- Resulta en más funcionalidades cubiertas por tests, y en mayor detalle.  Es decir, si se superan los tests, lo más problable es que no haya nada roto.

A largo plazo normalmente tiene como efecto un buen ahorro de tiempo.

## Qué y cómo probar

Aunque el enfoque de primero los tests descrito arriba tiene sentido para el largo plazo
y proyectos relativamente complejos, sería excesivo para proyectos más simples.
Hay algunas indicaciones de cuándo es apropiado:

- El proyecto ya es grande y complejo.
- Los requisitos del proyecto están empezando a hacerse complejos.  El proyecto crece constantemente.
- El proyecto pretende a ser a largo plazo.
- El coste de fallar es demasiado alto.

No hay nada malo en crear tests que cubran el comportamiento de una implementación existente.

- Es un proyecto legado que se va a renovar gradualmente.
- Le han dado un proyecto sobre el que trabajar y no tiene tests.

En algunos casos cualquier forma de testo automatizado sería exagerada:

- El proyecto es sencillo y no se va a volver más complejo.
- Es un proyecto puntual en el que no se seguirá trabajando.

De todas formas, si dispone de tiempo, es bueno automatizar las pruebas también en esos casos.

## Más lecturas

- Test Driven Development: By Example / Kent Beck. ISBN: 0321146530.
