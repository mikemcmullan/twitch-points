<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Services\AuthenticateUser;
use Illuminate\Http\Request;

class AuthController extends Controller {

    /**
     * Login a user.
     *
     * @param Request $request
     * @param AuthenticateUser $authUser
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function login(Request $request, AuthenticateUser $authUser)
    {
        return $authUser->execute($request->get('code'), $request->get('error'), $this);
    }

    /**
     * Logout a user.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function logout()
    {
        \Auth::logout();

        return redirect()->route('home_path');
    }

    /**
     * @return \Illuminate\Http\RedirectResponse
     */
    public function loginHasFailed()
    {
        return redirect()
            ->route('home_path')
            ->with('message', 'Login failed.');

    }

    /**
     * @param $user
     * @return \Illuminate\Http\RedirectResponse
     */
    public function userHasLoggedIn($user)
    {
        return redirect()
            ->route('home_path')
            ->with('message', 'Login successful');
    }

}
