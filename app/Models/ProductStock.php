<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductStock extends Model
{
    use HasFactory;

    protected $table = 'product_stock';

    protected $fillable = ['product_id', 'quantity'];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function sizeStock(): HasMany
    {
        return $this->hasMany(ProductSizeStock::class, 'product_id', 'product_id');
    }

    public function inventoryHistory(): HasMany
    {
        return $this->hasMany(InventoryHistory::class, 'product_id', 'product_id');
    }
}