<?php

namespace App\Repositories;

use App\Contracts\Repositories\CompanyRepositoryInterface;
use App\Enums\TaskStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

/**
 * Репозиторий для работы с компаниями
 * 
 * Реализация CompanyRepositoryInterface.
 * Инкапсулирует логику запросов к базе для работы с компаниями.
 */
class CompanyRepository implements CompanyRepositoryInterface
{
    /**
     * {@inheritDoc}
     */
    public function getUserCompaniesWithTaskCounts(User $user): Collection
    {
        return $user
            ->companies()
            ->wherePivot('enabled', 1)
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

