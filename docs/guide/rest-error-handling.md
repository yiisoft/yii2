Error Handling
==============

When handling a RESTful API request, if there is an error in the user request or if something unexpected
happens on the server, you may simply throw an exception to notify the user that something went wrong.
If you can identify the cause of the error (e.g., the requested resource does not exist), you should
consider throwing an exception along with a proper HTTP status code (e.g., [[yii\web\NotFoundHttpException]]
represents a 404 status code). Yii will send the response along with the corresponding HTTP status
code and text. Yii will also include the serialized representation of the
exception in the response body. For example:

```
HTTP/1.1 404 Not Found
Date: Sun, 02 Mar 2014 05:31:43 GMT
Server: Apache/2.2.26 (Unix) DAV/2 PHP/5.4.20 mod_ssl/2.2.26 OpenSSL/0.9.8y
Transfer-Encoding: chunked
Content-Type: application/json; charset=UTF-8

{
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
* `204`: The request was handled successfully and the response contains no body content (like a `DELETE` request).
* `304`: The resource was not modified. You can use the cached version.
* `400`: Bad request. This could be caused by various actions by the user, such as providing invalid JSON
   data in the request body, providing invalid action parameters, etc.
* `401`: Authentication failed.
* `403`: The authenticated user is not allowed to access the specified API endpoint.
* `404`: The requested resource does not exist.
* `405`: Method not allowed. Please check the `Allow` header for the allowed HTTP methods.
* `415`: Unsupported media type. The requested content type or version number is invalid.
* `422`: Data validation failed (in response to a `POST` request, for example). Please check the response body for detailed error messages.
* `429`: Too many requests. The request was rejected due to rate limiting.
* `500`: Internal server error. This could be caused by internal program errors.


## Customizing Error Response <span id="customizing-error-response"></span>

Sometimes you may want to customize the default error response format. For example, instead of relying on
using different HTTP statuses to indicate different errors, you would like to always use 200 as HTTP status
and enclose the actual HTTP status code as part of the JSON structure in the response, like shown in the following,

```
HTTP/1.1 200 OK
Date: Sun, 02 Mar 2014 05:31:43 GMT
Server: Apache/2.2.26 (Unix) DAV/2 PHP/5.4.20 mod_ssl/2.2.26 OpenSSL/0.9.8y
Transfer-Encoding: chunked
Content-Type: application/json; charset=UTF-8

{
    "success": false,
    "data": {
        "name": "Not Found Exception",
        "message": "The requested resource was not found.",
        "code": 0,
        "status": 404
    }
}
```

To achieve this goal, you can respond to the `beforeSend` event of the `response` component in the application configuration:

```php
return [
    // ...
    'components' => [
        'response' => [
            'class' => 'yii\web\Response',
            'on beforeSend' => function ($event) {
                $response = $event->sender;
                if ($response->data !== null && Yii::$app->request->get('suppress_response_code')) {
                    $response->data = [
                        'success' => $response->isSuccessful,
                        'data' => $response->data,
                    ];
                    $response->statusCode = 200;
                }
            },
        ],
    ],
];
```

The above code will reformat the response (for both successful and failed responses) as explained when
`suppress_response_code` is passed as a `GET` parameter.
