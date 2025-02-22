<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Orchid\Screen\AsSource;

class Product extends Model
{
    use HasFactory, AsSource;

    protected $fillable = ['name', 'category', 'price', 'purchase_rate', 'stock'];

    public function billingItems()
    {
        return $this->hasMany(BillingItem::class);
    }
}
