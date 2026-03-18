<?php

namespace ByErikas\EloquentBigQuery\Facades;

use ByErikas\EloquentBigQuery\AggregationsRepository as BigQueryAggregationsRepository;
use Illuminate\Support\Facades\Facade;

/**
 * @method static void from(array|\Illuminate\Support\Collection $metrics)
 * @method static array all()
 * @method static ?array find($string $keyword)
 *
 * @see \ByErikas\EloquentBigQuery\AggregationsRepository
 */
class AggregationsRepository extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return BigQueryAggregationsRepository::class;
    }
}
