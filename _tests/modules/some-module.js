// This is an app module with getters and setters
var someProperty;

exports.setSomeProperty = function(value) {
	someProperty = value;
};

exports.getSomeProperty = function() {
	return someProperty;
};