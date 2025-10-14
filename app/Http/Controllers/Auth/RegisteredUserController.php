<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:'.User::class,
            'phone' => 'required|max:10|min:10|unique:'.User::class,
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ], [
            'name.required' => 'Por favor ingresa tu nombre',
            'email.required' => 'Por favor ingresa tu correo electrónico.',
            'email.email' => 'El correo electrónico ingresado no es válido.',
            'email.unique' => 'El correo electrónico ya esta registrado.',
            'phone.required' => 'Por favor ingresa tu teléfono.',
            'phone.unique' => 'El teléfono ya esta registrado.',
            'phone.max' => 'El teléfono debe tener 10 dígitos.',
            'phone.min' => 'El teléfono debe tener 10 dígitos.',
            'password.required' => 'Por favor ingresa una contraseña.',
            'password.confirmed' => 'Las contraseñas no coinciden.'
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
        ]);

        $user->assignRole('customer');

        event(new Registered($user));

        Auth::login($user);

        $user->sendEmailVerificationNotification();

        return redirect(route('dashboard', absolute: false));
    }
}
