El Almacenamiento en Caché
==========================

El almacenamiento en caché es una forma económica y eficaz para mejorar el rendimiento de una aplicación web. Mediante
el almacenamiento de datos relativamente estáticos en la memoria caché y su correspondiente recuperación cuando éstos sean
solicidatos, la aplicación salvaría todo ese tiempo y recursos necesarios para volver a generarlos cada vez desde cero.

El almacenamiento puede ocurrir en diferentes niveles y lugar en una aplicación Web. En el lado del servidor, en el
nivel inferior, la memoria caché puede ser utilizada para guardar algunos datos básicos, tales como la lista más reciente
de artículos que han sido extraídos de la base de datos; y en el nivel superior, la memoria caché puede utilizarse para
almacenar fragmentos o un conjuto de páginas Web, tales como el resultado de la representación de los artículos más
recientes.

Caching can occur at different levels and places in a Web application. On the server side, at the lower level,
cache may be used to store basic data, such as a list of most recent article information fetched from database;
and at the higher level, cache may be used to store fragments or whole of Web pages, such as the rendering result
of the most recent articles. En el lado del cliente, el almacenamiento en caché HTTP puede ser utilizado para mantener
el contenido de la página que ha sido visitada más recientemente en el caché del navegador.

Yii soporta los siguientes mecanismos de almacenamiento de caché:

* [Caché de datos](caching-data.md)
* [Caché de fragmentos](caching-fragment.md)
* [Caché de páginas](caching-page.md)
* [Caché HTTP](caching-http.md)
