<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HabitRecords extends Model
{
    use HasFactory;

    protected $fillable = [
        'habit_id',
        'recorded_date',
        'completed',
        'note',
        'duration_minutes',
    ];

    protected $casts = [
        'recorded_date' => 'date',
        'completed' => 'boolean',
    ];

    public function habit(): BelongsTo
    {
        return $this->belongsTo(Habits::class);
    }
}
