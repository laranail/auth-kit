<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Auth\Actions;

use Simtabi\Laranail\Auth\Dtos\RegisterInput;
use Illuminate\Contracts\Auth\Authenticatable;
use Simtabi\Laranail\Auth\Models\PendingEmailToken;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Simtabi\Laranail\Auth\Contracts\RegisterInterface;

class Register implements RegisterInterface
{
    public function __construct(
        private AuthFactory $auth,
    ) {
    }

    public function execute(RegisterInput $input): Authenticatable
    {
        $provider = $this->auth->guard($input->guard)->getProvider();
        $modelClass = $provider->getModel();

        $user = new $modelClass();

        $user->fill([
            'email'     => $input->email,
            'password'  => $input->password,
            'name'      => $input->firstName,
            'last_name' => $input->lastName,
            'username'  => $input->username,
        ]);

        $user->save();

        PendingEmailToken::where(column: 'email', operator: '=', value: $input->email)->delete();

        return $user;
    }
}
