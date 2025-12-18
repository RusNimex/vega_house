<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SchedulePayload extends FormRequest
{
    /**
     * Авторизован ли юзер
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Правила валидации для запроса
     */
    public function rules(): array
    {
        return [
            'per_page' => 'sometimes|integer|min:1|max:100',
            'cursor' => 'sometimes|string',
            'date' => 'sometimes|date',
        ];
    }

    /**
     * Получить количество элементов на странице
     */
    public function getPerPage(): int
    {
        return $this->input('per_page', 5);
    }

    /**
     * Получить курсор для пагинации
     */
    public function getCursor(): ?string
    {
        return $this->input('cursor');
    }

    /**
     * Дата фильтра, если выбрали в календаре
     */
    public function getDate()
    {
        return $this->input('date', now());
    }
}

