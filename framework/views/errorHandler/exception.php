<?php
/* @var $this \yii\web\View */
/* @var $exception \Exception */
/* @var $handler \yii\web\ErrorHandler */
?>
<?php if (method_exists($this, 'beginPage')): ?>
    <?php $this->beginPage() ?>
<?php endif ?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8"/>

    <title><?php
        $name = $handler->getExceptionName($exception);
        if ($exception instanceof \yii\web\HttpException) {
            echo (int) $exception->statusCode . ' ' . $handler->htmlEncode($name);
        } else {
            if ($name !== null) {
                echo $handler->htmlEncode($name . ' – ' . get_class($exception));
            } else {
                echo $handler->htmlEncode(get_class($exception));
            }
        }
    ?></title>

    <style type="text/css">
/* reset */
html,body,div,span,h1,h2,h3,h4,h5,h6,p,pre,a,code,em,img,strong,b,i,ul,li{
    margin: 0;
    padding: 0;
    border: 0;
    font-size: 100%;
    font: inherit;
    vertical-align: baseline;
}
body{
    line-height: 1;
}
ul{
    list-style: none;
}

/* base */
a{
    text-decoration: none;
}
a:hover{
    text-decoration: underline;
}
h1,h2,h3,p,img,ul li{
    font-family: Arial,sans-serif;
    color: #505050;
}
/*corresponds to min-width of 860px for some elements (.header .footer .element ...)*/
@media screen and (min-width: 960px) {
    html,body{
        overflow-x: hidden;
    }
}

/* header */
.header{
    min-width: 860px; /* 960px - 50px * 2 */
    margin: 0 auto;
    background: #f3f3f3;
    padding: 40px 50px 30px 50px;
    border-bottom: #ccc 1px solid;
}
.header h1{
    font-size: 30px;
    color: #e57373;
    margin-bottom: 30px;
}
.header h1 span, .header h1 span a{
    color: #e51717;
}
.header h1 a{
    color: #e57373;
}
.header h1 a:hover{
    color: #e51717;
}
.header img.erroricon{
    float: right;
    margin-top: -15px;
    margin-left: 50px;
}
.header .tools{
    float: right;
}
.header .tools a {
    border-radius: 5px;
    width: 25px;
    text-align: center;
    margin-right: 7px;
}
.header .tools a,
.header .tools span{
    display: block;
    float: left;
    height: 25px;
    padding: 5px;
}
.header .tools span{
    display: none;
}
.header .tools a:hover{
    background: #fff;
    text-decoration: none;
}
.header .tools a:active img{
    position: relative;
    left: 2px;
    top: 2px;
}
.header .tools textarea{
    position: absolute;
    top: -500px;
    right: 300px;
    width: 750px;
    height: 150px;
}
.header h2{
    font-size: 20px;
    line-height: 1.25;
}
.header pre{
    margin: 10px 0;
    overflow-y: scroll;
    font-family: Courier, monospace;
    font-size: 14px;
}

/* previous exceptions */
.header .previous{
    margin: 20px 0;
    padding-left: 30px;
}
.header .previous div{
    margin: 20px 0;
}
.header .previous .arrow{
    -moz-transform: scale(-1, 1);
    -webkit-transform: scale(-1, 1);
    -o-transform: scale(-1, 1);
    transform: scale(-1, 1);
    filter: progid:DXImageTransform.Microsoft.BasicImage(mirror=1);
    font-size: 26px;
    position: absolute;
    margin-top: -3px;
    margin-left: -30px;
    color: #e51717;
}
.header .previous h2{
    font-size: 20px;
    color: #e57373;
    margin-bottom: 10px;
}
.header .previous h2 span{
    color: #e51717;
}
.header .previous h2 a{
    color: #e57373;
}
.header .previous h2 a:hover{
    color: #e51717;
}
.header .previous h3{
    font-size: 14px;
    margin: 10px 0;
}
.header .previous p{
    font-size: 14px;
    color: #aaa;
}
.header .previous pre{
    font-family: Courier, monospace;
    font-size: 14px;
    margin: 10px 0;
}

/* call stack */
.call-stack{
    margin-top: 30px;
    margin-bottom: 40px;
}
.call-stack ul li{
    margin: 1px 0;
}
.call-stack ul li .element-wrap{
    cursor: pointer;
    padding: 15px 0;
    background-color: #fdfdfd;
}
.call-stack ul li.application .element-wrap{
    background-color: #fafafa;
}
.call-stack ul li .element-wrap:hover{
    background-color: #edf9ff;
}
.call-stack ul li .element{
    min-width: 860px; /* 960px - 50px * 2 */
    margin: 0 auto;
    padding: 0 50px;
    position: relative;
}
.call-stack ul li a{
    color: #505050;
}
.call-stack ul li a:hover{
    color: #000;
}
.call-stack ul li .item-number{
    width: 45px;
    display: inline-block;
}
.call-stack ul li .text{
    color: #aaa;
}
.call-stack ul li.application .text{
    color: #505050;
}
.call-stack ul li .at{
    float: right;
    display: inline-block;
    width: 7em;
    padding-left: 1em;
    text-align: left;
    color: #aaa;
}
.call-stack ul li.application .at{
    color: #505050;
}
.call-stack ul li .line{
    display: inline-block;
    width: 3em;
    text-align: right;
}
.call-stack ul li .code-wrap{
    display: none;
    position: relative;
}
.call-stack ul li.application .code-wrap{
    display: block;
}
.call-stack ul li .error-line,
.call-stack ul li .hover-line{
    background-color: #ffebeb;
    position: absolute;
    width: 100%;
    z-index: 100;
    margin-top: 0;
}
.call-stack ul li .hover-line{
    background: none;
}
.call-stack ul li .hover-line.hover,
.call-stack ul li .hover-line:hover{
    background: #edf9ff !important;
}
.call-stack ul li .code{
    min-width: 860px; /* 960px - 50px * 2 */
    margin: 15px auto;
    padding: 0 50px;
    position: relative;
}
.call-stack ul li .code .lines-item{
    position: absolute;
    z-index: 200;
    display: block;
    width: 25px;
    text-align: right;
    color: #aaa;
    line-height: 20px;
    font-size: 12px;
    margin-top: 1px;
    font-family: Consolas, monospace;
}
.call-stack ul li .code pre{
    position: relative;
    z-index: 200;
    left: 50px;
    line-height: 20px;
    font-size: 12px;
    font-family: Consolas, monospace;
    display: inline;
}
@-moz-document url-prefix() {
    .call-stack ul li .code pre{
        line-height: 20px;
    }
}

/* request */
.request{
    background-color: #fafafa;
    padding-top: 40px;
    padding-bottom: 40px;
    margin-top: 40px;
    margin-bottom: 1px;
}
.request .code{
    min-width: 860px; /* 960px - 50px * 2 */
    margin: 0 auto;
    padding: 15px 50px;
}
.request .code pre{
    font-size: 14px;
    line-height: 18px;
    font-family: Consolas, monospace;
    display: inline;
    word-wrap: break-word;
}

/* footer */
.footer{
    position: relative;
    height: 222px;
    min-width: 860px; /* 960px - 50px * 2 */
    padding: 0 50px;
    margin: 1px auto 0 auto;
}
.footer p{
    font-size: 16px;
    padding-bottom: 10px;
}
.footer p a{
    color: #505050;
}
.footer p a:hover{
    color: #000;
}
.footer .timestamp{
    font-size: 14px;
    padding-top: 67px;
    margin-bottom: 28px;
}
.footer img{
    position: absolute;
    right: -50px;
}

/* highlight.js */
.comment{
    color: #808080;
    font-style: italic;
}
.keyword{
    color: #000080;
}
.number{
    color: #00a;
}
.number{
    font-weight: normal;
}
.string, .value{
    color: #0a0;
}
.symbol, .char {
    color: #505050;
    background: #d0eded;
    font-style: italic;
}
.phpdoc{
    text-decoration: underline;
}
.variable{
    color: #a00;
}

body pre {
    pointer-events: none;
}
body.mousedown pre {
    pointer-events: auto;
}
    </style>
</head>

<body>
    <svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" display="none">
        <symbol id="new-window" viewBox="0 0 24 24">
            <g transform="scale(0.0234375 0.0234375)">
                <path d="M598 128h298v298h-86v-152l-418 418-60-60 418-418h-152v-86zM810 810v-298h86v298c0 46-40 86-86 86h-596c-48 0-86-40-86-86v-596c0-46 38-86 86-86h298v86h-298v596h596z"></path>
            </g>
        </symbol>
    </svg>

    <div class="header">
        <div class="tools">
            <textarea id="clipboard"><?= $handler->htmlEncode($exception) ?></textarea>
            <span id="copied">Copied!</span>
            <!-- Icon Credit: Font Awesome by Dave Gandy - http://fontawesome.io ; fa-clipboard, fa-stackoverflow, fa-google-->
            <a href="#" id="copy-stacktrace" title="Copy the stacktrace for use in a bug report or pastebin">
            <img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABgAAAAZCAYAAAArK+5dAAAABGdBTUEAALGPC/xhBQAAAAZiS0dEAP8A/wD/oL2nkwAAAAlwSFlzAAALEwAACxMBAJqcGAAAAR1JREFUSMftlTFKxEAYhb8n2c7CarGR9RAWHkFsvMIqCy4iVpZ6AS9gIXqDbbdYLEUstLPwBt5AXQw+mxTDkkyymWwh+CAQMsP/zZt/XkbAEbAPiOVlYGr7rmqCgCcgo71y27tVg9lC8Wvbt3UVJV0CB0ENYoBQG5K2Gqx6vak9Ac8kyvZO1dgaK1YnAEnDpj2I6azk2wXQB04l9WzftAbYfixZ+Wfx+gocS2IR0lUPToCXAjLqHGD7o9jCB2AcQjo7RbbnwDlwH0Ky1LpFLyYluzKW9J7qYAbkwCB4wj/BYeMkx9JakY0JMPgbSf4HJAPeVgnIgBGwHXFzBWy2Btj+irmQNE91UKfvAtTmas1luy6Re8AQ6C1Z/AeY/gK6sUu5CuQ0NQAAAABJRU5ErkJggg==" alt="Copy Stacktrace"/>
            </a>
            <a href="https://stackoverflow.com/search?<?= http_build_query(['q' => $exception->getMessage()]) ?>" title="Search error on Stackoverflow" target="_blank">
                <img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABcAAAAZCAYAAADaILXQAAAABGdBTUEAALGPC/xhBQAAAAZiS0dEAP8A/wD/oL2nkwAAAAlwSFlzAAALEwAACxMBAJqcGAAAAc9JREFUSMe11MuLzlEcx/GXuTQpCzQYl3EL0UxNNDRNsWAht40UC7OzkT9BsrCQkI2ysJeSknssRsl9lFyGGrkUjRqDlEth2HzU0+SZeeZ5PN86nfP7ds7n+z3v7/f8qMw6MFEVbDt6cKTYhtoKxOuxAXPxFQ8rFa/D76z7s27PuIWBSlDsxmGMz3cNjgXPWUwoV3gh7kboBJrib8SV+A+Ui+UDHmEVpmMtHuAV+rAO8/ERveUwf4Nr6Ezm6/E2vnosxQpcx2Ap4u059BJD+IRLaEUzVifJ49k7E8txrhTxfdiGjRF/ji+4HN6LsSxIDgXXTdyoLaH15qWYk4JjMxrC+WoCdUS8DTtxEb9GEx/CbZzOQ1mQIO3YmvUZ3MPK3Kb77+FxYyxoAzahK2zhZ0TP437BIxuxW6ZhP2bn0XwOgl6cSgvOwlQswkm8H860mLWGcWeBrx9P8DjzjnBux7PhAoVYmjO/w49kvAYtCdRYpCYv0iU9mBEaA/heKN6TeUuuPNymJEhLxpKCf0kXnoZ7E3bhTt0YijmQTuguuPWcBOor1sfF7Gj6txS78C9njSraSJnvTV9XRXywGpnvwbcy9SaPJt5WDeYH/2MtX8Mf2fhjE3QWPKAAAAAASUVORK5CYII=" alt="Search Stackoverflow"/>
            </a>
            <a href="https://www.google.com/search?<?= http_build_query(['q' => $exception->getMessage()]) ?>" title="Search error on Google" target="_blank">
                <img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABQAAAAZCAYAAAAxFw7TAAAABGdBTUEAALGPC/xhBQAAAAZiS0dEAP8A/wD/oL2nkwAAAAlwSFlzAAALEwAACxMBAJqcGAAAAZZJREFUOMvl1T9IlVEYBvCf37301+iPFgYtBhUYQUIQiGaEgVCDELg11l5jo2MNgTg5NYiTNN2GIKJIA0MwGlIMFQoirAjRoUiq5XxwOHz33rrq1APf8pznPO/7nu897+G/Q1ONtYPoxVkcxk58wxJe4g1+/41hK25gAKUaAecxjFcxmW44h1F0IqtTXSuuBN1MTpYjQRfu18mqCF+KSj6GMTQn4qd4iHdYx1FcxHXswxAqRVHuhbTz7zku1MjqCLqr/eWTGE/4W3jRSNtk6Eu4x42a5YbnE25iM41dRlvCva2irRRoU6xk2B8R6/ixiQQPZViNiOZwxRrFRhmf0BKRHZgtEE+E3ouxG4Nxk5cxjdMRea2K4YMC7lJiuJDhSSLqr9a0CXbgZsJNlfAVJ9AeLfRgEe9rmN0Noy3HGobyQTCHq0GYb+jH8SD8jo1wly+HO3wmCTKC2aYtmDYwidv4FW/+gNeh3F3/YPYMd/CzaMB+xCPswak6Q/ZzqGg4HEfdN+VAeFM6w5uyN5znchj703lW24o/8/BJk5VnLk0AAAAASUVORK5CYII=" alt="Search Google"/>
            </a>
        <?php if ($exception instanceof \yii\base\ErrorException): ?>
            <img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAEwAAABACAMAAACHi2FiAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAyBpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuNS1jMDE0IDc5LjE1MTQ4MSwgMjAxMy8wMy8xMy0xMjowOToxNSAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wTU09Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9tbS8iIHhtbG5zOnN0UmVmPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvc1R5cGUvUmVzb3VyY2VSZWYjIiB4bWxuczp4bXA9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC8iIHhtcE1NOkRvY3VtZW50SUQ9InhtcC5kaWQ6Mjk0MTdEMEI1QjhGMTFFM0I3QzE5ODkzMUQwNUQyMzYiIHhtcE1NOkluc3RhbmNlSUQ9InhtcC5paWQ6Mjk0MTdEMEE1QjhGMTFFM0I3QzE5ODkzMUQwNUQyMzYiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIENTNSBXaW5kb3dzIj4gPHhtcE1NOkRlcml2ZWRGcm9tIHN0UmVmOmluc3RhbmNlSUQ9InhtcC5paWQ6NThCMjFBODBDNTUwMTFFMkE0QzFFREYxQTMyMDUzRTEiIHN0UmVmOmRvY3VtZW50SUQ9InhtcC5kaWQ6NThCMjFBODFDNTUwMTFFMkE0QzFFREYxQTMyMDUzRTEiLz4gPC9yZGY6RGVzY3JpcHRpb24+IDwvcmRmOlJERj4gPC94OnhtcG1ldGE+IDw/eHBhY2tldCBlbmQ9InIiPz6hNq0vAAACylBMVEXz8/Plc3P////m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" class="erroricon" alt="Error"/>
            </div>
            <h1>
                <span><?= $handler->htmlEncode($exception->getName()) ?></span>
                &ndash; <?= $handler->addTypeLinks(get_class($exception)) ?>
            </h1>
        <?php else: ?>
            <img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAEAAAAA6CAMAAAA3Dq9LAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAyBpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuNS1jMDE0IDc5LjE1MTQ4MSwgMjAxMy8wMy8xMy0xMjowOToxNSAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wTU09Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9tbS8iIHhtbG5zOnN0UmVmPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvc1R5cGUvUmVzb3VyY2VSZWYjIiB4bWxuczp4bXA9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC8iIHhtcE1NOkRvY3VtZW50SUQ9InhtcC5kaWQ6QUFDNzhCNUM1QjhDMTFFM0I3QzE5ODkzMUQwNUQyMzYiIHhtcE1NOkluc3RhbmNlSUQ9InhtcC5paWQ6QUFDNzhCNUI1QjhDMTFFM0I3QzE5ODkzMUQwNUQyMzYiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIENTNSBXaW5kb3dzIj4gPHhtcE1NOkRlcml2ZWRGcm9tIHN0UmVmOmluc3RhbmNlSUQ9InhtcC5paWQ6NzBFREE5RDFDNDdEMTFFMkJGNUU4MkNCQUY4MUM3RUEiIHN0UmVmOmRvY3VtZW50SUQ9InhtcC5kaWQ6NzBFREE5RDJDNDdEMTFFMkJGNUU4MkNCQUY4MUM3RUEiLz4gPC9yZGY6RGVzY3JpcHRpb24+IDwvcmRmOlJERj4gPC94OnhtcG1ldGE+IDw/eHBhY2tldCBlbmQ9InIiPz5g4xOFAAADAFBMVEUAAAD////lc3PahobplZXhl5felpbdlpbqoKDln5/moqLgn5/ppqbopaXmpqborKznrKziqKjlq6vnrq7psLDnr6/irKzpsrLqtLTps7Pst7ferKzqtrbntLTturrtvLzqvLzourrhtbXrwMDov7/sw8PrwsLowMDhvb3tysrqycntz8/qzMzqzs7u1NTr09Ps19fo1NTv29vo1dXx4ODx4uLw4uLx5OTq39/x5+fx6Ojw5+fo39/x6enw6Ojp4eHz7Ozy6+vt5+fq5OTz7u7y7e3s5+fz8PDy7+/x7u7z8fHy8PDt6+vz8vLz8/Py8vLw8PDv7+/t7e0AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAACxvAGHAAAB9klEQVR42qTXfVvBUBgG8Od6qGwx75WoVCKJSlR6pZJSQt//w8S2w86cPXaO+z9n8xu7bs8Z+KXSDgWDoTZ5ClAHm2GcJNxUBfoRNBPpKwInaKesBnQ1BmjvSkAeZ8mrAC1tDmgtBSCFjmTkgQZyuZUGDB6IygJ1dKUuCRhuwJADKriQmgzQ0xcBXQYooiBl/0BXEwHiQgNV4oAd++WxX4CV+PRvZObnyP4IHZ8AK/E1W7giCg1EiS/ZygVRaCA6VGUrVaLQQJR4EcCGD8CgAGM54CjxDDgnCg1Eic/YYokoNBAlLrHFgmOxQgMdZ4kLIkDvkUAelwFYpICWJgT2uN9UhwC4SYyHbDmN3hMaiEmcFgP8JgHEIJwBMX495QXUXBMk5gFwhQZiEjNgECMmNBCTOPZtHXhCYpMAahLv7E+zu43EJgH0JPZKZRHoaDLAvNAzICM4zTWVhYUGYYmtxB/H06E8fokLNomuC0gJrpJgV0kIDuZ5oCH6okkGJEX7VIsDDGmAFRqEJfYD4I0D0FWA6ByooApgFRo8HidM4NN+ZvYADAZ4ljh3YCbndbxmAXIldj/1gGsSy6U8BQaaOqAPJsADrpD7CfC1tgLwPL0HW+rv3zRv4uu66vs37qwefGQDSsm+sSoPR2OFjIZL/zf6yb8AAwCmB2Y7BrVl9wAAAABJRU5ErkJggg==" class="erroricon" alt="Exception"/>
            </div>
            <h1><?php
                if ($exception instanceof \yii\web\HttpException) {
                    echo '<span>' . $handler->createHttpStatusLink($exception->statusCode, $handler->htmlEncode($exception->getName())) . '</span>';
                    echo ' &ndash; ' . $handler->addTypeLinks(get_class($exception));
                } else {
                    $name = $handler->getExceptionName($exception);
                    if ($name !== null) {
                        echo '<span>' . $handler->htmlEncode($name) . '</span>';
                        echo ' &ndash; ' . $handler->addTypeLinks(get_class($exception));
                    } else {
                        echo '<span>' . $handler->htmlEncode(get_class($exception)) . '</span>';
                    }
                }
            ?></h1>
        <?php endif; ?>
        <h2><?= nl2br($handler->htmlEncode($exception->getMessage())) ?></h2>

        <?php if ($exception instanceof \yii\db\Exception && !empty($exception->errorInfo)): ?>
            <pre>Error Info: <?= $handler->htmlEncode(print_r($exception->errorInfo, true)) ?></pre>
        <?php endif ?>

        <?= $handler->renderPreviousExceptions($exception) ?>
    </div>

    <div class="call-stack">
        <?= $handler->renderCallStack($exception) ?>
    </div>

    <div class="request">
        <div class="code">
            <?= $handler->renderRequest() ?>
        </div>
    </div>

    <div class="footer">
        <img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAS4AAADeCAIAAAAMx3q5AAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAyBpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuMC1jMDYwIDYxLjEzNDc3NywgMjAxMC8wMi8xMi0xNzozMjowMCAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvIiB4bWxuczp4bXBNTT0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL21tLyIgeG1sbnM6c3RSZWY9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9zVHlwZS9SZXNvdXJjZVJlZiMiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIENTNSBXaW5kb3dzIiB4bXBNTTpJbnN0YW5jZUlEPSJ4bXAuaWlkOkE5NkEyOTAzQzQ3RDExRTI4NjIzOEE2RkU5QTc4RTU0IiB4bXBNTTpEb2N1bWVudElEPSJ4bXAuZGlkOkE5NkEyOTA0QzQ3RDExRTI4NjIzOEE2RkU5QTc4RTU0Ij4gPHhtcE1NOkRlcml2ZWRGcm9tIHN0UmVmOmluc3RhbmNlSUQ9InhtcC5paWQ6QTk2QTI5MDFDNDdEMTFFMjg2MjM4QTZGRTlBNzhFNTQiIHN0UmVmOmRvY3VtZW50SUQ9InhtcC5kaWQ6QTk2QTI5MDJDNDdEMTFFMjg2MjM4QTZGRTlBNzhFNTQiLz4gPC9yZGY6RGVzY3JpcHRpb24+IDwvcmRmOlJERj4gPC94OnhtcG1ldGE+IDw/eHBhY2tldCBlbmQ9InIiPz4DYCAjAAAx4UlEQVR42ux9aZvjuJEm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" alt="Yii Framework"/>
        <p class="timestamp"><?= date('Y-m-d, H:i:s') ?></p>
        <p><?= $handler->createServerInformationLink() ?></p>
        <p><a href="http://yiiframework.com/">Yii Framework</a>/<?= $handler->createFrameworkVersionLink() ?></p>
    </div>

    <script>
var hljs=new function(){function l(o){return o.replace(/&/gm,"&amp;").replace(/</gm,"&lt;").replace(/>/gm,"&gt;")}function b(p){for(var o=p.firstChild;o;o=o.nextSibling){if(o.nodeName=="CODE"){return o}if(!(o.nodeType==3&&o.nodeValue.match(/\s+/))){break}}}function h(p,o){return Array.prototype.map.call(p.childNodes,function(q){if(q.nodeType==3){return o?q.nodeValue.replace(/\n/g,""):q.nodeValue}if(q.nodeName=="BR"){return"\n"}return h(q,o)}).join("")}function a(q){var p=(q.className+" "+q.parentNode.className).split(/\s+/);p=p.map(function(r){return r.replace(/^language-/,"")});for(var o=0;o<p.length;o++){if(e[p[o]]||p[o]=="no-highlight"){return p[o]}}}function c(q){var o=[];(function p(r,s){for(var t=r.firstChild;t;t=t.nextSibling){if(t.nodeType==3){s+=t.nodeValue.length}else{if(t.nodeName=="BR"){s+=1}else{if(t.nodeType==1){o.push({event:"start",offset:s,node:t});s=p(t,s);o.push({event:"stop",offset:s,node:t})}}}}return s})(q,0);return o}function j(x,v,w){var p=0;var y="";var r=[];function t(){if(x.length&&v.length){if(x[0].offset!=v[0].offset){return(x[0].offset<v[0].offset)?x:v}else{return v[0].event=="start"?x:v}}else{return x.length?x:v}}function s(A){function z(B){return" "+B.nodeName+'="'+l(B.value)+'"'}return"<"+A.nodeName+Array.prototype.map.call(A.attributes,z).join("")+">"}while(x.length||v.length){var u=t().splice(0,1)[0];y+=l(w.substr(p,u.offset-p));p=u.offset;if(u.event=="start"){y+=s(u.node);r.push(u.node)}else{if(u.event=="stop"){var o,q=r.length;do{q--;o=r[q];y+=("</"+o.nodeName.toLowerCase()+">")}while(o!=u.node);r.splice(q,1);while(q<r.length){y+=s(r[q]);q++}}}}return y+l(w.substr(p))}function f(q){function o(s,r){return RegExp(s,"m"+(q.cI?"i":"")+(r?"g":""))}function p(y,w){if(y.compiled){return}y.compiled=true;var s=[];if(y.k){var r={};function z(A,t){t.split(" ").forEach(function(B){var C=B.split("|");r[C[0]]=[A,C[1]?Number(C[1]):1];s.push(C[0])})}y.lR=o(y.l||hljs.IR,true);if(typeof y.k=="string"){z("keyword",y.k)}else{for(var x in y.k){if(!y.k.hasOwnProperty(x)){continue}z(x,y.k[x])}}y.k=r}if(w){if(y.bWK){y.b="\\b("+s.join("|")+")\\s"}y.bR=o(y.b?y.b:"\\B|\\b");if(!y.e&&!y.eW){y.e="\\B|\\b"}if(y.e){y.eR=o(y.e)}y.tE=y.e||"";if(y.eW&&w.tE){y.tE+=(y.e?"|":"")+w.tE}}if(y.i){y.iR=o(y.i)}if(y.r===undefined){y.r=1}if(!y.c){y.c=[]}for(var v=0;v<y.c.length;v++){if(y.c[v]=="self"){y.c[v]=y}p(y.c[v],y)}if(y.starts){p(y.starts,w)}var u=[];for(var v=0;v<y.c.length;v++){u.push(y.c[v].b)}if(y.tE){u.push(y.tE)}if(y.i){u.push(y.i)}y.t=u.length?o(u.join("|"),true):{exec:function(t){return null}}}p(q)}function d(D,E){function o(r,M){for(var L=0;L<M.c.length;L++){var K=M.c[L].bR.exec(r);if(K&&K.index==0){return M.c[L]}}}function s(K,r){if(K.e&&K.eR.test(r)){return K}if(K.eW){return s(K.parent,r)}}function t(r,K){return K.i&&K.iR.test(r)}function y(L,r){var K=F.cI?r[0].toLowerCase():r[0];return L.k.hasOwnProperty(K)&&L.k[K]}function G(){var K=l(w);if(!A.k){return K}var r="";var N=0;A.lR.lastIndex=0;var L=A.lR.exec(K);while(L){r+=K.substr(N,L.index-N);var M=y(A,L);if(M){v+=M[1];r+='<span class="'+M[0]+'">'+L[0]+"</span>"}else{r+=L[0]}N=A.lR.lastIndex;L=A.lR.exec(K)}return r+K.substr(N)}function z(){if(A.sL&&!e[A.sL]){return l(w)}var r=A.sL?d(A.sL,w):g(w);if(A.r>0){v+=r.keyword_count;B+=r.r}return'<span class="'+r.language+'">'+r.value+"</span>"}function J(){return A.sL!==undefined?z():G()}function I(L,r){var K=L.cN?'<span class="'+L.cN+'">':"";if(L.rB){x+=K;w=""}else{if(L.eB){x+=l(r)+K;w=""}else{x+=K;w=r}}A=Object.create(L,{parent:{value:A}});B+=L.r}function C(K,r){w+=K;if(r===undefined){x+=J();return 0}var L=o(r,A);if(L){x+=J();I(L,r);return L.rB?0:r.length}var M=s(A,r);if(M){if(!(M.rE||M.eE)){w+=r}x+=J();do{if(A.cN){x+="</span>"}A=A.parent}while(A!=M.parent);if(M.eE){x+=l(r)}w="";if(M.starts){I(M.starts,"")}return M.rE?0:r.length}if(t(r,A)){throw"Illegal"}w+=r;return r.length||1}var F=e[D];f(F);var A=F;var w="";var B=0;var v=0;var x="";try{var u,q,p=0;while(true){A.t.lastIndex=p;u=A.t.exec(E);if(!u){break}q=C(E.substr(p,u.index-p),u[0]);p=u.index+q}C(E.substr(p));return{r:B,keyword_count:v,value:x,language:D}}catch(H){if(H=="Illegal"){return{r:0,keyword_count:0,value:l(E)}}else{throw H}}}function g(s){var o={keyword_count:0,r:0,value:l(s)};var q=o;for(var p in e){if(!e.hasOwnProperty(p)){continue}var r=d(p,s);r.language=p;if(r.keyword_count+r.r>q.keyword_count+q.r){q=r}if(r.keyword_count+r.r>o.keyword_count+o.r){q=o;o=r}}if(q.language){o.second_best=q}return o}function i(q,p,o){if(p){q=q.replace(/^((<[^>]+>|\t)+)/gm,function(r,v,u,t){return v.replace(/\t/g,p)})}if(o){q=q.replace(/\n/g,"<br>")}return q}function m(r,u,p){var v=h(r,p);var t=a(r);if(t=="no-highlight"){return}var w=t?d(t,v):g(v);t=w.language;var o=c(r);if(o.length){var q=document.createElement("pre");q.innerHTML=w.value;w.value=j(o,c(q),v)}w.value=i(w.value,u,p);var s=r.className;if(!s.match("(\\s|^)(language-)?"+t+"(\\s|$)")){s=s?(s+" "+t):t}r.innerHTML=w.value;r.className=s;r.result={language:t,kw:w.keyword_count,re:w.r};if(w.second_best){r.second_best={language:w.second_best.language,kw:w.second_best.keyword_count,re:w.second_best.r}}}function n(){if(n.called){return}n.called=true;Array.prototype.map.call(document.getElementsByTagName("pre"),b).filter(Boolean).forEach(function(o){m(o,hljs.tabReplace)})}function k(){window.addEventListener("DOMContentLoaded",n,false);window.addEventListener("load",n,false)}var e={};this.LANGUAGES=e;this.highlight=d;this.highlightAuto=g;this.fixMarkup=i;this.highlightBlock=m;this.initHighlighting=n;this.initHighlightingOnLoad=k;this.IR="[a-zA-Z][a-zA-Z0-9_]*";this.UIR="[a-zA-Z_][a-zA-Z0-9_]*";this.NR="\\b\\d+(\\.\\d+)?";this.CNR="(\\b0[xX][a-fA-F0-9]+|(\\b\\d+(\\.\\d*)?|\\.\\d+)([eE][-+]?\\d+)?)";this.BNR="\\b(0b[01]+)";this.RSR="!|!=|!==|%|%=|&|&&|&=|\\*|\\*=|\\+|\\+=|,|\\.|-|-=|/|/=|:|;|<|<<|<<=|<=|=|==|===|>|>=|>>|>>=|>>>|>>>=|\\?|\\[|\\{|\\(|\\^|\\^=|\\||\\|=|\\|\\||~";this.BE={b:"\\\\[\\s\\S]",r:0};this.ASM={cN:"string",b:"'",e:"'",i:"\\n",c:[this.BE],r:0};this.QSM={cN:"string",b:'"',e:'"',i:"\\n",c:[this.BE],r:0};this.CLCM={cN:"comment",b:"//",e:"$"};this.CBLCLM={cN:"comment",b:"/\\*",e:"\\*/"};this.HCM={cN:"comment",b:"#",e:"$"};this.NM={cN:"number",b:this.NR,r:0};this.CNM={cN:"number",b:this.CNR,r:0};this.BNM={cN:"number",b:this.BNR,r:0};this.inherit=function(q,r){var o={};for(var p in q){o[p]=q[p]}if(r){for(var p in r){o[p]=r[p]}}return o}}();hljs.LANGUAGES.php=function(a){var e={cN:"variable",b:"\\$+[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*"};var b=[a.inherit(a.ASM,{i:null}),a.inherit(a.QSM,{i:null}),{cN:"string",b:'b"',e:'"',c:[a.BE]},{cN:"string",b:"b'",e:"'",c:[a.BE]}];var c=[a.BNM,a.CNM];var d={cN:"title",b:a.UIR};return{cI:true,k:"and include_once list abstract global private echo interface as static endswitch array null if endwhile or const for endforeach self var while isset public protected exit foreach throw elseif include __FILE__ empty require_once do xor return implements parent clone use __CLASS__ __LINE__ else break print eval new catch __METHOD__ case exception php_user_filter default die require __FUNCTION__ enddeclare final try this switch continue endfor endif declare unset true false namespace trait goto instanceof insteadof __DIR__ __NAMESPACE__ __halt_compiler",c:[a.CLCM,a.HCM,{cN:"comment",b:"/\\*",e:"\\*/",c:[{cN:"phpdoc",b:"\\s@[A-Za-z]+"}]},{cN:"comment",eB:true,b:"__halt_compiler.+?;",eW:true},{cN:"string",b:"<<<['\"]?\\w+['\"]?$",e:"^\\w+;",c:[a.BE]},{cN:"preprocessor",b:"<\\?php",r:10},{cN:"preprocessor",b:"\\?>"},e,{cN:"function",bWK:true,e:"{",k:"function",i:"\\$|\\[|%",c:[d,{cN:"params",b:"\\(",e:"\\)",c:["self",e,a.CBLCLM].concat(b).concat(c)}]},{cN:"class",bWK:true,e:"{",k:"class",i:"[:\\(\\$]",c:[{bWK:true,eW:true,k:"extends",c:[d]},d]},{b:"=>"}].concat(b).concat(c)}}(hljs);
    </script>

    <script>
window.onload = function() {
    var codeBlocks = document.getElementsByTagName('pre'),
        callStackItems = document.getElementsByClassName('call-stack-item');

    // highlight code blocks
    for (var i = 0, imax = codeBlocks.length; i < imax; ++i) {
        hljs.highlightBlock(codeBlocks[i], '    ');
    }

    var refreshCallStackItemCode = function(callStackItem) {
        if (!callStackItem.getElementsByTagName('pre')[0]) {
            return;
        }
        var top = callStackItem.getElementsByClassName('code-wrap')[0].offsetTop - window.pageYOffset + 3,
            lines = callStackItem.getElementsByTagName('pre')[0].getClientRects(),
            lineNumbers = callStackItem.getElementsByClassName('lines-item'),
            errorLine = callStackItem.getElementsByClassName('error-line')[0],
            hoverLines = callStackItem.getElementsByClassName('hover-line');
        for (var i = 0, imax = lines.length; i < imax; ++i) {
            if (!lineNumbers[i]) {
                continue;
            }
            lineNumbers[i].style.top = parseInt(lines[i].top - top) + 'px';
            hoverLines[i].style.top = parseInt(lines[i].top - top) + 'px';
            hoverLines[i].style.height = parseInt(lines[i].bottom - lines[i].top + 6) + 'px';
            if (parseInt(callStackItem.getAttribute('data-line')) == i) {
                errorLine.style.top = parseInt(lines[i].top - top) + 'px';
                errorLine.style.height = parseInt(lines[i].bottom - lines[i].top + 6) + 'px';
            }
        }
    };

    for (var i = 0, imax = callStackItems.length; i < imax; ++i) {
        refreshCallStackItemCode(callStackItems[i]);

        // toggle code block visibility
        callStackItems[i].getElementsByClassName('element-wrap')[0].addEventListener('click', function(event) {
            if (event.target.nodeName.toLowerCase() === 'a') {
                return;
            }

            var callStackItem = this.parentNode,
                code = callStackItem.getElementsByClassName('code-wrap')[0];

            if (typeof code !== 'undefined') {
                code.style.display = window.getComputedStyle(code).display == 'block' ? 'none' : 'block';
                refreshCallStackItemCode(callStackItem);
            }
        });
    }

    // handle copy stacktrace action on clipboard button
    document.getElementById('copy-stacktrace').onclick = function(e) {
        e.preventDefault();
        var textarea = document.getElementById('clipboard');
        textarea.focus();
        textarea.select();
        try {
            succeeded = document.execCommand('copy');
        } catch (err) {
            succeeded = false;
        }
        if (succeeded) {
            document.getElementById('copied').style.display = 'block';
        } else {
            // fallback: show textarea if browser does not support copying directly
            textarea.style.top = 0;
        }
    }
};

    // Highlight lines that have text in them but still support text selection:
    document.onmousedown = function() { document.getElementsByTagName('body')[0].classList.add('mousedown'); };
    document.onmouseup = function() { document.getElementsByTagName('body')[0].classList.remove('mousedown'); };
    </script>
    <?php if (method_exists($this, 'endBody')): ?>
        <?php $this->endBody() // to allow injecting code into body (mostly by Yii Debug Toolbar)?>
    <?php endif ?>
</body>

</html>
<?php if (method_exists($this, 'endPage')): ?>
    <?php $this->endPage() ?>
<?php endif ?>
