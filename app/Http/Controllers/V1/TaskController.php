<?php

namespace App\Http\Controllers\V1;

use App\Contracts\Repositories\TaskRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\TaskPayload;
use App\Http\Resources\TaskResource;
use Illuminate\Database\QueryException;
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
     * Обзор задачи
     *
     * @param Request $request параметры запроса
     * @param int $id задачи
     * @return JsonResponse
     */
    public function task(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        try {
            $task = $this->taskRepository->getUserTaskById($user, $id);

            if (!$task) {
                return response()->json(['error' => 'Task not found'], 404);
            }

            return (new TaskResource($task))->response();
        } catch (\Exception $e) {
            Log::error('Failed to fetch task', [
                'user_id' => $user->id,
                'task_id' => $id,
                'error' => $e->getMessage()
            ]);
            return response()->json(['error' => 'Failed to fetch task'], 500);
        }
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
        $user = $request->user();
        try {
            $task = $this->taskRepository->getUserTaskById($user, $id);
            if (!$task) {
                return response()->json(['error' => 'Task not found'], 404);
            }
            $task->notes = $request->get('notes');
            $task->save();

            return (new TaskResource($task))->response();
        } catch (QueryException $e) {
            Log::error('Database exception while update task', [
                'user_id' => $user->id,
                'task_id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'error' => 'Failed to update task notes',
                'message' => config('app.debug') ? $e->getMessage() : 'Database error occurred'
            ], 500);
        } catch (\Exception $e) {
            Log::error('Failed to update task notes', [
                'user_id' => $user->id,
                'task_id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json(['error' => 'Failed to fetch task'], 500);
        }
    }
}

