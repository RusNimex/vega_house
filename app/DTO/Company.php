<?php

namespace App\DTO;

use App\Models\Company as CompanyModel;
use InvalidArgumentException;

/**
 * DTO для представления компании в API
 * 
 * Основная инфа о компании и ее задачи.
 */
class Company
{
    public function __construct(
        public readonly int    $id,
        public readonly string $name,
        public readonly string $city,
        public readonly Tasks  $tasks,
    ) {
    }

    /**
     * DTO из модели Company
     * 
     * Модель Company должна:
     * 1. Иметь загруженные обязательные поля: id, name, city
     * 2. Иметь загруженные счетчики задач через withCount {@see Tasks::fromModel}
     * 
     * @param CompanyModel $company компания и счетчики задач
     * @return self
     * @throws InvalidArgumentException когда нет обязательных полей или счетчиков
     */
    public static function fromModel(CompanyModel $company): self
    {
        // Валидация модели
        if (empty($company->id)) {
            throw new InvalidArgumentException('Company model must have an id.');
        }

        if (empty($company->name)) {
            throw new InvalidArgumentException('Company model must have a name.');
        }

        if (empty($company->city)) {
            throw new InvalidArgumentException('Company model must have a city.');
        }

        // Создание Tasks (внутри своя валидация)
        $tasksDTO = Tasks::fromModel($company);

        return new self(
            id: (int) $company->id,
            name: (string) $company->name,
            city: (string) $company->city,
            tasks: $tasksDTO,
        );
    }

    /**
     * В массив для JSON-сериализации
     * 
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'city' => $this->city,
            'tasks' => $this->tasks->toArray(),
        ];
    }
}

