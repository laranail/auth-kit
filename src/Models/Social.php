<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Auth\Models;

use Illuminate\Database\Eloquent\Model;
use Simtabi\Laranail\Auth\Enums\SocialProviderEnum;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Social extends Model
{
    protected $fillable = [
        'user_id',
        'provider',
        'provider_id',
        'name',
        'nickname',
        'email',
        'avatar_path',
        'token',
        'refresh_token',
        'expires_at',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(
            related: config('auth-kit.model', \App\Models\User::class),
        );
    }

    protected function casts(): array
    {
        return [
            'provider'      => SocialProviderEnum::class,
            'token'         => 'encrypted',
            'refresh_token' => 'encrypted',
            'expires_at'    => 'immutable_datetime',
        ];
    }
}
