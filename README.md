# cjsDelivery

Use this library to deliver [CommonJS-syntax](http://wiki.commonjs.org/wiki/Modules/1.1.1) JavaScript modules to clients as a single file. Any modules you add manually will have dependencies resolved statically. This typically means you only have to point cjsDelivery to your entry module and all dependencies will be magically resolved.

Experimental features include support for shortening ('minifying') identifiers, a plugin architecture, enabling and disabling static code pragmas, output caching and some more.

## Executable

The `bin/deliver` executable is provided for command-line use. Run the following example to compiled the bundled example `fruit` application:

```
./bin/delivery -m='examples/fruit/modules/main' --main_module='examples/fruit/modules/main'
```

## Pragmas

The pragma manager plugin is bundled with this package. Use it to include or exclude pieces of code from the final output.

When passed to the `delivery` executable, the `-p` option will turn on the manager and any code contained between undefined pragmas will be 'compiled out'.

The bundled example module in `examples/fruit/modules/main.js` includes the following lines:

```
// ifdef BANANA
log.print(require('banana').message);
// endif BANANA
```

Run the following example command to compile the `fruit` application without the `banana` module:

```
./bin/delivery -m='examples/fruit/modules/main' --main_module='examples/fruit/modules/main' -p
```

Now try the opposite:

```
./bin/delivery -m='examples/fruit/modules/main' --main_module='examples/fruit/modules/main' -p='BANANA'
```

## License

CommonJS is copyright © 2009 - Kevin Dangoor and many CommonJS contributors, licensed under an MIT license.
cjsDelivery is copyright © 2012 - Matthew Caruana Galizia, licensed under an MIT license.