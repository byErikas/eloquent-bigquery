<h1 align="center">Eloquent BigQuery</h1>

<p align="center">
  <a href="https://github.com/byErikas/eloquent-bigquery/actions"><img src="https://github.com/byErikas/cache-tags/actions/workflows/tests.yml/badge.svg" alt="tests Status"></a>
  <a href="https://codecov.io/gh/byErikas/eloquent-bigquery"><img src="https://codecov.io/gh/byErikas/eloquent-bigquery/graph/badge.svg?token=GfelSQj0FQ"/></a>
  <a href="https://packagist.org/packages/byerikas/eloquent-bigquery"><img src="https://img.shields.io/packagist/v/byerikas/eloquent-bigquery" alt="Packagist Version"></a>
  <a href="https://github.com/byErikas/eloquent-bigquery/blob/v1.x.x/LICENSE"><img src="https://img.shields.io/github/license/byerikas/eloquent-bigquery" alt="GitHub License"></a>
</p>

Simple select SQL generator and query service in the syntax style of Laravel's Eloquent for Google BigQuery.

# Usage
Queries can be built using the provided `Builder` facade:
```php
use ByErikas\EloquentBigQuery\Builder;

$query = Builder::table("test", "table_alias")
  ->select(["column AS column_alias"])
  ->where("columnA", "value")
  ->whereIn("columnB", [1, 2, 3])
  ->whereBetween("columnC", 1, 10)
  ->limit(10)
  ->offset(5)
  ->orderBy("column_alias", "desc");
```

And their SQL can be returned by using the `toSQL` method:
```php
$query = Builder::table("test")
  ->select(["column", "column2"])
  ->whereBetween("column", 1, 2);

$sql = $query->toSQL(); //returns: "SELECT column, column2 FROM `test` WHERE column BETWEEN 1 AND 2"
```

Executing the queries can be done using the `QueryService` facade:
```php
use ByErikas\EloquentBigQuery\Facades\QueryService;

$result = QueryService::query($query)->execute();

[$resultsA, $resultsB] = QueryService::query([$queryA, $queryB])->execute(); //multiple queries are supported
```

Selecting predefined columns or aggregations on queries can be done using `selectAggregations` method:
```php
Builder::table("test")->selectAggregations(["metric-1"]);
```

Joins are supported using variuos `join` methods. Complex where / having statements are supported by passing a function to the first parameter of `where` / `having` methods:
```php
use ByErikas\EloquentBigQuery\Join;

Builder::table("test")
  ->select(["column"])
  ->join("test1", "t1", function (Join $query) {
    $query->where("time", "test.time");
  });

use ByErikas\EloquentBigQuery\Where;

Builder::table("test")
  ->select(["column"])
  ->where(function (Where $query) {
    $query->where("columnA", true)
      ->whereBetween("columnB", "1000-01-01", "2000-01-01", "or");
  });

use ByErikas\EloquentBigQuery\Having;

Builder::table("test")
  ->select(["column"])
  ->having(function (Having $query) {
    $query->having("columnA", 100);
  });
```

# Installation
The package can be installed using:
```
composer require byerikas/eloquent-bigquery
```

# Configuration
Before usage it's recommended to define the default BigQuery client config in your `AppServiceProvider.php`:
```php
use ByErikas\EloquentBigQuery\Facades\QueryService;

public function boot(): void {

  //...

  QueryService::setClientConfig(["keyFile" => ...]);

  //...
}
```
As well as register any custom / complex select metrics or aggregations that you may be storing elsewhere:
```php
use ByErikas\EloquentBigQuery\Facades\AggregationsRepository;

public function boot(): void {

  //...

  AggregationsRepository::from([["keyword" => "metric-1", "value" => "ROUND(..."]]);

  //...
}
```
the `AggregationsRepository::from()` method accepts array of arrays or a Collection of arrays, and must have the following structure:
```php
$metrics = [
  [
    "keyword" => "metric-1", 
    "value" => "ROUND(..."
  ]
];
```
`keyword` is used to get the metric, and as the metric's alias in SQL, and the `value` defines the actual SQL that is inserted.
