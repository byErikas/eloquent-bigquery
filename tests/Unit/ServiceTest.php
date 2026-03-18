<?php

use ByErikas\EloquentBigQuery\Facades\QueryService;

it("can set and get client config", function () {
    QueryService::setClientConfig(["key" => true]);

    expect(QueryService::getClientConfig())->toBe(["key" => true]);
});

it("can generate query jobs", function () {
    expect(QueryService::query([])->execute())->toBe(null);
});