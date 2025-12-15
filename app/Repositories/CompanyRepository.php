<?php

namespace App\Repositories;

use App\Enums\TaskStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

/**
 * Репозиторий для работы с компаниями
 * 
 * Инкапсулирует логику запросов к базе для работы с компаниями.
 */
class CompanyRepository
{
    /**
     * Получить компании с подсчетом задач по статусам
     * 
     * - Общее кол-во задач (tasks_count)
     * - со статусом 'new' (tasks_new)
     * - со статусом 'process' (tasks_process)
     * - со статусом 'break' (tasks_break)
     * - со статусом 'decline' (tasks_decline)
     * - со статусом 'complete' (tasks_complete)
     * 
     * @param User $user Пользователь, чьи компании нужно получить
     * @return Collection Коллекция моделей Company с загруженными счетчиками задач
     */
    public function getUserCompaniesWithTaskCounts(User $user): Collection
    {
        return $user
            ->companies()
            ->withCount([
                'tasks',
                'tasks as tasks_new' => function ($query) {
                    $query->where('status', TaskStatus::NEW->value);
                },
                'tasks as tasks_process' => function ($query) {
                    $query->where('status', TaskStatus::PROCESS->value);
                },
                'tasks as tasks_break' => function ($query) {
                    $query->where('status', TaskStatus::BREAK->value);
                },
                'tasks as tasks_decline' => function ($query) {
                    $query->where('status', TaskStatus::DECLINE->value);
                },
                'tasks as tasks_complete' => function ($query) {
                    $query->where('status', TaskStatus::COMPLETE->value);
                },
            ])
            ->get();
    }
}

