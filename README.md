IQuery
======

[![Build Status](https://travis-ci.org/nyamsprod/IQuery.png)](https://travis-ci.org/nyamsprod/IQuery)

A simple library to query Iterator with a SQL-like syntax

`P\IQuery` is a simple library to ease Iterator manipulation by query Iterator using SQL like syntax.

The library is an extract of the [League\csv](http://csv.thephpleague.com) library repacked to be used on any type of `Iterator` not just `SplFileObject` objects.

This package is compliant with [PSR-2], and [PSR-4].

[PSR-2]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md
[PSR-4]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-4-autoloader.md


System Requirements
-------

You need **PHP >= 5.4.0** or **HHVM >= 3.2.0** to use `P\IQuery` but the latest stable version of PHP is recommended.

Install
-------

Install the `IQuery` package with Composer.

```json
{
    "require": {
        "P\IQuery": "*"
    }
}
```
### Going Solo

You can also use `P\IQuery` without using Composer by downloading the library and registing an autoloader function:

```php
spl_autoload_register(function ($class) {
    $prefix = 'P\\IQuery\\';
    $base_dir = __DIR__ . '/src/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        // no, move to the next registered autoloader
        return;
    }
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    if (file_exists($file)) {
        require $file;
    }
});
```

Or, use any other [PSR-4](http://www.php-fig.org/psr/psr-4/) compatible autoloader.

## Instantiation

You can use the library in two way.

* You can use the trait `IteratorQueryBuilder` on any class that implements the `IteratorAggregate` interface
* You can instantiate a the `QueryIterator` class with an Iterator object.

In both case you will end up with the ability to traverse your Iterator using a SQL-like method. 

## Querying the Iterator

The library ease the search by using a set of methods described below. But keep in mind that:

* The query options methods are all chainable *except when they have to return a boolean*;
* The query options methods can be call in any sort of order before any query execution;
* After each execution, all settings are cleared;
* All options follow the the *First In First Out* rule.

### Where methods

The where options **are the first settings applied to the Iterator before anything else**. 

#### addWhere($callable)

The `addWhere` method adds a callable filter function each time it is called. The function can take up to three parameters:

* the current iterator data;
* the current iterator key;
* the iterator object;

#### removeWhere($callable)

`removeWhere` method removes an already registered filter function. If the function was registered multiple times, you will have to call `removeWhere` as often as the filter was registered. **The first registered copy will be the first to be removed.**

#### hasWhere($callable)

`hasWhere` method checks if the filter function is already registered

#### clearWhere()

`clearWhere` method removes all registered filter functions.

### Order By methods

The sorting options are applied **after the where options**.

**To sort the data `iterator_to_array` is used which could lead to performance penalty if you have a heavy `iterator` to sort**

#### addOrderBy($callable)

`addOrderBy` method adds a sorting function each time it is called. The function takes exactly two parameters which will be filled by pairs of consecutive items in your iterator.

#### removeOrderBy($callable)

`removeOrderBy` method removes an already registered sorting function. If the function was registered multiple times, you will have to call `removeOrderBy` as often as the function was registered. **The first registered copy will be the first to be removed.**

#### hasOrderBy($callable)

`hasOrderBy` method checks if the sorting function is already registered

#### clearOrderBy()

`clearOrderBy` method removes all registered sorting functions.

### Interval methods

The methods enable returning a specific interval of Iterator items. When called more than once, only the last filtering settings is taken into account. The interval is calculated **after filtering and/or sorting but before extracting the data**.

#### setOffset($offset = 0)

`setOffset` method specifies an optional offset for the return data. By default the offset equals `0`.

#### setLimit($limit = -1)

`setLimit` method specifies an optional maximum items to return. By default the offset equals `-1`, which translate to all items.

### setSelect method

The `setSelect` method enable modifying the iterator content by specifying a callable function that will be applied on each iterator resulting items.

The method can take up to three parameters:

* the current iterator data;
* the current iterator key;
* the iterator object;

#### queryIterator()

The `queryIterator` method prepares and issues queries on the Iterator. It returns an `Iterator` that represents the result that you can further manipulate as you wish.

### A concrete example to sum it all

Here's an example on how to use the query features of the `IQuery` class:

```php

use P\IQuery;

function filterByEmail($row) 
{
    return filer_var($row[2], FILTER_VALIDATE_EMAIL);
}

function sortByLastName($rowA, $rowB)
{
    return strcmp($rowB[1], $rowA[1]);
}

$file = new SplFileObject('/path/to/my/csv/file.txt');
$file->setFlags(SplFileObject::DROP_NEW_LINE);

$stmt = new P\IQuery($file);
$iterator = $stmt
    ->setOffset(3)
    ->setLimit(2)
    ->queryIterator(); 
//iterator is a Iterator object which contains at most
// 2 items starting from the 4 line of the file

```

## Fetching Iterator Data

In addition to the QueryIterator() method you can retrieve specific items from your iterator using the following 2 methods.

- `fetchAll` will return a sequential array of the found items;
- `fetchOne` will return a single item from the array; *Of note: the Interval methods have no effect on the output of the method;

```php

use P\IQuery;

function filterByEmail($row) 
{
    return filer_var($row[2], FILTER_VALIDATE_EMAIL);
}

function sortByLastName($rowA, $rowB)
{
    return strcmp($rowB[1], $rowA[1]);
}

$file = new SplFileObject('/path/to/my/csv/file.txt');
$file->setFlags(SplFileObject::DROP_NEW_LINE);

$stmt = new P\IQuery($file);
$res = $stmt
    ->setOffset(3)
    ->setLimit(2)
    ->addSelect(function ($value) {
        return strtoupper($value);
    })
    ->fetchAll(); 
// $res is a array containing each line of the 
// file is carry the same result as using php file function
```

Last but not least, you can iterate over the Iterator and apply a callable to each found item using the `each` method. *This can be handy if you want for instance import data from your Iterator to a database for example.*

```php

use P\IQuery;

function filterByEmail($row) 
{
    return filer_var($row[2], FILTER_VALIDATE_EMAIL);
}

function sortByLastName($rowA, $rowB)
{
    return strcmp($rowB[1], $rowA[1]);
}

$csv = new SplFileObject('/path/to/my/csv/file.csv');
$csv->setFlags(SplFileObject::READ_CSV|SplFileObject::DROP_NEW_LINE);

$stmt = new P\IQuery($csv);
$nbIterations = $stmt
    ->setOffset(3)
    ->setLimit(2)
    ->addWhere('filterByEmail')
    ->addOrderBy('sortByLastName')
    ->addSelect(function ($value) {
        return strtoupper($value);
    })
    ->each(function ($row, $index, $iterator) use (&$res, $func)) {
        $res[] = $func($row, $index, $iterator);
        return true;
    }); 
// $nbIterations is the number of successfull iterations
```

Testing
-------

``` bash
$ phpunit
```

Contributing
-------

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

Credits
-------

- [ignace nyamagana butera](https://github.com/nyamsprod)
- [All Contributors](https://github.com/nyamsprod/IQuery/graphs/contributors)
