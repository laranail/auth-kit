<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Auth\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PendingEmailToken extends Model
{
    use HasFactory;
    use Prunable;

    public function scopeActive(Builder $builder): Builder
    {
        return $builder->where(column: 'expires_at', operator: '>=', value: now());
    }

    public function prunable(): Builder
    {
        return static::where(column: 'expires_at', operator: '<', value: now());
    }

    public function invalidate(): void
    {
        $this->delete();
    }

    protected function casts(): array
    {
        return [
            'token'      => 'encrypted',
            'expires_at' => 'immutable_datetime',
        ];
    }
}
