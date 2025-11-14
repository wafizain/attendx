<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class FirstPasswordChangeController extends Controller
{
    public function showForm()
    {
        return view('auth.first-change');
    }

    public function update(Request $request)
    {
        $request->validate([
            'old_password' => 'required',
            'password' => 'required|confirmed|min:8',
        ]);
        $user = Auth::user();
        if (!Hash::check($request->old_password, $user->password)) {
            return back()->withErrors(['old_password' => 'Password lama salah.']);
        }
        $user->password = Hash::make($request->password);
        $user->password_changed_at = now();
        $user->save();
        return redirect('/dashboard')->with('success', 'Password berhasil diganti.');
    }
}
