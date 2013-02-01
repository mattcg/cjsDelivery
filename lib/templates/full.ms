(function(modules) {

var require = function(identifier) {
	var module, exports, closure;

	if (!modules[identifier].hasOwnProperty('exports')) {
		exports = {};
		module = {id: identifier, exports: exports};
		closure = modules[identifier];
		modules[identifier] = module;
		closure.call(module, require, exports, module);
	}

	return modules[identifier].exports;
};

{{output}}

{{globals}}
	
{{main}}
}({}));