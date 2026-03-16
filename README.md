<h1 align="center">Eloquent BigQuery</h1>

<p align="center">
  <a href="https://github.com/byErikas/eloquent-bigquery/actions"><img src="https://github.com/byErikas/cache-tags/actions/workflows/tests.yml/badge.svg" alt="tests Status"></a>
  <a href="https://codecov.io/gh/byErikas/eloquent-bigquery"><img src="https://codecov.io/gh/byErikas/eloquent-bigquery/graph/badge.svg?token=GfelSQj0FQ"/></a>
  <a href="https://packagist.org/packages/byerikas/eloquent-bigquery"><img src="https://img.shields.io/packagist/v/byerikas/eloquent-bigquery" alt="Packagist Version"></a>
  <a href="https://github.com/byErikas/eloquent-bigquery/blob/v1.x.x/LICENSE"><img src="https://img.shields.io/github/license/byerikas/eloquent-bigquery" alt="GitHub License"></a>
</p>

Simple BigQuery SQL generator and query service in the syntax style of Laravel's Eloquent for Google BigQuery.

# Installation
The package can be installed using:
```
composer require byerikas/eloquent-bigquery
```

# Configuration
Before using it's recommended to define the default BigQuery client config in your `AppServiceProvider.php`:
```php
use ByErikas\EloquentBigQuery\Facades\QueryService;

QueryService::setClientConfig(["keyFile" => ...]);
```
As well as register any custom / complex select metrics that you may be storing elsewhere:
```php
use ByErikas\EloquentBigQuery\Facades\MetricsRepository;

MetricsRepository::from([["keyword" => "metric-1", "value" => "ROUND(..."]]);
```
the `MetricsRepository::from()` method accepts array of arrays or a Collection of arrays, and must have the following structure:
```php
$metrics = [
  [
    "keyword" => "metric-1", 
    "value" => "ROUND(..."
  ]
];
```
`keyword` is used to get the metric, and as the metric's alias in SQL, and the `value` defines the actual SQL that is inserted.
