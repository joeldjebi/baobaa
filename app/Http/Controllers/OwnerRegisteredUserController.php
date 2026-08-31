<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Enums\VerificationStatus;
use App\Models\OwnerProfile;
use App\Models\PortalAccessRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class OwnerRegisteredUserController extends Controller
{
    public function create(): View
    {
        return view('auth.owner-register', [
            'mode' => 'guest',
            'ownerProfile' => null,
            'pendingRequest' => null,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['required', 'string', 'max:32'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'applicant_type' => ['required', 'in:individual,company'],
            'business_name' => ['required', 'string', 'max:255'],
            'legal_name' => ['nullable', 'string', 'max:255'],
            'tax_identifier' => ['nullable', 'string', 'max:120'],
            'country_code' => ['required', 'string', 'size:2'],
            'city' => ['required', 'string', 'max:255'],
            'motivation' => ['nullable', 'string', 'max:1200'],
        ]);

        $user = User::query()->create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'role' => UserRole::Client,
            'portal_roles' => [UserRole::Client->value],
            'status' => UserStatus::Active,
            'password' => $validated['password'],
        ]);

        OwnerProfile::query()->create([
            'user_id' => $user->id,
            'owner_type' => $validated['applicant_type'],
            'business_name' => $validated['business_name'],
            'legal_name' => $validated['legal_name'] ?? $validated['business_name'],
            'tax_identifier' => $validated['tax_identifier'] ?? null,
            'verification_status' => VerificationStatus::Pending,
            'country_code' => strtoupper($validated['country_code']),
            'city' => $validated['city'],
            'whatsapp_phone' => $validated['phone'],
            'billing_preference' => 'commission',
        ]);

        PortalAccessRequest::query()->create([
            'user_id' => $user->id,
            'requested_role' => UserRole::Owner,
            'status' => 'pending',
            'applicant_type' => $validated['applicant_type'],
            'business_name' => $validated['business_name'],
            'legal_name' => $validated['legal_name'] ?? null,
            'tax_identifier' => $validated['tax_identifier'] ?? null,
            'country_code' => strtoupper($validated['country_code']),
            'city' => $validated['city'],
            'whatsapp_phone' => $validated['phone'],
            'motivation' => $validated['motivation'] ?? null,
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('client.dashboard')->with('portal_status', 'Votre dossier partenaire est en cours de validation par le SAP.');
    }
}
