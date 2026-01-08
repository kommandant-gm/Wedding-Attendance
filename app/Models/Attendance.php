<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'guest_id',
        'checked_in_at',
        'checked_in_by',
        'ip_address',
    ];

    protected $casts = [
        'checked_in_at' => 'datetime',
    ];

    public function guest()
    {
        return $this->belongsTo(Guest::class);
    }
}
