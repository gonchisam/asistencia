<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    /**
     * Display the password reset link request view.
     */
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Handle an incoming password reset link request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        // Todos los mensajes traducidos al español
        $messages = [
            Password::RESET_LINK_SENT => 'Te hemos enviado por correo el enlace para restablecer tu contraseña.',
            Password::INVALID_USER => 'No encontramos ningún usuario con ese correo electrónico.',
            Password::RESET_THROTTLED => 'Por favor espera antes de intentar nuevamente.',
        ];

        return $status == Password::RESET_LINK_SENT
                    ? back()->with('status', $messages[$status])
                    : back()->withInput($request->only('email'))
                            ->withErrors(['email' => $messages[$status] ?? __($status)]);
    }
}