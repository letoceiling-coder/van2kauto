<?php

namespace App\Services;

use App\Models\Lead;
use App\Models\ShowcaseCar;
use App\Services\Telegram\LeadNotifier;

class LeadService
{
    public function __construct(private readonly LeadNotifier $notifier) {}

    /** @param  array<string, mixed>  $data */
    public function create(array $data): Lead
    {
        if (! empty($data['car_slug'])) {
            $car = ShowcaseCar::query()->where('slug', $data['car_slug'])->first();
            if ($car) {
                $data['showcase_car_id'] = $car->id;
                $data['car_title'] ??= trim($car->title().' '.$car->trim.' '.$car->year);
            }
        }

        $lead = Lead::query()->create($data + ['status' => 'new', 'notify_attempts' => 0]);
        try {
            $this->notifier->notify($lead);
        } catch (\Throwable $e) {
            $lead->update(['notify_error' => $e->getMessage()]);
        }

        return $lead->fresh();
    }

    public function setStatus(Lead $lead, string $status, ?string $manager = null): Lead
    {
        $lead->update([
            'status' => $status,
            'manager' => $status === 'new' ? null : ($manager ?? $lead->manager),
        ]);
        $this->notifier->refresh($lead);

        return $lead;
    }
}
