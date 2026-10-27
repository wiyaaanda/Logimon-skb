<?php

namespace App\Imports;

use DateTimeInterface;

trait SkipsDuplicateRows
{
    protected array $skipValues = [];

    protected array $seenValues = [];

    public function __construct(array $skipValues = [])
    {
        $this->skipValues = $skipValues;
    }

    protected function shouldSkip(array $row, string $column): bool
    {
        $rawValue = $row[$column] ?? '';
        $value = $rawValue instanceof DateTimeInterface
            ? $rawValue->format('n-j')
            : trim((string) $rawValue);
        if ($value === '') {
            return false;
        }

        $normalized = mb_strtolower($value);
        if (isset($this->skipValues[$column][$normalized]) || isset($this->seenValues[$column][$normalized])) {
            return true;
        }

        $this->seenValues[$column][$normalized] = true;

        return false;
    }
}
