<?php
/**
 * @var \Exception $exception
 * @var \yii\base\ErrorHandler $handler
 */
?>
<?php if (method_exists($this, 'beginPage')) $this->beginPage(); ?>
<!doctype html>
<html lang="en-us">

<head>
	<meta charset="utf-8"/>

	<title><?php
		if ($exception instanceof \yii\web\HttpException) {
			echo (int) $exception->statusCode . ' ' . $handler->htmlEncode($exception->getName());
		} elseif ($exception instanceof \yii\base\Exception) {
			echo $handler->htmlEncode($exception->getName() . ' – ' . get_class($exception));
		} else {
			echo $handler->htmlEncode(get_class($exception));
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
html,body{
	overflow-x: hidden;
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
.header img{
	float: right;
	margin-top: -15px;
}
.header h2{
	font-size: 20px;
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
	margin-top: -5px;
	margin-left: -25px;
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
	position: absolute;
	right: 110px; /* 50px + 60px */
	color: #aaa;
}
.call-stack ul li.application .at{
	color: #505050;
}
.call-stack ul li .line{
	position: absolute;
	right: 50px;
	width: 60px;
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
	margin-top: -61px;
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
	margin-top: -63px;
	font-family: Consolas, Courier New, monospace;
}
.call-stack ul li .code pre{
	position: relative;
	z-index: 200;
	left: 50px;
	line-height: 20px;
	font-size: 12px;
	font-family: Consolas, Courier New, monospace;
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
	font-family: Consolas, Courier New, monospace;
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
pre .subst,pre .title{
	font-weight: normal;
	color: #505050;
}
pre .comment,pre .template_comment,pre .javadoc,pre .diff .header{
	color: #808080;
	font-style: italic;
}
pre .annotation,pre .decorator,pre .preprocessor,pre .doctype,pre .pi,pre .chunk,pre .shebang,pre .apache .cbracket,
pre .prompt,pre .http .title{
	color: #808000;
}
pre .tag,pre .pi{
	background: #efefef;
}
pre .tag .title,pre .id,pre .attr_selector,pre .pseudo,pre .literal,pre .keyword,pre .hexcolor,pre .css .function,
pre .ini .title,pre .css .class,pre .list .title,pre .clojure .title,pre .nginx .title,pre .tex .command,
pre .request,pre .status{
	color: #000080;
}
pre .attribute,pre .rules .keyword,pre .number,pre .date,pre .regexp,pre .tex .special{
	color: #00a;
}
pre .number,pre .regexp{
	font-weight: normal;
}
pre .string,pre .value,pre .filter .argument,pre .css .function .params,pre .apache .tag{
	color: #0a0;
}
pre .symbol,pre .ruby .symbol .string,pre .char,pre .tex .formula{
	color: #505050;
	background: #d0eded;
	font-style: italic;
}
pre .phpdoc,pre .yardoctag,pre .javadoctag{
	text-decoration: underline;
}
pre .variable,pre .envvar,pre .apache .sqbracket,pre .nginx .built_in{
	color: #a00;
}
pre .addition{
	background: #baeeba;
}
pre .deletion{
	background: #ffc8bd;
}
pre .diff .change{
	background: #bccff9;
}
	</style>
</head>

<body>
	<div class="header">
		<?php if ($exception instanceof \yii\base\ErrorException): ?>
			<img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAEwAAABACAMAAACHi2FiAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAyBpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuNS1jMDE0IDc5LjE1MTQ4MSwgMjAxMy8wMy8xMy0xMjowOToxNSAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wTU09Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9tbS8iIHhtbG5zOnN0UmVmPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvc1R5cGUvUmVzb3VyY2VSZWYjIiB4bWxuczp4bXA9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC8iIHhtcE1NOkRvY3VtZW50SUQ9InhtcC5kaWQ6Mjk0MTdEMEI1QjhGMTFFM0I3QzE5ODkzMUQwNUQyMzYiIHhtcE1NOkluc3RhbmNlSUQ9InhtcC5paWQ6Mjk0MTdEMEE1QjhGMTFFM0I3QzE5ODkzMUQwNUQyMzYiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIENTNSBXaW5kb3dzIj4gPHhtcE1NOkRlcml2ZWRGcm9tIHN0UmVmOmluc3RhbmNlSUQ9InhtcC5paWQ6NThCMjFBODBDNTUwMTFFMkE0QzFFREYxQTMyMDUzRTEiIHN0UmVmOmRvY3VtZW50SUQ9InhtcC5kaWQ6NThCMjFBODFDNTUwMTFFMkE0QzFFREYxQTMyMDUzRTEiLz4gPC9yZGY6RGVzY3JpcHRpb24+IDwvcmRmOlJERj4gPC94OnhtcG1ldGE+IDw/eHBhY2tldCBlbmQ9InIiPz6hNq0vAAACylBMVEXz8/Plc3P////m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" alt="Error"/>
			<h1>
				<span><?= $handler->htmlEncode($exception->getName()) ?></span>
				&ndash; <?= $handler->addTypeLinks(get_class($exception)) ?>
			</h1>
		<?php else: ?>
			<img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAEAAAAA6CAMAAAA3Dq9LAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAyBpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuNS1jMDE0IDc5LjE1MTQ4MSwgMjAxMy8wMy8xMy0xMjowOToxNSAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wTU09Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9tbS8iIHhtbG5zOnN0UmVmPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvc1R5cGUvUmVzb3VyY2VSZWYjIiB4bWxuczp4bXA9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC8iIHhtcE1NOkRvY3VtZW50SUQ9InhtcC5kaWQ6QUFDNzhCNUM1QjhDMTFFM0I3QzE5ODkzMUQwNUQyMzYiIHhtcE1NOkluc3RhbmNlSUQ9InhtcC5paWQ6QUFDNzhCNUI1QjhDMTFFM0I3QzE5ODkzMUQwNUQyMzYiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIENTNSBXaW5kb3dzIj4gPHhtcE1NOkRlcml2ZWRGcm9tIHN0UmVmOmluc3RhbmNlSUQ9InhtcC5paWQ6NzBFREE5RDFDNDdEMTFFMkJGNUU4MkNCQUY4MUM3RUEiIHN0UmVmOmRvY3VtZW50SUQ9InhtcC5kaWQ6NzBFREE5RDJDNDdEMTFFMkJGNUU4MkNCQUY4MUM3RUEiLz4gPC9yZGY6RGVzY3JpcHRpb24+IDwvcmRmOlJERj4gPC94OnhtcG1ldGE+IDw/eHBhY2tldCBlbmQ9InIiPz5g4xOFAAADAFBMVEUAAAD////lc3PahobplZXhl5felpbdlpbqoKDln5/moqLgn5/ppqbopaXmpqborKznrKziqKjlq6vnrq7psLDnr6/irKzpsrLqtLTps7Pst7ferKzqtrbntLTturrtvLzqvLzourrhtbXrwMDov7/sw8PrwsLowMDhvb3tysrqycntz8/qzMzqzs7u1NTr09Ps19fo1NTv29vo1dXx4ODx4uLw4uLx5OTq39/x5+fx6Ojw5+fo39/x6enw6Ojp4eHz7Ozy6+vt5+fq5OTz7u7y7e3s5+fz8PDy7+/x7u7z8fHy8PDt6+vz8vLz8/Py8vLw8PDv7+/t7e0AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAACxvAGHAAAB9klEQVR42qTXfVvBUBgG8Od6qGwx75WoVCKJSlR6pZJSQt//w8S2w86cPXaO+z9n8xu7bs8Z+KXSDgWDoTZ5ClAHm2GcJNxUBfoRNBPpKwInaKesBnQ1BmjvSkAeZ8mrAC1tDmgtBSCFjmTkgQZyuZUGDB6IygJ1dKUuCRhuwJADKriQmgzQ0xcBXQYooiBl/0BXEwHiQgNV4oAd++WxX4CV+PRvZObnyP4IHZ8AK/E1W7giCg1EiS/ZygVRaCA6VGUrVaLQQJR4EcCGD8CgAGM54CjxDDgnCg1Eic/YYokoNBAlLrHFgmOxQgMdZ4kLIkDvkUAelwFYpICWJgT2uN9UhwC4SYyHbDmN3hMaiEmcFgP8JgHEIJwBMX495QXUXBMk5gFwhQZiEjNgECMmNBCTOPZtHXhCYpMAahLv7E+zu43EJgH0JPZKZRHoaDLAvNAzICM4zTWVhYUGYYmtxB/H06E8fokLNomuC0gJrpJgV0kIDuZ5oCH6okkGJEX7VIsDDGmAFRqEJfYD4I0D0FWA6ByooApgFRo8HidM4NN+ZvYADAZ4ljh3YCbndbxmAXIldj/1gGsSy6U8BQaaOqAPJsADrpD7CfC1tgLwPL0HW+rv3zRv4uu66vs37qwefGQDSsm+sSoPR2OFjIZL/zf6yb8AAwCmB2Y7BrVl9wAAAABJRU5ErkJggg==" alt="Exception"/>
			<h1><?php
				if ($exception instanceof \yii\web\HttpException) {
					echo '<span>' . $handler->createHttpStatusLink($exception->statusCode, $handler->htmlEncode($exception->getName())) . '</span>';
					echo ' &ndash; ' . $handler->addTypeLinks(get_class($exception));
				} elseif ($exception instanceof \yii\base\Exception) {
					echo '<span>' . $handler->htmlEncode($exception->getName()) . '</span>';
					echo ' &ndash; ' . $handler->addTypeLinks(get_class($exception));
				} else {
					echo '<span>' . $handler->htmlEncode(get_class($exception)) . '</span>';
				}
			?></h1>
		<?php endif; ?>
		<h2><?= nl2br($handler->htmlEncode($exception->getMessage())) ?></h2>
		<?php if ($exception instanceof \yii\db\Exception && $exception->errorInfo !== null): ?>
			<pre><?= var_export($exception->errorInfo, true) ?></pre>
		<?php endif; ?>
		<?= $handler->renderPreviousExceptions($exception) ?>
	</div>

	<div class="call-stack">
		<ul>
			<?= $handler->renderCallStackItem($exception->getFile(), $exception->getLine(), null, null, 1) ?>
			<?php for ($i = 0, $trace = $exception->getTrace(), $length = count($trace); $i < $length; ++$i): ?>
				<?= $handler->renderCallStackItem(@$trace[$i]['file'] ?: null, @$trace[$i]['line'] ?: null,
					@$trace[$i]['class'] ?: null, @$trace[$i]['function'] ?: null, $i + 2) ?>
			<?php endfor; ?>
		</ul>
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

	<script type="text/javascript">
var hljs=new function(){function l(o){return o.replace(/&/gm,"&amp;").replace(/</gm,"&lt;").replace(/>/gm,"&gt;")}function b(p){for(var o=p.firstChild;o;o=o.nextSibling){if(o.nodeName=="CODE"){return o}if(!(o.nodeType==3&&o.nodeValue.match(/\s+/))){break}}}function h(p,o){return Array.prototype.map.call(p.childNodes,function(q){if(q.nodeType==3){return o?q.nodeValue.replace(/\n/g,""):q.nodeValue}if(q.nodeName=="BR"){return"\n"}return h(q,o)}).join("")}function a(q){var p=(q.className+" "+q.parentNode.className).split(/\s+/);p=p.map(function(r){return r.replace(/^language-/,"")});for(var o=0;o<p.length;o++){if(e[p[o]]||p[o]=="no-highlight"){return p[o]}}}function c(q){var o=[];(function p(r,s){for(var t=r.firstChild;t;t=t.nextSibling){if(t.nodeType==3){s+=t.nodeValue.length}else{if(t.nodeName=="BR"){s+=1}else{if(t.nodeType==1){o.push({event:"start",offset:s,node:t});s=p(t,s);o.push({event:"stop",offset:s,node:t})}}}}return s})(q,0);return o}function j(x,v,w){var p=0;var y="";var r=[];function t(){if(x.length&&v.length){if(x[0].offset!=v[0].offset){return(x[0].offset<v[0].offset)?x:v}else{return v[0].event=="start"?x:v}}else{return x.length?x:v}}function s(A){function z(B){return" "+B.nodeName+'="'+l(B.value)+'"'}return"<"+A.nodeName+Array.prototype.map.call(A.attributes,z).join("")+">"}while(x.length||v.length){var u=t().splice(0,1)[0];y+=l(w.substr(p,u.offset-p));p=u.offset;if(u.event=="start"){y+=s(u.node);r.push(u.node)}else{if(u.event=="stop"){var o,q=r.length;do{q--;o=r[q];y+=("</"+o.nodeName.toLowerCase()+">")}while(o!=u.node);r.splice(q,1);while(q<r.length){y+=s(r[q]);q++}}}}return y+l(w.substr(p))}function f(q){function o(s,r){return RegExp(s,"m"+(q.cI?"i":"")+(r?"g":""))}function p(y,w){if(y.compiled){return}y.compiled=true;var s=[];if(y.k){var r={};function z(A,t){t.split(" ").forEach(function(B){var C=B.split("|");r[C[0]]=[A,C[1]?Number(C[1]):1];s.push(C[0])})}y.lR=o(y.l||hljs.IR,true);if(typeof y.k=="string"){z("keyword",y.k)}else{for(var x in y.k){if(!y.k.hasOwnProperty(x)){continue}z(x,y.k[x])}}y.k=r}if(w){if(y.bWK){y.b="\\b("+s.join("|")+")\\s"}y.bR=o(y.b?y.b:"\\B|\\b");if(!y.e&&!y.eW){y.e="\\B|\\b"}if(y.e){y.eR=o(y.e)}y.tE=y.e||"";if(y.eW&&w.tE){y.tE+=(y.e?"|":"")+w.tE}}if(y.i){y.iR=o(y.i)}if(y.r===undefined){y.r=1}if(!y.c){y.c=[]}for(var v=0;v<y.c.length;v++){if(y.c[v]=="self"){y.c[v]=y}p(y.c[v],y)}if(y.starts){p(y.starts,w)}var u=[];for(var v=0;v<y.c.length;v++){u.push(y.c[v].b)}if(y.tE){u.push(y.tE)}if(y.i){u.push(y.i)}y.t=u.length?o(u.join("|"),true):{exec:function(t){return null}}}p(q)}function d(D,E){function o(r,M){for(var L=0;L<M.c.length;L++){var K=M.c[L].bR.exec(r);if(K&&K.index==0){return M.c[L]}}}function s(K,r){if(K.e&&K.eR.test(r)){return K}if(K.eW){return s(K.parent,r)}}function t(r,K){return K.i&&K.iR.test(r)}function y(L,r){var K=F.cI?r[0].toLowerCase():r[0];return L.k.hasOwnProperty(K)&&L.k[K]}function G(){var K=l(w);if(!A.k){return K}var r="";var N=0;A.lR.lastIndex=0;var L=A.lR.exec(K);while(L){r+=K.substr(N,L.index-N);var M=y(A,L);if(M){v+=M[1];r+='<span class="'+M[0]+'">'+L[0]+"</span>"}else{r+=L[0]}N=A.lR.lastIndex;L=A.lR.exec(K)}return r+K.substr(N)}function z(){if(A.sL&&!e[A.sL]){return l(w)}var r=A.sL?d(A.sL,w):g(w);if(A.r>0){v+=r.keyword_count;B+=r.r}return'<span class="'+r.language+'">'+r.value+"</span>"}function J(){return A.sL!==undefined?z():G()}function I(L,r){var K=L.cN?'<span class="'+L.cN+'">':"";if(L.rB){x+=K;w=""}else{if(L.eB){x+=l(r)+K;w=""}else{x+=K;w=r}}A=Object.create(L,{parent:{value:A}});B+=L.r}function C(K,r){w+=K;if(r===undefined){x+=J();return 0}var L=o(r,A);if(L){x+=J();I(L,r);return L.rB?0:r.length}var M=s(A,r);if(M){if(!(M.rE||M.eE)){w+=r}x+=J();do{if(A.cN){x+="</span>"}A=A.parent}while(A!=M.parent);if(M.eE){x+=l(r)}w="";if(M.starts){I(M.starts,"")}return M.rE?0:r.length}if(t(r,A)){throw"Illegal"}w+=r;return r.length||1}var F=e[D];f(F);var A=F;var w="";var B=0;var v=0;var x="";try{var u,q,p=0;while(true){A.t.lastIndex=p;u=A.t.exec(E);if(!u){break}q=C(E.substr(p,u.index-p),u[0]);p=u.index+q}C(E.substr(p));return{r:B,keyword_count:v,value:x,language:D}}catch(H){if(H=="Illegal"){return{r:0,keyword_count:0,value:l(E)}}else{throw H}}}function g(s){var o={keyword_count:0,r:0,value:l(s)};var q=o;for(var p in e){if(!e.hasOwnProperty(p)){continue}var r=d(p,s);r.language=p;if(r.keyword_count+r.r>q.keyword_count+q.r){q=r}if(r.keyword_count+r.r>o.keyword_count+o.r){q=o;o=r}}if(q.language){o.second_best=q}return o}function i(q,p,o){if(p){q=q.replace(/^((<[^>]+>|\t)+)/gm,function(r,v,u,t){return v.replace(/\t/g,p)})}if(o){q=q.replace(/\n/g,"<br>")}return q}function m(r,u,p){var v=h(r,p);var t=a(r);if(t=="no-highlight"){return}var w=t?d(t,v):g(v);t=w.language;var o=c(r);if(o.length){var q=document.createElement("pre");q.innerHTML=w.value;w.value=j(o,c(q),v)}w.value=i(w.value,u,p);var s=r.className;if(!s.match("(\\s|^)(language-)?"+t+"(\\s|$)")){s=s?(s+" "+t):t}r.innerHTML=w.value;r.className=s;r.result={language:t,kw:w.keyword_count,re:w.r};if(w.second_best){r.second_best={language:w.second_best.language,kw:w.second_best.keyword_count,re:w.second_best.r}}}function n(){if(n.called){return}n.called=true;Array.prototype.map.call(document.getElementsByTagName("pre"),b).filter(Boolean).forEach(function(o){m(o,hljs.tabReplace)})}function k(){window.addEventListener("DOMContentLoaded",n,false);window.addEventListener("load",n,false)}var e={};this.LANGUAGES=e;this.highlight=d;this.highlightAuto=g;this.fixMarkup=i;this.highlightBlock=m;this.initHighlighting=n;this.initHighlightingOnLoad=k;this.IR="[a-zA-Z][a-zA-Z0-9_]*";this.UIR="[a-zA-Z_][a-zA-Z0-9_]*";this.NR="\\b\\d+(\\.\\d+)?";this.CNR="(\\b0[xX][a-fA-F0-9]+|(\\b\\d+(\\.\\d*)?|\\.\\d+)([eE][-+]?\\d+)?)";this.BNR="\\b(0b[01]+)";this.RSR="!|!=|!==|%|%=|&|&&|&=|\\*|\\*=|\\+|\\+=|,|\\.|-|-=|/|/=|:|;|<|<<|<<=|<=|=|==|===|>|>=|>>|>>=|>>>|>>>=|\\?|\\[|\\{|\\(|\\^|\\^=|\\||\\|=|\\|\\||~";this.BE={b:"\\\\[\\s\\S]",r:0};this.ASM={cN:"string",b:"'",e:"'",i:"\\n",c:[this.BE],r:0};this.QSM={cN:"string",b:'"',e:'"',i:"\\n",c:[this.BE],r:0};this.CLCM={cN:"comment",b:"//",e:"$"};this.CBLCLM={cN:"comment",b:"/\\*",e:"\\*/"};this.HCM={cN:"comment",b:"#",e:"$"};this.NM={cN:"number",b:this.NR,r:0};this.CNM={cN:"number",b:this.CNR,r:0};this.BNM={cN:"number",b:this.BNR,r:0};this.inherit=function(q,r){var o={};for(var p in q){o[p]=q[p]}if(r){for(var p in r){o[p]=r[p]}}return o}}();hljs.LANGUAGES.php=function(a){var e={cN:"variable",b:"\\$+[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*"};var b=[a.inherit(a.ASM,{i:null}),a.inherit(a.QSM,{i:null}),{cN:"string",b:'b"',e:'"',c:[a.BE]},{cN:"string",b:"b'",e:"'",c:[a.BE]}];var c=[a.BNM,a.CNM];var d={cN:"title",b:a.UIR};return{cI:true,k:"and include_once list abstract global private echo interface as static endswitch array null if endwhile or const for endforeach self var while isset public protected exit foreach throw elseif include __FILE__ empty require_once do xor return implements parent clone use __CLASS__ __LINE__ else break print eval new catch __METHOD__ case exception php_user_filter default die require __FUNCTION__ enddeclare final try this switch continue endfor endif declare unset true false namespace trait goto instanceof insteadof __DIR__ __NAMESPACE__ __halt_compiler",c:[a.CLCM,a.HCM,{cN:"comment",b:"/\\*",e:"\\*/",c:[{cN:"phpdoc",b:"\\s@[A-Za-z]+"}]},{cN:"comment",eB:true,b:"__halt_compiler.+?;",eW:true},{cN:"string",b:"<<<['\"]?\\w+['\"]?$",e:"^\\w+;",c:[a.BE]},{cN:"preprocessor",b:"<\\?php",r:10},{cN:"preprocessor",b:"\\?>"},e,{cN:"function",bWK:true,e:"{",k:"function",i:"\\$|\\[|%",c:[d,{cN:"params",b:"\\(",e:"\\)",c:["self",e,a.CBLCLM].concat(b).concat(c)}]},{cN:"class",bWK:true,e:"{",k:"class",i:"[:\\(\\$]",c:[{bWK:true,eW:true,k:"extends",c:[d]},d]},{b:"=>"}].concat(b).concat(c)}}(hljs);
	</script>

	<script type="text/javascript">
/*! Sizzle v1.9.4-pre | (c) 2013 jQuery Foundation, Inc. | jquery.org/license
//@ sourceMappingURL=sizzle.min.map
*/(function(e,t){function n(e,t,n,r){var o,i,u,l,a,c,s,f,p,d;if((t?t.ownerDocument||t:U)!==H&&q(t),t=t||H,n=n||[],!e||"string"!=typeof e)return n;if(1!==(l=t.nodeType)&&9!==l)return[];if(O&&!r){if(o=Ct.exec(e))if(u=o[1]){if(9===l){if(i=t.getElementById(u),!i||!i.parentNode)return n;if(i.id===u)return n.push(i),n}else if(t.ownerDocument&&(i=t.ownerDocument.getElementById(u))&&j(t,i)&&i.id===u)return n.push(i),n}else{if(o[2])return ot.apply(n,t.getElementsByTagName(e)),n;if((u=o[3])&&S.getElementsByClassName&&t.getElementsByClassName)return ot.apply(n,t.getElementsByClassName(u)),n}if(S.qsa&&(!k||!k.test(e))){if(f=s=G,p=t,d=9===l&&e,1===l&&"object"!==t.nodeName.toLowerCase()){for(c=g(e),(s=t.getAttribute("id"))?f=s.replace(Tt,"\\$&"):t.setAttribute("id",f),f="[id='"+f+"'] ",a=c.length;a--;)c[a]=f+m(c[a]);p=mt.test(e)&&t.parentNode||t,d=c.join(",")}if(d)try{return ot.apply(n,p.querySelectorAll(d)),n}catch(h){}finally{s||t.removeAttribute("id")}}}return w(e.replace(dt,"$1"),t,n,r)}function r(e){return xt.test(e+"")}function o(){function e(n,r){return t.push(n+=" ")>L.cacheLength&&delete e[t.shift()],e[n]=r}var t=[];return e}function i(e){return e[G]=!0,e}function u(e){var t=H.createElement("div");try{return!!e(t)}catch(n){return!1}finally{t.parentNode&&t.parentNode.removeChild(t),t=null}}function l(e,t,n){e=e.split("|");for(var r,o=e.length,i=n?null:t;o--;)(r=L.attrHandle[e[o]])&&r!==t||(L.attrHandle[e[o]]=i)}function a(e,t){var n=e.getAttributeNode(t);return n&&n.specified?n.value:e[t]===!0?t.toLowerCase():null}function c(e,t){return e.getAttribute(t,"type"===t.toLowerCase()?1:2)}function s(e){return"input"===e.nodeName.toLowerCase()?e.defaultValue:t}function f(e,t){var n=t&&e,r=n&&1===e.nodeType&&1===t.nodeType&&(~t.sourceIndex||_)-(~e.sourceIndex||_);if(r)return r;if(n)for(;n=n.nextSibling;)if(n===t)return-1;return e?1:-1}function p(e){return function(t){var n=t.nodeName.toLowerCase();return"input"===n&&t.type===e}}function d(e){return function(t){var n=t.nodeName.toLowerCase();return("input"===n||"button"===n)&&t.type===e}}function h(e){return i(function(t){return t=+t,i(function(n,r){for(var o,i=e([],n.length,t),u=i.length;u--;)n[o=i[u]]&&(n[o]=!(r[o]=n[o]))})})}function g(e,t){var r,o,i,u,l,a,c,s=K[e+" "];if(s)return t?0:s.slice(0);for(l=e,a=[],c=L.preFilter;l;){(!r||(o=ht.exec(l)))&&(o&&(l=l.slice(o[0].length)||l),a.push(i=[])),r=!1,(o=gt.exec(l))&&(r=o.shift(),i.push({value:r,type:o[0].replace(dt," ")}),l=l.slice(r.length));for(u in L.filter)!(o=bt[u].exec(l))||c[u]&&!(o=c[u](o))||(r=o.shift(),i.push({value:r,type:u,matches:o}),l=l.slice(r.length));if(!r)break}return t?l.length:l?n.error(e):K(e,a).slice(0)}function m(e){for(var t=0,n=e.length,r="";n>t;t++)r+=e[t].value;return r}function y(e,t,n){var r=t.dir,o=n&&"parentNode"===r,i=X++;return t.first?function(t,n,i){for(;t=t[r];)if(1===t.nodeType||o)return e(t,n,i)}:function(t,n,u){var l,a,c,s=V+" "+i;if(u){for(;t=t[r];)if((1===t.nodeType||o)&&e(t,n,u))return!0}else for(;t=t[r];)if(1===t.nodeType||o)if(c=t[G]||(t[G]={}),(a=c[r])&&a[0]===s){if((l=a[1])===!0||l===D)return l===!0}else if(a=c[r]=[s],a[1]=e(t,n,u)||D,a[1]===!0)return!0}}function v(e){return e.length>1?function(t,n,r){for(var o=e.length;o--;)if(!e[o](t,n,r))return!1;return!0}:e[0]}function N(e,t,n,r,o){for(var i,u=[],l=0,a=e.length,c=null!=t;a>l;l++)(i=e[l])&&(!n||n(i,r,o))&&(u.push(i),c&&t.push(l));return u}function b(e,t,n,r,o,u){return r&&!r[G]&&(r=b(r)),o&&!o[G]&&(o=b(o,u)),i(function(i,u,l,a){var c,s,f,p=[],d=[],h=u.length,g=i||E(t||"*",l.nodeType?[l]:l,[]),m=!e||!i&&t?g:N(g,p,e,l,a),y=n?o||(i?e:h||r)?[]:u:m;if(n&&n(m,y,l,a),r)for(c=N(y,d),r(c,[],l,a),s=c.length;s--;)(f=c[s])&&(y[d[s]]=!(m[d[s]]=f));if(i){if(o||e){if(o){for(c=[],s=y.length;s--;)(f=y[s])&&c.push(m[s]=f);o(null,y=[],c,a)}for(s=y.length;s--;)(f=y[s])&&(c=o?ut.call(i,f):p[s])>-1&&(i[c]=!(u[c]=f))}}else y=N(y===u?y.splice(h,y.length):y),o?o(null,u,y,a):ot.apply(u,y)})}function x(e){for(var t,n,r,o=e.length,i=L.relative[e[0].type],u=i||L.relative[" "],l=i?1:0,a=y(function(e){return e===t},u,!0),c=y(function(e){return ut.call(t,e)>-1},u,!0),s=[function(e,n,r){return!i&&(r||n!==P)||((t=n).nodeType?a(e,n,r):c(e,n,r))}];o>l;l++)if(n=L.relative[e[l].type])s=[y(v(s),n)];else{if(n=L.filter[e[l].type].apply(null,e[l].matches),n[G]){for(r=++l;o>r&&!L.relative[e[r].type];r++);return b(l>1&&v(s),l>1&&m(e.slice(0,l-1).concat({value:" "===e[l-2].type?"*":""})).replace(dt,"$1"),n,r>l&&x(e.slice(l,r)),o>r&&x(e=e.slice(r)),o>r&&m(e))}s.push(n)}return v(s)}function C(e,t){var r=0,o=t.length>0,u=e.length>0,l=function(i,l,a,c,s){var f,p,d,h=[],g=0,m="0",y=i&&[],v=null!=s,b=P,x=i||u&&L.find.TAG("*",s&&l.parentNode||l),C=V+=null==b?1:Math.random()||.1;for(v&&(P=l!==H&&l,D=r);null!=(f=x[m]);m++){if(u&&f){for(p=0;d=e[p++];)if(d(f,l,a)){c.push(f);break}v&&(V=C,D=++r)}o&&((f=!d&&f)&&g--,i&&y.push(f))}if(g+=m,o&&m!==g){for(p=0;d=t[p++];)d(y,h,l,a);if(i){if(g>0)for(;m--;)y[m]||h[m]||(h[m]=nt.call(c));h=N(h)}ot.apply(c,h),v&&!i&&h.length>0&&g+t.length>1&&n.uniqueSort(c)}return v&&(V=C,P=b),y};return o?i(l):l}function E(e,t,r){for(var o=0,i=t.length;i>o;o++)n(e,t[o],r);return r}function w(e,t,n,r){var o,i,u,l,a,c=g(e);if(!r&&1===c.length){if(i=c[0]=c[0].slice(0),i.length>2&&"ID"===(u=i[0]).type&&S.getById&&9===t.nodeType&&O&&L.relative[i[1].type]){if(t=(L.find.ID(u.matches[0].replace(At,St),t)||[])[0],!t)return n;e=e.slice(i.shift().value.length)}for(o=bt.needsContext.test(e)?0:i.length;o--&&(u=i[o],!L.relative[l=u.type]);)if((a=L.find[l])&&(r=a(u.matches[0].replace(At,St),mt.test(i[0].type)&&t.parentNode||t))){if(i.splice(o,1),e=r.length&&m(i),!e)return ot.apply(n,r),n;break}}return R(e,c)(r,t,!O,n,mt.test(e)),n}function T(){}var A,S,D,L,B,I,R,P,$,q,H,M,O,k,F,z,j,G="sizzle"+-new Date,U=e.document,V=0,X=0,J=o(),K=o(),Q=o(),W=!1,Y=function(){return 0},Z=typeof t,_=1<<31,et={}.hasOwnProperty,tt=[],nt=tt.pop,rt=tt.push,ot=tt.push,it=tt.slice,ut=tt.indexOf||function(e){for(var t=0,n=this.length;n>t;t++)if(this[t]===e)return t;return-1},lt="checked|selected|async|autofocus|autoplay|controls|defer|disabled|hidden|ismap|loop|multiple|open|readonly|required|scoped",at="[\\x20\\t\\r\\n\\f]",ct="(?:\\\\.|[\\w-]|[^\\x00-\\xa0])+",st=ct.replace("w","w#"),ft="\\["+at+"*("+ct+")"+at+"*(?:([*^$|!~]?=)"+at+"*(?:(['\"])((?:\\\\.|[^\\\\])*?)\\3|("+st+")|)|)"+at+"*\\]",pt=":("+ct+")(?:\\(((['\"])((?:\\\\.|[^\\\\])*?)\\3|((?:\\\\.|[^\\\\()[\\]]|"+ft.replace(3,8)+")*)|.*)\\)|)",dt=RegExp("^"+at+"+|((?:^|[^\\\\])(?:\\\\.)*)"+at+"+$","g"),ht=RegExp("^"+at+"*,"+at+"*"),gt=RegExp("^"+at+"*([>+~]|"+at+")"+at+"*"),mt=RegExp(at+"*[+~]"),yt=RegExp("="+at+"*([^\\]'\"]*)"+at+"*\\]","g"),vt=RegExp(pt),Nt=RegExp("^"+st+"$"),bt={ID:RegExp("^#("+ct+")"),CLASS:RegExp("^\\.("+ct+")"),TAG:RegExp("^("+ct.replace("w","w*")+")"),ATTR:RegExp("^"+ft),PSEUDO:RegExp("^"+pt),CHILD:RegExp("^:(only|first|last|nth|nth-last)-(child|of-type)(?:\\("+at+"*(even|odd|(([+-]|)(\\d*)n|)"+at+"*(?:([+-]|)"+at+"*(\\d+)|))"+at+"*\\)|)","i"),bool:RegExp("^(?:"+lt+")$","i"),needsContext:RegExp("^"+at+"*[>+~]|:(even|odd|eq|gt|lt|nth|first|last)(?:\\("+at+"*((?:-\\d)?\\d*)"+at+"*\\)|)(?=[^-]|$)","i")},xt=/^[^{]+\{\s*\[native \w/,Ct=/^(?:#([\w-]+)|(\w+)|\.([\w-]+))$/,Et=/^(?:input|select|textarea|button)$/i,wt=/^h\d$/i,Tt=/'|\\/g,At=RegExp("\\\\([\\da-f]{1,6}"+at+"?|("+at+")|.)","ig"),St=function(e,t,n){var r="0x"+t-65536;return r!==r||n?t:0>r?String.fromCharCode(r+65536):String.fromCharCode(55296|r>>10,56320|1023&r)};try{ot.apply(tt=it.call(U.childNodes),U.childNodes),tt[U.childNodes.length].nodeType}catch(Dt){ot={apply:tt.length?function(e,t){rt.apply(e,it.call(t))}:function(e,t){for(var n=e.length,r=0;e[n++]=t[r++];);e.length=n-1}}}I=n.isXML=function(e){var t=e&&(e.ownerDocument||e).documentElement;return t?"HTML"!==t.nodeName:!1},S=n.support={},q=n.setDocument=function(e){var n=e?e.ownerDocument||e:U;return n!==H&&9===n.nodeType&&n.documentElement?(H=n,M=n.documentElement,O=!I(n),S.attributes=u(function(e){return e.innerHTML="<a href='#'></a>",l("type|href|height|width",c,"#"===e.firstChild.getAttribute("href")),l(lt,a,null==e.getAttribute("disabled")),e.className="i",!e.getAttribute("className")}),S.input=u(function(e){return e.innerHTML="<input>",e.firstChild.setAttribute("value",""),""===e.firstChild.getAttribute("value")}),l("value",s,S.attributes&&S.input),S.getElementsByTagName=u(function(e){return e.appendChild(n.createComment("")),!e.getElementsByTagName("*").length}),S.getElementsByClassName=u(function(e){return e.innerHTML="<div class='a'></div><div class='a i'></div>",e.firstChild.className="i",2===e.getElementsByClassName("i").length}),S.getById=u(function(e){return M.appendChild(e).id=G,!n.getElementsByName||!n.getElementsByName(G).length}),S.getById?(L.find.ID=function(e,t){if(typeof t.getElementById!==Z&&O){var n=t.getElementById(e);return n&&n.parentNode?[n]:[]}},L.filter.ID=function(e){var t=e.replace(At,St);return function(e){return e.getAttribute("id")===t}}):(delete L.find.ID,L.filter.ID=function(e){var t=e.replace(At,St);return function(e){var n=typeof e.getAttributeNode!==Z&&e.getAttributeNode("id");return n&&n.value===t}}),L.find.TAG=S.getElementsByTagName?function(e,n){return typeof n.getElementsByTagName!==Z?n.getElementsByTagName(e):t}:function(e,t){var n,r=[],o=0,i=t.getElementsByTagName(e);if("*"===e){for(;n=i[o++];)1===n.nodeType&&r.push(n);return r}return i},L.find.CLASS=S.getElementsByClassName&&function(e,n){return typeof n.getElementsByClassName!==Z&&O?n.getElementsByClassName(e):t},F=[],k=[],(S.qsa=r(n.querySelectorAll))&&(u(function(e){e.innerHTML="<select><option selected=''></option></select>",e.querySelectorAll("[selected]").length||k.push("\\["+at+"*(?:value|"+lt+")"),e.querySelectorAll(":checked").length||k.push(":checked")}),u(function(e){var t=n.createElement("input");t.setAttribute("type","hidden"),e.appendChild(t).setAttribute("t",""),e.querySelectorAll("[t^='']").length&&k.push("[*^$]="+at+"*(?:''|\"\")"),e.querySelectorAll(":enabled").length||k.push(":enabled",":disabled"),e.querySelectorAll("*,:x"),k.push(",.*:")})),(S.matchesSelector=r(z=M.webkitMatchesSelector||M.mozMatchesSelector||M.oMatchesSelector||M.msMatchesSelector))&&u(function(e){S.disconnectedMatch=z.call(e,"div"),z.call(e,"[s!='']:x"),F.push("!=",pt)}),k=k.length&&RegExp(k.join("|")),F=F.length&&RegExp(F.join("|")),j=r(M.contains)||M.compareDocumentPosition?function(e,t){var n=9===e.nodeType?e.documentElement:e,r=t&&t.parentNode;return e===r||!(!r||1!==r.nodeType||!(n.contains?n.contains(r):e.compareDocumentPosition&&16&e.compareDocumentPosition(r)))}:function(e,t){if(t)for(;t=t.parentNode;)if(t===e)return!0;return!1},S.sortDetached=u(function(e){return 1&e.compareDocumentPosition(n.createElement("div"))}),Y=M.compareDocumentPosition?function(e,t){if(e===t)return W=!0,0;var r=t.compareDocumentPosition&&e.compareDocumentPosition&&e.compareDocumentPosition(t);return r?1&r||!S.sortDetached&&t.compareDocumentPosition(e)===r?e===n||j(U,e)?-1:t===n||j(U,t)?1:$?ut.call($,e)-ut.call($,t):0:4&r?-1:1:e.compareDocumentPosition?-1:1}:function(e,t){var r,o=0,i=e.parentNode,u=t.parentNode,l=[e],a=[t];if(e===t)return W=!0,0;if(!i||!u)return e===n?-1:t===n?1:i?-1:u?1:$?ut.call($,e)-ut.call($,t):0;if(i===u)return f(e,t);for(r=e;r=r.parentNode;)l.unshift(r);for(r=t;r=r.parentNode;)a.unshift(r);for(;l[o]===a[o];)o++;return o?f(l[o],a[o]):l[o]===U?-1:a[o]===U?1:0},n):H},n.matches=function(e,t){return n(e,null,null,t)},n.matchesSelector=function(e,t){if((e.ownerDocument||e)!==H&&q(e),t=t.replace(yt,"='$1']"),!(!S.matchesSelector||!O||F&&F.test(t)||k&&k.test(t)))try{var r=z.call(e,t);if(r||S.disconnectedMatch||e.document&&11!==e.document.nodeType)return r}catch(o){}return n(t,H,null,[e]).length>0},n.contains=function(e,t){return(e.ownerDocument||e)!==H&&q(e),j(e,t)},n.attr=function(e,n){(e.ownerDocument||e)!==H&&q(e);var r=L.attrHandle[n.toLowerCase()],o=r&&et.call(L.attrHandle,n.toLowerCase())?r(e,n,!O):t;return o===t?S.attributes||!O?e.getAttribute(n):(o=e.getAttributeNode(n))&&o.specified?o.value:null:o},n.error=function(e){throw Error("Syntax error, unrecognized expression: "+e)},n.uniqueSort=function(e){var t,n=[],r=0,o=0;if(W=!S.detectDuplicates,$=!S.sortStable&&e.slice(0),e.sort(Y),W){for(;t=e[o++];)t===e[o]&&(r=n.push(o));for(;r--;)e.splice(n[r],1)}return e},B=n.getText=function(e){var t,n="",r=0,o=e.nodeType;if(o){if(1===o||9===o||11===o){if("string"==typeof e.textContent)return e.textContent;for(e=e.firstChild;e;e=e.nextSibling)n+=B(e)}else if(3===o||4===o)return e.nodeValue}else for(;t=e[r];r++)n+=B(t);return n},L=n.selectors={cacheLength:50,createPseudo:i,match:bt,attrHandle:{},find:{},relative:{">":{dir:"parentNode",first:!0}," ":{dir:"parentNode"},"+":{dir:"previousSibling",first:!0},"~":{dir:"previousSibling"}},preFilter:{ATTR:function(e){return e[1]=e[1].replace(At,St),e[3]=(e[4]||e[5]||"").replace(At,St),"~="===e[2]&&(e[3]=" "+e[3]+" "),e.slice(0,4)},CHILD:function(e){return e[1]=e[1].toLowerCase(),"nth"===e[1].slice(0,3)?(e[3]||n.error(e[0]),e[4]=+(e[4]?e[5]+(e[6]||1):2*("even"===e[3]||"odd"===e[3])),e[5]=+(e[7]+e[8]||"odd"===e[3])):e[3]&&n.error(e[0]),e},PSEUDO:function(e){var n,r=!e[5]&&e[2];return bt.CHILD.test(e[0])?null:(e[3]&&e[4]!==t?e[2]=e[4]:r&&vt.test(r)&&(n=g(r,!0))&&(n=r.indexOf(")",r.length-n)-r.length)&&(e[0]=e[0].slice(0,n),e[2]=r.slice(0,n)),e.slice(0,3))}},filter:{TAG:function(e){var t=e.replace(At,St).toLowerCase();return"*"===e?function(){return!0}:function(e){return e.nodeName&&e.nodeName.toLowerCase()===t}},CLASS:function(e){var t=J[e+" "];return t||(t=RegExp("(^|"+at+")"+e+"("+at+"|$)"))&&J(e,function(e){return t.test("string"==typeof e.className&&e.className||typeof e.getAttribute!==Z&&e.getAttribute("class")||"")})},ATTR:function(e,t,r){return function(o){var i=n.attr(o,e);return null==i?"!="===t:t?(i+="","="===t?i===r:"!="===t?i!==r:"^="===t?r&&0===i.indexOf(r):"*="===t?r&&i.indexOf(r)>-1:"$="===t?r&&i.slice(-r.length)===r:"~="===t?(" "+i+" ").indexOf(r)>-1:"|="===t?i===r||i.slice(0,r.length+1)===r+"-":!1):!0}},CHILD:function(e,t,n,r,o){var i="nth"!==e.slice(0,3),u="last"!==e.slice(-4),l="of-type"===t;return 1===r&&0===o?function(e){return!!e.parentNode}:function(t,n,a){var c,s,f,p,d,h,g=i!==u?"nextSibling":"previousSibling",m=t.parentNode,y=l&&t.nodeName.toLowerCase(),v=!a&&!l;if(m){if(i){for(;g;){for(f=t;f=f[g];)if(l?f.nodeName.toLowerCase()===y:1===f.nodeType)return!1;h=g="only"===e&&!h&&"nextSibling"}return!0}if(h=[u?m.firstChild:m.lastChild],u&&v){for(s=m[G]||(m[G]={}),c=s[e]||[],d=c[0]===V&&c[1],p=c[0]===V&&c[2],f=d&&m.childNodes[d];f=++d&&f&&f[g]||(p=d=0)||h.pop();)if(1===f.nodeType&&++p&&f===t){s[e]=[V,d,p];break}}else if(v&&(c=(t[G]||(t[G]={}))[e])&&c[0]===V)p=c[1];else for(;(f=++d&&f&&f[g]||(p=d=0)||h.pop())&&((l?f.nodeName.toLowerCase()!==y:1!==f.nodeType)||!++p||(v&&((f[G]||(f[G]={}))[e]=[V,p]),f!==t)););return p-=o,p===r||0===p%r&&p/r>=0}}},PSEUDO:function(e,t){var r,o=L.pseudos[e]||L.setFilters[e.toLowerCase()]||n.error("unsupported pseudo: "+e);return o[G]?o(t):o.length>1?(r=[e,e,"",t],L.setFilters.hasOwnProperty(e.toLowerCase())?i(function(e,n){for(var r,i=o(e,t),u=i.length;u--;)r=ut.call(e,i[u]),e[r]=!(n[r]=i[u])}):function(e){return o(e,0,r)}):o}},pseudos:{not:i(function(e){var t=[],n=[],r=R(e.replace(dt,"$1"));return r[G]?i(function(e,t,n,o){for(var i,u=r(e,null,o,[]),l=e.length;l--;)(i=u[l])&&(e[l]=!(t[l]=i))}):function(e,o,i){return t[0]=e,r(t,null,i,n),!n.pop()}}),has:i(function(e){return function(t){return n(e,t).length>0}}),contains:i(function(e){return function(t){return(t.textContent||t.innerText||B(t)).indexOf(e)>-1}}),lang:i(function(e){return Nt.test(e||"")||n.error("unsupported lang: "+e),e=e.replace(At,St).toLowerCase(),function(t){var n;do if(n=O?t.lang:t.getAttribute("xml:lang")||t.getAttribute("lang"))return n=n.toLowerCase(),n===e||0===n.indexOf(e+"-");while((t=t.parentNode)&&1===t.nodeType);return!1}}),target:function(t){var n=e.location&&e.location.hash;return n&&n.slice(1)===t.id},root:function(e){return e===M},focus:function(e){return e===H.activeElement&&(!H.hasFocus||H.hasFocus())&&!!(e.type||e.href||~e.tabIndex)},enabled:function(e){return e.disabled===!1},disabled:function(e){return e.disabled===!0},checked:function(e){var t=e.nodeName.toLowerCase();return"input"===t&&!!e.checked||"option"===t&&!!e.selected},selected:function(e){return e.parentNode&&e.parentNode.selectedIndex,e.selected===!0},empty:function(e){for(e=e.firstChild;e;e=e.nextSibling)if(e.nodeName>"@"||3===e.nodeType||4===e.nodeType)return!1;return!0},parent:function(e){return!L.pseudos.empty(e)},header:function(e){return wt.test(e.nodeName)},input:function(e){return Et.test(e.nodeName)},button:function(e){var t=e.nodeName.toLowerCase();return"input"===t&&"button"===e.type||"button"===t},text:function(e){var t;return"input"===e.nodeName.toLowerCase()&&"text"===e.type&&(null==(t=e.getAttribute("type"))||t.toLowerCase()===e.type)},first:h(function(){return[0]}),last:h(function(e,t){return[t-1]}),eq:h(function(e,t,n){return[0>n?n+t:n]}),even:h(function(e,t){for(var n=0;t>n;n+=2)e.push(n);return e}),odd:h(function(e,t){for(var n=1;t>n;n+=2)e.push(n);return e}),lt:h(function(e,t,n){for(var r=0>n?n+t:n;--r>=0;)e.push(r);return e}),gt:h(function(e,t,n){for(var r=0>n?n+t:n;t>++r;)e.push(r);return e})}};for(A in{radio:!0,checkbox:!0,file:!0,password:!0,image:!0})L.pseudos[A]=p(A);for(A in{submit:!0,reset:!0})L.pseudos[A]=d(A);R=n.compile=function(e,t){var n,r=[],o=[],i=Q[e+" "];if(!i){for(t||(t=g(e)),n=t.length;n--;)i=x(t[n]),i[G]?r.push(i):o.push(i);i=Q(e,C(o,r))}return i},L.pseudos.nth=L.pseudos.eq,T.prototype=L.filters=L.pseudos,L.setFilters=new T,S.sortStable=G.split("").sort(Y).join("")===G,q(),[0,0].sort(Y),S.detectDuplicates=W,"function"==typeof define&&define.amd?define(function(){return n}):e.Sizzle=n})(window);
	</script>

	<script type="text/javascript">
window.onload = function() {
	var codeBlocks = Sizzle('pre'),
		callStackItems = Sizzle('.call-stack-item');

	// highlight code blocks
	for (var i = 0, imax = codeBlocks.length; i < imax; ++i) {
		hljs.highlightBlock(codeBlocks[i], '    ');
	}

	// code block hover line
	document.onmousemove = function(e) {
		var event = e || window.event,
			clientY = event.clientY,
			lineFound = false,
			hoverLines = Sizzle('.hover-line');

		for (var i = 0, imax = codeBlocks.length - 1; i < imax; ++i) {
			var lines = codeBlocks[i].getClientRects();
			for (var j = 0, jmax = lines.length; j < jmax; ++j) {
				if (clientY >= lines[j].top && clientY <= lines[j].bottom) {
					lineFound = true;
					break;
				}
			}
			if (lineFound) {
				break;
			}
		}

		for (var k = 0, kmax = hoverLines.length; k < kmax; ++k) {
			hoverLines[k].className = 'hover-line';
		}
		if (lineFound) {
			var line = Sizzle('.call-stack-item:eq(' + i + ') .hover-line:eq(' + j + ')')[0];
			if (line) {
				line.className = 'hover-line hover';
			}
		}
	};

	var refreshCallStackItemCode = function(callStackItem) {
		if (!Sizzle('pre', callStackItem)[0]) {
			return;
		}
		var top = callStackItem.offsetTop - window.pageYOffset,
			lines = Sizzle('pre', callStackItem)[0].getClientRects(),
			lineNumbers = Sizzle('.lines-item', callStackItem),
			errorLine = Sizzle('.error-line', callStackItem)[0],
			hoverLines = Sizzle('.hover-line', callStackItem);
		for (var i = 0, imax = lines.length; i < imax; ++i) {
			if (!lineNumbers[i]) {
				continue;
			}
			lineNumbers[i].style.top = parseInt(lines[i].top - top) + 'px';
			hoverLines[i].style.top = parseInt(lines[i].top - top - 3) + 'px';
			hoverLines[i].style.height = parseInt(lines[i].bottom - lines[i].top + 6) + 'px';
			if (parseInt(callStackItem.getAttribute('data-line')) == i) {
				errorLine.style.top = parseInt(lines[i].top - top - 3) + 'px';
				errorLine.style.height = parseInt(lines[i].bottom - lines[i].top + 6) + 'px';
			}
		}
	};

	for (var i = 0, imax = callStackItems.length; i < imax; ++i) {
		refreshCallStackItemCode(callStackItems[i]);

		// toggle code block visibility
		Sizzle('.element-wrap', callStackItems[i])[0].addEventListener('click', function() {
			var callStackItem = this.parentNode,
				code = Sizzle('.code-wrap', callStackItem)[0];
			code.style.display = window.getComputedStyle(code).display == 'block' ? 'none' : 'block';
			refreshCallStackItemCode(callStackItem);
		});
	}
};
	</script>
	<?php if (method_exists($this, 'endBody')) $this->endBody(); // to allow injecting code into body (mostly by Yii Debug Toolbar) ?>
</body>

</html>
<?php if (method_exists($this, 'endPage')) $this->endPage(); ?>
