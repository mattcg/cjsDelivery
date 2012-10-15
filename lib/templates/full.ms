(function(modules) {

var require = function(identifier) {
	var module, exports, req, func;

	if (!modules[identifier].hasOwnProperty('exports')) {
		exports = {};
		module = {'id': identifier, 'exports': exports};
		func = modules[identifier];
		modules[identifier] = {'exports': exports};

		req = function(n) {
			if (n !== identifier) {
				return require(n);
			}

			return exports;
		};

		req.main = require.main;
		func.call(module, req, exports, module);
	}

	return modules[identifier].exports;
};

{{output}}

require('{{main}}');

}({}));