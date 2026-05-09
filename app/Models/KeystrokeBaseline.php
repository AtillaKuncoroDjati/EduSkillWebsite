<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KeystrokeBaseline extends Model
{
    protected $fillable = [
        'user_id',
        'device_type',
        'mean_dwell',
        'std_dwell',
        'mean_flight',
        'std_flight',
        'mean_speed_cps',
        'std_speed_cps',
        'mean_error_rate',
        'sample_sessions',
    ];

    protected $casts = [
        'mean_dwell'      => 'float',
        'std_dwell'       => 'float',
        'mean_flight'     => 'float',
        'std_flight'      => 'float',
        'mean_speed_cps'  => 'float',
        'std_speed_cps'   => 'float',
        'mean_error_rate' => 'float',
        'sample_sessions' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
