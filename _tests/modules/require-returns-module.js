testCases(test,

	function shouldMakeRequireAvailableInModuleScope() {
		assert.that(require, isA(Function));
	},

	function shouldReturnAnObjectFromRequireEvenIfTheModuleIsEmpty() {
		assert.that(require('empty-module'), isA(Object));
	},

	function shouldReturnTheExportsObjectOfAModuleThatProvidesOne() {
		assert.that(require('some-module'), isA(Object));
		assert.that(require('some-module').getSomeProperty, isA(Function));
	},

	function shouldReturnTheSameInstanceForTheSameModule() {
		assert.that(require('some-module'), eq(require('some-module')));
	},

	function shouldReturnAReferenceToTheModuleExportsObjectIfRequiringSelf() {
		assert.that(exports, isA(Object));
		assert.that(require('require-returns-module'), eq(exports));
	}
);