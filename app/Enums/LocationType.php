<?php

namespace App\Enums;

enum LocationType: string
{
    case ZONE = 'ZONE';
    case RACK = 'RACK';
    case SLOT = 'SLOT';

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
            self::ZONE => 'Area',
            self::RACK => 'Rak',
            self::SLOT => 'Tempat',
        };
    }
}

