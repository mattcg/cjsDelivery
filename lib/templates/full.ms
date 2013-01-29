(function(modules) {

var require = function(identifier) {
	var module, exports, req, func;

	if (!modules[identifier].hasOwnProperty('exports')) {
		exports = {};
		module = {id: identifier, exports: exports};
		func = modules[identifier];

		req = function(n) {
			if (n !== identifier) {
				return require(n);
			}

			return module.exports;
		};

		req.main = require.main;
		modules[identifier] = {exports: func.call(module, req, exports, module)};
	}

	return modules[identifier].exports;
};

{{output}}

{{globals}}
	
{{main}}
}({}));