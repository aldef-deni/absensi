<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    use BelongsToCompany, HasFactory;

    public const STATUS_PRESENT = 'present';
    public const STATUS_LATE = 'late';

    protected $fillable = [
        'company_id',
        'user_id',
        'date',
        'check_in',
        'check_out',
        'status',
        'late_minutes',
        'work_minutes',
        'location_in',
        'location_out',
        'note',
        'face_verified',
        'distance_in',
        'distance_out',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'check_in' => 'datetime',
            'check_out' => 'datetime',
            'late_minutes' => 'integer',
            'work_minutes' => 'integer',
            'face_verified' => 'boolean',
            'distance_in' => 'float',
            'distance_out' => 'float',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
