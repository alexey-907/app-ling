<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Word;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use function Laravel\Prompts\table;

class LoginController extends Controller
{
    public function enter(Request $request){
        $login = $request->login;
        $password = $request->password;
        $user = DB::table('users')->where('login', $login)->first();
        if (($login !== "" || $password !== "") && $user)
        {
            $hash_password = Hash::check($password, $user->password);
            $role = $user->role;
            if ($hash_password) {
                if ($role == 'admin') {
                    session(['admin_logged_in' => true]);
                    session()->save();
                    return redirect('/admin');
                } elseif ($role == 'translator') {
                    session(['translator_logged_in' => true]);
                    session()->save();
                    return redirect('/translator');
                }
                }
        }
        return view('login', ['error' => 'Неверный логин или пароль']);

    }
}
