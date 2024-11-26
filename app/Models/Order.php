<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Order extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $fillable = ['user_id', 'status', 'type', 'total_price', 'note', 'meta'];

    protected $casts = [
        'meta' => 'json',
    ];

    const MEDIA_BILL = 'order-bill';

    const STATUS_CREATED = 1;
    const STATUS_PAYMENT_SUCCESS = 2;
    const STATUS_PAYMENT_FAIL = 3;

    const TYPE_TAB = 1;
    const TYPE_SUBSCRIPTION = 2;
    
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}