<?php

namespace App\Contracts\Repositories;

use App\Models\User;
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
     * @param int $perPage кол-во задач на странице
     * @param string|null $cursor курсор (опционально)
     * @return CursorPaginator
     */
    public function getUserTasksWithCursorPagination(User $user, int $perPage, ?string $cursor = null): CursorPaginator;
}

