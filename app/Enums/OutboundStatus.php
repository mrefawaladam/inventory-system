<?php

namespace App\Enums;

enum OutboundStatus: string
{
    case PENDING = 'PENDING';
    case COMPLETED = 'COMPLETED';

    /**
     * Get all values as array
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get label for display
     */
    public function label(): string
    {
        return match($this) {
            self::PENDING => 'Pending',
            self::COMPLETED => 'Completed',
        };
    }
}

