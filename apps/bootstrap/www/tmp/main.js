window.onload = function() {
	var i, imax,
		codeBlocks = Sizzle('pre'),
		callStackItems = Sizzle('.call-stack-item');

	// highlight code blocks
	for (i = 0, imax = codeBlocks.length; i < imax; ++i) {
		hljs.highlightBlock(codeBlocks[i], '    ');
	}

	// code block hover line
	document.onmousemove = function(e) {
		var lines, i, imax, j, jmax, k, kmax,
			event = e || window.event,
			y = event.clientY,
			lineFound = false;
		for (i = 0, imax = codeBlocks.length; i < imax; ++i) {
			lines = codeBlocks[i].getClientRects();
			for (j = 0, jmax = lines.length; j < jmax; ++j) {
				if (y > lines[j].top && y < lines[j].bottom) {
					lineFound = true;
					break;
				}
			}
			if (lineFound) {
				break;
			}
		}
		var hoverLines = Sizzle('.hover-line');
		for (k = 0, kmax = hoverLines.length; k < kmax; ++k) {
			hoverLines[k].className = 'hover-line';
		}
		if (lineFound) {
			var line = Sizzle('.call-stack-item:eq(' + i + ') .hover-line:eq(' + j + ')');
			if (line[0]) {
				line[0].className = 'hover-line hover';
			}
		}
	}

	// toggle code block visibility
	for (i = 0, imax = callStackItems.length; i < imax; i++) {
		Sizzle('.element-wrap', callStackItems[i])[0].addEventListener('click', function() {
			var code = Sizzle('.code-wrap', this.parentNode)[0];
			code.style.display = window.getComputedStyle(code).display == 'block' ? 'none' : 'block';
		});
	}
};
