<?php

namespace App\Http\Resources;

use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Рабочее расписание с задачами
 *
 * @mixin Task
 */
class ScheduleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'company_id' => $this->company_id,
            'status' => $this->status->value,
            'description' => $this->description,
            'start' => $this->start?->toIso8601String(),
            'deadline' => $this->deadline?->toIso8601String(),
            'address' => $this->address,
            'notes' => $this->notes,
            'contacts' => $this->whenLoaded('contacts', function () {
                return $this->contacts->map(function ($contact) {
                    return [
                        'id' => $contact->id,
                        'name' => $contact->name,
                        'phone' => $contact->phone,
                        'email' => $contact->email,
                    ];
                });
            }),
        ];
    }
}

