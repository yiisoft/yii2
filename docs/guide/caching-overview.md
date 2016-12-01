Caching
=======

Caching is a cheap and effective way to improve the performance of a Web application. By storing relatively
static data in cache and serving it from cache when requested, the application saves the time that would be
required to generate the data from scratch every time.

Caching can occur at different levels and places in a Web application. On the server-side, at the lower level,
cache may be used to store basic data, such as a list of most recent article information fetched from database;
and at the higher level, cache may be used to store fragments or whole of Web pages, such as the rendering result
of the most recent articles. On the client-side, HTTP caching may be used to keep most recently visited page content in
the browser cache.

Yii supports all these caching mechanisms:

* [Data caching](caching-data.md)
* [Fragment caching](caching-fragment.md)
* [Page caching](caching-page.md)
* [HTTP caching](caching-http.md)
