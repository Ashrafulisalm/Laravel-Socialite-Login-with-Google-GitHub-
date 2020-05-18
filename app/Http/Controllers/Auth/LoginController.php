<?php

namespace App\Http\Controllers\Auth;
use App\User;
use Illuminate\Support\Facades\Auth;
use Socialite;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    //Methods for github

    public function redirectToProvider($website)
    {
        return Socialite::driver($website)->redirect();
    }

    /**
     * Obtain the user information from GitHub.
     *
     * @return \Illuminate\Http\Response
     */
    public function handleProviderCallback($website)
    {
        if ($website=='google'){
            $user = Socialite::driver($website)->stateless()->user();
        } else {
            $user = Socialite::driver($website)->user();
        }

        //If registered already then get the user
        $is_user=User::where('email',$user->getEmail())->first();
        if ($is_user){
            Auth::login($is_user);
            return redirect('/');
        } else {
            //Create a new user by getting data by API
            $new_user=new User;
            $new_user->name=$user->getName();
            $new_user->email=$user->getEmail();
            $new_user->password=bcrypt($user->getEmail());

            if($new_user->save()){
                Auth::login($new_user);
                return redirect('/');
            }
        }

    }
}
