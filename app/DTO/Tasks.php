<?php

namespace App\DTO;

use App\Models\Company as CompanyModel;
use InvalidArgumentException;

/**
 * DTO для представления статистики задач компании
 * 
 * Общее колвоо задач и колво по каждому статусу {@see TaskStatus}
 */
class Tasks
{
    public function __construct(
        public readonly int $total,
        public readonly int $new,
        public readonly int $process,
        public readonly int $break,
        public readonly int $decline,
        public readonly int $complete,
    ) {
    }

    /**
     * Создание DTO из модели Company
     * 
     * Модель Company должан иметь загруженные счетчики через withCount:
     * - tasks_count (общее количество)
     * - tasks_new (количество со статусом 'new')
     * - tasks_process (количество со статусом 'process')
     * - tasks_break (количество со статусом 'break')
     * - tasks_decline (количество со статусом 'decline')
     * - tasks_complete (количество со статусом 'complete')
     * 
     * @param CompanyModel $company компания с загруженными счетчиками задач
     * @return self
     * @throws InvalidArgumentException Если модель не имеет загруженных счетчиков задач
     */
    public static function fromModel(CompanyModel $company): self
    {
        // Проверяем наличие обязательных счетчиков
        if (!isset($company->tasks_count)) {
            throw new InvalidArgumentException(
                'Company model must have tasks_count loaded.'
            );
        }

        // Проверяем наличие счетчиков по статусам
        $requiredCounters = [
            'tasks_new',
            'tasks_process',
            'tasks_break',
            'tasks_decline',
            'tasks_complete',
        ];

        $missingCounters = [];
        foreach ($requiredCounters as $counter) {
            if (!isset($company->$counter)) {
                $missingCounters[] = $counter;
            }
        }

        if (!empty($missingCounters)) {
            throw new InvalidArgumentException(
                'Company model must have task status counters loaded: ' . implode(', ', $missingCounters)
            );
        }

        return new self(
            total: (int) $company->tasks_count,
            new: (int) $company->tasks_new,
            process: (int) $company->tasks_process,
            break: (int) $company->tasks_break,
            decline: (int) $company->tasks_decline,
            complete: (int) $company->tasks_complete,
        );
    }

    /**
     * Преобразование DTO в массив для JSON-сериализации
     * 
     * @return array<string, int>
     */
    public function toArray(): array
    {
        return [
            'total' => $this->total,
            'new' => $this->new,
            'process' => $this->process,
            'break' => $this->break,
            'decline' => $this->decline,
            'complete' => $this->complete,
        ];
    }
}

