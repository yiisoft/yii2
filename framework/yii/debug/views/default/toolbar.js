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
				e.innerHTML = xhr.responseText;
			},
			error: function(xhr) {
				e.innerHTML = xhr.responseText;
			}
		});
	}
})();
