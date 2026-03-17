<?php

namespace ByErikas\EloquentBigQuery\Facades;

use ByErikas\EloquentBigQuery\QueryService as BigQueryQueryService;
use Illuminate\Support\Facades\Facade;

/**
 * @method static \ByErikas\EloquentBigQuery\QueryService setClientConfig(array $config)
 * @method static array getClientConfig()
 * @method static \ByErikas\EloquentBigQuery\QueryService query(array|\ByErikas\EloquentBigQuery\Builder $query)
 * @method static null|array|QueryResults execute()
 *
 * @see \ByErikas\EloquentBigQuery\QueryService
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
