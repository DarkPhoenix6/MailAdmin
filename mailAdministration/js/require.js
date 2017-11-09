/**
 * @author Chris
 */
/**
	Worked this code out of stamat's gist. It had several issues back then.
	It's mechanism is very simple. Load it in the html file first, and then
	load your main script after that. Doing that you're able to create your
	client-side app just requiring your files inside each other in a modular
	fashion. It's awsome !!! ^^
 */

var _rmod = _rmod || {}; 	//require module namespace
_rmod.LOADED = false;
_rmod.on_ready_fn_stack = [];
_rmod.libpath = ''; 		//uri of your project folder here, or this script named 'require.js' at the root folder
_rmod.imported = {};
_rmod.loading = {
	scripts: {},
	length: 0
};

String.prototype.endsWith = function(suffix) {
    return this.indexOf(suffix, this.length - suffix.length) !== -1;
};
 
_rmod.findScriptPath = function(script_name) {
	var script_elems = document.getElementsByTagName('script');
	for (var i = 0; i < script_elems.length; i++) {
		if (script_elems[i].src.endsWith(script_name)) {
			var href = window.location.href;
			href = href.substring(0, href.lastIndexOf('/'));
			var url = script_elems[i].src.substring(0, script_elems[i].src.length-script_name.length);

			return url;
		}
	}

	return '';
};
 
_rmod.libpath = _rmod.libpath === '' ? _rmod.findScriptPath('require.js') : _rmod.libpath;

_rmod.injectScript = function(script_name, uri, callback, prepare, async) {
	if(prepare) prepare(script_name, uri);
	
	var script_elem = document.createElement('script');
	script_elem.src = uri;
	script_elem.defer = false;
	script_elem.async = async ? async : false;
	
	if(callback) script_elem.onload = function() { callback(script_name, uri); };

	document.getElementsByTagName('head')[0].appendChild(script_elem);
};
 
_rmod.requirePrepare = function(script_name, uri) {
	_rmod.loading.scripts[script_name] = uri;
	_rmod.loading.length++;
};
 
_rmod.requireCallback = function(script_name, uri) {
	delete _rmod.loading.scripts[script_name];
	_rmod.loading.length--;
	_rmod.imported[script_name] = uri;
 
	if(_rmod.loading.length == 0) _rmod.onReady();
};
 
_rmod.onReady = function() {
	if (!_rmod.LOADED) {
		for (var i = 0; i < _rmod.on_ready_fn_stack.length; i++) _rmod.on_ready_fn_stack[i]();
		_rmod.LOADED = true;
	}
};

_rmod.namespaceToUri = function(script_name, url) {
	var np = script_name.split('.');
	if (np[np.length-1] === '*') {
		np.pop();
		np.push('_all');
	} else if (np[np.length-1] === 'js') {
		np.pop();
	}

	if(!url) url = '';
	script_name = np.join('.');

	return url + np.join('/')+'.js';
};
 
var require = function(script_name, async) {
	var uri = '';
	if (script_name.indexOf('/') > -1) {
		uri = script_name;
		var lastSlash = uri.lastIndexOf('/');
		script_name = uri.substring(lastSlash+1, uri.length);
	} else {
		uri = _rmod.namespaceToUri(script_name, _rmod.libpath);
	}
 
	if (!_rmod.loading.scripts.hasOwnProperty(script_name)
	 && !_rmod.imported.hasOwnProperty(script_name)) {
		_rmod.injectScript(script_name, uri,
		_rmod.requireCallback,
		_rmod.requirePrepare, async);
	}
};
 
var ready = function(fn) {
	_rmod.on_ready_fn_stack.push(fn);
}; 