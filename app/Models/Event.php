<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'event_date',
        'event_time',
        'location',
        'type',
        'description',
    ];

    protected $casts = [
        'event_date' => 'date',
    ];

    public function attendance()
    {
        return $this->hasMany(Attendance::class);
    }
}
