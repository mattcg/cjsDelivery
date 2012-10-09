testCases(test,
	
	function shouldMakeModuleObjectAvailableInModuleScope() {
		assert.that(module, isA(Object));
	},

	function shouldSetIdPropertyOnModuleObject() {
		assert.that(module.id, eq('module-id-is-set'));
	}
);