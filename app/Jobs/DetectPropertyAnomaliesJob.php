<?php

namespace App\Jobs;

use App\Models\Property\Property;
use App\Services\PropertyAnomalyService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DetectPropertyAnomaliesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public int $propertyId
    ) {}

    public function handle(PropertyAnomalyService $service): void
    {
        $property = Property::find($this->propertyId);
        if (! $property) {
            return;
        }

        $anomalies = $service->detect($property);
        $reviewSuggested = count($anomalies) > 0;

        $property->anomaly_checked_at = now();
        $property->anomaly_review_suggested = $reviewSuggested ? 1 : 0;
        $property->anomaly_flags = $anomalies;
        $property->saveQuietly();
    }
}
