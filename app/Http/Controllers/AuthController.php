<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Helpers\LogHelper;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->only('username', 'password');
        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            
            // Catat log aktivitas login
            LogHelper::login(auth()->id(), 'User ' . auth()->user()->name . ' berhasil login');
            
            return redirect()->intended('/dashboard');
        }
        return back()->withErrors([
            'username' => 'Username atau password salah.',
        ])->withInput();
    }
    
    public function logout(Request $request)
    {
        // Catat log aktivitas logout sebelum logout
        LogHelper::logout(auth()->id(), 'User ' . auth()->user()->name . ' berhasil logout');
        
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect('/login');
    }
}
