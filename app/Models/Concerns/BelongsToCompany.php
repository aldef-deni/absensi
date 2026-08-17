<?php

namespace App\Models\Concerns;

use App\Models\Company;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToCompany
{
    protected static function bootBelongsToCompany(): void
    {
        static::addGlobalScope('company', function ($builder) {
            // PENTING: gunakan hasUser(), bukan user().
            // Memanggil user() di sini memicu resolusi user oleh guard, yang
            // menjalankan query ke model ini lagi -> rekursi tak terbatas.
            if (! auth()->hasUser()) {
                return;
            }

            $user = auth()->user();

            // Super admin melihat semua tenant.
            if ($user->role === 'super_admin') {
                return;
            }

            $table = $builder->getModel()->getTable();
            $builder->where($table.'.company_id', $user->company_id);
        });
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
