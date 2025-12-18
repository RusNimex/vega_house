<?php

namespace App\Models;

use App\Enums\SubtaskStatus;
use Eloquent;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Подзадачи к задачам
 *
 * @property int $id
 * @property int $task_id
 * @property string $target текст задачи описывающий цель
 * @property SubtaskStatus $status ENUM('new','complete')
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Task $task
 * @mixin Eloquent
 */
class TasksSubtask extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tasks_subtasks';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'task_id',
        'target',
        'status',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => SubtaskStatus::class,
        ];
    }

    /**
     * Задача, к которой относится подзадача
     */
    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }
}

