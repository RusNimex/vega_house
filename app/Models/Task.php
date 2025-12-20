<?php

namespace App\Models;

use App\Enums\TaskStatus;
use Eloquent;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * Задачи компании
 *
 * @property int $id
 * @property int $company_id
 * @property TaskStatus $status
 * @property string $description
 * @property Carbon $start дата начала осмотра
 * @property Carbon $deadline
 * @property string $address
 * @property string|null $notes к задаче, оставляет юзер сам себе
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Company $company
 * @property-read Collection<int, Contact> $contacts
 * @property-read Collection<int, TasksObject> $objects объекты осмотра
 * @property-read int|null $objects_amount объектов для осмотра всего
 * @property-read int|null $objects_completed завершенные объекты
 * @property-read Collection<int, TasksSubtask> $subtasks подзадачи по осмотру
 * @property-read int|null $count_new задач в работу (NEW) {@see TaskStatus}
 * @property-read int|null $count_completed завершенных задач (COMPLETE) {@see TaskStatus}
 * @mixin Eloquent
 */
class Task extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'company_id',
        'status',
        'description',
        'start',
        'deadline',
        'address',
        'notes',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => TaskStatus::class,
            'start' => 'datetime',
            'deadline' => 'datetime',
        ];
    }

    /**
     * Компания, к которой относится задача
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Контакты задачи
     */
    public function contacts(): HasMany
    {
        return $this->hasMany(Contact::class, 'task_id', 'id');
    }

    /**
     * Объекты задачи
     */
    public function objects(): HasMany
    {
        return $this->hasMany(TasksObject::class, 'task_id', 'id');
    }

    /**
     * Подзадачи задачи
     */
    public function subtasks(): HasMany
    {
        return $this->hasMany(TasksSubtask::class, 'task_id', 'id');
    }
}

