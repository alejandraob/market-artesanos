<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'artisan_id',
        'category_id',
        'name',
        'slug',
        'description',
        'price',
        'stock',
        'images',
        'is_featured',
        'is_active',
        'weight',
        'height',
        'width',
        'depth'
    ];

    protected $casts = [
        'images' => 'array',
        'is_featured' => 'boolean',
        'is_active' => 'boolean'
    ];

    public function artisan()
    {
        return $this->belongsTo(Artisan::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }
}
