<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Guest extends Model
{
    protected $fillable = [
        'name',
        'phone',
        'table_name',
        'hall',
    ];

    public function attendance()
    {
        return $this->hasOne(Attendance::class)->latestOfMany();
    }

    public function hasAttended(): bool
    {
        return $this->attendance()->exists();
    }
}
