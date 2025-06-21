<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules;

class RegisterController extends Controller
{
    public function create()
    {
        return view('auth.register');
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
                'password' => ['required', 'confirmed', Rules\Password::defaults()],
            ]);

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'email_notifications' => true, // Valeur par défaut
            ]);

            Auth::login($user);

            return redirect()->route('dashboard')->with('success', 'Compte créé avec succès !');
            
        } catch (\Exception $e) {
            \Log::error('Erreur lors de l\'inscription: ' . $e->getMessage());
            return back()->withErrors(['email' => 'Une erreur est survenue lors de la création du compte.'])->withInput();
        }
    }
}
