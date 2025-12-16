<?php

namespace App\Http\Controllers\V1;

use App\Contracts\Repositories\TaskRepositoryInterface;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TaskController extends Controller
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
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'per_page' => 'sometimes|integer|min:1|max:100',
            'cursor' => 'sometimes|string',
        ]);

        $user = $request->user();
        $perPage = $request->input('per_page', 5);
        $cursor = $request->input('cursor');

        try {
            $tasks = $this->taskRepository->getUserTasksWithCursorPagination($user, $perPage, $cursor);
        } catch (\Exception $e) {
            Log::error('Failed to fetch tasks', ['user_id' => $user->id, 'error' => $e]);
            return response()->json(['error' => 'Failed to fetch tasks'], 500);
        }

        return response()->json($tasks);
    }
}

