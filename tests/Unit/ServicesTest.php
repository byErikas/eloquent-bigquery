<?php

use ByErikas\EloquentBigQuery\Facades\MetricsRepository;
use ByErikas\EloquentBigQuery\Facades\QueryService;

it("can set and get client config", function () {
    QueryService::setClientConfig(["key" => true]);

    expect(QueryService::getClientConfig())->toBe(["key" => true]);
});

it("can generate query jobs", function () {
    expect(QueryService::query([])->execute())->toBe(null);
});


it("can set metrics to repository and get metrics from it", function () {
    MetricsRepository::from(collect([["keyword" => "test", "value" => "SUM(*)"]]));

    expect(MetricsRepository::all())->toBe([["keyword" => "test", "value" => "SUM(*)"]]);

    expect(MetricsRepository::find("test"))->toBe([
        "keyword" => "test",
        "value" => "SUM(*)"
    ]);
});
