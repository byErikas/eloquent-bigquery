<?php

namespace ByErikas\EloquentBigQuery\Traits;

trait EscapesProperties
{
    private const string ACCESS_OPERATOR = ".";
    private const array COMPARISON_OPERATORS = ["=", "!=", ">", ">=", "<", "<=", "<>", "LIKE", "NOT LIKE"];

    private function escape(mixed $value): mixed
    {
        $type = gettype($value);

        if ($type == "string") {
            $isAccessField = $this->containsAccessOperator($value);
            $isEscaped = str_starts_with($value, "\"") && str_ends_with($value, "\"");

            if ($isAccessField || $isEscaped) {
                return $value;
            }

            return "\"{$value}\"";
        }

        return $value;
    }

    private function containsAccessOperator(mixed $value): bool
    {
        if (gettype($value) == "string") {
            return str_contains($value, self::ACCESS_OPERATOR);
        }

        return false;
    }

    private function containsComparisonOperator(mixed $value): bool
    {
        if (gettype($value) == "string") {
            return in_array(strtoupper($value), self::COMPARISON_OPERATORS);
        }

        return false;
    }
}
