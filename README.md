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
        1 => ColumnDefinition::from('a', Type::integer()),
        2 => ColumnDefinition::from('b', Type::float()),
        3 => ColumnDefinition::from('c', Type::string()),
        4 => ColumnDefinition::from('d', Type::boolean()),
        5 => ColumnDefinition::from('e', Type::boolean()),
    ]
);

$parser = new Parser;

foreach ($parser->parse('example.csv', $schema) as $row) {
    var_dump($row);
}
```

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
