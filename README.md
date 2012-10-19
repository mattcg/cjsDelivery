# cjsDelivery

Use this library to deliver CommonJS-syntax JavaScript modules to clients as a single file. Any modules you add manually will have dependencies resolved statically. This typically means you only have to point cjsDelivery to your entry module and all dependencies will be magically resolved.

Experimental features include support for shortening ('minifying') identifiers, a plugin architecture, enabling and disabling static code pragmas, output caching and some more.

The `bin/deliver` executable is provided for command-line use. Run the following example:

```
./bin/delivery -m 'examples/fruit/modules/main' --main_module='examples/fruit/modules/main'
```