<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Auth\Models;

use Illuminate\Database\Eloquent\Model;
use Simtabi\Laranail\Auth\Enums\SocialProvider;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Simtabi\Laranail\Auth\Database\Factories\SocialFactory;

class Social extends Model
{
    use HasFactory;

    /** @var array<int, string> */
    protected $fillable = [
        'socialable_type',
        'socialable_id',
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

    public static function newFactory(): SocialFactory
    {
        return new SocialFactory();
    }

    public function socialable(): MorphTo
    {
        return $this->morphTo();
    }

    /** @return array<string, mixed> */
    protected function casts(): array
    {
        return [
            'provider'      => SocialProvider::class,
            'token'         => 'encrypted',
            'refresh_token' => 'encrypted',
            'expires_at'    => 'immutable_datetime',
        ];
    }
}
