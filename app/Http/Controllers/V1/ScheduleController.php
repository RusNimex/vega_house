<?php

namespace App\Http\Controllers\V1;

use App\Contracts\Repositories\TaskRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\SchedulePayload;
use App\Http\Resources\ScheduleResource;
use Illuminate\Http\JsonResponse;

class ScheduleController extends Controller
{
    public function __construct(
        private readonly TaskRepositoryInterface $taskRepository
    ) {
    }

    /**
     * Все задачи юзера + курсорная пагинация
     *
     * Задачи всех компаний, к которым принадлежит юзер.
     * Каждая задача включает информацию о компании и контактах.
     *
     * Поддерживает параметры запроса:
     * - per_page: количество элементов на странице (по умолчанию 5, максимум 100)
     * - cursor: курсор для навигации к следующей/предыдущей странице
     *
     * @param SchedulePayload $request параметры запроса
     * @return JsonResponse
     */
    public function index(SchedulePayload $request): JsonResponse
    {
        $contract = ScheduleResource::collection(
            $this->taskRepository
                ->getUserTasksWithCursorPagination(
                    $request->user(),
                    $request->getDate(),
                    $request->getPerPage(),
                    $request->getCursor()
                )
        );

        return $contract->response();
    }
}

