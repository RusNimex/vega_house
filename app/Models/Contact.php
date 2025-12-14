<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Contact extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'task_id',
        'name',
        'phone',
        'email',
    ];

    /**
     * Задача, к которой относится контакт
     */
    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }
}

