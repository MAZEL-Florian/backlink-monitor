<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function create()
    {
        return view('auth.login');
    }

    public function store(Request $request)
    {
        try {
            $credentials = $request->validate([
                'email' => ['required', 'email'],
                'password' => ['required'],
            ]);

            if (Auth::attempt($credentials, $request->boolean('remember'))) {
                $request->session()->regenerate();
                return redirect()->intended('dashboard');
            }

            return back()->withErrors([
                'email' => 'Les informations d\'identification fournies ne correspondent pas à nos enregistrements.',
            ])->onlyInput('email');
            
        } catch (\Exception $e) {
            \Log::error('Erreur lors de la connexion: ' . $e->getMessage());
            return back()->withErrors(['email' => 'Une erreur est survenue lors de la connexion.'])->withInput();
        }
    }

    public function destroy(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}
