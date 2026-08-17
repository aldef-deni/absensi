<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use BelongsToCompany, HasFactory, Notifiable;

    public const ROLE_SUPER_ADMIN = 'super_admin';
    public const ROLE_ADMIN = 'admin';
    public const ROLE_EMPLOYEE = 'employee';

    protected $fillable = [
        'company_id',
        'name',
        'email',
        'password',
        'role',
        'employee_code',
        'position',
        'phone',
        'is_active',
        'photo',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === self::ROLE_SUPER_ADMIN;
    }

    public function isAdmin(): bool
    {
        return in_array($this->role, [self::ROLE_ADMIN, self::ROLE_SUPER_ADMIN], true);
    }

    /**
     * Perusahaan yang sedang dikelola user.
     * Super admin tidak terikat satu perusahaan — ia bisa memilih via ?company_id=,
     * dengan fallback ke pilihan terakhir (session) atau perusahaan pertama.
     */
    public function companyContext(?int $requestedCompanyId = null): ?Company
    {
        if (! $this->isSuperAdmin()) {
            return $this->company;
        }

        $id = $requestedCompanyId ?: (int) session('company_context_id', 0);
        $company = $id > 0 ? Company::find($id) : null;

        if (! $company) {
            $company = Company::query()->orderBy('id')->first();
        }

        if ($company) {
            session(['company_context_id' => $company->id]);
        }

        return $company;
    }

    /**
     * Daftar perusahaan untuk switcher: semua perusahaan untuk super admin,
     * hanya perusahaannya sendiri untuk admin/karyawan.
     */
    public function companyOptions(?Company $active = null): \Illuminate\Support\Collection
    {
        if ($this->isSuperAdmin()) {
            return Company::query()->orderBy('name')->get();
        }

        return $active ? collect([$active]) : collect();
    }

    public function isEmployee(): bool
    {
        return $this->role === self::ROLE_EMPLOYEE;
    }

    /**
     * Inisial dari nama (maksimal 2 huruf) untuk avatar default.
     */
    public function avatarInitials(): string
    {
        return collect(explode(' ', trim($this->name)))
            ->filter()
            ->map(fn (string $word) => mb_strtoupper(mb_substr($word, 0, 1)))
            ->take(2)
            ->implode('');
    }

    /**
     * URL foto profil. Jika belum ada, kembalikan avatar SVG inisial (tanpa internet).
     */
    public function avatarUrl(): string
    {
        if ($this->photo) {
            return asset('uploads/photos/'.$this->photo);
        }

        $initials = $this->avatarInitials() ?: '?';
        $colors = ['#4f46e5', '#0d9488', '#d97706', '#dc2626', '#7c3aed', '#2563eb', '#059669', '#db2777'];
        $color = $colors[abs(crc32($this->email ?? $this->name)) % count($colors)];

        $svg = sprintf(
            '<svg xmlns="http://www.w3.org/2000/svg" width="80" height="80" viewBox="0 0 80 80"><rect width="80" height="80" rx="40" fill="%s"/><text x="50%%" y="50%%" dy=".35em" text-anchor="middle" font-family="Arial, sans-serif" font-size="30" font-weight="600" fill="#ffffff">%s</text></svg>',
            $color,
            htmlspecialchars($initials, ENT_QUOTES, 'UTF-8'),
        );

        return 'data:image/svg+xml;base64,'.base64_encode($svg);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function leaves(): HasMany
    {
        return $this->hasMany(Leave::class);
    }

    public function approvedLeaves(): HasMany
    {
        return $this->hasMany(Leave::class, 'approved_by');
    }
}
