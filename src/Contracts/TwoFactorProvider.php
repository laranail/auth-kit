<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Auth\Contracts;

interface TwoFactorProvider
{
    /**
     * Send a verification code to the given identifier via the specified channel.
     *
     * @param  string  $identifier  The user's email, phone, or other identifier.
     * @param  string  $channel     The delivery channel (e.g., 'email', 'sms').
     * @return void
     */
    public function send(string $identifier, string $channel): void;

    /**
     * Validate the verification code for the given identifier.
     *
     * @param  string  $identifier  The user's email, phone, or other identifier.
     * @param  string  $code        The verification code to validate.
     * @return bool
     */
    public function validate(string $identifier, string $code): bool;
}
