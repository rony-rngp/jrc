<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use App\Helpers\ActivityLogger;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    protected $redirectTo = '/home';

    public function __construct()
    {
        // For Laravel 11/10 compatibility
    }

    public function username()
    {
        return 'username';
    }

    protected function authenticated(Request $request, $user)
    {
        session([
            'admin' => $user->id,
            'role' => $user->role,
            'empID' => $user->empID,
        ]);

        ActivityLogger::log('Login', 'User ' . $user->username . ' logged in.');

        return redirect()->intended($this->redirectPath());
    }

    protected function loggedOut(Request $request)
    {
        return redirect('/login');
    }
}
