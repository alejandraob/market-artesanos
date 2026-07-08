<?php

namespace App\Http\Controllers;

use App\Models\ShippingRule;
use Illuminate\Http\Request;

class ShippingRuleController extends Controller
{
    public function index()
    {
        return response()->json(
            ShippingRule::with(['category:id,name', 'artisan.user:id,name'])
                ->orderBy('category_id')
                ->get()
        );
    }

    public function store(Request $request)
    {
        $data = $this->validateRule($request);

        if ($this->ruleExists($data['category_id'], $data['artisan_id'])) {
            return response()->json(['message' => 'Ya existe una regla de envio para esa categoria y artesano.'], 422);
        }

        $rule = ShippingRule::create($data);

        return response()->json($rule->load('category:id,name', 'artisan.user:id,name'), 201);
    }

    public function update(Request $request, ShippingRule $shippingRule)
    {
        $data = $this->validateRule($request);

        if ($this->ruleExists($data['category_id'], $data['artisan_id'], $shippingRule->id)) {
            return response()->json(['message' => 'Ya existe una regla de envio para esa categoria y artesano.'], 422);
        }

        $shippingRule->update($data);

        return response()->json($shippingRule->load('category:id,name', 'artisan.user:id,name'));
    }

    public function destroy(ShippingRule $shippingRule)
    {
        $shippingRule->delete();
        return response()->json(null, 204);
    }

    private function ruleExists(int $categoryId, ?int $artisanId, ?int $exceptId = null): bool
    {
        $query = ShippingRule::where('category_id', $categoryId);
        $query = $artisanId === null ? $query->whereNull('artisan_id') : $query->where('artisan_id', $artisanId);

        if ($exceptId !== null) {
            $query->where('id', '!=', $exceptId);
        }

        return $query->exists();
    }

    private function validateRule(Request $request): array
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'artisan_id' => 'nullable|exists:artisans,id',
            'shipping_mode' => 'required|in:coordination,flat,zone,weight',
            'shipping_flat_price' => 'required_if:shipping_mode,flat|nullable|numeric|min:0',
            'shipping_zone_rates' => 'required_if:shipping_mode,zone|nullable|array',
            'shipping_weight_base' => 'required_if:shipping_mode,weight|nullable|numeric|min:0',
            'shipping_weight_rate' => 'required_if:shipping_mode,weight|nullable|numeric|min:0',
        ]);

        // Limpiar campos que no corresponden al modo elegido
        foreach (['shipping_flat_price', 'shipping_zone_rates', 'shipping_weight_base', 'shipping_weight_rate'] as $field) {
            if (!isset($validated[$field])) {
                $validated[$field] = null;
            }
        }
        if ($validated['shipping_mode'] !== 'flat') $validated['shipping_flat_price'] = null;
        if ($validated['shipping_mode'] !== 'zone') $validated['shipping_zone_rates'] = null;
        if ($validated['shipping_mode'] !== 'weight') {
            $validated['shipping_weight_base'] = null;
            $validated['shipping_weight_rate'] = null;
        }

        return $validated;
    }
}
