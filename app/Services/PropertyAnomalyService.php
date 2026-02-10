<?php

namespace App\Services;

use App\Models\Language;
use App\Models\Property\Property;

class PropertyAnomalyService
{
    /** Minimum words in description to avoid "description_short" anomaly */
    protected int $minDescriptionWords = 25;

    /** Price deviation threshold (e.g. 0.40 = 40%) to flag price anomalies */
    protected float $priceDeviationThreshold = 0.40;

    /**
     * Run anomaly detection on a property. Returns array of anomalies.
     *
     * @return array<int, array{type: string, message: string, severity: string}>
     */
    public function detect(Property $property): array
    {
        $anomalies = [];

        $property->load(['propertyContents']);

        // 1. Required fields
        $this->checkRequiredFields($property, $anomalies);

        // 2. Description quality (length)
        $this->checkDescriptionQuality($property, $anomalies);

        // 3. Price vs similar properties (only if we have price and comparable data)
        $this->checkPriceVsSimilar($property, $anomalies);

        return $anomalies;
    }

    protected function checkRequiredFields(Property $property, array &$anomalies): void
    {
        $defaultLang = Language::where('is_default', 1)->first();
        $content = $defaultLang
            ? $property->propertyContents->where('language_id', $defaultLang->id)->first()
            : $property->propertyContents->first();

        if (! $content) {
            $anomalies[] = [
                'type' => 'missing_field',
                'message' => __('No default language content found.'),
                'severity' => 'warning',
            ];
            return;
        }

        if (trim(strip_tags((string) $content->title)) === '') {
            $anomalies[] = [
                'type' => 'missing_field',
                'message' => __('Title is required.'),
                'severity' => 'warning',
            ];
        }
        if (trim(strip_tags((string) $content->address ?? '')) === '') {
            $anomalies[] = [
                'type' => 'missing_field',
                'message' => __('Address is required.'),
                'severity' => 'warning',
            ];
        }
        if (trim(strip_tags((string) $content->description ?? '')) === '') {
            $anomalies[] = [
                'type' => 'missing_field',
                'message' => __('Description is required.'),
                'severity' => 'warning',
            ];
        }

        $price = $property->price ?? null;
        if ($price === null || $price === '' || (is_numeric($price) && (float) $price <= 0)) {
            $anomalies[] = [
                'type' => 'missing_field',
                'message' => __('Price is required.'),
                'severity' => 'warning',
            ];
        }
    }

    protected function checkDescriptionQuality(Property $property, array &$anomalies): void
    {
        $defaultLang = Language::where('is_default', 1)->first();
        $content = $defaultLang
            ? $property->propertyContents->where('language_id', $defaultLang->id)->first()
            : $property->propertyContents->first();

        if (! $content || trim((string) $content->description) === '') {
            return;
        }

        $text = strip_tags((string) $content->description);
        $words = preg_split('/\s+/', trim($text), -1, PREG_SPLIT_NO_EMPTY);
        $wordCount = is_array($words) ? count($words) : 0;

        if ($wordCount < $this->minDescriptionWords) {
            $anomalies[] = [
                'type' => 'description_short',
                'message' => __('Description is short (recommended at least :min words).', ['min' => $this->minDescriptionWords]),
                'severity' => 'info',
            ];
        }
    }

    protected function checkPriceVsSimilar(Property $property, array &$anomalies): void
    {
        $price = $property->price;
        if ($price === null || $price === '' || ! is_numeric($price) || (float) $price <= 0) {
            return;
        }
        $price = (float) $price;

        $query = Property::where('id', '!=', $property->id)
            ->whereNotNull('price')
            ->where('price', '>', 0);

        if ($property->city_id) {
            $query->where('city_id', $property->city_id);
        } elseif ($property->state_id) {
            $query->where('state_id', $property->state_id);
        } elseif ($property->country_id) {
            $query->where('country_id', $property->country_id);
        } else {
            return;
        }

        if ($property->type) {
            $query->where('type', $property->type);
        }

        $similar = $query->select('price')->limit(50)->get();
        if ($similar->count() < 3) {
            return;
        }

        $prices = $similar->pluck('price')->map(function ($p) {
            return (float) $p;
        })->filter(function ($p) {
            return $p > 0;
        })->values();

        if ($prices->count() < 3) {
            return;
        }

        $avg = $prices->avg();
        $median = $prices->sort()->values()->get((int) floor($prices->count() / 2));

        $ref = $median > 0 ? $median : $avg;
        if ($ref <= 0) {
            return;
        }

        $ratio = $price / $ref;
        if ($ratio >= (1 + $this->priceDeviationThreshold)) {
            $pct = (int) round(($ratio - 1) * 100);
            $anomalies[] = [
                'type' => 'price_high',
                'message' => __('Price is about :pct% above similar listings in this area.', ['pct' => $pct]),
                'severity' => 'warning',
            ];
        } elseif ($ratio <= (1 - $this->priceDeviationThreshold)) {
            $pct = (int) round((1 - $ratio) * 100);
            $anomalies[] = [
                'type' => 'price_low',
                'message' => __('Price is about :pct% below similar listings in this area.', ['pct' => $pct]),
                'severity' => 'info',
            ];
        }
    }
}
