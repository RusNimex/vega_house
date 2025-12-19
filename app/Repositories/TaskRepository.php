<?php

namespace App\Repositories;

use App\Contracts\Repositories\TaskRepositoryInterface;
use App\Enums\SubtaskStatus;
use App\Enums\TaskStatus;
use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\CursorPaginator;

/**
 * Репозиторий для работы с задачами
 */
class TaskRepository implements TaskRepositoryInterface
{
    /**
     * {@inheritDoc}
     */
    public function getUserTasksWithCursorPagination(
        User $user,
        Carbon $date,
        int $perPage,
        ?string $cursor = null
    ): CursorPaginator
    {
        $companyIds = $this->getActiveCompanyIds($user);

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
            ->where('start', '<=', $date)
            ->whereIn('status', [
                TaskStatus::NEW,
                TaskStatus::PROCESS,
            ])
            ->orderBy('start', 'desc')
            ->orderBy('id', 'desc'); // Второй критерий для уникальности курсора

        // Применяем курсор, если он передан
        if ($cursor !== null) {
            return $query->cursorPaginate($perPage, ['*'], 'cursor', $cursor);
        }

        return $query->cursorPaginate($perPage);
    }

    /**
     * {@inheritDoc}
     */
    public function getUserTaskById(User $user, int $taskId): ?Task
    {
        $companyIds = $this->getActiveCompanyIds($user);

        if (empty($companyIds)) {
            return null;
        }

        return Task::whereIn('company_id', $companyIds)
            ->where('id', $taskId)
            ->with(['contacts'])
            ->withCount([
                'objects as objects_amount',
                'objects as objects_completed' => function ($query) {
                    $query->where('completed', 1);
                }
            ])
            ->first();
    }

    /**
     * Активные компании, чтоб случайно не выбрать чужие задачи
     */
    private function getActiveCompanyIds(User $user): array
    {
        return $user->activeCompanies()
            ->pluck('companies.id')
            ->toArray();
    }
}

