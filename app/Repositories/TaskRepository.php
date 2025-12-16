<?php

namespace App\Repositories;

use App\Contracts\Repositories\TaskRepositoryInterface;
use App\Models\Task;
use App\Models\User;
use Illuminate\Contracts\Pagination\CursorPaginator;

/**
 * Репозиторий для работы с задачами
 * 
 * Реализация TaskRepositoryInterface.
 * Инкапсулирует логику запросов к базе для работы с задачами.
 */
class TaskRepository implements TaskRepositoryInterface
{
    /**
     * {@inheritDoc}
     */
    public function getUserTasksWithCursorPagination(User $user, int $perPage, ?string $cursor = null): CursorPaginator
    {
        $companyIds = $user->activeCompanies()->get()->pluck('id')->toArray();

        // Если нет компаний
        if (empty($companyIds)) {
            return Task::query()
                ->whereRaw('1 = 0') // Пустой запрос
                ->orderBy('created_at', 'desc')
                ->orderBy('id', 'desc')
                ->cursorPaginate($perPage);
        }

        // Получаем задачи компаний пользователя с курсорной пагинацией
        $query = Task::whereIn('company_id', $companyIds)
            ->with(['company:id,name,city', 'contacts:id,task_id,name,phone,email'])
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc'); // Второй критерий для уникальности курсора

        // Применяем курсор, если он передан
        if ($cursor !== null) {
            return $query->cursorPaginate($perPage, ['*'], 'cursor', $cursor);
        }

        return $query->cursorPaginate($perPage);
    }
}

