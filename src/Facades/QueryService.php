<?php

namespace ByErikas\EloquentBigQuery\Facades;

use ByErikas\EloquentBigQuery\QueryService as BigQueryQueryService;
use Illuminate\Support\Facades\Facade;

/**
 * @method static \App\Services\BigQuery\QueryService setClientConfig(array $config)
 * @method static array getClientConfig()
 * @method static \App\Services\BigQuery\QueryService query(array|\App\Services\BigQuery\Builder $query)
 * @method static null|array|QueryResults execute()
 *
 * @see \App\Services\BigQuery\QueryService
 */
class QueryService extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return BigQueryQueryService::class;
    }
}
