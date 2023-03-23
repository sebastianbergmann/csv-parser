[![Latest Stable Version](https://poser.pugx.org/sebastian/csv-parser/v/stable.png)](https://packagist.org/packages/sebastian/csv-parser)
[![CI Status](https://github.com/sebastianbergmann/csv-parser/workflows/CI/badge.svg)](https://github.com/sebastianbergmann/csv-parser/actions)
[![Type Coverage](https://shepherd.dev/github/sebastianbergmann/csv-parser/coverage.svg)](https://shepherd.dev/github/sebastianbergmann/csv-parser)
[![codecov](https://codecov.io/gh/sebastianbergmann/csv-parser/branch/main/graph/badge.svg)](https://codecov.io/gh/sebastianbergmann/csv-parser)

# sebastian/csv-parser

Library for type-safe parsing of CSV files.

## Installation

You can add this library as a local, per-project dependency to your project using [Composer](https://getcomposer.org/):

```
composer require sebastian/csv-parser
```

If you only need this library during development, for instance to run your project's test suite, then you should add it as a development-time dependency:

```
composer require --dev sebastian/csv-parser
```

## Usage

#### `example.csv`

```csv
1,2,3,1,0
```

```php
<?php declare(strict_types=1);
use SebastianBergmann\CsvParser\Parser;
use SebastianBergmann\CsvParser\Schema;
use SebastianBergmann\CsvParser\ColumnDefinition;
use SebastianBergmann\CsvParser\Type;

$schema = Schema::from(
    [
        ColumnDefinition::from(1, 'a', Type::integer()),
        ColumnDefinition::from(2, 'b', Type::float()),
        ColumnDefinition::from(3, 'c', Type::string()),
        ColumnDefinition::from(4, 'd', Type::boolean()),
        ColumnDefinition::from(5, 'e', Type::boolean()),
    ]
);

$parser = new Parser;

foreach ($parser->parse('example.csv', $schema, false) as $row) {
    var_dump($row);
}
```

The example above shows how to use a `Schema` object to configure how a string from a line of comma-separated values is parsed into an array element:

* `1` refers to the position of the string in the line of comma-separated values
* `'a'` specifies the key for the value in the associative array that is generated for each row
* `Type::integer()` specifies the type that should be used to store the value in the associative array that is generated for each row

The following types are available:

* boolean (`Type::boolean()`; uses `(bool)` type cast)
* integer (`Type::integer()`; uses `(int)` type cast)
* float (`Type::float()`; uses `(float)` type cast)
* string (`Type::string()`)

The `Parser::parse()` method requires two arguments and accepts an optional third argument:

* The first argument, `$filename`, is the path to the CSV file that should be parsed
* The second argument, `$schema`, is the `Schema` object we discussed above
* The third argument, `$ignoreFirstLine` (default: `true`), controls whether the first line of the CSV file should be ignored

Running the example shown above prints the output shown below:

```
array(3) {
  ["a"]=>
  int(1)
  ["b"]=>
  float(2)
  ["c"]=>
  string(1) "3"
  ["d"]=>
  bool(true)
  ["e"]=>
  bool(false)
}
```
