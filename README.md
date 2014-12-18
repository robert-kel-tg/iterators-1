Iterators
======

[![Build Status](https://travis-ci.org/nyamsprod/Iterators.png)](https://travis-ci.org/nyamsprod/Iterators)

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

Install the `p\iterators` using Composer.

```bash
$ composer require p/iterators
```
### Going Solo

You can also use `P\Iterators` by downloading the library and using a [PSR-4](http://www.php-fig.org/psr/psr-4/) compatible autoloader.

## MapIterator

`MapIterator` extends the SPL `IteratorIterator` class. This class transforms an Iterator by applying a callable function on each iterator item. The callable function can take up to three parameters:

* the current iterator data;
* the current iterator key;
* the iterator object;

Here's a simple usage:

```php
use P\Iterators\MapIterator;

$iterator = new ArrayIterator(['one', 'two', 'three', 'four']);
$iterator = new MapIterator($iterator, function ($item) {
    return strtoupper($item);
});

var_dump(iterator_to_array($iterator));
// will output something like ['ONE', 'TWO', 'THREE', 'FOUR'];

```

## QueryIterator

This class enable seeking data into an Iterator using a SQL like approach. You instantiate a `QueryIterator` object with an Iterator object. The `QueryIterator` class implements the `IteratorAggregate` interface.

The class uses a set of methods described below. But keep in mind that:

* The query options methods are all chainable *except when they have to return a boolean*;
* The query options methods can be call in any sort of order before any query execution;
* All options follow the the *First In First Out* rule.

### Filtering methods (equivalent to SQL WHERE conditions)

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

### Sorting methods (equivalent to SQL ORDER BY conditions)

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

### Interval methods (equivalent to SQL OFFSET and LIMIT conditions)

The methods enable returning a specific interval of Iterator items. When called more than once, only the last filtering settings is taken into account. The interval is calculated **after filtering and/or sorting but before extracting the data**.

#### setOffset($offset = 0)

`setOffset` method specifies an optional offset for the return data. By default the offset equals `0`.

#### getOffset()

`getOffset` method returns the current offset for the return data.

#### setLimit($limit = -1)

`setLimit` method specifies an optional maximum items to return. By default the offset equals `-1`, which translate to all items.

#### getLimit()

`getLimit` method returns the current limit for the return data.

### Selecting method (equivalent to SQL SELECT conditions)

#### setSelect(callable $callable = null)

The `setSelect` method enable modifying the iterator content by specifying a callable function that will be applied on each iterator resulting items.

#### getSelect()

`getSelect` method returns the current callable function that will format the returned data if any was registered.

### Clearing all the options

#### clearAll()

This methods clears all registered options at any given time prior to the query execution and reset them to their initial value.

## Query the Iterator

### getIterator()

The `getIterator` method applies the filtering options set on the `Iterator` object. The result returned is a `Iterator` object that you can further manipulate as you wish.

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

$file = new FilesystemIterator('/path/to/my/directory');

$iterator = new QueryIterator($file);
$iterator->setOffset(3);
$iterator->setLimit(2);
$iterator->addOrderBy(function ($fileA, $fileB) {
	return strcmp($fileA->getBasename(), $fileB->getBasename());
});

//iterator is a Iterator object which contains at most
// 2 items starting from the 4 file
//you can iterate over the $iterator using the foreach construct

foreach ($iterator as $file) {
    echo $file->getBasename(); //the selected file
}
```

Using the `each` method

```php
use P\Iterators\QueryIterator;

$file = new FilesystemIterator('/path/to/my/directory');

$iterator = new QueryIterator($file);
$iterator->addOrderBy(function ($fileA, $fileB) {
	return ! strcmp($fileA->getMTime(), $fileB->getMTime());
});
$res = [];
$nb_iterations = $iterator->each(function ($file) use (&$res) {
	$res[] = json_decode(file_get_contents($file->getRealPath()), true);

	return JSON_ERROR_NONE == json_last_error();
})
//$nb_iterations is the number of successfull iterations
//$res contains the result of applying the callable to the values
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
