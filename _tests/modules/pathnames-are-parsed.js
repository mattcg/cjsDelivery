testCases(test,

	function shouldParseRelativePathnames() {
		assert.that(require('app'), isA(Object));
		assert.that(require('../modules/app'), isA(Object));
	},

	function shouldAllowJsFileExtensionsInPaths() {
		assert.that(require('some-module.js'), isA(Object));
	},

	function shouldResolveDifferentPathStylesToTheSameModule() {
		assert.that(require('../modules/app'), eq(require('./app.js')));
		assert.that(require('some-module'), not(eq(require('some-folder/some-module'))));
	}
);