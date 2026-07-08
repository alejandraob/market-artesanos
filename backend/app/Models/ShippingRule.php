<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShippingRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'artisan_id',
        'shipping_mode',
        'shipping_flat_price',
        'shipping_zone_rates',
        'shipping_weight_base',
        'shipping_weight_rate',
    ];

    protected $casts = [
        'shipping_zone_rates' => 'array',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function artisan()
    {
        return $this->belongsTo(Artisan::class);
    }
}
