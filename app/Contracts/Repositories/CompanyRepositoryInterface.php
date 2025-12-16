<?php

namespace App\Contracts\Repositories;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

/**
 * Рпозиторий для работы с компаниями
 */
interface CompanyRepositoryInterface
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
     * @param User $user чьи компании нужно получить
     * @return Collection модели Company с загруженными счетчиками задач
     */
    public function getUserCompaniesWithTaskCounts(User $user): Collection;
}

