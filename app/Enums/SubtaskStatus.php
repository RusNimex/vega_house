<?php

namespace App\Enums;

/**
 * Статусы подзадач к основным задачам
 */
enum SubtaskStatus: string
{
    /**
     * к выполнению
     */
    case NEW = 'new';

    /**
     * завершена
     */
    case COMPLETE = 'complete';

    /**
     * Все значения
     * 
     * @return array<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Все статусы как массив
     * 
     * @return array<string>
     */
    public static function toArray(): array
    {
        return self::values();
    }
}

