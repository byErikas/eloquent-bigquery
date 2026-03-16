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
Before using it's recommended to define the default BigQuery client config in your `AppServiceProvider.php`:
```
use ByErikas\EloquentBigQuery\Facades\QueryService;

QueryService::setClientConfig(["keyFile" => ...]);
```
As well as register any custom / complex select metrics that you may be storing elsewhere:
```
use ByErikas\EloquentBigQuery\Facades\MetricsRepository;

MetricsRepository::from([["keyword" => "metric", "formula" => "ROUND(..."]]);
```

