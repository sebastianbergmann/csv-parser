[![No Maintenance Intended](https://unmaintained.tech/badge.svg)](https://unmaintained.tech/)
[![Latest Stable Version](https://poser.pugx.org/sebastian/csv-parser/v)](https://packagist.org/packages/sebastian/csv-parser)
[![CI Status](https://github.com/sebastianbergmann/csv-parser/workflows/CI/badge.svg)](https://github.com/sebastianbergmann/csv-parser/actions)
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
1,2,3,1,0,2023-03-24
```

```php
<?php declare(strict_types=1);
use SebastianBergmann\CsvParser\Parser;
use SebastianBergmann\CsvParser\Schema;
use SebastianBergmann\CsvParser\FieldDefinition;
use SebastianBergmann\CsvParser\Type;
use SebastianBergmann\CsvParser\ObjectMapper;

$schema = Schema::from(
    FieldDefinition::from(1, 'a', Type::integer()),
    FieldDefinition::from(2, 'b', Type::float()),
    FieldDefinition::from(3, 'c', Type::string()),
    FieldDefinition::from(4, 'd', Type::boolean()),
    FieldDefinition::from(5, 'e', Type::boolean()),
    FieldDefinition::from(6, 'f', Type::object(
        new class implements ObjectMapper
        {
            public function map(string $value): DateTimeImmutable
            {
                return new DateTimeImmutable($value);
            }
        }
    )),
);

$parser = new Parser;

foreach ($parser->parse('example.csv', $schema) as $record) {
    var_dump($record);
}
```

The example above shows how to use a `Schema` object to configure how a string from a line of comma-separated values is parsed into an array element:

* `1` refers to the position of the string in the line of comma-separated values
* `'a'` specifies the key for the value in the associative array that is generated for each record
* `Type::integer()` specifies the type that should be used to store the value in the associative array that is generated for each record

The following types are available:

* boolean (`Type::boolean()`; uses `(bool)` type cast)
* integer (`Type::integer()`; uses `(int)` type cast)
* float (`Type::float()`; uses `(float)` type cast)
* string (`Type::string()`)
* object (`Type::object($mapper)`; `$mapper` is an object that implements the `SebastianBergmann\CsvParser\ObjectMapper` interface)

The `Parser::parse()` method requires two arguments:

* The first argument, `$filename`, is the path to the CSV file that should be parsed
* The second argument, `$schema`, is the `Schema` object we discussed above

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
  ["f"]=>
  object(DateTimeImmutable)#1 (3) {
    ["date"]=>
    string(26) "2023-03-24 00:00:00.000000"
    ["timezone_type"]=>
    int(3)
    ["timezone"]=>
    string(13) "Europe/Berlin"
  }
}
```

The `$parser->ignoreFirstLine()` method can be used to configure the parser to ignore the first line of the CSV file.

The `$parser->setSeparator()` method can be used to configure the parser to use a separator different from the default `,`.

The `$parser->setEnclosure()` method can be used to configure the parser to use an enclosure different from the default `"`.

The `$parser->setEscape()` method can be used to configure the parser to use an escape different from the default `"`.

Please note that this is a [low maintenance project](https://github.com/sebastianbergmann/csv-parser/blob/main/.github/CONTRIBUTING.md#low-maintenance-project).
