<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Auth\Concerns;

use Simtabi\Laranail\Auth\Models\Social;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait LaranailUser
{
    public function socialAccounts(): HasMany
    {
        return $this->hasMany(Social::class);
    }

    public function connectedAccounts(): HasMany
    {
        return $this->socialAccounts();
    }

    public function hasSocialAccount(string $provider): bool
    {
        return $this->socialAccounts()
            ->where('provider', $provider)
            ->exists();
    }

    public function getSocialAccount(string $provider): ?Social
    {
        return $this->socialAccounts()
            ->where('provider', $provider)
            ->first();
    }

    public function getSocialAvatarUrl(): ?string
    {
        return $this->socialAccounts()
            ->whereNotNull('avatar_path')
            ->value('avatar_path');
    }
}
