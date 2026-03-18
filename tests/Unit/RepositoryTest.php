<?php

use ByErikas\EloquentBigQuery\Facades\AggregationsRepository;

it("can set metrics to repository and get metrics from it", function () {
    AggregationsRepository::from(collect([["keyword" => "test", "value" => "SUM(*)"]]));

    expect(AggregationsRepository::all())->toBe([["keyword" => "test", "value" => "SUM(*)"]]);

    expect(AggregationsRepository::find("test"))->toBe([
        "keyword" => "test",
        "value" => "SUM(*)"
    ]);
});
