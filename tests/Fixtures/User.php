<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Auth\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;
use Simtabi\Laranail\Auth\Models\Concerns\HasEmailLogin;

class User extends Model
{
    use HasEmailLogin;

    protected $table = 'users';

    protected $hidden = ['password'];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }
}
