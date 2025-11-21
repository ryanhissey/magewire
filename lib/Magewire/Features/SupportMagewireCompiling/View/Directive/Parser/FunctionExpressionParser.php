<?php
/**
 * Copyright © Willem Poortman 2021-present. All rights reserved.
 *
 * Please read the README and LICENSE files for more
 * details on copyrights and license information.
 */

declare(strict_types=1);

namespace Magewirephp\Magewire\Features\SupportMagewireCompiling\View\Directive\Parser;

use Exception;
use InvalidArgumentException;

class FunctionExpressionParser extends ExpressionParser
{
    /**
     * Parse named arguments from a function expression.
     *
     * @throws InvalidArgumentException If parsing fails
     */
    public function parseArguments(string $expression): array
    {
        if (empty(trim($expression))) {
            return [];
        }

        // Check if the entire expression is a JSON string first.
        if ($this->isJsonString($expression)) {
            return $this->parseJsonArguments($expression);
        }

        $arguments = [];
        $errors = [];
        $pos = 0;
        $length = strlen($expression);

        while ($pos < $length) {
            // Skip whitespace
            while ($pos < $length && ctype_space($expression[$pos])) {
                $pos++;
            }

            if ($pos >= $length) {
                break;
            }

            // Extract key
            $keyStart = $pos;
            while ($pos < $length && preg_match('/[a-zA-Z0-9_]/', $expression[$pos])) {
                $pos++;
            }

            if ($pos === $keyStart) {
                $errors[] = "Invalid key at position $pos";
                $pos++;
                continue;
            }

            $key = substr($expression, $keyStart, $pos - $keyStart);

            // Validate key
            if (!$this->isValidKey($key)) {
                $errors[] = "Invalid key at position $keyStart: '$key'";
                // Skip to next comma or end
                while ($pos < $length && $expression[$pos] !== ',') {
                    $pos++;
                }
                $pos++;
                continue;
            }

            // Skip whitespace and colon
            while ($pos < $length && ctype_space($expression[$pos])) {
                $pos++;
            }

            if ($pos >= $length || $expression[$pos] !== ':') {
                $errors[] = "Missing colon after key '$key' at position $pos";
                continue;
            }

            $pos++; // Skip colon

            // Skip whitespace
            while ($pos < $length && ctype_space($expression[$pos])) {
                $pos++;
            }

            if ($pos >= $length) {
                $errors[] = "Missing value for key '$key'";
                continue;
            }

            // Extract value using smart parsing
            $valueStart = $pos;
            try {
                $value = $this->extractValue($expression, $pos);
                $arguments[$key] = $value;
            } catch (Exception $e) {
                $errors[] = "Failed to parse value for key '$key' at position $valueStart: " . $e->getMessage();
            }

            // Skip whitespace and comma
            while ($pos < $length && ctype_space($expression[$pos])) {
                $pos++;
            }

            if ($pos < $length && $expression[$pos] === ',') {
                $pos++;
            }
        }

        // If we have errors but also some successful parses, you might want to log warnings
        // instead of throwing. Adjust based on your needs.
        if (! empty($errors) && empty($arguments)) {
            throw new InvalidArgumentException("Parsing failed with errors: " . implode(', ', $errors));
        }

        return $arguments;
    }

    /**
     * Extract a value from the expression starting at position $pos.
     * Updates $pos to point after the extracted value.
     */
    private function extractValue(string $expression, &$pos): mixed
    {
        if ($pos >= strlen($expression)) {
            throw new InvalidArgumentException("Unexpected end of expression");
        }

        $char = $expression[$pos];

        // JSON object
        if ($char === '{') {
            return $this->extractJsonObject($expression, $pos);
        }

        // JSON array
        if ($char === '[') {
            return $this->extractJsonArray($expression, $pos);
        }

        // Quoted string
        if ($char === '"' || $char === "'") {
            return $this->extractQuotedString($expression, $pos);
        }

        // Unquoted value (null, true, false, number, variable, unquoted string)
        return $this->extractUnquotedValue($expression, $pos);
    }

    /**
     * Extract a JSON object with proper bracket matching.
     */
    private function extractJsonObject(string $expression, &$pos): array
    {
        $start = $pos;
        $depth = 0;
        $inString = false;
        $escape = false;

        while ($pos < strlen($expression)) {
            $char = $expression[$pos];

            if ($escape) {
                $escape = false;
                $pos++;
                continue;
            }

            if ($char === '\\') {
                $escape = true;
                $pos++;
                continue;
            }

            if ($char === '"') {
                $inString = !$inString;
                $pos++;
                continue;
            }

            if (!$inString) {
                if ($char === '{') {
                    $depth++;
                } elseif ($char === '}') {
                    $depth--;
                    if ($depth === 0) {
                        $pos++;
                        $json = substr($expression, $start, $pos - $start);
                        return $this->parseJsonStructure($json);
                    }
                }
            }

            $pos++;
        }

        throw new InvalidArgumentException("Unclosed JSON object");
    }

    /**
     * Extract a JSON array with proper bracket matching.
     */
    private function extractJsonArray(string $expression, &$pos): array
    {
        $start = $pos;
        $depth = 0;
        $inString = false;
        $escape = false;

        while ($pos < strlen($expression)) {
            $char = $expression[$pos];

            if ($escape) {
                $escape = false;
                $pos++;
                continue;
            }

            if ($char === '\\') {
                $escape = true;
                $pos++;
                continue;
            }

            if ($char === '"') {
                $inString = !$inString;
                $pos++;
                continue;
            }

            if (!$inString) {
                if ($char === '[') {
                    $depth++;
                } elseif ($char === ']') {
                    $depth--;
                    if ($depth === 0) {
                        $pos++;
                        $json = substr($expression, $start, $pos - $start);
                        return $this->parseJsonStructure($json);
                    }
                }
            }

            $pos++;
        }

        throw new InvalidArgumentException("Unclosed JSON array");
    }

    /**
     * Extract a quoted string.
     */
    private function extractQuotedString(string $expression, &$pos): string
    {
        $quote = $expression[$pos];
        $pos++;
        $start = $pos;
        $escape = false;

        while ($pos < strlen($expression)) {
            $char = $expression[$pos];

            if ($escape) {
                $escape = false;
                $pos++;
                continue;
            }

            if ($char === '\\') {
                $escape = true;
                $pos++;
                continue;
            }

            if ($char === $quote) {
                $value = substr($expression, $start, $pos - $start);
                $pos++;
                // Handle escape sequences
                return str_replace(['\\' . $quote, '\\\\'], [$quote, '\\'], $value);
            }

            $pos++;
        }

        throw new InvalidArgumentException("Unclosed quoted string");
    }

    /**
     * Extract an unquoted value (null, true, false, number, variable, or unquoted string).
     */
    private function extractUnquotedValue(string $expression, &$pos): mixed
    {
        $start = $pos;

        // Read until we hit a comma, closing bracket, or end of string
        while ($pos < strlen($expression) && $expression[$pos] !== ',' && $expression[$pos] !== '}' && $expression[$pos] !== ']') {
            $pos++;
        }

        $value = trim(substr($expression, $start, $pos - $start));

        if ($value === '') {
            throw new InvalidArgumentException("Empty value");
        }

        // Parse the unquoted value
        if ($value === 'null') {
            return null;
        }
        if ($value === 'true') {
            return true;
        }
        if ($value === 'false') {
            return false;
        }
        if ($this->isNumber($value)) {
            return $this->parseNumber($value);
        }
        if ($this->isVariable($value)) {
            return $this->parseVariable($value);
        }

        return $value;
    }



    /**
     * Validate if a key is a valid-named argument identifier.
     *
     * @param string $key The key to validate
     * @return bool True if valid named argument, false otherwise
     */
    private function isValidKey(string $key): bool
    {
        // Named arguments should be valid PHP identifiers
        return preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $key) === 1;
    }

    /**
     * Parse a value string into the appropriate PHP type.
     *
     * @throws InvalidArgumentException If value cannot be parsed
     */
    private function parseValue(string $value): mixed
    {
        $value = trim($value);

        if ($value === '') {
            throw new InvalidArgumentException("Empty value");
        }

        if ($value === 'null') {
            return null;
        }

        if ($value === 'true') {
            return true;
        }
        if ($value === 'false') {
            return false;
        }

        // Handle quoted strings.
        if ($this->isQuotedString($value)) {
            return $this->parseQuotedString($value);
        }

        // Handle JSON objects and arrays.
        if ($this->isJsonStructure($value)) {
            return $this->parseJsonStructure($value);
        }

        // Handle numbers.
        if ($this->isNumber($value)) {
            return $this->parseNumber($value);
        }

        // Handle variables (starting with $).
        if ($this->isVariable($value)) {
            return $this->parseVariable($value);
        }

        // Handle unquoted strings (be careful with this).
        if ($this->isValidUnquotedString($value)) {
            return $value;
        }

        throw new InvalidArgumentException("Unable to parse value: $value");
    }

    /**
     * Check if the value is a quoted string.
     */
    private function isQuotedString(string $value): bool
    {
        return (str_starts_with($value, '"') && str_ends_with($value, '"'))
            || (str_starts_with($value, "'") && str_ends_with($value, "'"));
    }

    /**
     * Parse quoted string, handling escape sequences.
     */
    private function parseQuotedString(string $value): string
    {
        $quote = $value[0];
        $content = substr($value, 1, -1);

        // Handle escape sequences.
        return str_replace(['\\' . $quote, '\\\\'], [$quote, '\\'], $content);
    }

    /**
     * Check if value is a JSON structure.
     */
    private function isJsonStructure(string $value): bool
    {
        return (str_starts_with($value, '{') && str_ends_with($value, '}')) ||
            (str_starts_with($value, '[') && str_ends_with($value, ']'));
    }

    /**
     * Parse JSON structure.
     */
    private function parseJsonStructure(string $value)
    {
        $decoded = json_decode($value, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new InvalidArgumentException("Invalid JSON: " . json_last_error_msg());
        }

        return $decoded;
    }

    /**
     * Check if value is a number.
     */
    private function isNumber(string $value): bool
    {
        return preg_match('/^-?\d+(?:\.\d+)?(?:[eE][+-]?\d+)?$/', $value) === 1;
    }

    /**
     * Parse number value.
     */
    private function parseNumber(string $value): float|int
    {
        if (str_contains($value, '.') || str_contains(strtolower($value), 'e')) {
            return (float) $value;
        }

        return (int) $value;
    }

    /**
     * Check if the expression is a JSON string.
     */
    private function isJsonString(string $expression): bool
    {
        $trimmed = trim($expression);

        if (! str_starts_with($trimmed, '{') || ! str_ends_with($trimmed, '}')) {
            return false;
        }

        json_decode($trimmed);
        return json_last_error() === JSON_ERROR_NONE;
    }

    /**
     * Parse JSON string into named arguments.
     */
    private function parseJsonArguments(string $jsonString): array
    {
        $decoded = json_decode(trim($jsonString), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new InvalidArgumentException("Invalid JSON: " . json_last_error_msg());
        }
        if (! is_array($decoded)) {
            throw new InvalidArgumentException("JSON must decode to an associative array");
        }

        $arguments = [];
        foreach ($decoded as $key => $value) {
            // Validate that keys are valid named argument identifiers
            if (! $this->isValidKey($key)) {
                throw new InvalidArgumentException("Invalid argument name in JSON: '$key'");
            }

            $arguments[$key] = $value;
        }

        return $arguments;
    }

    /**
     * Check if value is a variable reference.
     */
    private function isVariable(string $value): bool
    {
        return preg_match('/^\$[a-zA-Z_][a-zA-Z0-9_]*$/', $value) === 1;
    }

    /**
     * Parse variable reference - returns a special object to indicate it's a variable.
     */
    private function parseVariable(string $value): array
    {
        return [
            'type' => 'variable',
            'name' => substr($value, 1) // Remove the $ prefix
        ];
    }

    /**
     * Check if unquoted string is valid for named arguments.
     */
    private function isValidUnquotedString(string $value): bool
    {
        // Be restrictive about what we accept as unquoted strings in function expressions.
        return preg_match('/^[a-zA-Z_][a-zA-Z0-9_-]*$/', $value) === 1;
    }
}
