IQuery
======

[![Build Status](https://travis-ci.org/nyamsprod/IQuery.png)](https://travis-ci.org/nyamsprod/IQuery)

A simple library to query Iterator with a SQL-like syntax

`P\IQuery` is a simple library to ease Iterator manipulation by query Iterator using SQL like syntax.

The library is an extract of the [League\csv](http://csv.thephpleague.com) library repacked to be used on any type of `Iterator` not just `SplFileObject` objects.

This package is compliant with [PSR-1], [PSR-2], and [PSR-4].

[PSR-1]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-1-basic-coding-standard.md
[PSR-2]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md
[PSR-4]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-4-autoloader.md


System Requirements
-------

You need **PHP >= 5.4.0** to use `P\IQuery` but the latest stable version of PHP is recommended.

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

## Tips

This library does not work out of the box on **hhvm** the reason being that [CallbackFilterIterator](https://github.com/facebook/hhvm/issues/1715) is not yet implemented.

Then everything should work as intended.

## Instantiation

You can use the library in two way.

* You can use the trait `IQueryTrait` on any class that implements the `IteratorAggregate` interface
* You can instantiate a the `IQuery` class with an Iterator object.

In both case you will end up with the ability to traverse your Iterator using a SQL-like method. 

## Querying the Iterator

The library ease the search by using a set of methods described below. But keep in mind that:

* The query options methods are all chainable *except when they have to return a boolean*;
* The query options methods can be call in any sort of order before any query execution;
* After each execution, all settings are cleared;

### Filtering methods

The filtering options **are the first settings applied to the Iterator before anything else**. The filters follow the *First In First Out* rule.
To enable filtering RecursiveIterator a optional argument $is_recursive is to be use to indicate which class is being used (ie: [CallbackFilterIterator](http://php.net/class.callbackfilteriterator.php) or [RecursiveCallbackFilterIterator](http://php.net/class.recursivecallbackfilteriterator.php)).

#### addWhere($callable, $is_recursive = false)

The `addWhere` method adds a callable filter function each time it is called. The function can take up to three parameters:

* the current iterator data;
* the current iterator key;
* the iterator object;

#### removeWhere($callable, $is_recursive = false)

`removeWhere` method removes an already registered filter function. If the function was registered multiple times, you will have to call `removeWhere` as often as the filter was registered. **The first registered copy will be the first to be removed.**

#### hasWhere($callable, $is_recursive = false)

`hasWhere` method checks if the filter function is already registered

#### clearWhere()

`clearWhere` method removes all registered filter functions.

### Sorting methods

The sorting options are applied **after the filtering options**. The sorting follow the *First In First Out* rule.

**To sort the data `iterator_to_array` is used which could lead to performance penalty if you have a heavy `iterator` to sort**

#### addSortBy($callable)

`addSortBy` method adds a sorting function each time it is called. The function takes exactly two parameters which will be filled by pairs of consecutive items.

#### removeSortBy($callable)

`removeSortBy` method removes an already registered sorting function. If the function was registered multiple times, you will have to call `removeSortBy` as often as the function was registered. **The first registered copy will be the first to be removed.**

#### hasSortBy($callable)

`hasSortBy` method checks if the sorting function is already registered

#### clearSortBy()

`clearSortBy` method removes all registered sorting functions.

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

$csv = new SplFileObject('/path/to/my/csv/file.csv');
$csv->setFlags(SplFileObject::READ_CSV|SplFileObject::DROP_NEW_LINE);

$stmt = new P\IQuery($csv);
$iterator = $stmt
    ->setOffset(3)
    ->setLimit(2)
    ->addWhere('filterByEmail')
    ->addSortBy('sortByLastName')
    ->addSelect(function ($value) {
        return array_map('strtoupper', $value);
    })
    ->queryIterator(); 
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
