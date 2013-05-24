/*;

var lines = null;
var line = document.getElementById('code-highlighter')

var updateLines = function() {
	lines = document.getElementById('code').getClientRects();
};
updateLines();
window.onresize = updateLines;
window.onscroll = updateLines;

document.onmousemove = function(e) {
	var event = e || window.event;
	var x = event.clientX, y = event.clientY;
	for (var i = 0, max = lines.length; i < max; i++) {
		if (y > lines[i].top && y < lines[i].bottom) {
			line.style.height = parseInt(lines[i].bottom - lines[i].top + 1) + 'px';
			line.style.top = parseInt(lines[i].top) + 'px';
			break;
		}
	}
}
*/

window.onload = function() {
	var i, j, max, max2,
		codeBlocks = Sizzle('pre'),
		traceBackItems = Sizzle('.trace-back-item');

	// highlight code
	for (i = 0, max = codeBlocks.length; i < max; i++) {
		hljs.highlightBlock(codeBlocks[i], '    ');
	}

	// error lines
//	var updateErrorLines = function() {
//		for (i = 0, max = codeBlocks.length; i < max; i++) {
//			var lines = codeBlocks[i].getClientRects(),
//				errorLine = codeBlocks[i].getAttribute('data-error-line'),
//				top = 0;
//			if (errorLine > lines.length - 1) {
//				errorLine = lines.length - 1;
//			}
//			for (j = 0; j < errorLine; j++) {
//				top += lines[j].height;
//			}
//			Sizzle('.error-line', codeBlocks[i].parentNode.parentNode)[0].style.marginTop = top + 'px';
//		}
//	};
//	updateErrorLines();

	// toggle code block visibility of each trace back item
	for (i = 0, max = traceBackItems.length; i < max; i++) {
		Sizzle('.li-wrap', traceBackItems[i])[0].addEventListener('click', function() {
			var code = Sizzle('.code-wrap', this.parentNode)[0];
			code.style.display = window.getComputedStyle(code).display == 'block' ? 'none' : 'block';
		});
	}
};
