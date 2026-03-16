<?php

use ByErikas\EloquentBigQuery\Builder;

it("#1 can generate basic select from SQL", function () {
    $sql = Builder::table("test", "table_alias")
        ->select(["column AS column_alias"])
        ->where("columnA", "value")
        ->whereIn("columnB", [1, true, "yes"])
        ->whereBetween("columnC", "1000-01-01", "2000-01-01")
        ->whereBetween("columnD", now(), now()->addMonth())
        ->limit(10)
        ->offset(5)
        ->groupBy(["column_alias"])
        ->orderBy("column_alias", "desc")
        ->toSQL();

    expect($sql)->toBe("SELECT column AS column_alias FROM `test` table_alias WHERE columnA = \"value\" AND columnB IN (1, '1', 'yes') AND columnC BETWEEN \"1000-01-01\" AND \"2000-01-01\" AND columnD BETWEEN \"2026-03-16 11:35:39\" AND \"2026-04-16 11:35:39\" GROUP BY column_alias ORDER BY column_alias DESC LIMIT 10 OFFSET 5");
});
