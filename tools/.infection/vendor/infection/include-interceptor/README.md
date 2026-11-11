[![Build Status](https://travis-ci.org/infection/include-interceptor.svg?branch=master)](https://travis-ci.org/infection/include-interceptor)
[![Coverage Status](https://coveralls.io/repos/github/infection/include-interceptor/badge.svg?branch=master)](https://coveralls.io/github/infection/include-interceptor?branch=master)

# Infection - Include Interceptor Stream Wrapper

It's a [Stream Wrapper](https://www.php.net/manual/en/book.stream.php) that wraps a [`file://`](https://www.php.net/manual/en/wrappers.file.php) protocol and allows overriding content of any (auto) loaded file, including files autoloaded by Composer package manager.

## Installation

```bash
composer require infection/include-interceptor
```

## How it works

If you want to replace the content of the file whenever it's loaded (by executing `include '/path/to/file.php`, by calling `file_get_contents('/path/to/file.php')`, etc.), you need to register `IncludeInterceptor` _before_ loading original file:

```php
use Infection\StreamWrapper\IncludeInterceptor;

IncludeInterceptor::intercept('/path/to/original_file.php', '/path/to/replacement_file.php');
IncludeInterceptor::enable();
```

After enabling `IncludeInterceptor`, content of the `replacement_file.php` will be loaded instead of content of the `original_file.php`.  

## Use cases

* This Stream Wrapper is used to replace the original file with the Mutant in [Infection](http://infection.github.io) Mutation Testing Framework
* The same approach is used in the [`dg/bypass-finals`](https://github.com/dg/bypass-finals/) package that allows to mock `final` classes, by overriding original content and removing `final` keyword in runtime

## Infection - Mutation Testing Framework

Please read documentation here: [infection.github.io](http://infection.github.io)

Twitter: [@infection_php](http://twitter.com/infection_php)
