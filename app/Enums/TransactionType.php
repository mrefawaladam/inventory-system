<?php

namespace App\Enums;

enum TransactionType: string
{
    case INBOUND = 'INBOUND';
    case OUTBOUND = 'OUTBOUND';
    case TRANSFER = 'TRANSFER';

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
            self::INBOUND => 'Inbound',
            self::OUTBOUND => 'Outbound',
            self::TRANSFER => 'Transfer',
        };
    }
}

