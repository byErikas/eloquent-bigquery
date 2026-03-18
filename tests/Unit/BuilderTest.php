<?php

use ByErikas\EloquentBigQuery\Builder;
use ByErikas\EloquentBigQuery\Exceptions\InvalidSelect;
use ByErikas\EloquentBigQuery\Exceptions\UndefinedAggregation;
use ByErikas\EloquentBigQuery\Facades\AggregationsRepository;
use ByErikas\EloquentBigQuery\Join;
use ByErikas\EloquentBigQuery\Where;

it("can generate selects", function () {
    AggregationsRepository::from([[
        "keyword" => "sumColumn3",
        "value" => "SUM(column3)"
    ]]);

    $query = Builder::table("test")
        ->select(["column1 AS alias", "column2"])
        ->selectAggregations(["sumColumn3"]);

    expect($query->toSQL())->toBe("SELECT column1 AS alias, column2, SUM(column3) AS sumColumn3 FROM `test`");

    $query = Builder::table("test")
        ->select(["*"])
        ->selectAggregations(["sumColumn3"]);

    expect(fn() => $query->toSQL())->toThrow(InvalidSelect::class, "Select can't be \"*\" when using \"selectAggregations\".");

    $query = Builder::table("test")
        ->select([]);

    expect(fn() => $query->toSQL())->toThrow(InvalidSelect::class, "Select can't be empty.");

    $query = Builder::table("test")
        ->selectAggregations(["unknownAggregation"]);

    expect(fn() => $query->toSQL())->toThrow(UndefinedAggregation::class, "Aggregation \"unknownAggregation\" not found, or is of invalid format!");
});

it("can generate wheres", function () {
    $query = Builder::table("test")
        ->select(["*"])
        ->where("column1", "value")
        ->where("column2", null)
        ->where("column3", ">", 100)
        ->where(function (Where $query) {
            $query->where("column4", true)
                ->where("column5", "!=", true, "or");
        });

    expect($query->toSQL())->toBe("SELECT * FROM `test` WHERE column1 = \"value\" AND column2 IS NULL AND column3 > 100 AND (column4 = 1 OR column5 != 1)");
});

it("can generate wheresIns", function () {
    $query = Builder::table("test")
        ->select(["*"])
        ->whereIn("column1", [])
        ->whereIn("column2", ["value1", "value2"])
        ->whereIn("column3", [1, 2]);

    expect($query->toSQL())->toBe("SELECT * FROM `test` WHERE column2 IN ('value1', 'value2') AND column3 IN (1, 2)");
});

it("can generate whereBetweens", function () {
    $carbon = now();
    $carbonFormatted = $carbon->format("Y-m-d H:i:s");

    $query = Builder::table("test")
        ->select(["*"])
        ->whereBetween("column1", $carbon, $carbon)
        ->whereBetween("column2", 1, 100, "or")
        ->whereBetween("column3", "1000-01-01", "2000-01-01");

    expect($query->toSQL())->toBe("SELECT * FROM `test` WHERE column1 BETWEEN \"{$carbonFormatted}\" AND \"{$carbonFormatted}\" OR column2 BETWEEN 1 AND 100 AND column3 BETWEEN \"1000-01-01\" AND \"2000-01-01\"");
});

it("can generate limits", function () {
    $query = Builder::table("test")
        ->select(["*"])
        ->limit(100);

    expect($query->toSQL())->toBe("SELECT * FROM `test` LIMIT 100");
});

it("can generate offsets", function () {
    $query = Builder::table("test")
        ->select(["*"])
        ->offset(100);

    expect($query->toSQL())->toBe("SELECT * FROM `test` OFFSET 100");
});

it("can generate groupBys", function () {
    $query = Builder::table("test")
        ->select(["columnA", "columnB", "columnC"])
        ->groupBy(["columnA", "columnB", "columnC"]);

    expect($query->toSQL())->toBe("SELECT columnA, columnB, columnC FROM `test` GROUP BY columnA, columnB, columnC");
});

it("can generate orderBys", function () {
    $query = Builder::table("test")
        ->select(["*"])
        ->orderBy("column", "desc")
        ->orderBy()
        ->orderBy([["columnA", "desc"], ["columnB"]]);

    expect($query->toSQL())->toBe("SELECT * FROM `test` ORDER BY columnA DESC, columnB");
});

// it("can generate basic select SQLs with dates, offset, limit and aliases", function () {
//     $date = now();

//     $sql = Builder::table("test", "table_alias")
//         ->select(["column AS column_alias"])
//         ->where("columnA", "value")
//         ->whereIn("columnB", [1, true, "yes"])
//         ->whereBetween("columnC", "1000-01-01", "2000-01-01")
//         ->whereBetween("columnD", $date, $date)
//         ->limit(10)
//         ->offset(5)
//         ->orderBy("column_alias", "desc")
//         ->toSQL();

//     expect($sql)->toBe("SELECT column AS column_alias FROM `test` table_alias WHERE columnA = \"value\" AND columnB IN (1, '1', 'yes') AND columnC BETWEEN \"1000-01-01\" AND \"2000-01-01\" AND columnD BETWEEN \"{$date->format("Y-m-d H:i:s")}\" AND \"{$date->format("Y-m-d H:i:s")}\" ORDER BY column_alias DESC LIMIT 10 OFFSET 5");
// });

// it("can generate advanced wheres", function () {
//     $sql = Builder::table("test")
//         ->select(["column"])
//         ->where(function (Where $query) {
//             $query->whereIn("columnA", [1, 2, 3])
//                 ->whereBetween("columnB", "1000-01-01", "2000-01-01", "or");
//         })->toSQL();

//     expect($sql)->toBe("SELECT column FROM `test` WHERE (columnA IN (1, 2, 3) OR columnB BETWEEN \"1000-01-01\" AND \"2000-01-01\")");
// });

// it("can generate joins and subquery", function () {
//     MetricsRepository::from(collect([["keyword" => "test", "value" => "SUM(*)"]]));

//     $queryA = Builder::table("test")
//         ->select(["column"])
//         ->join("test1", "t1", function (Join $query) {
//             $query->where("time", "test.time");
//         })->leftJoin("test2", "t2", function (Join $query) {
//             $query->where("time", "test.time")
//                 ->whereBetween("time", "1000-01-01", "2000-01-01");
//         })->rightJoin("test3", "t3", function (Join $query) {
//             $query->whereIn("time", [])
//                 ->whereIn("time", ["1000-01-01"]);
//         })->crossJoin("test4", "t4", function (Join $query) {
//             $query->whereBetween("time", "1000-01-01", "2000-01-01");
//         })->fullJoin("test5", "t5", function (Join $query) {
//             $query->where("time", null)
//                 ->where("time", ">=", "1000-01-01");
//         });

//     expect($queryA->toSQL())->toBe("SELECT column FROM `test` INNER JOIN `test1` t1 ON t1.time = test.time LEFT JOIN `test2` t2 ON t2.time = test.time AND t2.time BETWEEN \"1000-01-01\" AND \"2000-01-01\" RIGHT JOIN `test3` t3 ON t3.time IN ('1000-01-01') CROSS JOIN `test4` t4 ON t4.time BETWEEN \"1000-01-01\" AND \"2000-01-01\" FULL JOIN `test5` t5 ON t5.time IS NULL AND t5.time >= \"1000-01-01\"");

//     $queryB = Builder::table($queryA, "qa")
//         ->selectMetrics(["test"])
//         ->whereNotNull("qa.time");

//     expect($queryB->toSQL())->toBe("SELECT SUM(*) AS test FROM (SELECT column FROM `test` INNER JOIN `test1` t1 ON t1.time = test.time LEFT JOIN `test2` t2 ON t2.time = test.time AND t2.time BETWEEN \"1000-01-01\" AND \"2000-01-01\" RIGHT JOIN `test3` t3 ON t3.time IN ('1000-01-01') CROSS JOIN `test4` t4 ON t4.time BETWEEN \"1000-01-01\" AND \"2000-01-01\" FULL JOIN `test5` t5 ON t5.time IS NULL AND t5.time >= \"1000-01-01\") qa WHERE qa.time IS NOT NULL");
// });

// it("can generate ordered and grouped sqls", function () {
//     $sql = Builder::table("test")
//         ->select(["column", "column2"])
//         ->orderBy()
//         ->orderBy([["column", "DESC"]])
//         ->orderBy([["column2"]])
//         ->orderBy("column3", "ASC")
//         ->groupBy(["column"])
//         ->toSQL();

//     expect($sql)->toBe("SELECT column, column2 FROM `test` GROUP BY column ORDER BY column DESC, column2, column3 ASC");
// });

// it("can generate complex where", function () {
//     $sql = Builder::table("test")
//         ->select(["column", "column2"])
//         ->where(function (Where $query) {
//             $query->where("enabled", true)
//                 ->whereIn("keyword", [], "or");
//         })->toSQL();

//     expect($sql)->toBe("SELECT column, column2 FROM `test` WHERE (enabled = 1)");

//     $sql = Builder::table("test")
//         ->select(["column", "column2"])
//         ->where(function (Where $query) {
//             $query->whereBetween("value", 1, 100);
//         })->toSQL();

//     expect($sql)->toBe("SELECT column, column2 FROM `test` WHERE (value BETWEEN 1 AND 100)");
// });

// it("can remove empty wheres, and prefix where when needed", function () {
//     $sql = Builder::table("test")
//         ->select(["column", "column2"])
//         ->whereIn("column", []);

//     expect($sql->toSQL())->toBe("SELECT column, column2 FROM `test`");

//     $sql = Builder::table("test")
//         ->select(["column", "column2"])
//         ->whereIn("column", [1, 2]);

//     expect($sql->toSQL())->toBe("SELECT column, column2 FROM `test` WHERE column IN (1, 2)");

//     $sql = Builder::table("test")
//         ->select(["column", "column2"])
//         ->whereBetween("column", 1, 2);

//     expect($sql->toSQL())->toBe("SELECT column, column2 FROM `test` WHERE column BETWEEN 1 AND 2");
// });
