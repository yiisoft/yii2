El Almacenamiento en Caché
==========================

El almacenamiento en caché es una forma económica y eficaz para mejorar el rendimiento de una aplicación web. Mediante
el almacenamiento de datos relativamente estáticos en la memoria caché y su correspondiente recuperación cuando éstos sean
solicidatos, la aplicación salvaría todo ese tiempo y recursos necesarios para volver a generarlos cada vez desde cero.

El almacenamiento en caché se puede usar en diferentes niveles y lugares en una aplicación web. En el lado del servidor, al más bajo nivel,
la caché puede ser usada para almacenar datos básicos, tales como una una lista de los artículos más recientes obtenidos de una base de datos;
y en el más alto nivel, la caché puede ser usada para almacenar fragmentos o la totalidad de las páginas web, tales como el resultado del renderizado de los artículos más recientes. En el lado del cliente, el almacenamiento en caché HTTP puede ser utilizado para mantener
el contenido de la página que ha sido visitada más recientemente en el caché del navegador.

Yii soporta los siguientes mecanismos de almacenamiento de caché:

* [Caché de datos](caching-data.md)
* [Caché de fragmentos](caching-fragment.md)
* [Caché de páginas](caching-page.md)
* [Caché HTTP](caching-http.md)
