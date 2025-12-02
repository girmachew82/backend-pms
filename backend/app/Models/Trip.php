<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Trip extends Model
{
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class);

    }

    protected $casts = [
        'origin' =>'array',
        'destination' =>'array',
        'driver_location' =>'array',
        'is_started' => 'boolean',
        'is_completed' =>'boolean'
    ];
}
