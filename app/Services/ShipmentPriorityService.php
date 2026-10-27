<?php

namespace App\Services;

class ShipmentPriorityService
{
    public function determinePriority(?float $volumeKg, ?string $departureTime): string
    {
        if ($volumeKg !== null && $volumeKg >= 16000) {
            return 'urgent';
        }

        if ($departureTime !== null || ($volumeKg !== null && $volumeKg >= 14500)) {
            return 'high';
        }

        return 'normal';
    }

    public function weightForPriority(string $priority): float
    {
        return match ($priority) {
            'urgent' => 3.0,
            'high' => 2.0,
            default => 1.0,
        };
    }
}
