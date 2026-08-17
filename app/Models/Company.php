<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    use HasFactory;

    public const STATUS_ACTIVE = 'active';
    public const STATUS_SUSPENDED = 'suspended';

    protected $fillable = [
        'name',
        'slug',
        'email',
        'phone',
        'address',
        'logo',
        'timezone',
        'status',
        'latitude',
        'longitude',
        'radius_meters',
        'use_location_lock',
        'use_face_biometric',
    ];

    protected function casts(): array
    {
        return [
            'status' => 'string',
            'latitude' => 'float',
            'longitude' => 'float',
            'radius_meters' => 'integer',
            'use_location_lock' => 'boolean',
            'use_face_biometric' => 'boolean',
        ];
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function employees(): HasMany
    {
        return $this->hasMany(User::class)->where('role', User::ROLE_EMPLOYEE);
    }

    public function shifts(): HasMany
    {
        return $this->hasMany(Shift::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function leaves(): HasMany
    {
        return $this->hasMany(Leave::class);
    }

    public function faceTemplates(): HasMany
    {
        return $this->hasMany(FaceTemplate::class);
    }
}
