<?php

namespace ByErikas\EloquentBigQuery;

use Google\Cloud\BigQuery\BigQueryClient;
use Google\Cloud\BigQuery\Job;
use Google\Cloud\BigQuery\QueryResults;

class QueryService
{
    private array $queries = [];

    private static array $clientConfig = [];

    public function query(array|Builder $query): self
    {
        if (! is_array($query)) {
            $query = [$query];
        }

        $this->queries = $query;

        return $this;
    }

    public function execute(): null|array|QueryResults
    {
        $jobClosures = $this->buildJobClosures();
        $jobCount = count($jobClosures);

        if ($jobCount == 0) {
            return null;
        }

        $completedCount = 0;
        $completedJobs = [];

        $pendingJobs = [];

        foreach ($jobClosures as $index => $closure) {
            $pendingJobs[$index] = $closure();
        }

        while ($completedCount != $jobCount) {
            foreach ($pendingJobs as $index => $job) {
                $job->reload();

                if ($job->isComplete()) {
                    $completedJobs[$index] = $job->queryResults();

                    unset($pendingJobs[$index]);
                    $completedCount++;
                }
            }
        }

        /** If there is only one query, return it unwrapped  */
        if ($jobCount == 1) {
            return array_first($completedJobs);
        }

        return $completedJobs;
    }

    public static function setClientConfig(array $config): void
    {
        static::$clientConfig = $config;
    }

    public function getClientConfig(): array
    {
        return static::$clientConfig;
    }

    private function getClient(): BigQueryClient
    {
        return new BigQueryClient(static::$clientConfig);
    }

    private function buildJobClosures(): array
    {
        $tasks = [];

        foreach ($this->queries as $builder) {
            $tasks[] = function () use ($builder): Job {
                $client = $this->getClient();

                $query = $client->query($builder->toSQL());
                return $client->startQuery($query);
            };
        }

        return $tasks;
    }
}
