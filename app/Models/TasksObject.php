<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Объекты к задачам
 *
 * @property int $id
 * @property int $task_id
 * @property string $name название объекта
 * @property string $description дополнительно описание
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property bool $completed 1/0 - завершено/в работе
 * @property-read Task $task
 * @mixin Eloquent
 */
class TasksObject extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tasks_objects';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'task_id',
        'name',
        'description',
        'completed',
    ];

    /**
     * Задача, к которой относится объект
     */
    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }
}

