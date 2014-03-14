IQuery
======

A simple library to query Iterator with a SQL-like syntax

P\IQuery is a simple library to ease Iterator manipulation by query Iterator using SQL like syntax.

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
        "P/IQuery": "*"
    }
}
```

Documentation
-------------

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

## Filtering methods

The filtering options **are the first settings applied to the Iterator before anything else**. The filters follow the *First In First Out* rule.

### addFilter($callable)

The `addFilter` method adds a callable filter function each time it is called. The function can take up to three parameters:

* the current iterator data;
* the current iterator key;
* the iterator object;

### removeFilter($callable)

`removeFilter` method removes an already registered filter function. If the function was registered multiple times, you will have to call `removeFilter` as often as the filter was registered. **The first registered copy will be the first to be removed.**

### hasFilter($callable)

`hasFilter` method checks if the filter function is already registered

### clearFilter()

`clearFilter` method removes all registered filter functions.

## Sorting methods

The sorting options are applied **after the filtering options**. The sorting follow the *First In First Out* rule.

**To sort the data `iterator_to_array` is used which could lead to performance penalty if you have a heavy `iterator` to sort**

### addSortBy($callable)

`addSortBy` method adds a sorting function each time it is called. The function takes exactly two parameters which will be filled by pairs of rows.

### removeSortBy($callable)

`removeSortBy` method removes an already registered sorting function. If the function was registered multiple times, you will have to call `removeSortBy` as often as the function was registered. **The first registered copy will be the first to be removed.**

### hasSortBy($callable)

`hasSortBy` method checks if the sorting function is already registered

### clearSortBy()

`clearSortBy` method removes all registered sorting functions.

## Interval methods

The methods enable returning a specific interval of CSV rows. When called more than once, only the last filtering settings is taken into account. The interval is calculated **after filtering and/or sorting but before extracting the data**.

### setOffset($offset = 0)

`setOffset` method specifies an optional offset for the return data. By default the offset equals `0`.

### setLimit($limit = -1)

`setLimit` method specifies an optional maximum rows count for the return data. By default the offset equals `-1`, which translate to all rows.

## Select method

The `select` method enable modifying the iterator content by specifying a callable function that will be applied on each iterator resulting items.

The method can take up to three parameters:

* the current iterator data;
* the current iterator key;
* the iterator object;

### query()

The `query` method prepares and issues queries on the Iterator. It returns an `Iterator` that represents the result that you can further manipulate as you wish.

## A concrete example to sum it all

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
    ->addFilter('filterByEmail')
    ->addSortBy('sortByLastName')
    ->addSelect(function ($value) {
        return array_map('strtoupper', $value);
    })
    ->query(); 
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
