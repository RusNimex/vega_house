<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TaskPayload extends FormRequest
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
            'notes' => 'required|string|max:255',
        ];
    }

    /**
     * Уберем пробелы перед валидацией
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'notes' => trim($this->input('notes', '')),
        ]);
    }
}

