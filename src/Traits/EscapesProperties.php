<?php

namespace ByErikas\EloquentBigQuery\Traits;

trait EscapesProperties
{
    private const string ACCESS_OPERATOR = ".";
    private const array COMPARISON_OPERATORS = ["=", "!=", ">", ">=", "<", "<=", "<>", "LIKE", "NOT LIKE"];

    private function escape(mixed $value, string $type = "auto"): mixed
    {
        if (is_array($value)) {
            foreach ($value as $key => $element) {
                $value[$key] = $this->escape($element, $type);
            }

            return $value;
        }

        if ($type == "auto") {
            $type = gettype($value);
        }

        if ($type == "string") {
            $isAccessField = $this->containsAccessOperator($value);
            $isEscaped = str_starts_with($value, "\"") && str_ends_with($value, "\"");

            if ($isAccessField || $isEscaped) {
                return $value;
            }

            return "\"{$value}\"";
        }

        if ($type == "integer") {
            return intval($value);
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
