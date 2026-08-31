<?php

namespace App\Http\Controllers;

use App\Enums\PaymentStatus;
use App\Models\Payment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ClientProfileController extends Controller
{
    public function edit(Request $request): View
    {
        return view('dashboards.client-profile', [
            ...app(DashboardController::class)->clientMetricsFor($request->user()),
            'client' => $request->user(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:32'],
        ]);

        $request->user()->update($validated);

        return back()->with('profile_status', 'Profil mis à jour.');
    }

    public function password(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        if (! Hash::check($validated['current_password'], $request->user()->password)) {
            throw ValidationException::withMessages([
                'current_password' => 'Le mot de passe actuel est incorrect.',
            ]);
        }

        $request->user()->update([
            'password' => $validated['password'],
        ]);

        return back()->with('password_status', 'Mot de passe mis à jour.');
    }

    public function payments(Request $request): View
    {
        return view('dashboards.client-payments', [
            ...app(DashboardController::class)->clientMetricsFor($request->user()),
            'client' => $request->user(),
            'payments' => Payment::query()
                ->with('booking.venue')
                ->where('payer_id', $request->user()->id)
                ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
                ->when($request->filled('q'), function ($query) use ($request): void {
                    $search = '%'.$request->string('q')->toString().'%';
                    $query->where(function ($query) use ($search): void {
                        $query->where('reference', 'like', $search)
                            ->orWhereHas('booking.venue', fn ($query) => $query->where('name', 'like', $search));
                    });
                })
                ->latest()
                ->paginate(10, ['*'], 'payments_page')
                ->withQueryString(),
            'paymentStatusLabels' => [
                PaymentStatus::Initiated->value => 'Initialisé',
                PaymentStatus::Pending->value => 'En attente',
                PaymentStatus::Succeeded->value => 'Réussi',
                PaymentStatus::Failed->value => 'Échoué',
                PaymentStatus::Refunded->value => 'Remboursé',
                PaymentStatus::PartiallyRefunded->value => 'Partiellement remboursé',
            ],
        ]);
    }
}
