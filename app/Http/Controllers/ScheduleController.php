<?php

namespace App\Http\Controllers;

use App\Contracts\Repositories\TaskRepositoryInterface;
use App\Http\Requests\SchedulePayload;
use App\Http\Resources\ScheduleResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

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
        $user = $request->user();
        $perPage = $request->getPerPage();
        $cursor = $request->getCursor();
        $date = $request->getDate();

        try {
            $tasks = $this->taskRepository->getUserTasksWithCursorPagination($user, $date, $perPage, $cursor);
        } catch (\Exception $e) {
            Log::error('Failed to fetch tasks', ['user_id' => $user->id, 'error' => $e]);
            return response()->json(['error' => 'Failed to fetch tasks'], 500);
        }

        $contract = ScheduleResource::collection($tasks);

        return $contract->response();
    }
}

