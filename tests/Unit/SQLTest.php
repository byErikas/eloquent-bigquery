<?php

use ByErikas\EloquentBigQuery\Builder;
use ByErikas\EloquentBigQuery\Facades\MetricsRepository;
use ByErikas\EloquentBigQuery\Facades\QueryService;
use ByErikas\EloquentBigQuery\Join;
use ByErikas\EloquentBigQuery\Where;

it("can set and get client config", function () {
    QueryService::setClientConfig(["key" => true]);

    expect(QueryService::getClientConfig())->toBe(["key" => true]);
});

it("can set metrics to repository and get metrics from it", function () {
    MetricsRepository::from(collect([["keyword" => "test", "value" => "SUM(*)"]]));

    expect(MetricsRepository::all())->toBe([["keyword" => "test", "value" => "SUM(*)"]]);

    expect(MetricsRepository::find("test"))->toBe([
        "keyword" => "test",
        "value" => "SUM(*)"
    ]);
});

it("can generate basic select SQLs with dates, offset, limit and aliases", function () {
    $date = now();

    $sql = Builder::table("test", "table_alias")
        ->select(["column AS column_alias"])
        ->where("columnA", "value")
        ->whereIn("columnB", [1, true, "yes"])
        ->whereBetween("columnC", "1000-01-01", "2000-01-01")
        ->whereBetween("columnD", $date, $date)
        ->limit(10)
        ->offset(5)
        ->groupBy(["column_alias"])
        ->orderBy("column_alias", "desc")
        ->toSQL();

    expect($sql)->toBe("SELECT column AS column_alias FROM `test` table_alias WHERE columnA = \"value\" AND columnB IN (1, '1', 'yes') AND columnC BETWEEN \"1000-01-01\" AND \"2000-01-01\" AND columnD BETWEEN \"{$date->format("Y-m-d H:i:s")}\" AND \"{$date->format("Y-m-d H:i:s")}\" GROUP BY column_alias ORDER BY column_alias DESC LIMIT 10 OFFSET 5");
});

it("can generate advanced wheres", function () {
    $sql = Builder::table("test")
        ->select(["column"])
        ->where(function (Where $query) {
            $query->whereIn("columnA", [1, 2, 3])
                ->whereBetween("columnB", "1000-01-01", "2000-01-01", "or");
        })->toSQL();

    expect($sql)->toBe("SELECT column FROM `test` WHERE (columnA IN (1, 2, 3) OR columnB BETWEEN \"1000-01-01\" AND \"2000-01-01\")");
});

it("can generate join and subquery", function () {
    MetricsRepository::from(collect([["keyword" => "test", "value" => "SUM(*)"]]));

    $queryA = Builder::table("test")
        ->select(["column"])
        ->leftJoin("test2", "t2", function (Join $query) {
            $query->where("time", "test.time")
                ->whereBetween("time", "1000-01-01", "2000-01-01");
        });

    expect($queryA->toSQL())->toBe("SELECT column FROM `test` LEFT JOIN `test2` t2 ON t2.time = test.time AND t2.time BETWEEN \"1000-01-01\" AND \"2000-01-01\"");

    $queryB = Builder::table($queryA, "qa")
        ->selectMetrics(["test"])
        ->whereNotNull("qa.time");

    expect($queryB->toSQL())->toBe("SELECT SUM(*) AS test FROM (SELECT column FROM `test` LEFT JOIN `test2` t2 ON t2.time = test.time AND t2.time BETWEEN \"1000-01-01\" AND \"2000-01-01\") qa WHERE qa.time IS NOT NULL");
});

it("can generate query jobs", function () {
    expect(QueryService::query([])->execute())->toBe(null);
});
