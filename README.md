Iterators
======

[![Build Status](https://travis-ci.org/nyamsprod/IQuery.png)](https://travis-ci.org/nyamsprod/IQuery)

`P\Iterators` adds two new Iterators classes `MapIteraor` and `QueryIterator` to your project.

*The library is an extract of the [League\csv](http://csv.thephpleague.com) library repacked to be used on any type of `Iterator` not just `SplFileObject` objects used to treat CSV files.*

This package is compliant with [PSR-2], and [PSR-4].

[PSR-2]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md
[PSR-4]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-4-autoloader.md

System Requirements
-------

You need **PHP >= 5.4.0** or **HHVM >= 3.2.0** to use `P\Iterators` but the latest stable version of PHP is recommended.

Install
-------

Install the `Iterators` package with Composer.

```json
{
    "require": {
        "P\Iterators": "*"
    }
}
```
### Going Solo

You can also use `P\Iterators` without using Composer by downloading the library and registing an autoloader function:

```php
spl_autoload_register(function ($class) {
    $prefix = 'P\\Iterators\\';
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

## MapIterator

`MapIterator` extends the SPL `IteratorIterator` class. This class transforms an Iterator by applying a callable function on each iterator item. The callable function can take up to three parameters:

* the current iterator data;
* the current iterator key;
* the iterator object;

Here's a simple usage:

```php
use P\Iterators\MapIterator;

$callable = function ($item) {
    return strtoupper($item);
}

$iterator = new \ArrayIterator(['one', 'two', 'three', 'four']);
$iterator = new MapIterator($iterator, $callable);

var_dump(iterator_to_array($iterator));
// will output something like ['ONE', 'TWO', 'THREE', 'FOUR'];

```

## QueryIterator

This class enable seeking data into an Iterator using a SQL like approach. You instantiate a the `QueryIterator` class with an Iterator object. The `QueryIterator` class implements the `IteratorAggregate` interface.

The class uses a set of methods described below. But keep in mind that:

* The query options methods are all chainable *except when they have to return a boolean*;
* The query options methods can be call in any sort of order before any query execution;
* After each execution, all settings are cleared;
* All options follow the the *First In First Out* rule.

### Filtering methods

The filter options **are the first settings applied to the Iterator before anything else**. 

#### addWhere(callable $callable)

The `addWhere` method adds a callable filter function each time it is called. The function can take up to three parameters:

* the current iterator data;
* the current iterator key;
* the iterator object;

#### removeWhere(callable $callable)

`removeWhere` method removes an already registered filter function. If the function was registered multiple times, you will have to call `removeWhere` as often as the filter was registered. **The first registered copy will be the first to be removed.**

#### hasWhere(callable $callable)

`hasWhere` method checks if the filter function is already registered

#### clearWhere()

`clearWhere` method removes all registered filter functions.

### Sorting methods

The sorting options are applied **after the where options**.

**To sort the data `iterator_to_array` is used which could lead to performance penalty if you have a heavy `iterator` to sort**

#### addOrderBy(callable $callable)

`addOrderBy` method adds a sorting function each time it is called. The function takes exactly two parameters which will be filled by pairs of consecutive items in your iterator.

#### removeOrderBy(callable $callable)

`removeOrderBy` method removes an already registered sorting function. If the function was registered multiple times, you will have to call `removeOrderBy` as often as the function was registered. **The first registered copy will be the first to be removed.**

#### hasOrderBy(callable $callable)

`hasOrderBy` method checks if the sorting function is already registered

#### clearOrderBy()

`clearOrderBy` method removes all registered sorting functions.

### Interval methods

The methods enable returning a specific interval of Iterator items. When called more than once, only the last filtering settings is taken into account. The interval is calculated **after filtering and/or sorting but before extracting the data**.

#### setOffset($offset = 0)

`setOffset` method specifies an optional offset for the return data. By default the offset equals `0`.

#### setLimit($limit = -1)

`setLimit` method specifies an optional maximum items to return. By default the offset equals `-1`, which translate to all items.

### Selecting method

#### setSelect(callable $callable = null)

The `setSelect` method enable modifying the iterator content by specifying a callable function that will be applied on each iterator resulting items.

The method can take up to three parameters:

* the current iterator data;
* the current iterator key;
* the iterator object;

### Clearing all the options

#### clear()

This methods clears all registered options at any given time prior to the query execution and reset them to their initial value.

## Query the Iterator

### query()

The `query` method applies the filtering options set on the `Iterator` object. The result returned is a `Iterator` object that you can further manipulate as you wish.

### fetchAll()

The `fetchAll` behaves like the `query` method but instead of returning an `Iterator` it returns a sequential array of the found items;

### fetchOne()

The `fetchOne` method returns a single item from the Iterator; *Of note: the Interval methods have no effect on the output of this method*;

### each(callable $callable)

The `each` method allows you to iterate over the given Iterator and execute a callable function with each selected item. 

The callable function can take up to three parameters:

* the current iterator data;
* the current iterator key;
* the iterator object; 

The callable function **MUST** return `true` in order to continue to iterate over the original object.

The method returns the number of sucessfull iterations. 

## Examples

Here's an example on how to use the query features of the `Iterators` class:

```php

use P\Iterators\QueryIterator;

$file = new \SplFileObject('/path/to/my/csv/file.txt');
$file->setFlags(\SplFileObject::DROP_NEW_LINE);

$stmt = new QueryIterator($file);
$iterator = $stmt
    ->setOffset(3)
    ->setLimit(2)
    ->query(); 
//iterator is a Iterator object which contains at most
// 2 items starting from the 4 line of the file
//you can iterate over the $iterator using the foreach construct

foreach ($iterator as $line) {
    echo $line; //the selected line from the file
}
```

Here's another example using the `fetchAll` method

```php

use P\Iterators\QueryIterator;

$file = new \SplFileObject('/path/to/my/csv/file.txt');
$file->setFlags(\SplFileObject::DROP_NEW_LINE);

$stmt = new QueryIterator($file);
$res = $stmt
    ->setOffset(3)
    ->setLimit(2)
    ->setSelect(function ($value) {
        return strtoupper($value);
    })
    ->fetchAll(); 
// $res is a array containing each line of the 
// file is carry the same result as using php file function
```

Using the `each` method

```php

use P\Iterators\QueryIterator;

function filterByEmail($row) 
{
    return filer_var($row[2], FILTER_VALIDATE_EMAIL);
}

function sortByLastName($rowA, $rowB)
{
    return strcmp($rowB[1], $rowA[1]);
}

$csv = new \SplFileObject('/path/to/my/csv/file.csv');
$csv->setFlags(\SplFileObject::READ_CSV|SplFileObject::DROP_NEW_LINE);

$stmt = new QueryIterator($csv);
$nbIterations = $stmt
    ->setOffset(3)
    ->setLimit(2)
    ->addWhere('filterByEmail')
    ->addOrderBy('sortByLastName')
    ->setSelect(function ($value) {
        return strtoupper($value);
    })
    ->each(function ($row, $index, $iterator) use (&$res, $func)) {
        $res[] = $func($row, $index, $iterator);
        return true;
    }); 
// $nbIterations is the number of successfull iterations
// $res array contains the result of applying the $func function to the values
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
- [All Contributors](https://github.com/nyamsprod/Iterators/graphs/contributors)
