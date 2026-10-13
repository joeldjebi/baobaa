<?php

namespace App\Http\Controllers\Auth;

use App\Enums\ClientType;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class ClientRegisteredUserController extends Controller
{
    public function create(): View
    {
        return view('auth.client-register');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['required', 'string', 'max:32'],
            'client_type' => ['required', Rule::enum(ClientType::class)],
            'organization_name' => ['nullable', 'string', 'max:255', Rule::requiredIf(fn (): bool => ClientType::tryFrom((string) $request->input('client_type'))?->requiresOrganizationName() ?? false)],
            'password' => ['required', 'confirmed', Password::defaults()],
            'accept_terms' => ['accepted'],
        ]);

        $user = User::query()->create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'client_type' => $validated['client_type'],
            'organization_name' => ClientType::from($validated['client_type'])->requiresOrganizationName() ? $validated['organization_name'] : null,
            'role' => UserRole::Client,
            'portal_roles' => [UserRole::Client->value],
            'status' => UserStatus::Active,
            'password' => $validated['password'],
            'terms_accepted_at' => now(),
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('client.dashboard');
    }
}
