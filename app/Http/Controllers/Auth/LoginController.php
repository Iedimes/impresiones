<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use LdapRecord\Container;
use App\Ldap\User as LdapUser;

class LoginController extends Controller
{

    use AuthenticatesUsers;

    protected $redirectTo = '/home';

    public function __construct()
    {
        //$this->middleware('guest')->except('logout');
        //var_dump('hola');
    }

    public function username()
    {
        return config('ldap_auth.usernames.eloquent', 'username');
    }

    protected function validateLogin(Request $request)
    {
        $this->validate($request, [
            $this->username() => 'required|string|regex:/^\w+$/',
            'password' => 'required|string',
        ]);
    }

    protected function attemptLogin(Request $request)
    {
        $credentials = $request->only($this->username(), 'password');
        $username = $credentials[$this->username()];
        $password = $credentials['password'];

        // Logic from old controller: append @muvh for auth attempt
        $authUsername = $username . '@muvh';

        try {
            $connection = Container::getConnection('default');

            if ($connection->auth()->attempt($authUsername, $password, true)) {

                // Auth successful, finding local user
                $user = \App\User::where($this->username(), $username)->first();

                if (!$user) {
                    // Create new user if not exists
                    $user = new \App\User();
                    $user->{$this->username()} = $username;
                    $user->password = ''; // No password for LDAP users locally

                    $this->syncAttributes($user, $username);
                    $user->save();
                }

                $this->guard()->login($user, true);
                return true;
            }
        } catch (\Exception $e) {
            \Log::error("LDAP Error: " . $e->getMessage());
        }

        return false;
    }

    protected function syncAttributes(\App\User $user, $username)
    {
        // Search for the user in LDAP to get attributes
        $attribute = env('LDAP_USER_ATTRIBUTE', 'samaccountname');

        try {
            // Buscamos el usuario en LDAP usando el atributo configurado
            $ldapUser = LdapUser::query()->where($attribute, '=', $username)->first();

            if ($ldapUser) {
                // Sync attributes defined in old config
                // 'email' => 'userprincipalname', 'name' => 'displayName'

                $val = $ldapUser->getFirstAttribute('userprincipalname');
                if($val) $user->email = $val;

                $valName = $ldapUser->getFirstAttribute('displayName');
                if($valName) $user->name = $valName;

            }
        } catch (\Exception $e) {
            \Log::error("LDAP Sync Error: " . $e->getMessage());
        }
    }

    // Método auxiliar accedido por AuthenticatesUsers para la respuesta
    // Lo mantenemos vacío o default por ahora ya que attemptLogin maneja el login
}
