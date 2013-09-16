# cjsDelivery #

[![Build Status](https://travis-ci.org/mattcg/cjsDelivery.png?branch=master)](https://travis-ci.org/mattcg/cjsDelivery)
[![Coverage Status](https://coveralls.io/repos/mattcg/cjsDelivery/badge.png?branch=master)](https://coveralls.io/r/mattcg/cjsDelivery?branch=master)

[![Latest Stable Version](https://poser.pugx.org/mattcg/cjsdelivery/v/stable.png)](https://packagist.org/packages/mattcg/cjsdelivery)

## A CommonJS compiler written in PHP ##

cjsDelivery allows you to deliver [CommonJS-syntax](http://wiki.commonjs.org/wiki/Modules/1.1.1) JavaScript modules to clients as a **single file**. Any modules you add will have dependencies **resolved statically**. This typically means you only have to point cjsDelivery to your entry module and all dependencies will be magically resolved.

The output is designed to have as little overhead over your module code as possible. In all, only 13 short lines of code will be added by the compiler.

1. [Installation](#installation)
    1. [Per-project install using composer](#per-project-install-using-composer)
2. [Usage](#usage)
    1. [On the command-line](#on-the-command-line)
    2. [From PHP](#from-php)
    3. [Symfony](#symfony)
3. [Features](#features)
    1. [Include paths](#include-paths)
        1. [For external components](#for-external-components)
        2. [For internal components](#for-internal-components)
    2. [Pragmas](#pragmas)
    3. [Minified identifiers](#minified-identifiers)
    4. [Globals](#globals)
4. [How dependencies are resolved](#how-dependencies-are-resolved)
5. [Changelog](#changelog)
6. [Credits and license](#credits-and-license)

## Installation ##

Install globally by running this one-line command in your bash terminal:

```bash
bash <(curl -s https://raw.github.com/mattcg/cjsdelivery/go/install)
```

### Per-project install using composer ###

Get [composer](http://getcomposer.org/) and install cjsDelivery to your project by adding it as a requirement to `composer.json`.

```bash
cd myproject/
touch composer.json
composer require mattcg/cjsdelivery:0.4.2
```

As cjsDelivery is [PSR-0](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-0.md) compatible, composer will automatically generate `vendor/autoload.php`, which you can `require` in your code to have the cjsDelivery classes autoloaded when they're needed.

## Usage ##

### On the command-line ###

The `bin/delivery` executable is provided for command-line use. Run the following example to compiled the bundled example `fruit` application:

```bash
delivery --main_module='./examples/fruit/modules/main'
```
For the full list of options, run `delivery -h`.

### From PHP ###

Instances can be created using the provided factory class.

```PHP
use MattCG\cjsDelivery as cjsDelivery;

require '/path/to/cjsDelivery.php';

$includes = array('../mycompany/javascript', '../othercompany/modules');
$delivery = cjsDelivery\Delivery::create(array('includes' => $includes));
$delivery->addModule('./path/to/module');
echo $delivery->getOutput();
```

The factory method accepts a single parameter, which is a hashmap of options:

- `minifyIdentifiers` (boolean, default `false`) to turn on identifier minification
- `sendSignals` (boolean, default `false`) to force the [signal manager](#signals) to be on
- `globals` (array) to add [global](#globals) modules
- `includes` (array) to add [include paths](#include-paths)
- `parsePragmas` (boolean, default `false`) to enable [pragma parsing](#pragmas)
- `pragmaFormat` (string) to specify the pragma format 
- `pragmas` (array) to specify enabled pragmas

### Symfony ###

Use [cjsDeliveryBundle](https://github.com/mattcg/cjsDeliveryBundle) instead.

## Features ##

### Include paths ###

If you have many dependencies in folders external to your project, then it's worth setting an include path to avoid having long, absolute paths in your require statements.

If your company's standard modules are in `/projects/mycompany/javascript` and your project is in `/projects/myproject`, then you can require a standard module using `require('standardmodule')` instead of `require('/projects/mycompany/javascript/standardmodule')` by adding the include path `/projects/mycompany/javascript`.

```bash
cd /projects/myproject
delivery --main_module='./main' --include='../mycompany/javascript:../othercompany/modules'
```

Multiple paths can be specified in a colon-separated list.

In PHP, include directory paths can be passed to the factory method in the options hashmap by setting the value of the `includes` key to an array of paths.

```PHP
$includes = array('../mycompany/javascript', '../othercompany/modules');

$delivery = cjsDelivery\Delivery::create(array('includes' => $includes));

$mainmodule = './main';
$delivery->addModule($mainmodule);
$delivery->setMainModule($mainmodule);
```

#### For external components ####

Suppose that as part of your project build process, you use [bower](http://twitter.github.com/bower/) to install external components to a `components/` directory in your project:

```bash
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

```JavaScript
// ifdef BANANA
log.print(require('banana').message);
// endif BANANA
```

Run the following example command to compile the `fruit` application without the `banana` module:

```bash
delivery --main_module='./examples/fruit/modules/main' -p
```

Now try the opposite:

```bash
delivery --main_module='./examples/fruit/modules/main' -p='BANANA'
```

In PHP, instantiate a `PragmaManager` and use it to turn pragmas on. By default, all pragmas are off unless explicitly set using `setPragma` or `setPragmas`, but changes can be undone using `unsetPragma`.

```PHP
$delivery = cjsDelivery\Delivery::create(array('sendSignals' => true));

$pragmamanager = new PragmaManager($delivery->getSignalManager(), $delivery->getDependencyResolver());
$pragmamanager->setPragma('BANANA');

$mainmodule = './examples/fruit/modules/main';
$delivery->addModule($mainmodule);
$delivery->setMainModule($mainmodule);
```

#### Signals ####

The `PragmaManager` uses signals sent by an [Arua.Signal](https://github.com/auraphp/Aura.Signal) signal manager to process code from added modules. Using pragmas will enable the signal manager even if `sendSignals` is set to `false` in the options hashmap.

### Minified identifiers ###

By default, cjsDelivery will flatten the module tree internally, rewriting `path/to/module` as `module`, for example. In a production environment it makes sense to use non-mnemonic identifiers to save space. If enabled, cjsDelivery will rewrite `path/to/module` as `A`, `path/to/othermodule` as `B` and so on.

Try this example:

```bash
delivery --main_module='./examples/fruit/modules/main' --minify_identifiers
```

In PHP, set `minifyIdentifiers` to `true` when instantiating using the factory class.

```PHP
$delivery = cjsDelivery\Delivery::create(array('minifyIdentifiers' => true));

$mainmodule = './examples/fruit/modules/main';
$delivery->addModule($mainmodule);
$delivery->setMainModule($mainmodule);
```

### Globals ###

You might have a `globals.js` or `utilities.js` file (or both!) as part of your project, each containing variables or helper functions that you want to have available across all modules. To save you having to `require` these in your other modules, you can compile them in as globals.

```bash
delivery --main_module='./examples/globals/main' -g 'examples/globals/utilities' -g 'examples/globals/globals'
```

Global files have `require` within their scope and are parsed for dependencies.

In PHP, global file paths can be passed to the factory method in the options hashmap by setting the value of the `globals` key to an array of paths.

```PHP
$globals = array('examples/globals/utilities', 'examples/globals/globals');

$delivery = cjsDelivery\Delivery::create(array('globals' => $globals));

$mainmodule = './examples/globals/main';
$delivery->addModule($mainmodule);
$delivery->setMainModule($mainmodule);
```

## How dependencies are resolved ##

Code is always parsed statically, meaning statements like `require(pathVariable + '/mymodule')` will not be handled. You should use only a string literal as the argument to `require`.

The `.js` or `.json` extensions [may not](http://wiki.commonjs.org/wiki/Modules/1.1.1#Module_Identifiers) be added to module paths in require statements. Doing so will result in an `E_USER_NOTICE` level error being triggered, although the error is not fatal and operation will continue.

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

## Changelog ##

Please see the [closed milestones](https://github.com/mattcg/cjsDelivery/issues/milestones?page=1&sort=due_date&state=closed).

## Credits and license ##

cjsDelivery is copyright © 2012 [Matthew Caruana Galizia](http://twitter.com/mcaruanagalizia), licensed under an [MIT license](http://mattcg.mit-license.org/).

CommonJS is copyright © 2009 - Kevin Dangoor and many CommonJS contributors, licensed under an [MIT license](http://www.commonjs.org/license/).
