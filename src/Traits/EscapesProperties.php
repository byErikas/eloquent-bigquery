<?php

namespace ByErikas\EloquentBigQuery\Traits;

trait EscapesProperties
{
    private const string ACCESS_OPERATOR = ".";

    private function escape(mixed $value): mixed
    {
        $type = gettype($value);

        if ($type == "string") {
            $isAccessField = str_contains($value, self::ACCESS_OPERATOR);
            $isEscaped = str_starts_with($value, "\"") && str_ends_with($value, "\"");

            if ($isAccessField || $isEscaped) {
                return $value;
            }

            return "\"{$value}\"";
        }

        return $value;
    }
}
