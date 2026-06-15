<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Auth\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Simtabi\Laranail\Auth\Actions\IssueSession;
use Simtabi\Laranail\Auth\Http\Requests\LoginRequest;
use Simtabi\Laranail\Auth\Actions\AuthenticateCredentials;

class LoginController extends Controller
{
    public function __construct(
        protected AuthenticateCredentials $authenticate,
        protected IssueSession $issueSession,
    ) {
    }

    /**
     * Show the login form.
     */
    public function create()
    {
        return view('auth.login');
    }

    /**
     * Handle a login request.
     */
    public function store(LoginRequest $request)
    {
        $modelClass = $request->getAuthModel();

        $user = $this->authenticate->handle(
            modelClass: $modelClass,
            email: $request->input('email'),
            password: $request->input('password'),
        );

        $this->issueSession->handle(request: $request, user: $user);

        return redirect()->intended(
            config('auth-kit.models.' . $request->getModelKey() . '.redirect', '/'),
        );
    }

    /**
     * Log the user out.
     */
    public function destroy(Request $request)
    {
        auth()->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route(
            config('auth-kit.models.' . $request->getModelKey() . '.login_route', 'login.create')
        );
    }
}
