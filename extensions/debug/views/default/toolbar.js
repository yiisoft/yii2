(function() {
	var ajax = function(url, settings) {
		var xhr = window.XMLHttpRequest ? new XMLHttpRequest() : new ActiveXObject('Microsoft.XMLHTTP');
		settings = settings || {};
		xhr.open(settings.method || 'GET', url, true);
		xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
		xhr.onreadystatechange = function(state) {
			if (xhr.readyState == 4) {
				if (xhr.status == 200 && settings.success) {
					settings.success(xhr);
				} else if (xhr.status != 200 && settings.error) {
					settings.error(xhr);
				}
			}
		};
		xhr.send(settings.data || '');
	};

	var e = document.getElementById('yii-debug-toolbar');
	if (e) {
		e.style.display = 'block';
		var url = e.getAttribute('data-url');
		ajax(url, {
			success: function(xhr) {
				var div = document.createElement('div');
				div.innerHTML = xhr.responseText;
				e.parentNode.replaceChild(div, e);
				if (window.localStorage) {
					var pref = localStorage.getItem('yii-debug-toolbar');
					if (pref == 'minimized') {
						document.getElementById('yii-debug-toolbar').style.display = 'none';
						document.getElementById('yii-debug-toolbar-min').style.display = 'block';
					}
				}
			},
			error: function(xhr) {
				e.innerHTML = xhr.responseText;
			}
		});
	}
})();
