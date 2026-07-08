<?php

namespace App\Services;

use App\Models\ShippingRule;
use Illuminate\Support\Collection;

class ShippingCalculatorService
{
    /**
     * @param Collection $cartItems Items con product.category y product.artisan.user cargados
     */
    public function calculate(Collection $cartItems, string $province): array
    {
        $rules = ShippingRule::all();
        $rulesByArtisan = []; // "categoryId:artisanId" => rule
        $rulesDefault = [];   // "categoryId" => rule ("todos los artesanos")

        foreach ($rules as $rule) {
            if ($rule->artisan_id === null) {
                $rulesDefault[$rule->category_id] = $rule;
            } else {
                $rulesByArtisan["{$rule->category_id}:{$rule->artisan_id}"] = $rule;
            }
        }

        $itemsByArtisan = $cartItems->groupBy(fn($item) => $item->product->artisan_id);

        $artisans = [];
        $totalCost = 0;
        $hasPending = false;

        foreach ($itemsByArtisan as $artisanId => $items) {
            $artisan = $items->first()->product->artisan;
            $itemsByCategory = $items->groupBy(fn($item) => $item->product->category_id);

            $pending = false;
            $cost = 0;

            foreach ($itemsByCategory as $categoryId => $categoryItems) {
                $rule = $rulesByArtisan["{$categoryId}:{$artisanId}"] ?? $rulesDefault[$categoryId] ?? null;

                if (!$rule || $rule->shipping_mode === 'coordination') {
                    $pending = true;
                    continue;
                }

                $cost += $this->calculateGroupCost($rule, $categoryItems, $province);
            }

            if ($pending) {
                $hasPending = true;
                $cost = 0;
            } else {
                $totalCost += $cost;
            }

            $artisans[] = [
                'artisan_id' => $artisanId,
                'artisan_name' => $artisan?->user?->name ?? 'Artesano',
                'pending' => $pending,
                'cost' => $cost,
                'items' => $items->map(fn($item) => [
                    'product_id' => $item->product_id,
                    'name' => $item->product->name,
                    'quantity' => $item->quantity,
                ])->values(),
            ];
        }

        return [
            'artisans' => $artisans,
            'total_cost' => $totalCost,
            'has_pending' => $hasPending,
        ];
    }

    private function calculateGroupCost(ShippingRule $rule, Collection $items, string $province): float
    {
        switch ($rule->shipping_mode) {
            case 'flat':
                return (float) $rule->shipping_flat_price;

            case 'zone':
                $rates = $rule->shipping_zone_rates ?? [];
                return (float) ($rates[$province] ?? $rates['_default'] ?? 0);

            case 'weight':
                $totalGrams = $items->sum(fn($item) => ($item->product->weight ?? 1000) * $item->quantity);
                $totalKg = $totalGrams / 1000;
                return (float) $rule->shipping_weight_base + ((float) $rule->shipping_weight_rate * $totalKg);

            default:
                return 0;
        }
    }
}
