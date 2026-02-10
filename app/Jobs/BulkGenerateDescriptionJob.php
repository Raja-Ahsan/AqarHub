<?php

namespace App\Jobs;

use App\Models\Language;
use App\Models\Property\Property;
use App\Services\AiAssistantService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class BulkGenerateDescriptionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public int $propertyId
    ) {}

    public function handle(AiAssistantService $aiService): void
    {
        $property = Property::with('propertyContents')->find($this->propertyId);
        if (! $property) {
            return;
        }

        $defaultLang = Language::where('is_default', 1)->first();
        if (! $defaultLang) {
            return;
        }

        $content = $property->propertyContents->where('language_id', $defaultLang->id)->first();
        if (! $content) {
            $content = $property->propertyContents->first();
        }
        if (! $content) {
            return;
        }

        $title = trim(strip_tags((string) $content->title));
        if ($title === '') {
            return;
        }

        $location = trim(strip_tags((string) $content->address ?? ''));
        $features = '';

        $context = [];
        if ($property->price !== null && $property->price !== '') {
            $context['price'] = (string) $property->price;
        }
        if ($property->type) {
            $context['type'] = $property->type;
        }
        if ($property->purpose) {
            $context['purpose'] = $property->purpose;
        }
        if ($property->beds !== null && $property->beds !== '') {
            $context['beds'] = (string) $property->beds;
        }
        if ($property->bath !== null && $property->bath !== '') {
            $context['bath'] = (string) $property->bath;
        }
        if ($property->area !== null && $property->area !== '') {
            $context['area'] = (string) $property->area;
        }
        $cityContent = $property->propertyCity?->cityContents()->where('language_id', $defaultLang->id)->first();
        if ($cityContent && trim((string) $cityContent->name) !== '') {
            $context['city'] = trim($cityContent->name);
        }

        if (! $aiService->isAvailable()) {
            return;
        }

        $result = $aiService->generatePropertyDescription($title, $location, $features, $context);
        if (! ($result['success'] ?? false)) {
            return;
        }

        $content->description = $result['description'] ?? $content->description;
        if (isset($result['meta_keywords']) && $result['meta_keywords'] !== '') {
            $content->meta_keyword = $result['meta_keywords'];
        }
        if (isset($result['meta_description']) && $result['meta_description'] !== '') {
            $content->meta_description = $result['meta_description'];
        }
        $content->save();
    }
}
