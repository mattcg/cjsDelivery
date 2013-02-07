var log = require('./log');

// ifdef BANANA
log.print(require('./banana').message);
// endif BANANA
log.print(require('./apple').message);
log.print(require('./quince').message);