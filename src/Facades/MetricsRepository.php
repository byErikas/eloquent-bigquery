<?php

namespace ByErikas\EloquentBigQuery\Facades;

use App\Services\BigQuery\MetricsRepository as BigQueryMetricsRepository;
use Illuminate\Support\Facades\Facade;

/**
 * @method static \App\Services\BigQuery\QueryService from(array|\Illuminate\Support\Collection $metrics)
 * @method static array all()
 * @method static ?array find($string $keyword)
 *
 * @see \App\Services\BigQuery\MetricsRepository
 */
class MetricsRepository extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return BigQueryMetricsRepository::class;
    }
}
