# webforge-common

[![Build Status](https://travis-ci.org/webforge-labs/webforge-common.png)](https://travis-ci.org/webforge-labs/webforge-common) [![Coverage Status](https://coveralls.io/repos/webforge-labs/webforge-common/badge.png?branch=master)](https://coveralls.io/r/webforge-labs/webforge-common?branch=master) [![Latest Stable Version](https://poser.pugx.org/webforge/common/version.png)](https://packagist.org/packages/webforge/common)

This small library covers all common code for the webforge framework, to have a small set from classes that are sufficient to start off a litte project.
You can use it indepently from every webforge installation - allthough you will lose some features.

## whats included

- DateTime and Time classes for an advanced (and more concise) interface for the PHP::DateTime classes
- A full functional File and Directory implementation that abstracts a lot of PHP file funtions
  - supports nearly every way to write a file path (and convert): 
    - windows D:\www\something.php
    - unix /some/path
    - wrappers: vfs://some/file/topath
    - windows cygin /cydrive/d/www/someting.php
    - windows shares \\\\psc-laptop\shared\www\something.php
    - unix style windows paths (sublime and others): /D/www/something.php
  - copy dirs and files recursively
  - find files recursively
- Commonly used Exceptions with better semantics and debug output (FileNotFound, NotImplemented, Deprecated)
- Some simple Utils to debug and dump variables
- A bunch of useful String and Array functions
- System: use the CLI with better abstractions for you tests and build better, stable CLI applications 
- Convert read and lint JSON
- Common used value Objects (Url, Command, Macro)

## usage 

  - [Dir and File API](docs/dir-and-file.md)
  - [JSON](docs/json.md)

## installation

Use [Composer](http://getcomposer.org) to install.
```
composer require webforge/common:@stable
```

## testing

to run the tests use:
```
phpunit
```

## license

Copyright (c) 2013 ps-webforge.com

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
