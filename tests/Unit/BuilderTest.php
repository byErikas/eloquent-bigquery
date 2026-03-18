<?php

use ByErikas\EloquentBigQuery\Builder;
use ByErikas\EloquentBigQuery\Exceptions\HavingsCantBeEmpty;
use ByErikas\EloquentBigQuery\Exceptions\InvalidSelect;
use ByErikas\EloquentBigQuery\Exceptions\UndefinedAggregation;
use ByErikas\EloquentBigQuery\Exceptions\WheresCantBeEmpty;
use ByErikas\EloquentBigQuery\Facades\AggregationsRepository;
use ByErikas\EloquentBigQuery\Having;
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

    expect(fn() => $query->toSQL())->toThrow(InvalidSelect::class);

    $query = Builder::table("test")
        ->select([]);

    expect(fn() => $query->toSQL())->toThrow(InvalidSelect::class);

    $query = Builder::table("test")
        ->selectAggregations(["unknownAggregation"]);

    expect(fn() => $query->toSQL())->toThrow(UndefinedAggregation::class);
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

    $query = Builder::table("test")
        ->select(["*"])
        ->where(function (Where $query) {
            $query->whereIn("column1", [1, 2])
                ->whereIn("column2", []);
        });

    expect($query->toSQL())->toBe("SELECT * FROM `test` WHERE (column1 IN (1, 2))");

    $query = Builder::table("test")
        ->select(["*"])
        ->where(function (Where $query) {
            $query->whereBetween("column1", 1, 100);
        });

    expect($query->toSQL())->toBe("SELECT * FROM `test` WHERE (column1 BETWEEN 1 AND 100)");

    $query = Builder::table("test")
        ->select(["*"])
        ->whereNotNull("column1", "value");

    expect($query->toSQL())->toBe("SELECT * FROM `test` WHERE column1 IS NOT NULL");

    expect(function () {
        return Builder::table("test")
            ->select(["*"])
            ->where(function (Where $query) {})->toSQL();
    })->toThrow(WheresCantBeEmpty::class);
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

it("can generate joins", function () {
    $query = Builder::table("test")
        ->select(["*"])
        ->join("join", "j", function (Join $query) {
            $query->on("column", "test.column");
        });

    expect($query->toSQL())->toBe("SELECT * FROM `test` INNER JOIN `join` j ON j.column = test.column");

    $query = Builder::table("test")
        ->select(["*"])
        ->leftJoin("join", "j", function (Join $query) {
            $query->where("column", "test.column");
        });

    expect($query->toSQL())->toBe("SELECT * FROM `test` LEFT JOIN `join` j ON j.column = test.column");

    $query = Builder::table("test")
        ->select(["*"])
        ->rightJoin("join", "j", function (Join $query) {
            $query->where("column", "test.column");
        });

    expect($query->toSQL())->toBe("SELECT * FROM `test` RIGHT JOIN `join` j ON j.column = test.column");

    $query = Builder::table("test")
        ->select(["*"])
        ->crossJoin("join", "j", function (Join $query) {
            $query->where("column", "test.column");
        });

    expect($query->toSQL())->toBe("SELECT * FROM `test` CROSS JOIN `join` j ON j.column = test.column");

    $query = Builder::table("test")
        ->select(["*"])
        ->fullJoin("join", "j", function (Join $query) {
            $query->where("column", "test.column");
        });

    expect($query->toSQL())->toBe("SELECT * FROM `test` FULL JOIN `join` j ON j.column = test.column");
});

it("can generate havings", function () {
    $query = Builder::table("test")
        ->select(["*"])
        ->having("column1", "value")
        ->having("column2", ">", 100);

    expect($query->toSQL())->toBe("SELECT * FROM `test` HAVING column1 = \"value\" AND column2 > 100");

    $query = Builder::table("test")
        ->select(["*"])
        ->having(function (Having $query) {
            $query->having("column1", "value")
                ->having("column2", ">", 100, "or");
        });

    expect($query->toSQL())->toBe("SELECT * FROM `test` HAVING (column1 = \"value\" OR column2 > 100)");

    $query = Builder::table("test")
        ->select(["*"])
        ->having("column1", null);

    expect($query->toSQL())->toBe("SELECT * FROM `test` HAVING column1 IS NULL");

    expect(function () {
        return Builder::table("test")
            ->select(["*"])
            ->having(function (Having $query) {})->toSQL();
    })->toThrow(HavingsCantBeEmpty::class);

    AggregationsRepository::from([[
        "keyword" => "sumColumn3",
        "value" => "SUM(column3)"
    ]]);

    $query = Builder::table("test")
        ->select(["*"])
        ->havingAggregation("sumColumn3", null);

    expect($query->toSQL())->toBe("SELECT * FROM `test` HAVING SUM(column3) IS NULL");

    $query = Builder::table("test")
        ->select(["*"])
        ->havingAggregation("sumColumn3", 100)
        ->havingAggregation("sumColumn3", ">", 10);

    expect($query->toSQL())->toBe("SELECT * FROM `test` HAVING SUM(column3) = 100 AND SUM(column3) > 10");

    $query = Builder::table("test")
        ->select(["*"])
        ->having(function (Having $query) {
            $query->having("column1", 100)
                ->having("column2", ">", 10)
                ->havingAggregation("sumColumn3", 100, boolean: "or");
        });

    expect($query->toSQL())->toBe("SELECT * FROM `test` HAVING (column1 = 100 AND column2 > 10 OR SUM(column3) = 100)");

    $query = Builder::table("test")
        ->select(["*"])
        ->having(function (Having $query) {
            $query->havingAggregation("sumColumn3", 100)
                ->having("column1", "!=", 10, "or");
        });

    expect($query->toSQL())->toBe("SELECT * FROM `test` HAVING (SUM(column3) = 100 OR column1 != 10)");

    expect(function () {
        return Builder::table("test")
            ->select(["*"])
            ->havingAggregation("unknownAggregation", 100)->toSQL();
    })->toThrow(UndefinedAggregation::class);
});
