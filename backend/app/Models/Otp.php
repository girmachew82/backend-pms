<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Otp extends Model
{
    protected $guarded = [];
    protected $dates  = ['expires_at'];
}
