<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Order extends Model
{
    const STATUS = [
        'Placed' => 0,
        'Shiped' => 1,
        'Delivered' => 2,
        'Completed' => 3,
        'Failed' => 4,
        'Returned' => 5,
        'Partialy_Returned' => 6,

        0 => 'Placed',
        1 => 'Shiped',
        2 => 'Delivered',
        3 => 'Completed',
        4 => 'Failed',
        5 => 'Returned',
        6 => 'Partialy_Returned',
    ];

    protected $fillable = [
        'user_id',
        'total_price',
        'status',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class);
    }
}
