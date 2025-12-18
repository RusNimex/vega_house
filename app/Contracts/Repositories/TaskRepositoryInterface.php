<?php

namespace App\Contracts\Repositories;

use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\CursorPaginator;

/**
 * Репозиторий для работы с задачами
 */
interface TaskRepositoryInterface
{
    /**
     * Задачи с курсорной пагинацией
     *
     * В мобилке cursor pagination работает хорошо.
     * Не обязательно перескакивать со странички настраничку.
     *
     * @param User $user чьи задачи нужно получить
     * @param Carbon $date на какую дату выбрать
     * @param int $perPage кол-во задач на странице
     * @param string|null $cursor курсор пагинатора (опционально)
     * @return CursorPaginator
     */
    public function getUserTasksWithCursorPagination(
        User $user,
        Carbon $date,
        int $perPage,
        ?string $cursor = null
    ): CursorPaginator;

    /**
     * Открываем задач, если эта задача доступна юзеру
     *
     * @param User $user
     * @param int $taskId
     * @return Task|null
     */
    public function getUserTaskById(User $user, int $taskId): ?Task;

}

