(function(modules) {

var require = function(name) {
	var module, exports, req, func;

	if (!modules[name].hasOwnProperty('exports')) {
		exports = {};
		module = {'id': name, 'exports': exports};
		func = modules[name];
		modules[name] = {'exports': exports};

		req = function(n) {
			if (n !== name) {
				return require(n);
			}

			return exports;
		};

		req.main = require.main;
		func.call(module, req, exports, module);
	}

	return modules[name].exports;
};

{{output}}

require('{{main}}');

}({}));