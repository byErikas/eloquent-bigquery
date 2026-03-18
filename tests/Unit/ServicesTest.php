<?php

use ByErikas\EloquentBigQuery\Facades\AggregationsRepository;
use ByErikas\EloquentBigQuery\Facades\QueryService;

it("can set and get client config", function () {
    QueryService::setClientConfig(["key" => true]);

    expect(QueryService::getClientConfig())->toBe(["key" => true]);
});

it("can generate query jobs", function () {
    expect(QueryService::query([])->execute())->toBe(null);
});


it("can set metrics to repository and get metrics from it", function () {
    AggregationsRepository::from(collect([["keyword" => "test", "value" => "SUM(*)"]]));

    expect(AggregationsRepository::all())->toBe([["keyword" => "test", "value" => "SUM(*)"]]);

    expect(AggregationsRepository::find("test"))->toBe([
        "keyword" => "test",
        "value" => "SUM(*)"
    ]);
});
