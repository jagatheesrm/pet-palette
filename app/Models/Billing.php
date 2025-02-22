<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Product;

class Billing extends Model
{
    use HasFactory;

    protected $fillable = ['client_id', 'total_amount', 'payment_status'];

    protected static function boot()
    {
        parent::boot();

        static::created(function ($billing) {
            foreach ($billing->items as $item) {
                $product = Product::find($item->product_id);
                if ($product && $product->stock >= $item->quantity) {
                    $product->decrement('stock', $item->quantity);
                }
            }
        });
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function items()
    {
        return $this->hasMany(BillingItem::class);
    }
}
