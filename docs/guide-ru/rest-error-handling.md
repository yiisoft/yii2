Error Handling
==============

When handling a RESTful API request, if there is an error in the user request or if something unexpected
happens on the server, you may simply throw an exception to notify the user that something wrong has happened.
If you can identify the cause of the error (e.g. the requested resource does not exist), you should
consider throwing an exception with a proper HTTP status code (e.g. [[yii\web\NotFoundHttpException]]
representing a 404 HTTP status code). Yii will send the response with the corresponding HTTP status
code and text. It will also include in the response body the serialized representation of the
exception. For example,

```
HTTP/1.1 404 Not Found
Date: Sun, 02 Mar 2014 05:31:43 GMT
Server: Apache/2.2.26 (Unix) DAV/2 PHP/5.4.20 mod_ssl/2.2.26 OpenSSL/0.9.8y
Transfer-Encoding: chunked
Content-Type: application/json; charset=UTF-8

{
    "type": "yii\\web\\NotFoundHttpException",
    "name": "Not Found Exception",
    "message": "The requested resource was not found.",
    "code": 0,
    "status": 404
}
```

The following list summarizes the HTTP status code that are used by the Yii REST framework:

* `200`: OK. Everything worked as expected.
* `201`: A resource was successfully created in response to a `POST` request. The `Location` header
   contains the URL pointing to the newly created resource.
* `204`: The request is handled successfully and the response contains no body content (like a `DELETE` request).
* `304`: Resource was not modified. You can use the cached version.
* `400`: Bad request. This could be caused by various reasons from the user side, such as invalid JSON
   data in the request body, invalid action parameters, etc.
* `401`: Authentication failed.
* `403`: The authenticated user is not allowed to access the specified API endpoint.
* `404`: The requested resource does not exist.
* `405`: Method not allowed. Please check the `Allow` header for allowed HTTP methods.
* `415`: Unsupported media type. The requested content type or version number is invalid.
* `422`: Data validation failed (in response to a `POST` request, for example). Please check the response body for detailed error messages.
* `429`: Too many requests. The request is rejected due to rate limiting.
* `500`: Internal server error. This could be caused by internal program errors.
