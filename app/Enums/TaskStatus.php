<?php

namespace App\Enums;

/**
 * Статусы задач компаний
 */
enum TaskStatus: string
{
    /** новые задачи */
    case NEW = 'new';

    /** задачи в процессе */
    case PROCESS = 'process';

    /** приостановленные задачи */
    case BREAK = 'break';

    /** отклоненные задачи */
    case DECLINE = 'decline';

    /** завершенные задачи */
    case COMPLETE = 'complete';

    /**
     * Получить все значения
     * 
     * @return array<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Получить все статусы как массив
     * 
     * @return array<string>
     */
    public static function toArray(): array
    {
        return self::values();
    }
}

