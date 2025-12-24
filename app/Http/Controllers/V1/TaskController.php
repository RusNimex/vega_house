<?php

namespace App\Http\Controllers\V1;

use App\Contracts\Repositories\TaskRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\TaskPayload;
use App\Http\Resources\TaskResource;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function __construct(
        private readonly TaskRepositoryInterface $taskRepository
    ) {
    }

    /**
     * Обзор задачи
     *
     * @param Request $request параметры запроса
     * @param int $id задачи
     * @return JsonResponse
     */
    public function task(Request $request, int $id): JsonResponse
    {
        $task = $this->taskRepository
            ->getUserTaskById($request->user(), $id);

        if (!$task) {
            throw new ModelNotFoundException('Task not found');
        }

        return (new TaskResource($task))->response();
    }

    /**
     * Сохраним коммент от юзера к задаче
     *
     * @param TaskPayload $request
     * @param int $id
     * @return JsonResponse
     */
    public function notes(TaskPayload $request, int $id): JsonResponse
    {
        $task = $this->taskRepository
            ->getUserTaskById($request->user(), $id);

        if (!$task) {
            throw new ModelNotFoundException();
        }

        $task->notes = $request->get('notes');
        $task->save();

        return (new TaskResource($task))->response();
    }
}

