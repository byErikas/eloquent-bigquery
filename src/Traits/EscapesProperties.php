<?php

namespace ByErikas\EloquentBigQuery\Traits;

trait EscapesVariables
{
    private const string ACCESS_OPERATOR = ".";

    private function escape(mixed $value): mixed
    {
        $type = gettype($value);

        if ($type == "string") {
            if (str_contains($value, self::ACCESS_OPERATOR)) {
                return $value;
            }

            return "\"{$value}\"";
        }

        return $value;
    }
}
