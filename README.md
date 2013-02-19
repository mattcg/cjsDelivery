# cjsDelivery #

[![Build Status](https://travis-ci.org/mattcg/cjsdelivery.png?branch=master)](https://travis-ci.org/mattcg/cjsdelivery)

## A CommonJS compiler written in PHP ##

cjsDelivery allows you to deliver [CommonJS-syntax](http://wiki.commonjs.org/wiki/Modules/1.1.1) JavaScript modules to clients as a **single file**. Any modules you add will have dependencies **resolved statically**. This typically means you only have to point cjsDelivery to your entry module and all dependencies will be magically resolved.

The output is designed to have as little overhead over your module code as possible. In all, only 13 short lines of code will be added by the compiler.

Features, in summary:

- `require` dependencies in your code and define APIs on `exports` like you do with node.js on the server
- compiles all your code, including dependencies, into a single file, ready for delivery to browsers
- point cjsDelivery at your bootstrap (startup) file to compile the entire application
- exclude and include sections of code from compilation using pragmas
- specify multiple include paths to avoid typing long require statements
- add your own globals

## Installation ##

Install globally by running this one-line command in your bash terminal:

```
bash <(curl -s https://raw.github.com/mattcg/cjsdelivery/go)
```

### Using composer ###

Get [composer](http://getcomposer.org/) and install cjsDelivery to your project using:

```
cd myproject/
echo -e '{\n\t"minimum-stability": "dev"\n}' > composer.json
composer require mattcg/cjsdelivery:0.1.0
```

## Usage ##

### Executable ###

The `bin/delivery` executable is provided for command-line use. Run the following example to compiled the bundled example `fruit` application:

```
delivery --main_module='./examples/fruit/modules/main'
```

### PHP API ###

Instances can be created using the provided factory function.

```PHP
require '../cjsdelivery/cjsDelivery.php';

$minifyidentifiers = false;
$globals  = array('utilities.js', 'globals.js');
$includes = array('../mycompany/javascript', '../othercompany/modules');
$delivery = cjsDelivery\create($minifyidentifiers, $includes, $globals);

$delivery->addModule('./path/to/module');
echo $delivery->getOutput();
```

Full PHP API documentation to come.

## Features ##

### Include paths ###

If you have many dependencies in folders external to your project, then it's worth setting an include path to avoid having long, absolute paths in your require statements. If your company's standard modules are in `projects/mycompany/javascript` and your project is in `projects/myproject`, then you can require a standard module using `require('standardmodule')` instead of `require('projects/mycompany/javascript')` by adding the include path `projects/mycompany/javascript`:

```
cd projects/myproject
delivery --main_module='./main' --include='../mycompany/javascript:../othercompany/modules'
```

Multiple paths can be specified in a colon-separated list.

#### For external components ####

Suppose that as part of your project build process, you use [bower](http://twitter.github.com/bower/) to install external components to a `components/` directory in your project:

```
cd myproject/lib/javascript
bower install
```

You could then add `myproject/lib/javascript/components` to your cjsDelivery include path.

#### For internal components ####

An include path can be useful even with internal dependencies. Suppose your project has the following directory structure:

```
- myproject
|- moduleA
|-|- version1
|-|- version2
|- moduleB
|-|- version1
```

If you want to avoid having to type `require('../../moduleB/version1')` from within `moduleA/version1/index.js` then you could set `myproject` to be an include path. Then you would type `require('moduleB/version1')`.

### Pragmas ###

Use pragmas to include or exclude pieces of code from the final output.

When passed to the `delivery` executable, the `-p` option will turn on the manager and any code contained between undefined pragmas will be 'compiled out'.

The bundled example module in `examples/fruit/modules/main.js` includes the following lines:

```
// ifdef BANANA
log.print(require('banana').message);
// endif BANANA
```

Run the following example command to compile the `fruit` application without the `banana` module:

```
delivery --main_module='./examples/fruit/modules/main' -p
```

Now try the opposite:

```
delivery --main_module='./examples/fruit/modules/main' -p='BANANA'
```

### Minified identifiers ###

By default, cjsDelivery will flatten the module tree internally, rewriting `path/to/module` as `module`, for example. In a production environment it makes sense to use non-mnemonic identifiers to save space. If enabled, cjsDelivery will rewrite `path/to/module` as `A`, `path/to/othermodule` as `B` and so on.

Try this example:

```
delivery --main_module='./examples/fruit/modules/main' --minify_identifiers
```

### Globals ###

You might have a `globals.js` or `utilities.js` file (or both!) as part of your project, each containing variables or helper functions that you want to have available across all modules. To save you having to `require` these in your other modules, you can compile them in as globals:

```
delivery --main_module='./examples/globals/main' -g 'examples/globals/utilities' -g 'examples/globals/globals'
```

Global files have `require` within their scope and are parsed for dependencies.

## How dependencies are resolved ##

Code is always parsed statically, meaning statements like `require(pathVariable + '/mymodule')` will not be handled. You should use only a string literal as the argument to `require`.

The `.js` extension [may not](http://wiki.commonjs.org/wiki/Modules/1.1.1#Module_Identifiers) be added to module paths in require statements.

The following algorithm is used when resolving the given path to a dependency:

1. if `path` does not start with `.` or `/`
    1. for each include path, append `path` and go to 2.
2. if a file is at `path`
    1. add the file at `path` to the list of dependencies
3. if a directory is at `path`
    1. check for for the file `index.js` in directory `path` and if positive, append `index.js` to path and go to 2.
    2. check `package.json` in path and if the `main` property exists set `path` to its value and go to 2.
    3. check for a file with the same as the directory and if positive, append to `path` and go to 2.
    4. check whether the directory only contains one file and if positive, append to `path` and go to 2.
4. throw an exception

## Credits and license ##

cjsDelivery is copyright © 2012 - [Matthew Caruana Galizia](http://twitter.com/mcaruanagalizia), licensed under an MIT license. CommonJS is copyright © 2009 - Kevin Dangoor and many CommonJS contributors, licensed under an MIT license.
