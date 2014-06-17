¿Qué es Yii?
============

Yii es una librería (framework) PHP de alto rendimiento, basado en componentes para desarrollar aplicaciones Web 
modernas en poco tiempo.
El nombre Yii significa "simple y evolutivo" en chino. También puede ser tomado como un acrónimo
en inglés de **Yes It Is** (**Sí, eso es**)!


¿Para qué es Mejor Yii?
-----------------------

Yii es una librería (framework) genérica para el desarrollo Web, lo que significa que puede ser utilizado para desarrollar
todo tipo de aplicaciones Web basadas en PHP. Debido a su arquitectura basada en componentes y a su sofisticado
soporte de Cache, es especialmente apropiada para el desarrollo de aplicaciones de gran envergadura como portales,
foros, sistemas de gestión de contenidos (CMS), proyectos de e-commerce, RESTful Web services y mucho más.

¿Cómo se Compara Yii con Otras Librería o Frameworks?
-----------------------------------------------------

- Como la mayoría de frameworks PHP, Yii implementa el patrón de diseño MVC (Modelo-Vista-Controlador) y promueve
  la organización de código basada en este patrón.
- Yii toma la filosofía de que el código debe ser escrito de manera simple y elegante. Nunca intentará sobre-diseñar
  las cosas por el sólo hecho de seguir cierto patrón de diseño.
- Yii es un framework completo que provee muchas características probadas y listas para usar, como por ejemplo: query builders
  y ActiveRecord, tanto para bases de datos relacionales como para NoSQL; soporte de desarrollo de RESTful APIs;
  soporte de cache multi-tier; y mucho más.
- Yii es extremadamente extensible. Puedes personalizar o reemplazar practicamente cualquier pieza de código de su núcleo. 
  También puedes aprovecharte de su sólida arquitectura de extensiones, y así utilizar o desarrollar extensiones
  re-distribuibles.
- El alto rendimiento (performance) es siempre la meta principal en Yii.

Yii no es un show-de-un-solo-hombre, está sustentado por un [fuerte equipo de desarrollo][] así como por una gran comunidad
de muchos profesionales que están constantemente contribuyendo en el desarrollo del framework. El equipo de desarrollo de Yii se mantiene alerta sobre las últimas tendencias de desarrollo Web, así como en las mejores prácticas y características
encontradas en otros frameworks y proyectos. Las buenas prácticas más relevantes encontradas en cualquier otro lugar
son regularmente incorporadas en el núcleo y expuestas a través de simples y elegantes interfaces.

[fuerte equipo de desarrollo]: http://www.yiiframework.com/about/

Versiones de Yii
----------------

Actualmente Yii tiene dos versiones mayores disponibles: 1.1 y 2.0. La versión 1.1 es la anterior generación y ahora sólo cuenta con su mantenimiento.
La versión 2.0 está completamente reescrita, y adopta las últimas tecnologías y protocolos, incluyendo Composer, PSR, namespaces, traits, etc.
Esta versión representa la última generación del framework y su desarrollo recibirá nuestro principal esfuerzo en los próximos años.
Esta guía está basada principalmente sobre la versión 2.0 de la librería.


Requerimientos y Pre-requisitos
-------------------------------

Yii 2.0 requiere PHP 5.4.0 o mayor. Puedes encontrar requerimientos más detallados para características 
individuales corriendo el comprobador de requerimientos incluido en cada lanzamiento de Yii.

Para utilizar Yii se requieren conocimientos básicos acerca de la programación orientada a objetos (POO), ya que está
basado íntegramente en esta tecnología.
Yii 2.0 hace uso también de las últimas características de PHP, como [namespaces](http://www.php.net/manual/en/language.namespaces.php) y [traits](http://www.php.net/manual/en/language.oop5.traits.php).
Comprendiendo estos conceptos te ayudará a entender Yii 2.0 más fácilmente.
